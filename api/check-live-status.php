<?php
/**
 * API para verificar status "Ao Vivo"
 * GET /api/check-live-status.php
 * 
 * Retorna o status atual de cada avatar (último registro)
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';

$response = ['success' => false, 'message' => '', 'data' => null];

try {
    // Buscar últimos registros de cada avatar
    $sql = "SELECT nome, seguidores, ao_vivo, data 
            FROM corrida 
            WHERE id IN (SELECT MAX(id) FROM corrida WHERE ativo = 1 GROUP BY nome)
            ORDER BY nome";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception('Erro na consulta: ' . $conn->error);
    }
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[$row['nome']] = [
            'seguidores' => intval($row['seguidores']),
            'ao_vivo' => intval($row['ao_vivo']),
            'data' => $row['data']
        ];
    }
    
    $response['success'] = true;
    $response['data'] = $data;

} catch (Exception $e) {
    http_response_code(500);
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
$conn->close();
?>
