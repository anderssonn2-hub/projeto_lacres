<?php
/* modelo_oficio_poupa_tempo.php – Poupatempo (uma página por posto)
   - NÃO depende mais de poupatempo_payload
   - Usa pt_datas (enviado pelo formulário escondido em lacres_novo.php)
   - Faz SELECT direto em ciPostosCsv + ciRegionais para montar:
       código do posto, nome, quantidade total, endereço
   - Gera uma página de ofício por posto poupatempo
   - ATUALIZADO: Salva nome_posto, endereco e lacre_iipr no banco de dados
   - Compatível com PHP 5.3.3
   
   v9.8.6: Melhorias de Impressão (26/01/2026)
   - [CORRIGIDO] Coluna vazia de checkboxes removida na impressão
   - [CORRIGIDO] Título "📦 Lotes para Despacho" oculto na impressão
   - [CORRIGIDO] Texto "(lotes marcados):" oculto na impressão
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

function e($s){
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
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

/* ============================================================
   1.1) Processar salvamento do ofício (se acao=salvar_oficio_completo)
   ============================================================ */
$mensagem_status = '';
$tipo_mensagem = '';
$deve_imprimir = false;

// Variáveis para manter os dados do POST após salvamento
$dados_salvos = array();

if (isset($_POST['acao']) && $_POST['acao'] === 'salvar_oficio_completo') {
    try {
        if (!$pdo_controle) {
            throw new Exception('Conexao com o banco de dados nao disponivel.');
        }

        $id_despacho_post = isset($_POST['id_despacho']) ? (int)$_POST['id_despacho'] : 0;
        $datasStr_post = isset($_POST['pt_datas']) ? trim($_POST['pt_datas']) : '';
        
        // Arrays com os dados dos postos
        $lacres = isset($_POST['lacre_iipr']) && is_array($_POST['lacre_iipr']) ? $_POST['lacre_iipr'] : array();
        $nomes = isset($_POST['nome_posto']) && is_array($_POST['nome_posto']) ? $_POST['nome_posto'] : array();
        $enderecos = isset($_POST['endereco_posto']) && is_array($_POST['endereco_posto']) ? $_POST['endereco_posto'] : array();
        $quantidades = isset($_POST['quantidade_posto']) && is_array($_POST['quantidade_posto']) ? $_POST['quantidade_posto'] : array();

        if (empty($lacres) && empty($nomes)) {
            throw new Exception('Nenhum dado de posto foi informado.');
        }

        // Armazena os dados do POST para repopular os campos após salvar
        foreach ($lacres as $posto => $val) {
            if (!isset($dados_salvos[$posto])) {
                $dados_salvos[$posto] = array();
            }
            $dados_salvos[$posto]['lacre'] = trim($val);
        }
        foreach ($nomes as $posto => $val) {
            if (!isset($dados_salvos[$posto])) {
                $dados_salvos[$posto] = array();
            }
            $dados_salvos[$posto]['nome'] = trim($val);
        }
        foreach ($enderecos as $posto => $val) {
            if (!isset($dados_salvos[$posto])) {
                $dados_salvos[$posto] = array();
            }
            $dados_salvos[$posto]['endereco'] = trim($val);
        }
        foreach ($quantidades as $posto => $val) {
            if (!isset($dados_salvos[$posto])) {
                $dados_salvos[$posto] = array();
            }
            $dados_salvos[$posto]['quantidade'] = (int)$val;
        }
        
        // v8.14.4: Capturar lotes do POST (vindos dos hidden inputs do form)
        $lotes_post = isset($_POST['lote_posto']) && is_array($_POST['lote_posto']) ? $_POST['lote_posto'] : array();
        foreach ($lotes_post as $posto => $val) {
            if (!isset($dados_salvos[$posto])) {
                $dados_salvos[$posto] = array();
            }
            $dados_salvos[$posto]['lote'] = trim($val);
        }

        // v8.14.3: Verificar modo do ofício (sobrescrever/novo)
        // v8.15.6: CORRIGIDO - modo "novo" SEMPRE cria novo ofício com hash único
        $modoOficio = isset($_POST['modo_oficio']) ? trim($_POST['modo_oficio']) : '';
        
        // Se não tiver id_despacho, precisa criar o despacho primeiro
        if ($id_despacho_post <= 0 && !empty($datasStr_post)) {
            $grupo = 'POUPA TEMPO';
            $usuario = isset($_SESSION['usuario']) ? $_SESSION['usuario'] : 'conferencia';
            
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

        // v8.14.9: Adicionar campo usuario
        $sqlUpd = "
            UPDATE ciDespachoItens
               SET lacre_iipr = :lacre,
                   nome_posto = :nome,
                   endereco = :endereco,
                   lote = :lote,
                   quantidade = :quantidade,
                   usuario = :usuario
             WHERE id_despacho = :id_despacho
               AND posto = :posto
        ";
        $stmUpd = $pdo_controle->prepare($sqlUpd);

        $sqlIns = "
            INSERT INTO ciDespachoItens (id_despacho, posto, lacre_iipr, nome_posto, endereco, lote, quantidade, usuario, incluir)
            VALUES (:id_despacho, :posto, :lacre, :nome, :endereco, :lote, :quantidade, :usuario, 1)
        ";
        $stmIns = $pdo_controle->prepare($sqlIns);

        $totalInseridos = 0;
        $totalAtualizados = 0;

        // v8.14.9: Preparar busca de usuario por posto
        $stmUsuario = $pdo_controle->prepare("SELECT MAX(usuario) FROM ciPostosCsv WHERE posto = ? LIMIT 1");

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

            // Verifica se já existe registro para este posto
            $stmSel->execute(array(
                ':id_despacho' => $id_despacho_post,
                ':posto' => $posto
            ));
            $existe = (int)$stmSel->fetchColumn();

            if ($existe > 0) {
                // Atualiza registro existente
                $stmUpd->execute(array(
                    ':lacre' => $valorLacre,
                    ':nome' => $valorNome,
                    ':endereco' => $valorEndereco,
                    ':lote' => $valorLote,
                    ':quantidade' => $valorQuantidade,
                    ':usuario' => $valorUsuario,
                    ':id_despacho' => $id_despacho_post,
                    ':posto' => $posto
                ));
                $totalAtualizados++;
            } else {
                // Insere novo registro
                $stmIns->execute(array(
                    ':id_despacho' => $id_despacho_post,
                    ':posto' => $posto,
                    ':lacre' => $valorLacre,
                    ':nome' => $valorNome,
                    ':endereco' => $valorEndereco,
                    ':lote' => $valorLote,
                    ':quantidade' => $valorQuantidade,
                    ':usuario' => $valorUsuario
                ));
                $totalInseridos++;
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
            $datasNorm[] = $d;
        }
    }
}

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

if ($pdo_controle && !empty($datasNorm)) {

    $in = "'" . implode("','", array_map('strval', $datasNorm)) . "'";

    // v9.8.2: Busca lotes individuais (não agrupa quantidade)
    $sql = "
        SELECT 
            LPAD(c.posto,3,'0') AS codigo,
            COALESCE(r.nome, CONCAT('POUPA TEMPO - ', LPAD(c.posto,3,'0'))) AS nome,
            c.lote AS lote,
            COALESCE(c.quantidade,0) AS quantidade,
            r.endereco AS endereco,
            c.usuario AS usuario
        FROM ciPostosCsv c
        INNER JOIN ciRegionais r 
                ON LPAD(r.posto,3,'0') = LPAD(c.posto,3,'0')
        WHERE DATE(c.dataCarga) IN ($in)
          AND REPLACE(LOWER(r.entrega),' ','') LIKE 'poupa%tempo'
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
                'quantidade' => $quant
            );
            $postosPorCodigo[$codigo]['qtd_total'] += $quant;
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
$nomesPorPosto = array();
$enderecosPorPosto = array();
$quantidadesPorPosto = array();

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
$titulo_pdf = 'Comprovante de Entrega - Poupatempo';
if (isset($id_despacho_post) && $id_despacho_post > 0) {
    $data_titulo = date('d-m-Y');
    $titulo_pdf = $id_despacho_post . '_poupatempo_' . $data_titulo;
}
?>
<title><?php echo htmlspecialchars($titulo_pdf, ENT_QUOTES, 'UTF-8'); ?></title>
<style>
/* ====== v8.15.3: Layout melhorado - baseado em modelo antigo ====== */
table{border:1px solid #000;border-collapse:collapse;margin:10px;width:100%;}
th,td{border:1px solid #000;padding:8px!important;text-align:center}
th{background:#f2f2f2}
body{font-family:Arial,Helvetica,sans-serif;background:#f0f0f0;line-height:1.4}

/* Controles na tela (não imprime) */
.controles-pagina{width:800px;margin:20px auto;padding:15px;background:#fff;border:1px dashed #ccc;text-align:center}
.controles-pagina button{padding:10px 20px;font-size:16px;background:#007bff;color:#fff;border:none;border-radius:5px;cursor:pointer;margin:5px}
.controles-pagina button:hover{background:#0056b3}
.controles-pagina button.btn-sucesso{background:#28a745}
.controles-pagina button.btn-sucesso:hover{background:#1e7e34}
.controles-pagina button.btn-imprimir{background:#6c757d}
.controles-pagina button.btn-imprimir:hover{background:#545b62}

/* Folha A4 - v8.15.7: Margem 10mm para não encostar nas bordas */
.folha-a4-oficio{
    width:210mm;
    min-height:297mm;
    margin:20px auto;
    padding:10mm;
    background:#fff;
    box-shadow:0 0 10px rgba(0,0,0,.1);
    box-sizing:border-box;
    display:flex;
    page-break-after:always;
}
.folha-a4-oficio:last-of-type{page-break-after:auto}

/* Estrutura do ofício */
.oficio{
    width:100%;
    display:flex;
    flex-direction:column;
    min-height:calc(297mm - 40mm);
}
.oficio *{box-sizing:border-box}

/* Classes de layout */
.cols100{width:100%;margin-bottom:10px}
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
}
.oficio-observacao{
    height:100%;
    display:flex;
    flex-direction:column;
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

/* Moldura */
.moldura{outline:1px solid #000;padding:8px}

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

@media print{
    body{background:#fff;margin:0;padding:0}
    .controles-pagina,.nao-imprimir{display:none !important}
    
    .folha-a4-oficio{
        width:210mm;
        margin:0;
        padding:8mm;
        box-shadow:none;
        display:block;
        break-after:page;
        min-height:auto;
    }
    
    .oficio{
        display:flex;
        flex-direction:column;
        height:calc(297mm - 16mm);
    }
    
    .processo{flex:1}
    
    /* Evitar quebra nos últimos blocos */
    .oficio .cols100.border-1px.p5:nth-last-of-type(2),
    .oficio .cols100.border-1px.p5:last-of-type{
        page-break-inside:avoid;
        break-inside:avoid;
    }
    
    /* Garantir que tabelas não quebrem */
    table{page-break-inside:avoid}
    
    /* v9.8.2: Ocultar lotes desmarcados na impressão */
    .linha-lote[data-checked="0"]{
        display:none !important;
    }
    
    /* v9.8.5: Ocultar checkboxes, título e coluna vazia na impressão */
    .titulo-controle,
    .checkbox-lote,
    .marcar-todos,
    .col-checkbox{
        display:none !important;
    }
    
    /* v9.8.5: Ocultar texto "(lotes marcados):" no rodapé */
    .lotes-detalhe tfoot strong{
        display:none !important;
    }
    
    /* v9.8.5: Ajustar tabela sem coluna de checkbox */
    .lotes-detalhe thead th:first-child,
    .lotes-detalhe tbody td:first-child,
    .lotes-detalhe tfoot td:first-child{
        display:none !important;
        width:0 !important;
        padding:0 !important;
    }
    
    .tabela-lotes{
        background:transparent !important;
        border:1px solid #ccc !important;
        padding:5px !important;
    }
    
    /* v9.8.5: Ajusta layout da tabela de lotes na impressão */
    .lotes-detalhe thead tr,
    .lotes-detalhe tbody tr,
    .lotes-detalhe tfoot tr{
        background:transparent !important;
    }
    
    .lotes-detalhe th,
    .lotes-detalhe td{
        font-size:11px !important;
        padding:4px !important;
    }
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

function executarGravacaoPT(modo, comImpressao) {
    var form = document.getElementById('formOficio');
    if (form) {
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
function recalcularTotal(posto) {
    var checkboxes = document.querySelectorAll('.checkbox-lote[data-posto="' + posto + '"]');
    var total = 0;
    var lotesConfirmados = [];
    
    for (var i = 0; i < checkboxes.length; i++) {
        var cb = checkboxes[i];
        var quantidade = parseInt(cb.getAttribute('data-quantidade')) || 0;
        var lote = cb.getAttribute('data-lote');
        var linha = cb.closest('tr');
        
        if (cb.checked) {
            total += quantidade;
            lotesConfirmados.push(lote);
            linha.setAttribute('data-checked', '1');
        } else {
            linha.setAttribute('data-checked', '0');
        }
    }
    
    // Atualiza displays
    var totalCins = document.getElementById('total_' + posto);
    if (totalCins) {
        totalCins.textContent = formatarNumero(total);
    }
    
    var totalRodape = document.getElementById('total_rodape_' + posto);
    if (totalRodape) {
        totalRodape.textContent = formatarNumero(total);
    }
    
    // Atualiza hidden inputs
    var hiddenLotes = document.getElementById('lotes_confirmados_' + posto);
    if (hiddenLotes) {
        hiddenLotes.value = lotesConfirmados.join(',');
    }
    
    var hiddenQuantidade = document.getElementById('quantidade_final_' + posto);
    if (hiddenQuantidade) {
        hiddenQuantidade.value = total;
    }
    
    // Atualiza checkbox "marcar todos"
    atualizarCheckboxMarcarTodos(posto);
}

// v9.8.2: Marca/desmarca todos os lotes de um posto
function marcarTodosLotes(checkbox, posto) {
    var checkboxes = document.querySelectorAll('.checkbox-lote[data-posto="' + posto + '"]');
    var marcado = checkbox.checked;
    
    for (var i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = marcado;
    }
    
    recalcularTotal(posto);
}

// v9.8.2: Atualiza estado do checkbox "marcar todos"
function atualizarCheckboxMarcarTodos(posto) {
    var checkboxes = document.querySelectorAll('.checkbox-lote[data-posto="' + posto + '"]');
    var marcarTodos = document.querySelector('.marcar-todos[data-posto="' + posto + '"]');
    
    if (!marcarTodos) return;
    
    var todosмаrcados = true;
    var algumMarcado = false;
    
    for (var i = 0; i < checkboxes.length; i++) {
        if (checkboxes[i].checked) {
            algumMarcado = true;
        } else {
            todosMarcados = false;
        }
    }
    
    marcarTodos.checked = todosMarcados;
    marcarTodos.indeterminate = algumMarcado && !todosMarcados;
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
  <!-- Datas usadas no ofício (string original, como em ciDespachos.datas_str) -->
  <input type="hidden" name="pt_datas" value="<?php echo e($datasStr); ?>">
  <!-- Flag para imprimir após salvar -->
  <input type="hidden" name="imprimir_apos_salvar" id="imprimir_apos_salvar" value="0">
  <!-- v8.14.3: Modo do ofício (sobrescrever/novo) -->
  <input type="hidden" name="modo_oficio" id="modo_oficio_pt" value="">

  <div class="controles-pagina nao-imprimir">
    <h2>Modelo de Oficio - Poupatempo</h2>
    
    <?php if (!empty($mensagem_status)): ?>
    <div class="mensagem-status <?php echo ($tipo_mensagem === 'sucesso') ? 'mensagem-sucesso' : 'mensagem-erro'; ?>">
        <?php echo e($mensagem_status); ?>
    </div>
    <?php endif; ?>
    
    <p>
      Uma pagina por posto Poupatempo.
      <?php if ($id_despacho > 0): ?>
        Expedicao n. <b><?php echo (int)$id_despacho; ?></b>.
      <?php else: ?>
        <b style="color:orange">
          Atencao: este oficio ainda nao foi salvo. Clique em "Gravar" para salvar.
        </b>
      <?php endif; ?>
    </p>

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
  </div>

<?php if ($temDados): ?>
  <?php foreach ($paginas as $idx => $p): 
        $codigo   = $p['codigo'];                     
        $nome     = $p['nome'] ? $p['nome'] : "POUPA TEMPO";
        $qtd_total = (int)$p['qtd_total'];  // v9.8.2: Total de todos os lotes
        $lotes_array = isset($p['lotes']) && is_array($p['lotes']) ? $p['lotes'] : array();  // v9.8.3: Validação
        $endereco = isset($p['endereco']) ? $p['endereco'] : '';

        // garante código com 3 dígitos
        $codigo3 = str_pad($codigo, 3, '0', STR_PAD_LEFT);
        
        // Prioridade: dados salvos (do POST atual) > dados do banco > dados do SELECT original
        $valorLacre = isset($lacresPorPosto[$codigo3]) ? $lacresPorPosto[$codigo3] : '';
        $valorNome = isset($nomesPorPosto[$codigo3]) ? $nomesPorPosto[$codigo3] : ($codigo . ' - ' . $nome);
        $valorEndereco = isset($enderecosPorPosto[$codigo3]) ? $enderecosPorPosto[$codigo3] : $endereco;
        $valorQuantidade = isset($quantidadesPorPosto[$codigo3]) ? $quantidadesPorPosto[$codigo3] : $qtd_total;
  ?>
  <div class="folha-a4-oficio">
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

      <div class="cols100 center border-1px p5 moldura">
        <h4 class="left">
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

      <div class="cols100 processo border-1px">
        <div class="oficio-observacao">
          <table style="table-layout:fixed; width:100%;">
            <tr>
              <th style="width:55%; max-width:350px;">Poupatempo</th>
              <th style="width:22%;">Quantidade de CIN's</th>
              <th style="width:23%;">Numero do Lacre</th>
            </tr>
            <tr>
              <!-- v8.15.7: Nome do posto com fonte 14px (legível) e quebra de linha automática -->
              <td style="width:55%; max-width:350px; text-align:left; padding:10px !important;">
                <div style="width:100%; word-wrap:break-word; overflow-wrap:break-word; white-space:normal; line-height:1.3;">
                  <input type="text" 
                         name="nome_posto[<?php echo e($codigo3); ?>]" 
                         value="<?php echo e($valorNome); ?>" 
                         class="input-editavel"
                         style="width:100%; border:none; background:transparent; font-size:14px; font-weight:bold; word-wrap:break-word; overflow-wrap:break-word; white-space:normal; line-height:1.3;">
                </div>
              </td>
              <!-- Quantidade de carteiras - v9.8.2: Calculada dinamicamente dos lotes marcados -->
              <td style="text-align:right">
                <span class="total-cins" id="total_<?php echo e($codigo3); ?>" style="font-weight:bold; font-size:14px;">
                  <?php echo number_format($valorQuantidade, 0, ',', '.'); ?>
                </span>
              </td>
              <!-- Número do lacre -->
              <td style="text-align:right">
                <input type="text"
                    name="lacre_iipr[<?php echo e($codigo3); ?>]"
                    value="<?php echo e($valorLacre); ?>"
                    class="input-editavel"
                    style="text-align:right;"
                >
              </td>
            </tr>
          </table>

          <!-- v9.8.3: Tabela de Lotes Individuais com Checkboxes -->
          <?php if (!empty($lotes_array)): // v9.8.3: Só exibe se houver lotes ?>
          <div class="tabela-lotes no-print-controls" style="margin-top:15px; padding:10px; background:#f9f9f9; border:1px solid #ddd; border-radius:4px;">
            <h5 class="titulo-controle" style="margin:0 0 10px 0; color:#333; font-size:13px; font-weight:bold;">
              📦 Lotes para Despacho (marque os lotes a enviar):
            </h5>
            <table style="width:100%; border-collapse:collapse;" class="lotes-detalhe">
              <thead>
                <tr stclass="col-checkbox" yle="background:#e0e0e0;">
                  <th style="width:10%; text-align:center; padding:6px; border:1px solid #ccc;">
                    <input type="checkbox" 
                           class="marcar-todos" 
                           data-posto="<?php echo e($codigo3); ?>" 
                           checked 
                           onchange="marcarTodosLotes(this, '<?php echo e($codigo3); ?>')">
                  </th>
                  <th style="width:50%; text-align:left; padding:6px; border:1px solid #ccc;">Lote</th>
                  <th style="width:40%; text-align:right; padding:6px; border:1px solid #ccc;">Quantidade</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($lotes_array as $lote_info): ?>
                <tr clclass="col-checkbox" ass="linha-lote" data-posto="<?php echo e($codigo3); ?>" data-checked="1">
                  <td style="text-align:center; padding:6px; border:1px solid #ccc;">
                    <input type="checkbox" 
                           class="checkbox-lote" 
                           data-posto="<?php echo e($codigo3); ?>" 
                           data-quantidade="<?php echo e($lote_info['quantidade']); ?>"
                           data-lote="<?php echo e($lote_info['lote']); ?>"
                           checked 
                           onchange="recalcularTotal('<?php echo e($codigo3); ?>')">
                  </td>
                  <td style="text-align:left; padding:6px; border:1px solid #ccc; font-weight:bold;">
                    <?php echo e($lote_info['lote']); ?>
                  </td>
                  <td style="text-align:right; padding:6px; border:1px solid #ccc;">
                    <?php echo number_format($lote_info['quantidade'], 0, ',', '.'); ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
              <tfoot>
                <tr style="background:#f0f0f0; font-weight:bold;">
                  <td class="col-checkbox" style="border:1px solid #ccc;"></td>
                  <td style="text-align:right; padding:6px; border:1px solid #ccc;">
                    <strong>TOTAL (lotes marcados):</strong>
                  </td>
                  <td style="text-align:right; padding:6px; border:1px solid #ccc;">
                    <span class="total-lotes-rodape" id="total_rodape_<?php echo e($codigo3); ?>">
                      <?php echo number_format($qtd_total, 0, ',', '.'); ?>
                    </span>
                  </td>
                </tr>
              </tfoot>
            </table>
            <input type="hidden" 
                   name="lotes_confirmados[<?php echo e($codigo3); ?>]" 
                   id="lotes_confirmados_<?php echo e($codigo3); ?>" 
                   value="<?php echo implode(',', array_map(function($l){ return $l['lote']; }, $lotes_array)); ?>">
            <input type="hidden" 
                   name="quantidade_posto[<?php echo e($codigo3); ?>]" 
                   id="quantidade_final_<?php echo e($codigo3); ?>" 
                   value="<?php echo $qtd_total; ?>">
          </div>
          <?php endif; ?>  <!-- Fecha o if (!empty($lotes_array)) -->

          <div style="flex-grow:1;"></div>

          <div class="txtjust" style="margin-bottom:5px">
            Entregue em maos para __________________________________________________,<br>
            RG/CPF: _____________________________, que abaixo assina.
          </div>
        </div>
      </div>

      <div class="cols100 border-1px p5">
        <div class="cols50 fleft"><h4><b>Entregue por: </b><i>_________________</i></h4></div>
        <div class="cols50 fright"><h4><b>DATA: </b><i><?php echo date('d/m/Y'); ?></i></h4></div>
      </div>

      <div class="cols100 border-1px p5">
        <div class="cols100"><h4><b>Assinatura:</b></h4></div>
        <div class="cols100"><h4><b>Data:</b></h4></div>
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

<?php
// Se salvou com sucesso e flag de imprimir está ativa, adiciona script para imprimir
if ($deve_imprimir && $tipo_mensagem === 'sucesso'):
?>
<script type="text/javascript">
// Imprime após pequeno delay para garantir que a página renderizou
setTimeout(function() {
    window.print();
}, 500);
</script>
<?php endif; ?>

</body>
</html>
