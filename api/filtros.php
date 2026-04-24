<?php
// api/filtros.php - Filtros por data e comparação de períodos

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';

$response = ['success' => false, 'message' => '', 'data' => null];

$data_inicio = $_GET['data_inicio'] ?? null;
$data_fim = $_GET['data_fim'] ?? null;

if (!$data_inicio || !$data_fim) {
	http_response_code(400);
	$response['message'] = 'data_inicio e data_fim são obrigatórios';
	echo json_encode($response);
	exit;
}

// Validar datas
$data_inicio = DateTime::createFromFormat('Y-m-d', $data_inicio);
$data_fim = DateTime::createFromFormat('Y-m-d', $data_fim);

if (!$data_inicio || !$data_fim) {
	http_response_code(400);
	$response['message'] = 'Formato de data inválido (use YYYY-MM-DD)';
	echo json_encode($response);
	exit;
}

$data_inicio_str = $data_inicio->format('Y-m-d 00:00:00');
$data_fim_str = $data_fim->format('Y-m-d 23:59:59');

// Buscar dados dentro do período
$sql = "SELECT nome, seguidores, DATE(data) as data FROM corrida 
        WHERE ativo = 1 AND data >= ? AND data <= ? 
        ORDER BY nome, data ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $data_inicio_str, $data_fim_str);
$stmt->execute();
$result = $stmt->get_result();

$dados_por_avatar = [];
while ($row = $result->fetch_assoc()) {
	if (!isset($dados_por_avatar[$row['nome']])) {
		$dados_por_avatar[$row['nome']] = [];
	}
	$dados_por_avatar[$row['nome']][] = [
		'data' => $row['data'],
		'seguidores' => $row['seguidores']
	];
}

// Calcular estatísticas
$comparacao = [];
foreach ($dados_por_avatar as $avatar => $dados) {
	if (!empty($dados)) {
		$seguidores_inicio = $dados[0]['seguidores'];
		$seguidores_fim = $dados[count($dados) - 1]['seguidores'];
		$crescimento = $seguidores_fim - $seguidores_inicio;
		$percentual_crescimento = $seguidores_inicio > 0 ? (($crescimento / $seguidores_inicio) * 100) : 0;

		$comparacao[$avatar] = [
			'seguidores_inicio' => $seguidores_inicio,
			'seguidores_fim' => $seguidores_fim,
			'crescimento' => $crescimento,
			'percentual_crescimento' => round($percentual_crescimento, 2),
			'quantidade_atualizacoes' => count($dados)
		];
	}
}

$response['success'] = true;
$response['data'] = [
	'periodo' => [
		'inicio' => $data_inicio->format('d/m/Y'),
		'fim' => $data_fim->format('d/m/Y')
	],
	'comparacao' => $comparacao,
	'total_crescimento' => array_sum(array_map(fn($c) => $c['crescimento'], $comparacao))
];

$stmt->close();
$conn->close();
echo json_encode($response);
