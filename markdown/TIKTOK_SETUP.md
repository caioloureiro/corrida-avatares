# 🎬 Integração TikTok API - Guia de Implementação

## 📋 O que foi criado

### 1. **Arquivos de Configuração**

- `config/tiktok.php` - Credenciais e configurações do TikTok API

### 2. **APIs PHP**

- `api/tiktok-oauth.php` - Gerenciar autenticação OAuth2 do TikTok
- `api/fetch-followers.php` - Buscar seguidores direto do TikTok
- `callback.php` - Receber callback da autorização do TikTok

### 3. **Interface de Admin**

- `admin-tiktok.php` - Dashboard para gerenciar autenticações

### 4. **JavaScript**

- `js/tiktok-manager.js` - Gerenciar interação com as APIs

### 5. **Banco de Dados**

- `model/migration_ao_vivo.sql` - Script para adicionar coluna e tabela

---

## 🚀 Passo a Passo de Implementação

### **PASSO 1: Executar Migração do Banco**

Execute o arquivo SQL para criar a coluna e tabela:

```bash
# No prompt do MySQL ou via PHPMyAdmin:
mysql -u root -p airbr_flow < model/migration_ao_vivo.sql
```

Ou via PHPMyAdmin:

1. Vá para o banco `airbr_flow`
2. Clique em "SQL"
3. Cole o conteúdo de `model/migration_ao_vivo.sql`
4. Execute

Isso vai:

- ✅ Adicionar coluna `ao_vivo` na tabela `corrida`
- ✅ Criar tabela `perfis_tiktok`
- ✅ Inserir os 5 avatares (Ana, Megg, Bia, Luna, Mel)

---

### **PASSO 2: Configurar as Credenciais do TikTok**

As credenciais já estão em `config/tiktok.php`:

```php
define('TIKTOK_CLIENT_KEY', 'awkzlre7h5dsvez7');
define('TIKTOK_CLIENT_SECRET', 'zsZYw5Dnj0DCSGmu3lkdUbWwDB1qBSyG');
```

**⚠️ IMPORTANTE:**

- Essas credenciais estão no código-fonte. Em produção, mova-as para variáveis de ambiente
- O `REDIRECT_URI` é configurado automaticamente para apontar ao seu domínio atual

---

### **PASSO 3: Acessar o Admin**

1. Abra no navegador: `http://localhost:8000/admin-tiktok.php`
2. Você verá os 5 avatares com status "⚠️ Não autenticado"

---

### **PASSO 4: Autenticar os Avatares**

Para cada avatar:

1. Clique no botão "🔐 Autenticar"
2. Você será redirecionado ao TikTok
3. Faça login e autorize o acesso
4. Você volta ao admin com status "✅ Autenticado"

⚠️ **IMPORTANTE:** Você precisa ter acesso às contas TikTok dos avatares para autenticar!

---

### **PASSO 5: Buscar Seguidores**

Após autenticar, você pode:

**Opção A: Buscar um avatar**

- Clique em "📊 Buscar Agora" no card do avatar

**Opção B: Buscar todos**

- Clique em "🔄 Buscar Seguidores de Todos"

Os dados serão:

- ✅ Consultados em tempo real no TikTok
- ✅ Salvos na tabela `corrida`
- ✅ Exibidos no dashboard principal

---

## 📊 Estrutura de Dados

### Tabela `corrida` (atualizada)

```sql
-- Coluna adicionada:
ao_vivo INT DEFAULT 0 -- Indica se o avatar está em livestream (0 = offline, 1 = ao vivo)
```

### Tabela `perfis_tiktok` (nova)

```
id                          - ID único
avatar_nome                 - Nome do avatar (Ana, Megg, Bia, Luna, Mel)
tiktok_username             - Username TikTok (@usuario)
tiktok_open_id              - ID único do TikTok
access_token                - Token para fazer requisições
access_token_expires_at     - Quando o token expira
refresh_token               - Para renovar token (se disponível)
created_at                  - Data de criação
updated_at                  - Última atualização
```

---

## 🔌 Endpoints da API

### 1. **Autenticação OAuth**

```
GET /api/tiktok-oauth.php?action=authorize&avatar=Ana
→ Retorna URL para redirecionar ao TikTok

GET /api/tiktok-oauth.php?action=callback&code=...&state=...
→ Processa o callback do TikTok (chamado automaticamente)
```

### 2. **Buscar Seguidores**

```
POST /api/fetch-followers.php
Body: { "avatar": "Ana" }
→ Busca seguidores de um avatar

POST /api/fetch-followers.php
Body: { "refresh_all": true }
→ Busca seguidores de todos os 5

GET /api/fetch-followers.php
→ Lista status de autenticação de todos
```

---

## 🛠️ Integração no Dashboard Principal

Para integrar com o `index.php`, você pode:

### Opção 1: Adicionar botão no dashboard

```html
<a
	href="/admin-tiktok.php"
	class="btn"
>
	⚙️ Gerenciar TikTok
</a>
```

### Opção 2: Atualizar automático (via cron)

```bash
# A cada 15 minutos, por exemplo:
*/15 * * * * curl -X POST http://localhost:8000/api/fetch-followers.php -d '{"refresh_all": true}' -H "Content-Type: application/json"
```

### Opção 3: Widget em tempo real

Adicionar ao `index.php`:

```php
<?php
$sql = "SELECT ao_vivo, COUNT(*) as total FROM corrida WHERE ativo = 1 AND data = (SELECT MAX(data) FROM corrida) AND ao_vivo = 1 GROUP BY ao_vivo";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$ao_vivo_count = $row ? $row['total'] : 0;
?>
<div class="widget">
    🔴 Em direto: <?php echo $ao_vivo_count; ?> avatar(es)
</div>
```

---

## 🔐 Segurança

### Implementado:

- ✅ CSRF token para OAuth (state)
- ✅ Validação de código de autorização
- ✅ Tokens armazenados no banco (não em cookies/sessão)
- ✅ Expiração de tokens

### Recomendações:

- 🔒 Em produção, criptografe os `access_token` no banco
- 🔒 Mova credenciais para `.env`
- 🔒 Use HTTPS
- 🔒 Implemente autenticação no `admin-tiktok.php`
- 🔒 Limpe tokens expirados periodicamente

---

## ❌ Troubleshooting

### "Erro: Avatar não especificado"

→ Certifique-se de que o nome do avatar é exato: Ana, Megg, Bia, Luna, Mel

### "Erro ao obter Access Token"

→ Verifique se as credenciais em `config/tiktok.php` estão corretas

### "Token de acesso expirado"

→ Clique em "🔄 Renovar" para fazer nova autenticação

### "Erro ao buscar informações do perfil TikTok"

→ Verifique se a conta TikTok está pública e acessível

### Dados não aparecem no dashboard

→ Abra as developer tools (F12) e verifique os erros no console

---

## 📝 Próximos Passos

1. [ ] Execute a migração SQL
2. [ ] Acesse `/admin-tiktok.php`
3. [ ] Autentique cada avatar
4. [ ] Clique em "Buscar Seguidores de Todos"
5. [ ] Verifique os dados em `/index.php`
6. [ ] Configure cron para atualizações automáticas (opcional)

---

## 📚 Documentação Oficial

- TikTok API Docs: https://developers.tiktok.com/
- OAuth 2.0 Flow: https://developers.tiktok.com/doc/login-kit-web

---

## 💡 Dicas

- Os dados de seguidores são salvos com timestamp, então você tem histórico completo
- Coluna `ao_vivo` está pronta para ser preenchida com status de livestream (implementação futura)
- API retorna dados em JSON, pronta para integração com front-end moderno
- Você pode chamar a API via JavaScript/AJAX para atualizações sem recarregar página

---

**Criado em:** 16 de maio de 2026  
**Status:** ✅ Pronto para usar
