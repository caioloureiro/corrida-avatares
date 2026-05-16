<?php
/**
 * API de Autenticação OAuth com TikTok
 * GET /api/tiktok-oauth.php?action=authorize&avatar=Nome
 * GET /api/tiktok-oauth.php?action=callback&code=...
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/tiktok.php';

$response = ['success' => false, 'message' => '', 'data' => null];
$action = $_GET['action'] ?? null;

if ($action === 'authorize') {
    // Gerar link de autorização
    $avatar = $_GET['avatar'] ?? null;
    
    if (!$avatar) {
        http_response_code(400);
        $response['message'] = 'Avatar não especificado';
        echo json_encode($response);
        exit;
    }
    
    // Gerar estado CSRF e armazenar em sessão
    session_start();
    $state = generateCsrfState();
    $_SESSION['tiktok_state'] = $state;
    $_SESSION['tiktok_avatar'] = $avatar;
    
    // Gerar PKCE (Proof Key for Code Exchange)
    $codeVerifier = bin2hex(random_bytes(32)); // 64 caracteres
    $codeChallenge = rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');
    $_SESSION['tiktok_code_verifier'] = $codeVerifier;
    
    // Construir URL de autorização com PKCE
    $authUrl = TIKTOK_AUTH_URL . '?' . http_build_query([
        'client_key' => TIKTOK_CLIENT_KEY,
        'scope' => TIKTOK_SCOPES,
        'response_type' => 'code',
        'redirect_uri' => TIKTOK_REDIRECT_URI,
        'state' => $state,
        'code_challenge' => $codeChallenge,
        'code_challenge_method' => 'S256'
    ]);
    
    $response['success'] = true;
    $response['data'] = [
        'auth_url' => $authUrl,
        'avatar' => $avatar
    ];

} else if ($action === 'callback') {
    // Receber código de autorização
    $code = $_GET['code'] ?? null;
    $state = $_GET['state'] ?? null;
    
    session_start();
    
    // Validar estado CSRF
    if (!isset($_SESSION['tiktok_state']) || $_SESSION['tiktok_state'] !== $state) {
        http_response_code(400);
        $response['message'] = 'Falha na validação de segurança (CSRF)';
        echo json_encode($response);
        exit;
    }
    
    if (!$code) {
        http_response_code(400);
        $response['message'] = 'Código de autorização não recebido';
        echo json_encode($response);
        exit;
    }
    
    $avatar = $_SESSION['tiktok_avatar'] ?? null;
    if (!$avatar) {
        http_response_code(400);
        $response['message'] = 'Avatar não encontrado na sessão';
        echo json_encode($response);
        exit;
    }
    
    // Recuperar code_verifier do PKCE
    $codeVerifier = $_SESSION['tiktok_code_verifier'] ?? null;
    
    // Trocar código por token
    $tokenData = exchangeCodeForToken($code, $codeVerifier);
    
    if (!$tokenData || !isset($tokenData['access_token'])) {
        http_response_code(400);
        $response['message'] = 'Erro ao obter token de acesso';
        echo json_encode($response);
        exit;
    }
    
    // Buscar informações do usuário TikTok
    $userData = fetchTikTokUserInfo($tokenData['access_token']);
    
    if (!$userData) {
        http_response_code(400);
        $response['message'] = 'Erro ao buscar informações do perfil TikTok';
        echo json_encode($response);
        exit;
    }
    
    // Salvar tokens e dados do usuário no banco
    $expiresAt = date('Y-m-d H:i:s', time() + $tokenData['expires_in']);
    $stmt = $conn->prepare("
        UPDATE perfis_tiktok 
        SET tiktok_open_id = ?, 
            tiktok_username = ?,
            access_token = ?, 
            access_token_expires_at = ?,
            refresh_token = ?,
            updated_at = NOW()
        WHERE avatar_nome = ?
    ");
    
    $refreshToken = $tokenData['refresh_token'] ?? null;
    $stmt->bind_param(
        "ssssss",
        $userData['open_id'],
        $userData['display_name'],
        $tokenData['access_token'],
        $expiresAt,
        $refreshToken,
        $avatar
    );
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = "Perfil TikTok de {$avatar} autenticado com sucesso!";
        $response['data'] = [
            'avatar' => $avatar,
            'tiktok_user' => $userData['display_name'],
            'open_id' => $userData['open_id']
        ];
        
        // Limpar sessão
        unset($_SESSION['tiktok_state']);
        unset($_SESSION['tiktok_avatar']);
    } else {
        http_response_code(500);
        $response['message'] = 'Erro ao salvar dados: ' . $stmt->error;
    }
    
    $stmt->close();

} else {
    http_response_code(400);
    $response['message'] = 'Ação não reconhecida';
}

echo json_encode($response);
$conn->close();

/**
 * Trocar código de autorização por token de acesso
 */
function exchangeCodeForToken($code, $codeVerifier = null) {
    $postFields = [
        'client_key' => TIKTOK_CLIENT_KEY,
        'client_secret' => TIKTOK_CLIENT_SECRET,
        'code' => $code,
        'grant_type' => 'authorization_code',
        'redirect_uri' => TIKTOK_REDIRECT_URI
    ];
    
    // Adicionar code_verifier se fornecido (para PKCE)
    if ($codeVerifier) {
        $postFields['code_verifier'] = $codeVerifier;
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, TIKTOK_TOKEN_URL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        error_log("TikTok cURL error: " . $error);
        return null;
    }
    
    return json_decode($response, true);
}

/**
 * Buscar informações do usuário do TikTok
 */
function fetchTikTokUserInfo($accessToken) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, TIKTOK_USER_INFO_URL . '?fields=open_id,display_name,follower_count,avatar_url,video_count,heart_count');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $accessToken",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        error_log("TikTok cURL error: " . $error);
        return null;
    }
    
    $data = json_decode($response, true);
    
    // A API do TikTok retorna a resposta em um formato específico
    if (isset($data['data']['user'])) {
        return $data['data']['user'];
    }
    
    return null;
}
?>
