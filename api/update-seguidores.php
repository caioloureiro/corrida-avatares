<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';

$nome = trim($_POST['nome'] ?? '');
$seguidores = isset($_POST['seguidores']) ? intval($_POST['seguidores']) : null;

if ($nome === '' || $seguidores === null || $seguidores < 0) {
	http_response_code(400);
	echo json_encode([
		'success' => false,
		'message' => 'Parâmetros inválidos'
	]);
	exit;
}

$agora = date('Y-m-d H:i:s');
$tiktokSeguidores = 0;
$aoVivo = 0;

$hasTikTokSeguidores = false;
$hasAoVivo = false;

$checkTikTokCol = $conn->query("SHOW COLUMNS FROM corrida LIKE 'tiktok_seguidores'");
if ($checkTikTokCol && $checkTikTokCol->num_rows > 0) {
	$hasTikTokSeguidores = true;
}

$checkAoVivoCol = $conn->query("SHOW COLUMNS FROM corrida LIKE 'ao_vivo'");
if ($checkAoVivoCol && $checkAoVivoCol->num_rows > 0) {
	$hasAoVivo = true;
}

// Preserva último valor vindo do TikTok para não "contaminar" a coluna de comparação.
$selectFields = [];
if ($hasTikTokSeguidores) {
	$selectFields[] = 'tiktok_seguidores';
}
if ($hasAoVivo) {
	$selectFields[] = 'ao_vivo';
}

$stmtLast = null;
if (!empty($selectFields)) {
	$stmtLast = $conn->prepare(
		"SELECT " . implode(', ', $selectFields) . " FROM corrida WHERE ativo = 1 AND nome = ? ORDER BY id DESC LIMIT 1"
	);
}
if ($stmtLast) {
	$stmtLast->bind_param('s', $nome);
	$stmtLast->execute();
	$resLast = $stmtLast->get_result();
	if ($resLast && $resLast->num_rows > 0) {
		$last = $resLast->fetch_assoc();
		if (isset($last['tiktok_seguidores']) && $last['tiktok_seguidores'] !== null) {
			$tiktokSeguidores = intval($last['tiktok_seguidores']);
		}
		if (isset($last['ao_vivo']) && $last['ao_vivo'] !== null) {
			$aoVivo = intval($last['ao_vivo']);
		}
	}
	$stmtLast->close();
}

$stmtInsert = null;

if ($hasTikTokSeguidores && $hasAoVivo) {
	$stmtInsert = $conn->prepare(
		"INSERT INTO corrida (ativo, nome, seguidores, tiktok_seguidores, ao_vivo, data, created_at, updated_at)
		 VALUES (1, ?, ?, ?, ?, ?, ?, ?)"
	);
} elseif ($hasTikTokSeguidores) {
	$stmtInsert = $conn->prepare(
		"INSERT INTO corrida (ativo, nome, seguidores, tiktok_seguidores, data, created_at, updated_at)
		 VALUES (1, ?, ?, ?, ?, ?, ?)"
	);
} elseif ($hasAoVivo) {
	$stmtInsert = $conn->prepare(
		"INSERT INTO corrida (ativo, nome, seguidores, ao_vivo, data, created_at, updated_at)
		 VALUES (1, ?, ?, ?, ?, ?, ?)"
	);
} else {
	$stmtInsert = $conn->prepare(
		"INSERT INTO corrida (ativo, nome, seguidores, data, created_at, updated_at)
		 VALUES (1, ?, ?, ?, ?, ?)"
	);
}

if (!$stmtInsert) {
	http_response_code(500);
	echo json_encode([
		'success' => false,
		'message' => 'Erro ao preparar insert: ' . $conn->error
	]);
	$conn->close();
	exit;
}
if ($hasTikTokSeguidores && $hasAoVivo) {
	$stmtInsert->bind_param('siiisss', $nome, $seguidores, $tiktokSeguidores, $aoVivo, $agora, $agora, $agora);
} elseif ($hasTikTokSeguidores) {
	$stmtInsert->bind_param('siisss', $nome, $seguidores, $tiktokSeguidores, $agora, $agora, $agora);
} elseif ($hasAoVivo) {
	$stmtInsert->bind_param('siisss', $nome, $seguidores, $aoVivo, $agora, $agora, $agora);
} else {
	$stmtInsert->bind_param('sisss', $nome, $seguidores, $agora, $agora, $agora);
}

if ($stmtInsert->execute()) {
	echo json_encode([
		'success' => true,
		'message' => 'Seguidores atualizados com sucesso',
		'data' => [
			'nome' => $nome,
			'seguidores' => $seguidores,
			'tiktok_seguidores' => $tiktokSeguidores,
			'ao_vivo' => $aoVivo,
			'atualizado_em' => $agora
		]
	]);
} else {
	http_response_code(500);
	echo json_encode([
		'success' => false,
		'message' => 'Erro ao salvar: ' . $stmtInsert->error
	]);
}

$stmtInsert->close();
$conn->close();
