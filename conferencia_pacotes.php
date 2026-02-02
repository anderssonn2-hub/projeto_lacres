<?php
/* conferencia_pacotes.php — v9.23.0
 * CHANGELOG v9.23.0:
 * - [NOVO] Usuário obrigatório para iniciar conferência
 * - [NOVO] Card Status de Conferências (últimas/pendentes)
 * - [CORRIGIDO] Salvamento de dataexp na conferência
 *
 * CHANGELOG v9.22.9:
 * - [CORRIGIDO] Inputs do filtro visíveis
 * - [MELHORADO] PT segregado por posto (concluido por grupo)
 *
 * CHANGELOG v9.22.8:
 * - [NOVO] Filtro por intervalo + datas avulsas
 * - [NOVO] Cards de resumo (carteiras, conferidas, postos)
 * - [NOVO] Lista das últimas conferências
 * - [MELHORADO] Scroll central e pulsação da última leitura
 * - [MELHORADO] Desbloqueio de áudio para beep
 *
 * CHANGELOG v9.22.7:
 * - [NOVO] Fila de áudio sem sobreposição
 * - [NOVO] beep.mp3 em toda leitura válida de código
 * - [NOVO] concluido.mp3 ao finalizar grupo (mesmo com 1 pacote)
 * - [NOVO] pacotedeoutraregional.mp3 para regional diferente
 * - [NOVO] posto_poupatempo.mp3 para PT no meio de correios (e PT único)
 * 
 * LÓGICA INTELIGENTE DE SONS BASEADA NO CONTEXTO:
 * - beep.mp3: toda leitura válida de código de barras
 * - posto_poupatempo.mp3: PT aparece enquanto confere correios (misturado!)
 * - pacotedeoutraregional.mp3: Regional diferente OU correios no meio do PT
 * - pacotejaconferido.mp3: Pacote já conferido
 * - concluido.mp3: Grupo completo conferido (PT/Capital/R01/999/regionais)
 * 
 * Agrupamento (fonte: ciRegionais):
 * 1. Postos do Poupa Tempo - UMA tabela (ciRegionais.entrega)
 * 2. Postos do Posto 01 - UMA tabela (ciRegionais.regional = 1, exceto PT)
 * 3. Postos da Capital - UMA tabela (ciRegionais.regional = 0)
 * 4. Postos da Central IIPR - UMA tabela (ciRegionais.regional = 999)
 * 5. Regionais - ordem crescente (100, 105, 200...)
 */

// Inicializa variáveis
$total_codigos = 0;
$datas_expedicao = array();
$regionais_data = array();
$data_ini = isset($_GET['data_ini']) ? trim($_GET['data_ini']) : '';
$data_fim = isset($_GET['data_fim']) ? trim($_GET['data_fim']) : '';
$datas_avulsas = isset($_GET['datas_avulsas']) ? trim($_GET['datas_avulsas']) : '';
$datas_sql = array();
$datas_exib = array();
$poupaTempoPostos = array();
$conferencias = array();
$dias_com_conferencia = array();
$dias_sem_conferencia = array();
$metadados_dias = array();

function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

// Conexão
$host = '10.15.61.169';
$dbname = 'controle';
$user = 'controle_mat';
$pass = '375256';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Handler AJAX salvar
    if (isset($_POST['salvar_lote_ajax'])) {
        $lote = trim($_POST['lote']);
        $regional = trim($_POST['regional']);
        $posto = trim($_POST['posto']);
        $dataexp = trim($_POST['dataexp']);
        $qtd = (int)$_POST['qtd'];
        $codbar = trim($_POST['codbar']);
        $usuario_conf = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';

        if ($dataexp === '') {
            $dataexp = date('d-m-Y');
        }
        if ($usuario_conf === '') {
            die(json_encode(array('success' => false, 'erro' => 'Usuario obrigatorio')));
        }
        
        $sql = "INSERT INTO conferencia_pacotes (regional, nlote, nposto, dataexp, qtd, codbar, conf, usuario) 
                VALUES (?, ?, ?, ?, ?, ?, 's', ?)
                ON DUPLICATE KEY UPDATE conf='s', qtd=VALUES(qtd), codbar=VALUES(codbar), dataexp=VALUES(dataexp), usuario=VALUES(usuario)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($regional, $lote, $posto, $dataexp, $qtd, $codbar, $usuario_conf));
        $stmt = null; // v8.17.4: Libera statement
        $pdo = null;  // v8.17.4: Fecha conexão
        die(json_encode(array('success' => true)));
    }

    // Handler AJAX excluir
    if (isset($_POST['excluir_lote_ajax'])) {
        $lote = trim($_POST['lote']);
        $regional = trim($_POST['regional']);
        $posto = trim($_POST['posto']);
        
        $sql = "DELETE FROM conferencia_pacotes WHERE nlote = ? AND regional = ? AND nposto = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($lote, $regional, $posto));
        $stmt = null; // v8.17.4: Libera statement
        $pdo = null;  // v8.17.4: Fecha conexão
        die(json_encode(array('success' => true)));
    }

    // v9.0: Busca REGIONAL e ENTREGA de ciRegionais (fonte da verdade)
    $postosInfo = array(); // posto => array('regional' => X, 'entrega' => 'poupatempo'/'correios'/null)
    $sql = "SELECT LPAD(posto,3,'0') AS posto, 
                   CAST(regional AS UNSIGNED) AS regional,
                   LOWER(TRIM(REPLACE(entrega,' ',''))) AS entrega
            FROM ciRegionais 
            LIMIT 1000";
    $stmt = $pdo->query($sql);
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $posto_pad = $r['posto'];
        $regional_real = (int)$r['regional'];
        $entrega_tipo = null;
        
        if (!empty($r['entrega'])) {
            $entrega_limpo = $r['entrega'];
            if (strpos($entrega_limpo, 'poupa') !== false || strpos($entrega_limpo, 'tempo') !== false) {
                $entrega_tipo = 'poupatempo';
            } elseif (strpos($entrega_limpo, 'correio') !== false) {
                $entrega_tipo = 'correios';
            }
        }
        
        $postosInfo[$posto_pad] = array(
            'regional' => $regional_real,
            'entrega' => $entrega_tipo
        );
    }

    // Busca conferências já realizadas (com LIMIT)
    $stmt = $pdo->query("SELECT nlote, regional, nposto FROM conferencia_pacotes WHERE conf='s' LIMIT 5000");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $key = $row['nlote'] . '|' . str_pad($row['regional'], 3, '0', STR_PAD_LEFT) . '|' . str_pad($row['nposto'], 3, '0', STR_PAD_LEFT);
        $conferencias[$key] = 1;
    }

    // v9.22.8: Normalizar datas (intervalo + avulsas)
    function normalizarDataSql($d) {
        $d = trim($d);
        if ($d === '') return '';
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $d, $m)) {
            return $m[3] . '-' . $m[2] . '-' . $m[1];
        }
        if (preg_match('/^(\d{2})\-(\d{2})\-(\d{4})$/', $d, $m)) {
            return $m[3] . '-' . $m[2] . '-' . $m[1];
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) {
            return $d;
        }
        return '';
    }

    function normalizarDataExib($d) {
        $d = trim($d);
        if ($d === '') return '';
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $d, $m)) {
            return $m[3] . '-' . $m[2] . '-' . $m[1];
        }
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $d, $m)) {
            return $m[1] . '-' . $m[2] . '-' . $m[3];
        }
        if (preg_match('/^(\d{2})\-(\d{2})\-(\d{4})$/', $d)) {
            return $d;
        }
        return '';
    }

    $data_ini_sql = normalizarDataSql($data_ini);
    $data_fim_sql = normalizarDataSql($data_fim);

    if ($data_ini_sql !== '' && $data_fim_sql === '') {
        $data_fim_sql = $data_ini_sql;
    }

    if ($data_ini_sql === '' && $data_fim_sql !== '') {
        $data_ini_sql = $data_fim_sql;
    }

    if (!empty($datas_avulsas)) {
        $partes = preg_split('/[\s,;]+/', $datas_avulsas);
        foreach ($partes as $p) {
            $ds = normalizarDataSql($p);
            if ($ds !== '') {
                $datas_sql[] = $ds;
            }
        }
    }

    // v8.17.2: Se nenhum filtro, carrega APENAS última data (rápido)
    if ($data_ini_sql === '' && $data_fim_sql === '' && empty($datas_sql)) {
        $stmt = $pdo->query("SELECT DISTINCT DATE_FORMAT(dataCarga, '%Y-%m-%d') as data 
                             FROM ciPostosCsv 
                             WHERE dataCarga IS NOT NULL 
                             ORDER BY dataCarga DESC 
                             LIMIT 1");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && !empty($row['data'])) {
            $data_ini_sql = $row['data'];
            $data_fim_sql = $row['data'];
            if ($data_ini === '') {
                $data_ini = $row['data'];
            }
            if ($data_fim === '') {
                $data_fim = $row['data'];
            }
        }
    }

    // Busca últimas 5 datas para seletor
    $stmt = $pdo->query("SELECT DISTINCT DATE_FORMAT(dataCarga, '%d-%m-%Y') as data 
                         FROM ciPostosCsv 
                         WHERE dataCarga IS NOT NULL 
                         ORDER BY dataCarga DESC 
                         LIMIT 5");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $datas_expedicao[] = $row['data'];
    }

    // Busca dados do ciPostosCsv (com LIMIT)
    $condicoes_data = array();
    $params_data = array();
    if ($data_ini_sql !== '' && $data_fim_sql !== '') {
        $condicoes_data[] = "DATE(dataCarga) BETWEEN ? AND ?";
        $params_data[] = $data_ini_sql;
        $params_data[] = $data_fim_sql;
    }
    if (!empty($datas_sql)) {
        $ph = implode(',', array_fill(0, count($datas_sql), '?'));
        $condicoes_data[] = "DATE(dataCarga) IN ($ph)";
        $params_data = array_merge($params_data, $datas_sql);
    }

    if (!empty($condicoes_data)) {
        $whereData = "WHERE (" . implode(' OR ', $condicoes_data) . ")";
        $sql = "SELECT lote, posto, regional, quantidade, dataCarga 
                FROM ciPostosCsv 
                $whereData
                ORDER BY regional, lote, posto 
                LIMIT 3000";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params_data);

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (empty($row['dataCarga'])) continue;

            $data_formatada = date('d-m-Y', strtotime($row['dataCarga']));

                $lote = $row['lote'];
                $posto = str_pad($row['posto'], 3, '0', STR_PAD_LEFT);
                $regional_csv = (int)$row['regional']; // Regional do CSV (para código de barras)
                $regional_str = str_pad($regional_csv, 3, '0', STR_PAD_LEFT);
                $quantidade = str_pad($row['quantidade'], 5, '0', STR_PAD_LEFT);

                $codigo_barras = $lote . $regional_str . $posto . $quantidade;
                
                // v9.0: Usa informações CORRETAS de ciRegionais
                $regional_real = isset($postosInfo[$posto]) ? $postosInfo[$posto]['regional'] : $regional_csv;
                $tipoEntrega = isset($postosInfo[$posto]) ? $postosInfo[$posto]['entrega'] : null;
                $isPT = ($tipoEntrega == 'poupatempo') ? 1 : 0;
                
                // Verifica se já foi conferido
                $key = $lote . '|' . $regional_str . '|' . $posto;
                $conferido = isset($conferencias[$key]) ? 1 : 0;

                // v9.0: Agrupa por REGIONAL REAL (de ciRegionais)
                if (!isset($regionais_data[$regional_real])) {
                    $regionais_data[$regional_real] = array();
                }

                // v9.3: Poupa Tempo usa próprio posto como regional na exibição
                $regional_exibida = ($isPT == 1) ? $posto : $regional_str;

                $regionais_data[$regional_real][] = array(
                    'lote' => $lote,
                    'posto' => $posto,
                    'regional' => $regional_exibida,
                    'tipoEntrega' => $tipoEntrega,
                    'data' => $data_formatada,
                    'qtd' => ltrim($quantidade, '0'),
                    'codigo' => $codigo_barras,
                    'isPT' => $isPT,
                    'conf' => $conferido
                );

            $total_codigos++;
        }
    }

    sort($datas_expedicao);

    // v9.22.8: Montar datas exibidas para filtros/estatísticas
    if ($data_ini_sql !== '' && $data_fim_sql !== '') {
        try {
            $dtIni = new DateTime($data_ini_sql);
            $dtFim = new DateTime($data_fim_sql);
            while ($dtIni <= $dtFim) {
                $datas_exib[] = $dtIni->format('d-m-Y');
                $dtIni->modify('+1 day');
            }
        } catch (Exception $e) {}
    }
    if (!empty($datas_sql)) {
        foreach ($datas_sql as $ds) {
            $datas_exib[] = normalizarDataExib($ds);
        }
    }
    $datas_exib = array_values(array_unique(array_filter($datas_exib)));

    // v9.22.8: Estatísticas
    $stats = array(
        'carteiras_emitidas' => 0,
        'carteiras_conferidas' => 0,
        'postos_conferidos' => 0,
        'pacotes_conferidos' => 0
    );

    // v9.23.0: Status de conferências (últimos 30 dias)
    try {
        $stmt_conferidos = $pdo->query("
            SELECT DISTINCT 
                DATE(dataCarga) as data,
                DAYOFWEEK(dataCarga) as dia_semana
            FROM ciPostosCsv 
            WHERE dataCarga >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ORDER BY data DESC
            LIMIT 15
        ");
        $dias_com_producao = array();
        while ($row = $stmt_conferidos->fetch(PDO::FETCH_ASSOC)) {
            $data_fmt = date('d/m/Y', strtotime($row['data']));
            $dias_com_producao[] = $data_fmt;

            $dia_num = (int)$row['dia_semana'];
            $label = '';
            if ($dia_num == 6) $label = 'SEX';
            elseif ($dia_num == 7) $label = 'SÁB';
            elseif ($dia_num == 1) $label = 'DOM';

            $metadados_dias[$data_fmt] = array(
                'dia_semana_num' => $dia_num,
                'label' => $label
            );
        }

        try {
            $stmt_conf = $pdo->query("
                SELECT DISTINCT DATE(csv.dataCarga) as data
                FROM ciPostosCsv csv
                INNER JOIN conferencia_pacotes cp ON csv.lote = cp.nlote
                WHERE csv.dataCarga >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                  AND cp.conf = 's'
                ORDER BY data DESC
            ");
            while ($row = $stmt_conf->fetch(PDO::FETCH_ASSOC)) {
                $dias_com_conferencia[] = date('d/m/Y', strtotime($row['data']));
            }
        } catch (Exception $e) {
            $dias_com_conferencia = array();
        }

        $dias_sem_conferencia = array_diff($dias_com_producao, $dias_com_conferencia);
        $dias_sem_conferencia = array_values($dias_sem_conferencia);
        $dias_sem_conferencia = array_slice($dias_sem_conferencia, 0, 10);
    } catch (Exception $e) {
        // ignore
    }

    if (!empty($condicoes_data)) {
        $sqlEmitidas = "SELECT COALESCE(SUM(quantidade),0) AS total FROM ciPostosCsv $whereData";
        $stmtEmit = $pdo->prepare($sqlEmitidas);
        $stmtEmit->execute($params_data);
        $stats['carteiras_emitidas'] = (int)$stmtEmit->fetchColumn();
    }

    if (!empty($datas_exib)) {
        $phEx = implode(',', array_fill(0, count($datas_exib), '?'));
        $sqlConf = "SELECT 
                        COALESCE(SUM(qtd),0) AS total_qtd,
                        COUNT(*) AS total_pacotes,
                        COUNT(DISTINCT nposto) AS total_postos
                    FROM conferencia_pacotes
                    WHERE conf='s' AND dataexp IN ($phEx)";
        $stmtConf = $pdo->prepare($sqlConf);
        $stmtConf->execute($datas_exib);
        $rowConf = $stmtConf->fetch(PDO::FETCH_ASSOC);
        if ($rowConf) {
            $stats['carteiras_conferidas'] = (int)$rowConf['total_qtd'];
            $stats['pacotes_conferidos'] = (int)$rowConf['total_pacotes'];
            $stats['postos_conferidos'] = (int)$rowConf['total_postos'];
        }
    }

    // v9.22.8: Últimas conferências
    $ultimas_conferencias = array();
    try {
        if (!empty($datas_exib)) {
            $phEx2 = implode(',', array_fill(0, count($datas_exib), '?'));
            $sqlUlt = "SELECT nlote, regional, nposto, dataexp, qtd, codbar 
                       FROM conferencia_pacotes 
                       WHERE conf='s' AND dataexp IN ($phEx2)
                       ORDER BY dataexp DESC, nlote DESC 
                       LIMIT 5";
            $stmtUlt = $pdo->prepare($sqlUlt);
            $stmtUlt->execute($datas_exib);
            $ultimas_conferencias = $stmtUlt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Exception $ex) {
        $ultimas_conferencias = array();
    }
} catch (PDOException $e) {
    echo "Erro ao conectar ao banco de dados: " . $e->getMessage();
    die();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Conferência de Pacotes v9.22.7</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        h2 { color: #333; border-bottom: 3px solid #007bff; padding-bottom: 10px; margin-bottom: 20px; }
        h3 { 
            color: #555; 
            margin: 30px 0 10px; 
            padding-left: 10px; 
            border-left: 4px solid #007bff; 
        }
        
        .radio-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 6px;
        }
        .radio-box label {
            color: white;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
        }
        .radio-box input { margin-right: 10px; width: 18px; height: 18px; cursor: pointer; }
        
        .filtro-datas { 
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .filtro-datas form { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
        .filtro-datas label { margin-right: 10px; cursor: pointer; }
        .filtro-row { display:flex; flex-wrap:wrap; gap:10px; align-items:center; width:100%; }
        .filtro-datas input[type="date"],
        .filtro-datas input[type="text"] {
            padding: 8px 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            min-width: 180px;
            background: #fff;
        }
        .filtro-datas input[type="submit"] { padding: 8px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .filtro-datas input[type="submit"]:hover { background: #0056b3; }
        
        .info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 6px;
            font-weight: 600;
        }
        
        #codigo_barras { 
            padding: 12px; 
            font-size: 16px; 
            width: 100%;
            max-width: 400px;
            border: 2px solid #007bff; 
            border-radius: 4px;
            margin: 10px 0;
        }
        
        #resetar {
            padding: 10px 20px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            margin-left: 10px;
        }
        #resetar:hover { background-color: #c82333; }
        
        table { 
            border-collapse: collapse; 
            width: 100%; 
            margin-top: 15px;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        thead { background: #343a40; color: white; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        tbody tr { cursor: pointer; transition: background 0.2s; }
        tbody tr:hover { background: #f8f9fa; }
        tbody tr.confirmado { background-color: #d4edda !important; font-weight: 500; }
        tbody tr.ultimo-lido { animation: pulse 1.2s ease-in-out infinite; }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(0,123,255,0.6); }
            70% { box-shadow: 0 0 0 10px rgba(0,123,255,0); }
            100% { box-shadow: 0 0 0 0 rgba(0,123,255,0); }
        }
        
        .tag-pt {
            background: #e74c3c;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 700;
            margin-left: 8px;
        }
        .versao {
            position: fixed;
            top: 10px;
            right: 10px;
            background: #28a745;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        .cards-resumo {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 12px;
            margin: 15px 0 10px;
        }
        .card-resumo {
            background: #fff;
            border-radius: 8px;
            padding: 14px 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-left: 4px solid #007bff;
        }
        .card-resumo h4 { margin: 0; font-size: 12px; color: #555; text-transform: uppercase; }
        .card-resumo .valor { font-size: 20px; font-weight: 700; color: #007bff; margin-top: 6px; }
        .painel-ultimas {
            background: #fff;
            border-radius: 8px;
            padding: 12px 16px;
            margin-top: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .painel-ultimas ul { margin: 8px 0 0; padding-left: 18px; }
        .btn-toggle {
            padding: 8px 14px;
            background: #17a2b8;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            margin-top: 6px;
        }
        #indicador-dias {
            position: fixed;
            top: 70px;
            right: 20px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 12px 14px;
            width: 260px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            z-index: 1000;
        }
        #indicador-dias.collapsed .indicador-conteudo { display: none; }
        .badge-data { display:inline-block; padding:4px 8px; border-radius:6px; font-size:11px; margin:2px 4px 2px 0; }
        .badge-data.conferida { background:#28a745; color:#fff; }
        .badge-data.pendente { background:#ffc107; color:#333; font-weight:bold; }
        .indicador-toggle { cursor:pointer; float:right; }
    </style>
</head>
<body>
<div class="versao">v9.22.9</div>

<h2>📋 Conferência de Pacotes v9.23.0</h2>

<!-- Radio Auto-Save -->
<div class="radio-box">
    <label>
        <input type="radio" id="autoSalvar" checked>
        Auto-salvar conferências durante leitura
    </label>
</div>

<div class="radio-box" style="margin-top:10px;">
    <label style="gap:10px;">
        👤 Usuário da conferência:
        <input type="text" id="usuario_conf" placeholder="Digite o usuário" style="padding:6px 8px; border-radius:4px; border:1px solid #ccc;">
    </label>
</div>

<!-- Filtro de datas -->
<div class="filtro-datas">
    <form method="get" action="">
        <strong>📅 Filtrar por intervalo:</strong>
        <div class="filtro-row">
            <input type="date" name="data_ini" value="<?php echo e($data_ini); ?>">
            <input type="date" name="data_fim" value="<?php echo e($data_fim); ?>">
            <input type="submit" value="🔍 Aplicar Filtro">
        </div>
        <label style="min-width:100%;">
            Datas avulsas (dd-mm-aaaa ou yyyy-mm-dd, separadas por vírgula):
            <input type="text" name="datas_avulsas" value="<?php echo e($datas_avulsas); ?>" style="width:100%; margin-top:4px;">
        </label>
    </form>
</div>

<div id="indicador-dias">
    <div style="font-weight:bold;color:#333;font-size:13px;">
        📅 Status de Conferências
        <span class="indicador-toggle" onclick="toggleIndicadorDias()" title="Recolher/Expandir">▼</span>
    </div>
    <div class="indicador-conteudo">
        <div style="margin:10px 0;">
            <strong style="color:#28a745;font-size:12px;">✓ Últimas Conferências:</strong><br>
            <div style="margin-top:5px;">
                <?php 
                $ultimas_cinco = array_slice($dias_com_conferencia, 0, 5);
                if (!empty($ultimas_cinco)) {
                    foreach ($ultimas_cinco as $data) {
                        $label_dia = isset($metadados_dias[$data]) ? $metadados_dias[$data]['label'] : '';
                        $badge_label = !empty($label_dia) ? " <small style='font-size:9px;background:#6c757d;color:white;padding:1px 3px;border-radius:2px;'>$label_dia</small>" : '';
                        echo '<span class="badge-data conferida">' . htmlspecialchars($data) . $badge_label . '</span>';
                    }
                } else {
                    echo '<span style="color:#999;font-size:11px;">Nenhuma</span>';
                }
                ?>
            </div>
        </div>
        <div style="margin:10px 0;">
            <strong style="color:#ffc107;font-size:12px;">⚠ Conferências Pendentes:</strong><br>
            <div style="margin-top:5px;">
                <?php 
                $ultimas_pendentes = array_slice($dias_sem_conferencia, 0, 5);
                if (!empty($ultimas_pendentes)) {
                    foreach ($ultimas_pendentes as $data) {
                        $label_dia = isset($metadados_dias[$data]) ? $metadados_dias[$data]['label'] : '';
                        $badge_label = '';
                        if ($label_dia == 'SEX') {
                            $badge_label = " <small style='font-size:9px;background:#ffc107;color:#333;padding:1px 3px;border-radius:2px;font-weight:bold;'>SEX</small>";
                        } elseif ($label_dia == 'SÁB') {
                            $badge_label = " <small style='font-size:9px;background:#17a2b8;color:white;padding:1px 3px;border-radius:2px;font-weight:bold;'>SÁB</small>";
                        } elseif ($label_dia == 'DOM') {
                            $badge_label = " <small style='font-size:9px;background:#dc3545;color:white;padding:1px 3px;border-radius:2px;font-weight:bold;'>DOM</small>";
                        }
                        echo '<span class="badge-data pendente">' . htmlspecialchars($data) . $badge_label . '</span>';
                    }
                } else {
                    echo '<span style="color:#999;font-size:11px;">Nenhuma</span>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<button class="btn-toggle" type="button" onclick="var el=document.getElementById('painel-estatisticas'); el.style.display = (el.style.display==='none'?'block':'none');">
    📊 Mostrar/Ocultar Estatísticas
</button>

<div id="painel-estatisticas" style="display:block;">
    <div class="cards-resumo">
        <div class="card-resumo">
            <h4>Pacotes na tela</h4>
            <div class="valor"><?php echo (int)$total_codigos; ?></div>
        </div>
        <div class="card-resumo">
            <h4>Carteiras emitidas</h4>
            <div class="valor"><?php echo number_format((int)$stats['carteiras_emitidas'], 0, ',', '.'); ?></div>
        </div>
        <div class="card-resumo">
            <h4>Carteiras conferidas</h4>
            <div class="valor"><?php echo number_format((int)$stats['carteiras_conferidas'], 0, ',', '.'); ?></div>
        </div>
        <div class="card-resumo">
            <h4>Postos com retirada</h4>
            <div class="valor"><?php echo (int)$stats['postos_conferidos']; ?></div>
        </div>
        <div class="card-resumo">
            <h4>Pacotes conferidos</h4>
            <div class="valor"><?php echo (int)$stats['pacotes_conferidos']; ?></div>
        </div>
    </div>

    <div class="painel-ultimas">
        <strong>🕒 Últimas conferências</strong>
        <?php if (!empty($ultimas_conferencias)): ?>
            <ul>
                <?php foreach ($ultimas_conferencias as $u): ?>
                <li>
                    Lote <?php echo e($u['nlote']); ?> | Posto <?php echo e(str_pad($u['nposto'],3,'0',STR_PAD_LEFT)); ?> | Reg. <?php echo e(str_pad($u['regional'],3,'0',STR_PAD_LEFT)); ?> | <?php echo e($u['dataexp']); ?> | Qtd <?php echo e($u['qtd']); ?>
                </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <div style="margin-top:6px; color:#666;">Sem conferências recentes para o filtro atual.</div>
        <?php endif; ?>
    </div>
</div>

<div>
    <input type="text" id="codigo_barras" placeholder="Escaneie o código de barras (19 dígitos)" maxlength="19" autofocus>
    <button id="resetar">🔄 Resetar Conferência</button>
</div>

<!-- Tabelas Agrupadas -->
<div id="tabelas">
<?php
// ========================================
// v9.0: AGRUPAMENTO USANDO DADOS DE ciRegionais
// Classificação baseada em regional e entrega REAIS
// ========================================


$grupo_pt = array();           // Poupa Tempo agrupado por posto
$grupo_r01 = array();          // Todos postos Regional 01 em UMA lista (excluindo PT)
$grupo_capital = array();      // Todos postos Capital em UMA lista
$grupo_999 = array();          // Todos postos Central IIPR em UMA lista
$grupo_outros = array();       // Regionais: array($regional => array de postos)

foreach ($regionais_data as $regional => $postos) {
    foreach ($postos as $posto) {
        // 1. Poupa Tempo (PRIORIDADE MÁXIMA - ex: posto 28, 80)
        if ($posto['tipoEntrega'] == 'poupatempo') {
            $postoKey = $posto['posto'];
            if (!isset($grupo_pt[$postoKey])) {
                $grupo_pt[$postoKey] = array();
            }
            $grupo_pt[$postoKey][] = $posto; // Agrupa por posto
            continue; // v8.17.5: NÃO classifica em outros grupos
        }
        // 2. Regional 01 (postos 01, 02, 27 - excluindo os que já foram para PT)
        if ($regional == 1) {
            $grupo_r01[] = $posto; // Adiciona direto na lista
            continue;
        }
        // 3. Capital (regional = 0)
        if ($regional == 0) {
            $grupo_capital[] = $posto; // Adiciona direto na lista
            continue;
        }
        // 4. Central IIPR (regional = 999)
        if ($regional == 999) {
            $grupo_999[] = $posto; // Adiciona direto na lista
            continue;
        }
        // 5. Demais regionais (serão ordenadas crescentemente)
        if (!isset($grupo_outros[$regional])) {
            $grupo_outros[$regional] = array();
        }
        $grupo_outros[$regional][] = $posto;
    }
}

// v8.17.5: Ordena demais regionais em ordem crescente
ksort($grupo_outros);

// v8.17.5: Função para renderizar tabela (aceita array plano OU aninhado)
function renderizarTabela($titulo, $dados, $ehPoupaTempo = false, $ptGroup = '') {
    if (empty($dados)) {
        return;
    }
    
    // Verifica se é array plano (lista de postos) ou aninhado (regional => postos)
    $primeiro = reset($dados);
    $eh_array_plano = isset($primeiro['lote']); // Se tem 'lote', é um posto
    
    // Normaliza para formato de iteração
    $postos_para_exibir = array();
    if ($eh_array_plano) {
        // Array plano: já é lista de postos
        $postos_para_exibir = $dados;
    } else {
        // Array aninhado: achatar
        foreach ($dados as $regional => $postos) {
            foreach ($postos as $posto) {
                $postos_para_exibir[] = $posto;
            }
        }
    }
    
    // Conta total de pacotes e conferidos
    $total_pacotes = count($postos_para_exibir);
    $total_conferidos = 0;
    foreach ($postos_para_exibir as $posto) {
        if ($posto['conf'] == 1) {
            $total_conferidos++;
        }
    }
    
    echo '<h3>' . htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8');
    echo ' <span style="color:#666; font-weight:normal; font-size:14px;">(' . $total_pacotes . ' pacotes / ' . $total_conferidos . ' conferidos)</span>';
    if ($ehPoupaTempo) {
        echo ' <span class="tag-pt">POUPA TEMPO</span>';
    }
    echo '</h3>';
    echo '<table>';
    echo '<thead><tr>';
    echo '<th>Regional</th>';
    echo '<th>Lote</th>';
    echo '<th>Posto</th>';
    echo '<th>Data Expedição</th>';
    echo '<th>Quantidade</th>';
    echo '<th>Código de Barras</th>';
    echo '</tr></thead>';
    echo '<tbody>';
    
    foreach ($postos_para_exibir as $posto) {
        $classeConf = ($posto['conf'] == 1) ? ' confirmado' : '';
        echo '<tr class="linha-conferencia' . $classeConf . '" ';
        echo 'data-codigo="' . htmlspecialchars($posto['codigo'], ENT_QUOTES, 'UTF-8') . '" ';
        echo 'data-regional="' . htmlspecialchars($posto['regional'], ENT_QUOTES, 'UTF-8') . '" ';
        echo 'data-lote="' . htmlspecialchars($posto['lote'], ENT_QUOTES, 'UTF-8') . '" ';
        echo 'data-posto="' . htmlspecialchars($posto['posto'], ENT_QUOTES, 'UTF-8') . '" ';
        echo 'data-data="' . htmlspecialchars($posto['data'], ENT_QUOTES, 'UTF-8') . '" ';
        echo 'data-qtd="' . htmlspecialchars($posto['qtd'], ENT_QUOTES, 'UTF-8') . '" ';
        echo 'data-ispt="' . $posto['isPT'] . '" ';
        echo 'data-pt-group="' . htmlspecialchars($ptGroup, ENT_QUOTES, 'UTF-8') . '">';
        echo '<td>' . htmlspecialchars($posto['regional'], ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars($posto['lote'], ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars($posto['posto'], ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars($posto['data'], ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars($posto['qtd'], ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars($posto['codigo'], ENT_QUOTES, 'UTF-8') . '</td>';
        echo '</tr>';
    }
    
    echo '</tbody></table>';
}

// v8.17.4: Renderizar na ordem correta (cada grupo = UMA tabela)
if (!empty($grupo_pt)) {
    ksort($grupo_pt);
    foreach ($grupo_pt as $postoKey => $postosPt) {
        renderizarTabela('Posto ' . $postoKey . ' - Poupa Tempo', $postosPt, true, $postoKey);
    }
}
if (!empty($grupo_r01)) {
    renderizarTabela('Postos do Posto 01', $grupo_r01);
}
if (!empty($grupo_capital)) {
    renderizarTabela('Postos da Capital', $grupo_capital);
}
if (!empty($grupo_999)) {
    renderizarTabela('Postos da Central IIPR', $grupo_999);
}
// v8.17.5: Demais regionais já ordenadas (ksort aplicado na linha 367)
if (!empty($grupo_outros)) {
    foreach ($grupo_outros as $regional => $postos) {
        $regionalStr = str_pad($regional, 3, '0', STR_PAD_LEFT);
        renderizarTabela($regionalStr . ' - Regional ' . $regionalStr, array($regional => $postos));
    }
}

if (empty($regionais_data)) {
    echo '<p style="text-align:center; margin-top:40px; color:#999;">Nenhum dado encontrado para as datas selecionadas.</p>';
}
?>

<!-- Áudios -->
<audio id="beep" src="beep.mp3" preload="auto"></audio>
<audio id="concluido" src="concluido.mp3" preload="auto"></audio>
<audio id="pacotejaconferido" src="pacotejaconferido.mp3" preload="auto"></audio>
<audio id="pacotedeoutraregional" src="pacotedeoutraregional.mp3" preload="auto"></audio>
<audio id="posto_poupatempo" src="posto_poupatempo.mp3" preload="auto"></audio>

<script>
// ========================================
// v9.22.7: JavaScript com fila de sons sem sobreposição
// ========================================

function substituirMultiplosPadroes(inputString) {
    var stringProcessada = inputString;
    
    // Regra 1: Substituir "755" por "779" quando seguido por 5 dígitos
    var regex755 = /(\d{11})(755)(\d{5})/g;
    if (regex755.test(stringProcessada)) {
        stringProcessada = stringProcessada.replace(regex755, function(match, p1, p2) {
            return "779" + p2;
        });
    }
    
    // Regra 2: Substituir "500" por "507" quando seguido por 5 dígitos
    var regex500 = /(\d{11})(500)(\d{5})/g;
    if (regex500.test(stringProcessada)) {
        stringProcessada = stringProcessada.replace(regex500, function(match, p1, p2) {
            return "507" + p2;
        });
    }
    
    return stringProcessada;
}

document.addEventListener("DOMContentLoaded", function() {
    var input = document.getElementById("codigo_barras");
    var radioAutoSalvar = document.getElementById("autoSalvar");
    var beep = document.getElementById("beep");
    var concluido = document.getElementById("concluido");
    var pacoteJaConferido = document.getElementById("pacotejaconferido");
    var pacoteOutraRegional = document.getElementById("pacotedeoutraregional");
    var postoPoupaTempo = document.getElementById("posto_poupatempo");
    var btnResetar = document.getElementById("resetar");
    var usuarioInput = document.getElementById("usuario_conf");
    var audioDesbloqueado = false;

    // v9.22.7: Fila de áudio para evitar sobreposição
    var filaSons = [];
    var tocando = false;

    function tocarProximoSom() {
        if (filaSons.length === 0) {
            tocando = false;
            return;
        }
        tocando = true;
        var som = filaSons.shift();
        try {
            som.currentTime = 0;
            var playPromise = som.play();
            if (playPromise && playPromise.then) {
                playPromise.catch(function() {
                    tocando = false;
                    tocarProximoSom();
                });
            }
        } catch (e) {
            tocando = false;
            tocarProximoSom();
        }
    }

    function enfileirarSom(som) {
        if (!som) return;
        filaSons.push(som);
        if (!tocando) {
            tocarProximoSom();
        }
    }

    // Encadeia para tocar o próximo som quando o atual terminar
    var listaSons = [beep, concluido, pacoteJaConferido, pacoteOutraRegional, postoPoupaTempo];
    for (var si = 0; si < listaSons.length; si++) {
        listaSons[si].addEventListener('ended', function() {
            tocarProximoSom();
        });
    }

    function desbloquearAudio() {
        if (audioDesbloqueado) return;
        audioDesbloqueado = true;
        for (var i = 0; i < listaSons.length; i++) {
            try {
                listaSons[i].volume = 0;
                var p = listaSons[i].play();
                if (p && p.then) {
                    p.then(function() {
                        for (var j = 0; j < listaSons.length; j++) {
                            listaSons[j].pause();
                            listaSons[j].currentTime = 0;
                            listaSons[j].volume = 1;
                        }
                    }).catch(function() {
                        for (var k = 0; k < listaSons.length; k++) {
                            listaSons[k].volume = 1;
                        }
                    });
                }
            } catch (e) {
                for (var k2 = 0; k2 < listaSons.length; k2++) {
                    listaSons[k2].volume = 1;
                }
            }
        }
    }

    input.addEventListener('focus', desbloquearAudio);
    input.addEventListener('click', desbloquearAudio);
    document.addEventListener('keydown', desbloquearAudio, { once: true });
    
    // v9.2: Variáveis de contexto para sons inteligentes
    var regionalAtual = null;
    var tipoAtual = null; // 'poupatempo' ou 'correios'
    var primeiroConferido = false;
    
    input.focus();
    
    // Função para salvar conferência via AJAX
    function salvarConferencia(lote, regional, posto, dataexp, qtd, codbar, usuario) {
        var formData = new FormData();
        formData.append('salvar_lote_ajax', '1');
        formData.append('lote', lote);
        formData.append('regional', regional);
        formData.append('posto', posto);
        formData.append('dataexp', dataexp);
        formData.append('qtd', qtd);
        formData.append('codbar', codbar);
        formData.append('usuario', usuario);
        
        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if (!data.sucesso) {
                console.error('Erro ao salvar:', data.erro);
            }
        })
        .catch(function(error) {
            console.error('Erro AJAX:', error);
        });
    }
    
    // Scanner de código de barras
    input.addEventListener("input", function() {
        var valor = input.value.trim();
        
        if (valor.length !== 19) {
            return;
        }
        
        // Aplicar transformações opcionais
        // valor = substituirMultiplosPadroes(valor);
        
        var linha = document.querySelector('tr[data-codigo="' + valor + '"]');
        
        if (!linha) {
            input.value = "";
            return;
        }

        // v9.23.0: usuário obrigatório
        if (!usuarioInput || usuarioInput.value.trim() === '') {
            alert('Informe o usuário da conferência para iniciar.');
            input.value = "";
            if (usuarioInput) { usuarioInput.focus(); }
            return;
        }
        
        var regionalDoPacote = linha.getAttribute("data-regional");
        var isPoupaTempo = linha.getAttribute("data-ispt") === "1";
        var tipoPacote = isPoupaTempo ? 'poupatempo' : 'correios';
        
        // Verifica se já foi conferido
        if (linha.classList.contains("confirmado")) {
            enfileirarSom(beep);
            enfileirarSom(pacoteJaConferido);
            input.value = "";
            return;
        }
        
        // v9.22.7: Lógica inteligente de sons
        var somAlerta = null;
        
        // Caso 1: Primeiro pacote da conferência - sempre beep
        if (!primeiroConferido) {
            regionalAtual = regionalDoPacote;
            tipoAtual = tipoPacote;
            primeiroConferido = true;
        }
        // Caso 2: Pacote Poupa Tempo aparecendo em meio aos Correios
        else if (tipoAtual === 'correios' && tipoPacote === 'poupatempo') {
            somAlerta = postoPoupaTempo; // Alerta: PT misturado com correios!
            // NÃO altera regionalAtual nem tipoAtual - continua conferindo correios
        }
        // Caso 3: Pacote Correios aparecendo em meio ao Poupa Tempo
        else if (tipoAtual === 'poupatempo' && tipoPacote === 'correios') {
            somAlerta = pacoteOutraRegional; // Alerta: correios no meio do PT!
            // NÃO altera regionalAtual nem tipoAtual
        }
        // Caso 4: Regional diferente (mesmo tipo)
        else if (regionalDoPacote !== regionalAtual && tipoPacote === tipoAtual) {
            somAlerta = pacoteOutraRegional; // Alerta: regional diferente!
            // NÃO altera regionalAtual nem tipoAtual
        }

        // PT único: emitir aviso específico mesmo no primeiro pacote
        if (tipoPacote === 'poupatempo') {
            var totalPT = document.querySelectorAll('tbody tr[data-ispt="1"]').length;
            if (totalPT === 1 && !somAlerta) {
                somAlerta = postoPoupaTempo;
            }
        }
        
        // Marca como conferido
        linha.classList.add("confirmado");
        
        // Toca os sons: beep sempre na leitura válida, alerta se necessário
        enfileirarSom(beep);
        if (somAlerta) {
            enfileirarSom(somAlerta);
        }
        
        input.value = "";
        
        // Centraliza a linha na tela e destaca última leitura
        var ultimas = document.querySelectorAll('tr.ultimo-lido');
        for (var u = 0; u < ultimas.length; u++) {
            ultimas[u].classList.remove('ultimo-lido');
        }
        linha.classList.add('ultimo-lido');

        var rect = linha.getBoundingClientRect();
        var alvo = rect.top + window.pageYOffset - (window.innerHeight / 2) + (rect.height / 2);
        window.scrollTo({ top: alvo, behavior: 'smooth' });
        
        // Salvar no banco se auto-save estiver ativo
        if (radioAutoSalvar.checked) {
            var lote = linha.getAttribute("data-lote");
            var regional = linha.getAttribute("data-regional");
            var posto = linha.getAttribute("data-posto");
            var dataexp = linha.getAttribute("data-data");
            var qtd = linha.getAttribute("data-qtd");
            var codbar = linha.getAttribute("data-codigo");
            var usuario = usuarioInput.value.trim();
            salvarConferencia(lote, regional, posto, dataexp, qtd, codbar, usuario);
        }
        
        // v9.2: Verifica se completou o GRUPO atual (PT, Capital, R01, 999, ou outra regional)
        var grupoAtual = null;
        var todasLinhas = document.querySelectorAll('tbody tr');
        var linhasDoGrupo = [];
        
        // Determina qual grupo está sendo conferido
        if (tipoAtual === 'poupatempo') {
            grupoAtual = linha.getAttribute('data-pt-group') || linha.getAttribute('data-posto');
            // Linhas PT do mesmo posto
            for (var i = 0; i < todasLinhas.length; i++) {
                if (todasLinhas[i].getAttribute('data-ispt') === '1' &&
                    (todasLinhas[i].getAttribute('data-pt-group') === grupoAtual || todasLinhas[i].getAttribute('data-posto') === grupoAtual)) {
                    linhasDoGrupo.push(todasLinhas[i]);
                }
            }
        } else {
            grupoAtual = regionalAtual;
            // Todas as linhas da regional atual que NÃO sejam PT
            for (var i = 0; i < todasLinhas.length; i++) {
                if (todasLinhas[i].getAttribute('data-regional') === regionalAtual && 
                    todasLinhas[i].getAttribute('data-ispt') !== '1') {
                    linhasDoGrupo.push(todasLinhas[i]);
                }
            }
        }
        
        // Conta quantos foram conferidos
        var conferidosDoGrupo = 0;
        for (var j = 0; j < linhasDoGrupo.length; j++) {
            if (linhasDoGrupo[j].classList.contains('confirmado')) {
                conferidosDoGrupo++;
            }
        }
        
        // Se completou o grupo, toca concluído e reseta contexto
        if (conferidosDoGrupo === linhasDoGrupo.length && linhasDoGrupo.length > 0) {
            enfileirarSom(concluido);
            regionalAtual = null;
            tipoAtual = null;
            primeiroConferido = false;
        }
    });
    
    // Resetar conferência
    btnResetar.addEventListener("click", function() {
        if (confirm("Tem certeza que deseja reiniciar a conferência? Isso irá APAGAR todos os dados conferidos do banco!")) {
            // Obter datas filtradas
            var checkboxes = document.querySelectorAll('.filtro-datas input[type="checkbox"]:checked');
            var datas = [];
            
            for (var i = 0; i < checkboxes.length; i++) {
                datas.push(checkboxes[i].value);
            }
            
            // Resetar visualmente
            var trsConfirmados = document.querySelectorAll("tr.confirmado");
            for (var j = 0; j < trsConfirmados.length; j++) {
                trsConfirmados[j].classList.remove("confirmado");
            }
            
            regionalAtual = null;
            tipoAtual = null; // v9.2: Reseta tipo
            primeiroConferido = false; // v9.2: Reseta flag
            input.value = "";
            input.focus();
            
            // Excluir do banco via AJAX
            if (datas.length > 0) {
                var formData = new FormData();
                formData.append('excluir_lote_ajax', '1');
                formData.append('datas', datas.join(','));
                
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    if (data.sucesso) {
                        alert('Conferências resetadas com sucesso!');
                    } else {
                        console.error('Erro ao resetar:', data.erro);
                    }
                })
                .catch(function(error) {
                    console.error('Erro AJAX:', error);
                });
            }
        }
    });

    window.toggleIndicadorDias = function() {
        var el = document.getElementById('indicador-dias');
        if (!el) return;
        if (el.classList.contains('collapsed')) {
            el.classList.remove('collapsed');
        } else {
            el.classList.add('collapsed');
        }
    };
});
</script>

</body>
</html>
