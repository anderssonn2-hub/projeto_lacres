# Sistema de Lacres e Ofícios - Poupa Tempo

## Overview

Sistema para gerenciamento de lacres e ofícios do Poupa Tempo, desenvolvido em PHP puro para compatibilidade com Yii Framework antigo (PHP 5.3.3). O sistema permite gerar comprovantes de entrega por posto Poupatempo, salvar informações no banco de dados MySQL e imprimir os ofícios.

## Versao Atual: 6.0

## User Preferences

Preferred communication style: Simple, everyday language.

## System Architecture

### Frontend Architecture
- PHP puro com HTML/CSS/JavaScript inline
- Sem uso de frameworks frontend
- Compatível com navegadores antigos (IE8+)
- Formulários POST para envio de dados
- Campos editáveis convertidos em inputs para permitir salvamento

### Backend Architecture
- PHP 5.3.3+ compatível (sem sintaxe moderna como `[]`, closures complexas, traits)
- PDO para conexão com MySQL
- Padrão de arquitetura simples com arquivos PHP standalone
- Sistema de sessões para manter estado do usuário

### Data Storage
- MySQL (banco de dados: controle, host: 10.15.61.169)
- Tabelas principais:
  - `ciDespachos` - Cabeçalho dos ofícios/despachos
  - `ciDespachoItens` - Itens do despacho (postos, lacres, endereços)
  - `ciDespachoLotes` - Detalhamento por lotes
  - `ciPostosCsv` - Dados de postos carregados via CSV
  - `ciRegionais` - Informações das regionais/postos

### Key Design Patterns
- UPSERT (INSERT ou UPDATE) para salvamento de dados
- Hash SHA1 para identificação única de despachos (grupo + datas)
- Repopulação de formulários após salvamento para feedback visual

## Arquivos Principais

### lacres_novo.php
- Tela principal de gerenciamento de lacres
- Contém lógica de salvamento de lacres (`salvar_lacres_pt`)
- Contém lógica de salvamento de ofício (`salvar_oficio_pt`)
- Botão "Gerar Ofício Poupa Tempo" que abre `modelo_oficio_poupa_tempo.php`

### modelo_oficio_poupa_tempo.php
- Gera comprovantes de entrega por posto Poupatempo
- **ATUALIZADO**: Agora salva dados diretamente no banco de dados
- Campos editáveis: nome do posto, endereço, quantidade, número do lacre
- Botões:
  - "Gravar e Imprimir" - salva e imprime automaticamente
  - "Gravar Dados" - apenas salva
  - "Apenas Imprimir" - imprime sem salvar
- Lógica de UPSERT na tabela `ciDespachoItens`
- Repopulação automática dos campos após salvamento

### despachos_poupatempo.php
- Consulta de ofícios e lotes salvos
- Filtros por grupo, datas e lote
- Exibe detalhes dos itens e lotes

## Recent Changes

### 2025-11-28: VERSAO 6 - Correcoes de Contagem, PDF e Debug

#### consulta_producao.php - Versao 6
- Contagem de postos/carteiras corrigida: agora usa subqueries em ciDespachoLotes
- Funciona corretamente para CORREIOS (que nao usam ciDespachoItens)
- Dropdown de usuarios substituiu input texto para facilitar busca
- Nova coluna "PDF Oficio" com link para arquivo na rede
- Resumo do despacho mostra totais corretos baseados em ciDespachoLotes
- Mensagem informativa quando ciDespachoItens esta vazio (CORREIOS)

#### despachos_poupatempo.php - Versao 6
- Nova coluna "PDF" com link para arquivo na rede
- Resumo do despacho com totais corretos (postos, carteiras, lotes)
- Tipo do despacho exibido em destaque no titulo
- Mensagem informativa quando nao ha ciDespachoItens

#### lacres_novo.php - Versao 6
- Debug MELHORADO para rastrear etiquetas em todas as etapas
- Busca de etiqueta tenta multiplas variacoes de chave (normalizada, sem zeros, com prefixo)
- Garantia de que etiqueta e passada como STRING pura para o banco
- Log detalhado de lotes processados e etiquetas associadas

#### Link para PDF na Rede
- Formato: `Q:\cosep\IIPR\Oficios\{Mes} {Ano}\Oficio Lacres V8.2 - {DD_MM_YYYY}`
- Mes em portugues: Novembro, Dezembro, etc.
- Link usando protocolo `file:///` para acesso local

### 2025-11-28: VERSAO 5 - Conferencia, Etiquetas e Filtros Avancados

#### despachos_poupatempo.php
- Contagem de postos corrigida: agora usa ciDespachoLotes em vez de ciDespachoItens
- Cruzamento com tabela `conferencia_pacotes` para exibir status de conferencia
- Nova coluna "Conferido Por" mostra usuario que conferiu o lote
- Destaque em verde para lotes conferidos
- Nova coluna "Etiqueta Correios" na tabela de lotes

#### consulta_producao.php - Versao 5
- Novo filtro "Periodo Rapido": Hoje, Esta Semana, Este Mes, Este Ano
- Novo filtro por Usuario/Responsavel (usa EXISTS subquery para evitar duplicacao)
- Cruzamento com conferencia_pacotes (nlote) com filtro `cp.conf='S'`
- Exibicao de "Conferido Por" na tabela de lotes
- Estatisticas de producao por dia, mes, usuario

#### lacres_novo.php - Etiqueta Correios
- Debug adicionado ao salvamento de etiquetas para rastrear valores
- IMPORTANTE: A coluna `ciDespachoLotes.etiqueta_correios` DEVE ser VARCHAR(35)
- Se estiver como INT, os valores de 35 digitos serao truncados/zerados
- Comando para corrigir: `ALTER TABLE ciDespachoLotes MODIFY etiqueta_correios VARCHAR(35);`

#### Split Central IIPR
- Funcao `lock()` remove atributo `readonly` e classe CSS ao desbloquear
- Primeira linha do grupo 2 (abaixo do split) fica editavel
- Valores diferentes de etiqueta sao salvos para cada grupo
- Propagacao automatica funciona independentemente por coluna

### 2025-11-28: VERSAO 4 - Melhorias Gerais

#### modelo_oficio_poupa_tempo.php
- Corrigido problema de nomes de postos ficarem escondidos no input
- Adicionado `min-width:350px` na coluna Poupatempo
- Tabela com `table-layout:fixed` para larguras consistentes
- Inputs com largura 100% e min-width:320px para exibir nomes completos

#### lacres_novo.php - Split Melhorado
- Funcao `lock()` agora remove atributo `readonly` do HTML ao desbloquear
- Remove classe `etiqueta-central-readonly` quando input e desbloqueado
- Primeira linha do grupo 2 agora fica editavel corretamente apos split

#### Novo arquivo: consulta_producao.php
- Sistema de busca avancada de producao de cedulas
- Busca por etiqueta dos correios
- Busca por intervalo de datas (calendario)
- Busca por lote e posto
- Integracao com tabela conferencia_pacotes (status conferido/pendente)
- Estatisticas de producao por dia (ultimos 30 dias)
- Abas para alternar entre Despachos e Estatisticas
- Destaque em verde para lotes encontrados/conferidos
- Totais de carteiras, lotes e media diaria

#### despachos_poupatempo.php Atualizado
- Dropdown apenas com Poupa Tempo e Correios (removidos Capital, Central IIPR, Regionais)
- Campos de calendario para data inicial e final
- Campo de busca por etiqueta dos correios
- Botao Limpar para resetar filtros
- Logica de busca por intervalo de datas usando ciDespachoLotes.data_carga BETWEEN para pegar datas intermediarias

### 2025-11-27: Correções de compatibilidade ES5/IE8/IE9 em lacres_novo.php
- **classList.add/remove**: Substituídos por funções auxiliares `adicionarClasse()`/`removerClasse()` e `addClass()`/`removeClass()`
- **padStart**: Substituído por função customizada `padStart()` com loop while
- **forEach**: Todos convertidos para loops for tradicionais indexados
- **Array.from/.find()**: Convertidos para loops for com busca manual
- **Element.closest()**: Substituído por função `findParent()` com traversão manual
- **Array.prototype.indexOf.call()**: Substituído por função `findIndex()` com loop
- **let/const**: Todos substituídos por `var`
- **Arrow functions**: Substituídas por `function(){}`
- **Template literals**: Substituídos por concatenação de strings

### 2025-11-27: Melhorias de UX em lacres_novo.php
- Botões de limpar coluna (X) no cabeçalho de cada coluna (Lacre IIPR, Lacre Correios, Etiqueta Correios)
- Botões de salvamento pulsantes para indicar alterações não salvas
- Função SPLIT CENTRAL preserva valores diferentes durante impressão
- Botões de exclusão para postos CAPITAL e CENTRAL IIPR

### 2025-11-27: Implementação de salvamento para Correios em lacres_novo.php
- Adicionado handler `salvar_oficio_correios` para salvar dados de postos Correios
- Formulário HTML envolvendo tabelas CAPITAL, CENTRAL IIPR e REGIONAIS
- Inputs para lacre_iipr, lacre_correios e etiqueta_correios por posto
- Filtro SQL corrigido: `LOWER(TRIM(r.entrega)) = 'correios'` (antes era NOT LIKE 'poupa%tempo')
- Lógica de UPSERT para ciDespachos, ciDespachoItens e ciDespachoLotes
- Botões: "Gravar e Imprimir", "Gravar Dados" e "Apenas Imprimir"
- Botões de exclusão convertidos para JavaScript (evita formulários aninhados inválidos)
- Todo código mantido compatível com PHP 5.3.3

### 2025-11-27: Implementação de salvamento no modelo_oficio_poupa_tempo.php
- Campos `contenteditable` convertidos para inputs HTML
- Adicionada lógica de salvamento direto (ação `salvar_oficio_completo`)
- Implementado UPSERT para atualizar/inserir dados na tabela `ciDespachoItens`
- Adicionados botões "Gravar e Imprimir", "Gravar Dados" e "Apenas Imprimir"
- Implementada repopulação dos campos após salvamento bem-sucedido
- Mensagens de sucesso/erro exibidas na tela
- Impressão automática só ocorre após salvamento bem-sucedido
- Todo código mantido compatível com PHP 5.3.3

### 2025-11-27: Correções de bugs e melhoria do Split em lacres_novo.php
- **Exclusão de postos CAPITAL e CENTRAL IIPR**: Corrigido para verificar `$_POST[...] === '1'` em vez de `isset()` que retornava true com valor vazio
- **Erro "Tabela nao encontrada"**: Funções `limparColuna()`, `limparEtiquetasCentral()` e `sincronizarValoresSplit()` agora verificam ambos os IDs possíveis da tabela (`tabela-central-iipr` e `tblCentralIIPR`)
- **Impressão Correios sem Poupa Tempo**: Adicionado CSS `@media print` para ocultar tabelas de Poupa Tempo durante impressão de Correios
- **Split da Central com dois grupos independentes**:
  - Função `applyAt()` reescrita: GRUPO 1 (acima do split) e GRUPO 2 (abaixo do split) cada um com primeira linha editável e demais readonly
  - Nova função `configurarPropagacaoGrupos()`: propaga automaticamente os valores digitados para as demais linhas do mesmo grupo
  - Permite valores DIFERENTES de lacre correios e etiqueta correios para cada grupo

### 2025-11-27: VERSÃO 3 - Melhorias de Split, Salvamento e UX
- **Split corrigido para Etiqueta Correios**:
  - Função `configurarPropagacaoGrupos()` reescrita para propagar CADA COLUNA INDEPENDENTEMENTE
  - Cada coluna (Lacre Correios, Etiqueta Correios) agora tem seu próprio listener de propagação
  - Evita interferência entre colunas - alterar uma não afeta a outra
  - Handler global desativado quando split ativo (evita duplicação)
- **Botões X ocultos na impressão**: 
  - Adicionado CSS `@media print` para ocultar `.btn-limpar-coluna`, `.btn-limpar-coluna-header`, `button.btn-limpar` e `th button`
- **Salvamento de etiqueta_correios no banco**:
  - Adicionada coluna `etiqueta_correios` (INT 35) na tabela `ciDespachoLotes`
  - INSERT modificado para incluir a nova coluna
  - Captura as etiquetas por posto do formulário e associa a cada lote
- **Botões pulsantes inteligentes**:
  - Iniciam SEM pulsação (estado salvo)
  - Ativam pulsação somente quando há alteração nos inputs
  - Param de pulsar após salvamento bem-sucedido (chamada `marcarComoSalvo()`)
  - Voltam a pulsar se houver novas alterações após salvar
  - Nova função `capturarEstadoInicial()` para detectar mudanças

## Arquitetura de Dados do Split Central IIPR

### Estrutura de Dados
- **Cada posto** da Central IIPR tem um **código único** (ex: 041, 042, 043)
- O split divide a **lista de postos** em dois grupos visuais (superior/inferior)
- Cada grupo pode ter valores DIFERENTES de lacre_correios e etiqueta_correios
- Os postos NÃO são duplicados - cada código aparece apenas UMA vez

### Fluxo de Dados do Formulário
1. **HTML**: Cada input usa chave com prefixo `p_` para preservar zeros
   - Ex: `name="etiqueta_correios[p_041]"` 
2. **PHP**: A função `normalizarChavesPosto()` remove o prefixo e normaliza
   - Ex: `'p_041'` → `'041'`
3. **Banco**: Postos salvos com código normalizado de 3 dígitos

### Propagação no Split
- GRUPO 1 (acima do split): primeira linha editável, demais readonly e propagadas
- GRUPO 2 (abaixo do split): primeira linha editável, demais readonly e propagadas
- Cada COLUNA propaga independentemente (Lacre Correios e Etiqueta Correios)
- Valores aplicados IMEDIATAMENTE quando split é ativado (não apenas ao digitar)

### Normalização de Chaves PHP (Versão 3)
- Função `normalizarChavesPosto()` processa todos os arrays POST na captura
- Remove prefixo "p_" das chaves de etiqueta_correios
- Normaliza para formato "041" (3 dígitos com zeros à esquerda)
- Garante consistência entre todos os arrays: lacres_iipr, lacres_correios, etiquetas, nomes, grupos

## JavaScript Compatibility Helpers

Para manter compatibilidade com navegadores antigos (IE8/9), as seguintes funções auxiliares foram implementadas:

### Manipulação de Classes CSS
```javascript
function adicionarClasse(el, classe) { ... }  // Adiciona classe
function removerClasse(el, classe) { ... }    // Remove classe
```

### Navegação DOM
```javascript
function findParent(el, tagName) { ... }      // Substitui Element.closest()
function findIndex(list, element) { ... }     // Substitui Array.indexOf()
```

### Formatação de Strings
```javascript
function padStart(str, targetLen, padChar) { ... }  // Substitui String.padStart()
```

## External Dependencies

### Database Services
- MySQL em 10.15.61.169 (banco: controle)
- Usuário: controle_mat

### Infrastructure & Deployment
- Desenvolvido para rodar em servidor com Yii Framework (PHP 5.3.3)
- Não requer dependências externas além do PDO MySQL
