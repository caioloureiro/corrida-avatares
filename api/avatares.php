<?php
// api/avatares.php - API REST completa para CRUD de avatares

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

$response = ['success' => false, 'message' => '', 'data' => null];

// GET - Listar avatares
if ($method === 'GET') {
	$sql = "SELECT * FROM corrida WHERE ativo = 1 ORDER BY seguidores DESC";
	$result = $conn->query($sql);

	if ($result) {
		$avatares = [];
		while ($row = $result->fetch_assoc()) {
			$row['percentual'] = ($row['seguidores'] / 2000) * 100;
			$row['meta_atingida'] = $row['seguidores'] >= 2000;
			$avatares[] = $row;
		}
		$response['success'] = true;
		$response['data'] = $avatares;
	}
}

// POST - Criar novo avatar
else if ($method === 'POST' && isset($input['nome']) && !isset($input['id'])) {
	$nome = trim($input['nome']);
	$seguidores = intval($input['seguidores'] ?? 0);

	if (empty($nome)) {
		http_response_code(400);
		$response['message'] = 'Nome é obrigatório';
	} else if ($seguidores < 0) {
		http_response_code(400);
		$response['message'] = 'Seguidores não pode ser negativo';
	} else {
		$stmt = $conn->prepare("INSERT INTO corrida (ativo, nome, seguidores, created_at, updated_at, data) VALUES (1, ?, ?, NOW(), NOW(), NOW())");
		$stmt->bind_param("si", $nome, $seguidores);

		if ($stmt->execute()) {
			$response['success'] = true;
			$response['message'] = 'Avatar criado com sucesso';
			$response['data'] = ['id' => $stmt->insert_id, 'nome' => $nome, 'seguidores' => $seguidores];
		} else {
			http_response_code(500);
			$response['message'] = 'Erro ao criar: ' . $stmt->error;
		}
		$stmt->close();
	}
}

// PUT - Atualizar avatar
else if ($method === 'POST' && isset($input['id'])) {
	$id = intval($input['id']);
	$seguidores = isset($input['seguidores']) ? intval($input['seguidores']) : null;
	$nome = isset($input['nome']) ? trim($input['nome']) : null;

	if ($seguidores !== null && $seguidores < 0) {
		http_response_code(400);
		$response['message'] = 'Seguidores não pode ser negativo';
	} else {
		$updates = [];
		$params = [];
		$types = "";

		if ($seguidores !== null) {
			$updates[] = "seguidores = ?";
			$params[] = $seguidores;
			$types .= "i";
		}

		if ($nome !== null && !empty($nome)) {
			$updates[] = "nome = ?";
			$params[] = $nome;
			$types .= "s";
		}

		$updates[] = "updated_at = NOW()";
		$params[] = $id;
		$types .= "i";

		$sql = "UPDATE corrida SET " . implode(", ", $updates) . " WHERE id = ?";
		$stmt = $conn->prepare($sql);

		if ($stmt) {
			$stmt->bind_param($types, ...$params);

			if ($stmt->execute()) {
				$response['success'] = true;
				$response['message'] = 'Avatar atualizado com sucesso';
			} else {
				http_response_code(500);
				$response['message'] = 'Erro ao atualizar: ' . $stmt->error;
			}
			$stmt->close();
		} else {
			http_response_code(500);
			$response['message'] = 'Erro na preparação da consulta: ' . $conn->error;
		}
	}
}

// DELETE - Desativar avatar
else if ($method === 'DELETE' && isset($input['id'])) {
	$id = intval($input['id']);

	$stmt = $conn->prepare("UPDATE corrida SET ativo = 0, updated_at = NOW() WHERE id = ?");
	$stmt->bind_param("i", $id);

	if ($stmt->execute()) {
		$response['success'] = true;
		$response['message'] = 'Avatar deletado com sucesso';
	} else {
		http_response_code(500);
		$response['message'] = 'Erro ao deletar: ' . $stmt->error;
	}
	$stmt->close();
}

// Método não permitido
else {
	http_response_code(405);
	$response['message'] = 'Método não permitido';
}

$conn->close();
echo json_encode($response);
