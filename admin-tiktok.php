<?php
/**
 * Admin - Gerenciar Autenticação TikTok
 * Acesso restrito para configurar as autenticações dos avatares
 */

require_once __DIR__ . '/config/db.php';

// Buscar status de autenticação
$sql = "SELECT avatar_nome, tiktok_username, tiktok_open_id, 
        access_token IS NOT NULL as autenticado, 
        access_token_expires_at,
        DATE_FORMAT(updated_at, '%d/%m/%Y %H:%i:%s') as ultima_atualizacao
        FROM perfis_tiktok ORDER BY avatar_nome";
$result = $conn->query($sql);
$perfis = [];

while ($row = $result->fetch_assoc()) {
    $row['token_expirado'] = !empty($row['access_token_expires_at']) && strtotime($row['access_token_expires_at']) < time();
    $perfis[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gerenciar TikTok</title>
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
            max-width: 1000px;
            margin: 0 auto;
        }

        .header {
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 32px;
            color: #00f7ef;
            margin-bottom: 10px;
        }

        .header p {
            color: #bdc3c7;
            font-size: 14px;
        }

        .section {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(0, 247, 239, 0.2);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            backdrop-filter: blur(10px);
        }

        .section h2 {
            color: #00f7ef;
            margin-bottom: 20px;
            font-size: 20px;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #00f7ef;
            color: #000;
        }

        .btn-primary:hover:not(:disabled) {
            background: #00d4d1;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 247, 239, 0.3);
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .perfis-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
        }

        .perfil-card {
            background: rgba(0, 247, 239, 0.05);
            border: 1px solid rgba(0, 247, 239, 0.3);
            border-radius: 8px;
            padding: 15px;
            transition: all 0.3s;
        }

        .perfil-card:hover {
            background: rgba(0, 247, 239, 0.1);
            border-color: rgba(0, 247, 239, 0.6);
        }

        .perfil-card h3 {
            color: #00f7ef;
            margin-bottom: 10px;
            font-size: 18px;
        }

        .perfil-info {
            font-size: 13px;
            line-height: 1.8;
            margin-bottom: 10px;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
            margin-right: 5px;
        }

        .status-autenticado {
            background: rgba(46, 213, 115, 0.3);
            color: #2ed573;
        }

        .status-nao-autenticado {
            background: rgba(255, 71, 87, 0.3);
            color: #ff4757;
        }

        .status-token-valido {
            background: rgba(0, 247, 239, 0.3);
            color: #00f7ef;
        }

        .status-token-expirado {
            background: rgba(255, 195, 0, 0.3);
            color: #ffc300;
        }

        .perfil-actions {
            display: flex;
            gap: 8px;
            margin-top: 10px;
        }

        .btn-small {
            flex: 1;
            padding: 8px 12px;
            font-size: 12px;
            background: rgba(0, 247, 239, 0.1);
            border: 1px solid rgba(0, 247, 239, 0.4);
            color: #00f7ef;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-small:hover:not(:disabled) {
            background: rgba(0, 247, 239, 0.2);
            border-color: rgba(0, 247, 239, 0.8);
        }

        .success-message {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #2ed573;
            color: #000;
            padding: 15px 20px;
            border-radius: 5px;
            z-index: 10000;
            box-shadow: 0 2px 10px rgba(46, 213, 115, 0.3);
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .info-box {
            background: rgba(0, 247, 239, 0.1);
            border-left: 3px solid #00f7ef;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 14px;
        }

        .info-box strong {
            color: #00f7ef;
        }

        #status-container {
            display: contents;
        }

        .ao-vivo-widget {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            padding: 15px;
        }

        .ao-vivo-stats {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            align-items: center;
        }

        .stat-item {
            flex: 1;
            min-width: 150px;
            text-align: center;
            padding: 15px;
            background: rgba(0, 247, 239, 0.05);
            border: 1px solid rgba(0, 247, 239, 0.2);
            border-radius: 6px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: bold;
            color: #00f7ef;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 12px;
            color: #95a5a6;
            text-transform: uppercase;
        }

        .ao-vivo-item {
            padding: 10px;
            margin: 8px 0;
            background: rgba(239, 68, 68, 0.1);
            border-left: 3px solid #ef4444;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .ao-vivo-item.offline {
            background: rgba(107, 114, 128, 0.1);
            border-left-color: #6b7280;
        }

        @media (max-width: 768px) {
            .perfis-grid {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <!-- Wrapper functions inline para garantir disponibilidade imediata -->
    <script>
        // Instância global do TikTokManager
        let tikTokManager = null;

        // Funções wrapper que podem ser chamadas pelos onclick handlers
        function ensureTikTokManager() {
            if (!tikTokManager && typeof TikTokManager !== 'undefined') {
                try {
                    tikTokManager = new TikTokManager();
                    console.log("✅ TikTokManager criado no wrapper");
                    return true;
                } catch (error) {
                    console.error("❌ Erro ao criar TikTokManager no wrapper:", error);
                    return false;
                }
            }
            return !!tikTokManager;
        }

        function authenticate(avatar) {
            if (!ensureTikTokManager()) {
                console.error("❌ TikTokManager não disponível. Tipo da classe:", typeof TikTokManager);
                alert("Sistema ainda está carregando. Recarregue a página.");
                return;
            }
            console.log("📍 Chamando authenticate para:", avatar);
            tikTokManager.authenticate(avatar);
        }

        function fetchFollowers(avatar) {
            if (!ensureTikTokManager()) {
                alert("Sistema ainda está carregando. Recarregue a página.");
                return;
            }
            console.log("📍 Chamando fetchFollowers para:", avatar);
            tikTokManager.fetchFollowers(avatar);
        }

        function fetchAllFollowers() {
            if (!ensureTikTokManager()) {
                alert("Sistema ainda está carregando. Recarregue a página.");
                return;
            }
            console.log("📍 Chamando fetchAllFollowers");
            tikTokManager.fetchAllFollowers();
        }

        function loadLiveStatus() {
            if (!ensureTikTokManager()) {
                alert("Sistema ainda está carregando. Recarregue a página.");
                return;
            }
            console.log("📍 Chamando loadLiveStatus");
            tikTokManager.loadLiveStatus();
        }

        // Debug: mostrar estado inicial
        console.log("🔧 Wrapper functions definidas. Estado inicial:");
        console.log("   - TikTokManager class:", typeof TikTokManager);
        console.log("   - tikTokManager instance:", tikTokManager);
    </script>
    
    <div class="container">
        <div class="header">
            <h1>🎬 Gerenciar Autenticação TikTok</h1>
            <p>Configure as autenticações dos 5 avatares para buscar seguidores automaticamente</p>
        </div>

        <div class="section">
            <h2>⚡ Ações Rápidas</h2>
            <div class="action-buttons">
                <button class="btn btn-primary" onclick="fetchAllFollowers()" data-action="fetch-all">
                    🔄 Buscar Seguidores de Todos
                </button>
                <a href="/" class="btn btn-primary" style="text-decoration: none; display: inline-flex; align-items: center;">
                    ← Voltar ao Dashboard
                </a>
            </div>

            <div class="info-box">
                <strong>ℹ️ Como funciona:</strong><br>
                1. Clique em "Autenticar" para conectar a conta TikTok<br>
                2. Você será redirecionado para o TikTok para autorizar<br>
                3. Após autenticado, use "Buscar Agora" para atualizar seguidores<br>
                4. Ou use "Buscar Seguidores de Todos" para atualizar tudo de uma vez
            </div>
        </div>

        <div class="section">
            <h2>� Status "Ao Vivo"</h2>
            <div class="ao-vivo-widget">
                <div class="ao-vivo-stats">
                    <div class="stat-item">
                        <div class="stat-value" id="ao-vivo-count">0</div>
                        <div class="stat-label">Em Direto Agora</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="offline-count">0</div>
                        <div class="stat-label">Offline</div>
                    </div>
                    <div class="stat-item">
                        <button class="btn btn-secondary" onclick="loadLiveStatus()">
                            🔄 Atualizar
                        </button>
                    </div>
                </div>
                <div id="ao-vivo-list" style="margin-top: 15px;"></div>
            </div>
        </div>

        <div class="section">
            <h2>�👥 Status dos Avatares</h2>
            <div class="perfis-grid" id="status-container">
                <?php foreach ($perfis as $perfil): ?>
                    <div class="perfil-card">
                        <h3><?php echo htmlspecialchars($perfil['avatar_nome']); ?></h3>
                        
                        <div class="perfil-info">
                            <div>
                                <span class="status-badge <?php echo $perfil['autenticado'] ? 'status-autenticado' : 'status-nao-autenticado'; ?>">
                                    <?php echo $perfil['autenticado'] ? '✅ Autenticado' : '⚠️ Não autenticado'; ?>
                                </span>
                            </div>

                            <?php if ($perfil['autenticado']): ?>
                                <div style="margin-top: 8px;">
                                    <span class="status-badge <?php echo $perfil['token_expirado'] ? 'status-token-expirado' : 'status-token-valido'; ?>">
                                        <?php echo $perfil['token_expirado'] ? '⏰ Token Expirado' : '🔄 Token Válido'; ?>
                                    </span>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($perfil['tiktok_username'])): ?>
                                <div style="margin-top: 8px; color: #bdc3c7;">
                                    <strong>Usuário TikTok:</strong> @<?php echo htmlspecialchars($perfil['tiktok_username']); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($perfil['ultima_atualizacao'])): ?>
                                <div style="margin-top: 8px; color: #95a5a6; font-size: 12px;">
                                    <strong>Última atualização:</strong> <?php echo $perfil['ultima_atualizacao']; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="perfil-actions">
                            <button class="btn-small" onclick="authenticate('<?php echo htmlspecialchars($perfil['avatar_nome']); ?>')">
                                <?php echo $perfil['autenticado'] ? '🔄 Renovar' : '🔐 Autenticar'; ?>
                            </button>
                            <?php if ($perfil['autenticado'] && !$perfil['token_expirado']): ?>
                                <button class="btn-small" onclick="fetchFollowers('<?php echo htmlspecialchars($perfil['avatar_nome']); ?>')" data-action="fetch" data-avatar="<?php echo htmlspecialchars($perfil['avatar_nome']); ?>">
                                    📊 Buscar Agora
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="js/tiktok-manager.js"></script>
    <script>
        // Criar instância assim que o script carrega
        setTimeout(function() {
            if (typeof TikTokManager !== 'undefined' && !tikTokManager) {
                try {
                    tikTokManager = new TikTokManager();
                    console.log("✅ TikTokManager criado com sucesso no timeout");
                    console.log("   - Métodos disponíveis:", Object.getOwnPropertyNames(Object.getPrototypeOf(tikTokManager)));
                } catch (error) {
                    console.error("❌ Erro ao criar TikTokManager:", error);
                }
            } else {
                console.log("🔍 Estado no timeout:");
                console.log("   - TikTokManager:", typeof TikTokManager);
                console.log("   - tikTokManager:", tikTokManager);
            }
        }, 500);
    </script>
</body>
</html>
