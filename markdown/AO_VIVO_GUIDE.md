# 🔴 Atualizar Coluna "Ao Vivo"

## ✅ Automático (Recomendado)

Quando você clica "🔄 Buscar Seguidores de Todos" no admin:

1. A API busca dados do TikTok
2. Detecta se está ao vivo (campo `is_live` ou `live_status`)
3. **Preenche automaticamente a coluna `ao_vivo`**
4. Dashboard mostra: 🔴 "Ao vivo" ou ⚫ "Offline"

---

## 📊 Visualização no Dashboard

Coluna "Ao vivo" na tabela exibe:

- **🔴 Ao vivo** - Avatar está transmitindo (com animação pulsante)
- **⚫ Offline** - Avatar não está transmitindo

---

## 🛠️ Manual (via SQL)

Se quiser atualizar manualmente:

### Marcar como "Ao Vivo"

```sql
-- Marcar Ana como ao vivo
UPDATE corrida SET ao_vivo = 1, updated_at = NOW()
WHERE nome = 'Ana' AND id = (SELECT MAX(id) FROM corrida WHERE nome = 'Ana');

-- Marcar todos como ao vivo
UPDATE corrida SET ao_vivo = 1, updated_at = NOW()
WHERE id IN (SELECT MAX(id) FROM corrida GROUP BY nome);
```

### Marcar como "Offline"

```sql
-- Marcar Ana como offline
UPDATE corrida SET ao_vivo = 0, updated_at = NOW()
WHERE nome = 'Ana' AND id = (SELECT MAX(id) FROM corrida WHERE nome = 'Ana');

-- Marcar todos como offline
UPDATE corrida SET ao_vivo = 0, updated_at = NOW()
WHERE id IN (SELECT MAX(id) FROM corrida GROUP BY nome);
```

---

## 📋 Scripts SQL Úteis

### Ver Status Atual

```sql
SELECT nome, seguidores, ao_vivo, data
FROM corrida
WHERE id IN (SELECT MAX(id) FROM corrida GROUP BY nome)
ORDER BY nome;
```

### Ver Histórico de "Ao Vivo"

```sql
SELECT nome, seguidores, ao_vivo, data
FROM corrida
WHERE ao_vivo = 1
ORDER BY data DESC
LIMIT 20;
```

### Contar Quantos Estão Ao Vivo Agora

```sql
SELECT COUNT(*) as avatares_ao_vivo
FROM (
    SELECT nome, ao_vivo
    FROM corrida
    WHERE id IN (SELECT MAX(id) FROM corrida GROUP BY nome)
    AND ao_vivo = 1
) as T;
```

### Histórico Completo por Avatar

```sql
SELECT nome, COUNT(*) as total_buscas,
       SUM(ao_vivo) as vezes_ao_vivo,
       ROUND(SUM(ao_vivo) / COUNT(*) * 100, 2) as percentual_ao_vivo
FROM corrida
WHERE ativo = 1
GROUP BY nome;
```

---

## 🔄 Fluxo de Atualização (Automático)

```
1. Clica "Buscar Seguidores de Todos" em /admin-tiktok.php
   ↓
2. POST /api/fetch-followers.php {"refresh_all": true}
   ↓
3. Para cada avatar autenticado:
   - GET https://open.tiktokapis.com/v2/user/info/
   - Campos solicitados: ..., is_live, ...
   ↓
4. Detecta status ao vivo
   ↓
5. INSERT novo registro em corrida com:
   - nome
   - seguidores
   - ao_vivo ← 1 ou 0
   - data (timestamp atual)
   ↓
6. Dashboard carrega e exibe 🔴 ou ⚫
```

---

## 🤖 Automação via Cron (Opcional)

Para atualizar automaticamente a cada 15 minutos:

```bash
*/15 * * * * php /caminho/para/api/update-followers-cron.php
```

Este script:

- ✅ Busca todos os avatares autenticados
- ✅ Consulta TikTok API
- ✅ Preenche `ao_vivo` automaticamente
- ✅ Salva dados no BD
- ✅ Registra log em `logs/followers-update.log`

---

## 🎯 Como Verificar

### Via Admin

1. Acesse `/admin-tiktok.php`
2. Clique "📊 Buscar Agora" em um avatar
3. Aguarde a resposta
4. Acesse `/index.php`
5. Coluna "Ao vivo" será atualizada

### Via SQL

```sql
SELECT nome, ao_vivo, data
FROM corrida
ORDER BY id DESC
LIMIT 5;
```

### Via API

```bash
curl http://localhost:8000/api/fetch-followers.php
```

Retorna JSON com status de autenticação.

---

## 📝 Possíveis Valores

### Coluna `ao_vivo` na tabela `corrida`

| Valor | Significado                | Badge      |
| ----- | -------------------------- | ---------- |
| 0     | Offline / Não transmitindo | ⚫ Offline |
| 1     | Ao vivo / Transmitindo     | 🔴 Ao vivo |

---

## 🐛 Troubleshooting

**"Ao vivo" sempre mostra "⚫ Offline"**

- Verifique se a API do TikTok retorna o campo `is_live`
- Verifique logs em `/logs/followers-update.log`
- Teste a API com `curl`

**"Ao vivo" não atualiza após buscar**

- Recarregue a página (F5)
- Verifique o console do navegador (F12)
- Verifique logs do servidor

**Erro ao buscar dados**

- Verifique se o token TikTok está válido
- Verifique se o avatar está autenticado em `/admin-tiktok.php`

---

## 📚 Referências

- [Documentação TikTok API](https://developers.tiktok.com/doc/login-kit-web)
- [Setup Completo](TIKTOK_SETUP.md)
- [Arquitetura](ARQUITETURA_TIKTOK.md)

---

**Status:** ✅ Coluna pronta para usar  
**Atualizado em:** 16 de maio de 2026
