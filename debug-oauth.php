<?php
/**
 * Debugar configurações OAuth para WAMP64
 */
require_once __DIR__ . '/config/tiktok.php';

$scheme = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$scriptPath = '/corrida-avatares';

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Debug OAuth TikTok - WAMP64</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .box { background: white; padding: 20px; margin: 15px 0; border-radius: 5px; border-left: 5px solid #0066cc; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .error { border-left-color: #cc0000; }
        .warning { border-left-color: #ff9900; }
        .success { border-left-color: #00cc00; }
        code { background: #e0e0e0; padding: 3px 7px; border-radius: 3px; font-family: 'Courier New', monospace; }
        strong { color: #333; }
        .copy-btn { padding: 5px 10px; background: #0066cc; color: white; border: none; border-radius: 3px; cursor: pointer; margin-left: 10px; }
        .copy-btn:hover { background: #0052a3; }
    </style>
</head>
<body>
    <h1>🔧 Debug OAuth TikTok - WAMP64</h1>";

echo "<div class='box warning'>
    <h3>⚠️ AVISO IMPORTANTE</h3>
    <p>TikTok <strong>não aceita</strong> URLs locais com HTTP simples como <code>http://localhost</code>.</p>
    <p>Você tem 3 opções:</p>
    <ol>
        <li><strong>Usar ngrok</strong> (recomendado para testes): Expõe seu localhost para internet com HTTPS</li>
        <li><strong>HTTPS local</strong>: Configurar certificado SSL no WAMP</li>
        <li><strong>Domínio real</strong>: Usar um domínio real registrado</li>
    </ol>
</div>";

echo "<div class='box'>
    <strong>Configuração Detectada:</strong><br><br>
    Scheme: <code>$scheme</code><br>
    Host: <code>$host</code><br>
    Script Path: <code>$scriptPath</code><br><br>
    <strong>Redirect URI Construída:</strong><br>
    <code style='display: block; margin-top: 10px; padding: 10px; background: #f0f0f0;'>" . TIKTOK_REDIRECT_URI . "</code>
</div>";

echo "<div class='box success'>
    <h3>📋 O que fazer:</h3>
    <p><strong>1. Verifique no TikTok Developer Console:</strong></p>
    <p>Qual é a URL de Redirect registrada? Compartilhe comigo.</p><br>
    
    <p><strong>2. Se não está registrada, registre esta URL:</strong></p>
    <code style='display: block; margin: 10px 0; padding: 10px; background: #f0f0f0; word-wrap: break-word;'>" . TIKTOK_REDIRECT_URI . "</code><br>
    
    <p><strong>3. Se quer usar NGROK (solução rápida):</strong></p>
    <pre style='background: #f0f0f0; padding: 10px; overflow-x: auto;'>
1. Baixe ngrok: https://ngrok.com/download
2. Coloque na pasta do projeto (ou em PATH)
3. Execute: ngrok http 80
4. Você verá uma URL como: https://abc123.ngrok.io
5. Registre no TikTok Console: https://abc123.ngrok.io/corrida-avatares/callback.php
6. Acesse: https://abc123.ngrok.io/corrida-avatares/admin-tiktok.php
    </pre>
</div>";

echo "<div class='box'>
    <strong>Client Key:</strong> <code>" . TIKTOK_CLIENT_KEY . "</code><br>
    <strong>Auth URL:</strong> <code>" . TIKTOK_AUTH_URL . "</code>
</div>";

echo "</body>
</html>";
?>

