<?php
/* modelo_oficio_poupa_tempo.php – Poupatempo (uma página por posto)
   - NÃO depende mais de poupatempo_payload
   - Usa pt_datas (enviado pelo formulário escondido em lacres_novo.php)
   - Faz SELECT direto em ciPostosCsv + ciRegionais para montar:
       código do posto, nome, quantidade total, endereço
   - Gera uma página de ofício por posto poupatempo
   - ATUALIZADO: Salva nome_posto, endereco e lacre_iipr no banco de dados
   - Compatível com PHP 5.3.3
   
   v8.14.3: Confirmação com 3 opções (Sobrescrever/Criar Novo/Cancelar)
   - Modal customizado ao clicar "Gravar Dados" ou "Gravar e Imprimir"
   - Modo sobrescrever: apaga itens/lotes do último ofício antes de gravar
   - Modo novo: cria novo ofício com número incrementado
   - Campo modo_oficio enviado via POST para o handler
   
   v8.14.4: Gravação completa de lotes PT
   - Campo lote agora salvo em ciDespachoItens (antes vazio)
   - SELECT usa GROUP_CONCAT para capturar todos os lotes do posto
   - Compatibilidade com pesquisas por lote no BD
   
   v8.14.5: Modal confirmação + botões pulsantes + correção FK
   - Modal 3 opções (Sobrescrever/Novo/Cancelar) ao clicar "Gravar e Imprimir"
   - Botões pulsam quando há dados não salvos na tela
   - Correção erro FK: garantir id_despacho existe antes de INSERT em ciDespachoItens
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
        $modoOficio = isset($_POST['modo_oficio']) ? trim($_POST['modo_oficio']) : '';
        
        // Se não tiver id_despacho, precisa criar o despacho primeiro
        if ($id_despacho_post <= 0 && !empty($datasStr_post)) {
            $grupo = 'POUPA TEMPO';
            $usuario = isset($_SESSION['usuario']) ? $_SESSION['usuario'] : 'conferencia';
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

        $sqlUpd = "
            UPDATE ciDespachoItens
               SET lacre_iipr = :lacre,
                   nome_posto = :nome,
                   endereco = :endereco,
                   lote = :lote,
                   quantidade = :quantidade
             WHERE id_despacho = :id_despacho
               AND posto = :posto
        ";
        $stmUpd = $pdo_controle->prepare($sqlUpd);

        $sqlIns = "
            INSERT INTO ciDespachoItens (id_despacho, posto, lacre_iipr, nome_posto, endereco, lote, quantidade, incluir)
            VALUES (:id_despacho, :posto, :lacre, :nome, :endereco, :lote, :quantidade, 1)
        ";
        $stmIns = $pdo_controle->prepare($sqlIns);

        $totalInseridos = 0;
        $totalAtualizados = 0;

        // Itera sobre todos os postos
        $postos_processados = array_keys($dados_salvos);

        foreach ($postos_processados as $posto) {
            $valorLacre = isset($dados_salvos[$posto]['lacre']) ? $dados_salvos[$posto]['lacre'] : '';
            $valorNome = isset($dados_salvos[$posto]['nome']) ? $dados_salvos[$posto]['nome'] : '';
            $valorEndereco = isset($dados_salvos[$posto]['endereco']) ? $dados_salvos[$posto]['endereco'] : '';
            $valorLote = isset($dados_salvos[$posto]['lote']) ? $dados_salvos[$posto]['lote'] : '';
            $valorQuantidade = isset($dados_salvos[$posto]['quantidade']) ? $dados_salvos[$posto]['quantidade'] : 0;

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
                    ':quantidade' => $valorQuantidade
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

    $sql = "
        SELECT 
            LPAD(c.posto,3,'0') AS codigo,
            COALESCE(r.nome, CONCAT('POUPA TEMPO - ', LPAD(c.posto,3,'0'))) AS nome,
            SUM(COALESCE(c.quantidade,0)) AS quantidade,
            GROUP_CONCAT(DISTINCT c.lote ORDER BY c.lote SEPARATOR ',') AS lotes,
            r.endereco AS endereco
        FROM ciPostosCsv c
        INNER JOIN ciRegionais r 
                ON LPAD(r.posto,3,'0') = LPAD(c.posto,3,'0')
        WHERE DATE(c.dataCarga) IN ($in)
          AND REPLACE(LOWER(r.entrega),' ','') LIKE 'poupa%tempo'
        GROUP BY 
            LPAD(c.posto,3,'0'), r.nome, r.endereco
        ORDER BY 
            LPAD(c.posto,3,'0')
    ";

    try {
        $stmt = $pdo_controle->query($sql);
        foreach ($stmt as $r) {
            $codigo   = (string)$r['codigo'];           
            $nome     = (string)$r['nome'];             
            $quant    = (int)$r['quantidade'];          
            $lotes    = isset($r['lotes']) ? (string)$r['lotes'] : '';
            $endereco = trim((string)$r['endereco']);   

            $paginas[] = array(
                'codigo'   => $codigo,
                'nome'     => $nome,
                'qtd'      => $quant,
                'lotes'    => $lotes,
                'endereco' => $endereco,
            );
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
<title>Comprovante de Entrega - Poupatempo</title>
<style>
/* ====== estilos base (mantidos do seu modelo) ====== */
table{border:1px solid #000;border-collapse:collapse;margin:10px}
th,td{border:1px solid #000;padding:8px!important;text-align:center}
th{background:#f2f2f2}
body{font-family:Arial,Helvetica,sans-serif;background:#f0f0f0;line-height:1.4}
.controles-pagina{width:800px;margin:20px auto;padding:15px;background:#fff;border:1px dashed #ccc;text-align:center}
.controles-pagina button{padding:10px 20px;font-size:16px;background:#007bff;color:#fff;border:none;border-radius:5px;cursor:pointer;margin:5px}
.controles-pagina button:hover{background:#0056b3}
.controles-pagina button.btn-sucesso{background:#28a745}
.controles-pagina button.btn-sucesso:hover{background:#1e7e34}
.controles-pagina button.btn-imprimir{background:#6c757d}
.controles-pagina button.btn-imprimir:hover{background:#545b62}

.folha-a4-oficio{width:210mm;min-height:297mm;margin:20px auto;padding:20mm;background:#fff;box-shadow:0 0 10px rgba(0,0,0,.1);box-sizing:border-box;display:flex;page-break-after:always}
.folha-a4-oficio:last-of-type{page-break-after:auto}
.oficio{width:100%;display:flex;flex-direction:column;min-height:calc(297mm - 40mm)}
.oficio *{box-sizing:border-box}
.cols100{width:100%;margin-bottom:10px}
.cols65{width:65%}.cols50{width:50%}.cols25{width:25%}
.fleft{float:left}.fright{float:right}
.center{text-align:center}.left{text-align:left}
.border-1px{border:1px solid #000}.margin2px{margin:2px}.p5{padding:5px}
.nometit{font-weight:bold}
.processo{flex-grow:1;display:flex;flex-direction:column}
.oficio-observacao{height:100%;display:flex;flex-direction:column}
.oficio h3,.oficio h4{margin:5px 0}
.cols100:after{content:"";display:table;clear:both}

/* Estilos para inputs editáveis */
.input-editavel{
    border:none;
    border-bottom:1px solid #000;
    background:transparent;
    font-family:inherit;
    font-size:inherit;
    padding:2px 4px;
    width:100%;
}
.input-editavel:focus{
    outline:2px dashed #007bff;
    background:#ffffcc;
}

/* borda externa com outline para não "vazar" */
.moldura{outline:1px solid #000;padding:8px}

/* classe auxiliar para esconder na impressão */
.nao-imprimir{}

/* v8.14.5: Animação de pulsar para botões com dados não salvos */
@keyframes pulsar {
  0%, 100% { transform: scale(1); box-shadow: 0 0 5px rgba(255, 193, 7, 0.5); }
  50% { transform: scale(1.05); box-shadow: 0 0 20px rgba(255, 193, 7, 0.8); }
}
.btn-nao-salvo {
  animation: pulsar 2s ease-in-out infinite;
  border: 2px solid #ffc107 !important;
}

/* Mensagens de status */
.mensagem-status{
    padding:10px 20px;
    margin:10px 0;
    border-radius:5px;
    font-weight:bold;
}
.mensagem-sucesso{
    background:#d4edda;
    color:#155724;
    border:1px solid #c3e6cb;
}
.mensagem-erro{
    background:#f8d7da;
    color:#721c24;
    border:1px solid #f5c6cb;
}

@media print{
  /* limpa a página */
  body{background:#fff;margin:0;padding:0}
  .controles-pagina,
  .nao-imprimir{display:none}

  /* inputs aparecem como texto normal na impressão */
  .input-editavel{
    border:none!important;
    border-bottom:none!important;
    background:transparent!important;
  }

  /* 1) a folha ocupa exatamente 1 página */
  .folha-a4-oficio{
    width:210mm;
    margin:0;
    padding:8mm;
    box-shadow:none;
    display:block;
    break-after: page;
    min-height:auto;
  }

  /* 2) altura útil da área interna = 297mm - (padding topo+base) */
  .oficio{
    display:flex;
    flex-direction:column;
    height: calc(297mm - 16mm);
  }

  /* mantém o miolo elástico */
  .processo{ flex:1 }

  /* 3) não deixar os dois últimos blocos quebrarem */
  .oficio .cols100.border-1px.p5:nth-last-of-type(2),
  .oficio .cols100.border-1px.p5:last-of-type{
    page-break-inside: avoid;
    break-inside: avoid;
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
        $quant    = (int)$p['qtd'];
        $endereco = isset($p['endereco']) ? $p['endereco'] : '';

        // garante código com 3 dígitos
        $codigo3 = str_pad($codigo, 3, '0', STR_PAD_LEFT);
        
        // Prioridade: dados salvos (do POST atual) > dados do banco > dados do SELECT original
        $valorLacre = isset($lacresPorPosto[$codigo3]) ? $lacresPorPosto[$codigo3] : '';
        $valorNome = isset($nomesPorPosto[$codigo3]) ? $nomesPorPosto[$codigo3] : ($codigo . ' - ' . $nome);
        $valorEndereco = isset($enderecosPorPosto[$codigo3]) ? $enderecosPorPosto[$codigo3] : $endereco;
        $valorQuantidade = isset($quantidadesPorPosto[$codigo3]) ? $quantidadesPorPosto[$codigo3] : $quant;
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
              <th style="min-width:350px; width:55%;">Poupatempo</th>
              <th style="width:22%;">Quantidade de CIN's</th>
              <th style="width:23%;">Numero do Lacre</th>
            </tr>
            <tr>
              <!-- Nome do posto editável como input -->
              <td style="min-width:350px;">
                <input type="text" 
                       name="nome_posto[<?php echo e($codigo3); ?>]" 
                       value="<?php echo e($valorNome); ?>" 
                       class="input-editavel"
                       style="width:100%; min-width:320px;">
              </td>
              <!-- Quantidade de carteiras editável como input -->
              <td style="text-align:right">
                <input type="text" 
                       name="quantidade_posto[<?php echo e($codigo3); ?>]" 
                       value="<?php echo e($valorQuantidade); ?>" 
                       class="input-editavel"
                       style="text-align:right;">
              </td>
              <!-- Número do lacre -->
              <td style="text-align:right">
                <input type="text"
                    name="lacre_iipr[<?php echo e($codigo3); ?>]"
                    value="<?php echo e($valorLacre); ?>"
                    class="input-editavel"
                    style="text-align:right;"
                >
                <!-- v8.14.4: Hidden input para lote (usado ao salvar) -->
                <input type="hidden"
                    name="lote_posto[<?php echo e($codigo3); ?>]"
                    value="<?php echo e($p['lotes']); ?>"
                >
              </td>
            </tr>
          </table>

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
  <?php endforeach; ?>

<?php else: ?>
  <!-- Página editável padrão quando não há dados (nenhum Poupatempo encontrado) -->
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
          <br><span class="nometit">ENDERECO: 
            <input type="text" name="endereco_posto[000]" value="" class="input-editavel" style="width:90%;" placeholder="Digite o endereco">
          </span>
          <br><span class="nometit"></span>
        </h4>
      </div>

      <div class="cols100 processo border-1px">
        <div class="oficio-observacao">
          <table style="table-layout:fixed; width:100%;">
            <tr>
              <th style="min-width:350px; width:55%;">Poupatempo</th>
              <th style="width:22%;">Quantidade de CIN's</th>
              <th style="width:23%;">Numero do Lacre</th>
            </tr>
            <tr>
              <td style="min-width:350px;"><input type="text" name="nome_posto[000]" value="" class="input-editavel" style="width:100%; min-width:320px;" placeholder="Digite o posto"></td>
              <td style="text-align:right"><input type="text" name="quantidade_posto[000]" value="" class="input-editavel" style="text-align:right;" placeholder="0"></td>
              <td style="text-align:right"><input type="text" name="lacre_iipr[000]" value="" class="input-editavel" style="text-align:right;" placeholder="Lacre"></td>
            </tr>
          </table>
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
<?php endif; ?>

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
