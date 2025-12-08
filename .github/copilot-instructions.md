## Visão Rápida

Este repositório é uma aplicação PHP procedural voltada para a geração e controle de "ofícios" (despachos) e lacres para dois fluxos principais: POUPA TEMPO e CORREIOS. O código é tradicional (estilo procedural), usa PDO para acesso a MySQL e gera páginas HTML/JS diretamente nos arquivos PHP.

## Arquitetura & Fluxos principais

- **Entradas / páginas importantes**:
  - `lacres_novo.php` — tela principal de criação/edição de ofícios; trata SALVAR de ofícios e lacres (ações via `POST['acao']`, ex: `salvar_oficio_pt`, `salvar_lacres_pt`, `salvar_oficio_correios`).
  - `consulta_producao.php` — busca/consulta de produções; usa filtros por data, lote, posto, etiqueta.
  - `modelo_oficio_poupa_tempo.php` — gera / imprime ofício por posto para Poupa Tempo.
  - `src/index.php` — pequena rota/placeholder para o app (ver `src/` para código exposto ao servidor).

- **Banco de dados**:
  - Bancos usados (hardcoded): `controle`, `servico`, `contrsos` com host `10.15.61.169` (ver `lacres_novo.php`, `consulta_producao.php`, `modelo_oficio_poupa_tempo.php`).
  - Tabelas principais: `ciDespachos`, `ciDespachoItens`, `ciDespachoLotes`, `ciPostosCsv`, `ciRegionais`, `conferencia_pacotes`.
  - Padrões SQL: uso de `sha1($grupo . '|' . $datasStr)` como chave para upsert de despacho; várias partes usam `EXISTS` ou subqueries para evitar duplicação.

## Convenções de implementação

- Estilo procedural, compatibilidade histórica documentada (alguns arquivos mencionam PHP 5.3.3). Evite trocar para sintaxe moderníssima sem validar execução no ambiente alvo.
- Segurança / encoding: funções utilitárias simples como `function e($s)` executam `htmlspecialchars(..., 'UTF-8')` — preserve esse padrão ao imprimir conteúdo.
- Sessão: várias telas dependem de `$_SESSION` (ex: `$_SESSION['etiquetas']`, `$_SESSION['id_despacho_poupa_tempo']`). Tenha cuidado ao limpar/alterar chaves de sessão.
- A aplicação frequentemente envia HTML/JS diretamente (ex.: `echo "<script>alert(...)")`). Alterações no fluxo front-end podem requerer ajustes diretos nesses arquivos.

## Pontos críticos ao editar/corrigir

- **Credenciais no código**: as strings de conexão PDO estão hardcoded; ao desenvolver, não comite alterações que deixem credenciais expostas. Preferir mover para variáveis de ambiente se for alterar.
- **Migrations / Schema**: não há migrations; as SQL dependem de colunas e tipos (ex.: uso de `CAST(l.lote AS UNSIGNED)` em junções). Antes de alterar colunas, verifique todas as consultas que assumem formato específico.
- **Limites de compatibilidade**: testes manuais no servidor web são necessários — não há suíte automatizada. Para alterações, abrir página no navegador ou rodar um servidor PHP local.

## Como executar / debug rápido

- Rodar com PHP embutido (para desenvolvimento local):
  - `cd /path/to/repo && php -S localhost:8000 -t .`
  - Em seguida abrir `http://localhost:8000/lacres_novo.php` e `http://localhost:8000/consulta_producao.php`.
- Ambiente real: a aplicação espera um servidor web com acesso ao host MySQL `10.15.61.169` ou equivalente. Se não houver acesso, prepare um banco local com mesmas tabelas.
- Debug: muitos arquivos têm trechos de debug ativáveis via query string (ex: `?debug_pt=1` em `modelo_oficio_poupa_tempo.php`). Use isso para inspecionar estruturas interceptadas.

## Exemplos de padrões para PRs

- Ao alterar consultas, mantenha `PDO::prepare` + parâmetros vinculados para evitar injeção.
- Para buscar/formatar datas, reuse a função `converterDataSQL()` (exemplo em `consulta_producao.php`).
- Para inserir/atualizar ofícios: respeite o fluxo de `hash_chave` + DELETE/INSERT usado em `lacres_novo.php` e `modelo_oficio_poupa_tempo.php` para evitar duplicação de registros.

## Histórico de Versões (lacres_novo.php)

- **v8.8**: Corrige captura de lacres e etiquetas dos Correios (HTML + POST + gravação)
  - Introduz arrays alinhados enviados pelo frontend: `posto_lacres[]`, `lacre_iipr[]`, `lacre_correios[]`, `etiqueta_correios[]`
  - Adiciona função JS que prepara esses arrays antes do submit (`prepararLacresCorreiosParaSubmit`)
  - Backend usa esses arrays para montar `mapaLacresPorPosto` e gravar `etiquetaiipr`, `etiquetacorreios`, `etiqueta_correios` em `ciDespachoLotes`
  - Mantém comportamento de validação de etiquetas, SPLIT e auto-foco

- **v8.6**: Grava lacre IIPR, lacre Correios e etiqueta Correios em ciDespachoLotes (CORREIOS SOMENTE)
  - CORREIOS: Mapa de lacres por posto criado a partir de arrays POST normalizados
  - INSERT em ciDespachoLotes atualizado com 3 novos campos:
    - etiquetaiipr (INT): lacre IIPR do malote
    - etiquetacorreios (INT): lacre Correios do malote
    - etiqueta_correios (VARCHAR(35)): código de barras do malote
  - Cada lote recebe os lacres correspondentes ao seu posto
  - PT (Poupa Tempo): será tratado em versão futura separada
  - Debug: add_debug registra mapa de lacres para verificação

- **v8.5**: Persistência confirmada de lacres e etiquetas em ciDespachoLotes e ciDespachoItens
  - CORREIOS: lacres (IIPR e Correios) + etiquetas capturados do POST → ciDespachoItens + ciDespachoLotes
  - PT (Poupa Tempo): lacre_iipr capturado do POST → ciDespachoItens via modelo_oficio_poupa_tempo.php
  - Fluxo PT: utiliza `modelo_oficio_poupa_tempo.php` com `acao=salvar_oficio_completo` (não usa `salvar_oficio_pt`)
  - Fluxo Correios: utiliza handler `salvar_oficio_correios` em `lacres_novo.php` com normalização de chaves (prefixo `p_` removido automaticamente)
  - Validação: use `consulta_producao.php` para verificar dados gravados (filtrar por etiqueta, lote, posto)

- **v8.4**: Auto-avançamento para próximo input de etiqueta após leitura bem-sucedida
  - Scanner detection: 35 dígitos dispara `blur()` automático
  - Listeners: `input` event (detecta 35 dígitos) + `blur` event (validação)

- **v8.3**: Seis melhorias (validação duplicata, CSS print, contagem correta de postos, etc.)
  - Botão SPLIT oculto na impressão com classe `no-print`
  - Validação de etiquetas duplicadas (CAPITAL + REGIONAIS, excluindo CENTRAL IIPR e PT)
  - Contagem correta de postos distintos vs. lotes gravados

- **v8.0-8.1**: SPLIT funcionalidade para CENTRAL IIPR postos com JavaScript puro (sem AJAX)

## Onde procurar quando algo quebra

- Falha de conexão ao DB: verifique conexões em `lacres_novo.php`, `consulta_producao.php`, `modelo_oficio_poupa_tempo.php`.
- Erros de negócio (duplicidade de etiquetas, issues com lotes): revisar lógica em `lacres_novo.php` (várias seções de insert/update/transaction).
- Problemas de render/JS: arquivos PHP emitem HTML/JS inline — procurar no topo do mesmo arquivo ou no fim do arquivo onde `echo "<script>..."` é usado.

## Exemplos concretos que ajudam

- `lacres_novo.php` — transação ao salvar lacres do Poupa Tempo:
  - busca `id_despacho` e faz `SELECT COUNT(*)` antes de `INSERT` ou `UPDATE` por posto.
- `consulta_producao.php` — usa subqueries para contar `num_postos` e `total_carteiras` e `GROUP BY d.id` para evitar duplicatas.

Se quiser, eu posso ajustar o tom, adicionar notas de segurança mais detalhadas (ex.: mover credenciais para `.env`) ou incluir comandos de verificação do schema SQL. Deseja que eu aplique essas mudanças agora ou prefira revisar o conteúdo antes? 
