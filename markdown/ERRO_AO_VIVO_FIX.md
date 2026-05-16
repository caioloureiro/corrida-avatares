# 🔧 Erro: Coluna 'ao_vivo' não encontrada

## ❌ O Problema

```
Unknown column 'ao_vivo' in 'where clause'
```

Isso significa que a **coluna `ao_vivo` ainda não foi criada** no banco de dados.

---

## ✅ Solução (2 Maneiras)

### Opção 1: Automática (Recomendada) 🚀

Abra no navegador:

```
http://localhost:8000/setup.php
```

Isso irá:

- ✅ Criar coluna `ao_vivo`
- ✅ Criar tabela `perfis_tiktok`
- ✅ Inserir os 5 avatares
- ✅ Exibir confirmação visual

**Pronto! Recarregue a página `/index.php`**

---

### Opção 2: Manual (SQL)

Se preferir fazer via PHPMyAdmin:

1. **Acesse seu banco**: `http://localhost/phpmyadmin`
2. **Banco**: Selecione `airbr_flow`
3. **SQL**: Clique em "SQL"
4. **Cole este código**:

```sql
-- Adicionar coluna ao_vivo
ALTER TABLE corrida ADD COLUMN ao_vivo INT DEFAULT 0;

-- Criar tabela perfis_tiktok
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
  PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- Inserir os 5 avatares
INSERT IGNORE INTO perfis_tiktok (avatar_nome) VALUES ('Ana'), ('Megg'), ('Bia'), ('Luna'), ('Mel');
```

5. **Clique**: "Executar"

**Pronto!**

---

## 🔍 Verificar Status

Após executar o setup:

```
http://localhost:8000/health-check.php
```

Isso vai validar se tudo foi criado corretamente.

---

## 📋 Próximos Passos

1. ✅ Execute setup.php
2. ✅ Verifique com health-check.php
3. ✅ Abra `/admin-tiktok.php`
4. ✅ Autentique os avatares
5. ✅ Clique "Buscar Seguidores de Todos"
6. ✅ Veja os dados em `/index.php` ✨

---

**Status Atual:**

- ❌ Coluna `ao_vivo` - **não criada** ← Execute o setup!
- ❌ Tabela `perfis_tiktok` - **não criada** ← Execute o setup!

Depois de executar o setup, volte para `/index.php` e recarregue (F5). 🚀
