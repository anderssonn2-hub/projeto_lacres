<?php
error_reporting(E_ALL & ~E_NOTICE);
header('Content-Type: text/html; charset=utf-8');

if (!isset($_SESSION)) {
    session_start();
}

function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function normalizarDataPtSqlDinamico($valor) {
    $valor = trim((string)$valor);
    if ($valor === '') {
        return '';
    }
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $valor)) {
        return $valor;
    }
    if (preg_match('/^(\d{2})[\/-](\d{2})[\/-](\d{4})$/', $valor, $m)) {
        return $m[3] . '-' . $m[2] . '-' . $m[1];
    }
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})\s+\d{2}:\d{2}:\d{2}$/', $valor, $m)) {
        return $m[1] . '-' . $m[2] . '-' . $m[3];
    }
    return '';
}

function formatarDataBrDinamico($valor) {
    $sql = normalizarDataPtSqlDinamico($valor);
    if ($sql === '') {
        return '';
    }
    return substr($sql, 8, 2) . '-' . substr($sql, 5, 2) . '-' . substr($sql, 0, 4);
}

function tabelaTemColunaDinamico($pdo, $tabela, $coluna) {
    static $cache = array();
    $chave = $tabela . '.' . $coluna;
    if (isset($cache[$chave])) {
        return $cache[$chave];
    }
    try {
        $stmt = $pdo->prepare('SHOW COLUMNS FROM ' . $tabela . ' LIKE ?');
        $stmt->execute(array($coluna));
        $cache[$chave] = $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
    } catch (Exception $e) {
        $cache[$chave] = false;
    }
    return $cache[$chave];
}

function resolverNomePostoCiPostosDinamico($pdo, $posto) {
    $posto = trim((string)$posto);
    if ($posto === '') {
        return '';
    }
    $postoPad = str_pad(preg_replace('/\D+/', '', $posto), 3, '0', STR_PAD_LEFT);
    try {
        $stmt = $pdo->prepare('SELECT posto FROM ciPostos WHERE posto LIKE ? ORDER BY id DESC LIMIT 1');
        $stmt->execute(array($postoPad . ' -%'));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && !empty($row['posto'])) {
            return $row['posto'];
        }
    } catch (Exception $e) {
    }
    return $postoPad . ' - POSTO';
}

function mapearTurnoCiPostosDinamico($turno) {
    $turno = trim((string)$turno);
    if ($turno === 'Madrugada') {
        return 0;
    }
    if ($turno === 'Tarde') {
        return 2;
    }
    if ($turno === 'Noite') {
        return 3;
    }
    return 1;
}

function normalizarDataHoraSqlDinamico($valor) {
    $valor = trim((string)$valor);
    if ($valor === '') {
        return '';
    }
    $valor = str_replace('T', ' ', $valor);
    if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $valor)) {
        return $valor . ':00';
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $valor)) {
        return $valor;
    }
    return '';
}

$pdo = null;
$erroConexao = '';
try {
    $pdo = new PDO(
        'mysql:host=10.15.61.169;dbname=controle;charset=utf8mb4',
        'controle_mat',
        '375256'
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $erroConexao = $e->getMessage();
}

if ($pdo && isset($_POST['ajax_resolver_codbar_pt'])) {
    header('Content-Type: application/json');
    $codbar = isset($_POST['codbar']) ? preg_replace('/\D+/', '', (string)$_POST['codbar']) : '';
    $dataPadrao = normalizarDataPtSqlDinamico(isset($_POST['data_padrao']) ? $_POST['data_padrao'] : '');
    if ($dataPadrao === '') {
        $dataPadrao = date('Y-m-d');
    }

    if (!preg_match('/^\d{19}$/', $codbar)) {
        die(json_encode(array('success' => false, 'erro' => 'Codigo invalido. Use 19 digitos.')));
    }

    $lote = str_pad(substr($codbar, 0, 8), 8, '0', STR_PAD_LEFT);
    $regional = str_pad(substr($codbar, 8, 3), 3, '0', STR_PAD_LEFT);
    $posto = str_pad(substr($codbar, 11, 3), 3, '0', STR_PAD_LEFT);
    $quantidadeBarra = (int)substr($codbar, 14, 5);

    $stmtPosto = $pdo->prepare("SELECT LPAD(posto,3,'0') AS posto, COALESCE(NULLIF(TRIM(nome),''), LPAD(posto,3,'0')) AS nome, endereco,
                                      LOWER(REPLACE(COALESCE(entrega,''),' ','')) AS entrega
                               FROM ciRegionais
                               WHERE LPAD(posto,3,'0') = ?
                               LIMIT 1");
    $stmtPosto->execute(array($posto));
    $postoRow = $stmtPosto->fetch(PDO::FETCH_ASSOC);

    if (!$postoRow) {
        die(json_encode(array('success' => false, 'erro' => 'Posto nao encontrado em ciRegionais.')));
    }

    $entrega = isset($postoRow['entrega']) ? (string)$postoRow['entrega'] : '';
    if (strpos($entrega, 'poupa') === false && strpos($entrega, 'tempo') === false) {
        die(json_encode(array('success' => false, 'erro' => 'Este codigo nao pertence a um posto Poupa Tempo.')));
    }

    $stmtCarga = $pdo->prepare("SELECT LPAD(CAST(lote AS CHAR),8,'0') AS lote,
                                       LPAD(CAST(posto AS CHAR),3,'0') AS posto,
                                       LPAD(CAST(regional AS CHAR),3,'0') AS regional,
                                       COALESCE(quantidade,0) AS quantidade,
                                       DATE(COALESCE(dataCarga, data)) AS data_carga,
                                       usuario
                                FROM ciPostosCsv
                                WHERE LPAD(CAST(lote AS CHAR),8,'0') = ?
                                  AND LPAD(CAST(posto AS CHAR),3,'0') = ?
                                ORDER BY CASE WHEN LPAD(CAST(regional AS CHAR),3,'0') = ? THEN 0 ELSE 1 END,
                                         COALESCE(dataCarga, data) DESC
                                LIMIT 1");
    $stmtCarga->execute(array($lote, $posto, $regional));
    $cargaRow = $stmtCarga->fetch(PDO::FETCH_ASSOC);

    $carregado = $cargaRow ? true : false;
    $quantidade = $carregado ? (int)$cargaRow['quantidade'] : $quantidadeBarra;
    if ($quantidade <= 0) {
        $quantidade = $quantidadeBarra > 0 ? $quantidadeBarra : 1;
    }
    $dataCarga = $carregado && !empty($cargaRow['data_carga']) ? normalizarDataPtSqlDinamico($cargaRow['data_carga']) : $dataPadrao;
    $usuario = $carregado && !empty($cargaRow['usuario']) ? trim((string)$cargaRow['usuario']) : (isset($_SESSION['usuario']) ? trim((string)$_SESSION['usuario']) : '');

    die(json_encode(array(
        'success' => true,
        'carregado' => $carregado,
        'codbar' => $codbar,
        'lote' => $lote,
        'regional' => $regional,
        'posto' => $posto,
        'nome' => isset($postoRow['nome']) ? trim((string)$postoRow['nome']) : $posto,
        'endereco' => isset($postoRow['endereco']) ? trim((string)$postoRow['endereco']) : '',
        'quantidade' => $quantidade,
        'data_carga' => $dataCarga,
        'data_carga_br' => formatarDataBrDinamico($dataCarga),
        'responsaveis' => $usuario,
        'mensagem' => $carregado ? 'Lote localizado em ciPostosCsv.' : 'Lote nao carregado. Adicionado com data padrao e fila de pendencias.'
    )));
}

if ($pdo && isset($_POST['inserir_pendencias_pt'])) {
    header('Content-Type: application/json');
    $payload = isset($_POST['pacotes']) ? $_POST['pacotes'] : '';
    $usuarioConf = isset($_POST['usuario']) ? trim((string)$_POST['usuario']) : '';
    $autor = isset($_POST['autor_salvamento']) ? trim((string)$_POST['autor_salvamento']) : '';
    $criado = normalizarDataHoraSqlDinamico(isset($_POST['criado_salvamento']) ? $_POST['criado_salvamento'] : '');
    $turno = isset($_POST['turno_salvamento']) ? trim((string)$_POST['turno_salvamento']) : 'Manhã';
    $consolidar = !empty($_POST['consolidar_salvamento']);
    $pacotes = json_decode($payload, true);

    if (!is_array($pacotes) || empty($pacotes)) {
        die(json_encode(array('success' => false, 'erro' => 'Nenhuma pendencia para salvar.')));
    }
    if ($usuarioConf === '') {
        $usuarioConf = isset($_SESSION['usuario']) ? trim((string)$_SESSION['usuario']) : '';
    }
    if ($usuarioConf === '') {
        die(json_encode(array('success' => false, 'erro' => 'Responsavel obrigatorio.')));
    }
    if ($autor === '') {
        $autor = $usuarioConf;
    }
    if ($criado === '') {
        $criado = date('Y-m-d H:i:s');
    }

    $stmtCsv = $pdo->prepare('INSERT INTO ciPostosCsv (lote, posto, regional, quantidade, dataCarga, data, usuario) VALUES (?,?,?,?,?,NOW(),?)');
    $okCsv = 0;
    $okPostos = 0;
    $erros = array();
    $grupos = array();

    foreach ($pacotes as $pacote) {
        try {
            $lote = isset($pacote['lote']) ? str_pad(preg_replace('/\D+/', '', (string)$pacote['lote']), 8, '0', STR_PAD_LEFT) : '';
            $posto = isset($pacote['posto']) ? str_pad(preg_replace('/\D+/', '', (string)$pacote['posto']), 3, '0', STR_PAD_LEFT) : '';
            $regional = isset($pacote['regional']) ? str_pad(preg_replace('/\D+/', '', (string)$pacote['regional']), 3, '0', STR_PAD_LEFT) : '';
            $quantidade = isset($pacote['quantidade']) ? (int)$pacote['quantidade'] : 0;
            $dataCarga = normalizarDataPtSqlDinamico(isset($pacote['dataexp']) ? $pacote['dataexp'] : '');
            $responsavel = isset($pacote['responsavel']) ? trim((string)$pacote['responsavel']) : '';

            if ($responsavel === '') {
                $responsavel = $usuarioConf;
            }
            if ($lote === '' || $posto === '' || $regional === '' || $quantidade <= 0 || $dataCarga === '') {
                throw new Exception('Pendencia com dados obrigatorios ausentes.');
            }

            $stmtCsv->execute(array($lote, $posto, $regional, $quantidade, $dataCarga, $responsavel));
            $okCsv++;

            $chave = $posto . '|' . $dataCarga . '|' . ($consolidar ? $responsavel : $lote . '|' . $regional);
            if (!isset($grupos[$chave])) {
                $grupos[$chave] = array(
                    'posto' => $posto,
                    'dia' => $dataCarga,
                    'quantidade' => 0,
                    'turno' => mapearTurnoCiPostosDinamico($turno),
                    'autor' => $autor,
                    'criado' => $criado,
                    'regional' => $regional,
                    'lote' => $lote
                );
            }
            $grupos[$chave]['quantidade'] += $quantidade;
        } catch (Exception $e) {
            $erros[] = $e->getMessage();
        }
    }

    foreach ($grupos as $grupo) {
        try {
            $campos = array();
            $vals = array();
            $pars = array();

            if (tabelaTemColunaDinamico($pdo, 'ciPostos', 'posto')) {
                $campos[] = 'posto';
                $vals[] = '?';
                $pars[] = resolverNomePostoCiPostosDinamico($pdo, $grupo['posto']);
            }
            if (tabelaTemColunaDinamico($pdo, 'ciPostos', 'dia')) {
                $campos[] = 'dia';
                $vals[] = '?';
                $pars[] = $grupo['dia'];
            }
            if (tabelaTemColunaDinamico($pdo, 'ciPostos', 'quantidade')) {
                $campos[] = 'quantidade';
                $vals[] = '?';
                $pars[] = (int)$grupo['quantidade'];
            }
            if (tabelaTemColunaDinamico($pdo, 'ciPostos', 'turno')) {
                $campos[] = 'turno';
                $vals[] = '?';
                $pars[] = (int)$grupo['turno'];
            }
            if (tabelaTemColunaDinamico($pdo, 'ciPostos', 'autor')) {
                $campos[] = 'autor';
                $vals[] = '?';
                $pars[] = $grupo['autor'];
            }
            if (tabelaTemColunaDinamico($pdo, 'ciPostos', 'criado')) {
                $campos[] = 'criado';
                $vals[] = '?';
                $pars[] = $grupo['criado'];
            }
            if (tabelaTemColunaDinamico($pdo, 'ciPostos', 'regional')) {
                $campos[] = 'regional';
                $vals[] = '?';
                $pars[] = (int)$grupo['regional'];
            }
            if (tabelaTemColunaDinamico($pdo, 'ciPostos', 'lote') && !$consolidar) {
                $campos[] = 'lote';
                $vals[] = '?';
                $pars[] = (int)$grupo['lote'];
            }
            if (tabelaTemColunaDinamico($pdo, 'ciPostos', 'situacao')) {
                $campos[] = 'situacao';
                $vals[] = '?';
                $pars[] = 0;
            }

            if (!empty($campos)) {
                $sql = 'INSERT INTO ciPostos (' . implode(',', $campos) . ') VALUES (' . implode(',', $vals) . ')';
                $stmt = $pdo->prepare($sql);
                $stmt->execute($pars);
                $okPostos++;
            }
        } catch (Exception $e) {
            $erros[] = $e->getMessage();
        }
    }

    die(json_encode(array(
        'success' => $okCsv > 0,
        'salvos_csv' => $okCsv,
        'salvos_postos' => $okPostos,
        'erros' => $erros
    )));
}

$usuarioSessao = isset($_SESSION['usuario']) ? trim((string)$_SESSION['usuario']) : '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerar Ofício Poupa Tempo Dinâmico v0.9.25.23</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: "Trebuchet MS", Tahoma, Verdana, sans-serif; background: linear-gradient(180deg, #f5f1ea 0%, #eef5f9 100%); color: #22313f; }
        .page { max-width: 1280px; margin: 0 auto; padding: 24px; }
        .hero { display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; margin-bottom: 18px; }
        .hero h1 { margin: 0; font-size: 30px; color: #10324a; font-family: Georgia, "Times New Roman", serif; }
        .hero p { margin: 8px 0 0; color: #536372; }
        .badge { background: #10324a; color: #fff; padding: 8px 14px; border-radius: 999px; font-weight: bold; font-size: 12px; }
        .top-grid { display: grid; grid-template-columns: minmax(320px, 1.15fr) minmax(280px, 0.85fr); gap: 18px; margin-bottom: 18px; }
        .panel { background: rgba(255,255,255,0.94); border: 1px solid #d8e2ea; border-radius: 18px; box-shadow: 0 14px 32px rgba(16,50,74,0.1); padding: 18px; }
        .panel h2 { margin: 0 0 12px; font-size: 15px; text-transform: uppercase; letter-spacing: 1px; color: #5b7284; }
        .scan-box { display: grid; gap: 12px; }
        #inputCodbar { width: 100%; padding: 16px 18px; border-radius: 14px; border: 2px solid #8fb3c8; font-size: 26px; letter-spacing: 1px; background: #fbfdff; }
        #inputCodbar:focus { outline: none; border-color: #0e7490; box-shadow: 0 0 0 4px rgba(14,116,144,0.15); }
        .hint { font-size: 13px; color: #667888; }
        .row { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
        .toolbar { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 14px; }
        .toolbar button, .toolbar a, .btn, .field input, .field select { font: inherit; }
        .btn { border: none; border-radius: 12px; padding: 12px 16px; text-decoration: none; cursor: pointer; font-weight: bold; display: inline-flex; align-items: center; justify-content: center; gap: 8px; }
        .btn-primary { background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%); color: #fff; }
        .btn-secondary { background: linear-gradient(135deg, #334155 0%, #64748b 100%); color: #fff; }
        .btn-accent { background: linear-gradient(135deg, #b45309 0%, #f59e0b 100%); color: #fff; }
        .btn-danger { background: linear-gradient(135deg, #b91c1c 0%, #ef4444 100%); color: #fff; }
        .metrics { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; }
        .metric { background: #f6fafc; border: 1px solid #d9e7ef; border-radius: 14px; padding: 14px; }
        .metric strong { display: block; font-size: 26px; color: #10324a; margin-top: 6px; }
        .status { min-height: 44px; display: flex; align-items: center; padding: 10px 12px; border-radius: 12px; background: #edf6fb; color: #0f3d5a; font-weight: bold; }
        .status.error { background: #fdecec; color: #991b1b; }
        .workspace { display: grid; grid-template-columns: minmax(0, 1.3fr) minmax(320px, 0.7fr); gap: 18px; }
        .cards { display: grid; gap: 14px; }
        .posto-card { border: 1px solid #d4e0e8; border-radius: 18px; overflow: hidden; background: #fff; }
        .posto-head { display: flex; justify-content: space-between; gap: 12px; align-items: center; padding: 14px 16px; background: linear-gradient(135deg, #10324a 0%, #1d5c78 100%); color: #fff; }
        .posto-head h3 { margin: 0; font-size: 18px; }
        .posto-head small { display: block; opacity: 0.9; margin-top: 4px; }
        .posto-total { font-size: 24px; font-weight: bold; white-space: nowrap; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px 12px; border-bottom: 1px solid #e7edf2; text-align: left; font-size: 14px; }
        th { background: #f7fafc; color: #5c7183; font-size: 12px; text-transform: uppercase; letter-spacing: 0.8px; }
        tr.off td { opacity: 0.45; }
        .pill { display: inline-flex; align-items: center; padding: 5px 10px; border-radius: 999px; font-size: 12px; font-weight: bold; }
        .pill.ok { background: #dcfce7; color: #166534; }
        .pill.pending { background: #fef3c7; color: #92400e; }
        .side-block { margin-bottom: 16px; }
        .side-block h3 { margin: 0 0 10px; font-size: 15px; color: #24475f; }
        .empty { padding: 18px; text-align: center; color: #687b8b; border: 1px dashed #c9d6df; border-radius: 14px; background: #f9fbfd; }
        .field { display: grid; gap: 6px; margin-bottom: 10px; }
        .field label { font-size: 12px; text-transform: uppercase; letter-spacing: 0.7px; color: #61788a; font-weight: bold; }
        .field input, .field select { width: 100%; padding: 11px 12px; border-radius: 12px; border: 1px solid #c7d6e0; background: #fff; }
        .inline-check { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #4d6476; margin: 10px 0 14px; }
        .footer-actions { display: flex; flex-wrap: wrap; gap: 10px; }
        .mini { font-size: 12px; color: #6e8292; }
        @media (max-width: 980px) {
            .top-grid, .workspace, .row, .metrics { grid-template-columns: 1fr; }
            .hero { flex-direction: column; }
            #inputCodbar { font-size: 20px; }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="hero">
            <div>
                <h1>Gerar Ofício Poupa Tempo Dinâmico</h1>
                <p>Leia os códigos, monte a lista por posto e envie apenas os lotes marcados para o modelo oficial.</p>
            </div>
            <div class="badge">v0.9.25.23</div>
        </div>

        <?php if ($erroConexao !== ''): ?>
            <div class="status error" style="margin-bottom:16px;">Falha de conexão com o banco: <?php echo e($erroConexao); ?></div>
        <?php endif; ?>

        <div class="top-grid">
            <section class="panel">
                <h2>Leitura</h2>
                <div class="scan-box">
                    <input type="text" id="inputCodbar" placeholder="Escaneie ou digite o código de barras" autocomplete="off">
                    <div class="row">
                        <div class="field">
                            <label for="dataPadrao">Data produção padrão</label>
                            <input type="date" id="dataPadrao" value="<?php echo e(date('Y-m-d')); ?>">
                        </div>
                        <div class="field">
                            <label for="responsavelPadrao">Responsável</label>
                            <input type="text" id="responsavelPadrao" value="<?php echo e($usuarioSessao); ?>" maxlength="30">
                        </div>
                    </div>
                    <div class="status" id="statusLeitura">Aguardando primeira leitura.</div>
                    <div class="hint">Quando o lote não estiver carregado, ele entra na fila de pendências com a data padrão acima.</div>
                    <div class="toolbar">
                        <button type="button" class="btn btn-primary" id="btnGerarModelo">Gerar modelo do ofício</button>
                        <button type="button" class="btn btn-secondary" id="btnFocarLeitura">Focar leitura</button>
                        <button type="button" class="btn btn-danger" id="btnLimparTudo">Limpar tela</button>
                        <a class="btn btn-accent" href="inicio.php">Voltar ao início</a>
                    </div>
                </div>
            </section>

            <section class="panel">
                <h2>Resumo</h2>
                <div class="metrics">
                    <div class="metric">Postos<strong id="metricPostos">0</strong></div>
                    <div class="metric">Lotes ativos<strong id="metricLotes">0</strong></div>
                    <div class="metric">CINs<strong id="metricQtd">0</strong></div>
                    <div class="metric">Pendências<strong id="metricPendencias">0</strong></div>
                </div>
            </section>
        </div>

        <div class="workspace">
            <section class="panel">
                <h2>Montagem por posto</h2>
                <div id="cardsPostos" class="cards">
                    <div class="empty">Nenhum lote lido ainda.</div>
                </div>
            </section>

            <aside>
                <section class="panel side-block">
                    <h2>Pendências não carregadas</h2>
                    <div id="listaPendenciasWrap" class="empty">Nenhuma pendência acumulada.</div>
                </section>

                <section class="panel side-block">
                    <h3>Salvar pendências</h3>
                    <div class="field">
                        <label for="turnoPendencia">Turno</label>
                        <select id="turnoPendencia">
                            <option>Manhã</option>
                            <option>Tarde</option>
                            <option>Noite</option>
                            <option>Madrugada</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="criadoPendencia">Criado em</label>
                        <input type="datetime-local" id="criadoPendencia" value="">
                    </div>
                    <label class="inline-check"><input type="checkbox" id="consolidarPendencia"> Consolidar por posto e data ao gravar em ciPostos</label>
                    <div class="footer-actions">
                        <button type="button" class="btn btn-primary" id="btnSalvarPendencias">Salvar pendências no banco</button>
                        <button type="button" class="btn btn-secondary" id="btnLimparPendencias">Limpar pendências</button>
                    </div>
                    <div class="mini" style="margin-top:10px;">As pendências ficam salvas no navegador até serem gravadas ou removidas.</div>
                </section>
            </aside>
        </div>

        <form method="post" action="modelo_oficio_poupa_tempo.php" id="formModelo" style="display:none;">
            <input type="hidden" name="pt_datas" id="payloadDatas" value="">
            <input type="hidden" name="pt_dinamico_payload" id="payloadDinamico" value="">
        </form>
    </div>

    <script>
    var estado = { postos: {}, ordem: [], pendencias: [] };
    var chavePendencias = 'pt_dinamico_pendencias_v1';

    function formatarNumero(valor) {
        return Number(valor || 0).toLocaleString('pt-BR');
    }

    function formatarDataBr(valor) {
        var texto = String(valor || '').trim();
        var m = texto.match(/^(\d{4})-(\d{2})-(\d{2})$/);
        return m ? (m[3] + '-' + m[2] + '-' + m[1]) : texto;
    }

    function status(texto, erro) {
        var el = document.getElementById('statusLeitura');
        el.textContent = texto;
        el.className = erro ? 'status error' : 'status';
    }

    function agoraLocal() {
        var d = new Date();
        var yyyy = d.getFullYear();
        var mm = String(d.getMonth() + 1).padStart(2, '0');
        var dd = String(d.getDate()).padStart(2, '0');
        var hh = String(d.getHours()).padStart(2, '0');
        var ii = String(d.getMinutes()).padStart(2, '0');
        return yyyy + '-' + mm + '-' + dd + 'T' + hh + ':' + ii;
    }

    function carregarPendencias() {
        try {
            var bruto = localStorage.getItem(chavePendencias);
            var lista = bruto ? JSON.parse(bruto) : [];
            estado.pendencias = Object.prototype.toString.call(lista) === '[object Array]' ? lista : [];
        } catch (e) {
            estado.pendencias = [];
        }
    }

    function salvarPendencias() {
        localStorage.setItem(chavePendencias, JSON.stringify(estado.pendencias));
    }

    function existeLote(codbar) {
        for (var posto in estado.postos) {
            if (!estado.postos.hasOwnProperty(posto)) continue;
            var itens = estado.postos[posto].lotes;
            for (var i = 0; i < itens.length; i++) {
                if (itens[i].codbar === codbar) {
                    return true;
                }
            }
        }
        return false;
    }

    function atualizarResumo() {
        var totalPostos = 0;
        var totalLotes = 0;
        var totalQtd = 0;
        for (var posto in estado.postos) {
            if (!estado.postos.hasOwnProperty(posto)) continue;
            var grupo = estado.postos[posto];
            var ativosDoPosto = 0;
            for (var i = 0; i < grupo.lotes.length; i++) {
                var item = grupo.lotes[i];
                if (item.ativo) {
                    ativosDoPosto++;
                    totalLotes++;
                    totalQtd += parseInt(item.quantidade, 10) || 0;
                }
            }
            if (ativosDoPosto > 0) {
                totalPostos++;
            }
        }
        document.getElementById('metricPostos').textContent = formatarNumero(totalPostos);
        document.getElementById('metricLotes').textContent = formatarNumero(totalLotes);
        document.getElementById('metricQtd').textContent = formatarNumero(totalQtd);
        document.getElementById('metricPendencias').textContent = formatarNumero(estado.pendencias.length);
    }

    function renderizarPendencias() {
        var wrap = document.getElementById('listaPendenciasWrap');
        if (!estado.pendencias.length) {
            wrap.className = 'empty';
            wrap.innerHTML = 'Nenhuma pendência acumulada.';
            atualizarResumo();
            return;
        }
        var html = '<table><thead><tr><th>Lote</th><th>Posto</th><th>Qtd</th><th>Data</th><th></th></tr></thead><tbody>';
        for (var i = 0; i < estado.pendencias.length; i++) {
            var item = estado.pendencias[i];
            html += '<tr>' +
                '<td>' + item.lote + '</td>' +
                '<td>' + item.posto + '</td>' +
                '<td>' + formatarNumero(item.quantidade) + '</td>' +
                '<td>' + formatarDataBr(item.dataexp) + '</td>' +
                '<td><button type="button" class="btn btn-danger" onclick="removerPendencia(' + i + ')" style="padding:6px 10px;">Remover</button></td>' +
                '</tr>';
        }
        html += '</tbody></table>';
        wrap.className = '';
        wrap.innerHTML = html;
        atualizarResumo();
    }

    function removerPendencia(idx) {
        estado.pendencias.splice(idx, 1);
        salvarPendencias();
        renderizarPendencias();
    }

    function obterTotalPosto(grupo) {
        var total = 0;
        for (var i = 0; i < grupo.lotes.length; i++) {
            if (grupo.lotes[i].ativo) {
                total += parseInt(grupo.lotes[i].quantidade, 10) || 0;
            }
        }
        return total;
    }

    function escapar(texto) {
        return String(texto || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function renderizarPostos() {
        var host = document.getElementById('cardsPostos');
        if (!estado.ordem.length) {
            host.innerHTML = '<div class="empty">Nenhum lote lido ainda.</div>';
            atualizarResumo();
            return;
        }
        var html = '';
        for (var o = 0; o < estado.ordem.length; o++) {
            var codigo = estado.ordem[o];
            var grupo = estado.postos[codigo];
            if (!grupo) continue;
            html += '<article class="posto-card">';
            html += '<div class="posto-head"><div><h3>POUPA TEMPO ' + codigo + ' - ' + escapar(grupo.nome) + '</h3><small>' + escapar(grupo.endereco || 'Endereço não informado') + '</small></div><div class="posto-total">' + formatarNumero(obterTotalPosto(grupo)) + '</div></div>';
            html += '<table><thead><tr><th></th><th>Lote</th><th>Qtd</th><th>Data Produção</th><th>Origem</th><th></th></tr></thead><tbody>';
            for (var i = 0; i < grupo.lotes.length; i++) {
                var item = grupo.lotes[i];
                html += '<tr class="' + (item.ativo ? '' : 'off') + '">';
                html += '<td><input type="checkbox" ' + (item.ativo ? 'checked ' : '') + 'onchange="alternarAtivo(\'' + codigo + '\',' + i + ',this.checked)"></td>';
                html += '<td>' + item.lote + '</td>';
                html += '<td>' + formatarNumero(item.quantidade) + '</td>';
                html += '<td>' + formatarDataBr(item.data_carga) + '</td>';
                html += '<td><span class="pill ' + (item.carregado ? 'ok' : 'pending') + '">' + (item.carregado ? 'Carregado' : 'Pendente') + '</span></td>';
                html += '<td><button type="button" class="btn btn-danger" onclick="removerItem(\'' + codigo + '\',' + i + ')" style="padding:6px 10px;">Excluir</button></td>';
                html += '</tr>';
            }
            html += '</tbody></table></article>';
        }
        host.innerHTML = html;
        atualizarResumo();
    }

    function alternarAtivo(codigo, idx, checked) {
        if (estado.postos[codigo] && estado.postos[codigo].lotes[idx]) {
            estado.postos[codigo].lotes[idx].ativo = !!checked;
            renderizarPostos();
        }
    }

    function removerItem(codigo, idx) {
        if (!estado.postos[codigo]) return;
        estado.postos[codigo].lotes.splice(idx, 1);
        if (!estado.postos[codigo].lotes.length) {
            delete estado.postos[codigo];
            var novaOrdem = [];
            for (var i = 0; i < estado.ordem.length; i++) {
                if (estado.ordem[i] !== codigo) {
                    novaOrdem.push(estado.ordem[i]);
                }
            }
            estado.ordem = novaOrdem;
        }
        renderizarPostos();
    }

    function adicionarPendencia(resp) {
        for (var i = 0; i < estado.pendencias.length; i++) {
            if (estado.pendencias[i].codbar === resp.codbar) {
                return;
            }
        }
        estado.pendencias.push({
            codbar: resp.codbar,
            lote: resp.lote,
            regional: resp.regional,
            posto: resp.posto,
            quantidade: resp.quantidade,
            dataexp: resp.data_carga,
            responsavel: document.getElementById('responsavelPadrao').value.trim()
        });
        salvarPendencias();
        renderizarPendencias();
    }

    function adicionarLote(resp) {
        if (existeLote(resp.codbar)) {
            status('Leitura ignorada: código já incluído na lista.', true);
            return;
        }
        if (!estado.postos[resp.posto]) {
            estado.postos[resp.posto] = {
                codigo: resp.posto,
                nome: resp.nome,
                endereco: resp.endereco,
                usuario: resp.responsaveis || '',
                lotes: []
            };
            estado.ordem.push(resp.posto);
            estado.ordem.sort();
        }
        estado.postos[resp.posto].lotes.push({
            codbar: resp.codbar,
            lote: resp.lote,
            quantidade: parseInt(resp.quantidade, 10) || 0,
            data_carga: resp.data_carga,
            responsaveis: resp.responsaveis || '',
            carregado: !!resp.carregado,
            ativo: true
        });
        renderizarPostos();
        if (!resp.carregado) {
            adicionarPendencia(resp);
        }
        status((resp.carregado ? 'Lote carregado incluído: ' : 'Lote pendente incluído: ') + resp.posto + ' / ' + resp.lote, false);
    }

    function lerCodigo(codigo) {
        var limpo = String(codigo || '').replace(/\D+/g, '');
        if (limpo.length < 19) {
            return;
        }
        if (limpo.length > 19) {
            limpo = limpo.substr(0, 19);
        }
        var formData = new FormData();
        formData.append('ajax_resolver_codbar_pt', '1');
        formData.append('codbar', limpo);
        formData.append('data_padrao', document.getElementById('dataPadrao').value || '');
        fetch('gera_oficio_poupa_tempo_dinamico.php', {
            method: 'POST',
            body: formData
        }).then(function(r) { return r.json(); }).then(function(resp) {
            if (!resp.success) {
                status(resp.erro || 'Falha ao resolver leitura.', true);
                return;
            }
            adicionarLote(resp);
        }).catch(function() {
            status('Falha de comunicação com a tela dinâmica.', true);
        });
    }

    function montarPayloadModelo() {
        var postos = [];
        var datas = {};
        var datasLista = [];
        for (var i = 0; i < estado.ordem.length; i++) {
            var codigo = estado.ordem[i];
            var grupo = estado.postos[codigo];
            if (!grupo) continue;
            var lotes = [];
            for (var j = 0; j < grupo.lotes.length; j++) {
                var item = grupo.lotes[j];
                if (!item.ativo) continue;
                lotes.push({
                    lote: item.lote,
                    quantidade: item.quantidade,
                    data_carga: item.data_carga,
                    responsaveis: item.responsaveis || document.getElementById('responsavelPadrao').value.trim()
                });
                if (item.data_carga) {
                    datas[item.data_carga] = true;
                }
            }
            if (!lotes.length) continue;
            postos.push({
                codigo: codigo,
                nome: grupo.nome,
                endereco: grupo.endereco,
                usuario: grupo.usuario || document.getElementById('responsavelPadrao').value.trim(),
                lotes: lotes
            });
        }
        for (var dataRef in datas) {
            if (datas.hasOwnProperty(dataRef)) {
                datasLista.push(dataRef);
            }
        }
        return {
            postos: postos,
            datas: datasLista
        };
    }

    function gerarModelo() {
        var payload = montarPayloadModelo();
        if (!payload.postos.length) {
            status('Marque ao menos um lote antes de gerar o modelo.', true);
            return;
        }
        document.getElementById('payloadDinamico').value = JSON.stringify(payload);
        document.getElementById('payloadDatas').value = payload.datas.join(',');
        document.getElementById('formModelo').submit();
    }

    function salvarPendenciasNoBanco() {
        if (!estado.pendencias.length) {
            status('Não há pendências para salvar.', true);
            return;
        }
        var formData = new FormData();
        formData.append('inserir_pendencias_pt', '1');
        formData.append('pacotes', JSON.stringify(estado.pendencias));
        formData.append('usuario', document.getElementById('responsavelPadrao').value.trim());
        formData.append('autor_salvamento', document.getElementById('responsavelPadrao').value.trim());
        formData.append('criado_salvamento', document.getElementById('criadoPendencia').value || '');
        formData.append('turno_salvamento', document.getElementById('turnoPendencia').value || 'Manhã');
        if (document.getElementById('consolidarPendencia').checked) {
            formData.append('consolidar_salvamento', '1');
        }
        fetch('gera_oficio_poupa_tempo_dinamico.php', {
            method: 'POST',
            body: formData
        }).then(function(r) { return r.json(); }).then(function(resp) {
            if (!resp.success) {
                status(resp.erro || 'Falha ao salvar pendências.', true);
                return;
            }
            estado.pendencias = [];
            salvarPendencias();
            renderizarPendencias();
            status('Pendências gravadas. ciPostosCsv: ' + (resp.salvos_csv || 0) + ' / ciPostos: ' + (resp.salvos_postos || 0), false);
        }).catch(function() {
            status('Erro ao salvar pendências.', true);
        });
    }

    function limparTudo() {
        estado.postos = {};
        estado.ordem = [];
        renderizarPostos();
        status('Tela limpa.', false);
    }

    document.getElementById('btnGerarModelo').addEventListener('click', gerarModelo);
    document.getElementById('btnSalvarPendencias').addEventListener('click', salvarPendenciasNoBanco);
    document.getElementById('btnLimparPendencias').addEventListener('click', function() {
        estado.pendencias = [];
        salvarPendencias();
        renderizarPendencias();
        status('Pendências removidas.', false);
    });
    document.getElementById('btnLimparTudo').addEventListener('click', limparTudo);
    document.getElementById('btnFocarLeitura').addEventListener('click', function() {
        document.getElementById('inputCodbar').focus();
    });
    document.getElementById('inputCodbar').addEventListener('input', function() {
        var limpo = this.value.replace(/\D+/g, '');
        if (limpo.length >= 19) {
            this.value = '';
            lerCodigo(limpo);
        }
    });
    document.getElementById('inputCodbar').addEventListener('keydown', function(ev) {
        if (ev.key === 'Enter') {
            ev.preventDefault();
            var valor = this.value;
            this.value = '';
            lerCodigo(valor);
        }
    });
    document.addEventListener('keydown', function() {
        var campo = document.getElementById('inputCodbar');
        if (document.activeElement !== campo) {
            campo.focus();
        }
    });

    document.getElementById('criadoPendencia').value = agoraLocal();
    carregarPendencias();
    renderizarPendencias();
    renderizarPostos();
    document.getElementById('inputCodbar').focus();
    </script>
</body>
</html>