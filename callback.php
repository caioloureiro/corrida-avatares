<?php
/**
 * Callback do OAuth TikTok
 * Encaminha para /corrida-avatares/api/tiktok-oauth.php?action=callback
 */

require_once __DIR__ . '/config/tiktok.php';

$code = $_GET['code'] ?? null;
$state = $_GET['state'] ?? null;

if (!$code) {
    die('Erro: Código de autorização não recebido do TikTok.');
}

// Fazer requisição para processar o callback
$ch = curl_init();
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['SERVER_PORT'] ?? '') === '443');
$scheme = $isHttps ? 'https' : 'http';

$callbackUrl = $scheme . '://' . $host . APP_BASE_PATH
    . '/api/tiktok-oauth.php?action=callback&code=' . urlencode($code)
    . '&state=' . urlencode($state);

curl_setopt($ch, CURLOPT_URL, $callbackUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    die("Erro ao processar callback: " . $error);
}

$data = json_decode($response, true);

if ($data['success']) {
    echo "<div style='font-family: Arial, sans-serif; padding: 20px; text-align: center;'>";
    echo "<h1 style='color: #00f7ef;'>✅ Autenticação Bem-sucedida!</h1>";
    echo "<p>Perfil: <strong>" . htmlspecialchars($data['data']['avatar']) . "</strong></p>";
    echo "<p>Usuário TikTok: <strong>" . htmlspecialchars($data['data']['tiktok_user']) . "</strong></p>";
    echo "<p>Open ID: <code>" . htmlspecialchars($data['data']['open_id']) . "</code></p>";
    echo "<p><a href='" . APP_BASE_PATH . "/' style='background: #00f7ef; color: #000; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Voltar ao Dashboard</a></p>";
    echo "</div>";
} else {
    echo "<div style='font-family: Arial, sans-serif; padding: 20px; text-align: center;'>";
    echo "<h1 style='color: #ff4757;'>❌ Erro na Autenticação</h1>";
    echo "<p>" . htmlspecialchars($data['message']) . "</p>";
    echo "<p><a href='" . APP_BASE_PATH . "/' style='background: #ff4757; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Voltar ao Dashboard</a></p>";
    echo "</div>";
}
?>
