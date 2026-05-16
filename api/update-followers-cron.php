<?php
/**
 * update-followers.php
 * Script para atualizar seguidores automaticamente via cron
 * 
 * Uso:
 * php /caminho/para/update-followers.php
 * 
 * Ou via cron (a cada 15 minutos):
 * * /15 * * * * php /caminho/para/update-followers.php
 */

// Permitir execução via CLI
if (php_sapi_name() !== 'cli' && php_sapi_name() !== 'cli-server') {
    // Se for HTTP, validar token simples
    $token = $_GET['token'] ?? $_POST['token'] ?? null;
    if ($token !== 'seu_token_secreto_aqui') {
        http_response_code(403);
        die('Acesso negado');
    }
}

require_once __DIR__ . '/../config/db.php';

// Log file
$logFile = __DIR__ . '/logs/followers-update.log';
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

function log_message($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    if (php_sapi_name() === 'cli') {
        echo $logMessage;
    }
}

log_message("=== Iniciando atualização de seguidores ===");

try {
    // Buscar avatares com autenticação válida
    $sql = "SELECT avatar_nome, access_token, access_token_expires_at 
            FROM perfis_tiktok 
            WHERE access_token IS NOT NULL 
            AND access_token_expires_at > NOW()
            ORDER BY avatar_nome";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Erro na query: " . $conn->error);
    }
    
    $successCount = 0;
    $errorCount = 0;
    
    while ($perfil = $result->fetch_assoc()) {
        $avatar = $perfil['avatar_nome'];
        $token = $perfil['access_token'];
        
        // Buscar dados do TikTok
        $userData = fetchTikTokUserInfo($token);
        
        if (!$userData || !isset($userData['follower_count'])) {
            log_message("❌ Erro ao buscar dados de $avatar");
            $errorCount++;
            continue;
        }
        
        $tiktokSeguidores = intval($userData['follower_count']);

        // Preservar valor manual do input para manter comparação com TikTok
        $seguidores = $tiktokSeguidores;
        $stmtLast = $conn->prepare("SELECT seguidores FROM corrida WHERE ativo = 1 AND nome = ? ORDER BY id DESC LIMIT 1");
        if ($stmtLast) {
            $stmtLast->bind_param("s", $avatar);
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
        
        // Detectar se está ao vivo
        $aoVivo = 0;
        if (isset($userData['is_live'])) {
            $aoVivo = $userData['is_live'] ? 1 : 0;
        } elseif (isset($userData['live_status'])) {
            $aoVivo = intval($userData['live_status']);
        }
        $agora = date('Y-m-d H:i:s');
        
        // Salvar novo registro
        $stmt = $conn->prepare("\n            INSERT INTO corrida (ativo, nome, seguidores, tiktok_seguidores, ao_vivo, data, created_at, updated_at)\n            VALUES (1, ?, ?, ?, ?, ?, ?, ?)\n        ");
        
        if (!$stmt) {
            log_message("❌ Erro ao preparar statement: " . $conn->error);
            $errorCount++;
            continue;
        }
        
        $stmt->bind_param("siiisss", $avatar, $seguidores, $tiktokSeguidores, $aoVivo, $agora, $agora, $agora);
        
        if ($stmt->execute()) {
            log_message("✅ $avatar: $seguidores seguidores (Ao vivo: " . ($aoVivo ? "Sim" : "Não") . ")");
            $successCount++;
        } else {
            log_message("❌ Erro ao salvar $avatar: " . $stmt->error);
            $errorCount++;
        }
        
        $stmt->close();
    }
    
    log_message("=== Atualização concluída ===");
    log_message("Sucessos: $successCount | Erros: $errorCount");
    
    // Se for HTTP, retornar JSON
    if (php_sapi_name() !== 'cli') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Atualização concluída',
            'sucessos' => $successCount,
            'erros' => $errorCount
        ]);
    }

} catch (Exception $e) {
    $errorMsg = "Erro: " . $e->getMessage();
    log_message($errorMsg);
    
    if (php_sapi_name() !== 'cli') {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $errorMsg
        ]);
    }
}

$conn->close();

/**
 * Buscar informações do usuário do TikTok
 */
function fetchTikTokUserInfo($accessToken) {
    $ch = curl_init();
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
    
    if (isset($data['data']['user'])) {
        return $data['data']['user'];
    }
    
    return null;
}
?>
