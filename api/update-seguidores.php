<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	$response['message'] = 'Método não permitido';
	echo json_encode($response);
	exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['nome']) || !isset($input['seguidores'])) {
	http_response_code(400);
	$response['message'] = 'Dados inválidos';
	echo json_encode($response);
	exit;
}

$nome = $input['nome'];
$seguidores = intval($input['seguidores']);

// Validar se seguidores é um número positivo
if ($seguidores < 0) {
	http_response_code(400);
	$response['message'] = 'Seguidores não pode ser negativo';
	echo json_encode($response);
	exit;
}

// Verificar se já existe um registro idêntico nos últimos 5 segundos para evitar duplicatas
$check_stmt = $conn->prepare("SELECT id FROM corrida WHERE nome = ? AND seguidores = ? AND ativo = 1 AND created_at > DATE_SUB(NOW(), INTERVAL 5 SECOND) LIMIT 1");
$check_stmt->bind_param("si", $nome, $seguidores);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
	// Registro duplicado detectado, retornar sucesso para não quebrar o fluxo
	$response['success'] = true;
	$response['message'] = 'Registro já foi adicionado recentemente (duplicata detectada e ignorada)';
	http_response_code(200);
	$check_stmt->close();
	$conn->close();
	echo json_encode($response);
	exit;
}
$check_stmt->close();

// Inserir novo registro em vez de atualizar
$stmt = $conn->prepare("INSERT INTO corrida (ativo, nome, seguidores, data, created_at, updated_at) VALUES (1, ?, ?, NOW(), NOW(), NOW())");
if (!$stmt) {
	http_response_code(500);
	$response['message'] = 'Erro na preparação da consulta: ' . $conn->error;
	echo json_encode($response);
	exit;
}

$stmt->bind_param("si", $nome, $seguidores);

if ($stmt->execute()) {
	$response['success'] = true;
	$response['message'] = 'Seguidores atualizados com sucesso';
	http_response_code(200);
} else {
	http_response_code(500);
	$response['message'] = 'Erro ao atualizar: ' . $stmt->error;
}

$stmt->close();
$conn->close();

echo json_encode($response);
