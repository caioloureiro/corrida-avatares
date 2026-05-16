<?php
// Configurações do TikTok API

// Credenciais do seu app no TikTok Developer
define('TIKTOK_CLIENT_KEY', 'awkzlre7h5dsvez7');
define('TIKTOK_CLIENT_SECRET', 'zsZYw5Dnj0DCSGmu3lkdUbWwDB1qBSyG');

// ⚠️ CONFIGURAÇÃO PARA WAMP64 COM LOCALHOST
// Detecta se está em localhost e ajusta a URL de redirect
$scheme = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$scriptPath = '/corrida-avatares';  // Ajuste se necessário

// Para WAMP com localhost, a URL será: http://localhost/corrida-avatares/callback.php
define('TIKTOK_REDIRECT_URI', $scheme . '://' . $host . $scriptPath . '/callback.php');

// Debug: Mostrar redirect URI
error_log("=== TikTok OAuth Debug ===");
error_log("Redirect URI: " . TIKTOK_REDIRECT_URI);
error_log("Client Key: " . TIKTOK_CLIENT_KEY);

// Endpoints da API
define('TIKTOK_AUTH_URL', 'https://www.tiktok.com/v2/auth/authorize/');
define('TIKTOK_TOKEN_URL', 'https://open.tiktokapis.com/v2/oauth/token/');
define('TIKTOK_USER_INFO_URL', 'https://open.tiktokapis.com/v2/user/info/');

// Escopos necessários
define('TIKTOK_SCOPES', 'user.info.basic,user.info.stats');

// Cache de tokens (em segundos)
define('TOKEN_CACHE_DURATION', 3600); // 1 hora

// Estado aleatório para segurança CSRF
function generateCsrfState() {
    return bin2hex(random_bytes(32));
}
?>
