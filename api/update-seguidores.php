<?php
header('Content-Type: application/json; charset=utf-8');


require_once '../config/db.php';

$nome = $_POST['nome'];
$seguidores = $_POST['seguidores'];

$hoje = date('Y-m-d H:i:s');
//dd( $hoje );

$sql = "INSERT INTO corrida (
	nome,
	seguidores,
	data
) VALUES (" .
	"'" . $nome . "'," .
	"'" . $seguidores . "'," .
	"'" . $hoje . "'" .
	");";

//echo $sql; exit();

if ($conn->multi_query($sql) === TRUE) {
	echo 'Item CRIADO com sucesso: ' . $nome . ' - ' . $seguidores;
} else {
	echo 'Erro: ' . $sql . ' - ' . $conn->error;
}

$conn->close();
