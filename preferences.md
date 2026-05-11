## Exemplo de importação de CSS e JS

<style><?php require 'css/estilo.css'; ?></style>
<script><?php require 'js/script.js'; ?></script>

# Preferences

- Indentação: sempre TAB
- Foco em Float para layouts simples
- nunca usar 100vw, use sempre 100%
- CSS: usar unidades VW para medidas responsivas; evitar VH e PX quando possível; prefira percentuais quando aplicável
- 1vw = largura da tela em px dividido por 100
- Cores: usar `white` e `black` explicitamente quando necessário fora do `:root`; todas as demais cores devem vir de variáveis em `:root` (ex.: `--karazan-bordo`, `--karazan-cream`)
- Fontes e espaçamento: usar as variáveis de `:root` para família, tamanho e altura de linha. Exemplos: `--ibarra`, `--linbiolinum`, `--segoe`, `--fontSizeTitulo`, `--lineHeightTitulo`, `--fontSizeTexto`, `--lineHeightTexto`, `--fontSizeBtn`
- Layout: padrão é `float:left` para seções simples; usar flexbox/grid apenas quando necessário
- Comentários: não adicionar comentários dentro de arquivos CSS; documentação do projeto e diretrizes devem ficar em `templates/preferences.md` ou em um README separado
- Propriedades: não usar shorthands genéricos (ex.: evite `background` e `border`); use propriedades específicas como `background-image`, `background-size`, `background-position`, `background-color`, `border-width`, `border-style`, `border-color`
- Start/End: cada arquivo view/css/js deve conter apenas os marcadores de início/fim no topo/rodapé do próprio arquivo como comentários, seguindo o padrão: `/* Start - caminho/arquivo.ext */` e `/* End - caminho/arquivo.ext */`. Fora esses marcadores, evite comentários inline em CSS.
- Branco/Preto literais: quando uma cor literal for equivalente a `#fff` ou `#ffffff` use `white`; quando for `#000` ou `#000000` use `black`.
- Formatação CSS: evitar linhas em branco desnecessárias; mantenha CSS conciso para facilitar inclusão inline quando necessário
- Importação: preferir includes/require para injetar CSS/JS nas views (ex.: `<?php require 'css/arquivo.css'; ?>`)

Notas rápidas de implementação

- Só é permitido comentário inline em CSS para os marcadores Start/End no topo e rodapé do arquivo. Comentários explicativos, TO-DOs ou exemplos devem ficar neste arquivo (`templates/preferences.md`) ou em `README.md`.
- Prefira usar variáveis do `:root` para qualquer cor, família de fontes, tamanhos e espaçamentos. Evite valores literais espalhados.
- Evite shorthands genéricos: use propriedades específicas para melhor controle e consistência.

Essas diretrizes devem ser seguidas em todos os projetos e tarefas. Se houver exceção, documentar no README do projeto.

## Modelo de componentes (exemplo sem comentários inline em CSS)

<!-- Start - caminho/do/arquivo.php -->
<?php // PHP aqui ?>
<style>
	/* CSS do componente — sem comentários inline */
</style>
<section>
<!-- HTML do componente -->
</section>
<script>
	// JS do componente — sem comentários inline
</script>
<!-- End - caminho/do/arquivo.php -->

## Modelo de CSS ou JS

/_ Start - caminho/arquivo.css _/
/_ CSS ou JS aqui — sem comentários inline _/
/_ End - caminho/arquivo.css _/

## Sempre colocar no README.md os dados do autor

Desenvolvido por Caio Loureiro.
Site: https://digitalmd.com.br
Informações do Desenvolvedor: https://digitalmd.com.br/caioloureiro/cv/

## Arquivos Markdown (MD)

- **Todos os arquivos `.md` criados por inteligência artificial, ou arquivos `.md` de teste e debug devem ficar SEMPRE dentro da pasta `/markdown`**
- Nunca criar arquivos `.md` fora dessa pasta
- Isso mantém a documentação do projeto organizada e centralizada

---

# MODUS OPERANDI - ANÁLISE DETALHADA DE PROJETOS

## 1. ARQUITETURA GERAL DO PROJETO

### Estrutura de Pastas Principal

O projeto segue uma arquitetura **modular e descentralizada**, com separação clara entre camadas:

- **`/api`** - API REST com 90+ endpoints que retornam dados em JSON
- **`/model`** - Camada de dados (queries SQL e processamento de banco de dados)
- **`/controller`** - Funções utilitárias, componentes, validações e lógica compartilhada
- **`/routes`** - Roteamento de páginas e lógica de fluxo
- **`/view`** - Templates HTML (componentes reutilizáveis)
- **`/admin`** - Painel administrativo com autenticação, CRUD e gestão de conteúdo
- **`/css`** - Folhas de estilo (via `require` inline nas views)
- **`/js`** - Scripts (via `require` inline nas views)
- **`/templates`** - Arquivos de configuração e documentação
- **`/arquivos`, `/uploads`, `/formularios_arquivos`, etc** - Armazenamento de mídia

### Tipo de Projeto

**Sistema** com 80+ módulos pré-construídos incluindo:

- Notícias e Galeria
- Licitações, Concursos e Editais
- Transparência e Legislação
- Diário Oficial e e-SIC
- Formulários e Enquetes
- Newsletter e Comunicação
- Conselhos Municipais e Organograma
- Downloads e Links Úteis

---

## 2. PADRÕES DE DESENVOLVIMENTO

### 2.1 Inclusão de Arquivos (Require/Include)

O padrão é usar **`require` inline** para injetar CSS e JS diretamente no HTML:

```php
<!-- View - HTML -->
<style><?php require 'css/componente.css'; ?></style>
<section class="componente">
    <!-- HTML -->
</section>
<script><?php require 'js/componente.js'; ?></script>
```

**Vantagens:**

- CSS/JS inline reduz requisições HTTP
- Facilita distribuição de componentes autossuficientes
- Ideal para templates simples que serão incluídos via require

### 2.2 Fluxo de Requisições HTTP

**Página Estática (`index.php`):**

```
1. index.php carrega routes/main.php (configurações gerais)
2. routes/main.php requer model/conexao.php (conecta ao BD)
3. index.php requer routes/model.php (carrega dados)
4. routes/home.php OU routes/{pagina}.php renderiza o conteúdo
5. Views incluem CSS/JS inline via require
```

**API (`/api/*.php`):**

```
1. /api/{endpoint}.php requer conexao.php
2. /api/{endpoint}.php requer model/{entidade}.php
3. Executa SQL e monta array
4. Retorna JSON via header "application/json"
```

**Painel Admin (`/admin/index.php`):**

```
1. Verifica autenticação (controller/auth.php)
2. Carrega controller/funcoes.php (funções utilitárias)
3. Carrega routes/model.php (dados)
4. Renderiza templates do /admin/templates/
```

### 2.3 Conexão ao Banco de Dados (Dinâmica)

O projeto detecta automaticamente o ambiente:

```php
<?php
if($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '192.168.0.2') {
    require 'model/conexao-off.php';  // Desenvolvimento local
} else {
    require 'model/conexao-on.php';   // Produção
}
?>
```

Isso permite ter duas configurações sem alterar código.

---

## 3. CONVENÇÕES DE CÓDIGO

### 3.1 Comentários Start/End

Cada arquivo (CSS, JS, PHP) deve ter marcadores no topo e rodapé:

```php
/* Start - view/cabecalho.php */
<!-- Conteúdo -->
/* End - view/cabecalho.php */
```

**Dentro de CSS/JS:** apenas os marcadores Start/End no topo/rodapé. Nenhum comentário inline explicativo.

### 3.2 Funções Utilitárias em `/controller/funcoes.php`

Funções globais para debug e manipulação:

```php
function dd($variavel) { }          // Debug & Die
function mostrar_array($variavel) { } // Exibe array formatado
function console($variavel) { }      // Log em console JS
```

Usadas frequentemente para verificar dados em desenvolvimento.

### 3.3 Variáveis Globais

Uso extensivo de variáveis globais para dados compartilhados:

```php
global $noticias_array;
global $paginas;
global $logo;
global $conn;  // Conexão MySQLi
```

### 3.4 Padrão de Model (`/model/{entidade}.php`)

Todos os arquivos em `/model/` seguem uma estrutura padrão e uniforme:

```php
<?php

$sql_{entidade} = "SELECT * FROM {tabela} WHERE ativo = 1";

${entidade}_tabela = $conn->query($sql_{entidade});

${entidade}_array = array();

while (${entidade}_montado = ${entidade}_tabela->fetch_assoc()) {

	${entidade}_array[] = ${entidade}_montado;
}

//dd( ${entidade}_array );
```

**Padrão explicado:**

1. **Linha 1:** Abre tag PHP
2. **Linha 3:** Query SQL com WHERE ativo = 1 (filtra apenas registros ativos)
3. **Linha 5:** Executa query no banco usando `$conn->query()`
4. **Linha 7:** Inicializa array vazio com sufixo `_array`
5. **Linha 9-11:** Loop while que transforma resultado em array de objetos associativos
6. **Linha 11:** Cada linha é adicionada ao array com `[]`
7. **Linha 13:** Comentário de debug (dd = Debug & Die)

**Nomenclatura:**

- `$sql_{entidade}` - Variável SQL (ex: `$sql_exemplo`)
- `${entidade}_tabela` - Resultado da query (ex: `$exemplo_tabela`)
- `${entidade}_montado` - Variável temporária do loop (ex: `$exemplo_montado`)
- `${entidade}_array` - Array final retornado (ex: `$exemplo_array`)

**Exemplo Real (exemplo):**

```php
<?php

$sql_exemplo = "SELECT * FROM exemplo WHERE ativo = 1";

$exemplo_tabela = $conn->query($sql_exemplo);

$exemplo_array = array();

while ($exemplo_montado = $exemplo_tabela->fetch_assoc()) {

	$exemplo_array[] = $exemplo_montado;
}

//dd( $exemplo_array );
```

**Variações permitidas:**

- Trocar `WHERE ativo = 1` por outras condições conforme necessário
- Usar `ORDER BY` para ordenação
- Usar `JOIN` para relacionamentos
- Mais de um `$sql_` dentro do mesmo arquivo (para múltiplas queries)

**Resultado:**

O `$array` final é:

- Declarado como `global` nas views/controllers que o usam
- Retornado como JSON pelos endpoints da API
- Iterado em loops `foreach` nas views

---

## 4. ESTRUTURA DE COMPONENTES

### 4.1 Componentes Reutilizáveis

Componentes são views simples incluídas em múltiplas páginas:

**Exemplo: `view/cabecalho.php`**

```php
<!-- Start - view/cabecalho.php !-->
<style><?php require 'css/cabecalho.css'; ?></style>
<nav class="cabecalho">
    <!-- HTML do componente -->
</nav>
<?php require 'view/menu-mobile.php'; ?>
<!-- End - view/cabecalho.php !-->
```

Padrão:

1. Marcadores Start/End
2. CSS via require inline
3. HTML do componente
4. Inclusão de subcomponentes
5. Sem comentários inline

### 4.2 Módulos Administrativos

Sistema modular onde cada tipo de conteúdo tem:

- **API endpoint** em `/api/{entidade}.php`
- **Model** em `/model/{entidade}.php`
- **View** em `/view/{entidade}.php` (ou em `/admin/view/`)
- **Controller** em `/admin/controller/` (quando necessário)

Exemplo: Notícias

- `/api/noticias.php` - endpoint que retorna JSON
- `/model/noticias.php` - query e processamento
- `/view/noticias.php` - renderização para usuário
- `/admin/view/noticias/` - painel de gestão

---

## 5. GESTÃO DE DADOS

### 5.1 Fonte de Dados

Duas fontes de dados são carregadas:

1. **Banco de Dados** - MySQL/MariaDB com múltiplas tabelas
2. **Arrays PHP** - em `/model/arrays.php` para dados estáticos ou de configuração

### 5.2 API como Fonte Centralizada

A `/api` expõe 90+ endpoints que consolidam dados. Exemplos:

- `/api/noticias.php` - todas as notícias ativas
- `/api/licitacoes.php` - licitações ativas
- `/api/menu.php` - menu estruturado
- `/api/settings.php` - configurações globais

### 5.3 Roteamento de Páginas Dinâmicas

O `index.php` determina qual página renderizar:

```php
if(!isset($_GET['pagina'])) {
    require 'routes/home.php';  // Homepage
} else {
    // Verifica em páginas_fixas (definidas em array)
    // Depois em páginas dinâmicas (do banco de dados)
}
```

Permite adicionar novas páginas sem alterar código.

---

## 6. PADRÕES DE INCLUÃO DE ARQUIVOS

### 6.1 Padrão Geral

```php
// No início do arquivo principal (index.php)
require "routes/main.php";        // Config globais
require "routes/model.php";       // Carrega dados
require "view/seo.php";           // Meta tags

// No meio do HTML
require "view/cabecalho.php";     // Componente
require "view/footer.php";        // Componente

// Ao final
require "view/scripts-bottom.php"; // Scripts globais
```

### 6.2 Caminho Relativo vs Absoluto

Usa caminhos **relativos** do contexto do arquivo:

```php
<?php
// Em index.php (raiz)
require "view/cabecalho.php";

// Em /admin/index.php
$raiz_admin = '';
require $raiz_admin . 'view/login.php';
```

---

## 7. SEGURANÇA

### 7.1 Proteção contra Brute Force

Implementado no `/admin/index.php`:

```php
// Limita tentativas de login a 4 por 5 minutos
if($tentativas > 4) {
    // Bloqueia e exibe mensagem
}
```

### 7.2 Whitelist

Arquivo `/controller/whitelist.php` verifica permissões de usuário.

### 7.3 Autenticação

Sistema de cookies para sessão:

```php
$_COOKIE['cidade_ADMIN_SESSION_usuario']
$_COOKIE['cidade_ADMIN_SESSION_senha']
```

### 7.4 Exclusão de Registros

**Nunca usar DELETE em SQL.** Sempre usar `UPDATE ativo = 0` para "excluir" registros.

Razões:

- Mantém histórico e auditoria dos dados
- Permite recuperação de informações deletadas acidentalmente
- Facilita rastreamento e compliance regulatório
- Compatível com sistema de logs em `rastrear_usuario`

Exemplo:

```php
// ✗ NUNCA fazer isso:
$sql = "DELETE FROM tabela WHERE id = " . $id;

// ✓ SEMPRE fazer assim:
$sql = "UPDATE tabela SET ativo = 0 WHERE id = " . $id;
```

---

## 8. CSS E JAVASCRIPT

### 8.1 Sistema de Variáveis CSS

Todas as cores, fontes e tamanhos definidos em `:root`:

```css
:root {
	--karazan-bordo: #8b0000;
	--karazan-cream: #f5e6d3;
	--segoe: "Segoe UI", sans-serif;
	--fontSizeTitulo: 1.5vw;
	--lineHeightTitulo: 1.8vw;
}
```

### 8.2 Layout com Float

Preferência por `float: left` para layouts simples, flexbox/grid apenas quando necessário.

### 8.3 Unidades Responsivas

- VW para medidas (1vw = 1% da largura da tela)
- Percentuais para layouts flexíveis
- Evitar PX quando possível
- Nunca usar 100vw (usar 100%)

### 8.4 Media Queries

Breakpoint principal: `@media only screen and (max-width:1024px) and (orientation: portrait)`

---

## 9. WORKFLOW TÍPICO

### Para Adicionar um Novo Módulo:

1. **Criar tabela no banco** com campos: `id`, `titulo`, `descricao`, `ativo`, `data_criacao`, `data_atualizacao`

2. **Criar arquivo API** em `/api/novo_modulo.php`:

```php
<?php
require 'conexao.php';
require '../model/novo_modulo.php';
header("Content-Type: application/json");
echo json_encode($novo_modulo_array);
?>
```

3. **Criar model** em `/model/novo_modulo.php`:

```php
<?php
$sql = "SELECT * FROM novo_modulo WHERE ativo = 1";
$resultado = $conn->query($sql);
$novo_modulo_array = array();
while($linha = $resultado->fetch_assoc()) {
    $novo_modulo_array[] = $linha;
}
?>
```

4. **Criar view** em `/view/novo_modulo.php`:

```php
<!-- Start - view/novo_modulo.php -->
<style><?php require 'css/novo_modulo.css'; ?></style>
<section class="novo_modulo">
    <?php foreach($novo_modulo_array as $item) { ?>
        <article>
            <h3><?= $item['titulo'] ?></h3>
            <p><?= $item['descricao'] ?></p>
        </article>
    <?php } ?>
</section>
<script><?php require 'js/novo_modulo.js'; ?></script>
<!-- End - view/novo_modulo.php -->
```

5. **Adicionar rota** em `/routes/novo_modulo.php` se necessário

6. **Integrar ao menu** em `/model/menu.php` ou `/api/menu.php`

---

## 10. FERRAMENTAS E INTEGRAÇÕES

- **Banco de Dados:** MySQL/MariaDB (MySQLi)
- **Autenticação:** Cookies de sessão
- **Arquivos:** Upload em `/uploads/` e pastas específicas
- **Analytics:** Google Analytics integrado
- **Acessibilidade:** VLibras, leitor de voz
- **SEO:** Meta tags dinâmicas em `view/seo.php`
- **Formulários:** Recaptcha e validação PHP
- **Redes Sociais:** Integração com endpoints

---

## 11. CARACTERÍSTICAS PRINCIPAIS DO PROJETO

✅ **Portal completo para prefeituras** com 80+ módulos pré-construídos
✅ **Responsivo em mobile/tablet/desktop** com CSS baseado em VW
✅ **API REST** com 90+ endpoints que retornam JSON
✅ **Painel administrativo** com CRUD para todos os módulos
✅ **Sistema de permissões** para diferentes usuários
✅ **Transparência municipal** (licitações, diário oficial, etc)
✅ **Comunicação** (notícias, newsletter, formulários)
✅ **Conformidade legal** (LAI, LGPD, e-SIC, Compliance Eleitoral)
✅ **Gestão centralizada** de todas as funções municipais
✅ **Backup automático** e logs de auditoria

---

## 12. AMBIENTE DE DESENVOLVIMENTO

- **Sistema Operacional:** Windows/Linux/Mac
- **Editor:** VS Code
- **Servidor Local:** XAMPP/WAMP com PHP 7.4+ e MySQL
- **Versionamento:** Git
- **Banco:** Dump disponível em `/model/prefeitura_zerado.sql`

Ao trabalhar neste projeto, manter sempre os padrões acima para garantir consistência e facilitar manutenção futura.
