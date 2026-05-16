<?php
/**
 * health-check.php
 * Verificar se tudo está configurado corretamente
 */

require_once __DIR__ . '/config/db.php';

$checks = [
    'database' => false,
    'corrida_table' => false,
    'ao_vivo_column' => false,
    'perfis_tiktok_table' => false,
    'config_tiktok' => false,
    'tiktok_credentials' => false,
    'api_files' => false,
];

// 1. Verificar conexão BD
try {
    if ($conn && $conn->connect_error === null) {
        $checks['database'] = true;
    }
} catch (Exception $e) {
    // Erro de conexão
}

if ($checks['database']) {
    // 2. Verificar tabela corrida
    $result = $conn->query("SHOW TABLES LIKE 'corrida'");
    $checks['corrida_table'] = ($result && $result->num_rows > 0);

    // 3. Verificar coluna ao_vivo
    $result = $conn->query("SHOW COLUMNS FROM corrida LIKE 'ao_vivo'");
    $checks['ao_vivo_column'] = ($result && $result->num_rows > 0);

    // 4. Verificar tabela perfis_tiktok
    $result = $conn->query("SHOW TABLES LIKE 'perfis_tiktok'");
    $checks['perfis_tiktok_table'] = ($result && $result->num_rows > 0);
}

// 5. Verificar config TikTok
$checks['config_tiktok'] = file_exists(__DIR__ . '/config/tiktok.php');

// 6. Verificar credenciais
if ($checks['config_tiktok']) {
    require_once __DIR__ . '/config/tiktok.php';
    $checks['tiktok_credentials'] = (defined('TIKTOK_CLIENT_KEY') && defined('TIKTOK_CLIENT_SECRET'));
}

// 7. Verificar arquivos da API
$apiFiles = [
    'api/tiktok-oauth.php',
    'api/fetch-followers.php',
    'api/update-followers-cron.php',
    'callback.php',
    'admin-tiktok.php',
    'js/tiktok-manager.js',
];

$checks['api_files'] = true;
foreach ($apiFiles as $file) {
    if (!file_exists(__DIR__ . '/' . $file)) {
        $checks['api_files'] = false;
        break;
    }
}

// Calcular score
$allChecks = count($checks);
$passedChecks = count(array_filter($checks, fn($v) => $v === true));
$score = ($passedChecks / $allChecks) * 100;

// Preparar resposta
$status = ($passedChecks === $allChecks) ? 'success' : ($passedChecks >= 5 ? 'warning' : 'error');

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Check - TikTok Integration</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #ecf0f1;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 32px;
            color: #00f7ef;
            margin-bottom: 10px;
        }

        .progress-bar {
            background: rgba(0, 247, 239, 0.2);
            border: 1px solid rgba(0, 247, 239, 0.5);
            border-radius: 10px;
            height: 30px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #00f7ef 0%, #00d4d1 100%);
            transition: width 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #000;
        }

        .checks-list {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(0, 247, 239, 0.2);
            border-radius: 10px;
            padding: 20px;
            backdrop-filter: blur(10px);
        }

        .check-item {
            padding: 12px;
            margin-bottom: 8px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(0, 247, 239, 0.05);
            border-left: 3px solid rgba(0, 247, 239, 0.3);
        }

        .check-item.pass {
            border-left-color: #2ed573;
            background: rgba(46, 213, 115, 0.1);
        }

        .check-item.fail {
            border-left-color: #ff4757;
            background: rgba(255, 71, 87, 0.1);
        }

        .check-icon {
            font-size: 20px;
            flex-shrink: 0;
        }

        .check-label {
            flex-grow: 1;
        }

        .check-status {
            font-size: 12px;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 3px;
            flex-shrink: 0;
        }

        .status-pass {
            background: rgba(46, 213, 115, 0.3);
            color: #2ed573;
        }

        .status-fail {
            background: rgba(255, 71, 87, 0.3);
            color: #ff4757;
        }

        .next-steps {
            background: rgba(0, 247, 239, 0.1);
            border-left: 3px solid #00f7ef;
            padding: 15px;
            margin-top: 20px;
            border-radius: 5px;
            font-size: 14px;
            line-height: 1.8;
        }

        .next-steps strong {
            color: #00f7ef;
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            justify-content: center;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #00f7ef;
            color: #000;
        }

        .btn-primary:hover {
            background: #00d4d1;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: rgba(0, 247, 239, 0.2);
            color: #00f7ef;
            border: 1px solid #00f7ef;
        }

        .btn-secondary:hover {
            background: rgba(0, 247, 239, 0.3);
        }

        .json-response {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(0, 247, 239, 0.3);
            border-radius: 5px;
            padding: 15px;
            margin-top: 20px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            color: #00f7ef;
            overflow-x: auto;
            max-height: 300px;
            overflow-y: auto;
        }

        .emoji-large {
            font-size: 48px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="emoji-large">
                <?php if ($passedChecks === $allChecks): ?>
                    ✅
                <?php elseif ($passedChecks >= 5): ?>
                    ⚠️
                <?php else: ?>
                    ❌
                <?php endif; ?>
            </div>
            <h1>Health Check</h1>
            <p>Verificação de instalação - TikTok Integration</p>
        </div>

        <div class="progress-bar">
            <div class="progress-fill" style="width: <?php echo $score; ?>%">
                <?php echo $passedChecks; ?>/<?php echo $allChecks; ?>
            </div>
        </div>

        <div class="checks-list">
            <?php
            $labels = [
                'database' => 'Conexão com Banco de Dados',
                'corrida_table' => 'Tabela corrida existe',
                'ao_vivo_column' => 'Coluna ao_vivo criada',
                'perfis_tiktok_table' => 'Tabela perfis_tiktok existe',
                'config_tiktok' => 'Arquivo config/tiktok.php',
                'tiktok_credentials' => 'Credenciais TikTok configuradas',
                'api_files' => 'Todos os arquivos da API',
            ];

            foreach ($labels as $key => $label):
                $isPass = $checks[$key];
            ?>
                <div class="check-item <?php echo $isPass ? 'pass' : 'fail'; ?>">
                    <div class="check-icon">
                        <?php echo $isPass ? '✅' : '❌'; ?>
                    </div>
                    <div class="check-label"><?php echo $label; ?></div>
                    <div class="check-status <?php echo $isPass ? 'status-pass' : 'status-fail'; ?>">
                        <?php echo $isPass ? 'OK' : 'FALHA'; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($passedChecks === $allChecks): ?>
            <div class="next-steps">
                <strong>✅ Tudo Pronto!</strong><br>
                Acesse <a href="/admin-tiktok.php" style="color: #00f7ef;">admin-tiktok.php</a> para começar a autenticar os avatares.
            </div>
        <?php elseif (!$checks['corrida_table'] || !$checks['ao_vivo_column'] || !$checks['perfis_tiktok_table']): ?>
            <div class="next-steps">
                <strong>⚠️ Banco de Dados Incompleto</strong><br>
                Execute o arquivo de migração:<br>
                <code>mysql -u root -p airbr_flow &lt; model/migration_ao_vivo.sql</code>
            </div>
        <?php elseif (!$checks['api_files']): ?>
            <div class="next-steps">
                <strong>❌ Arquivos Faltando</strong><br>
                Verifique se todos os arquivos foram criados em:<br>
                - api/<br>
                - config/<br>
                - js/
            </div>
        <?php else: ?>
            <div class="next-steps">
                <strong>⚠️ Verificar Configuração</strong><br>
                Alguns itens estão incompletos. Verifique a documentação em TIKTOK_SETUP.md
            </div>
        <?php endif; ?>

        <div class="button-group">
            <button class="btn btn-primary" onclick="location.reload()">🔄 Verificar Novamente</button>
            <a href="/admin-tiktok.php" class="btn btn-secondary">➜ Ir para Admin</a>
        </div>

        <div class="json-response">
            <strong>Response JSON:</strong><br><br>
            <?php echo json_encode([
                'status' => $status,
                'score' => $score . '%',
                'passed' => $passedChecks,
                'total' => $allChecks,
                'checks' => $checks,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); ?>
        </div>
    </div>
</body>
</html>
