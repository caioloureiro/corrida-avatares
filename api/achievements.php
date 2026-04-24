<?php
// api/achievements.php - Sistema de badges/achievements

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';

$response = ['success' => false, 'data' => []];

// Badges disponíveis
$badges_config = [
	'meta_atingida' => [
		'nome' => 'Meta Atingida',
		'descricao' => 'Atingiu 2.000 seguidores',
		'icone' => '🏆',
		'condicao' => function ($seguidores) {
			return $seguidores >= 2000;
		}
	],
	'lider' => [
		'nome' => 'Líder',
		'descricao' => 'Maior número de seguidores',
		'icone' => '👑',
		'condicao' => null // calculado especialmente
	],
	'crescimento_rapido' => [
		'nome' => 'Crescimento Rápido',
		'descricao' => '100+ seguidores em um dia',
		'icone' => '🚀',
		'condicao' => null // calculado especialmente
	],
	'consistente' => [
		'nome' => 'Consistente',
		'descricao' => 'Atualizado todos os dias',
		'icone' => '📈',
		'condicao' => null // calculado especialmente
	]
];

// Buscar dados dos avatares
$sql = "SELECT id, nome, seguidores FROM corrida WHERE ativo = 1 ORDER BY seguidores DESC";
$result = $conn->query($sql);

$max_seguidores = 0;
$avatares_dados = [];

while ($row = $result->fetch_assoc()) {
	$avatares_dados[$row['id']] = $row;
	if ($row['seguidores'] > $max_seguidores) {
		$max_seguidores = $row['seguidores'];
	}
}

// Calcular badges para cada avatar
foreach ($avatares_dados as $id => $avatar) {
	$badges = [];

	// Badge: Meta Atingida
	if ($avatar['seguidores'] >= 2000) {
		$badges[] = array_merge(
			['id' => 'meta_atingida'],
			array_filter($badges_config['meta_atingida'], fn($k) => $k !== 'condicao', ARRAY_FILTER_USE_KEY)
		);
	}

	// Badge: Líder
	if ($avatar['seguidores'] === $max_seguidores && $max_seguidores > 0) {
		$badges[] = array_merge(
			['id' => 'lider'],
			array_filter($badges_config['lider'], fn($k) => $k !== 'condicao', ARRAY_FILTER_USE_KEY)
		);
	}

	// Badge: Crescimento Rápido (últimos 2 registros)
	$sql_crescimento = "SELECT seguidores FROM corrida WHERE id = ? ORDER BY data DESC LIMIT 2";
	$stmt = $conn->prepare($sql_crescimento);
	$stmt->bind_param("i", $id);
	$stmt->execute();
	$res = $stmt->get_result();

	$valores = [];
	while ($row = $res->fetch_assoc()) {
		$valores[] = $row['seguidores'];
	}
	$stmt->close();

	if (count($valores) === 2 && ($valores[0] - $valores[1]) >= 100) {
		$badges[] = array_merge(
			['id' => 'crescimento_rapido'],
			array_filter($badges_config['crescimento_rapido'], fn($k) => $k !== 'condicao', ARRAY_FILTER_USE_KEY)
		);
	}

	$response['data'][$avatar['nome']] = [
		'id' => $id,
		'seguidores' => $avatar['seguidores'],
		'badges' => $badges,
		'total_badges' => count($badges)
	];
}

$response['success'] = true;
$conn->close();
echo json_encode($response);
