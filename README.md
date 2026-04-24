# 🏁 Corrida de Avatares

> Sistema profissional de monitoramento e gestão de seguidores em tempo real com dashboard interativo e gráficos de evolução.

## 📋 Visão Geral

O **Corrida de Avatares** é um sistema web completo desenvolvido em **PHP + MySQL** que permite gerenciar e acompanhar o crescimento de seguidores de múltiplos avatares/perfis. Com uma interface escura e moderna, oferece edição em tempo real (estilo Excel), dashboard com estatísticas e gráficos interativos.

### ✨ Características Principais

- 🎨 **Design Escuro e Moderno** - Interface elegante com gradientes violeta/indigo
- ⚡ **Edição em Tempo Real** - Inputs estilo Excel, salva ao pressionar Enter
- 📊 **Dashboard Dinâmico** - Visualize métricas em tempo real
- 📈 **Gráficos Interativos** - Acompanhe a evolução de seguidores ao longo do tempo
- 🎯 **Meta de Crescimento** - 2.000 seguidores por avatar (configurável)
- 💾 **Banco de Dados** - MySQL com histórico completo de alterações
- 📱 **Responsivo** - Funciona em desktop e mobile
- 🔒 **Validações** - Dados validados no front e back-end

---

## 🚀 Quick Start

### Pré-requisitos

- **PHP** 7.4+
- **MySQL** 8.0+
- **Apache** ou equivalente
- Navegador moderno (Chrome, Firefox, Edge, Safari)

### Instalação

1. **Clone o repositório**

```bash
git clone https://github.com/seu-usuario/corrida-avatares.git
cd corrida-avatares
```

2. **Configure o banco de dados**

```bash
# Importe o arquivo SQL
mysql -u root -p airbr_flow < model/airbr_flow.sql
```

3. **Configure as credenciais** (já definidas em `config/db.php`)

```php
$host = 'localhost';
$db_name = 'airbr_flow';
$user = 'root';
$password = 'caio1234';
```

4. **Inicie o servidor PHP local**

```bash
cd d:\Sites\corrida-avatares
php -S localhost:8000
```

5. **Abra no navegador**

```
http://localhost:8000
```

---

## 📂 Estrutura do Projeto

```
corrida-avatares/
├── index.php                    # Dashboard principal
├── config/
│   └── db.php                  # Configuração de conexão MySQL
├── api/
│   └── update-seguidores.php   # API REST para atualizar dados
├── css/
│   └── style.css               # Estilos escuros e responsivos
├── js/
│   ├── main.js                 # Lógica de interação com inputs
│   └── chart.js                # Gráficos com Chart.js
├── model/
│   └── airbr_flow.sql          # Dump do banco de dados
└── README.md                    # Este arquivo
```

---

## 🛠️ Componentes

### 1. **Frontend - index.php**

- Renderização da página HTML
- Dashboard com 5 cards de estatísticas
- Tabela interativa com rankings
- Container para gráficos
- Integração com Chart.js

**Funcionalidades:**

- Busca de últimos dados de cada avatar
- Cálculo de percentuais (seguidores/2000 \* 100)
- Data da última atualização
- Histórico completo de dados para gráficos

### 2. **Backend - config/db.php**

- Conexão MySQLi com o banco `airbr_flow`
- Charset UTF-8MB4
- Tratamento de erros

**Credenciais padrão:**

```
Servidor: localhost
Banco: airbr_flow
Usuário: root
Senha: caio1234
```

### 3. **API - api/update-seguidores.php**

Endpoint REST para atualizar seguidores.

**Método:** POST  
**Content-Type:** application/json

**Request:**

```json
{
	"id": 6,
	"seguidores": 150
}
```

**Response:**

```json
{
	"success": true,
	"message": "Seguidores atualizados com sucesso"
}
```

**Validações:**

- ✅ Seguidores > 0
- ✅ ID e seguidores são inteiros
- ✅ Atualiza `updated_at` automaticamente
- ✅ Prepared statements contra SQL injection

### 4. **Estilização - css/style.css**

Temas em variáveis CSS:

```css
--primary: #6366f1 (Indigo) --secondary: #8b5cf6 (Roxo) --dark-bg: #0f172a
	(Fundo escuro) --card-bg: #1e293b (Cards);
```

**Componentes estilizados:**

- Dashboard com 4-5 cards
- Tabela com hover effects
- Inputs editáveis
- Badges de posição (ouro, prata, bronze)
- Barras de progresso
- Container responsivo para gráficos

### 5. **Interatividade - js/main.js**

Gerencia a edição dos inputs.

**Eventos:**

- `Enter` - Salva o valor
- `Blur` - Detecta mudanças e salva automaticamente
- Feedback visual com cores
- Loading indicator

**Fluxo:**

1. Usuário clica no input de seguidores
2. Edita o valor
3. Pressiona Enter ou sai do campo
4. Enviado para API
5. Salvo no banco com novo `updated_at`
6. Página recarrega para refletir mudanças

### 6. **Gráficos - js/chart.js**

Gráfico linear com Chart.js 4.4.0.

**Características:**

- Uma linha por avatar
- Cores diferentes para cada perfil
- Evolução ao longo do tempo
- Tooltips interativos
- Responsivo (100% width)
- Eixo Y até 2000 (meta)
- Formatação de datas (DD/MM)

---

## 📊 Dashboard

### Cards de Estatísticas

| Card                    | Dados          | Atualização   |
| ----------------------- | -------------- | ------------- |
| **Total de Seguidores** | Soma de todos  | Em tempo real |
| **Média por Avatar**    | Total ÷ Qtd    | Em tempo real |
| **Progresso Total**     | (Total/Meta) % | Em tempo real |
| **Avatares Ativos**     | Contagem       | Estática      |
| **Última Atualização**  | Timestamp      | Ao editar     |

### Ranking de Seguidores

| Coluna         | Descrição              | Editável |
| -------------- | ---------------------- | -------- |
| **Posição**    | #1, #2, #3...          | ❌       |
| **Avatar**     | Nome com letra inicial | ❌       |
| **Seguidores** | Número de seguidores   | ✅       |
| **Progresso**  | Barra visual           | ❌       |
| **Percentual** | % da meta              | ❌       |

---

## 🎯 Meta de Seguidores

A meta padrão é **2.000 seguidores por avatar**, configurável em `index.php`:

```php
$META_SEGUIDORES = 2000;
```

O percentual é calculado como:

```
Percentual = (Seguidores / 2000) * 100
```

---

## 📱 Banco de Dados

### Tabela: `corrida`

```sql
CREATE TABLE corrida (
  id INT PRIMARY KEY AUTO_INCREMENT,
  ativo INT DEFAULT 1,
  created_at DATETIME,
  updated_at DATETIME,
  nome VARCHAR(255),
  seguidores INT,
  data DATETIME
) ENGINE=MyISAM CHARSET=utf8mb4;
```

### Avatares Iniciais

| ID  | Nome | Seguidores (inicial) |
| --- | ---- | -------------------- |
| 6   | Ana  | 105                  |
| 8   | Bia  | 86                   |
| 10  | Mel  | 61                   |
| 7   | Megg | 58                   |
| 9   | Luna | 25                   |

---

## 🎨 Paleta de Cores

```
Primária:     #6366f1 (Indigo)
Secundária:   #8b5cf6 (Roxo)
Fundo:        #0f172a (Muito escuro)
Cards:        #1e293b (Escuro)
Bordas:       #334155 (Cinza escuro)
Texto prim:   #f1f5f9 (Branco)
Texto sec:    #cbd5e1 (Cinza claro)
```

**Cores por Avatar (Gráfico):**

- Ana: Indigo #6366f1
- Bia: Roxo #8b5cf6
- Megg: Rosa #ec4899
- Luna: Amarelo #f59e0b
- Mel: Verde #10b981

---

## 🔧 Funcionalidades Detalhadas

### Edição em Tempo Real

```javascript
// Ao pressionar Enter no input de seguidores:
1. Validação (número válido)
2. POST para api/update-seguidores.php
3. Sucesso: feedback visual + reload
4. Erro: alertar e reverter valor
```

### Gráfico de Evolução

```javascript
// Mostra histórico de seguidores
1. Agrupa dados por avatar
2. Normaliza datas
3. Renderiza com Chart.js
4. Atualiza automaticamente após edições
```

### Dashboard em Tempo Real

```php
// Sempre busca dados mais recentes
1. MAX(id) por avatar (evita duplicatas)
2. Ordena por seguidores DESC
3. Calcula percentuais
4. Formata datas
```

---

## 🐛 Troubleshooting

### Problema: CSS/JS não carregam

- **Solução:** Verifique se os caminhos em `index.php` são relativos (`./css/style.css`)

### Problema: Gráfico em branco

- **Solução:** Verifique se Chart.js está carregado (`<script src="https://cdn.jsdelivr.net/...">`)
- **Alternativa:** Use CDN local

### Problema: Dados não salvam

- **Solução:** Verifique conexão MySQL em `config/db.php`
- **Comandos úteis:**

```bash
mysql -u root -p airbr_flow -e "SELECT * FROM corrida;"
```

### Problema: Inputs aparecem, mas não editam

- **Solução:** Verifique console do navegador (F12)
- **Debug:** Adicione `console.log(data)` em `js/main.js`

---

## 📝 API Reference

### POST `/api/update-seguidores.php`

**Headers:**

```
Content-Type: application/json
```

**Body:**

```json
{
	"id": 6,
	"seguidores": 200
}
```

**Success (200):**

```json
{
	"success": true,
	"message": "Seguidores atualizados com sucesso"
}
```

**Errors:**

| Status | Erro                              |
| ------ | --------------------------------- |
| 400    | Dados inválidos ou seguidores < 0 |
| 405    | Método não é POST                 |
| 500    | Erro no banco de dados            |

---

## 🎓 Tecnologias Utilizadas

- **PHP** 8.2+ - Backend
- **MySQL** 8.0+ - Banco de dados
- **Chart.js** 4.4.0 - Gráficos
- **HTML5** - Markup
- **CSS3** - Estilos (custom properties)
- **JavaScript** (Vanilla) - Interatividade

---

## 📄 Licença

Este projeto é código aberto. Use livremente!

---

## 👤 Autor

Desenvolvido com ❤️ por [Seu Nome]

---

## 🚀 Próximas Melhorias (Ideas)

- [ ] Autenticação de usuários
- [ ] Filtros por data (histórico)
- [ ] Export para CSV/PDF
- [ ] Notificações quando atingir meta
- [ ] Comparação entre períodos
- [ ] Badges/achievements
- [ ] Dark/Light mode toggle
- [ ] Múltiplas competições
- [ ] API completa REST com CRUD
- [ ] Testes automatizados

---

## 📞 Suporte

Para dúvidas ou problemas, abra uma issue no GitHub!
