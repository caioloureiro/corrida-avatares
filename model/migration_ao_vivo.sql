-- Adicionar coluna 'ao_vivo' na tabela corrida
ALTER TABLE corrida ADD COLUMN ao_vivo INT DEFAULT 0;

-- Criar tabela para vincular avatares com perfis do TikTok
CREATE TABLE IF NOT EXISTS perfis_tiktok (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- Inserir os 5 avatares na tabela de perfis do TikTok
INSERT IGNORE INTO perfis_tiktok (avatar_nome, tiktok_username) VALUES
('Ana', ''),
('Megg', ''),
('Bia', ''),
('Luna', ''),
('Mel', '');
