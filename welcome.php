<?php
/**
 * Welcome.php - Primeira página após instalação
 * Guia o usuário através do setup inicial
 */

require_once __DIR__ . '/config/db.php';

// Verificar status do setup
$ao_vivo_exists = false;
$perfis_tiktok_exists = false;

$checkCol = $conn->query("SHOW COLUMNS FROM corrida LIKE 'ao_vivo'");
if ($checkCol && $checkCol->num_rows > 0) {
	$ao_vivo_exists = true;
}

$checkTable = $conn->query("SHOW TABLES LIKE 'perfis_tiktok'");
if ($checkTable && $checkTable->num_rows > 0) {
	$perfis_tiktok_exists = true;
}

$setup_complete = $ao_vivo_exists && $perfis_tiktok_exists;

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Bem-vindo - Corrida de Avatares</title>
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
			max-width: 700px;
			margin: 0 auto;
		}

		.header {
			text-align: center;
			margin-bottom: 30px;
		}

		.emoji-large {
			font-size: 64px;
			margin-bottom: 20px;
		}

		.header h1 {
			font-size: 36px;
			color: #00f7ef;
			margin-bottom: 10px;
		}

		.header p {
			color: #bdc3c7;
			font-size: 16px;
		}

		.section {
			background: rgba(255, 255, 255, 0.05);
			border: 1px solid rgba(0, 247, 239, 0.2);
			border-radius: 10px;
			padding: 25px;
			margin-bottom: 20px;
			backdrop-filter: blur(10px);
		}

		.section h2 {
			color: #00f7ef;
			margin-bottom: 15px;
			font-size: 20px;
		}

		.step {
			display: flex;
			gap: 15px;
			margin-bottom: 20px;
		}

		.step-number {
			width: 40px;
			height: 40px;
			background: #00f7ef;
			color: #000;
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			font-weight: bold;
			flex-shrink: 0;
		}

		.step-content h3 {
			color: #ecf0f1;
			margin-bottom: 5px;
		}

		.step-content p {
			color: #bdc3c7;
			font-size: 14px;
			line-height: 1.6;
		}

		.status-item {
			padding: 12px;
			margin-bottom: 10px;
			border-radius: 5px;
			display: flex;
			align-items: center;
			gap: 10px;
			border-left: 3px solid;
		}

		.status-item.done {
			background: rgba(46, 213, 115, 0.1);
			border-left-color: #2ed573;
			color: #2ed573;
		}

		.status-item.pending {
			background: rgba(255, 195, 0, 0.1);
			border-left-color: #ffc300;
			color: #ffc300;
		}

		.button-group {
			display: flex;
			gap: 10px;
			margin-top: 20px;
			flex-wrap: wrap;
			justify-content: center;
		}

		.btn {
			padding: 12px 24px;
			border: none;
			border-radius: 5px;
			cursor: pointer;
			font-size: 14px;
			font-weight: 600;
			transition: all 0.3s;
			text-decoration: none;
			display: inline-flex;
			align-items: center;
			gap: 8px;
		}

		.btn-primary {
			background: #00f7ef;
			color: #000;
		}

		.btn-primary:hover {
			background: #00d4d1;
			transform: translateY(-2px);
			box-shadow: 0 5px 15px rgba(0, 247, 239, 0.3);
		}

		.btn-secondary {
			background: rgba(0, 247, 239, 0.1);
			color: #00f7ef;
			border: 1px solid #00f7ef;
		}

		.btn-secondary:hover {
			background: rgba(0, 247, 239, 0.2);
		}

		.progress-box {
			background: rgba(0, 247, 239, 0.1);
			border-left: 3px solid #00f7ef;
			padding: 15px;
			border-radius: 5px;
			margin-bottom: 20px;
		}

		.progress-bar-outer {
			background: rgba(0, 0, 0, 0.3);
			height: 30px;
			border-radius: 5px;
			overflow: hidden;
			margin-top: 10px;
		}

		.progress-bar-inner {
			background: linear-gradient(90deg, #00f7ef 0%, #00d4d1 100%);
			height: 100%;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 12px;
			font-weight: bold;
			color: #000;
			transition: width 0.3s;
		}

		.next-action {
			background: rgba(0, 247, 239, 0.1);
			border: 2px dashed rgba(0, 247, 239, 0.5);
			border-radius: 8px;
			padding: 20px;
			text-align: center;
			margin-top: 20px;
		}

		.next-action strong {
			color: #00f7ef;
			font-size: 16px;
		}
	</style>
</head>
<body>
	<div class="container">
		<div class="header">
			<div class="emoji-large">🏁</div>
			<h1>Bem-vindo!</h1>
			<p>Corrida de Avatares - Sistema de Monitoramento de Seguidores</p>
		</div>

		<?php if (!$setup_complete): ?>
			<div class="section">
				<h2>⚙️ Setup Inicial</h2>
				
				<div class="progress-box">
					<strong>Progresso do Setup</strong>
					<div class="progress-bar-outer">
						<div class="progress-bar-inner" style="width: <?php echo ($ao_vivo_exists && $perfis_tiktok_exists ? 100 : ($ao_vivo_exists || $perfis_tiktok_exists ? 50 : 0)); ?>%;">
							<?php echo ($ao_vivo_exists && $perfis_tiktok_exists ? 100 : ($ao_vivo_exists || $perfis_tiktok_exists ? 50 : 0)); ?>%
						</div>
					</div>
				</div>

				<div style="margin-top: 20px;">
					<div class="status-item <?php echo $ao_vivo_exists ? 'done' : 'pending'; ?>">
						<span><?php echo $ao_vivo_exists ? '✅' : '⏳'; ?></span>
						<span><?php echo $ao_vivo_exists ? 'Coluna "ao_vivo" criada' : 'Coluna "ao_vivo" - pendente'; ?></span>
					</div>
					<div class="status-item <?php echo $perfis_tiktok_exists ? 'done' : 'pending'; ?>">
						<span><?php echo $perfis_tiktok_exists ? '✅' : '⏳'; ?></span>
						<span><?php echo $perfis_tiktok_exists ? 'Tabela "perfis_tiktok" criada' : 'Tabela "perfis_tiktok" - pendente'; ?></span>
					</div>
				</div>

				<div class="next-action">
					<strong>🔧 Execute o Setup Automático</strong><br>
					<p style="margin-top: 10px; color: #bdc3c7;">Clique no botão abaixo para criar as tabelas e colunas necessárias:</p>
				</div>

				<div class="button-group">
					<a href="/setup.php" class="btn btn-primary">
						⚙️ Executar Setup
					</a>
				</div>
			</div>

		<?php else: ?>
			<div class="section">
				<h2>✅ Setup Completo!</h2>
				
				<div class="progress-box">
					<strong>Progresso do Setup</strong>
					<div class="progress-bar-outer">
						<div class="progress-bar-inner" style="width: 100%;">
							100%
						</div>
					</div>
				</div>

				<div style="margin-top: 20px;">
					<div class="status-item done">
						<span>✅</span>
						<span>Coluna "ao_vivo" criada</span>
					</div>
					<div class="status-item done">
						<span>✅</span>
						<span>Tabela "perfis_tiktok" criada</span>
					</div>
				</div>

				<p style="color: #bdc3c7; margin-top: 15px; text-align: center;">
					Seu banco de dados está pronto! Agora configure os avatares no TikTok.
				</p>
			</div>

			<div class="section">
				<h2>🎬 Próximos Passos</h2>

				<div class="step">
					<div class="step-number">1</div>
					<div class="step-content">
						<h3>Autentique os Avatares</h3>
						<p>Acesse o painel de admin do TikTok e conecte cada avatar com sua conta TikTok (Ana, Megg, Bia, Luna, Mel).</p>
					</div>
				</div>

				<div class="step">
					<div class="step-number">2</div>
					<div class="step-content">
						<h3>Busque os Seguidores</h3>
						<p>Clique em "Buscar Seguidores de Todos" para sincronizar os dados direto do TikTok.</p>
					</div>
				</div>

				<div class="step">
					<div class="step-number">3</div>
					<div class="step-content">
						<h3>Visualize os Dados</h3>
						<p>Veja o ranking de avatares, seguidores, status ao vivo e histórico completo no dashboard.</p>
					</div>
				</div>

				<div class="button-group" style="margin-top: 30px;">
					<a href="/admin-tiktok.php" class="btn btn-primary">
						⚙️ Admin - Gerenciar TikTok
					</a>
					<a href="/" class="btn btn-secondary">
						📊 Dashboard Principal
					</a>
				</div>
			</div>
		<?php endif; ?>

		<div class="section" style="background: rgba(0, 247, 239, 0.05); border-color: rgba(0, 247, 239, 0.3);">
			<h2>📚 Documentação</h2>
			<p style="color: #bdc3c7; margin-bottom: 15px;">
				Consulte os guias para mais informações:
			</p>
			<div style="color: #00f7ef;">
				<a href="/QUICKSTART_TIKTOK.md" style="color: #00f7ef; text-decoration: underline;">
					⚡ Quick Start (5 minutos)
				</a><br>
				<a href="/TIKTOK_SETUP.md" style="color: #00f7ef; text-decoration: underline;">
					📖 Setup Completo
				</a><br>
				<a href="/AO_VIVO_IMPLEMENTED.md" style="color: #00f7ef; text-decoration: underline;">
					🔴 Coluna "Ao Vivo"
				</a>
			</div>
		</div>
	</div>
</body>
</html>
