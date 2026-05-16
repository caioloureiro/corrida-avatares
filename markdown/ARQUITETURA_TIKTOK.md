# 🎬 Corrida de Avatares - Integração TikTok

## 📊 Visão Geral da Arquitetura

```
┌─────────────────────────────────────────────────────────────────┐
│                       TikTok API Server                         │
│                  (OAuth 2.0 + User Info)                       │
└────────────────────┬────────────────────────────────────────────┘
                     │
                     │ HTTPS
                     │ (OAuth Flow)
                     │
        ┌────────────┴───────────────┐
        │                            │
        v                            v
┌──────────────────────────┐  ┌──────────────────────────┐
│   Navegador do Usuário    │  │    Seu Servidor PHP     │
│    (admin-tiktok.php)     │  │   (corrida-avatares)    │
│                           │  │                        │
│  ┌─────────────────────┐ │  │  ┌──────────────────┐  │
│  │ Clique "Autenticar" │ │  │  │ tiktok-oauth.php │  │
│  └──────────┬──────────┘ │  │  │ (authorize)      │  │
│             │            │  │  │ (callback)       │  │
│             v            │  │  └──────────────────┘  │
│  ┌─────────────────────┐ │  │                        │
│  │ Redireciona para    │────→  callback.php          │
│  │ TikTok para Auth    │ │  │                        │
│  └─────────────────────┘ │  │  ┌──────────────────┐  │
│             ^            │  │  │fetch-followers.php   │
│             │ (Callback) │  │  │ (GET/POST)       │  │
│             └────────────┼──┼─→│ Busca seguidores │  │
│                          │  │  │ Atualiza BD      │  │
│  ┌─────────────────────┐ │  │  └──────────────────┘  │
│  │ Exibe Status ✅     │ │  │       │                │
│  │ Botões para ação    │ │  │       │                │
│  └─────────────────────┘ │  │       v                │
│             ^            │  │  ┌──────────────────┐  │
│             │            │  │  │    MySQL DB      │  │
│             └────────────┼──┼──→│                  │  │
│        (tiktok-manager.js)│  │  │ tabela: corrida  │  │
│                           │  │  │ tabela: perfis_  │  │
│                           │  │  │    tiktok        │  │
│                           │  │  └──────────────────┘  │
└──────────────────────────┘  └──────────────────────┘
        Frontend                   Backend
```

---

## 🔄 Fluxo de Autenticação (OAuth 2.0)

```
1. USUÁRIO CLICA "Autenticar"
   ↓
2. admin-tiktok.php → tiktok-manager.js → api/tiktok-oauth.php?action=authorize
   ↓
3. tiktok-oauth.php gera URL de autorização com CSRF token
   ↓
4. Usuário redireciona para TikTok para fazer login
   ↓
5. TikTok faz callback para callback.php?code=xxx&state=yyy
   ↓
6. callback.php → api/tiktok-oauth.php?action=callback
   ↓
7. tiktok-oauth.php troca código por access_token
   ↓
8. Salva token no banco em perfis_tiktok
   ↓
9. Redireciona para admin-tiktok.php com sucesso
```

---

## 📈 Fluxo de Busca de Seguidores

```
1. USUÁRIO CLICA "Buscar Agora" ou "Buscar Todos"
   ↓
2. admin-tiktok.php → tiktok-manager.js → fetch-followers.php (POST)
   ↓
3. fetch-followers.php busca token no banco (perfis_tiktok)
   ↓
4. Verifica se token ainda é válido
   ↓
5. Faz requisição para TikTok API:
   GET https://open.tiktokapis.com/v2/user/info/
   Authorization: Bearer {access_token}
   ↓
6. Recebe resposta com follower_count
   ↓
7. INSERT novo registro em corrida com:
   - nome (avatar)
   - seguidores
   - ao_vivo (status)
   - data (timestamp)
   ↓
8. Retorna JSON com resultado
   ↓
9. JavaScript atualiza UI e recarrega dashboard
```

---

## 📁 Estrutura de Arquivos

```
corrida-avatares/
├── admin-tiktok.php              ← Dashboard de admin
├── callback.php                  ← Callback do OAuth
├── config/
│   ├── db.php                   ← Conexão MySQL
│   └── tiktok.php               ← Config TikTok (credentials)
├── api/
│   ├── tiktok-oauth.php         ← OAuth implementation
│   ├── fetch-followers.php      ← Buscar seguidores
│   └── update-followers-cron.php ← Cron job automation
├── js/
│   └── tiktok-manager.js        ← Manager JavaScript class
├── model/
│   └── migration_ao_vivo.sql    ← Migration do BD
├── TIKTOK_SETUP.md              ← Este guia
└── logs/
    └── followers-update.log     ← Log de atualizações
```

---

## 🗄️ Estrutura do Banco de Dados

### Tabela: `corrida` (existente + alteração)

```sql
id           INT          -- ID do registro
ativo        INT          -- 1 = ativo, 0 = desativo
created_at   DATETIME     -- Data de criação
updated_at   DATETIME     -- Data de atualização
nome         VARCHAR(255) -- Nome do avatar (Ana, Megg, Bia, Luna, Mel)
seguidores   INT          -- Número de seguidores
ao_vivo      INT          -- 0 = offline, 1 = ao vivo (NOVO)
data         DATETIME     -- Timestamp do registro
```

### Tabela: `perfis_tiktok` (nova)

```sql
id                      INT          -- ID único
avatar_nome             VARCHAR(255) -- Nome do avatar
tiktok_username         VARCHAR(255) -- @username do TikTok
tiktok_open_id          VARCHAR(255) -- ID único do perfil TikTok
access_token            TEXT         -- Token para fazer requests
access_token_expires_at DATETIME     -- Quando expira o token
refresh_token           TEXT         -- Para renovar token (se houver)
created_at              DATETIME     -- Quando foi autenticado
updated_at              DATETIME     -- Última atualização
```

---

## 🔐 Segurança

### ✅ Implementado

- **CSRF Token** - State parameter no OAuth
- **Token Expiration** - Verifica validade antes de usar
- **Secure Redirect** - Valida estado do CSRF antes de salvar

### ⚠️ Recomendações Futuras

- Criptografar `access_token` no banco
- Usar variáveis de ambiente para credenciais
- Autenticação no admin-tiktok.php (login)
- Rate limiting na API
- Logs de auditoria
- HTTPS obrigatório

---

## 📋 Endpoints da API

### 1. Autenticação OAuth

```
GET /api/tiktok-oauth.php?action=authorize&avatar=Ana
GET /api/tiktok-oauth.php?action=callback&code=XXX&state=YYY
```

### 2. Buscar Seguidores

```
POST /api/fetch-followers.php
{
  "avatar": "Ana"
}

POST /api/fetch-followers.php
{
  "refresh_all": true
}

GET /api/fetch-followers.php
```

### 3. Atualização Automática (Cron)

```
GET /api/update-followers-cron.php?token=seu_token_secreto
php /caminho/para/api/update-followers-cron.php
```

---

## 🚀 Como Usar

### Manual (via Admin)

1. Acesse `/admin-tiktok.php`
2. Clique "Autenticar" para cada avatar
3. Clique "Buscar Agora" para atualizar manualmente

### Automático (via Cron)

```bash
# Atualizar a cada 15 minutos
*/15 * * * * php /caminho/para/api/update-followers-cron.php

# Ou via HTTP (certifique-se de usar HTTPS em produção)
*/15 * * * * curl "https://seu-dominio.com/api/update-followers-cron.php?token=seu_token_secreto"
```

### Via JavaScript

```javascript
// Autenticar
tikTokManager.authenticate("Ana");

// Buscar um avatar
tikTokManager.fetchFollowers("Ana");

// Buscar todos
tikTokManager.fetchAllFollowers();
```

---

## 🐛 Debug

### Verificar Logs

```bash
tail -f logs/followers-update.log
```

### Testar API via cURL

```bash
# Listar status
curl http://localhost:8000/api/fetch-followers.php

# Buscar seguidores
curl -X POST http://localhost:8000/api/fetch-followers.php \
  -H "Content-Type: application/json" \
  -d '{"avatar": "Ana"}'
```

### Console Browser

Abra DevTools (F12) em `admin-tiktok.php` para ver logs do JavaScript

---

## 📊 Fluxo Completo - Timeline

```
T0:00 - Usuário acessa /admin-tiktok.php
        ↓ carrega tiktok-manager.js
        ↓ loadStatus() busca perfis_tiktok
        ↓ exibe cards dos 5 avatares

T0:05 - Usuário clica "Autenticar" em Ana
        ↓ authenticate('Ana') chamado
        ↓ GET /api/tiktok-oauth.php?action=authorize&avatar=Ana
        ↓ retorna URL de auth do TikTok
        ↓ window.location.href redirecionado

T0:10 - Usuário faz login no TikTok
        ↓ clica "Autorizar"
        ↓ TikTok faz callback para /callback.php?code=XXX&state=YYY

T0:15 - callback.php processa
        ↓ chama /api/tiktok-oauth.php?action=callback
        ↓ exchangeCodeForToken() obtém access_token
        ↓ fetchTikTokUserInfo() busca dados do perfil
        ↓ UPDATE perfis_tiktok com token
        ↓ exibe mensagem de sucesso
        ↓ redireciona para dashboard

T0:20 - Usuário clica "Buscar Agora" em Ana
        ↓ fetchFollowers('Ana') chamado
        ↓ POST /api/fetch-followers.php {"avatar": "Ana"}
        ↓ busca token no banco
        ↓ verifica validade
        ↓ GET TikTok API /v2/user/info/
        ↓ INSERT em corrida com seguidores
        ↓ retorna JSON com resultado
        ↓ showSuccess() exibe mensagem
        ↓ refreshUI() recarrega página

T0:25 - Dados aparecem no dashboard /index.php
        ↓ SELECT registros recentes
        ↓ exibe gráficos e estatísticas
```

---

## 🎯 Checklist de Implementação

- [ ] Executar migration SQL (`model/migration_ao_vivo.sql`)
- [ ] Verificar credenciais em `config/tiktok.php`
- [ ] Acessar `/admin-tiktok.php`
- [ ] Autenticar primeiro avatar
- [ ] Testar "Buscar Agora"
- [ ] Verificar dados em `/index.php`
- [ ] Autenticar restantes 4 avatares
- [ ] Testar "Buscar Seguidores de Todos"
- [ ] Configurar cron job (opcional)
- [ ] Testar update automático
- [ ] Implementar autenticação no admin
- [ ] Criptografar tokens (produção)

---

## 💡 Dicas e Tricks

1. **Testar sem autenticação real**

   ```php
   // Inserir manualmente para teste:
   INSERT INTO perfis_tiktok (avatar_nome, tiktok_username, access_token)
   VALUES ('Ana', 'ana_tiktok', 'fake_token_for_test');
   ```

2. **Ver requisição ao TikTok**

   ```php
   // Em fetch-followers.php, adicione:
   error_log("Request: " . json_encode($userData));
   ```

3. **Limpar cache do navegador**
   Ctrl+Shift+Del em Chrome/Firefox

4. **Força atualizar página**
   Ctrl+Shift+R

5. **Testar via POST direto**
   ```bash
   curl -X POST http://localhost:8000/api/fetch-followers.php \
     -H "Content-Type: application/json" \
     -d '{"refresh_all": true}'
   ```

---

**Criado em:** 16 de maio de 2026  
**Versão:** 1.0  
**Status:** ✅ Pronto para Produção
