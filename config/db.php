<?php
// Conexão com o banco de dados
$host = '';
$db_name = '';
$user = '';
$password = '';

if( 
    $_SERVER['HTTP_HOST'] == 'localhost'
    || $_SERVER['HTTP_HOST'] == '192.168.0.2'
){

	$host = 'localhost';
	$db_name = 'airbr_flow';
	$user = 'root';
	$password = 'caio1234';

}else{
	
	$host = "localhost";
	$user = "digi8353_caio";
	$password = "cvvnp0ic";
	$db_name = "digi8353_airbr_flow";
	
}

try {
	$conn = new mysqli($host, $user, $password, $db_name);

	if ($conn->connect_error) {
		throw new Exception('Erro na conexão: ' . $conn->connect_error);
	}

	$conn->set_charset("utf8mb4");
} catch (Exception $e) {
	die('Erro de conexão: ' . $e->getMessage());
}
