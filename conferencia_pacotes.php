<?php
/* conferencia_pacotes.php — v8.16.7
 * Melhorias:
 * 1) Carrega APENAS datas não conferidas por padrão (filtro inteligente)
 * 2) Mantém últimas 5 datas disponíveis para seleção manual
 * 3) Datepicker moderno com input de intervalo de datas
 * 4) Removida coluna "Salvar" e checkboxes individuais
 * 5) Removida seção "Digite data para excluir conferências"
 * 6) Agrupamento melhorado: Poupa Tempo → Regional 01 → Capital → Central IIPR → Demais
 * 7) Radio button auto-save mantido, sons de alerta mantidos
 * 8) Posto Poupa Tempo dispara posto_poupatempo.mp3 (não pacotedeoutraregional.mp3)
 */

// Inicializa as variáveis
$total_codigos = 0;
$datas_postos_nao_conferidos = array();
$datas_expedicao = array();
$regionais_data = array();
$datas_filtro = isset($_GET['datas']) ? $_GET['datas'] : array();
$poupaTempoPostos = array();
$usar_filtro_padrao = false; // Flag para usar filtro padrão

// v8.16.7: Processar intervalo de datas do datepicker
if (isset($_GET['data_inicial']) && isset($_GET['data_final'])) {
    $data_inicial_input = $_GET['data_inicial']; // yyyy-mm-dd
    $data_final_input = $_GET['data_final'];     // yyyy-mm-dd
    
    if (!empty($data_inicial_input) && !empty($data_final_input)) {
        // Limpa datas checkboxes se intervalo foi usado
        $datas_filtro = array();
    }
}

// Conexão com o banco de dados
$host = '10.15.61.169';
$dbname = 'controle';
$user = 'controle_mat';
$pass = '375256';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Handler para AJAX salvar conferência quando radio está marcado
    if (isset($_POST['salvar_lote_ajax'])) {
        $lote = isset($_POST['lote']) ? trim($_POST['lote']) : '';
        if (!empty($lote)) {
            $sqlInsert = "INSERT INTO conferencia_pacotes (nlote, conf, usuario, lido_em) 
                         VALUES (?, 1, ?, NOW())
                         ON DUPLICATE KEY UPDATE conf=1, usuario=VALUES(usuario), lido_em=NOW()";
            $stmtInsert = $pdo->prepare($sqlInsert);
            $usuario = isset($_SESSION['usuario']) ? $_SESSION['usuario'] : 'conferencia';
            $stmtInsert->execute(array($lote, $usuario));
            echo json_encode(array('status' => 'success'));
            exit;
        }
    }

    // Handler para AJAX excluir conferência quando radio é desmarcado
    if (isset($_POST['excluir_lote_ajax'])) {
        $lote = isset($_POST['lote']) ? trim($_POST['lote']) : '';
        if (!empty($lote)) {
            $sqlDelete = "DELETE FROM conferencia_pacotes WHERE nlote = ?";
            $stmtDelete = $pdo->prepare($sqlDelete);
            $stmtDelete->execute(array($lote));
            echo json_encode(array('status' => 'success'));
            exit;
        }
    }

    // v8.16.7: Handler de exclusão por data removido (não mais necessário)

    // Mapa de postos Poupa Tempo (para alerta sonoro) - COM LIMITE
    $poupaTempoPostos = array();
    try {
        $stmtPt = $pdo->query("SELECT LPAD(posto,3,'0') AS posto FROM ciRegionais WHERE LOWER(REPLACE(entrega,' ','')) LIKE 'poupatempo%' LIMIT 100");
        while ($r = $stmtPt->fetch(PDO::FETCH_ASSOC)) {
            $poupaTempoPostos[] = $r['posto'];
        }
    } catch (Exception $e) {
        // Silent fail - continua sem Poupa Tempo
    }

    // Busca conferências já realizadas - COM ÍNDICE (rápido)
    $conferencias_realizadas = array();
    try {
        $stmtConf = $pdo->query("SELECT nlote FROM conferencia_pacotes WHERE conf=1 LIMIT 10000");
        while ($row = $stmtConf->fetch(PDO::FETCH_ASSOC)) {
            $conferencias_realizadas[$row['nlote']] = true;
        }
    } catch (Exception $e) {
        // Silent fail
    }

    // v8.16.7: Carrega APENAS datas não conferidas (ou todas se todas conferidas)
    if (empty($datas_filtro)) {
        $usar_filtro_padrao = true;
        
        // Busca datas que têm pacotes sem conferência
        $stmtNaoConferidas = $pdo->query(
            "SELECT DISTINCT DATE_FORMAT(csv.dataCarga, '%d-%m-%Y') as data
             FROM ciPostosCsv csv
             LEFT JOIN conferencia_pacotes cp ON csv.lote = cp.nlote AND cp.conf = 1
             WHERE csv.dataCarga IS NOT NULL AND cp.nlote IS NULL
             ORDER BY csv.dataCarga DESC
             LIMIT 3"
        );
        
        $datas_nao_conferidas = array();
        while ($row = $stmtNaoConferidas->fetch(PDO::FETCH_ASSOC)) {
            $datas_nao_conferidas[] = $row['data'];
        }
        
        // Se não há datas não conferidas, pega a última data disponível
        if (empty($datas_nao_conferidas)) {
            $stmtUltima = $pdo->query(
                "SELECT DISTINCT DATE_FORMAT(dataCarga, '%d-%m-%Y') as data
                 FROM ciPostosCsv
                 WHERE dataCarga IS NOT NULL
                 ORDER BY dataCarga DESC
                 LIMIT 1"
            );
            while ($row = $stmtUltima->fetch(PDO::FETCH_ASSOC)) {
                $datas_nao_conferidas[] = $row['data'];
            }
        }
        
        $datas_filtro = $datas_nao_conferidas;
    }

    // Busca as últimas 5 datas disponíveis para o seletor (OTIMIZADO)
    $stmtTodasDatas = $pdo->query(
        "SELECT DISTINCT DATE_FORMAT(dataCarga, '%d-%m-%Y') as data 
         FROM ciPostosCsv 
         WHERE dataCarga IS NOT NULL 
         ORDER BY dataCarga DESC 
         LIMIT 5"
    );
    
    $datas_expedicao = array();
    while ($row = $stmtTodasDatas->fetch(PDO::FETCH_ASSOC)) {
        $datas_expedicao[] = $row['data'];
    }

    // Busca dados dos postos - OTIMIZADO COM FILTRO SQL
    $dataSqlArray = array();
    
    // v8.16.7: Usar intervalo de datas se fornecido
    if (isset($data_inicial_input) && isset($data_final_input) && !empty($data_inicial_input) && !empty($data_final_input)) {
        // Query por intervalo
        $sqlPosios = "SELECT lote, posto, regional, quantidade, dataCarga 
                      FROM ciPostosCsv 
                      WHERE DATE(dataCarga) BETWEEN ? AND ?
                      ORDER BY regional, lote, posto
                      LIMIT 5000";
        
        $stmtPosios = $pdo->prepare($sqlPosios);
        $stmtPosios->execute(array($data_inicial_input, $data_final_input));
    } else {
        // Query por datas específicas (checkboxes)
        foreach ($datas_filtro as $dataFormatada) {
            // Converte d-m-Y para Y-m-d
            $partes = explode('-', $dataFormatada);
            if (count($partes) == 3) {
                $dataSql = $partes[2] . '-' . $partes[1] . '-' . $partes[0];
                $dataSqlArray[] = $dataSql;
            }
        }
        
        if (!empty($dataSqlArray)) {
            $placeholders = implode(',', array_fill(0, count($dataSqlArray), '?'));
            $sqlPosios = "SELECT lote, posto, regional, quantidade, dataCarga 
                          FROM ciPostosCsv 
                          WHERE DATE(dataCarga) IN ($placeholders)
                          ORDER BY regional, lote, posto
                          LIMIT 5000";
            
            $stmtPosios = $pdo->prepare($sqlPosios);
            $stmtPosios->execute($dataSqlArray);
        }
    }
    
    if (isset($stmtPosios)) {
        while ($row = $stmtPosios->fetch(PDO::FETCH_ASSOC)) {
            if (empty($row['dataCarga'])) continue;

            $data_original = $row['dataCarga'];
            $data_formatada = date('d-m-Y', strtotime($data_original));

            $lote = $row['lote'];
            $posto = $row['posto'];
            $posto_str = str_pad($posto, 3, '0', STR_PAD_LEFT);
            $regional = $row['regional'];
            $regional_str = str_pad($regional, 3, '0', STR_PAD_LEFT);
            $quantidade = str_pad($row['quantidade'], 5, '0', STR_PAD_LEFT);

            $nome_posto = "$posto_str - Posto $posto_str";
            $isPoupaTempo = in_array($posto_str, $poupaTempoPostos) ? '1' : '0';
            $codigo_barras = $lote . $regional_str . $posto_str . $quantidade;
            $conferido = isset($conferencias_realizadas[$lote]) ? 1 : 0;

            // Classifica por regional
            if (!isset($regionais_data[$regional])) {
                $regionais_data[$regional] = array();
            }

            $regionais_data[$regional][] = array(
                'regional_str' => $regional_str,
                'lote' => $lote,
                'posto' => $posto_str,
                'nome_posto' => $nome_posto,
                'data_expedicao' => $data_formatada,
                'quantidade' => ltrim($quantidade, '0'),
                'codigo_barras' => $codigo_barras,
                'isPoupaTempo' => $isPoupaTempo,
                'conferido' => $conferido
            );

            $total_codigos++;
            
            // Limita a 5000 registros
            if ($total_codigos >= 5000) break;
        }
    }

    sort($datas_expedicao);
} catch (PDOException $e) {
    echo "Erro ao conectar ao banco de dados: " . $e->getMessage();
    die();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Conferência de Pacotes - v8.16.7</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            padding: 30px 20px;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        h1 {
            color: #2c3e50;
            margin-bottom: 30px;
            font-size: 28px;
            border-bottom: 3px solid #3498db;
            padding-bottom: 15px;
        }
        
        h2 {
            color: #34495e;
            margin-top: 40px;
            margin-bottom: 20px;
            font-size: 18px;
            border-left: 5px solid #3498db;
            padding-left: 15px;
        }
        
        /* Radio Auto-Save no topo */
        .radio-salvar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px 25px;
            margin-bottom: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .radio-salvar label {
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
        }
        
        .radio-salvar input[type="radio"] {
            margin-right: 12px;
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: white;
        }
        
        /* Filtro de Datas Moderno */
        .filtro-datas {
            background: white;
            padding: 25px;
            margin-bottom: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border-top: 4px solid #3498db;
        }
        
        .filtro-datas h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 16px;
            font-weight: 600;
        }
        
        .datas-checkboxes {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .datas-checkboxes label {
            display: flex;
            align-items: center;
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 6px;
            transition: background-color 0.3s;
            user-select: none;
        }
        
        .datas-checkboxes label:hover {
            background-color: #ecf0f1;
        }
        
        .datas-checkboxes input[type="checkbox"] {
            margin-right: 8px;
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #3498db;
        }
        
        .filtro-botoes {
            display: flex;
            gap: 10px;
        }
        
        .filtro-botoes input[type="submit"],
        .filtro-botoes input[type="button"] {
            padding: 10px 25px;
            background-color: #3498db;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 6px;
            font-weight: 600;
            transition: background-color 0.3s, transform 0.2s;
            font-size: 14px;
        }
        
        .filtro-botoes input[type="submit"]:hover,
        .filtro-botoes input[type="button"]:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }
        
        .filtro-botoes input[type="submit"]:active,
        .filtro-botoes input[type="button"]:active {
            transform: translateY(0);
        }
        
        /* Total de Pacotes */
        .info-total {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 25px;
            margin-bottom: 30px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        /* Input para Barcode */
        .barcode-input-wrapper {
            margin-bottom: 30px;
        }
        
        .barcode-input-wrapper label {
            display: block;
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        #codigo_barras {
            padding: 15px 15px;
            width: 100%;
            max-width: 400px;
            font-size: 16px;
            border: 2px solid #3498db;
            border-radius: 6px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        
        #codigo_barras:focus {
            outline: none;
            border-color: #2980b9;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
        
        /* Tabelas */
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            margin-bottom: 30px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        
        thead {
            background-color: #34495e;
            color: white;
        }
        
        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }
        
        td {
            padding: 12px 15px;
            border-bottom: 1px solid #ecf0f1;
            font-size: 14px;
        }
        
        tbody tr {
            transition: background-color 0.2s;
        }
        
        tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        /* Linha conferida (verde) */
        tbody tr.confirmado {
            background-color: #d4edda;
            font-weight: 500;
        }
        
        tbody tr.confirmado:hover {
            background-color: #c3e6cb;
        }
        
        /* Label Poupa Tempo */
        .poupa-tempo-label {
            display: inline-block;
            background-color: #e74c3c;
            color: white;
            padding: 4px 10px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 12px;
            margin-left: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Informação de contagem */
        .pacotes-info {
            color: #7f8c8d;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Conferência de Pacotes</h1>

        <!-- Radio Button Auto-Save no Topo -->
        <div class="radio-salvar">
            <label>
                <input type="radio" id="autoSalvar" name="auto_salvar" value="1" checked>
                Auto-salvar conferências durante a leitura
            </label>
        </div>

        <!-- Filtro de Datas Moderno v8.16.7 -->
        <div class="filtro-datas">
            <form method="GET">
                <h3>📅 Filtro de Datas (padrão: apenas não conferidas)</h3>
                
                <!-- Últimas 5 datas para seleção rápida -->
                <div class="datas-checkboxes">
                    <strong>Seleção rápida:</strong>
                    <?php foreach ($datas_expedicao as $data): ?>
                        <label>
                            <input type="checkbox" name="datas[]" value="<?php echo $data; ?>" 
                                <?php echo (in_array($data, $datas_filtro) ? 'checked' : ''); ?>>
                            <?php echo $data; ?>
                        </label>
                    <?php endforeach; ?>
                </div>
                
                <!-- Datepicker para intervalo -->
                <div class="datepicker-wrapper" style="margin-top: 20px;">
                    <strong>Ou filtrar por intervalo:</strong>
                    <div style="display: flex; gap: 15px; margin-top: 10px; align-items: center;">
                        <div>
                            <label style="font-size: 13px; color: #7f8c8d;">Data inicial:</label>
                            <input type="date" id="data_inicial" name="data_inicial" 
                                   style="padding: 8px; border: 2px solid #3498db; border-radius: 6px; font-size: 14px;">
                        </div>
                        <div>
                            <label style="font-size: 13px; color: #7f8c8d;">Data final:</label>
                            <input type="date" id="data_final" name="data_final" 
                                   style="padding: 8px; border: 2px solid #3498db; border-radius: 6px; font-size: 14px;">
                        </div>
                    </div>
                </div>
                
                <div class="filtro-botoes" style="margin-top: 20px;">
                    <input type="submit" value="🔍 Aplicar Filtro">
                    <input type="button" value="🔄 Resetar" onclick="window.location.href = window.location.pathname;">
                </div>
            </form>
        </div>

        <!-- Total de Pacotes -->
        <div class="info-total">
            📦 Total de pacotes exibidos: <strong><?php echo $total_codigos; ?></strong>
        </div>

        <!-- Input para escanear código de barras -->
        <div class="barcode-input-wrapper">
            <label for="codigo_barras">📍 Escaneie o código de barras:</label>
            <input type="text" id="codigo_barras" placeholder="Leia o código aqui..." maxlength="19" autofocus>
        </div>

        <!-- Tabelas por Regional -->
        <div id="tabelas">
    <?php
        // Segregação CORRETA: 1) Poupa Tempo, 2) Regional 1, 3) Capital (0), 4) Central IIPR (999), 5) Demais Regionais
        
        // 1. POSTOS POUPA TEMPO (de qualquer regional)
        $postos_pt_todos = array();
        foreach ($regionais_data as $regional => $postos) {
            foreach ($postos as $p) {
                if ($p['isPoupaTempo'] == '1') {
                    $postos_pt_todos[] = $p;
                }
            }
        }
        
        if (!empty($postos_pt_todos)) {
            $total_pt = count($postos_pt_todos);
            $conferidos_pt = count(array_filter($postos_pt_todos, function($p) { return $p['conferido']; }));
            
            echo "<h2>Postos Poupa Tempo <span class='poupa-tempo-label'>POUPA TEMPO</span> ($total_pt pacotes / $conferidos_pt conferidos)</h2>";
            echo '<table>';
            echo '<thead><tr>';
            echo '<th>Regional</th><th>Lote</th><th>Posto</th><th>Data</th><th>Qtd</th><th>Código</th>';
            echo '</tr></thead><tbody>';
            
            foreach ($postos_pt_todos as $post) {
                $classe = $post['conferido'] ? 'confirmado' : '';
                $checked = $post['conferido'] ? 'checked' : '';
                echo "<tr data-lote='{$post['lote']}' data-codigo='{$post['codigo_barras']}' data-poupatempo='1' class='$classe' onclick=\"document.querySelector('input[data-lote=\\\"{$post['lote']}\\\"]').click()\">";
                echo "<td>{$post['regional_str']}</td><td>{$post['lote']}</td><td>{$post['posto']}</td>";
                echo "<td>{$post['data_expedicao']}</td><td>{$post['quantidade']}</td><td>{$post['codigo_barras']}</td>";
                echo '<input type="hidden" class="radio-conferencia" data-lote="' . $post['lote'] . '" data-checked="' . $checked . '" style="display:none;">';
                echo '</tr>';
            }
            echo '</tbody></table>';
        }
        
        // 2. REGIONAL 1
        if (isset($regionais_data[1])) {
            $postos_r1 = array_filter($regionais_data[1], function($p) {
                return $p['isPoupaTempo'] != '1';
            });
            
            if (!empty($postos_r1)) {
                $total_r1 = count($postos_r1);
                $conferidos_r1 = count(array_filter($postos_r1, function($p) { return $p['conferido']; }));
                
                echo "<h2>001 - Regional 001 ($total_r1 pacotes / $conferidos_r1 conferidos)</h2>";
                echo '<table>';
                echo '<thead><tr>';
                echo '<th>Regional</th><th>Lote</th><th>Posto</th><th>Data</th><th>Qtd</th><th>Código</th>';
                echo '</tr></thead><tbody>';
                
                foreach ($postos_r1 as $post) {
                    $classe = $post['conferido'] ? 'confirmado' : '';
                    $checked = $post['conferido'] ? 'checked' : '';
                    echo "<tr data-lote='{$post['lote']}' data-codigo='{$post['codigo_barras']}' data-poupatempo='0' class='$classe' onclick=\"document.querySelector('input[data-lote=\\\"{$post['lote']}\\\"]').click()\">";
                    echo "<td>{$post['regional_str']}</td><td>{$post['lote']}</td><td>{$post['posto']}</td>";
                    echo "<td>{$post['data_expedicao']}</td><td>{$post['quantidade']}</td><td>{$post['codigo_barras']}</td>";
                    echo '<input type="hidden" class="radio-conferencia" data-lote="' . $post['lote'] . '" data-checked="' . $checked . '" style="display:none;">';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            }
        }
        
        // 3. CAPITAL (Regional 0)
        if (isset($regionais_data[0])) {
            $postos_capital = array_filter($regionais_data[0], function($p) {
                return $p['isPoupaTempo'] != '1';
            });
            
            if (!empty($postos_capital)) {
                $total_capital = count($postos_capital);
                $conferidos_capital = count(array_filter($postos_capital, function($p) { return $p['conferido']; }));
                
                echo "<h2>000 - Postos da Capital ($total_capital pacotes / $conferidos_capital conferidos)</h2>";
                echo '<table>';
                echo '<thead><tr>';
                echo '<th>Regional</th><th>Lote</th><th>Posto</th><th>Data</th><th>Qtd</th><th>Código</th>';
                echo '</tr></thead><tbody>';
                
                foreach ($postos_capital as $post) {
                    $classe = $post['conferido'] ? 'confirmado' : '';
                    $checked = $post['conferido'] ? 'checked' : '';
                    echo "<tr data-lote='{$post['lote']}' data-codigo='{$post['codigo_barras']}' data-poupatempo='0' class='$classe' onclick=\"document.querySelector('input[data-lote=\\\"{$post['lote']}\\\"]').click()\">";
                    echo "<td>{$post['regional_str']}</td><td>{$post['lote']}</td><td>{$post['posto']}</td>";
                    echo "<td>{$post['data_expedicao']}</td><td>{$post['quantidade']}</td><td>{$post['codigo_barras']}</td>";
                    echo '<input type="hidden" class="radio-conferencia" data-lote="' . $post['lote'] . '" data-checked="' . $checked . '" style="display:none;">';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            }
        }
        
        // 4. CENTRAL IIPR (Regional 999)
        if (isset($regionais_data[999])) {
            $total_central = count($regionais_data[999]);
            $conferidos_central = count(array_filter($regionais_data[999], function($p) { return $p['conferido']; }));
            
            echo "<h2>999 - Central IIPR ($total_central pacotes / $conferidos_central conferidos)</h2>";
            echo '<table>';
            echo '<thead><tr>';
            echo '<th>Regional</th><th>Lote</th><th>Posto</th><th>Data</th><th>Qtd</th><th>Código</th>';
            echo '</tr></thead><tbody>';
            
            foreach ($regionais_data[999] as $post) {
                $classe = $post['conferido'] ? 'confirmado' : '';
                $checked = $post['conferido'] ? 'checked' : '';
                echo "<tr data-lote='{$post['lote']}' data-codigo='{$post['codigo_barras']}' data-poupatempo='0' class='$classe' onclick=\"document.querySelector('input[data-lote=\\\"{$post['lote']}\\\"]').click()\">";
                echo "<td>{$post['regional_str']}</td><td>{$post['lote']}</td><td>{$post['posto']}</td>";
                echo "<td>{$post['data_expedicao']}</td><td>{$post['quantidade']}</td><td>{$post['codigo_barras']}</td>";
                echo '<input type="hidden" class="radio-conferencia" data-lote="' . $post['lote'] . '" data-checked="' . $checked . '" style="display:none;">';
                echo '</tr>';
            }
            echo '</tbody></table>';
        }
        
        // 5. DEMAIS REGIONAIS (2, 3, 4, 5, etc) - sem Poupa Tempo e sem 1, 0, 999
        foreach (array_keys($regionais_data) as $regional) {
            if ($regional != 0 && $regional != 1 && $regional != 999) {
                $postos_reg = array_filter($regionais_data[$regional], function($p) {
                    return $p['isPoupaTempo'] != '1';
                });
                
                if (!empty($postos_reg)) {
                    $regional_str = str_pad($regional, 3, '0', STR_PAD_LEFT);
                    $total_reg = count($postos_reg);
                    $conferidos_reg = count(array_filter($postos_reg, function($p) { return $p['conferido']; }));
                    
                    echo "<h2>$regional_str - Regional $regional ($total_reg pacotes / $conferidos_reg conferidos)</h2>";
                    echo '<table>';
                    echo '<thead><tr>';
                    echo '<th>Regional</th><th>Lote</th><th>Posto</th><th>Data</th><th>Qtd</th><th>Código</th>';
                    echo '</tr></thead><tbody>';
                    
                    foreach ($postos_reg as $post) {
                        $classe = $post['conferido'] ? 'confirmado' : '';
                        $checked = $post['conferido'] ? 'checked' : '';
                        echo "<tr data-lote='{$post['lote']}' data-codigo='{$post['codigo_barras']}' data-poupatempo='0' class='$classe' onclick=\"document.querySelector('input[data-lote=\\\"{$post['lote']}\\\"]').click()\">";
                        echo "<td>{$post['regional_str']}</td><td>{$post['lote']}</td><td>{$post['posto']}</td>";
                        echo "<td>{$post['data_expedicao']}</td><td>{$post['quantidade']}</td><td>{$post['codigo_barras']}</td>";
                        echo '<input type="hidden" class="radio-conferencia" data-lote="' . $post['lote'] . '" data-checked="' . $checked . '" style="display:none;">';
                        echo '</tr>';
                    }
                    echo '</tbody></table>';
                }
            }
        }
    ?>
    </div>

    <!-- Áudios -->
    <audio id="beep" src="beep.mp3" preload="auto"></audio>
    <audio id="concluido" src="concluido.mp3" preload="auto"></audio>
    <audio id="pacotejaconferido" src="pacotejaconferido.mp3" preload="auto"></audio>
    <audio id="pacotedeoutraregional" src="pacotedeoutraregional.mp3" preload="auto"></audio>
    <audio id="posto_poupatempo" src="posto_poupatempo.mp3" preload="auto"></audio>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const autoSalvarRadio = document.getElementById('autoSalvar');
        const codigoBarrasInput = document.getElementById('codigo_barras');
        const beep = document.getElementById('beep');
        const concluido = document.getElementById('concluido');
        const pacoteJaConferido = document.getElementById('pacotejaconferido');
        const postoPoupaTempo = document.getElementById('posto_poupatempo');

        // Busca todos os hidden radio conferencia inputs
        function buscarRadiosPorTabela() {
            const tables = document.querySelectorAll('table tbody');
            const radiosMap = {};
            
            tables.forEach(table => {
                const rows = table.querySelectorAll('tr');
                const radiosPorTabela = [];
                
                rows.forEach(row => {
                    const radio = row.querySelector('.radio-conferencia');
                    if (radio) {
                        radiosPorTabela.push(radio);
                    }
                });
                
                if (radiosPorTabela.length > 0) {
                    radiosMap[table] = radiosPorTabela;
                }
            });
            
            return radiosMap;
        }

        const radioMap = buscarRadiosPorTabela();

        // Listener para mudança de estado do radio via clique na linha ou barcode
        function setupRadioChangeListener(radio) {
            const lote = radio.getAttribute('data-lote');
            const row = radio.closest('tr');
            
            // Listener para mudança de estado (quando o radio muda de checked)
            const observer = new MutationObserver(function() {
                const isChecked = radio.getAttribute('data-checked') === 'checked' || row.classList.contains('confirmado');
                
                if (isChecked && !row.classList.contains('confirmado')) {
                    // Marcar = Salvar
                    row.classList.add('confirmado');
                    
                    if (autoSalvarRadio.checked) {
                        fetch('', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: 'salvar_lote_ajax=1&lote=' + encodeURIComponent(lote)
                        })
                        .catch(error => console.error('Erro ao salvar:', error));
                    }
                } else if (!isChecked && row.classList.contains('confirmado')) {
                    // Desmarcar = Deletar
                    row.classList.remove('confirmado');
                    
                    if (autoSalvarRadio.checked) {
                        fetch('', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: 'excluir_lote_ajax=1&lote=' + encodeURIComponent(lote)
                        })
                        .catch(error => console.error('Erro ao excluir:', error));
                    }
                }
            });
            
            observer.observe(row, { attributes: true, attributeFilter: ['class'] });
        }

        // Setup de todos os radios
        document.querySelectorAll('.radio-conferencia').forEach(radio => {
            setupRadioChangeListener(radio);
        });

        // Listener para scanear código de barras
        codigoBarrasInput.addEventListener('input', function() {
            let valor = this.value.trim();
            if (valor.length !== 19) return;

            // Busca a linha com esse código de barras
            const linha = document.querySelector(`tr[data-codigo="${valor}"]`);

            if (!linha) {
                this.value = '';
                return;
            }

            const radio = linha.querySelector('.radio-conferencia');
            const isPoupaTempo = linha.getAttribute('data-poupatempo') === '1';
            const jaSalvo = linha.classList.contains('confirmado');

            if (jaSalvo) {
                // Já conferido
                pacoteJaConferido.currentTime = 0;
                pacoteJaConferido.play();
            } else if (isPoupaTempo && autoSalvarRadio.checked) {
                // Poupa Tempo alerta
                postoPoupaTempo.currentTime = 0;
                postoPoupaTempo.play();
            } else if (autoSalvarRadio.checked) {
                // Marca como conferido
                linha.classList.add('confirmado');
                radio.setAttribute('data-checked', 'checked');
                
                beep.currentTime = 0;
                beep.play();

                // Centraliza a linha
                linha.scrollIntoView({ behavior: 'smooth', block: 'center' });

                // Salva via AJAX
                const lote = radio.getAttribute('data-lote');
                fetch('', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'salvar_lote_ajax=1&lote=' + encodeURIComponent(lote)
                })
                .catch(error => console.error('Erro ao salvar:', error));

                // Verifica se todos os pacotes foram conferidos
                setTimeout(() => {
                    const table = linha.closest('table');
                    if (table) {
                        const allRows = table.querySelectorAll('tbody tr');
                        const confirmedRows = table.querySelectorAll('tbody tr.confirmado');
                        
                        if (allRows.length === confirmedRows.length) {
                            concluido.currentTime = 0;
                            concluido.play();
                        }
                    }
                }, 120);
            }

            this.value = '';
            this.focus();
        });

        // Listener para cliques diretos na linha da tabela
        document.querySelectorAll('tbody tr').forEach(row => {
            row.addEventListener('click', function(e) {
                if (e.target.tagName === 'TR') {
                    const radio = this.querySelector('.radio-conferencia');
                    if (radio) {
                        // Toggle do estado
                        const isChecked = this.classList.contains('confirmado');
                        if (!isChecked) {
                            this.classList.add('confirmado');
                            radio.setAttribute('data-checked', 'checked');
                        } else {
                            this.classList.remove('confirmado');
                            radio.setAttribute('data-checked', '');
                        }
                        
                        // Dispara mudança
                        if (autoSalvarRadio.checked) {
                            const lote = radio.getAttribute('data-lote');
                            if (!isChecked) {
                                fetch('', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                    body: 'salvar_lote_ajax=1&lote=' + encodeURIComponent(lote)
                                });
                            } else {
                                fetch('', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                    body: 'excluir_lote_ajax=1&lote=' + encodeURIComponent(lote)
                                });
                            }
                        }
                    }
                }
            });
        });
    });
    </script>

</body>
</html>
