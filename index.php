<?php
require_once __DIR__ . '/config/db.php';

// Meta de seguidores
$META_SEGUIDORES = 2000;

// Contar o total de registros por avatar
$sql_count_reps = "SELECT nome, COUNT(*) as repeticoes 
FROM corrida WHERE ativo = 1
GROUP BY nome";
$result_reps = $conn->query($sql_count_reps);

$repeticoes_por_nome = [];

while ($row_rep = $result_reps->fetch_assoc()) {
	$repeticoes_por_nome[$row_rep['nome']] = $row_rep['repeticoes'];
}

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
$repeticoes_primeiro = null;

while ($row = $result->fetch_assoc()) {
	$row['posicao'] = $posicao;
	$row['percentual'] = ($row['seguidores'] / $META_SEGUIDORES) * 100;
	$repeticoes_avatar = isset($repeticoes_por_nome[$row['nome']]) ? $repeticoes_por_nome[$row['nome']] : 0;

	// Armazenar as repetições do primeiro colocado
	if ($posicao === 1) {
		$repeticoes_primeiro = $repeticoes_avatar;
	}

	$row['desatualizado'] = ($repeticoes_avatar !== $repeticoes_primeiro) ? true : false;
	$avatares[] = $row;
	$total_seguidores += $row['seguidores'];
	$posicao++;
}

$media_seguidores = count($avatares) > 0 ? intval($total_seguidores / count($avatares)) : 0;
$progresso_total = count($avatares) > 0 ? ($total_seguidores / ($META_SEGUIDORES * count($avatares))) * 100 : 0;

// Buscar data da última modificação
$sql_data = "SELECT MAX(updated_at) as ultima_atualizacao FROM corrida WHERE ativo = 1";
$result_data = $conn->query($sql_data);
$row_data = $result_data->fetch_assoc();
$ultima_atualizacao = $row_data['ultima_atualizacao'];
$data_formatada = $ultima_atualizacao ? date('d/m/Y H:i:s', strtotime($ultima_atualizacao)) : 'Sem registros';

// Buscar todos os registros em ordem de insert (por ID)
$sql_registros = "SELECT id, nome, seguidores, data FROM corrida WHERE ativo = 1 ORDER BY id ASC";
$result_registros = $conn->query($sql_registros);

if (!$result_registros) {
	die('Erro na consulta: ' . $conn->error);
}

// Preparar dados agrupados por avatar, mantendo ordem de aparição
$grafico_dados = [];
$labels_datas = []; // Labels com datas

$avatares_ordem = [];
while ($row = $result_registros->fetch_assoc()) {
	$nome = $row['nome'];
	$seguidores = $row['seguidores'];
	$data = $row['data'];

	// Inicializar array do avatar se não existir
	if (!isset($grafico_dados[$nome])) {
		$grafico_dados[$nome] = [];
		$avatares_ordem[] = $nome;
	}

	// Adicionar dados
	$grafico_dados[$nome][] = [
		'data' => $data,
		'seguidores' => $seguidores
	];

	// Adicionar data ao labels se não existir
	if (!in_array($data, $labels_datas)) {
		$labels_datas[] = $data;
	}
}

$grafico_json = json_encode(['datas' => $labels_datas, 'avatares' => $grafico_dados]);

$conn->close();

$perfil_ana = 'https://www.tiktok.com/@ana.lindinha1';
$perfil_bia = 'https://www.tiktok.com/@bia.ttshop';
$perfil_megg = 'https://www.tiktok.com/@megg.shop';
$perfil_luna = 'https://www.tiktok.com/@luna.shop10';
$perfil_mel = 'https://www.tiktok.com/@mel329647';

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Corrida de Avatares - Dashboard</title>
	<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
</head>

<body>
	<style>
		<?php
		require 'css/style.css';
		require 'css/filtros.css';
		?>
	</style>

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

		<!-- Painel de Filtros -->
		<div class="filtro-section">
			<h2>🔍 Filtros e Comparação</h2>
			<div class="filtro-header">
				<div class="filtro-group">
					<label for="data-inicio">Data de Início</label>
					<input type="date" id="data-inicio" name="data-inicio">
				</div>
				<div class="filtro-group">
					<label for="data-fim">Data de Fim</label>
					<input type="date" id="data-fim" name="data-fim">
				</div>
				<div class="filtro-buttons">
					<button class="btn-filtrar" id="btn-filtrar">🔎 Filtrar</button>
					<button class="btn-reset-filtro" id="btn-reset-filtro">↻ Limpar</button>
				</div>
			</div>
			<div id="filtro-resultados"></div>
		</div>

		<!-- Tabela de Corrida -->
		<div class="table-section">
			<div class="table-header">
				<h2>📊 Ranking de Pedreiros</h2>
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
						<th>Atualizações</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($avatares as $avatar): ?>
						<tr data-id="<?php echo $avatar['id']; ?>" data-nome="<?php echo htmlspecialchars($avatar['nome']); ?>" <?php echo $avatar['desatualizado'] ? 'class="desatualizado"' : ''; ?>>
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
									<a
										target="_blank"
										href="<?php
												$nome = $avatar['nome'];
												echo ($nome === 'Ana') ? $perfil_ana : (($nome === 'Bia') ? $perfil_bia : (($nome === 'Megg') ? $perfil_megg : (($nome === 'Luna') ? $perfil_luna : (($nome === 'Mel') ? $perfil_mel : '#'))));
												?>">
										<?php echo htmlspecialchars($avatar['nome']); ?>
									</a>
								</div>
							</td>
							<td>
								<input
									type="number"
									class="seguidores-input"
									value="<?php echo $avatar['seguidores']; ?>"
									min="0"
									placeholder="0"
									onkeypress="return false">
								<button class="btn-gravar" onclick="gravarSeguidores(this)">Gravar</button>
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
							<td style="text-align: center;">
								<span class="update-count"><?php echo $repeticoes_por_nome[$avatar['nome']] ?? 0; ?></span>
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
	<script src="./js/filtros.js"></script>
	<script src="./js/achievements.js"></script>
	<script src="./js/main.js"></script>
</body>

</html>