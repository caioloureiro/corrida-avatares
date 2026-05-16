<?php
/**
 * Setup.php - Executar migrações do banco de dados
 * 
 * Acesse no navegador:
 * http://localhost:8000/setup.php
 */

require_once __DIR__ . '/config/db.php';

$setup_completed = [];
$setup_errors = [];

// Verificar se coluna ao_vivo existe
$checkColumn = $conn->query("SHOW COLUMNS FROM corrida LIKE 'ao_vivo'");
if (!$checkColumn || $checkColumn->num_rows === 0) {
	// Coluna não existe, criar
	$sql = "ALTER TABLE corrida ADD COLUMN ao_vivo INT DEFAULT 0";
	if ($conn->query($sql)) {
		$setup_completed[] = "✅ Coluna 'ao_vivo' criada";
	} else {
		$setup_errors[] = "❌ Erro ao criar coluna 'ao_vivo': " . $conn->error;
	}
} else {
	$setup_completed[] = "✅ Coluna 'ao_vivo' já existe";
}

// Verificar se coluna tiktok_seguidores existe
$checkColumn = $conn->query("SHOW COLUMNS FROM corrida LIKE 'tiktok_seguidores'");
if (!$checkColumn || $checkColumn->num_rows === 0) {
	// Coluna não existe, criar
	$sql = "ALTER TABLE corrida ADD COLUMN tiktok_seguidores INT DEFAULT 0 COMMENT 'Valor de seguidores vindo direto do TikTok'";
	if ($conn->query($sql)) {
		$setup_completed[] = "✅ Coluna 'tiktok_seguidores' criada";
	} else {
		$setup_errors[] = "❌ Erro ao criar coluna 'tiktok_seguidores': " . $conn->error;
	}
} else {
	$setup_completed[] = "✅ Coluna 'tiktok_seguidores' já existe";
}

// Verificar se tabela perfis_tiktok existe
$checkTable = $conn->query("SHOW TABLES LIKE 'perfis_tiktok'");
if (!$checkTable || $checkTable->num_rows === 0) {
	// Tabela não existe, criar
	$sql = "CREATE TABLE IF NOT EXISTS perfis_tiktok (
		id INT NOT NULL AUTO_INCREMENT,
		avatar_nome VARCHAR(255) NOT NULL UNIQUE,
		tiktok_username VARCHAR(255),
		tiktok_open_id VARCHAR(255),
		access_token TEXT,
		access_token_expires_at DATETIME,
		refresh_token TEXT,
		created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
		updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		PRIMARY KEY (id),
		FOREIGN KEY (avatar_nome) REFERENCES corrida(nome)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci";
	
	if ($conn->query($sql)) {
		$setup_completed[] = "✅ Tabela 'perfis_tiktok' criada";
		
		// Inserir os 5 avatares
		$avatares = ['Ana', 'Megg', 'Bia', 'Luna', 'Mel'];
		foreach ($avatares as $avatar) {
			$sql_insert = "INSERT IGNORE INTO perfis_tiktok (avatar_nome) VALUES ('$avatar')";
			$conn->query($sql_insert);
		}
		$setup_completed[] = "✅ Avatares inseridos na tabela 'perfis_tiktok'";
	} else {
		$setup_errors[] = "❌ Erro ao criar tabela 'perfis_tiktok': " . $conn->error;
	}
} else {
	$setup_completed[] = "✅ Tabela 'perfis_tiktok' já existe";
}

$conn->close();

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Setup - Corrida de Avatares</title>
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
			max-width: 600px;
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
			margin-bottom: 15px;
			font-size: 18px;
		}

		.message {
			padding: 12px;
			margin-bottom: 10px;
			border-radius: 5px;
			border-left: 3px solid;
		}

		.message.success {
			background: rgba(46, 213, 115, 0.1);
			border-left-color: #2ed573;
			color: #2ed573;
		}

		.message.error {
			background: rgba(255, 71, 87, 0.1);
			border-left-color: #ff4757;
			color: #ff4757;
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

		.status-box {
			background: rgba(0, 247, 239, 0.1);
			border-left: 3px solid #00f7ef;
			padding: 15px;
			border-radius: 5px;
			margin-top: 20px;
		}

		.emoji {
			font-size: 48px;
			text-align: center;
			margin-bottom: 10px;
		}
	</style>
</head>
<body>
	<div class="container">
		<div class="header">
			<div class="emoji">⚙️</div>
			<h1>Setup - Banco de Dados</h1>
			<p>Inicializar migrações do banco</p>
		</div>

		<div class="section">
			<h2>✅ Operações Concluídas</h2>
			<?php foreach ($setup_completed as $msg): ?>
				<div class="message success"><?php echo htmlspecialchars($msg); ?></div>
			<?php endforeach; ?>
		</div>

		<?php if (!empty($setup_errors)): ?>
			<div class="section">
				<h2>❌ Erros</h2>
				<?php foreach ($setup_errors as $msg): ?>
					<div class="message error"><?php echo htmlspecialchars($msg); ?></div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if (empty($setup_errors)): ?>
			<div class="status-box">
				<strong>✅ Tudo Pronto!</strong><br>
				Seu banco de dados foi inicializado com sucesso.
				<br><br>
				Próximos passos:
				<ol style="margin-left: 20px; margin-top: 10px;">
					<li>Acesse <strong>/admin-tiktok.php</strong></li>
					<li>Autentique os avatares com suas contas TikTok</li>
					<li>Clique "Buscar Seguidores de Todos"</li>
					<li>Veja os dados em <strong>/index.php</strong></li>
				</ol>
			</div>
		<?php endif; ?>

		<div class="button-group">
			<a href="/health-check.php" class="btn btn-primary">📋 Verificar Status</a>
			<a href="/admin-tiktok.php" class="btn btn-primary">⚙️ Admin</a>
			<a href="/" class="btn btn-primary">📊 Dashboard</a>
		</div>
	</div>
</body>
</html>
