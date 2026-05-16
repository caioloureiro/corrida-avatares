<?php
// Configuracoes do TikTok API

// Credenciais do app no TikTok Developer
define('TIKTOK_CLIENT_KEY', 'awkzlre7h5dsvez7');
define('TIKTOK_CLIENT_SECRET', 'zsZYw5Dnj0DCSGmu3lkdUbWwDB1qBSyG');

// Projeto publicado em subpasta
define('APP_BASE_PATH', '/corrida-avatares');

// Detecta host/scheme da requisicao atual
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['SERVER_PORT'] ?? '') === '443');

// Em dominio real, forcar https para evitar mismatch no OAuth
$isLocalHost = (stripos($host, 'localhost') !== false || stripos($host, '127.0.0.1') !== false);
$scheme = ($isHttps || !$isLocalHost) ? 'https' : 'http';

// Redirect URI DEVE ser exatamente igual ao cadastrado no TikTok Developer Console
define('TIKTOK_REDIRECT_URI', $scheme . '://' . $host . APP_BASE_PATH . '/callback.php');

// Endpoints da API
define('TIKTOK_AUTH_URL', 'https://www.tiktok.com/v2/auth/authorize/');
define('TIKTOK_TOKEN_URL', 'https://open.tiktokapis.com/v2/oauth/token/');
define('TIKTOK_USER_INFO_URL', 'https://open.tiktokapis.com/v2/user/info/');

// Escopos necessarios
define('TIKTOK_SCOPES', 'user.info.basic,user.info.stats');

// Cache de tokens (em segundos)
define('TOKEN_CACHE_DURATION', 3600);

// Estado aleatorio para seguranca CSRF
function generateCsrfState() {
    return bin2hex(random_bytes(32));
}

