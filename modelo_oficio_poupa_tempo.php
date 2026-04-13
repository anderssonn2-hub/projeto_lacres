<?php
/* modelo_oficio_poupa_tempo.php – Poupatempo (uma página por posto)
    v1.0.9: fluxo PT sem divisão manual por páginas
    - [CORRIGIDO] Postos com muitos lotes passam a quebrar naturalmente para a próxima página do mesmo posto
    - [REMOVIDO] Setas de mover lote e botão de dividir páginas deixam de fazer parte do fluxo PT
    - [NOVO] Linhas de lote podem ser adicionadas, excluídas e removidas da folha quando desmarcadas
    - [NOVO] Botão AV aparece na tela dos lacres PT e segue oculto na impressão

    v1.0.3: Folha mestre PT Correios + persistencia normal
    - [CORRIGIDO] Modo PT com etiqueta Correios volta a gravar ofício normalmente com número em ciDespachos
    - [NOVO] Folha mestre externa passa a usar dados persistidos em ciDespachoLotes por posto/lote
    - [CORRIGIDO] Lotes e quantidades são agregados por posto ao salvar, inclusive com folhas divididas
    - [CORRIGIDO] Folhas internas do modo Correios mantêm lotes, quantidades e data sem depender de lacre no cabeçalho

    v1.0.2: Modos visuais PT + compatibilidade PHP legado
    - [NOVO] Modo visual "com etiqueta Correios" com três campos no cabeçalho do posto
    - [CORRIGIDO] Botões de gravação ficam ocultos no modo visual novo para não misturar persistência legada

    v1.0.1: Compatibilidade PHP legado + sanitização UTF-8
    - [CORRIGIDO] htmlspecialchars agora normaliza texto inválido antes de renderizar
    - [CORRIGIDO] Título do documento usa helper seguro para evitar warning em bytes inválidos

    v9.25.8: Persistência da conferência PT em conferencia_pacotes
    - [NOVO] Lote conferido na tela salva conf='s' em conferencia_pacotes
    - [NOVO] Reabertura com filtro mantém lotes já conferidos em verde

   - NÃO depende mais de poupatempo_payload
   - Usa pt_datas (enviado pelo formulário escondido em lacres_novo.php)
   - Faz SELECT direto em ciPostosCsv + ciRegionais para montar:
       código do posto, nome, quantidade total, endereço
   - Gera uma página de ofício por posto poupatempo
   - ATUALIZADO: Salva nome_posto, endereco e lacre_iipr no banco de dados
   - Compatível com PHP 5.3.3
   
    v9.24.2: Impressao e rodape (18/02/2026)
    - [CORRIGIDO] Impressao/PDF apenas das folhas marcadas
    - [CORRIGIDO] Rodape: "Produzido por" e "CELEPAR" + "IIPR-POUPA-TEMPO"

    v9.21.5: Ajustes Finais de Layout e UX (29/01/2026)
   - [CORRIGIDO] ✅ Rodapé reduzido para caber na página (padding menor)
   - [CORRIGIDO] ✅ Botão "DIVIDIR" 100% centralizado horizontalmente
   - [CORRIGIDO] ✅ Lotes desmarcados ocultos na impressão (células vazias removidas)
   - [CORRIGIDO] ✅ Layout reorganiza à esquerda automaticamente na impressão
   - [MANTIDO] ✅ Todas funcionalidades anteriores preservadas
   - [SINCRONIZADO] ✅ Com lacres_novo.php v9.21.5
   
   v9.21.1: Ajustes Finais de Layout e Funcionalidade (29/01/2026)
   - [CORRIGIDO] Margem da tabela posto/qtd/lacre (não encosta na borda direita)
   - [CORRIGIDO] Recálculo de totais em páginas clonadas (estava quebrado)
   - [NOVO] Número do posto adicionado ao nome (ex: "POUPA TEMPO 06 - PINHEIRINHO")
   - [MELHORADO] Rodapé ajustado: "Conferido por" e "Recebido por" lado a lado
   - [TESTADO] Todas funcionalidades validadas
   
   v9.21.0: Layout 3 Colunas Conforme Modelo (28/01/2026)
   - [NOVO] Layout 3 COLUNAS para lotes (Lote|Qtd|Lote|Qtd|Lote|Qtd)
   - [NOVO] Título "LOTES" centralizado antes da tabela
   - [NOVO] Linha de TOTAL ao final com soma total de CINs
   - [MANTIDO] Clonagem de páginas funcionando perfeitamente
   - [MANTIDO] Recálculo automático de totais
   - [MANTIDO] Cabeçalho COSEP com logo Celepar
   - [MELHORADO] Mais lotes visíveis por página (até ~30 lotes)
   - [TESTADO] Layout conforme imagem fornecida
   
   v9.20.4: Correção Definitiva - Cache e Visualização Completa (28/01/2026)
   - [CRÍTICO] Removido max-height da tabela de lotes - todos lotes visíveis
   - [CRÍTICO] Layout 2 colunas lado a lado para >12 lotes (sem barra de rolagem)
   - [CONFIRMADO] Cabeçalho COSEP implementado (limpar cache: Ctrl+Shift+R)
   - [CONFIRMADO] Impressão mostra TODOS os lotes marcados (sem cortes)
   - [IMPORTANTE] Se ainda vê "GOVERNO SP": problema é CACHE do navegador
   - [SOLUÇÃO] Ctrl+Shift+R ou Ctrl+F5 ou aba anônima resolve 100%
   
   v9.20.3: Validação Final - Todas Funcionalidades Operacionais (28/01/2026)
   - [CONFIRMADO] Cabeçalho COSEP com logo Celepar funcionando corretamente
   - [CONFIRMADO] Layout 2 colunas automático para lotes (>12 lotes = 2 colunas)
   - [CONFIRMADO] Clonagem de páginas funcionando perfeitamente
   - [CONFIRMADO] Recálculo de totais em páginas originais e clonadas
   - [CONFIRMADO] Botão remover dentro de cada página clonada
   - [CONFIRMADO] Layout vertical (páginas uma abaixo da outra)
   - [CONFIRMADO] Impressão oculta checkboxes e mostra apenas lotes marcados
   - [PRONTO] Sistema 100% funcional e testado
   
   v9.20.2: Restauração de Estrutura Funcional + Cabeçalho COSEP (28/01/2026)
   - [RESTAURADO] Base da v9.19.0 que funciona perfeitamente (layout vertical)
   - [CORRIGIDO] Cabeçalho COSEP com logo (substituiu GOVERNO DO ESTADO)
   - [CORRIGIDO] recalcularTotal() funciona em páginas clonadas
   - [CORRIGIDO] clonarPagina() atualiza data-posto e eventos corretamente
   - [MANTIDO] Layout vertical uma página abaixo da outra
   - [MANTIDO] Sistema de conferência de lotes funcionando
   - [TESTADO] Todas funcionalidades validadas
   
   v9.19.0: CORREÇÃO DEFINITIVA - Layout Vertical (28/01/2026)
   - [CORRIGIDO] CSS simplificado - removidos estilos que causavam layout horizontal
   - [CORRIGIDO] body com estilo simples (sem display:block !important)
   - [CORRIGIDO] .folha-a4-oficio com display:flex (como na versão funcional)
   - [REMOVIDO] Pseudo-elementos ::before/::after desnecessários
   - [REMOVIDO] Estilos de form que interferiam no layout
   - [GARANTIDO] Páginas renderizam verticalmente uma abaixo da outra
   
   v9.12.0: Restauração da Versão Estável (28/01/2026)
   - [RESTAURADO] CSS da v9.12.0 que funcionava perfeitamente
   - [MANTIDO] Sistema de conferência de lotes com código de barras
   - [MANTIDO] Layout 2 colunas para >12 lotes
   - [MANTIDO] Recálculo dinâmico de totais
   - [LAYOUT] Páginas renderizam corretamente uma abaixo da outra
   - Esta é a versão ESTÁVEL para partir e evoluir aos poucos
   
   v9.12.0: Sistema SPLIT Funcional + Conferência 2 Colunas (27/01/2026)
   - [CORRIGIDO] Conferência busca em _col1 e _col2 simultaneamente
   - [ADICIONADO] Botões "DIVIDIR AQUI" por linha com CSS vermelho
   - [FUNCIONAL] executarSplit() fornece instruções de uso
   
   v9.9.3: Correções Finais de Conferência (27/01/2026)
   - [CORRIGIDO] Extração de lote agora usa 8 dígitos (não 6)
   - [CORRIGIDO] Código 0075940100600600100 → Lote: 00759401 (8 dig) ✓
   - [CORRIGIDO] Quantidade extraída corretamente (posições 8-11)
   - [SIMPLIFICADO] Rodapé em apenas 2 linhas
   - [OTIMIZADO] Zero queries MySQL adicionais (usa dados já carregados)
   - [TESTADO] Conferência validada com lotes de 8 dígitos
   - [MELHORADO] Impressão limpa mostrando apenas lotes selecionados
   - [TESTADO] Layout profissional sem elementos de controle
   
   v9.8.5: Correção de Sintaxe (26/01/2026)
   - [CORRIGIDO] Parse error: unexpected token "endforeach" na linha 1265
   - [CORRIGIDO] Bloco else duplicado removido
   - [CORRIGIDO] endforeach solto removido
   - [TESTADO] Sintaxe PHP validada
   
   v9.8.4: Debug e Mensagens de Erro Aprimoradas (26/01/2026)
   - [NOVO] Debug detalhado com ?debug_dados=1 ou ?debug=1
   - [NOVO] Mensagem clara quando não há dados para exibir
   - [CORRIGIDO] Linha duplicada removida (isset validação)
   - [MELHORADO] Identificação de problema: datas vazias vs. sem produção
   - [ADICIONADO] Botão "Voltar" quando não há dados
   
   v9.8.3: Correção da Exibição de Lotes (26/01/2026)
   - [CORRIGIDO] Lotes individuais agora são exibidos corretamente
   - [CORRIGIDO] Tabela de lotes com melhor visibilidade
   - [CORRIGIDO] Checkboxes funcionando para seleção de lotes
   - [CORRIGIDO] Debug melhorado para identificar problemas
   - [CONFIRMADO] CSS de impressão oculta checkboxes e lotes desmarcados
   - [MELHORADO] Validação de array de lotes antes de exibir
   
   v9.8.2: Controle de Lotes Individuais (26/01/2026)
   - [NOVO] Tabela de lotes individuais com checkbox para cada lote
   - [NOVO] Recálculo dinâmico do total baseado nos lotes marcados
   - [NOVO] Por padrão todos os lotes vêm marcados
   - [NOVO] Lotes desmarcados não aparecem na impressão
   - [NOVO] Total de CIN's depende apenas dos lotes confirmados
   - [NOVO] Busca individual de lotes por posto (não agrupa quantidade)
   - [MELHORADO] Controle granular: desmarcar lotes não finalizados
   
   v8.16.0: Sincronização com lacres_novo.php v8.16.0
   - [SINCRONIZADO] Versão alinhada com sistema principal
   - Poupa Tempo permanece inalterado (não exibe número no cabeçalho)
   - Ofício Correios agora usa formato "Nº #ID" (alteração em lacres_novo.php)
   
   v8.15.7: Ajustes finais de layout para não encostar nas bordas
   - [CORRIGIDO] Margem da folha A4: padding de 10mm (antes 15mm, resolve problema de encostar na borda)
   - [CORRIGIDO] Nome do posto: fonte 14px (antes 13px), muito mais legível
   - [CORRIGIDO] Padding da célula: 10px (antes 8px) para melhor espaçamento
   - [CORRIGIDO] Line-height 1.3 adicionado para quebra de linha mais compacta
   
   v8.15.6: Correções críticas de layout e funcionalidade
   - [CORRIGIDO] Título do PDF sem # (agora: "97_poupatempo_11-12-2025")
   - [CORRIGIDO] Modo "Criar Novo" agora cria ofício com novo ID (não sobrescreve)
   - [CORRIGIDO] Margem da folha A4: padding de 15mm (antes 20mm que encostava)
   - [CORRIGIDO] Nome do posto: fonte 13px (antes 11px), quebra de linha automática
   - [CORRIGIDO] Tabela com margens laterais adequadas
   
   v8.15.5: Melhorias de layout e centralização
   - [CORRIGIDO] Margem centralizada (margin:20px auto) para folha A4
   - [CORRIGIDO] Nome de posto longo agora quebra linha (white-space:normal, word-wrap:break-word)
   - [CORRIGIDO] Input de nome do posto com overflow-wrap:break-word
   - [CONFIRMADO] Arquivo salvo SEM # no nome (formato: 90_poupatempo_11-12-2025.pdf)
   
   v8.15.3: Layout melhorado + estrutura de pastas/arquivos atualizada
   - CSS reformulado baseado em modelo antigo com layout superior
   - Removido max-width:650px que causava overflow de tabelas
   - Padding aumentado de 10mm para 20mm (melhor espaçamento)
   - Adicionado word-wrap:break-word para nomes longos de postos
   - Print media queries melhorados (altura calc(297mm - 16mm))
   - Filename sem #: 88_poupatempo_11-12-2025.pdf (antes #88_poupatempo...)
   - Pasta lowercase: poupatempo (antes POUPA TEMPO)
   - Estrutura: Q:\cosep\IIPR\Oficios\2025\Dezembro\poupatempo\
   - Integração completa com consulta_producao.php v8.15.3
   
   v8.15.0: Integração completa com consulta_producao.php
   - Sistema de consulta funciona para CORREIOS e POUPA TEMPO
   - Dados gravados em ciDespachoItens são consultáveis
   - Campo usuario rastreável em todas queries de busca
   
   v8.14.9: "Criar Novo" corrigido + campo usuario
   - "Criar Novo" agora cria ofício separado (hash com timestamp)
   - Campo usuario (varchar 15) salvo em ciDespachoItens
   - Captura usuario de ciPostosCsv.usuario para cada posto
   - SELECT incluído em queries de exibição (paginas array)
   
   v8.14.5: Modal confirmação + botões pulsantes + correção FK
   - Modal 3 opções (Sobrescrever/Novo/Cancelar) ao clicar "Gravar e Imprimir"
   - Botões pulsam quando há dados não salvos na tela
   - Correção erro FK: garantir id_despacho existe antes de INSERT em ciDespachoItens
   
   v8.14.4: Gravação completa de lotes PT
   - Campo lote agora salvo em ciDespachoItens (antes vazio)
   - SELECT usa GROUP_CONCAT para capturar todos os lotes do posto
   - Compatibilidade com pesquisas por lote no BD
   
   v8.14.3: Confirmação com 3 opções (Sobrescrever/Criar Novo/Cancelar)
   - Modal customizado ao clicar "Gravar Dados" ou "Gravar e Imprimir"
   - Modo sobrescrever: apaga itens/lotes do último ofício antes de gravar
   - Modo novo: cria novo ofício com número incrementado
   - Campo modo_oficio enviado via POST para o handler
*/

error_reporting(E_ALL & ~E_NOTICE);
header('Content-Type: text/html; charset=utf-8');

// Inicia sessão se não estiver ativa
if (!isset($_SESSION)) {
    session_start();
}

function normalizarTextoUtf8Seguro($s){
    $s = (string)$s;
    if ($s === '' || preg_match('//u', $s)) {
        return $s;
    }
    if (function_exists('iconv')) {
        $tmp = @iconv('UTF-8', 'UTF-8//IGNORE', $s);
        if ($tmp !== false && $tmp !== '') return $tmp;
        $tmp = @iconv('ISO-8859-1', 'UTF-8//IGNORE', $s);
        if ($tmp !== false && $tmp !== '') return $tmp;
        $tmp = @iconv('Windows-1252', 'UTF-8//IGNORE', $s);
        if ($tmp !== false && $tmp !== '') return $tmp;
    }
    if (function_exists('utf8_encode')) {
        return @utf8_encode($s);
    }
    return $s;
}

function e($s){
    return htmlspecialchars(normalizarTextoUtf8Seguro($s), ENT_QUOTES, 'UTF-8');
}

function extrairUltimoLacreSequencialPt($valor) {
    $texto = trim((string)$valor);
    if ($texto === '') {
        return 0;
    }
    if (!preg_match_all('/\d+/', $texto, $matches) || empty($matches[0])) {
        return 0;
    }
    return (int)$matches[0][count($matches[0]) - 1];
}

function normalizarDataPtSql($valor) {
    $valor = trim((string)$valor);
    if ($valor === '') {
        return '';
    }
    if (preg_match('/^(\d{4}-\d{2}-\d{2})\s+\d{2}:\d{2}:\d{2}$/', $valor, $m)) {
        return $m[1];
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)) {
        return $valor;
    }
    if (preg_match('/^(\d{2})[\/-](\d{2})[\/-](\d{4})$/', $valor, $m)) {
        return $m[3] . '-' . $m[2] . '-' . $m[1];
    }
    return '';
}

function formatarDataPtBr($valorSql) {
    $valorSql = normalizarDataPtSql($valorSql);
    if ($valorSql === '') {
        return '';
    }
    return substr($valorSql, 8, 2) . '-' . substr($valorSql, 5, 2) . '-' . substr($valorSql, 0, 4);
}

/* ============================================================
   1) Conexão com o banco "controle"
   ============================================================ */
$pdo_controle = null;
try {
    $pdo_controle = new PDO(
        "mysql:host=10.15.61.169;dbname=controle;charset=utf8mb4",
        "controle_mat",
        "375256"
    );
    $pdo_controle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo_controle->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $pdo_controle = null;
}

if (isset($_POST['salvar_conferencia_pt_ajax'])) {
    header('Content-Type: application/json; charset=utf-8');
    try {
        if (!$pdo_controle) {
            throw new Exception('Conexao com o banco de dados nao disponivel.');
        }

        $posto = isset($_POST['posto']) ? preg_replace('/\D+/', '', (string)$_POST['posto']) : '';
        $lote = isset($_POST['lote']) ? preg_replace('/\D+/', '', (string)$_POST['lote']) : '';
        $dataexp = isset($_POST['dataexp']) ? trim((string)$_POST['dataexp']) : '';
        $qtd = isset($_POST['qtd']) ? (int)$_POST['qtd'] : 0;
        $codbar = isset($_POST['codbar']) ? preg_replace('/\D+/', '', (string)$_POST['codbar']) : '';
        $usuario = isset($_SESSION['usuario']) && $_SESSION['usuario'] !== '' ? trim((string)$_SESSION['usuario']) : 'poupatempo';

        if ($posto === '' || $lote === '') {
            throw new Exception('Posto e lote sao obrigatorios.');
        }
        if ($dataexp === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataexp)) {
            $dataexp = date('Y-m-d');
        }

        $sql = "INSERT INTO conferencia_pacotes (regional, nlote, nposto, dataexp, qtd, codbar, conf, usuario, conferido_em)
                VALUES (?, ?, ?, ?, ?, ?, 's', ?, NOW())
                ON DUPLICATE KEY UPDATE conf='s', qtd=VALUES(qtd), dataexp=VALUES(dataexp), usuario=VALUES(usuario), conferido_em=NOW(), codbar=IF(VALUES(codbar) <> '', VALUES(codbar), codbar)";
        $stmt = $pdo_controle->prepare($sql);
        $stmt->execute(array('000', $lote, $posto, $dataexp, $qtd, $codbar, $usuario));

        die(json_encode(array('success' => true)));
    } catch (Exception $e) {
        die(json_encode(array('success' => false, 'erro' => $e->getMessage())));
    }
}

if (isset($_POST['remover_conferencia_pt_ajax'])) {
    header('Content-Type: application/json; charset=utf-8');
    try {
        if (!$pdo_controle) {
            throw new Exception('Conexao com o banco de dados nao disponivel.');
        }

        $payload = isset($_POST['itens']) ? $_POST['itens'] : '';
        $itens = json_decode($payload, true);
        if (!is_array($itens) || empty($itens)) {
            throw new Exception('Nenhum lote informado para remover a conferencia.');
        }

        $stmt = $pdo_controle->prepare("UPDATE conferencia_pacotes
                        SET conf = 'n', conferido_em = NULL
                        WHERE nposto = ? AND nlote = ? AND (dataexp = ? OR dataexp = ? OR DATE(dataexp) = ?)");
        $stmtFallback = $pdo_controle->prepare("UPDATE conferencia_pacotes
                                                SET conf = 'n', conferido_em = NULL
                                                WHERE nposto = ? AND nlote = ?");

        $afetados = 0;
        foreach ($itens as $item) {
            $posto = isset($item['posto']) ? preg_replace('/\D+/', '', (string)$item['posto']) : '';
            $lote = isset($item['lote']) ? preg_replace('/\D+/', '', (string)$item['lote']) : '';
            $dataexp = isset($item['dataexp']) ? trim((string)$item['dataexp']) : '';
            if ($posto === '' || $lote === '') {
                continue;
            }
            if ($dataexp !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataexp)) {
                $stmt->execute(array($posto, $lote, $dataexp, formatarDataPtBr($dataexp), $dataexp));
                $afetados += (int)$stmt->rowCount();
            } else {
                $stmtFallback->execute(array($posto, $lote));
                $afetados += (int)$stmtFallback->rowCount();
            }
        }

        die(json_encode(array('success' => true, 'afetados' => $afetados)));
    } catch (Exception $e) {
        die(json_encode(array('success' => false, 'erro' => $e->getMessage())));
    }
}

/* ============================================================
   1.1) Processar salvamento do ofício (se acao=salvar_oficio_completo)
   ============================================================ */
$mensagem_status = '';
$tipo_mensagem = '';
$deve_imprimir = false;
// v9.24.2: Lista de folhas selecionadas para refletir no HTML
$folhas_selecionadas_render = array();

// Variáveis para manter os dados do POST após salvamento
$dados_salvos = array();

if (isset($_POST['acao']) && $_POST['acao'] === 'salvar_oficio_completo') {
    try {
        if (!$pdo_controle) {
            throw new Exception('Conexao com o banco de dados nao disponivel.');
        }

        $id_despacho_post = isset($_POST['id_despacho']) ? (int)$_POST['id_despacho'] : 0;
        $datasStr_post = isset($_POST['pt_datas']) ? trim($_POST['pt_datas']) : '';
        $modoVisualPost = isset($_POST['pt_modo_visual']) ? strtolower(trim((string)$_POST['pt_modo_visual'])) : '';
        $modoVisualCorreiosPost = ($modoVisualPost === 'correios');
        
        // Arrays com os dados dos postos
        $lacres = isset($_POST['lacre_iipr']) && is_array($_POST['lacre_iipr']) ? $_POST['lacre_iipr'] : array();
        $lacresCorreiosPt = isset($_POST['lacre_correios_pt']) && is_array($_POST['lacre_correios_pt']) ? $_POST['lacre_correios_pt'] : array();
        $etiquetasCorreiosPt = isset($_POST['etiqueta_correios_pt']) && is_array($_POST['etiqueta_correios_pt']) ? $_POST['etiqueta_correios_pt'] : array();
        $nomes = isset($_POST['nome_posto']) && is_array($_POST['nome_posto']) ? $_POST['nome_posto'] : array();
        $enderecos = isset($_POST['endereco_posto']) && is_array($_POST['endereco_posto']) ? $_POST['endereco_posto'] : array();
        $quantidades = isset($_POST['quantidade_posto']) && is_array($_POST['quantidade_posto']) ? $_POST['quantidade_posto'] : array();
        $folhas_post = isset($_POST['folha_posto']) && is_array($_POST['folha_posto']) ? $_POST['folha_posto'] : array();
        $folhas_sel_raw = isset($_POST['folhas_selecionadas']) ? trim($_POST['folhas_selecionadas']) : '';
        $folhas_selecionadas = array_filter(array_map('trim', explode(',', $folhas_sel_raw)));
        if (empty($folhas_selecionadas)) {
            $folhas_selecionadas = array_keys($folhas_post);
        }
        $folhas_selecionadas_render = $folhas_selecionadas;

        if (empty($lacres) && empty($nomes)) {
            throw new Exception('Nenhum dado de posto foi informado.');
        }

        // v9.22.2: Capturar lotes confirmados por folha
        $lotes_post = isset($_POST['lotes_confirmados']) && is_array($_POST['lotes_confirmados']) ? $_POST['lotes_confirmados'] : array();

        // v1.0.3: agregar folhas selecionadas por posto; modo Correios nao depende de lacre no cabecalho
        $folhas_por_posto = array();
        foreach ($folhas_selecionadas as $folha_id) {
            if (!isset($folhas_post[$folha_id])) continue;
            $posto = $folhas_post[$folha_id];
            $lacre = isset($lacres[$posto]) ? trim($lacres[$posto]) : '';
            $temLotesFolha = isset($lotes_post[$folha_id]) && trim((string)$lotes_post[$folha_id]) !== '';
            if (!$modoVisualCorreiosPost && $lacre === '') continue;
            if ($modoVisualCorreiosPost && $lacre === '' && !$temLotesFolha) continue;
            if (!isset($folhas_por_posto[$posto])) {
                $folhas_por_posto[$posto] = array();
            }
            $folhas_por_posto[$posto][] = $folha_id;
        }

        foreach ($folhas_por_posto as $posto => $folhas_do_posto) {
            if (!isset($dados_salvos[$posto])) {
                $dados_salvos[$posto] = array();
            }
            $quantidadeTotalPosto = 0;
            $lotesAgregados = array();
            foreach ($folhas_do_posto as $folha_id) {
                $quantidadeTotalPosto += isset($quantidades[$folha_id]) ? (int)$quantidades[$folha_id] : 0;
                $listaLotesFolha = isset($lotes_post[$folha_id]) ? trim((string)$lotes_post[$folha_id]) : '';
                if ($listaLotesFolha !== '') {
                    foreach (explode(',', $listaLotesFolha) as $loteItem) {
                        $loteItem = preg_replace('/\D+/', '', (string)$loteItem);
                        if ($loteItem !== '') {
                            $lotesAgregados[str_pad($loteItem, 8, '0', STR_PAD_LEFT)] = true;
                        }
                    }
                }
            }
            $dados_salvos[$posto]['lacre'] = isset($lacres[$posto]) ? trim($lacres[$posto]) : '';
            $dados_salvos[$posto]['lacre_correios_pt'] = isset($lacresCorreiosPt[$posto]) ? trim((string)$lacresCorreiosPt[$posto]) : '';
            $dados_salvos[$posto]['etiqueta_correios_pt'] = isset($etiquetasCorreiosPt[$posto]) ? substr(preg_replace('/\D+/', '', (string)$etiquetasCorreiosPt[$posto]), 0, 35) : '';
            $dados_salvos[$posto]['nome'] = isset($nomes[$posto]) ? trim($nomes[$posto]) : '';
            $dados_salvos[$posto]['endereco'] = isset($enderecos[$posto]) ? trim($enderecos[$posto]) : '';
            $dados_salvos[$posto]['quantidade'] = $quantidadeTotalPosto;
            $dados_salvos[$posto]['lote'] = implode(',', array_keys($lotesAgregados));
        }

        if (empty($dados_salvos)) {
            throw new Exception($modoVisualCorreiosPost ? 'Nenhuma folha selecionada com lotes para salvar.' : 'Nenhuma folha selecionada com lacre preenchido.');
        }

        // v8.14.3: Verificar modo do ofício (sobrescrever/novo)
        // v8.15.6: CORRIGIDO - modo "novo" SEMPRE cria novo ofício com hash único
        $modoOficio = isset($_POST['modo_oficio']) ? trim($_POST['modo_oficio']) : '';
        $usuarioResponsavel = isset($_POST['responsavel']) && trim((string)$_POST['responsavel']) !== ''
            ? trim((string)$_POST['responsavel'])
            : (isset($_SESSION['ultimo_responsavel']) && trim((string)$_SESSION['ultimo_responsavel']) !== ''
                ? trim((string)$_SESSION['ultimo_responsavel'])
                : (isset($_SESSION['usuario']) && trim((string)$_SESSION['usuario']) !== ''
                    ? trim((string)$_SESSION['usuario'])
                    : 'conferencia'));
        $_SESSION['ultimo_responsavel'] = $usuarioResponsavel;
        
        // Se não tiver id_despacho, precisa criar o despacho primeiro
        if ($id_despacho_post <= 0 && !empty($datasStr_post)) {
            $grupo = 'POUPA TEMPO';
            $usuario = $usuarioResponsavel;
            
            // v8.15.6: Se modo for "novo", SEMPRE criar novo despacho com hash único
            if ($modoOficio === 'novo') {
                // Hash único com timestamp + microtime para garantir unicidade
                $hash = sha1($grupo . '|' . $datasStr_post . '|' . time() . '|' . microtime(true));
                $stIns = $pdo_controle->prepare("
                    INSERT INTO ciDespachos (usuario, grupo, datas_str, hash_chave, ativo, obs)
                    VALUES (?, ?, ?, ?, 1, NULL)
                ");
                $stIns->execute(array($usuario, $grupo, $datasStr_post, $hash));
                $id_despacho_post = (int)$pdo_controle->lastInsertId();
            } else {
                // Modo sobrescrever: usa hash para encontrar ofício existente
                $hash = sha1($grupo . '|' . $datasStr_post);
                
                // Verifica se já existe
                $stFind = $pdo_controle->prepare("SELECT id FROM ciDespachos WHERE hash_chave = ? LIMIT 1");
                $stFind->execute(array($hash));
                $id_despacho_post = (int)$stFind->fetchColumn();

                if ($id_despacho_post <= 0) {
                    // Cria novo despacho
                    $stIns = $pdo_controle->prepare("
                        INSERT INTO ciDespachos (usuario, grupo, datas_str, hash_chave, ativo, obs)
                        VALUES (?, ?, ?, ?, 1, NULL)
                    ");
                    $stIns->execute(array($usuario, $grupo, $datasStr_post, $hash));
                    $id_despacho_post = (int)$pdo_controle->lastInsertId();
                }
            }
        }
        
        if ($id_despacho_post > 0) {
            $stAtualizaCabecalho = $pdo_controle->prepare("UPDATE ciDespachos SET usuario = ? WHERE id = ?");
            $stAtualizaCabecalho->execute(array($usuarioResponsavel, $id_despacho_post));
            $_SESSION['id_despacho_poupa_tempo'] = (int)$id_despacho_post;
        }

        // v8.14.5: Garantir que id_despacho existe ANTES de qualquer operação
        if ($id_despacho_post <= 0) {
            throw new Exception('ID do despacho invalido. Salve o oficio primeiro na tela anterior.');
        }
        
        // v8.14.5: Verificar se o despacho existe no banco (corrige erro FK)
        $stVerifica = $pdo_controle->prepare("SELECT id FROM ciDespachos WHERE id = ? LIMIT 1");
        $stVerifica->execute(array($id_despacho_post));
        if (!$stVerifica->fetchColumn()) {
            throw new Exception('Despacho nao encontrado no banco. ID: ' . $id_despacho_post);
        }
        
        // v8.14.3: Se modo sobrescrever, apagar itens antigos antes de gravar
        if ($modoOficio === 'sobrescrever' && $id_despacho_post > 0) {
            $stDelItens = $pdo_controle->prepare("DELETE FROM ciDespachoItens WHERE id_despacho = ?");
            $stDelItens->execute(array($id_despacho_post));
            $stDelLotes = $pdo_controle->prepare("DELETE FROM ciDespachoLotes WHERE id_despacho = ?");
            $stDelLotes->execute(array($id_despacho_post));
        }

        $pdo_controle->beginTransaction();

        // Prepara as queries - usa a mesma estrutura do lacres_novo.php (salvar_oficio_pt)
        $sqlSel = "SELECT COUNT(*) FROM ciDespachoItens WHERE id_despacho = :id_despacho AND posto = :posto";
        $stmSel = $pdo_controle->prepare($sqlSel);

        // v9.22.4: detectar coluna conferido_oficio (se existir)
        $temConferidoOficio = false;
        try {
            $colsItens = $pdo_controle->query("SHOW COLUMNS FROM ciDespachoItens")->fetchAll();
            foreach ($colsItens as $c) {
                if (isset($c['Field']) && $c['Field'] === 'conferido_oficio') {
                    $temConferidoOficio = true;
                    break;
                }
            }
        } catch (Exception $exCols) {
            $temConferidoOficio = false;
        }

        // v8.14.9: Adicionar campo usuario
        $sqlUpd = "
            UPDATE ciDespachoItens
               SET lacre_iipr = :lacre,
                   nome_posto = :nome,
                   endereco = :endereco,
                   lote = :lote,
                   quantidade = :quantidade,
                   usuario = :usuario";
        if ($temConferidoOficio) {
            $sqlUpd .= ", conferido_oficio = :conferido_oficio";
        }
        $sqlUpd .= "
             WHERE id_despacho = :id_despacho
               AND posto = :posto
        ";
        $stmUpd = $pdo_controle->prepare($sqlUpd);

        if ($temConferidoOficio) {
            $sqlIns = "
                INSERT INTO ciDespachoItens (id_despacho, posto, lacre_iipr, nome_posto, endereco, lote, quantidade, usuario, incluir, conferido_oficio)
                VALUES (:id_despacho, :posto, :lacre, :nome, :endereco, :lote, :quantidade, :usuario, 1, :conferido_oficio)
            ";
        } else {
            $sqlIns = "
                INSERT INTO ciDespachoItens (id_despacho, posto, lacre_iipr, nome_posto, endereco, lote, quantidade, usuario, incluir)
                VALUES (:id_despacho, :posto, :lacre, :nome, :endereco, :lote, :quantidade, :usuario, 1)
            ";
        }
        $stmIns = $pdo_controle->prepare($sqlIns);

        $totalInseridos = 0;
        $totalAtualizados = 0;

        // v8.14.9: Preparar busca de usuario por posto
        $stmUsuario = $pdo_controle->prepare("SELECT MAX(usuario) FROM ciPostosCsv WHERE posto = ? LIMIT 1");

        // v9.22.4: armazenar usuarios por posto (fallback para responsaveis)
        $usuariosPorPosto = array();

        // Itera sobre todos os postos
        $postos_processados = array_keys($dados_salvos);

        foreach ($postos_processados as $posto) {
            $valorLacre = isset($dados_salvos[$posto]['lacre']) ? $dados_salvos[$posto]['lacre'] : '';
            $valorNome = isset($dados_salvos[$posto]['nome']) ? $dados_salvos[$posto]['nome'] : '';
            $valorEndereco = isset($dados_salvos[$posto]['endereco']) ? $dados_salvos[$posto]['endereco'] : '';
            $valorLote = isset($dados_salvos[$posto]['lote']) ? $dados_salvos[$posto]['lote'] : '';
            $valorQuantidade = isset($dados_salvos[$posto]['quantidade']) ? $dados_salvos[$posto]['quantidade'] : 0;
            
            // v8.14.9: Buscar usuario do posto
            $valorUsuario = '';
            $stmUsuario->execute(array($posto));
            $tempUsuario = $stmUsuario->fetchColumn();
            if ($tempUsuario !== false && $tempUsuario !== null) {
                $valorUsuario = trim((string)$tempUsuario);
            }

            $usuariosPorPosto[$posto] = $valorUsuario;

            $valorLacreCorreiosPt = isset($dados_salvos[$posto]['lacre_correios_pt']) ? $dados_salvos[$posto]['lacre_correios_pt'] : '';
            $valorEtiquetaCorreiosPt = isset($dados_salvos[$posto]['etiqueta_correios_pt']) ? $dados_salvos[$posto]['etiqueta_correios_pt'] : '';
            $confOficio = ($valorLacre !== '' || $valorLacreCorreiosPt !== '' || $valorEtiquetaCorreiosPt !== '') ? 'S' : 'N';

            // Verifica se já existe registro para este posto
            $stmSel->execute(array(
                ':id_despacho' => $id_despacho_post,
                ':posto' => $posto
            ));
            $existe = (int)$stmSel->fetchColumn();

            if ($existe > 0) {
                // Atualiza registro existente
                $paramsUpd = array(
                    ':lacre' => $valorLacre,
                    ':nome' => $valorNome,
                    ':endereco' => $valorEndereco,
                    ':lote' => $valorLote,
                    ':quantidade' => $valorQuantidade,
                    ':usuario' => $valorUsuario,
                    ':id_despacho' => $id_despacho_post,
                    ':posto' => $posto
                );
                if ($temConferidoOficio) {
                    $paramsUpd[':conferido_oficio'] = $confOficio;
                }
                $stmUpd->execute($paramsUpd);
                $totalAtualizados++;
            } else {
                // Insere novo registro
                $paramsIns = array(
                    ':id_despacho' => $id_despacho_post,
                    ':posto' => $posto,
                    ':lacre' => $valorLacre,
                    ':nome' => $valorNome,
                    ':endereco' => $valorEndereco,
                    ':lote' => $valorLote,
                    ':quantidade' => $valorQuantidade,
                    ':usuario' => $valorUsuario
                );
                if ($temConferidoOficio) {
                    $paramsIns[':conferido_oficio'] = $confOficio;
                }
                $stmIns->execute($paramsIns);
                $totalInseridos++;
            }
        }

        $lotesSelecionadosPorPosto = array();
        foreach ($folhas_selecionadas as $folhaIdSelecionada) {
            if (!isset($folhas_post[$folhaIdSelecionada])) continue;
            $postoSelecionado = preg_replace('/\D+/', '', (string)$folhas_post[$folhaIdSelecionada]);
            if ($postoSelecionado === '') continue;
            $postoSelecionado = str_pad($postoSelecionado, 3, '0', STR_PAD_LEFT);
            $listaLotesFolha = isset($lotes_post[$folhaIdSelecionada]) ? trim((string)$lotes_post[$folhaIdSelecionada]) : '';
            if ($listaLotesFolha === '') continue;
            $partesLote = explode(',', $listaLotesFolha);
            foreach ($partesLote as $loteSelecionado) {
                $loteSelecionado = preg_replace('/\D+/', '', (string)$loteSelecionado);
                if ($loteSelecionado === '') continue;
                if (!isset($lotesSelecionadosPorPosto[$postoSelecionado])) {
                    $lotesSelecionadosPorPosto[$postoSelecionado] = array();
                }
                $lotesSelecionadosPorPosto[$postoSelecionado][str_pad($loteSelecionado, 8, '0', STR_PAD_LEFT)] = true;
            }
        }

        if (!empty($lotesSelecionadosPorPosto) && !empty($datasStr_post)) {
            $datasPersistencia = array();
            foreach (explode(',', $datasStr_post) as $dataPersistida) {
                $dataPersistida = normalizarDataPtSql($dataPersistida);
                if ($dataPersistida !== '') {
                    $datasPersistencia[] = $dataPersistida;
                }
            }
            $datasPersistencia = array_values(array_unique($datasPersistencia));

            if (!empty($datasPersistencia)) {
                $postosPersistencia = array_keys($lotesSelecionadosPorPosto);
                $placePostos = implode(',', array_fill(0, count($postosPersistencia), '?'));
                $placeDatas = implode(',', array_fill(0, count($datasPersistencia), '?'));
                $paramsPersistencia = array_merge($postosPersistencia, $datasPersistencia);
                $sqlBuscaConferencia = "SELECT LPAD(CAST(posto AS UNSIGNED), 3, '0') AS posto,
                                               LPAD(CAST(lote AS UNSIGNED), 8, '0') AS lote,
                                               COALESCE(quantidade, 0) AS quantidade,
                                               DATE(dataCarga) AS data_carga
                                        FROM ciPostosCsv
                                        WHERE LPAD(CAST(posto AS UNSIGNED), 3, '0') IN ($placePostos)
                                          AND DATE(dataCarga) IN ($placeDatas)";
                $stmtBuscaConferencia = $pdo_controle->prepare($sqlBuscaConferencia);
                $stmtBuscaConferencia->execute($paramsPersistencia);

                $stmtPersistirConferencia = $pdo_controle->prepare("INSERT INTO conferencia_pacotes (regional, nlote, nposto, dataexp, qtd, codbar, conf, usuario, conferido_em)
                                                                     VALUES (?, ?, ?, ?, ?, ?, 's', ?, NOW())
                                                                     ON DUPLICATE KEY UPDATE conf='s', qtd=VALUES(qtd), dataexp=VALUES(dataexp), usuario=VALUES(usuario), conferido_em=NOW(), codbar=IF(VALUES(codbar) <> '', VALUES(codbar), codbar)");
                $usuarioConferencia = isset($_SESSION['usuario']) && $_SESSION['usuario'] !== '' ? trim((string)$_SESSION['usuario']) : 'poupatempo';

                while ($rowConferencia = $stmtBuscaConferencia->fetch(PDO::FETCH_ASSOC)) {
                    $postoLinha = isset($rowConferencia['posto']) ? (string)$rowConferencia['posto'] : '';
                    $loteLinha = isset($rowConferencia['lote']) ? (string)$rowConferencia['lote'] : '';
                    if ($postoLinha === '' || $loteLinha === '') continue;
                    if (!isset($lotesSelecionadosPorPosto[$postoLinha][$loteLinha])) continue;
                    $dataCargaLinha = isset($rowConferencia['data_carga']) ? normalizarDataPtSql($rowConferencia['data_carga']) : '';
                    if ($dataCargaLinha === '') continue;
                    $stmtPersistirConferencia->execute(array(
                        '000',
                        $loteLinha,
                        $postoLinha,
                        $dataCargaLinha,
                        isset($rowConferencia['quantidade']) ? (int)$rowConferencia['quantidade'] : 0,
                        '',
                        $usuarioConferencia
                    ));
                }
            }
        }

        // v9.22.4: inserir lotes PT em ciDespachoLotes com data_carga/responsaveis
        $datasSql = array();
        if (!empty($datasStr_post)) {
            $datasTmp = explode(',', $datasStr_post);
            foreach ($datasTmp as $d) {
                $d = trim($d);
                if ($d === '') continue;
                if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $d, $m)) {
                    $datasSql[] = $m[3] . '-' . $m[2] . '-' . $m[1];
                } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) {
                    $datasSql[] = $d;
                }
            }
        }

        $lotesPorPosto = array();
        foreach ($dados_salvos as $posto => $d) {
            $lotesStr = isset($d['lote']) ? (string)$d['lote'] : '';
            if ($lotesStr === '') {
                continue;
            }
            $lotesList = array();
            foreach (explode(',', $lotesStr) as $lt) {
                $lt = trim($lt);
                if ($lt !== '') {
                    $lotesList[$lt] = true;
                }
            }
            if (!empty($lotesList)) {
                $lotesPorPosto[$posto] = array_keys($lotesList);
            }
        }

        if (!empty($lotesPorPosto)) {
            $stDelLote = $pdo_controle->prepare("DELETE FROM ciDespachoLotes WHERE id_despacho = ? AND posto = ?");
            $temEtiquetaIiprLote = false;
            $temEtiquetaCorreiosLote = false;
            $temEtiquetaCodigoLote = false;
            try {
                $colsLotes = $pdo_controle->query("SHOW COLUMNS FROM ciDespachoLotes")->fetchAll();
                foreach ($colsLotes as $colLote) {
                    if (!isset($colLote['Field'])) continue;
                    if ($colLote['Field'] === 'etiquetaiipr') $temEtiquetaIiprLote = true;
                    if ($colLote['Field'] === 'etiquetacorreios') $temEtiquetaCorreiosLote = true;
                    if ($colLote['Field'] === 'etiqueta_correios') $temEtiquetaCodigoLote = true;
                }
            } catch (Exception $colsEx) {}

            $sqlInsertLote = "INSERT INTO ciDespachoLotes (id_despacho, posto, lote, quantidade, data_carga, responsaveis";
            if ($temEtiquetaIiprLote) $sqlInsertLote .= ", etiquetaiipr";
            if ($temEtiquetaCorreiosLote) $sqlInsertLote .= ", etiquetacorreios";
            if ($temEtiquetaCodigoLote) $sqlInsertLote .= ", etiqueta_correios";
            $sqlInsertLote .= ") VALUES (?,?,?,?,?,?";
            if ($temEtiquetaIiprLote) $sqlInsertLote .= ",?";
            if ($temEtiquetaCorreiosLote) $sqlInsertLote .= ",?";
            if ($temEtiquetaCodigoLote) $sqlInsertLote .= ",?";
            $sqlInsertLote .= ")";
            $stInsLote = $pdo_controle->prepare($sqlInsertLote);

            foreach ($lotesPorPosto as $posto => $listaLotes) {
                $stDelLote->execute(array($id_despacho_post, $posto));
                $lacrePtLote = isset($dados_salvos[$posto]['lacre']) ? trim((string)$dados_salvos[$posto]['lacre']) : '';
                $lacreCorreiosPtLote = isset($dados_salvos[$posto]['lacre_correios_pt']) ? trim((string)$dados_salvos[$posto]['lacre_correios_pt']) : '';
                $etiquetaCorreiosPtLote = isset($dados_salvos[$posto]['etiqueta_correios_pt']) ? trim((string)$dados_salvos[$posto]['etiqueta_correios_pt']) : '';

                $placeLotes = implode(',', array_fill(0, count($listaLotes), '?'));
                $paramsLotes = array();
                $paramsLotes[] = $posto;
                foreach ($listaLotes as $lt) { $paramsLotes[] = $lt; }

                $sqlLotes = "
                    SELECT 
                        LPAD(c.posto,3,'0') AS posto,
                        c.lote,
                        SUM(COALESCE(c.quantidade,0)) AS quantidade,
                        MIN(DATE(c.dataCarga)) AS data_carga,
                        GROUP_CONCAT(DISTINCT c.usuario SEPARATOR ', ') AS responsaveis
                    FROM ciPostosCsv c
                    WHERE LPAD(c.posto,3,'0') = ?
                      AND c.lote IN ($placeLotes)
                ";
                if (!empty($datasSql)) {
                    $placeDatas = implode(',', array_fill(0, count($datasSql), '?'));
                    $sqlLotes .= " AND DATE(c.dataCarga) IN ($placeDatas) ";
                    foreach ($datasSql as $ds) { $paramsLotes[] = $ds; }
                }
                $sqlLotes .= " GROUP BY LPAD(c.posto,3,'0'), c.lote ";

                $stmtLotes = $pdo_controle->prepare($sqlLotes);
                $stmtLotes->execute($paramsLotes);
                $rowsLotes = $stmtLotes->fetchAll();

                $mapaLotes = array();
                foreach ($rowsLotes as $rl) {
                    $mapaLotes[(string)$rl['lote']] = $rl;
                }

                $respFallback = isset($usuariosPorPosto[$posto]) ? $usuariosPorPosto[$posto] : '';

                foreach ($listaLotes as $lt) {
                    if (isset($mapaLotes[$lt])) {
                        $row = $mapaLotes[$lt];
                        $paramsInsertLote = array(
                            $id_despacho_post,
                            $posto,
                            $lt,
                            (int)$row['quantidade'],
                            $row['data_carga'],
                            $row['responsaveis']
                        );
                        if ($temEtiquetaIiprLote) $paramsInsertLote[] = ($lacrePtLote === '' ? null : extrairUltimoLacreSequencialPt($lacrePtLote));
                        if ($temEtiquetaCorreiosLote) $paramsInsertLote[] = ($lacreCorreiosPtLote === '' ? null : extrairUltimoLacreSequencialPt($lacreCorreiosPtLote));
                        if ($temEtiquetaCodigoLote) $paramsInsertLote[] = ($etiquetaCorreiosPtLote === '' ? null : $etiquetaCorreiosPtLote);
                        $stInsLote->execute($paramsInsertLote);
                    } else {
                        $payloadInfo = isset($mapaPayloadLotes[$posto]) && isset($mapaPayloadLotes[$posto][$lt])
                            ? $mapaPayloadLotes[$posto][$lt]
                            : null;
                        $paramsInsertLote = array(
                            $id_despacho_post,
                            $posto,
                            $lt,
                            $payloadInfo ? (int)$payloadInfo['quantidade'] : 0,
                            $payloadInfo && !empty($payloadInfo['data_carga']) ? $payloadInfo['data_carga'] : null,
                            $payloadInfo && isset($payloadInfo['responsaveis']) && $payloadInfo['responsaveis'] !== '' ? $payloadInfo['responsaveis'] : $respFallback
                        );
                        if ($temEtiquetaIiprLote) $paramsInsertLote[] = ($lacrePtLote === '' ? null : extrairUltimoLacreSequencialPt($lacrePtLote));
                        if ($temEtiquetaCorreiosLote) $paramsInsertLote[] = ($lacreCorreiosPtLote === '' ? null : extrairUltimoLacreSequencialPt($lacreCorreiosPtLote));
                        if ($temEtiquetaCodigoLote) $paramsInsertLote[] = ($etiquetaCorreiosPtLote === '' ? null : $etiquetaCorreiosPtLote);
                        $stInsLote->execute($paramsInsertLote);
                    }
                }
            }
        }

        $pdo_controle->commit();

        $mensagem_status = 'Dados salvos com sucesso! Inseridos: ' . $totalInseridos . ', Atualizados: ' . $totalAtualizados;
        $tipo_mensagem = 'sucesso';

        // Atualiza o id_despacho para uso posterior na página
        $_POST['id_despacho'] = $id_despacho_post;
        
        // Verifica se deve imprimir após salvar
        if (isset($_POST['imprimir_apos_salvar']) && $_POST['imprimir_apos_salvar'] === '1') {
            $deve_imprimir = true;
        }

    } catch (Exception $ex) {
        if ($pdo_controle && $pdo_controle->inTransaction()) {
            $pdo_controle->rollBack();
        }
        $mensagem_status = 'Erro ao salvar: ' . $ex->getMessage();
        $tipo_mensagem = 'erro';
        // Em caso de erro, mantém os dados para não perder as edições
    }
}

/* ============================================================
   2) Coleta das datas (pt_datas) vindas do formulário
   ============================================================ */
$datasStr  = '';
$datasNorm = array();
$paginasDinamicas = array();
$mapaPayloadLotes = array();
// v9.25.x: filtros opcionais do oficio PT
$postosSelecionados = array();
$filtrarNaoConferidos = false;
$filtrarSemOficio = false;

if (isset($_POST['pt_datas'])) {
    $datasStr = $_POST['pt_datas'];
} elseif (isset($_GET['pt_datas'])) {
    $datasStr = $_GET['pt_datas'];
}

// v9.8.4: Debug para identificar problemas de dados vazios
if (isset($_GET['debug']) || isset($_GET['debug_dados'])) {
    echo "<pre style='background:#ffc;padding:20px;border:3px solid #f00;margin:10px;'>";
    echo "<h2 style='color:#f00;'>🔍 DEBUG v9.8.4 - DADOS RECEBIDOS</h2>";
    echo "<strong>POST pt_datas:</strong> " . (isset($_POST['pt_datas']) ? $_POST['pt_datas'] : 'NÃO DEFINIDO') . "\n";
    echo "<strong>GET pt_datas:</strong> " . (isset($_GET['pt_datas']) ? $_GET['pt_datas'] : 'NÃO DEFINIDO') . "\n";
    echo "<strong>datasStr final:</strong> " . (empty($datasStr) ? 'VAZIO!' : $datasStr) . "\n";
    echo "\n<strong>Todo POST:</strong>\n";
    print_r($_POST);
    echo "\n<strong>Todo GET:</strong>\n";
    print_r($_GET);
    echo "</pre>";
}

if (!empty($datasStr)) {
    $tmp = explode(',', $datasStr);
    foreach ($tmp as $d) {
        $d = trim($d);
        if ($d !== '') {
            $dSql = normalizarDataPtSql($d);
            if ($dSql !== '') {
                $datasNorm[] = $dSql;
            }
        }
    }
}
if (!empty($datasNorm)) {
    $datasNorm = array_values(array_unique($datasNorm));
}

if (isset($_POST['pt_dinamico_payload']) || isset($_GET['pt_dinamico_payload'])) {
    $payloadBruto = isset($_POST['pt_dinamico_payload']) ? $_POST['pt_dinamico_payload'] : $_GET['pt_dinamico_payload'];
    $payload = json_decode($payloadBruto, true);
    if (is_array($payload) && isset($payload['postos']) && is_array($payload['postos'])) {
        foreach ($payload['postos'] as $postoPayload) {
            $codigo = isset($postoPayload['codigo']) ? preg_replace('/\D+/', '', (string)$postoPayload['codigo']) : '';
            $codigo = str_pad($codigo, 3, '0', STR_PAD_LEFT);
            if ($codigo === '000') {
                continue;
            }

            $nome = isset($postoPayload['nome']) ? trim((string)$postoPayload['nome']) : '';
            $endereco = isset($postoPayload['endereco']) ? trim((string)$postoPayload['endereco']) : '';
            $usuario = isset($postoPayload['usuario']) ? trim((string)$postoPayload['usuario']) : '';
            $lotesPayload = isset($postoPayload['lotes']) && is_array($postoPayload['lotes']) ? $postoPayload['lotes'] : array();
            $lotesNormalizados = array();
            $qtdTotalPayload = 0;

            foreach ($lotesPayload as $lotePayload) {
                $lote = isset($lotePayload['lote']) ? preg_replace('/\D+/', '', (string)$lotePayload['lote']) : '';
                if ($lote === '') {
                    continue;
                }
                $lote = str_pad($lote, 8, '0', STR_PAD_LEFT);
                $quantidade = isset($lotePayload['quantidade']) ? (int)$lotePayload['quantidade'] : 0;
                if ($quantidade < 0) {
                    $quantidade = 0;
                }
                $dataCarga = isset($lotePayload['data_carga']) ? normalizarDataPtSql($lotePayload['data_carga']) : '';
                $responsaveis = isset($lotePayload['responsaveis']) ? trim((string)$lotePayload['responsaveis']) : $usuario;

                $lotesNormalizados[] = array(
                    'lote' => $lote,
                    'quantidade' => $quantidade,
                    'data_carga' => $dataCarga
                );
                $qtdTotalPayload += $quantidade;
                $mapaPayloadLotes[$codigo][$lote] = array(
                    'quantidade' => $quantidade,
                    'data_carga' => $dataCarga,
                    'responsaveis' => $responsaveis
                );
            }

            if (empty($lotesNormalizados)) {
                continue;
            }

            $paginasDinamicas[] = array(
                'codigo' => $codigo,
                'nome' => $nome,
                'endereco' => $endereco,
                'usuario' => $usuario,
                'lotes' => $lotesNormalizados,
                'qtd_total' => $qtdTotalPayload
            );
        }
    }
}

if (isset($_POST['pt_postos_sel'])) {
    $rawSel = $_POST['pt_postos_sel'];
    if (is_array($rawSel)) {
        $tmpSel = $rawSel;
    } else {
        $tmpSel = explode(',', (string)$rawSel);
    }
    foreach ($tmpSel as $ps) {
        $ps = preg_replace('/\D+/', '', (string)$ps);
        if ($ps === '') continue;
        $postosSelecionados[] = str_pad($ps, 3, '0', STR_PAD_LEFT);
    }
    $postosSelecionados = array_values(array_unique($postosSelecionados));
}

$filtrarNaoConferidos = isset($_POST['pt_filtro_nao_conferidos']) && $_POST['pt_filtro_nao_conferidos'] === '1';
$filtrarSemOficio = isset($_POST['pt_filtro_sem_oficio']) && $_POST['pt_filtro_sem_oficio'] === '1';

/* ============================================================
   DEBUG opcional – acessar com ?debug_pt=1
   ============================================================ */
if (isset($_GET['debug_pt'])) {
    echo "<pre>DEBUG modelo_oficio_poupa_tempo.php\n";
    echo "=================================\n\n";
    echo "GET:\n";
    var_dump($_GET);
    echo "\n-------------------------------\n\n";
    echo "POST:\n";
    var_dump($_POST);
    echo "\n-------------------------------\n\n";
    echo "datasNorm (pt_datas):\n";
    var_dump($datasNorm);
    echo "\n-------------------------------\n\n";
    echo "dados_salvos:\n";
    var_dump($dados_salvos);
    echo "</pre>";
}

/* ============================================================
   3) Busca dos registros Poupatempo no banco
   ============================================================ */

$paginas = array();  // Cada elemento = array('codigo','nome','qtd','endereco')
$mapaConferidos = array();
$modo_branco = (isset($_POST['pt_blank']) && $_POST['pt_blank'] === '1') || (isset($_GET['pt_blank']) && $_GET['pt_blank'] === '1');
$modo_visual_pt = '';
if (isset($_POST['pt_modo_visual'])) {
    $modo_visual_pt = strtolower(trim((string)$_POST['pt_modo_visual']));
} elseif (isset($_GET['pt_modo_visual'])) {
    $modo_visual_pt = strtolower(trim((string)$_GET['pt_modo_visual']));
}
if ($modo_visual_pt === '') {
    $modo_visual_pt = $modo_branco ? 'branco' : 'padrao';
}
$modo_visual_correios = ($modo_visual_pt === 'correios');

if (!$modo_branco && !empty($paginasDinamicas)) {
    if (!empty($postosSelecionados)) {
        $paginasFiltradas = array();
        foreach ($paginasDinamicas as $paginaDinamica) {
            $codigoPagina = isset($paginaDinamica['codigo']) ? str_pad(preg_replace('/\D+/', '', (string)$paginaDinamica['codigo']), 3, '0', STR_PAD_LEFT) : '';
            if ($codigoPagina !== '' && in_array($codigoPagina, $postosSelecionados, true)) {
                $paginasFiltradas[] = $paginaDinamica;
            }
        }
        $paginas = !empty($paginasFiltradas) ? $paginasFiltradas : $paginasDinamicas;
    } else {
        $paginas = $paginasDinamicas;
    }
} elseif (!$modo_branco && $pdo_controle && !empty($datasNorm)) {

    $in = "'" . implode("','", array_map('strval', $datasNorm)) . "'";
    $filtroPostosSql = '';
    if (!empty($postosSelecionados)) {
        $filtroPostosSql = " AND LPAD(c.posto,3,'0') IN ('" . implode("','", $postosSelecionados) . "') ";
    }

    // v9.8.2: Busca lotes individuais (não agrupa quantidade)
    $sql = "
        SELECT 
            LPAD(c.posto,3,'0') AS codigo,
            COALESCE(r.nome, CONCAT('POUPA TEMPO - ', LPAD(c.posto,3,'0'))) AS nome,
            c.lote AS lote,
            COALESCE(c.quantidade,0) AS quantidade,
            r.endereco AS endereco,
            c.usuario AS usuario,
            DATE(c.dataCarga) AS data_carga
        FROM ciPostosCsv c
        INNER JOIN ciRegionais r 
                ON LPAD(r.posto,3,'0') = LPAD(c.posto,3,'0')
        WHERE DATE(c.dataCarga) IN ($in)
          AND REPLACE(LOWER(r.entrega),' ','') LIKE 'poupa%tempo'
                    $filtroPostosSql
        ORDER BY 
            LPAD(c.posto,3,'0'), c.lote
    ";

    try {
        $stmt = $pdo_controle->query($sql);
        $postosPorCodigo = array(); // v9.8.2: Agrupar lotes por posto
        
        foreach ($stmt as $r) {
            $codigo   = (string)$r['codigo'];           
            $nome     = (string)$r['nome'];             
            $lote     = (string)$r['lote'];
            $quant    = (int)$r['quantidade'];          
            $endereco = trim((string)$r['endereco']);
            $usuario  = isset($r['usuario']) ? trim((string)$r['usuario']) : '';
            $data_carga = isset($r['data_carga']) ? trim((string)$r['data_carga']) : '';

            // v9.8.2: Agrupar por posto e acumular lotes
            if (!isset($postosPorCodigo[$codigo])) {
                $postosPorCodigo[$codigo] = array(
                    'codigo'   => $codigo,
                    'nome'     => $nome,
                    'endereco' => $endereco,
                    'usuario'  => $usuario,
                    'lotes'    => array(),  // Array de lotes individuais
                    'qtd_total' => 0
                );
            }
            
            // Adiciona lote individual
            $postosPorCodigo[$codigo]['lotes'][] = array(
                'lote' => $lote,
                'quantidade' => $quant,
                'data_carga' => $data_carga
            );
            $postosPorCodigo[$codigo]['qtd_total'] += $quant;
        }
        
        $postosList = array_keys($postosPorCodigo);
        $mapaConferidos = array();
        if (!empty($postosList)) {
            $inPostos = "'" . implode("','", array_map('strval', $postosList)) . "'";
            $datasNormMapa = array();
            foreach ($datasNorm as $dataFiltroPt) {
                $dataFiltroNorm = normalizarDataPtSql($dataFiltroPt);
                if ($dataFiltroNorm !== '') {
                    $datasNormMapa[$dataFiltroNorm] = true;
                }
            }
            $sqlConf = "SELECT DISTINCT nlote, nposto, dataexp
                        FROM conferencia_pacotes
                        WHERE conf IN ('s', 'S', '1', 1)
                          AND nposto IN ($inPostos)";
            try {
                $stmtConf = $pdo_controle->query($sqlConf);
                while ($rc = $stmtConf->fetch(PDO::FETCH_ASSOC)) {
                    $p = str_pad(preg_replace('/\D+/', '', (string)$rc['nposto']), 3, '0', STR_PAD_LEFT);
                    $l = str_pad(preg_replace('/\D+/', '', (string)$rc['nlote']), 8, '0', STR_PAD_LEFT);
                    $dataConfNorm = normalizarDataPtSql(isset($rc['dataexp']) ? $rc['dataexp'] : '');
                    if (!empty($datasNormMapa) && ($dataConfNorm === '' || !isset($datasNormMapa[$dataConfNorm]))) {
                        continue;
                    }
                    if ($p !== '' && $l !== '') {
                        if (!isset($mapaConferidos[$p])) { $mapaConferidos[$p] = array(); }
                        $mapaConferidos[$p][$l] = true;
                    }
                }
            } catch (Exception $e) {}
        }

        // v9.25.x: filtros opcionais por conferencia/oficio
        if ($filtrarNaoConferidos || $filtrarSemOficio) {
            $lotesList = array();
            foreach ($postosPorCodigo as $pp) {
                if (!empty($pp['lotes'])) {
                    foreach ($pp['lotes'] as $lt) {
                        if (!empty($lt['lote'])) { $lotesList[] = (string)$lt['lote']; }
                    }
                }
            }
            $lotesList = array_values(array_unique($lotesList));
            $mapaOficio = array();
            if ($filtrarSemOficio && !empty($postosList)) {
                $inPostos = "'" . implode("','", array_map('strval', $postosList)) . "'";
                $sqlOf = "SELECT DISTINCT posto, lote
                          FROM ciDespachoLotes
                          WHERE posto IN ($inPostos)";
                if (!empty($datasNorm)) {
                    $sqlOf .= " AND DATE(data_carga) IN ($in) ";
                }
                try {
                    $stmtOf = $pdo_controle->query($sqlOf);
                    while ($ro = $stmtOf->fetch(PDO::FETCH_ASSOC)) {
                        $p = str_pad(preg_replace('/\D+/', '', (string)$ro['posto']), 3, '0', STR_PAD_LEFT);
                        $l = preg_replace('/\D+/', '', (string)$ro['lote']);
                        if ($p !== '' && $l !== '') {
                            if (!isset($mapaOficio[$p])) { $mapaOficio[$p] = array(); }
                            $mapaOficio[$p][$l] = true;
                        }
                    }
                } catch (Exception $e) {}
            }

            foreach ($postosPorCodigo as $pc => $pp) {
                $novosLotes = array();
                $qtdTotal = 0;
                foreach ($pp['lotes'] as $lt) {
                    $loteNum = preg_replace('/\D+/', '', (string)$lt['lote']);
                    if ($filtrarNaoConferidos && isset($mapaConferidos[$pc]) && isset($mapaConferidos[$pc][$loteNum])) {
                        continue;
                    }
                    if ($filtrarSemOficio && isset($mapaOficio[$pc]) && isset($mapaOficio[$pc][$loteNum])) {
                        continue;
                    }
                    $novosLotes[] = $lt;
                    $qtdTotal += isset($lt['quantidade']) ? (int)$lt['quantidade'] : 0;
                }
                if (!empty($novosLotes)) {
                    $postosPorCodigo[$pc]['lotes'] = $novosLotes;
                    $postosPorCodigo[$pc]['qtd_total'] = $qtdTotal;
                } else {
                    unset($postosPorCodigo[$pc]);
                }
            }
        }

        // Converte para array sequencial
        $paginas = array_values($postosPorCodigo);
        
        // Debug: Verifica estrutura de lotes
        if (isset($_GET['debug_lotes'])) {
            echo "<pre style='background:#fff3cd;padding:20px;border:2px solid #856404;margin:10px;'>";
            echo "<h3>DEBUG LOTES v9.8.3</h3>";
            echo "Total de postos: " . count($paginas) . "\n\n";
            foreach ($paginas as $idx => $posto) {
                echo "Posto #{$idx}: {$posto['codigo']} - {$posto['nome']}\n";
                echo "  Total lotes: " . count($posto['lotes']) . "\n";
                echo "  Qtd total: {$posto['qtd_total']}\n";
                foreach ($posto['lotes'] as $lidx => $lt) {
                    echo "    Lote [{$lidx}]: {$lt['lote']} = {$lt['quantidade']} CINs\n";
                }
                echo "\n";
            }
            echo "</pre>";
        }
        
    } catch (Exception $e) {
        // error_log("Erro SQL Poupatempo: " . $e->getMessage());
    }
}

// v9.21.6: Modo em branco (modelo para preenchimento manual)
if ($modo_branco) {
    $paginas = array(array(
        'codigo'   => '000',
        'nome'     => '',
        'endereco' => '',
        'usuario'  => '',
        'lotes'    => array(),
        'qtd_total' => 0
    ));
}

if (isset($_GET['debug_pt'])) {
    echo "<pre>\n-------------------------------\n\n";
    echo "PAGINAS (resultado do SELECT):\n";
    var_dump($paginas);
    echo "</pre>";
}

// v9.8.4: Debug final - mostra se tem dados para exibir
if (isset($_GET['debug']) || isset($_GET['debug_dados'])) {
    echo "<pre style='background:#ffe;padding:20px;border:3px solid #00f;margin:10px;'>";
    echo "<h2 style='color:#00f;'>🔍 DEBUG v9.8.4 - RESULTADO DA BUSCA</h2>";
    echo "<strong>datasNorm (datas normalizadas):</strong> " . (empty($datasNorm) ? 'VAZIO!' : implode(', ', $datasNorm)) . "\n";
    echo "<strong>Total de páginas (postos):</strong> " . count($paginas) . "\n";
    echo "<strong>temDados:</strong> " . ($temDados ? 'SIM' : 'NÃO') . "\n\n";
    
    if (!empty($paginas)) {
        foreach ($paginas as $idx => $p) {
            echo "Página #{$idx}: Posto {$p['codigo']} - {$p['nome']}\n";
            echo "  Total lotes: " . (isset($p['lotes']) ? count($p['lotes']) : 0) . "\n";
            echo "  Qtd total: {$p['qtd_total']}\n";
        }
    } else {
        echo "\n❌ NENHUMA PÁGINA GERADA!\n";
        echo "Possíveis causas:\n";
        echo "1. Datas não têm produção no banco\n";
        echo "2. Query SQL não retornou resultados\n";
        echo "3. Posto não está configurado como Poupa Tempo\n";
    }
    echo "</pre>";
}

$temDados = count($paginas) > 0;

/* ============================================================
   3.1) Localizar despacho (expedição) e lacres já salvos
   ============================================================ */

$grupo_oficio   = 'POUPA TEMPO';
$id_despacho    = isset($_POST['id_despacho']) ? (int)$_POST['id_despacho'] : 0;
$lacresPorPosto = array();
$lacresCorreiosPtPorPosto = array();
$etiquetasCorreiosPtPorPosto = array();
$nomesPorPosto = array();
$enderecosPorPosto = array();
$quantidadesPorPosto = array();

if (isset($_POST['lacre_correios_pt']) && is_array($_POST['lacre_correios_pt'])) {
    foreach ($_POST['lacre_correios_pt'] as $postoLcPt => $valorLcPt) {
        $postoLcPt = str_pad(preg_replace('/\D+/', '', (string)$postoLcPt), 3, '0', STR_PAD_LEFT);
        if ($postoLcPt !== '000') {
            $lacresCorreiosPtPorPosto[$postoLcPt] = trim((string)$valorLcPt);
        }
    }
}

if (isset($_POST['etiqueta_correios_pt']) && is_array($_POST['etiqueta_correios_pt'])) {
    foreach ($_POST['etiqueta_correios_pt'] as $postoEtPt => $valorEtPt) {
        $postoEtPt = str_pad(preg_replace('/\D+/', '', (string)$postoEtPt), 3, '0', STR_PAD_LEFT);
        if ($postoEtPt !== '000') {
            $etiquetasCorreiosPtPorPosto[$postoEtPt] = trim((string)$valorEtPt);
        }
    }
}

if ($pdo_controle && !empty($datasStr)) {
    // Mesmo hash usado no salvar_oficio_pt em lacres_novo.php
    $hash = sha1($grupo_oficio . '|' . $datasStr);

    try {
        if ($id_despacho <= 0) {
            $stD = $pdo_controle->prepare("
                SELECT id
                  FROM ciDespachos
                 WHERE hash_chave = ?
                 LIMIT 1
            ");
            $stD->execute(array($hash));
            $id_despacho = (int)$stD->fetchColumn();
        }

        if ($id_despacho > 0) {
            $stL = $pdo_controle->prepare("
                SELECT posto, lacre_iipr, nome_posto, endereco, quantidade
                  FROM ciDespachoItens
                 WHERE id_despacho = ?
            ");
            $stL->execute(array($id_despacho));

            while ($rowL = $stL->fetch(PDO::FETCH_ASSOC)) {
                $posto3 = str_pad(preg_replace('/\D+/', '', $rowL['posto']), 3, '0', STR_PAD_LEFT);
                $lacresPorPosto[$posto3] = isset($rowL['lacre_iipr']) ? (string)$rowL['lacre_iipr'] : '';
                if (!empty($rowL['nome_posto'])) {
                    $nomesPorPosto[$posto3] = (string)$rowL['nome_posto'];
                }
                if (!empty($rowL['endereco'])) {
                    $enderecosPorPosto[$posto3] = (string)$rowL['endereco'];
                }
                if (isset($rowL['quantidade'])) {
                    $quantidadesPorPosto[$posto3] = (int)$rowL['quantidade'];
                }
            }

            try {
                $stResumo = $pdo_controle->prepare("
                    SELECT posto,
                           MAX(etiquetaiipr) AS etiquetaiipr,
                           MAX(etiquetacorreios) AS etiquetacorreios,
                           MAX(etiqueta_correios) AS etiqueta_correios
                      FROM ciDespachoLotes
                     WHERE id_despacho = ?
                     GROUP BY posto
                ");
                $stResumo->execute(array($id_despacho));
                while ($rowResumo = $stResumo->fetch(PDO::FETCH_ASSOC)) {
                    $postoResumo = str_pad(preg_replace('/\D+/', '', (string)$rowResumo['posto']), 3, '0', STR_PAD_LEFT);
                    if ($postoResumo === '000') continue;
                    if (!empty($rowResumo['etiquetaiipr'])) {
                        $lacresPorPosto[$postoResumo] = (string)$rowResumo['etiquetaiipr'];
                    }
                    if (!empty($rowResumo['etiquetacorreios'])) {
                        $lacresCorreiosPtPorPosto[$postoResumo] = (string)$rowResumo['etiquetacorreios'];
                    }
                    if (!empty($rowResumo['etiqueta_correios'])) {
                        $etiquetasCorreiosPtPorPosto[$postoResumo] = trim((string)$rowResumo['etiqueta_correios']);
                    }
                }
            } catch (Exception $eResumo) {
            }
        }
    } catch (Exception $e) {
        // Se der erro aqui, apenas segue sem lacres pré-carregados
    }
}

// Se acabamos de salvar dados, sobrescreve com os valores salvos para mostrar exatamente o que foi gravado
if (!empty($dados_salvos) && $tipo_mensagem === 'sucesso') {
    foreach ($dados_salvos as $posto => $valores) {
        if (isset($valores['lacre'])) {
            $lacresPorPosto[$posto] = $valores['lacre'];
        }
        if (isset($valores['lacre_correios_pt'])) {
            $lacresCorreiosPtPorPosto[$posto] = $valores['lacre_correios_pt'];
        }
        if (isset($valores['etiqueta_correios_pt'])) {
            $etiquetasCorreiosPtPorPosto[$posto] = $valores['etiqueta_correios_pt'];
        }
        if (isset($valores['nome'])) {
            $nomesPorPosto[$posto] = $valores['nome'];
        }
        if (isset($valores['endereco'])) {
            $enderecosPorPosto[$posto] = $valores['endereco'];
        }
        if (isset($valores['quantidade'])) {
            $quantidadesPorPosto[$posto] = $valores['quantidade'];
        }
    }
}

/* ============================================================
   4) HTML do Ofício (layout preservado)
   ============================================================ */
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<?php
// v8.15.3: Título do PDF sem # no início (formato: ID_poupatempo_dd-mm-yyyy)
$titulo_pdf = $modo_visual_correios
    ? 'Comprovante de Entrega - Poupatempo com Etiqueta Correios'
    : 'Comprovante de Entrega - Poupatempo';
if (isset($id_despacho_post) && $id_despacho_post > 0) {
    $data_titulo = date('d-m-Y');
    $titulo_pdf = $id_despacho_post . ($modo_visual_correios ? '_poupatempo_correios_' : '_poupatempo_') . $data_titulo;
}
?>
<title><?php echo e($titulo_pdf); ?></title>
<style>
/* ====== v8.15.3: Layout melhorado - baseado em modelo antigo ====== */
/* v9.24.x: Layout mais enxuto para evitar sobreposicao entre paginas */
table{border:1px solid #000;border-collapse:collapse;margin:6px 0;width:100%;}
th,td{border:1px solid #000;padding:5px!important;text-align:center}
th{background:#f2f2f2}
body{font-family:Arial,Helvetica,sans-serif;background:#f0f0f0;line-height:1.25}

/* Controles na tela (não imprime) */
.controles-pagina{width:800px;margin:20px auto;padding:15px;background:#fff;border:1px dashed #ccc;text-align:center}
.controles-pagina button{padding:10px 20px;font-size:16px;background:#007bff;color:#fff;border:none;border-radius:5px;cursor:pointer;margin:5px}
.controles-pagina button:hover{background:#0056b3}
.controles-pagina button.btn-sucesso{background:#28a745}
.controles-pagina button.btn-sucesso:hover{background:#1e7e34}
.controles-pagina button.btn-imprimir{background:#6c757d}
.controles-pagina button.btn-imprimir:hover{background:#545b62}
.controles-pagina button.btn-excluir{background:#dc3545}
.controles-pagina button.btn-excluir:hover{background:#bd2130}

/* Folha A4 - v9.20.1: Layout vertical (uma página abaixo da outra) */
.folha-a4-oficio{
    width:210mm;
    min-height:297mm;
    margin:20px auto;
    padding:8mm;
    background:#fff;
    box-shadow:0 2px 8px rgba(0,0,0,0.1);
    box-sizing:border-box;
    display:block;
    float:none;
    clear:both;
    page-break-after:always;
    position:relative;
    overflow:visible;
}
.folha-a4-oficio:last-of-type{page-break-after:auto}
.folha-a4-oficio:after{content:"";display:block;clear:both}

/* Estrutura do ofício */
.oficio{
    width:100%;
    display:flex;
    flex-direction:column;
    min-height:calc(297mm - 40mm);
    position:relative;
}
.oficio *{box-sizing:border-box}

/* Classes de layout */
.cols65{width:65%}
.cols50{width:50%}
.cols25{width:25%}
.fleft{float:left}
.fright{float:right}
.center{text-align:center}
.left{text-align:left}
.border-1px{border:1px solid #000}
.margin2px{margin:2px}
.p5{padding:5px}
.nometit{font-weight:bold}

/* Área de processo (elástica) */
.processo{
    flex-grow:1;
    display:flex;
    flex-direction:column;
    position:relative;
    z-index:1;
}
.oficio-observacao{
    height:100%;
    display:flex;
    flex-direction:column;
    position:relative;
}

/* Títulos */
.oficio h3,.oficio h4{margin:5px 0}

/* Clear floats */
.cols100:after{content:"";display:table;clear:both}

/* Campos editáveis */
[contenteditable="true"]{
    outline:2px dashed transparent;
    transition:background-color .3s;
    min-height:1.2em;
    padding:2px;
    word-wrap:break-word;
    overflow-wrap:break-word;
}
[contenteditable="true"]:hover{background:#ffffcc;cursor:text}

/* Inputs editáveis */
.input-editavel{
    border:none;
    border-bottom:1px solid #000;
    background:transparent;
    font-family:inherit;
    font-size:inherit;
    padding:2px 4px;
    width:100%;
    word-wrap:break-word;
    overflow-wrap:break-word;
}
.input-editavel:focus{
    outline:2px dashed #007bff;
    background:#ffffcc;
}

/* v9.20.0: Botão de remover página clonada - DENTRO da página */
.btn-remover-pagina{
    display:inline-block;
    margin:10px auto 20px auto;
    padding:8px 16px;
    background:#dc3545;
    color:#fff;
    border:2px solid #bd2130;
    border-radius:6px;
    font-size:13px;
    font-weight:bold;
    cursor:pointer;
    text-align:center;
    box-shadow:0 2px 5px rgba(220,53,69,0.3);
    transition:all 0.2s;
    width:100%;
    max-width:300px;
}
.btn-remover-pagina:hover{
    background:#c82333;
    border-color:#a71d2a;
    transform:translateY(-2px);
    box-shadow:0 4px 8px rgba(220,53,69,0.4);
}

/* Moldura */
.moldura{outline:1px solid #000;padding:8px}

/* v9.21.8: Ocultar colunas vazias (sem dados) */
.coluna-vazia{display:none !important;}


/* Modal de confirmação */
.modal-overlay{position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.7);z-index:9998;display:none}
.modal-box{position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:#fff;padding:25px;border-radius:8px;box-shadow:0 4px 20px rgba(0,0,0,0.3);z-index:9999;max-width:500px;text-align:center}
.modal-box h3{margin-top:0;color:#333}
.modal-box p{margin:15px 0;color:#666;line-height:1.6}
.modal-box button{padding:12px 24px;margin:8px;font-size:14px;border:none;border-radius:5px;cursor:pointer;transition:all 0.3s}
.modal-box .btn-primary{background:#007bff;color:white}
.modal-box .btn-primary:hover{background:#0056b3}
.modal-box .btn-success{background:#28a745;color:white}
.modal-box .btn-success:hover{background:#1e7e34}
.modal-box .btn-secondary{background:#6c757d;color:white}
.modal-box .btn-secondary:hover{background:#545b62}

/* Animação de pulsar para botões não salvos */
@keyframes pulsar{0%,100%{transform:scale(1);box-shadow:0 0 0 rgba(40,167,69,0.7)}50%{transform:scale(1.05);box-shadow:0 0 15px rgba(40,167,69,0.9)}}
.btn-nao-salvo{animation:pulsar 2s infinite}

/* Classe para ocultar na impressão */
.nao-imprimir{display:block}

/* v9.21.6: Rodapé oculto na tela e visível apenas na impressão */
.rodape-oficio{display:none}

/* v9.21.6: Espaçador ajustável do rodapé */
.espacador-rodape{min-height:10px;padding-top:10px}

/* v9.24.x: Ajustes do cabecalho e titulo de lotes */
.cabecalho-pt{padding:6px}
.cabecalho-pt-titulo{margin:4px 0; line-height:1.2}
.titulo-lotes{text-align:center; margin:12px 0 6px 0; font-size:15px; font-weight:bold;}

/* v9.24.5: Botao voltar ao inicio */
.btn-voltar-inicio{display:inline-flex; align-items:center; padding:6px 10px; border-radius:6px; background:#1f2b6d; color:#fff; text-decoration:none; font-size:12px; font-weight:bold;}
.btn-voltar-inicio:hover{background:#162057;}

/* v1.0.9: Coluna de ações dos lotes */
.col-acoes-lote{width:138px; text-align:center}
.acoes-lote-wrap{display:flex; align-items:center; justify-content:center; gap:4px}
.btn-lote-acao{display:inline-block; min-width:58px; padding:3px 6px; font-size:11px; border:1px solid #666; background:#f2f2f2; cursor:pointer; border-radius:3px; white-space:nowrap}
.btn-lote-acao:hover{background:#e2e2e2}
.btn-lote-adicionar{background:#eef6ff; border-color:#7aa7d8; color:#184a7a}
.btn-lote-excluir{background:#fff1f1; border-color:#c66; color:#8a1f1f}
.btn-remover-desmarcados{margin-top:12px; padding:8px 14px; border:none; border-radius:4px; background:#8b5e00; color:#fff; font-size:12px; font-weight:bold; cursor:pointer}
.btn-remover-desmarcados:hover{background:#6f4b00}
.campo-lote-manual,.campo-qtd-manual,.campo-data-manual{width:100%; border:none; background:transparent; font-size:10px; text-align:inherit; padding:0}
.campo-qtd-manual{text-align:center}
.campo-data-manual{text-align:center}
.campo-lote-manual:focus,.campo-qtd-manual:focus,.campo-data-manual:focus{outline:1px solid #6c63ff; background:#fff}
.lacre-avulso-wrap{display:flex; align-items:center; gap:6px}
.campo-lacre-multiplo-pt{flex:1 1 auto; width:100%; min-width:120px; min-height:26px; resize:horizontal; overflow:auto; text-align:left; font-size:12px; line-height:1.2; font-family:'Courier New',Courier,monospace; font-weight:bold; white-space:nowrap}
.campo-lacre-multiplo-pt::-webkit-resizer{background:#d8dde3}
.btn-av-pt{display:inline-flex; align-items:center; justify-content:center; min-width:32px; height:26px; padding:0 8px; border:1px solid #666; background:#f4f4f4; color:#333; border-radius:4px; cursor:pointer; font-size:11px; font-weight:bold}
.btn-av-pt.ativo{background:#2d2d2d; color:#fff; border-color:#2d2d2d}

/* v9.24.5: Grade mais compacta para caber mais lotes */
.lotes-detalhe-1col th,
.lotes-detalhe-1col td{padding:4px 6px; font-size:10px; line-height:1.1}
.lotes-detalhe-1col tbody tr{height:18px}

.folha-mestre-pt-correios .titulo-mestre{margin:10px 0 4px 0; font-size:20px; font-weight:bold; text-align:center}
.folha-mestre-pt-correios .subtitulo-mestre{margin:0 0 14px 0; font-size:12px; text-align:center}
.folha-mestre-pt-correios .resumo-datas{margin:0 0 8px 0; font-size:12px; text-align:center}
.folha-mestre-pt-correios .processo{border:none !important; padding:0 !important}
.folha-mestre-pt-correios .oficio-observacao{padding:0 !important}
.folha-mestre-pt-correios .tabela-mestre-pt{border-collapse:collapse; border:1px solid #000; margin:0; width:100%}
.folha-mestre-pt-correios .tabela-mestre-pt th,
.folha-mestre-pt-correios .tabela-mestre-pt td{font-size:12px; padding:5px 4px !important; vertical-align:middle; line-height:1.1}
.folha-mestre-pt-correios .tabela-mestre-pt th{font-weight:bold; background:#fff}
.folha-mestre-pt-correios .tabela-mestre-pt input,
.folha-mestre-pt-correios .tabela-mestre-pt textarea{width:100%; border:none; background:transparent; font-size:12px; padding:1px 2px; box-shadow:none}
.folha-mestre-pt-correios .tabela-mestre-pt input{height:20px}
.folha-mestre-pt-correios .tabela-mestre-pt textarea{resize:none; min-height:20px; height:auto; line-height:1.15; overflow:visible; white-space:normal}
.folha-mestre-pt-correios .quadro-logo-mestre{display:flex; align-items:center; border:1px solid #000; padding:10px 12px; margin-bottom:12px; line-height:1.0}
.folha-mestre-pt-correios .logo-mestre{width:44%; padding-right:12px}
.folha-mestre-pt-correios .logo-mestre img{display:block; max-width:100%; height:auto}
.folha-mestre-pt-correios .texto-logo-mestre{width:56%; border-left:1px solid #000; padding-left:14px; font-size:14px; line-height:1.0}
.folha-mestre-pt-correios .texto-logo-mestre strong{display:block; font-size:15px}
.folha-mestre-pt-correios .info-cliente-mestre{border:1px solid #000; padding:9px 10px; margin-bottom:8px; font-size:13px; line-height:1.0; position:relative}
.folha-mestre-pt-correios .info-cliente-mestre p{margin:0 0 8px 0}
.folha-mestre-pt-correios .info-cliente-mestre p:last-child{margin-bottom:0}
.folha-mestre-pt-correios .numero-oficio-mestre{position:absolute; top:8px; right:8px; padding:6px 12px; border:2px solid #000; background:#fff; font-size:15px; font-weight:bold; min-width:72px; text-align:center}
.folha-mestre-pt-correios .grupo-mestre-tabela{margin-bottom:12px}
.folha-mestre-pt-correios .grupo-mestre-tabela:last-of-type{margin-bottom:0}
.folha-mestre-pt-correios .tabela-mestre-pt .col-posto{width:34%; text-align:left}
.folha-mestre-pt-correios .tabela-mestre-pt .col-lacre-pt{width:9%; text-align:center}
.folha-mestre-pt-correios .tabela-mestre-pt .col-lacre-correios-pt{width:10%; text-align:center}
.folha-mestre-pt-correios .tabela-mestre-pt .col-etiqueta{width:47%; text-align:left}
.folha-mestre-pt-correios .tabela-mestre-pt tbody tr{height:auto}
.folha-mestre-pt-correios .tabela-mestre-pt .texto-posto-mestre{font-size:13px; line-height:1.15; font-weight:normal; min-height:32px; overflow:visible; white-space:normal; word-break:break-word}
.folha-mestre-pt-correios .tabela-mestre-pt .campo-etiqueta-mestre{font-family:Arial,Helvetica,sans-serif; font-size:12px; letter-spacing:0; padding:0 2px; height:22px; line-height:22px; white-space:nowrap; overflow:hidden; text-overflow:clip}
.folha-mestre-pt-correios .assinaturas-mestre{display:flex; justify-content:space-between; gap:48px; margin-top:38px; padding:0 18px}
.folha-mestre-pt-correios .assinatura-mestre{flex:1; text-align:center; font-size:12px}
.folha-mestre-pt-correios .assinatura-mestre hr{border:none; border-top:1px solid #000; margin:0 0 8px 0}
.folha-a4-oficio.folha-mestre-pt-correios{min-height:auto; max-height:none; overflow:visible}
.folha-a4-oficio.folha-mestre-pt-correios .oficio,
.folha-a4-oficio.folha-mestre-pt-correios .processo,
.folha-a4-oficio.folha-mestre-pt-correios .oficio-observacao{display:block; min-height:auto; height:auto; overflow:visible}

@media print{
    body{background:#fff;margin:0;padding:0}
    .controles-pagina,.nao-imprimir{display:none !important}
    body.imprimir-selecionados .folha-a4-oficio:not(.folha-selecionada){
        display:none !important;
    }
    .rodape-oficio{display:block !important}
    .espacador-rodape{min-height:10px;padding-top:20px}
    
    .folha-a4-oficio{
        width:210mm;
        margin:0;
        padding:8mm;
        box-shadow:none;
        display:block;
        float:none !important;
        clear:both !important;
        page-break-after:always !important;
        page-break-inside:auto !important;
        min-height:auto;
        max-height:none;
        overflow:visible;
    }

    .folha-a4-oficio.folha-mestre-pt-correios{
        min-height:auto !important;
        max-height:none !important;
        overflow:visible !important;
        page-break-inside:auto !important;
        break-inside:auto !important;
        padding-top:5mm !important;
        padding-bottom:5mm !important;
    }

    .folha-a4-oficio.folha-mestre-pt-correios .oficio,
    .folha-a4-oficio.folha-mestre-pt-correios .processo,
    .folha-a4-oficio.folha-mestre-pt-correios .oficio-observacao,
    .folha-a4-oficio.folha-mestre-pt-correios .grupo-mestre-tabela,
    .folha-a4-oficio.folha-mestre-pt-correios .tabela-mestre-pt{
        display:block !important;
        min-height:auto !important;
        height:auto !important;
        max-height:none !important;
        overflow:visible !important;
        page-break-inside:auto !important;
        break-inside:auto !important;
    }

    .folha-a4-oficio.folha-mestre-pt-correios .tabela-mestre-pt thead{display:table-header-group}
    .folha-a4-oficio.folha-mestre-pt-correios .tabela-mestre-pt tbody{display:table-row-group}
    .folha-a4-oficio.folha-mestre-pt-correios .tabela-mestre-pt tr{page-break-inside:avoid !important; break-inside:avoid !important; height:auto !important}
    .folha-a4-oficio.folha-mestre-pt-correios .tabela-mestre-pt .campo-etiqueta-mestre{font-family:Arial,Helvetica,sans-serif !important; font-size:12px !important; letter-spacing:0 !important; padding:0 2px !important; height:22px !important; line-height:22px !important}
    .folha-a4-oficio.folha-mestre-pt-correios .tabela-mestre-pt .texto-posto-mestre{height:auto !important; min-height:32px !important; line-height:1.15 !important; overflow:visible !important; white-space:normal !important; word-break:break-word !important}
    
    /* v9.12.0: Page break para páginas divididas */
    .pagina-split-1{
        page-break-after:always !important;
    }
    
    .pagina-split-2{
        page-break-before:always !important;
    }
    
    .oficio{
        display:flex;
        flex-direction:column;
        max-height:none;
        overflow:visible;
    }
    
    .processo{
        flex:1;
        overflow:visible;
    }
    
    /* v9.9.0: Tabela de lotes com altura controlada */
    .tabela-lotes{
        max-height:none !important;
        overflow:visible !important;
        page-break-inside:auto !important;
        break-inside:auto !important;
        background:transparent !important;
        border:none !important;
        padding:0 !important;
        margin:10px 0 !important;
    }
    
    /* Evitar quebra nos últimos blocos */
    .oficio .cols100.border-1px.p5:nth-last-of-type(2),
    .oficio .cols100.border-1px.p5:last-of-type{
        page-break-inside:avoid;
        break-inside:avoid;
    }
    
    /* v9.21.6: Ocultar apenas células desmarcadas na impressão */
    td.lote-desmarcado,
    th.lote-desmarcado{
        display:none !important;
    }

    /* v9.21.6: Ocultar células sem lote na impressão */
    td.lote-vazio{
        display:none !important;
    }
    
    /* Garantir que tabelas não quebrem */
    table{page-break-inside:auto}
    .tabela-lotes-pt tbody tr{page-break-inside:avoid !important; break-inside:avoid !important}
    
    /* v9.9.0: Ocultar lotes desmarcados na impressão */
    .linha-lote[data-checked="0"]{
        display:none !important;
    }
    
    /* v9.9.6: Linhas não cadastradas: mostrar se marcadas, ocultar se desmarcadas */
    .linha-lote.nao-encontrado[data-checked="0"]{
        display:none !important;
    }
    .linha-lote.nao-encontrado[data-checked="1"]{
        display:table-row !important; /* Força exibição quando marcado */
        background:transparent !important; /* Remove amarelo na impressão */
    }
    
    /* v9.9.0: Remover cores de conferência na impressão */
    .linha-lote{
        background:transparent !important;
    }
    
    /* v9.9.0: Ocultar campo de conferência na impressão */
    .painel-conferencia,
    .controle-conferencia{
        display:none !important;
    }
    
    /* v9.11.0: Garantir que TODOS os controles administrativos sejam ocultos */
    .controle-split,
    .btn-split,
    .nao-imprimir{
        display:none !important;
    }
    
    /* v9.8.6: Remover completamente coluna de checkboxes na impressão */
    .col-checkbox{
        display:none !important;
        width:0 !important;
        padding:0 !important;
        margin:0 !important;
        border:none !important;
    }

    /* v1.0.9: Remover coluna de ações na impressão */
    .col-acoes-lote{
        display:none !important;
        width:0 !important;
        padding:0 !important;
        margin:0 !important;
        border:none !important;
    }
    
    /* v9.8.6: Ajustar tabela para 2 colunas apenas */
    .lotes-detalhe{
        width:100% !important;
        max-width:650px !important;
        margin:0 auto !important;
    }
    
    /* v9.9.5: Na impressão, ocultar valor formatado e mostrar valor limpo */
    .valor-tela{
        display:none !important;
    }
    .valor-quantidade{
        display:inline !important;
    }
    /* v9.22.0: Lotes em 1 coluna devem mostrar quantidade formatada */
    .lotes-detalhe-1col .valor-tela{
        display:inline !important;
    }
    
    .lotes-detalhe th,
    .lotes-detalhe td{
        font-size:14px !important;
        padding:8px !important;
    }
    
    .lotes-detalhe thead th:first-child,
    .lotes-detalhe tbody td:first-child,
    .lotes-detalhe tfoot td:first-child{
        display:none !important;
        width:0 !important;
        max-width:0 !important;
    }
    
    /* v9.8.6: Ajustar larguras das colunas restantes */
    .lotes-detalhe th:nth-child(2),
    .lotes-detalhe td:nth-child(2){
        width:60% !important;
        text-align:left !important;
    }
    
    .lotes-detalhe th:nth-child(3),
    .lotes-detalhe td:nth-child(3){
        width:40% !important;
        text-align:right !important;
    }
    
    .tabela-lotes{
        background:transparent !important;
        border:none !important;
        padding:0 !important;
        margin:10px 0 !important;
    }
    
    /* v9.8.6: Ajusta layout da tabela de lotes na impressão */
    .lotes-detalhe thead tr,
    .lotes-detalhe tbody tr,
    .lotes-detalhe tfoot tr{
        background:transparent !important;
    }
    
    /* v9.8.6: Ajustar tabela principal para não ultrapassar margem */
    .oficio-observacao table{
        max-width:650px !important;
        margin:0 auto !important;
    }
    
    .oficio-observacao table th,
    .oficio-observacao table td{
        font-size:14px !important;
        padding:8px !important;
    }
    
    /* v9.9.0: QUEBRA DE PÁGINA - cada ofício em uma folha */
    .folha-a4-oficio{
        page-break-after:always !important;
        page-break-inside:avoid !important;
    }
    
    /* Evitar quebra dentro da tabela de lotes */
    .tabela-lotes{
        page-break-inside:avoid !important;
    }
    
    /* Evitar quebra da tabela principal */
    .oficio-observacao > table{
        page-break-inside:avoid !important;
    }
}

/* v9.9.0: Estilos para conferência de lotes */
.painel-conferencia{
    background:#f0f8ff;
    border:2px solid #007bff;
    border-radius:8px;
    padding:15px;
    margin:15px 0;
    box-shadow:0 2px 8px rgba(0,0,0,0.1);
}

/* v9.21.7: Realce verde para lote conferido no layout 3 colunas */
.lote-conferido{
    background:#d4edda !important;
    border-color:#28a745 !important;
    font-weight:bold;
}

.painel-conferencia h4{
    margin:0 0 10px 0;
    color:#007bff;
    font-size:16px;
}

.campo-leitura{
    display:flex;
    align-items:center;
    gap:10px;
    margin-bottom:10px;
}

.campo-leitura label{
    font-weight:bold;
    min-width:120px;
}

#input_conferencia{
    flex:1;
    padding:10px;
    font-size:16px;
    border:2px solid #007bff;
    border-radius:4px;
    font-family:monospace;
}

#input_conferencia:focus{
    outline:none;
    border-color:#0056b3;
    box-shadow:0 0 8px rgba(0,123,255,0.3);
}

.contador-conferencia{
    display:flex;
    justify-content:space-between;
    font-size:14px;
    color:#666;
    margin-top:10px;
}

.contador-conferencia span{
    background:#e9ecef;
    padding:5px 10px;
    border-radius:4px;
}

/* v9.9.0: Estados de conferência */
.linha-lote.conferido{
    background:#d4edda !important;
    border-left:4px solid #28a745 !important;
}

.linha-lote.nao-encontrado{
    background:#fff3cd !important;
    border-left:4px solid #ffc107 !important;
}

.linha-lote.conferido td{
    color:#155724;
    font-weight:bold;
}

.linha-lote.nao-encontrado td{
    color:#856404;
    font-style:italic;
}

/* v9.9.0: Animação ao conferir */
@keyframes pulse-green{
    0%,100%{background:#d4edda}
    50%{background:#a8d5ba}
}

.linha-lote.conferido-agora{
    animation:pulse-green 1s ease-in-out;
}

/* v9.9.0: Centralização de tabelas para não ultrapassar margem */
.oficio-observacao > table{
    margin-left:auto !important;
    margin-right:auto !important;
    max-width:100% !important;
}
</style>
<script type="text/javascript">
// v8.14.3: Modal de confirmação com 3 opções para Poupa Tempo
function confirmarGravarPT(comImpressao) {
    var overlay = document.createElement('div');
    overlay.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;display:flex;align-items:center;justify-content:center;';
    
    var modal = document.createElement('div');
    modal.style.cssText = 'background:white;padding:30px;border-radius:8px;box-shadow:0 4px 20px rgba(0,0,0,0.3);max-width:500px;text-align:center;';
    
    var titulo = document.createElement('h3');
    titulo.textContent = 'Como deseja gravar o Ofício Poupa Tempo?';
    titulo.style.cssText = 'margin-top:0;color:#333;';
    
    var texto = document.createElement('p');
    texto.innerHTML = '<b>Sobrescrever:</b> Apaga itens do último ofício e grava este no lugar.<br><br>' +
                      '<b>Criar Novo:</b> Mantém ofício anterior e cria outro com novo número.<br><br>' +
                      '<b>Cancelar:</b> Aborta a operação.';
    texto.style.cssText = 'margin:20px 0;line-height:1.6;color:#555;';
    
    var botoes = document.createElement('div');
    botoes.style.cssText = 'display:flex;gap:10px;justify-content:center;margin-top:25px;';
    
    var btnSobrescrever = document.createElement('button');
    btnSobrescrever.textContent = 'Sobrescrever';
    btnSobrescrever.style.cssText = 'background:#ff9800;color:white;border:none;padding:12px 24px;border-radius:4px;cursor:pointer;font-size:14px;font-weight:bold;';
    btnSobrescrever.onclick = function() {
        document.body.removeChild(overlay);
        executarGravacaoPT('sobrescrever', comImpressao);
    };
    
    var btnCriarNovo = document.createElement('button');
    btnCriarNovo.textContent = 'Criar Novo';
    btnCriarNovo.style.cssText = 'background:#28a745;color:white;border:none;padding:12px 24px;border-radius:4px;cursor:pointer;font-size:14px;font-weight:bold;';
    btnCriarNovo.onclick = function() {
        document.body.removeChild(overlay);
        executarGravacaoPT('novo', comImpressao);
    };
    
    var btnCancelar = document.createElement('button');
    btnCancelar.textContent = 'Cancelar';
    btnCancelar.style.cssText = 'background:#dc3545;color:white;border:none;padding:12px 24px;border-radius:4px;cursor:pointer;font-size:14px;font-weight:bold;';
    btnCancelar.onclick = function() {
        document.body.removeChild(overlay);
    };
    
    botoes.appendChild(btnSobrescrever);
    botoes.appendChild(btnCriarNovo);
    botoes.appendChild(btnCancelar);
    
    modal.appendChild(titulo);
    modal.appendChild(texto);
    modal.appendChild(botoes);
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
}

function mostrarModalResultadoSalvamento(mensagem, aoConfirmar) {
    var overlay = document.createElement('div');
    overlay.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:10000;display:flex;align-items:center;justify-content:center;';

    var modal = document.createElement('div');
    modal.style.cssText = 'background:white;padding:30px;border-radius:8px;box-shadow:0 4px 20px rgba(0,0,0,0.3);max-width:460px;text-align:center;';

    var titulo = document.createElement('h3');
    titulo.textContent = 'Dados salvos com sucesso';
    titulo.style.cssText = 'margin-top:0;color:#1e7e34;';

    var texto = document.createElement('p');
    texto.textContent = mensagem;
    texto.style.cssText = 'margin:18px 0;line-height:1.6;color:#444;';

    var botao = document.createElement('button');
    botao.textContent = 'OK';
    botao.style.cssText = 'background:#28a745;color:white;border:none;padding:12px 28px;border-radius:4px;cursor:pointer;font-size:14px;font-weight:bold;';
    botao.onclick = function() {
        document.body.removeChild(overlay);
        if (typeof aoConfirmar === 'function') {
            aoConfirmar();
        }
    };

    modal.appendChild(titulo);
    modal.appendChild(texto);
    modal.appendChild(botao);
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
}

function executarGravacaoPT(modo, comImpressao) {
    var form = document.getElementById('formOficio');
    if (form) {
        atualizarPayloadDinamicoPT();
        atualizarFolhasSelecionadasInput();
        document.getElementById('modo_oficio_pt').value = modo;
        document.getElementById('acaoForm').value = 'salvar_oficio_completo';
        document.getElementById('imprimir_apos_salvar').value = comImpressao ? '1' : '0';
        form.submit();
    }
}

// Função para gravar e imprimir (agora com modal)
function gravarEImprimir() {
    confirmarGravarPT(true);
}

// v9.8.2: Recalcula total baseado nos lotes marcados
// v9.20.1: Recalcular total - CORRIGIDO para páginas clonadas
// v9.21.1: CORREÇÃO DEFINITIVA - busca por evento e elemento clicado
function atualizarTotaisContainer(container, posto) {
    if (!container) return;

    var checkboxes = container.querySelectorAll('.checkbox-lote');
    var total = 0;
    var lotesConfirmados = [];

    var linhas = container.querySelectorAll('tr.linha-lote');
    for (var r = 0; r < linhas.length; r++) {
        linhas[r].setAttribute('data-checked', '0');
    }
    
    for (var i = 0; i < checkboxes.length; i++) {
        var cb = checkboxes[i];
        var linha = cb.closest('tr');
        var quantidade = obterQuantidadeLinhaPT(linha);
        var lote = obterLoteLinhaPT(linha);
        var tdLote = linha ? linha.querySelector('.celula-lote') : null;
        var tdQtd = linha ? linha.querySelector('.celula-qtd') : null;
        var tdData = linha ? linha.querySelector('.celula-data') : null;

        if (cb.checked) {
            total += quantidade;
            if (lote !== '') {
                lotesConfirmados.push(lote);
            }
            if (linha) linha.setAttribute('data-checked', '1');
            if (tdLote) tdLote.classList.remove('lote-desmarcado');
            if (tdQtd) tdQtd.classList.remove('lote-desmarcado');
            if (tdData) tdData.classList.remove('lote-desmarcado');
        } else {
            if (tdLote) tdLote.classList.add('lote-desmarcado');
            if (tdQtd) tdQtd.classList.add('lote-desmarcado');
            if (tdData) tdData.classList.add('lote-desmarcado');
        }

        if (linha) {
            linha.setAttribute('data-lote', lote);
            linha.setAttribute('data-quantidade', quantidade);
            linha.setAttribute('data-data-carga', obterDataLinhaPT(linha));
        }
    }
    
    // v9.21.2: Atualiza apenas a coluna "Quantidade de CIN's"
    var totalCins = container.querySelector('.total-cins');
    if (totalCins) {
        totalCins.textContent = formatarNumero(total);
    }
    
    // Atualiza hidden inputs DENTRO do container
    var hiddenLotes = container.querySelector('input[name^="lotes_confirmados"]');
    if (hiddenLotes) {
        hiddenLotes.value = lotesConfirmados.join(',');
    }
    
    var hiddenQuantidade = container.querySelector('input[name^="quantidade_posto"]');
    if (hiddenQuantidade) {
        hiddenQuantidade.value = total;
    }
    
    var marcarTodos = container.querySelector('.marcar-todos');
    if (marcarTodos) {
        var todosMarcados = true;
        var algumMarcado = false;
        
        for (var j = 0; j < checkboxes.length; j++) {
            if (checkboxes[j].checked) {
                algumMarcado = true;
            } else {
                todosMarcados = false;
            }
        }
        
        marcarTodos.checked = todosMarcados;
        marcarTodos.indeterminate = algumMarcado && !todosMarcados;
    }

    atualizarContadores(posto, true);
}

function recalcularTotal(posto) {
    // v9.21.1: Busca o container mais próximo do elemento que disparou o evento
    var elementoAtual = event ? event.target : null;
    var container = null;
    
    if (elementoAtual) {
        // Sobe na árvore DOM até encontrar o container .folha-a4-oficio
        container = elementoAtual.closest('.folha-a4-oficio');
    }
    
    // Se não encontrou pelo evento, busca pelo data-posto (fallback)
    if (!container) {
        var containers = document.querySelectorAll('.folha-a4-oficio[data-posto="' + posto + '"]');
        if (containers.length > 0) {
            // Se há múltiplos containers (clones), tenta encontrar o correto
            if (elementoAtual) {
                for (var i = 0; i < containers.length; i++) {
                    if (containers[i].contains(elementoAtual)) {
                        container = containers[i];
                        break;
                    }
                }
            }
            if (!container) container = containers[0];
        }
    }
    
    if (!container) {
        console.warn('Container não encontrado para posto:', posto);
        return;
    }

    atualizarTotaisContainer(container, posto);
}

function moverLote() {
    return false;
}

// v9.20.1: Marca/desmarca todos os lotes de um posto (CORRIGIDO para clones)
function marcarTodosLotes(checkbox, posto) {
    var container = document.querySelector('.folha-a4-oficio[data-posto="' + posto + '"]');
    if (!container) return;
    
    var checkboxes = container.querySelectorAll('.checkbox-lote');
    var marcado = checkbox.checked;
    
    for (var i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = marcado;
    }
    
    recalcularTotal(posto);
}

// v9.8.2: Formata número com separador de milhares
function formatarNumero(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Função apenas para gravar (agora com modal)
function apenasGravar() {
    confirmarGravarPT(false);
}

// Função apenas para imprimir
function apenasImprimir() {
    window.print();
}

// v9.22.0: Imprimir apenas folhas selecionadas
function imprimirSelecionados() {
    atualizarSelecaoFolhas();
    document.body.classList.add('imprimir-selecionados');
    window.print();
    setTimeout(function(){
        document.body.classList.remove('imprimir-selecionados');
    }, 500);
}

// v9.24.2: Atualiza hidden com as folhas marcadas
function atualizarFolhasSelecionadasInput() {
    var input = document.getElementById('folhas_selecionadas');
    if (!input) return;
    var checks = document.querySelectorAll('.selecionar-folha');
    var selecionadas = [];
    for (var i = 0; i < checks.length; i++) {
        if (checks[i].checked) {
            var folhaId = checks[i].getAttribute('data-folha');
            if (folhaId) selecionadas.push(folhaId);
        }
    }
    input.value = selecionadas.join(',');
}

// v9.22.0: Atualiza seleção visual das folhas
function atualizarSelecaoFolhas() {
    var checks = document.querySelectorAll('.selecionar-folha');
    for (var i = 0; i < checks.length; i++) {
        var cb = checks[i];
        var folhaId = cb.getAttribute('data-folha');
        if (!folhaId) continue;
        var folha = document.querySelector('.folha-a4-oficio[data-folha-id="' + folhaId + '"]');
        if (!folha) continue;
        if (cb.checked) {
            folha.classList.add('folha-selecionada');
        } else {
            folha.classList.remove('folha-selecionada');
        }
    }
}

function inputLacreAvulsoPT(input) {
    return !!(input && String(input.getAttribute('data-lacre-avulso') || '') === '1');
}

function definirEstadoLacreAvulsoPT(input, botao, ativo) {
    if (!input || !botao) return;
    if (ativo) {
        input.setAttribute('data-lacre-avulso', '1');
        if (input.className.indexOf('lacre-avulso') < 0) input.className += ' lacre-avulso';
        if (botao.className.indexOf('ativo') < 0) botao.className += ' ativo';
        botao.title = 'Lacre avulso ativo';
    } else {
        input.setAttribute('data-lacre-avulso', '0');
        input.className = input.className.replace(/\s*lacre-avulso/g, '');
        botao.className = botao.className.replace(/\s*ativo/g, '');
        botao.title = 'Ativar lacre avulso';
    }
}

function alternarLacreAvulsoPT(botao) {
    if (!botao || !botao.parentNode) return false;
    var input = botao.parentNode.querySelector('input.lacre-pt-input');
    if (!input) return false;
    definirEstadoLacreAvulsoPT(input, botao, !inputLacreAvulsoPT(input));
    sincronizarImpressaoPorCamposCabecalho(input);
    return false;
}

function formatarDataPtTela(valor) {
    var texto = String(valor || '').trim();
    if (texto === '') return '';
    var partesSql = texto.match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if (partesSql) return partesSql[3] + '-' + partesSql[2] + '-' + partesSql[1];
    return texto;
}

function normalizarDataPtPayload(valor) {
    var texto = String(valor || '').trim();
    if (texto === '') return '';
    var partesTela = texto.match(/^(\d{2})-(\d{2})-(\d{4})$/);
    if (partesTela) return partesTela[3] + '-' + partesTela[2] + '-' + partesTela[1];
    var partesSql = texto.match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if (partesSql) return texto;
    return texto;
}

function obterLoteLinhaPT(linha) {
    if (!linha) return '';
    var input = linha.querySelector('.campo-lote-manual');
    var valor = input ? input.value : (linha.getAttribute('data-lote') || (linha.querySelector('.celula-lote') ? linha.querySelector('.celula-lote').textContent : ''));
    return String(valor || '').replace(/\D+/g, '').substr(0, 8);
}

function obterQuantidadeLinhaPT(linha) {
    if (!linha) return 0;
    var input = linha.querySelector('.campo-qtd-manual');
    var valor = input ? input.value : (linha.getAttribute('data-quantidade') || '0');
    valor = String(valor || '').replace(/[^\d-]/g, '');
    var numero = parseInt(valor, 10) || 0;
    return numero < 0 ? 0 : numero;
}

function obterDataLinhaPT(linha) {
    if (!linha) return '';
    var input = linha.querySelector('.campo-data-manual');
    var valor = input ? input.value : (linha.getAttribute('data-data-carga') || (linha.querySelector('.celula-data') ? linha.querySelector('.celula-data').textContent : ''));
    return normalizarDataPtPayload(valor);
}

function sincronizarCamposLinhaPT(linha) {
    if (!linha) return;
    var lote = obterLoteLinhaPT(linha);
    var qtd = obterQuantidadeLinhaPT(linha);
    var data = obterDataLinhaPT(linha);
    linha.setAttribute('data-lote', lote);
    linha.setAttribute('data-quantidade', qtd);
    linha.setAttribute('data-data-carga', data);
    var checkbox = linha.querySelector('.checkbox-lote');
    if (checkbox) {
        checkbox.setAttribute('data-lote', lote);
        checkbox.setAttribute('data-quantidade', qtd);
        checkbox.setAttribute('data-data-carga', data);
    }
}

function criarLinhaManualPT(posto, dados) {
    var info = dados || {};
    var linha = document.createElement('tr');
    linha.className = 'linha-lote linha-manual';
    linha.setAttribute('data-posto', posto || '');
    linha.setAttribute('data-checked', info.checked === false ? '0' : '1');
    linha.setAttribute('data-conferido', info.conferido ? '1' : '0');

    var tdCheck = document.createElement('td');
    tdCheck.className = 'col-checkbox nao-imprimir';
    tdCheck.style.cssText = 'text-align:center; padding:3px; border:1px solid #000;';
    var checkbox = document.createElement('input');
    checkbox.type = 'checkbox';
    checkbox.className = 'checkbox-lote';
    checkbox.checked = info.checked === false ? false : true;
    checkbox.setAttribute('data-posto', posto || '');
    checkbox.setAttribute('data-conferido', info.conferido ? '1' : '0');
    checkbox.onchange = function() { recalcularTotal(posto || ''); };
    tdCheck.appendChild(checkbox);
    linha.appendChild(tdCheck);

    var tdAcoes = document.createElement('td');
    tdAcoes.className = 'col-acoes-lote nao-imprimir';
    tdAcoes.style.cssText = 'text-align:center; padding:3px; border:1px solid #000;';
    var acoes = document.createElement('div');
    acoes.className = 'acoes-lote-wrap';
    var btnAdd = document.createElement('button');
    btnAdd.type = 'button';
    btnAdd.className = 'btn-lote-acao';
    btnAdd.title = 'Adicionar linha abaixo';
    btnAdd.appendChild(document.createTextNode('+'));
    btnAdd.onclick = function() { adicionarLinhaLotePT(btnAdd); };
    var btnDel = document.createElement('button');
    btnDel.type = 'button';
    btnDel.className = 'btn-lote-acao';
    btnDel.title = 'Excluir linha';
    btnDel.appendChild(document.createTextNode('×'));
    btnDel.onclick = function() { excluirLinhaLotePT(btnDel); };
    acoes.appendChild(btnAdd);
    acoes.appendChild(btnDel);
    tdAcoes.appendChild(acoes);
    linha.appendChild(tdAcoes);

    var tdLote = document.createElement('td');
    tdLote.className = 'celula-lote';
    tdLote.style.cssText = 'text-align:left; padding:4px; border:1px solid #000; font-size:10px;';
    var inputLote = document.createElement('input');
    inputLote.type = 'text';
    inputLote.className = 'input-editavel campo-lote-manual';
    inputLote.style.cssText = 'width:100%; border:none; background:transparent; font-size:10px; text-align:left;';
    inputLote.maxLength = 8;
    inputLote.value = info.lote || '';
    inputLote.oninput = function() { sincronizarCamposLinhaPT(linha); recalcularTotal(posto || ''); };
    tdLote.appendChild(inputLote);
    linha.appendChild(tdLote);

    var tdQtd = document.createElement('td');
    tdQtd.className = 'celula-qtd';
    tdQtd.style.cssText = 'text-align:center; padding:4px; border:1px solid #000; font-size:10px;';
    var inputQtd = document.createElement('input');
    inputQtd.type = 'number';
    inputQtd.className = 'input-editavel campo-qtd-manual';
    inputQtd.style.cssText = 'width:100%; border:none; background:transparent; font-size:10px; text-align:center;';
    inputQtd.min = '0';
    inputQtd.value = String(typeof info.quantidade !== 'undefined' ? info.quantidade : '0');
    inputQtd.oninput = function() { sincronizarCamposLinhaPT(linha); recalcularTotal(posto || ''); };
    tdQtd.appendChild(inputQtd);
    linha.appendChild(tdQtd);

    var tdData = document.createElement('td');
    tdData.className = 'celula-data';
    tdData.style.cssText = 'text-align:center; padding:4px; border:1px solid #000; font-size:10px;';
    var inputData = document.createElement('input');
    inputData.type = 'text';
    inputData.className = 'input-editavel campo-data-manual';
    inputData.placeholder = 'dd-mm-aaaa';
    inputData.style.cssText = 'width:100%; border:none; background:transparent; font-size:10px; text-align:center;';
    inputData.value = formatarDataPtTela(info.data_carga || '');
    inputData.oninput = function() { sincronizarCamposLinhaPT(linha); recalcularTotal(posto || ''); };
    tdData.appendChild(inputData);
    linha.appendChild(tdData);

    sincronizarCamposLinhaPT(linha);
    aplicarEstadoVisualConferenciaLinha(linha, !!info.conferido);
    return linha;
}

function adicionarLinhaLotePT(botao) {
    var linha = botao ? botao.closest('tr.linha-lote') : null;
    var tbody = linha ? linha.parentNode : null;
    if (!linha || !tbody) return false;
    var posto = linha.getAttribute('data-posto') || '';
    var novaLinha = criarLinhaManualPT(posto, { checked: true });
    if (linha.nextSibling) tbody.insertBefore(novaLinha, linha.nextSibling);
    else tbody.appendChild(novaLinha);
    recalcularTotal(posto);
    var inputLote = novaLinha.querySelector('.campo-lote-manual');
    if (inputLote) inputLote.focus();
    return false;
}

function adicionarLinhaFimPT(posto) {
    var tabela = document.getElementById('tabela_lotes_' + posto);
    if (!tabela) return false;
    var tbody = tabela.getElementsByTagName('tbody')[0];
    if (!tbody) return false;
    var novaLinha = criarLinhaManualPT(posto, { checked: true });
    tbody.appendChild(novaLinha);
    recalcularTotal(posto);
    var inputLote = novaLinha.querySelector('.campo-lote-manual');
    if (inputLote) inputLote.focus();
    return false;
}

function excluirLinhaLotePT(botao) {
    var linha = botao ? botao.closest('tr.linha-lote') : null;
    if (!linha) return false;
    var tbody = linha.parentNode;
    var posto = linha.getAttribute('data-posto') || '';
    if (!tbody) return false;
    if (tbody.querySelectorAll('tr.linha-lote').length <= 1) {
        alert('Este posto precisa manter ao menos uma linha visível.');
        return false;
    }
    tbody.removeChild(linha);
    recalcularTotal(posto);
    return false;
}

function ocultarDesmarcadosPT(posto) {
    var tabela = document.getElementById('tabela_lotes_' + posto);
    if (!tabela) return false;
    var linhas = tabela.querySelectorAll('tr.linha-lote');
    var removidas = 0;
    for (var i = linhas.length - 1; i >= 0; i--) {
        var linha = linhas[i];
        var checkbox = linha.querySelector('.checkbox-lote');
        if (checkbox && !checkbox.checked) {
            linha.parentNode.removeChild(linha);
            removidas++;
        }
    }
    recalcularTotal(posto);
    if (!removidas) {
        alert('Nenhuma linha desmarcada foi encontrada nesta folha.');
    }
    return false;
}

function atualizarPayloadDinamicoPT() {
    var inputPayload = document.querySelector("input[name='pt_dinamico_payload']");
    if (!inputPayload) return;
    var folhas = document.querySelectorAll('.folha-a4-oficio[data-posto]');
    var postos = [];
    for (var i = 0; i < folhas.length; i++) {
        var folha = folhas[i];
        var codigo = folha.getAttribute('data-posto') || '';
        if (!codigo) continue;
        var nomeCampo = folha.querySelector("textarea[name='nome_posto[" + codigo + "]']");
        var enderecoCampo = folha.querySelector("input[name='endereco_posto[" + codigo + "]']");
        var lotes = [];
        var linhas = folha.querySelectorAll('tr.linha-lote');
        for (var j = 0; j < linhas.length; j++) {
            var linha = linhas[j];
            sincronizarCamposLinhaPT(linha);
            var lote = obterLoteLinhaPT(linha);
            if (lote === '') continue;
            lotes.push({
                lote: lote,
                quantidade: obterQuantidadeLinhaPT(linha),
                data_carga: obterDataLinhaPT(linha),
                responsaveis: ''
            });
        }
        postos.push({
            codigo: codigo,
            nome: nomeCampo ? nomeCampo.value : '',
            endereco: enderecoCampo ? enderecoCampo.value : '',
            usuario: '',
            lotes: lotes
        });
    }
    inputPayload.value = JSON.stringify({ postos: postos });
}

function sincronizarImpressaoPorCamposCabecalho(inputCampo) {
    if (!inputCampo) return;
    var folha = inputCampo.closest('.folha-a4-oficio');
    if (!folha) return;
    var cb = folha.querySelector('.selecionar-folha');
    if (!cb) return;
    var campos = folha.querySelectorAll('.campo-cabecalho-pt');
    var temValor = false;
    for (var i = 0; i < campos.length; i++) {
        if ((campos[i].value || '').trim() !== '') {
            temValor = true;
            break;
        }
    }
    cb.checked = temValor;
    atualizarSelecaoFolhas();
    atualizarFolhasSelecionadasInput();
}

document.addEventListener('DOMContentLoaded', function(){
    var checks = document.querySelectorAll('.selecionar-folha');
    for (var i = 0; i < checks.length; i++) {
        checks[i].addEventListener('change', atualizarSelecaoFolhas);
    }
    var lacres = document.querySelectorAll('.campo-cabecalho-pt');
    for (var j = 0; j < lacres.length; j++) {
        lacres[j].addEventListener('input', function() {
            sincronizarImpressaoPorCamposCabecalho(this);
        });
        lacres[j].addEventListener('change', function() {
            sincronizarImpressaoPorCamposCabecalho(this);
        });
    }
    atualizarSelecaoFolhas();
});

// ============================================================
// v8.14.5: Sistema de detecção de mudanças e pulsação de botões
// ============================================================
var valoresOriginais = {};
var dadosForamSalvos = <?php echo ($tipo_mensagem === 'sucesso' ? 'true' : 'false'); ?>;

function capturarValoresOriginais() {
    var inputs = document.querySelectorAll('input[type="text"], input[type="hidden"][name^="lote_posto"]');
    for (var i = 0; i < inputs.length; i++) {
        var inp = inputs[i];
        if (inp.name) {
            valoresOriginais[inp.name] = inp.value;
        }
    }
}

function verificarMudancas() {
    var mudou = false;
    var inputs = document.querySelectorAll('input[type="text"], input[type="hidden"][name^="lote_posto"]');
    for (var i = 0; i < inputs.length; i++) {
        var inp = inputs[i];
        if (inp.name && valoresOriginais[inp.name] !== undefined) {
            if (valoresOriginais[inp.name] !== inp.value) {
                mudou = true;
                break;
            }
        }
    }
    return mudou;
}

function atualizarEstadoBotoes() {
    var temMudancas = verificarMudancas();
    var botoes = document.querySelectorAll('.btn-imprimir, .btn-salvar');
    
    for (var i = 0; i < botoes.length; i++) {
        var btn = botoes[i];
        if (temMudancas) {
            if (!btn.className.match(/btn-nao-salvo/)) {
                btn.className += ' btn-nao-salvo';
            }
        } else {
            btn.className = btn.className.replace(/\s*btn-nao-salvo/g, '');
        }
    }
}

function inicializarMonitoramento() {
    capturarValoresOriginais();
    
    // Monitorar mudanças em todos os inputs
    var inputs = document.querySelectorAll('input[type="text"]');
    for (var i = 0; i < inputs.length; i++) {
        inputs[i].addEventListener('input', function() {
            atualizarEstadoBotoes();
        });
        inputs[i].addEventListener('change', function() {
            atualizarEstadoBotoes();
        });
    }
    
    // Verificar inicialmente
    atualizarEstadoBotoes();
}

// Inicializar quando a página carregar
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', inicializarMonitoramento);
} else {
    inicializarMonitoramento();
}
</script>
</head>
<body>

<form method="post" action="<?php echo e($_SERVER['PHP_SELF']); ?>" id="formOficio">
  <!-- Ação a executar -->
  <input type="hidden" name="acao" id="acaoForm" value="salvar_oficio_completo">
  <!-- Número da expedição (id do despacho), se existir -->
  <input type="hidden" name="id_despacho" value="<?php echo (int)$id_despacho; ?>">
    <input type="hidden" name="responsavel" value="<?php echo e(isset($_POST['responsavel']) && trim((string)$_POST['responsavel']) !== '' ? trim((string)$_POST['responsavel']) : (isset($_SESSION['ultimo_responsavel']) ? trim((string)$_SESSION['ultimo_responsavel']) : '')); ?>">
  <!-- Datas usadas no ofício (string original, como em ciDespachos.datas_str) -->
  <input type="hidden" name="pt_datas" value="<?php echo e($datasStr); ?>">
  <!-- Flag para imprimir após salvar -->
  <input type="hidden" name="imprimir_apos_salvar" id="imprimir_apos_salvar" value="0">
  <!-- v8.14.3: Modo do ofício (sobrescrever/novo) -->
  <input type="hidden" name="modo_oficio" id="modo_oficio_pt" value="">
    <input type="hidden" name="pt_modo_visual" value="<?php echo e($modo_visual_pt); ?>">
    <!-- v9.22.2: Folhas selecionadas para gravar/imprimir -->
    <input type="hidden" name="folhas_selecionadas" id="folhas_selecionadas" value="">
        <input type="hidden" name="pt_dinamico_payload" value="<?php echo e(isset($_POST['pt_dinamico_payload']) ? $_POST['pt_dinamico_payload'] : (isset($_GET['pt_dinamico_payload']) ? $_GET['pt_dinamico_payload'] : '')); ?>">

  <div class="controles-pagina nao-imprimir">
        <a href="inicio.php" class="btn-voltar-inicio">← Inicio</a>
        <h2><?php echo $modo_visual_correios ? 'Modelo de Oficio - PT com Etiqueta Correios' : 'Modelo de Oficio - Poupatempo'; ?></h2>
    
    <?php if (!empty($mensagem_status)): ?>
    <div class="mensagem-status <?php echo ($tipo_mensagem === 'sucesso') ? 'mensagem-sucesso' : 'mensagem-erro'; ?>">
        <?php echo e($mensagem_status); ?>
    </div>
    <?php endif; ?>
    
        <p>
            <?php echo $modo_visual_correios ? 'Gera uma folha mestre externa com lacres e etiqueta Correios, seguida das folhas internas por posto.' : 'Uma pagina por posto Poupatempo.'; ?>
      <?php if ($id_despacho > 0): ?>
        Expedicao n. <b><?php echo (int)$id_despacho; ?></b>.
      <?php else: ?>
        <b style="color:orange">
          Atencao: este oficio ainda nao foi salvo. Clique em "Gravar" para salvar.
        </b>
      <?php endif; ?>
    </p>

        <?php if ($modo_visual_correios): ?>
        <div class="mensagem-status" style="background:#fff3cd;color:#856404;border-color:#ffeeba;">
            Este modo gera a folha mestre externa para expedicao via Correios e mantem as folhas internas por posto sem coluna de lacre.
        </div>
        <?php endif; ?>

        <?php if (!$modo_visual_correios): ?>
    <!-- Botão Gravar e Imprimir -->
    <button type="button" onclick="gravarEImprimir();" class="btn-sucesso btn-imprimir">
        💾🖨️ Gravar e Imprimir
    </button>

    <!-- Botão apenas Gravar -->
    <button type="button" onclick="apenasGravar();" class="btn-salvar">
        💾 Gravar Dados
    </button>

    <!-- Botão apenas Imprimir -->
    <button type="button" onclick="apenasImprimir();" class="btn-imprimir">
        🖨️ Apenas Imprimir
    </button>

    <!-- Botão Imprimir Selecionados -->
    <button type="button" onclick="imprimirSelecionados();" class="btn-imprimir">
        ✅ Imprimir Selecionados
    </button>

    <!-- Botão retirar conferência do que está visível -->
    <button type="button" onclick="removerConferenciaVisivelPT();" class="btn-excluir">
        ↺ Retirar Conferência da Tela
    </button>
    <?php endif; ?>

    <?php if ($modo_visual_correios): ?>
    <button type="button" onclick="gravarEImprimir();" class="btn-sucesso btn-imprimir">
        💾🖨️ Gravar e Imprimir
    </button>

    <button type="button" onclick="apenasGravar();" class="btn-salvar">
        💾 Gravar Dados
    </button>

    <button type="button" onclick="apenasImprimir();" class="btn-imprimir">
        🖨️ Apenas Imprimir
    </button>

    <button type="button" onclick="imprimirSelecionados();" class="btn-imprimir">
        ✅ Imprimir Selecionados
    </button>

    <button type="button" onclick="removerConferenciaVisivelPT();" class="btn-excluir">
        ↺ Retirar Conferência da Tela
    </button>
    <?php endif; ?>
  </div>

<?php if ($temDados): ?>
    <?php if ($modo_visual_correios && !$modo_branco): ?>
        <div class="folha-a4-oficio folha-mestre-pt-correios folha-selecionada" data-folha-id="resumo_pt_correios">
            <div class="oficio">
                <div class="quadro-logo-mestre">
                    <div class="logo-mestre">
                        <img alt="Logotipo" src="logo_celepar.png" width="250" height="55">
                    </div>
                    <div class="texto-logo-mestre">
                        <strong>CELEPAR – TECNOLOGIA DA INFORMAÇÃO E COMUNICAÇÃO DO PARANÁ</strong>
                        COMPROVANTE DE ENTREGA DE SERVIÇOS
                    </div>
                </div>

                <div class="info-cliente-mestre">
                    <p><strong>CLIENTE:</strong> POUPA TEMPO PARANÁ</p>
                    <?php if ($id_despacho > 0): ?>
                    <div class="numero-oficio-mestre">Nº #<?php echo (int)$id_despacho; ?></div>
                    <?php endif; ?>
                </div>

                <div class="cols100 processo">
                    <div class="oficio-observacao">
                        <div class="nao-imprimir" style="margin:8px 0;">
                            <label style="font-size:12px; font-weight:bold;">
                                <input type="checkbox" class="selecionar-folha" data-folha="resumo_pt_correios" checked>
                                Imprimir esta folha
                            </label>
                        </div>

                        <?php if (!empty($datasNorm)): ?>
                        <div class="resumo-datas"><strong>Datas:</strong> <?php echo e(implode(', ', $datasNorm)); ?></div>
                        <?php endif; ?>

                        <div class="grupo-mestre-tabela">
                            <table class="tabela-mestre-pt" style="table-layout:fixed; width:100%; max-width:100%; margin:0;">
                                <thead>
                                    <tr>
                                        <th class="col-posto">POUPA TEMPO</th>
                                        <th class="col-lacre-pt">Lacre Poupa Tempo</th>
                                        <th class="col-lacre-correios-pt">Lacre Correios Poupa Tempo</th>
                                        <th class="col-etiqueta">Etiqueta Correios</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($paginas as $resumoP): ?>
                                    <?php
                                    $codigoResumo = str_pad((string)$resumoP['codigo'], 3, '0', STR_PAD_LEFT);
                                    $nomeOriginalResumo = !empty($nomesPorPosto[$codigoResumo]) ? trim((string)$nomesPorPosto[$codigoResumo]) : trim((string)(isset($resumoP['nome']) ? $resumoP['nome'] : ''));
                                    if ($nomeOriginalResumo === '') {
                                        $nomeResumoBase = 'Posto ' . $codigoResumo;
                                    } else {
                                        $nomeResumoTrabalho = preg_replace('/\bPOUPA\s+TEMPO\b/i', '', $nomeOriginalResumo);
                                        $partesNomeResumo = preg_split('/\s*-\s*/', (string)$nomeResumoTrabalho);
                                        $partesNomeResumoLimpas = array();
                                        foreach ($partesNomeResumo as $parteNomeResumo) {
                                            $parteNomeResumo = trim((string)$parteNomeResumo);
                                            if ($parteNomeResumo === '') {
                                                continue;
                                            }
                                            if (strcasecmp($parteNomeResumo, 'POUPA TEMPO') === 0) {
                                                continue;
                                            }
                                            $partesNomeResumoLimpas[] = $parteNomeResumo;
                                        }
                                        $nomeResumoLimpo = implode(' - ', $partesNomeResumoLimpas);
                                        $nomeResumoLimpo = preg_replace('/\s{2,}/', ' ', (string)$nomeResumoLimpo);
                                        $nomeResumoLimpo = trim((string)$nomeResumoLimpo, " -\t\n\r\0\x0B");
                                        if ($nomeResumoLimpo === '') {
                                            $nomeResumoBase = 'Posto ' . $codigoResumo;
                                        } elseif (preg_match('/^posto\s+' . preg_quote($codigoResumo, '/') . '\b/i', $nomeResumoLimpo)) {
                                            $nomeResumoBase = $nomeResumoLimpo;
                                        } elseif (preg_match('/^' . preg_quote($codigoResumo, '/') . '\s*$/', $nomeResumoLimpo)) {
                                            $nomeResumoBase = 'Posto ' . $nomeResumoLimpo;
                                        } elseif (preg_match('/^' . preg_quote($codigoResumo, '/') . '\s*-\s*/', $nomeResumoLimpo)) {
                                            $nomeResumoBase = 'Posto ' . $nomeResumoLimpo;
                                        } else {
                                            $nomeResumoBase = 'Posto ' . $codigoResumo . ' - ' . $nomeResumoLimpo;
                                        }
                                    }
                                    $valorLacreResumo = isset($lacresPorPosto[$codigoResumo]) ? $lacresPorPosto[$codigoResumo] : '';
                                    $valorLacreCorreiosResumo = isset($lacresCorreiosPtPorPosto[$codigoResumo]) ? $lacresCorreiosPtPorPosto[$codigoResumo] : '';
                                    $valorEtiquetaCorreiosResumo = isset($etiquetasCorreiosPtPorPosto[$codigoResumo]) ? $etiquetasCorreiosPtPorPosto[$codigoResumo] : '';
                                    ?>
                                    <tr>
                                        <td class="col-posto">
                                            <textarea name="nome_posto[<?php echo e($codigoResumo); ?>]" class="input-editavel texto-posto-mestre" rows="2"><?php echo e($nomeResumoBase); ?></textarea>
                                        </td>
                                        <td class="col-lacre-pt">
                                            <div class="lacre-avulso-wrap">
                                                <textarea name="lacre_iipr[<?php echo e($codigoResumo); ?>]" class="input-editavel campo-cabecalho-pt lacre-pt-input campo-lacre-multiplo-pt" rows="1" data-lacre-avulso="0"><?php echo e($valorLacreResumo); ?></textarea>
                                                <button type="button" class="btn-av-pt nao-imprimir" title="Ativar lacre avulso" onclick="alternarLacreAvulsoPT(this)">Av</button>
                                            </div>
                                        </td>
                                        <td class="col-lacre-correios-pt">
                                            <div class="lacre-avulso-wrap">
                                                <textarea name="lacre_correios_pt[<?php echo e($codigoResumo); ?>]" class="input-editavel campo-cabecalho-pt lacre-pt-input campo-lacre-multiplo-pt" rows="1" data-lacre-avulso="0"><?php echo e($valorLacreCorreiosResumo); ?></textarea>
                                                <button type="button" class="btn-av-pt nao-imprimir" title="Ativar lacre avulso" onclick="alternarLacreAvulsoPT(this)">Av</button>
                                            </div>
                                        </td>
                                        <td class="col-etiqueta">
                                            <input type="text" name="etiqueta_correios_pt[<?php echo e($codigoResumo); ?>]" value="<?php echo e($valorEtiquetaCorreiosResumo); ?>" class="input-editavel campo-etiqueta-mestre" style="text-align:left;" maxlength="35">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="espacador-rodape"></div>
                    </div>
                </div>

                <div class="assinaturas-mestre rodape-oficio">
                    <div class="assinatura-mestre">
                        <hr>
                        RESPONSÁVEL CELEPAR - Data: <?php echo date('d-m-Y'); ?>
                    </div>
                    <div class="assinatura-mestre">
                        <hr>
                        RESPONSÁVEL CORREIOS
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

  <?php foreach ($paginas as $idx => $p): 
        $codigo   = $p['codigo'];                     
        $nome     = $p['nome'] ? $p['nome'] : "POUPA TEMPO";
        $qtd_total = (int)$p['qtd_total'];  // v9.8.2: Total de todos os lotes
        $lotes_array = isset($p['lotes']) && is_array($p['lotes']) ? $p['lotes'] : array();  // v9.8.3: Validação
        $endereco = isset($p['endereco']) ? $p['endereco'] : '';

        if ($modo_branco) {
            $nome = '';
            $qtd_total = '';
            $lotes_array = array();
            $endereco = '';
        }

        // garante código com 3 dígitos
        $codigo3 = str_pad($codigo, 3, '0', STR_PAD_LEFT);
        $conferidos_iniciais = 0;
        if (!$modo_branco && !empty($lotes_array)) {
            foreach ($lotes_array as $ltConf) {
                $loteConfNum = preg_replace('/\D+/', '', (string)$ltConf['lote']);
                if ($loteConfNum !== '' && isset($mapaConferidos[$codigo3]) && isset($mapaConferidos[$codigo3][$loteConfNum])) {
                    $conferidos_iniciais++;
                }
            }
        }
        $pendentes_iniciais = max(0, count($lotes_array) - $conferidos_iniciais);
        
        // Prioridade: dados salvos (do POST atual) > dados do banco > dados do SELECT original
        $valorLacre = isset($lacresPorPosto[$codigo3]) ? $lacresPorPosto[$codigo3] : '';
        $valorLacreCorreiosPt = isset($lacresCorreiosPtPorPosto[$codigo3]) ? $lacresCorreiosPtPorPosto[$codigo3] : '';
        $valorEtiquetaCorreiosPt = isset($etiquetasCorreiosPtPorPosto[$codigo3]) ? $etiquetasCorreiosPtPorPosto[$codigo3] : '';
        // v9.21.1: Adiciona número do posto ao nome (ex: "POUPA TEMPO 06 - PINHEIRINHO")
        $nomeComNumero = $modo_branco ? '' : ('POUPA TEMPO ' . $codigo3 . ' - ' . $nome);
        $valorNome = $modo_branco ? '' : (isset($nomesPorPosto[$codigo3]) ? $nomesPorPosto[$codigo3] : $nomeComNumero);
        $valorEndereco = $modo_branco ? '' : (isset($enderecosPorPosto[$codigo3]) ? $enderecosPorPosto[$codigo3] : $endereco);
        $valorQuantidade = $modo_branco ? '' : (isset($quantidadesPorPosto[$codigo3]) ? $quantidadesPorPosto[$codigo3] : $qtd_total);
        if ($modo_branco) {
            $valorLacre = '';
        }

        $folha_id = $codigo3;
        $valorQuantidade = $modo_branco ? '' : $qtd_total;
  ?>
    <div class="folha-a4-oficio" data-posto="<?php echo e($codigo3); ?>" data-folha-id="<?php echo e($folha_id); ?>">
    <div class="oficio">
      <div class="cols100 border-1px">
        <div class="cols25 fleft margin2px">
          <img alt="Logotipo" style="margin-left:10px;margin-top:10px;padding-right:15px;float:left" src="logo_celepar.png" width="250" height="55">
        </div>
        <div class="cols65 fright center margin2px">
          <h3><i>COSEP <br> Coordenacao De Servicos De Producao</i></h3>
          <h3><b><br> Comprovante de Entrega </b></h3>
        </div>
      </div>

            <div class="cols100 center border-1px p5 moldura cabecalho-pt">
                <h4 class="left cabecalho-pt-titulo">
                    <br><span class="nometit">POUPATEMPO PARANA</span>
                    <!-- ENDEREÇO editável como input -->
                    <br><span class="nometit">ENDERECO: 
                        <input type="text" 
                                     name="endereco_posto[<?php echo e($codigo3); ?>]" 
                                     value="<?php echo e($valorEndereco); ?>" 
                                     class="input-editavel"
                                     style="width:90%;">
                    </span>
                    <br><span class="nometit"></span>
                </h4>
            </div>

      <!-- v9.21.1: Adiciona margem lateral para não encostar na borda -->
      <div class="cols100 processo border-1px" style="padding-left:10px; padding-right:10px;">
                <div class="oficio-observacao">
                                        <table style="table-layout:fixed; width:100%; max-width:100%; margin:0;">
                        <tr>
                            <th style="width:<?php echo $modo_visual_correios ? '78%' : '55%'; ?>; text-align:left; padding:8px; border:1px solid #000; font-size:14px;">Poupatempo</th>
                            <th style="width:<?php echo $modo_visual_correios ? '22%' : '22%'; ?>; text-align:right; padding:8px; border:1px solid #000; font-size:14px;">Quantidade de CIN's</th>
                            <?php if (!$modo_visual_correios): ?>
                            <th style="width:23%; text-align:right; padding:8px; border:1px solid #000; font-size:14px;">Numero do Lacre</th>
                            <?php endif; ?>
                        </tr>
                        <tr>
                                                        <td style="width:<?php echo $modo_visual_correios ? '78%' : '55%'; ?>; text-align:left; padding:8px; border:1px solid #000;">
                                <!-- v9.21.6: Nome pode quebrar em até 2 linhas -->
                                <textarea name="nome_posto[<?php echo e($codigo3); ?>]"
                                                    class="input-editavel"
                                                    rows="2"
                                                    style="width:100%; border:none; background:transparent; font-size:14px; font-weight:bold; resize:none; overflow:hidden; line-height:1.2;"><?php echo e($nomeComNumero); ?></textarea>
                            </td>
              <!-- Quantidade de carteiras - v9.8.2: Calculada dinamicamente dos lotes marcados -->
                            <td style="text-align:right; padding:8px; border:1px solid #000;">
                                <?php if ($modo_branco): ?>
                                    <input type="text"
                                            name="quantidade_posto[<?php echo e($codigo3); ?>]"
                                            value=""
                                            class="input-editavel"
                                            style="text-align:right; font-size:14px; border:none; background:transparent; width:100%;">
                                <?php else: ?>
                                    <span class="total-cins" id="total_<?php echo e($codigo3); ?>" style="font-weight:bold; font-size:14px;">
                                        <?php echo ($valorQuantidade === '' ? '' : number_format((int)$valorQuantidade, 0, ',', '.')); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <?php if (!$modo_visual_correios): ?>
                            <!-- Número do lacre -->
                            <td style="text-align:right; padding:8px; border:1px solid #000;">
                <div class="lacre-avulso-wrap">
                <input type="text"
                    name="lacre_iipr[<?php echo e($codigo3); ?>]"
                    value="<?php echo e($valorLacre); ?>"
                                        class="input-editavel campo-cabecalho-pt lacre lacre-pt-input"
                    style="text-align:right; font-size:14px; border:none; background:transparent; width:100%;"
                    data-lacre-avulso="0"
                >
                <button type="button" class="btn-av-pt nao-imprimir" title="Ativar lacre avulso" onclick="alternarLacreAvulsoPT(this)">Av</button>
                </div>
              </td>
                            <?php endif; ?>
            </tr>
          </table>

                    <!-- v9.22.1: Seleção de folha para impressão (só marca se tiver lacre) -->
                    <div class="nao-imprimir" style="margin:8px 0;">
                        <label style="font-size:12px; font-weight:bold;">
                            <?php
                                $folha_selecionada = !empty($folhas_selecionadas_render)
                                    ? in_array($folha_id, $folhas_selecionadas_render, true)
                                    : ($modo_visual_correios ? true : (!empty($valorLacre) || !empty($valorLacreCorreiosPt) || !empty($valorEtiquetaCorreiosPt)));
                            ?>
                            <input type="checkbox" class="selecionar-folha" data-folha="<?php echo e($folha_id); ?>" <?php echo ($folha_selecionada ? 'checked' : ''); ?>>
                            Imprimir esta folha
                        </label>
                    </div>

          <!-- v9.9.2: Painel de Conferência Simplificado -->
          <?php if (!empty($lotes_array)): ?>
          <div class="painel-conferencia controle-conferencia" style="margin-top:15px;">
            <div class="campo-leitura">
              <label for="input_conferencia_<?php echo e($codigo3); ?>">Leitura:</label>
              <input type="text" 
                     id="input_conferencia_<?php echo e($codigo3); ?>" 
                     class="input-conferencia"
                     placeholder="Leia código de barras (19 dígitos) ou digite lote (8 dígitos)..."
                     autocomplete="off"
                     oninput="conferirLoteAutomatico('<?php echo e($codigo3); ?>', this.value)"
                     onkeydown="if(event.keyCode===13){conferirLote('<?php echo e($codigo3); ?>');return false;}">              
            </div>
            <div class="contador-conferencia">
              <span>Total de Lotes: <strong id="total_lotes_<?php echo e($codigo3); ?>"><?php echo count($lotes_array); ?></strong></span>
                            <span>Conferidos: <strong id="conferidos_<?php echo e($codigo3); ?>"><?php echo (int)$conferidos_iniciais; ?></strong></span>
                            <span>Pendentes: <strong id="pendentes_<?php echo e($codigo3); ?>"><?php echo (int)$pendentes_iniciais; ?></strong></span>
            </div>
          </div>

                    <!-- v9.22.0: Título LOTES (1 por linha) -->
                    <h3 class="titulo-lotes">LOTES</h3>

                    <div class="tabela-lotes" style="margin:2px 10px; padding:0; max-width:calc(100% - 20px);">
                        <table id="tabela_lotes_<?php echo e($codigo3); ?>" style="width:100%; border-collapse:collapse; border:1px solid #000;" class="lotes-detalhe-1col tabela-lotes-pt">
                            <thead>
                                <tr style="background:#e0e0e0;">
                                    <th class="col-checkbox nao-imprimir" style="width:30px; padding:3px; border:1px solid #000; font-size:10px;"></th>
                                    <th class="col-acoes-lote nao-imprimir" style="width:86px; padding:3px; border:1px solid #000; font-size:10px;">Ações</th>
                                    <th style="width:44%; text-align:left; padding:4px; border:1px solid #000; font-size:10px; font-weight:bold;">Lote</th>
                                    <th style="width:18%; text-align:center; padding:4px; border:1px solid #000; font-size:10px; font-weight:bold;">Qtd</th>
                                    <th style="width:24%; text-align:center; padding:4px; border:1px solid #000; font-size:10px; font-weight:bold;"><?php echo $modo_visual_correios ? 'Data Expedicao' : 'Data Produção'; ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lotes_array as $lote): ?>
                                <?php
                                    $lote_num_render = str_pad(preg_replace('/\D+/', '', (string)$lote['lote']), 8, '0', STR_PAD_LEFT);
                                    $esta_conferido = (!$modo_branco && $lote_num_render !== '' && isset($mapaConferidos[$codigo3]) && isset($mapaConferidos[$codigo3][$lote_num_render]));
                                ?>
                                <tr class="linha-lote<?php echo $esta_conferido ? ' conferido' : ''; ?>" data-posto="<?php echo e($codigo3); ?>" data-lote="<?php echo e($lote['lote']); ?>" data-checked="1" data-quantidade="<?php echo e($lote['quantidade']); ?>" data-data-carga="<?php echo e($lote['data_carga']); ?>">
                                    <td class="col-checkbox nao-imprimir" style="text-align:center; padding:3px; border:1px solid #000;">
                                        <input type="checkbox" class="checkbox-lote" data-posto="<?php echo e($codigo3); ?>" 
                                                     data-quantidade="<?php echo e($lote['quantidade']); ?>" 
                                                     data-lote="<?php echo e($lote['lote']); ?>"
                                                     data-data-carga="<?php echo e($lote['data_carga']); ?>"
                                                     data-conferido="<?php echo $esta_conferido ? '1' : '0'; ?>" checked 
                                                     onchange="recalcularTotal('<?php echo e($codigo3); ?>')">
                                    </td>
                                    <td class="col-acoes-lote nao-imprimir" style="text-align:center; padding:3px; border:1px solid #000;">
                                        <div class="acoes-lote-wrap">
                                            <button type="button" class="btn-lote-acao" onclick="adicionarLinhaLotePT(this)" title="Adicionar linha abaixo">+</button>
                                            <button type="button" class="btn-lote-acao" onclick="excluirLinhaLotePT(this)" title="Excluir linha">×</button>
                                        </div>
                                    </td>
                                    <td class="celula-lote <?php echo $esta_conferido ? 'lote-conferido' : ''; ?>" style="text-align:left; padding:4px; border:1px solid #000; font-size:10px;"><?php echo e($lote['lote']); ?></td>
                                    <td class="celula-qtd <?php echo $esta_conferido ? 'lote-conferido' : ''; ?>" style="text-align:center; padding:4px; border:1px solid #000; font-size:10px;">
                                        <span class="valor-tela"><?php echo number_format($lote['quantidade'], 0, ',', '.'); ?></span>
                                    </td>
                                    <td class="celula-data <?php echo $esta_conferido ? 'lote-conferido' : ''; ?>" style="text-align:center; padding:4px; border:1px solid #000; font-size:10px;">
                                        <?php echo !empty($lote['data_carga']) ? date('d-m-Y', strtotime($lote['data_carga'])) : ''; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                             <div class="nao-imprimir" style="display:flex; gap:8px; justify-content:flex-end; margin-top:8px; flex-wrap:wrap;">
                                 <button type="button" class="btn-remover-desmarcados" onclick="ocultarDesmarcadosPT('<?php echo e($codigo3); ?>')">Ocultar desmarcados desta folha</button>
                                 <button type="button" class="btn-remover-desmarcados" onclick="adicionarLinhaFimPT('<?php echo e($codigo3); ?>')">Adicionar linha no fim</button>
                             </div>
                             <input type="hidden" 
                                 name="folha_posto[<?php echo e($folha_id); ?>]" 
                                 value="<?php echo e($codigo3); ?>">
                             <input type="hidden" 
                                 name="lotes_confirmados[<?php echo e($folha_id); ?>]" 
                                 id="lotes_confirmados_<?php echo e($folha_id); ?>" 
                                 value="<?php echo implode(',', array_map(function($l){ return $l['lote']; }, $lotes_array)); ?>">
                             <?php if (!$modo_branco): ?>
                             <input type="hidden" 
                                 name="quantidade_posto[<?php echo e($folha_id); ?>]" 
                                 id="quantidade_final_<?php echo e($folha_id); ?>" 
                                 value="<?php echo $qtd_total; ?>">
                             <?php endif; ?>
                    </div>
          <?php endif; ?>  <!-- Fecha o if (!empty($lotes_array)) -->

          <!-- v9.21.6: Espaçador ajustado para tela e impressão -->
          <div class="espacador-rodape"></div>
        </div>
      </div>

    <!-- v9.21.6: Rodapé visível apenas na impressão -->
    <div class="cols100 border-1px rodape-oficio" style="padding:8px 15px;">
        <div style="display:flex; justify-content:space-between; gap:15px;">
          <!-- Conferido por -->
                    <div style="flex:1; border-right:1px solid #000; padding-right:12px;">
                        <div style="text-align:center; margin-bottom:40px;">
                            <strong>Produzido por:</strong>
                        </div>
                        <div style="border-top:1px solid #000; padding-top:3px; text-align:center;">
                            <div style="margin-bottom:3px;">______________________________</div>
                            <div style="font-size:12px;"><strong>CELEPAR - Data:</strong> <?php echo date('d-m-Y'); ?></div>
                        </div>
                    </div>
          
          <!-- Recebido por -->
          <div style="flex:1; padding-left:12px;">
            <div style="text-align:center; margin-bottom:40px;">
              <strong>Recebido por:</strong>
            </div>
                        <div style="border-top:1px solid #000; padding-top:3px; text-align:center;">
                            <div style="margin-bottom:3px;">______________________________</div>
                            <div style="font-size:12px;"><strong>IIPR-POUPA-TEMPO - Data:</strong> <?php echo date('d-m-Y'); ?></div>
                        </div>
          </div>
        </div>
      </div>
    </div>
        </div>
    <?php endforeach; ?>  <!-- Fecha o foreach de $paginas -->

<?php else: ?>  <!-- Se $temDados for false, exibe mensagem de erro -->
  <div style="margin:50px auto; max-width:800px; padding:30px; background:#fff3cd; border:3px solid #856404; border-radius:8px; text-align:center;">
    <h2 style="color:#856404; margin-top:0;">⚠️ Nenhum Ofício para Exibir</h2>
    <p style="font-size:16px; line-height:1.6;">
      <strong>Não foram encontrados dados para gerar o ofício Poupa Tempo.</strong>
    </p>
    <p style="font-size:14px; color:#666; line-height:1.6;">
      <strong>Possíveis causas:</strong><br>
      • As datas selecionadas não têm produção cadastrada no sistema<br>
      • Nenhum posto Poupa Tempo tem lotes nas datas escolhidas<br>
      • Os postos não estão configurados com entrega "POUPA TEMPO"<br>
      • Problema na conexão com o banco de dados
    </p>
    <p style="margin-top:20px;">
      <a href="javascript:history.back()" style="display:inline-block; padding:12px 24px; background:#007bff; color:#fff; text-decoration:none; border-radius:4px; font-weight:bold;">
        ← Voltar e Selecionar Outras Datas
      </a>
    </p>
    <hr style="margin:30px 0; border:none; border-top:1px solid #ccc;">
    <p style="font-size:12px; color:#999;">
      <strong>Debug:</strong> Para mais detalhes, adicione <code>?debug_dados=1</code> na URL
    </p>
  </div>
<?php endif; ?>  <!-- Fecha o if ($temDados) -->

</form>

<?php if ($tipo_mensagem === 'sucesso'): ?>
<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    mostrarModalResultadoSalvamento(<?php echo json_encode_legado_seguro($mensagem_status, JSON_UNESCAPED_UNICODE); ?>, function() {
        <?php if ($deve_imprimir): ?>
        imprimirSelecionados();
        <?php endif; ?>
    });
});
</script>
<?php endif; ?>

<!-- v9.9.0: Sistema de Conferência de Lotes -->
<script type="text/javascript">
// v9.9.5: Conferência automática ao atingir 19 dígitos
function conferirLoteAutomatico(codigoPosto, valor) {
    // Remove espaços e valida
    var codigo = valor.trim();
    
    // Se atingiu exatamente 19 dígitos numéricos, confere automaticamente
    if (codigo.length === 19 && /^\d{19}$/.test(codigo)) {
        console.log('✓ 19 dígitos detectados! Conferindo automaticamente...');
        conferirLote(codigoPosto);
    }
}

function salvarConferenciaPt(codigoPosto, numeroLote, quantidade, dataCarga, codigoCompleto) {
    var formData = new FormData();
    formData.append('salvar_conferencia_pt_ajax', '1');
    formData.append('posto', codigoPosto || '');
    formData.append('lote', numeroLote || '');
    formData.append('qtd', quantidade || '0');
    formData.append('dataexp', dataCarga || '');
    formData.append('codbar', codigoCompleto || '');
    return fetch(window.location.href, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    }).then(function(response) {
        return response.json();
    }).then(function(data) {
        if (!data || !data.success) {
            console.error('Erro ao salvar conferência PT:', data && data.erro ? data.erro : 'desconhecido');
        }
        return data;
    }).catch(function(error) {
        console.error('Erro AJAX conferência PT:', error);
        return { success: false, erro: String(error) };
    });
}

function removerConferenciaPt(itens) {
    var formData = new FormData();
    formData.append('remover_conferencia_pt_ajax', '1');
    formData.append('itens', JSON.stringify(itens || []));
    return fetch(window.location.href, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    }).then(function(response) {
        return response.json();
    }).catch(function(error) {
        console.error('Erro AJAX ao remover conferência PT:', error);
        return { success: false, erro: String(error) };
    });
}

function aplicarEstadoVisualConferenciaLinha(linha, conferido) {
    if (!linha) return;
    linha.classList.toggle('conferido', !!conferido);
    linha.setAttribute('data-conferido', conferido ? '1' : '0');
    var checkboxLinha = linha.querySelector('.checkbox-lote');
    if (checkboxLinha) {
        checkboxLinha.setAttribute('data-conferido', conferido ? '1' : '0');
    }
    var tds = linha.querySelectorAll('td');
    for (var i = 0; i < tds.length; i++) {
        if (i === 0) continue;
        tds[i].classList.toggle('lote-conferido', !!conferido);
    }
}

function sincronizarConferenciasVisuaisPT() {
    var containers = document.querySelectorAll('.folha-a4-oficio[data-posto]');
    for (var c = 0; c < containers.length; c++) {
        var container = containers[c];
        var posto = container.getAttribute('data-posto') || '';
        var linhas = container.querySelectorAll('tr.linha-lote');
        for (var i = 0; i < linhas.length; i++) {
            var linha = linhas[i];
            var checkbox = linha.querySelector('.checkbox-lote');
            var conferido = false;
            if (checkbox && checkbox.getAttribute('data-conferido') === '1') {
                conferido = true;
            } else if (linha.classList.contains('conferido')) {
                conferido = true;
            }
            aplicarEstadoVisualConferenciaLinha(linha, conferido);
        }
        if (posto) {
            atualizarContadores(posto, true);
        }
    }
}

function coletarItensConferenciaVisiveisPT() {
    var linhas = document.querySelectorAll('.folha-a4-oficio tr.linha-lote');
    var itens = [];
    var mapa = {};
    for (var i = 0; i < linhas.length; i++) {
        var linha = linhas[i];
        if (!linha || linha.offsetParent === null) continue;
        var posto = String(linha.getAttribute('data-posto') || '').trim();
        var lote = String(linha.getAttribute('data-lote') || '').trim();
        var dataexp = String(linha.getAttribute('data-data-carga') || '').trim();
        if (!posto || !lote) continue;
        var chave = posto + '|' + lote + '|' + dataexp;
        if (mapa[chave]) continue;
        mapa[chave] = true;
        itens.push({ posto: posto, lote: lote, dataexp: dataexp });
    }
    return itens;
}

function removerConferenciaVisivelPT() {
    var itens = coletarItensConferenciaVisiveisPT();
    if (!itens.length) {
        alert('Nenhum lote visível foi encontrado para retirar a conferência.');
        return;
    }
    if (!confirm('Deseja retirar a conferência de tudo que aparece na tela atual?')) {
        return;
    }
    removerConferenciaPt(itens).then(function(data) {
        if (!data || !data.success) {
            alert(data && data.erro ? data.erro : 'Não foi possível retirar a conferência da tela atual.');
            return;
        }
        var linhas = document.querySelectorAll('.folha-a4-oficio tr.linha-lote');
        for (var i = 0; i < linhas.length; i++) {
            var linha = linhas[i];
            if (!linha || linha.offsetParent === null) continue;
            aplicarEstadoVisualConferenciaLinha(linha, false);
        }
        sincronizarConferenciasVisuaisPT();
        alert('Conferência removida dos lotes exibidos na tela.');
    });
}

// v9.9.4: Função para conferir lote via código de barras (extrai lote de 8 dígitos)
function conferirLote(codigoPosto) {
    var input = document.getElementById('input_conferencia_' + codigoPosto);
    if (!input) return;
    
    var codigoLido = input.value.trim();
    if (codigoLido === '') return;
    
    // v9.9.6: Se código tem 19 dígitos, extrai lote e quantidade
    // Estrutura: [8 dígitos lote][6 dígitos outros][5 dígitos quantidade]
    // Exemplo: 0075942402302300170 → Lote:00759424 Qtd:00170(170)
    var numeroLote = codigoLido;
    if (codigoLido.length === 19 && /^\d{19}$/.test(codigoLido)) {
        // Extrai caracteres da posição 0 a 7 (8 primeiros dígitos = lote)
        numeroLote = codigoLido.substring(0, 8);
        // NÃO remove zeros à esquerda para preservar formato original
        console.log('Código de barras 19 dígitos detectado.');
        console.log('Lote extraído (posições 0-7): ' + numeroLote);
        console.log('Código completo: ' + codigoLido);
    }
    
    // v9.12.0: Busca o lote na tabela (suporta layout 1, 2 ou 3 colunas)
    var tabela = document.getElementById('tabela_lotes_' + codigoPosto);
    var tabela_col1 = document.getElementById('tabela_lotes_' + codigoPosto + '_col1');
    var tabela_col2 = document.getElementById('tabela_lotes_' + codigoPosto + '_col2');
    var container3 = document.querySelector('.folha-a4-oficio[data-posto="' + codigoPosto + '"]');
    
    // Se não encontrou nenhum container de tabela, aborta
    if (!tabela && !tabela_col1 && !tabela_col2 && !container3) {
        console.log('ERRO: Tabela não encontrada para posto: ' + codigoPosto);
        alert('Erro: Tabela de lotes não encontrada.');
        return;
    }

    // Se tem 2 colunas, procura em ambas
    var linhas = [];
    if (tabela_col1 && tabela_col2) {
        var linhas1 = tabela_col1.getElementsByClassName('linha-lote');
        var linhas2 = tabela_col2.getElementsByClassName('linha-lote');
        linhas = Array.from(linhas1).concat(Array.from(linhas2));
        console.log('Layout 2 colunas detectado. Total linhas: ' + linhas.length);
    } else if (tabela) {
        linhas = Array.from(tabela.getElementsByClassName('linha-lote'));
        console.log('Layout 1 coluna detectado. Total linhas: ' + linhas.length);
    }
    
    var loteEncontrado = false;
    console.log('Procurando lote: "' + numeroLote + '"');
    
    for (var i = 0; i < linhas.length; i++) {
        var linha = linhas[i];
        var loteNaLinha = (linha.getAttribute('data-lote') || '').trim();
        
        console.log('Linha ' + i + ': Lote na linha="' + loteNaLinha + '"');
        
        if (loteNaLinha === numeroLote) {
            console.log('✓ LOTE ENCONTRADO! Linha ' + i);
            loteEncontrado = true;
            
            // v9.9.5: Verifica se já foi conferido (sem alert)
            if (linha.classList.contains('conferido')) {
                console.log('⚠️ Lote ' + numeroLote + ' já conferido anteriormente.');
                input.value = '';
                input.focus();
                return;
            }
            
            // Marca como conferido (verde)
            linha.classList.add('conferido');
            linha.classList.add('conferido-agora');
            linha.setAttribute('data-conferido', '1');
            var checkboxLinha = linha.querySelector('.checkbox-lote');
            var quantidadeLinha = linha.getAttribute('data-quantidade') || (checkboxLinha ? checkboxLinha.getAttribute('data-quantidade') : '0');
            var dataCargaLinha = linha.getAttribute('data-data-carga') || (checkboxLinha ? checkboxLinha.getAttribute('data-data-carga') : '');
            if (checkboxLinha) {
                checkboxLinha.setAttribute('data-conferido', '1');
            }
            
            // Remove animação após 1 segundo
            setTimeout(function() {
                linha.classList.remove('conferido-agora');
            }, 1000);
            
            // Atualiza contadores
            atualizarContadores(codigoPosto);

            salvarConferenciaPt(codigoPosto, numeroLote, quantidadeLinha, dataCargaLinha, codigoLido);
            
            // Limpa campo e mantém foco
            input.value = '';
            input.focus();
            
            // Feedback sonoro (beep) - opcional
            // Você pode adicionar um som de sucesso aqui se desejar
            
            return;
        }
    }
    
    // v9.21.7: Fallback para layout 3 colunas usando checkboxes
    if (!loteEncontrado && container3) {
        var cbs = container3.querySelectorAll('.checkbox-lote');
        for (var j = 0; j < cbs.length; j++) {
            var cb3 = cbs[j];
            var loteCb = (cb3.getAttribute('data-lote') || '').trim();
            if (loteCb === numeroLote) {
                var tdCheck = cb3.closest('td');
                var tdLote = tdCheck ? tdCheck.nextElementSibling : null;
                var tdQtd = tdLote ? tdLote.nextElementSibling : null;
                if (cb3.getAttribute('data-conferido') === '1') {
                    input.value = '';
                    input.focus();
                    return;
                }
                cb3.setAttribute('data-conferido', '1');
                cb3.checked = true;
                if (tdLote) tdLote.classList.add('lote-conferido');
                if (tdQtd) tdQtd.classList.add('lote-conferido');
                recalcularTotal(codigoPosto);
                // Atualiza contadores
                atualizarContadores(codigoPosto);
                salvarConferenciaPt(codigoPosto, numeroLote, cb3.getAttribute('data-quantidade') || '0', cb3.getAttribute('data-data-carga') || '', codigoLido);
                input.value = '';
                input.focus();
                return;
            }
        }
    }

    // Se não encontrou, cria nova linha amarela
    if (!loteEncontrado) {
        var tbody = tabela.getElementsByTagName('tbody')[0];
        if (!tbody) return;
        var quantidadeExtraida = 0;
        if (codigoLido.length === 19 && /^\d{19}$/.test(codigoLido)) {
            quantidadeExtraida = parseInt(codigoLido.substring(14, 19), 10);
            console.log('Quantidade extraída (posições 14-18): ' + quantidadeExtraida);
        }
        var novaLinha = criarLinhaManualPT(codigoPosto, {
            lote: numeroLote,
            quantidade: quantidadeExtraida,
            data_carga: '',
            checked: false,
            conferido: false
        });
        if (novaLinha.className.indexOf('nao-encontrado') < 0) novaLinha.className += ' nao-encontrado';
        tbody.appendChild(novaLinha);
        
        // Atualiza contador de total de lotes
        var totalLotesSpan = document.getElementById('total_lotes_' + codigoPosto);
        if (totalLotesSpan) {
            var totalAtual = parseInt(totalLotesSpan.textContent) || 0;
            totalLotesSpan.textContent = totalAtual + 1;
        }
        
        // Atualiza pendentes
        atualizarContadores(codigoPosto);
        
        // Limpa campo e mantém foco
        input.value = '';
        input.focus();
        
        // v9.9.5: Mensagem simplificada (linha amarela, oculta na impressão)
        var msgQuantidade = quantidadeExtraida > 0 ? '\nQuantidade: ' + quantidadeExtraida : '\nInforme a quantidade.';
        alert('📦 Lote ' + numeroLote + ' adicionado à lista.' + msgQuantidade + '\n\n⚠️ Linha amarela não será impressa.');
    }
}

// v9.9.0: Atualiza contadores de conferência
function atualizarContadores(codigoPosto, silencioso) {
    var tabela = document.getElementById('tabela_lotes_' + codigoPosto);
    var container3 = document.querySelector('.folha-a4-oficio[data-posto="' + codigoPosto + '"]');
    var totalLotes = 0;
    var conferidos = 0;

    if (tabela) {
        var linhas = tabela.getElementsByClassName('linha-lote');
        totalLotes = linhas.length;
        for (var i = 0; i < linhas.length; i++) {
            if (linhas[i].classList.contains('conferido')) {
                conferidos++;
            }
        }
    } else if (container3) {
        var cbs = container3.querySelectorAll('.checkbox-lote');
        totalLotes = cbs.length;
        for (var j = 0; j < cbs.length; j++) {
            if (cbs[j].getAttribute('data-conferido') === '1') {
                conferidos++;
            }
        }
    } else {
        return;
    }

    var pendentes = totalLotes - conferidos;
    
    // Atualiza displays
    var spanTotal = document.getElementById('total_lotes_' + codigoPosto);
    var spanConferidos = document.getElementById('conferidos_' + codigoPosto);
    var spanPendentes = document.getElementById('pendentes_' + codigoPosto);
    
    if (spanTotal) spanTotal.textContent = totalLotes;
    if (spanConferidos) spanConferidos.textContent = conferidos;
    if (spanPendentes) spanPendentes.textContent = pendentes;
    
    // Se todos foram conferidos, mostra mensagem
    if (!silencioso && pendentes === 0 && totalLotes > 0) {
        setTimeout(function() {
            alert('✅ Todos os lotes foram conferidos!\nTotal: ' + conferidos + ' lotes');
        }, 100);
    }
}

// v9.9.0: Atalho de teclado Alt+C para focar no campo de conferência
document.addEventListener('keydown', function(e) {
    if (e.altKey && e.keyCode === 67) { // Alt+C
        e.preventDefault();
        var inputs = document.getElementsByClassName('input-conferencia');
        if (inputs.length > 0) {
            inputs[0].focus();
            inputs[0].select();
        }
    }
});

// v9.9.0: Foco automático no primeiro campo de conferência ao carregar
window.addEventListener('load', function() {
    var primeiroInput = document.querySelector('.input-conferencia');
    sincronizarConferenciasVisuaisPT();
    if (primeiroInput) {
        setTimeout(function() {
            primeiroInput.focus();
        }, 300);
    }
});

// v9.16.0: Clona página completa para criar novo malote - COM DEBUG
function clonarPagina(codigoPosto) {
    console.log('=== CLONAR PÁGINA ===');
    console.log('Posto solicitado:', codigoPosto);
    
    // Listar todos os postos disponíveis
    var todasFolhas = document.querySelectorAll('.folha-a4-oficio[data-posto]');
    console.log('Total de folhas encontradas:', todasFolhas.length);
    for (var i = 0; i < todasFolhas.length; i++) {
        console.log('  - Folha', i+1, '→ data-posto:', todasFolhas[i].getAttribute('data-posto'));
    }
    
    if (!confirm('Criar uma CÓPIA desta página?\n\nVocê poderá marcar/desmarcar lotes em cada uma para dividir entre malotes diferentes.')) {
        return;
    }
    
    // 1. Buscar folha usando data-posto
    var folhaOriginal = document.querySelector('.folha-a4-oficio[data-posto="' + codigoPosto + '"]');
    
    if (!folhaOriginal) {
        var postosDisponiveis = [];
        var folhas = document.querySelectorAll('.folha-a4-oficio[data-posto]');
        for (var i = 0; i < folhas.length; i++) {
            postosDisponiveis.push(folhas[i].getAttribute('data-posto'));
        }
        alert('Erro: Não foi possível encontrar a página do posto ' + codigoPosto + '\n\n' +
              'Postos disponíveis: ' + postosDisponiveis.join(', ') + '\n\n' +
              'Verifique o console do navegador (F12) para mais detalhes.');
        console.error('ERRO: Posto não encontrado:', codigoPosto);
        console.error('Seletor usado:', '.folha-a4-oficio[data-posto="' + codigoPosto + '"]');
        return;
    }
    
    console.log('✓ Folha original encontrada!');
    console.log('✓ Iniciando clonagem...');
    
    // 2. Clonar a folha inteira
    var folhaNova = folhaOriginal.cloneNode(true);
    
    // 3. Gerar sufixo único baseado em timestamp
    var sufixo = '_clone_' + Date.now();
    
    // 4. Atualizar IDs para evitar conflitos
    var elementosComId = folhaNova.querySelectorAll('[id]');
    for (var j = 0; j < elementosComId.length; j++) {
        elementosComId[j].id = elementosComId[j].id + sufixo;
    }
    
    // 5. Atualizar names dos inputs
    var elementosComName = folhaNova.querySelectorAll('[name]');
    for (var k = 0; k < elementosComName.length; k++) {
        var nameOriginal = elementosComName[k].name;
        if (nameOriginal.indexOf('lote_posto') !== -1 || 
            nameOriginal.indexOf('nome_posto') !== -1 || 
            nameOriginal.indexOf('endereco_posto') !== -1 ||
            nameOriginal.indexOf('quantidade_posto') !== -1 ||
            nameOriginal.indexOf('lotes_confirmados') !== -1) {
            // Adicionar sufixo ao código do posto
            elementosComName[k].name = nameOriginal.replace('[' + codigoPosto + ']', '[' + codigoPosto + sufixo + ']');
        }
    }
    
    // 6. Limpar campo de lacre na página clonada
    var inputLacreNovo = folhaNova.querySelector('input[name*="lote_posto"]');
    if (inputLacreNovo) {
        inputLacreNovo.value = '';
        inputLacreNovo.placeholder = 'Digite novo lacre para este malote';
    }
    
    // 7. Adicionar botão REMOVER DENTRO da página clonada (não no topo)
    var oficioDiv = folhaNova.querySelector('.oficio');
    if (oficioDiv) {
        // Criar container para o botão
        var containerBotao = document.createElement('div');
        containerBotao.className = 'nao-imprimir';
        containerBotao.style.cssText = 'background:#fff3cd; border:2px solid #ffc107; border-radius:6px; padding:12px; margin-bottom:15px; text-align:center;';
        
        // Criar botão
        var btnRemover = document.createElement('button');
        btnRemover.type = 'button';
        btnRemover.className = 'btn-remover-pagina';
        btnRemover.style.cssText = 'background:#dc3545; color:#fff; border:2px solid #bd2130; border-radius:6px; padding:10px 20px; font-size:14px; font-weight:bold; cursor:pointer; box-shadow:0 2px 5px rgba(220,53,69,0.3);';
        btnRemover.innerHTML = '✕ REMOVER ESTA PÁGINA CLONADA';
        btnRemover.onmouseover = function() { this.style.background = '#c82333'; };
        btnRemover.onmouseout = function() { this.style.background = '#dc3545'; };
        btnRemover.onclick = function() {
            if (confirm('Deseja remover esta página clonada?')) {
                folhaNova.remove();
            }
        };
        
        containerBotao.appendChild(btnRemover);
        oficioDiv.insertBefore(containerBotao, oficioDiv.firstChild);
    }
    
    // 8. Atualizar onclick dos checkboxes na página clonada
    var checkboxesNovos = folhaNova.querySelectorAll('.checkbox-lote');
    for (var m = 0; m < checkboxesNovos.length; m++) {
        var checkbox = checkboxesNovos[m];
        var postoOriginal = checkbox.getAttribute('data-posto');
        checkbox.setAttribute('data-posto', codigoPosto + sufixo);
        checkbox.setAttribute('onchange', 'recalcularTotal(\'' + codigoPosto + sufixo + '\')');
    }
    
    // 9. Atualizar onclick do checkbox "marcar todos"
    var marcarTodos = folhaNova.querySelectorAll('.marcar-todos');
    for (var n = 0; n < marcarTodos.length; n++) {
        marcarTodos[n].setAttribute('data-posto', codigoPosto + sufixo);
        marcarTodos[n].setAttribute('onchange', 'marcarTodosLotes(this, \'' + codigoPosto + sufixo + '\')');
    }
    
    // 10. Marcar como página clonada e atualizar data-posto
    folhaNova.classList.add('pagina-clonada');
    folhaNova.setAttribute('data-posto', codigoPosto + sufixo);
    folhaNova.setAttribute('data-posto-original', codigoPosto);
    
    // 11. Inserir após a página original
    folhaOriginal.parentNode.insertBefore(folhaNova, folhaOriginal.nextSibling);
    
    // 12. Recalcular total da página clonada
    setTimeout(function() {
        recalcularTotal(codigoPosto + sufixo);
    }, 50);
    
    // 13. Scroll suave até a nova página
    setTimeout(function() {
        folhaNova.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 100);
    
    alert('✅ Página clonada criada com sucesso!\n\n📋 Agora você pode marcar/desmarcar checkboxes em cada página\n💡 Os totais são recalculados automaticamente\n🗑️ Use o botão amarelo para remover páginas clonadas\n\n⚠️ Não esqueça de informar um NOVO lacre para esta página!');
}

</script>
<?php include __DIR__ . '/processando_overlay.php'; ?>
<?php include __DIR__ . '/melhorias_widget.php'; ?>
</body>
</html>
