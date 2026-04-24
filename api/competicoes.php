<?php
// api/competicoes.php - Sistema de múltiplas competições

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

$response = ['success' => false, 'message' => '', 'data' => null];

// Verificar se tabela de competições existe
$check_table = $conn->query("SHOW TABLES LIKE 'competicoes'");
if ($check_table->num_rows === 0) {
	// Criar tabela se não existir
	$conn->query("CREATE TABLE competicoes (
        id INT PRIMARY KEY AUTO_INCREMENT,
        nome VARCHAR(255) NOT NULL,
        descricao TEXT,
        meta INT DEFAULT 2000,
        ativa INT DEFAULT 1,
        created_at DATETIME,
        updated_at DATETIME
    )");

	// Criar tabela de relacionamento
	$conn->query("CREATE TABLE competicao_avatares (
        id INT PRIMARY KEY AUTO_INCREMENT,
        competicao_id INT,
        avatar_id INT,
        FOREIGN KEY (competicao_id) REFERENCES competicoes(id),
        FOREIGN KEY (avatar_id) REFERENCES corrida(id)
    )");
}

// GET - Listar competições
if ($method === 'GET') {
	$competicao_id = $_GET['competicao_id'] ?? null;

	if ($competicao_id) {
		// Retornar uma competição específica com seus avatares
		$sql = "SELECT c.*, COUNT(ca.avatar_id) as total_avatares FROM competicoes c 
                LEFT JOIN competicao_avatares ca ON c.id = ca.competicao_id 
                WHERE c.id = ? GROUP BY c.id";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("i", $competicao_id);
	} else {
		// Listar todas as competições
		$sql = "SELECT c.*, COUNT(ca.avatar_id) as total_avatares FROM competicoes c 
                LEFT JOIN competicao_avatares ca ON c.id = ca.competicao_id 
                GROUP BY c.id ORDER BY c.ativa DESC, c.created_at DESC";
		$stmt = $conn->prepare($sql);
	}

	$stmt->execute();
	$result = $stmt->get_result();
	$competicoes = $result->fetch_all(MYSQLI_ASSOC);

	$response['success'] = true;
	$response['data'] = $competicoes;
	$stmt->close();
}

// POST - Criar nova competição
else if ($method === 'POST' && isset($input['nome'])) {
	$nome = trim($input['nome']);
	$descricao = $input['descricao'] ?? '';
	$meta = intval($input['meta'] ?? 2000);

	if (empty($nome)) {
		http_response_code(400);
		$response['message'] = 'Nome é obrigatório';
	} else if ($meta <= 0) {
		http_response_code(400);
		$response['message'] = 'Meta deve ser maior que 0';
	} else {
		$stmt = $conn->prepare("INSERT INTO competicoes (nome, descricao, meta, ativa, created_at, updated_at) VALUES (?, ?, ?, 1, NOW(), NOW())");
		$stmt->bind_param("ssi", $nome, $descricao, $meta);

		if ($stmt->execute()) {
			$response['success'] = true;
			$response['message'] = 'Competição criada com sucesso';
			$response['data'] = ['id' => $stmt->insert_id, 'nome' => $nome, 'meta' => $meta];
		} else {
			http_response_code(500);
			$response['message'] = 'Erro ao criar competição: ' . $stmt->error;
		}
		$stmt->close();
	}
}

// PUT - Atualizar competição
else if ($method === 'POST' && isset($input['competicao_id']) && !isset($input['avatar_id'])) {
	$competicao_id = intval($input['competicao_id']);
	$nome = isset($input['nome']) ? trim($input['nome']) : null;
	$descricao = isset($input['descricao']) ? trim($input['descricao']) : null;
	$meta = isset($input['meta']) ? intval($input['meta']) : null;

	$updates = [];
	$params = [];
	$types = "";

	if ($nome !== null) {
		$updates[] = "nome = ?";
		$params[] = $nome;
		$types .= "s";
	}

	if ($descricao !== null) {
		$updates[] = "descricao = ?";
		$params[] = $descricao;
		$types .= "s";
	}

	if ($meta !== null && $meta > 0) {
		$updates[] = "meta = ?";
		$params[] = $meta;
		$types .= "i";
	}

	if (!empty($updates)) {
		$updates[] = "updated_at = NOW()";
		$params[] = $competicao_id;
		$types .= "i";

		$sql = "UPDATE competicoes SET " . implode(", ", $updates) . " WHERE id = ?";
		$stmt = $conn->prepare($sql);

		if ($stmt) {
			$stmt->bind_param($types, ...$params);

			if ($stmt->execute()) {
				$response['success'] = true;
				$response['message'] = 'Competição atualizada com sucesso';
			} else {
				http_response_code(500);
				$response['message'] = 'Erro ao atualizar: ' . $stmt->error;
			}
			$stmt->close();
		}
	}
}

// POST - Adicionar avatar à competição
else if ($method === 'POST' && isset($input['competicao_id']) && isset($input['avatar_id'])) {
	$competicao_id = intval($input['competicao_id']);
	$avatar_id = intval($input['avatar_id']);

	$stmt = $conn->prepare("INSERT INTO competicao_avatares (competicao_id, avatar_id) VALUES (?, ?)");
	$stmt->bind_param("ii", $competicao_id, $avatar_id);

	if ($stmt->execute()) {
		$response['success'] = true;
		$response['message'] = 'Avatar adicionado à competição';
	} else {
		http_response_code(500);
		$response['message'] = 'Erro ao adicionar avatar: ' . $stmt->error;
	}
	$stmt->close();
} else {
	http_response_code(405);
	$response['message'] = 'Método não permitido';
}

$conn->close();
echo json_encode($response);
