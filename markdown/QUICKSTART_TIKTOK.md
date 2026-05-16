# 🚀 Quick Start - TikTok Integration

## ⚡ 5 Minutos para Começar

### 1️⃣ Executar Migration (1 minuto)

Via terminal:

```bash
cd d:\Sites\corrida-avatares
mysql -u root -p airbr_flow < model/migration_ao_vivo.sql
```

Ou via PHPMyAdmin:

- Abra seu banco `airbr_flow`
- Clique em "SQL"
- Cole tudo de `model/migration_ao_vivo.sql`
- Clique "Executar"

✅ Pronto! Tabelas criadas.

---

### 2️⃣ Acessar Admin (30 segundos)

Abra no navegador:

```
http://localhost:8000/admin-tiktok.php
```

Você verá 5 cards com os avatares.

---

### 3️⃣ Autenticar Um Avatar (2 minutos)

Para cada avatar que quiser (ex: Ana):

1. Clique "🔐 Autenticar"
2. Você vai para o TikTok
3. Faça login na conta TikTok
4. Clique "Autorizar"
5. Volta automaticamente para o admin
6. Status muda para "✅ Autenticado"

**Repita para os 5 avatares!**

---

### 4️⃣ Buscar Seguidores (30 segundos)

Opção A - Buscar um:

- Clique "📊 Buscar Agora" no avatar

Opção B - Buscar todos:

- Clique "🔄 Buscar Seguidores de Todos"

**Pronto!** Os dados aparecerão no dashboard `/index.php`

---

## 📋 Credenciais Já Configuradas

```php
// config/tiktok.php
Client Key:    awkzlre7h5dsvez7
Client Secret: zsZYw5Dnj0DCSGmu3lkdUbWwDB1qBSyG
Redirect:      http://seu-servidor/callback.php
```

✅ Tudo pronto para usar!

---

## 📊 Dados Salvos

Cada vez que você clica "Buscar", um novo registro é criado com:

- Nome do avatar
- Seguidores (do TikTok)
- Status "Ao vivo" (0 ou 1)
- Timestamp

Você tem **histórico completo** de seguidores!

---

## 🔄 Automático (Opcional)

Para atualizar automaticamente a cada 15 minutos, adicione ao Cron:

```bash
*/15 * * * * php /caminho/para/api/update-followers-cron.php
```

Ou via HTTP:

```bash
*/15 * * * * curl "http://localhost:8000/api/update-followers-cron.php?token=seu_token_aqui"
```

---

## 📚 Documentação Completa

- [Setup Detalhado](TIKTOK_SETUP.md)
- [Arquitetura](ARQUITETURA_TIKTOK.md)

---

## ⚠️ Common Issues

**"Token expirado"**
→ Clique "🔄 Renovar" para fazer nova autenticação

**"Perfil não encontrado"**
→ Execute a migration SQL

**"Erro ao conectar TikTok"**
→ Verifique se tem internet e as credenciais estão corretas

**Sem dados aparecer?**
→ Abra F12 (DevTools) e procure por erros vermelhos

---

## 🎯 Arquivos Criados

```
✅ config/tiktok.php                    - Configuração
✅ api/tiktok-oauth.php                 - OAuth
✅ api/fetch-followers.php              - Buscar dados
✅ api/update-followers-cron.php        - Automático
✅ callback.php                         - Callback
✅ admin-tiktok.php                     - Admin UI
✅ js/tiktok-manager.js                 - JavaScript
✅ model/migration_ao_vivo.sql          - Banco
✅ TIKTOK_SETUP.md                      - Guia
✅ ARQUITETURA_TIKTOK.md                - Detalhes
✅ QUICKSTART_TIKTOK.md                 - Este arquivo
```

---

**Status:** ✅ Pronto para usar  
**Tempo estimado:** 5-10 minutos
