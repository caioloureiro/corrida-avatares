<?php
require_once __DIR__ . '/config/db.php';

// Meta de seguidores
$META_SEGUIDORES = 2000;

// Buscar dados da tabela corrida - apenas o registro mais recente de cada avatar
$sql = "SELECT * FROM corrida WHERE ativo = 1 AND id IN (
    SELECT MAX(id) FROM corrida WHERE ativo = 1 GROUP BY nome
) ORDER BY seguidores DESC";
$result = $conn->query($sql);

if (!$result) {
	die('Erro na consulta: ' . $conn->error);
}

$avatares = [];
$posicao = 1;
$total_seguidores = 0;

while ($row = $result->fetch_assoc()) {
	$row['posicao'] = $posicao;
	$row['percentual'] = ($row['seguidores'] / $META_SEGUIDORES) * 100;
	$avatares[] = $row;
	$total_seguidores += $row['seguidores'];
	$posicao++;
}

$media_seguidores = count($avatares) > 0 ? intval($total_seguidores / count($avatares)) : 0;
$progresso_total = count($avatares) > 0 ? ($total_seguidores / ($META_SEGUIDORES * count($avatares))) * 100 : 0;

// Buscar data da última modificação
require_once __DIR__ . '/config/db.php';
$sql_data = "SELECT MAX(updated_at) as ultima_atualizacao FROM corrida WHERE ativo = 1";
$result_data = $conn->query($sql_data);
$row_data = $result_data->fetch_assoc();
$ultima_atualizacao = $row_data['ultima_atualizacao'];
$data_formatada = $ultima_atualizacao ? date('d/m/Y H:i:s', strtotime($ultima_atualizacao)) : 'Sem registros';

// Buscar dados históricos para o gráfico
require_once __DIR__ . '/config/db.php';
$sql_historico = "SELECT nome, seguidores, DATE(data) as data FROM corrida WHERE ativo = 1 ORDER BY data ASC";
$result_historico = $conn->query($sql_historico);

$dados_historicos = [];
while ($row = $result_historico->fetch_assoc()) {
	$dados_historicos[] = $row;
}

// Organizar dados por avatar
$grafico_dados = [];
$datas_unicas = [];

foreach ($dados_historicos as $dado) {
	if (!isset($grafico_dados[$dado['nome']])) {
		$grafico_dados[$dado['nome']] = [];
	}
	$grafico_dados[$dado['nome']][] = [
		'data' => $dado['data'],
		'seguidores' => $dado['seguidores']
	];
	if (!in_array($dado['data'], $datas_unicas)) {
		$datas_unicas[] = $dado['data'];
	}
}

sort($datas_unicas);
$grafico_json = json_encode(['datas' => $datas_unicas, 'avatares' => $grafico_dados]);

$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Corrida de Avatares - Dashboard</title>
	<link rel="stylesheet" href="./css/style.css">
	<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
</head>

<body>
	<header>
		<div class="container">
			<h1>🏁 Corrida de Avatares</h1>
			<p>Sistema de monitoramento de seguidores em tempo real</p>
		</div>
	</header>

	<main class="container">
		<!-- Dashboard de Estatísticas -->
		<div class="dashboard">
			<div class="stat-card">
				<h3>Total de Seguidores</h3>
				<div class="value"><?php echo number_format($total_seguidores, 0, ',', '.'); ?></div>
				<div class="meta">Todos os avatares</div>
			</div>

			<div class="stat-card">
				<h3>Média por Avatar</h3>
				<div class="value"><?php echo number_format($media_seguidores, 0, ',', '.'); ?></div>
				<div class="meta">de 2.000</div>
			</div>

			<div class="stat-card">
				<h3>Progresso Total</h3>
				<div class="value"><?php echo number_format($progresso_total, 1, ',', '.'); ?>%</div>
				<div class="meta">rumo à meta</div>
			</div>

			<div class="stat-card">
				<h3>Avatares Ativos</h3>
				<div class="value"><?php echo count($avatares); ?></div>
				<div class="meta">na competição</div>
			</div>

			<div class="stat-card">
				<h3>Última Atualização</h3>
				<div class="value" style="font-size: 1.2em;">🕐</div>
				<div class="meta"><?php echo $data_formatada; ?></div>
			</div>
		</div>

		<!-- Tabela de Corrida -->
		<div class="table-section">
			<div class="table-header">
				<h2>📊 Ranking de Seguidores</h2>
				<p>Clique nos valores e pressione Enter para editar • Meta: 2.000 seguidores por avatar</p>
			</div>

			<table>
				<thead>
					<tr>
						<th>Posição</th>
						<th>Avatar</th>
						<th>Seguidores</th>
						<th>Progresso</th>
						<th>Percentual</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($avatares as $avatar): ?>
						<tr data-id="<?php echo $avatar['id']; ?>" data-original-value="<?php echo $avatar['seguidores']; ?>">
							<td>
								<span class="pos-badge <?php
														echo $avatar['posicao'] == 1 ? 'first' : ($avatar['posicao'] == 2 ? 'second' : ($avatar['posicao'] == 3 ? 'third' : ''));
														?>">
									#<?php echo $avatar['posicao']; ?>
								</span>
							</td>
							<td>
								<div class="avatar-name">
									<div class="avatar-initial"><?php echo strtoupper(substr($avatar['nome'], 0, 1)); ?></div>
									<span><?php echo htmlspecialchars($avatar['nome']); ?></span>
								</div>
							</td>
							<td>
								<input
									type="number"
									class="seguidores-input"
									value="<?php echo $avatar['seguidores']; ?>"
									min="0"
									placeholder="0">
								<span class="loading"></span>
							</td>
							<td>
								<div class="progress-bar-container">
									<div class="progress-bar" style="width: <?php echo min($avatar['percentual'], 100); ?>%"></div>
								</div>
							</td>
							<td>
								<span class="percentage"><?php echo number_format($avatar['percentual'], 2, ',', '.'); ?>%</span>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<div class="info-text">
				ℹ️ <strong>Como usar:</strong> Clique no campo de seguidores, insira o novo valor e pressione <strong>Enter</strong> para salvar no banco de dados automaticamente.
			</div>
		</div>

		<!-- Gráfico de Evolução -->
		<div class="chart-section">
			<div class="table-header">
				<h2>📈 Evolução de Seguidores</h2>
				<p>Acompanhe o crescimento ao longo do tempo</p>
			</div>
			<div class="chart-container">
				<canvas id="graficoEvolucao"></canvas>
			</div>
		</div>
	</main>

	<script>
		const dadosGrafico = <?php echo $grafico_json; ?>;
	</script>
	<script src="./js/chart.js"></script>
	<script src="./js/main.js"></script>
</body>

</html>