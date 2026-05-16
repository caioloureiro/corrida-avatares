<?php
/**
 * API para buscar seguidores direto do TikTok
 * POST /api/fetch-followers.php
 * Body: { "avatar": "Ana" }
 * 
 * Busca os dados do TikTok e atualiza a tabela corrida
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/tiktok.php';

$response = ['success' => false, 'message' => '', 'data' => null];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $avatar = $input['avatar'] ?? null;
    $refreshAll = $input['refresh_all'] ?? false;
    
    // Se refresh_all, atualizar todos os 5 avatares
    if ($refreshAll) {
        $avatares = ['Ana', 'Megg', 'Bia', 'Luna', 'Mel'];
    } else {
        if (!$avatar) {
            http_response_code(400);
            $response['message'] = 'Avatar não especificado';
            echo json_encode($response);
            exit;
        }
        $avatares = [$avatar];
    }
    
    $results = [];
    
    foreach ($avatares as $nome) {
        // Buscar token de acesso para o avatar
        $stmt = $conn->prepare("SELECT access_token, access_token_expires_at, tiktok_open_id FROM perfis_tiktok WHERE avatar_nome = ?");
        $stmt->bind_param("s", $nome);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $results[$nome] = [
                'success' => false,
                'message' => 'Perfil TikTok não autenticado'
            ];
            $stmt->close();
            continue;
        }
        
        $perfil = $result->fetch_assoc();
        $stmt->close();
        
        // Verificar se token expirou
        $tokenExpired = strtotime($perfil['access_token_expires_at']) < time();
        
        // Se token expirou, tentar renovar (se houver refresh_token)
        if ($tokenExpired) {
            // Por enquanto, retornar erro - a renovação de token seria implementada depois
            $results[$nome] = [
                'success' => false,
                'message' => 'Token de acesso expirou'
            ];
            continue;
        }
        
        // Buscar dados de seguidores do TikTok
        $userData = fetchTikTokUserInfo($perfil['access_token']);
        
        if (!$userData || !isset($userData['follower_count'])) {
            $results[$nome] = [
                'success' => false,
                'message' => 'Erro ao buscar dados do TikTok'
            ];
            continue;
        }
        
        // Extrair dados
        $tiktokSeguidores = intval($userData['follower_count']);

        // Preservar valor manual do input para permitir comparação com TikTok
        $seguidores = $tiktokSeguidores;
        $stmtLast = $conn->prepare("SELECT seguidores FROM corrida WHERE ativo = 1 AND nome = ? ORDER BY id DESC LIMIT 1");
        if ($stmtLast) {
            $stmtLast->bind_param("s", $nome);
            $stmtLast->execute();
            $lastResult = $stmtLast->get_result();
            if ($lastResult && $lastResult->num_rows > 0) {
                $lastRow = $lastResult->fetch_assoc();
                if (isset($lastRow['seguidores'])) {
                    $seguidores = intval($lastRow['seguidores']);
                }
            }
            $stmtLast->close();
        }
        
        // Tentar detectar se está ao vivo
        // Campos possíveis: is_live, live_status, ou verificar se há livestream ativo
        $aoVivo = 0;
        if (isset($userData['is_live'])) {
            $aoVivo = $userData['is_live'] ? 1 : 0;
        } elseif (isset($userData['live_status'])) {
            $aoVivo = intval($userData['live_status']);
        }
        
        // Salvar novo registro na tabela corrida
        $agora = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("
            INSERT INTO corrida (ativo, nome, seguidores, tiktok_seguidores, ao_vivo, data, created_at, updated_at)
            VALUES (1, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->bind_param("siiisss", $nome, $seguidores, $tiktokSeguidores, $aoVivo, $agora, $agora, $agora);
        
        if ($stmt->execute()) {
            $results[$nome] = [
                'success' => true,
                'message' => "Seguidores atualizados com sucesso",
                'data' => [
                    'nome' => $nome,
                    'seguidores' => $seguidores,
                    'tiktok_seguidores' => $tiktokSeguidores,
                    'ao_vivo' => $aoVivo,
                    'atualizado_em' => $agora
                ]
            ];
        } else {
            $results[$nome] = [
                'success' => false,
                'message' => 'Erro ao salvar no banco: ' . $stmt->error
            ];
        }
        
        $stmt->close();
    }
    
    $response['success'] = true;
    $response['message'] = "Busca de seguidores completada";
    $response['data'] = $results;

} else if ($method === 'GET') {
    // GET - Listar status de autenticação dos avatares
    $sql = "SELECT avatar_nome, tiktok_username, tiktok_open_id, access_token IS NOT NULL as autenticado, access_token_expires_at FROM perfis_tiktok ORDER BY avatar_nome";
    $result = $conn->query($sql);
    
    if ($result) {
        $perfis = [];
        while ($row = $result->fetch_assoc()) {
            $row['token_expirado'] = strtotime($row['access_token_expires_at']) < time();
            $perfis[] = $row;
        }
        $response['success'] = true;
        $response['data'] = $perfis;
    } else {
        http_response_code(500);
        $response['message'] = 'Erro ao consultar banco: ' . $conn->error;
    }

} else {
    http_response_code(405);
    $response['message'] = 'Método não permitido';
}

echo json_encode($response);
$conn->close();

/**
 * Buscar informações do usuário do TikTok
 */
function fetchTikTokUserInfo($accessToken) {
    $ch = curl_init();
    // Requisitar campos incluindo potencial live_status
    curl_setopt($ch, CURLOPT_URL, 'https://open.tiktokapis.com/v2/user/info/?fields=open_id,display_name,follower_count,avatar_url,video_count,heart_count,is_live,bio_description');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $accessToken",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        error_log("TikTok cURL error: " . $error);
        return null;
    }
    
    $data = json_decode($response, true);
    
    if ($httpCode !== 200) {
        error_log("TikTok API error (HTTP {$httpCode}): " . $response);
        return null;
    }
    
    // A API do TikTok retorna a resposta em um formato específico
    if (isset($data['data']['user'])) {
        return $data['data']['user'];
    }
    
    return null;
}
?>
