<?php
// Conexão com o banco de dados
$host = 'localhost';
$db_name = 'airbr_flow';
$user = 'root';
$password = 'caio1234';

try {
	$conn = new mysqli($host, $user, $password, $db_name);

	if ($conn->connect_error) {
		throw new Exception('Erro na conexão: ' . $conn->connect_error);
	}

	$conn->set_charset("utf8mb4");
} catch (Exception $e) {
	die('Erro de conexão: ' . $e->getMessage());
}
