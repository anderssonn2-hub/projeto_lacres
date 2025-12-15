<?php
/* conferencia_pacotes.php — v8.16.6
 * Melhorias em relação v8.16.5:
 * 1) Carregamento inteligente: postos NÃO conferidos ou últimos dias se todos conferidos
 * 2) Melhor seletor de datas com checkboxes
 * 3) Total de pacotes visível na tela
 * 4) Segregação CORRETA de regionais (1, 0, 999, demais)
 * 5) Sem sobrecarga de dados na tela
 * 6) Mantém: radio button auto-save, sons, excluir por data
 */

// Inicializa as variáveis
$total_codigos = 0;
$datas_postos_nao_conferidos = array();
$datas_expedicao = array();
$regionais_data = array();
$datas_filtro = isset($_GET['datas']) ? $_GET['datas'] : array();
$poupaTempoPostos = array();
$usar_filtro_padrao = false; // Flag para usar filtro padrão

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

    // Handler para excluir conferências por data (dd-mm-yyyy)
    if (isset($_POST['excluir_por_data'])) {
        $data_str = isset($_POST['data_exclusao']) ? trim($_POST['data_exclusao']) : '';
        if (!empty($data_str)) {
            // Converte dd-mm-yyyy para yyyy-mm-dd
            $partes = explode('-', $data_str);
            if (count($partes) == 3) {
                $data_sql = $partes[2] . '-' . $partes[1] . '-' . $partes[0];
                
                // Busca todos os lotes da data
                $sqlSelect = "SELECT nlote FROM conferencia_pacotes 
                            WHERE DATE(lido_em) = ?";
                $stmtSelect = $pdo->prepare($sqlSelect);
                $stmtSelect->execute(array($data_sql));
                
                $lotes = array();
                while ($row = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
                    $lotes[] = $row['nlote'];
                }
                
                // Deleta as conferências
                if (count($lotes) > 0) {
                    $sqlDelete = "DELETE FROM conferencia_pacotes WHERE DATE(lido_em) = ?";
                    $stmtDelete = $pdo->prepare($sqlDelete);
                    $stmtDelete->execute(array($data_sql));
                    echo "<script>alert('Conferências de " . $data_str . " excluídas com sucesso! Total: " . count($lotes) . " lotes.');</script>";
                } else {
                    echo "<script>alert('Nenhuma conferência encontrada para a data " . $data_str . "'.');</script>";
                }
            }
        }
    }

    // Mapa de postos Poupa Tempo (para alerta sonoro)
    $stmtPt = $pdo->query("SELECT LPAD(posto,3,'0') AS posto FROM ciRegionais WHERE LOWER(REPLACE(entrega,' ','')) LIKE 'poupatempo%'");
    while ($r = $stmtPt->fetch(PDO::FETCH_ASSOC)) {
        $poupaTempoPostos[] = $r['posto'];
    }

    // Busca conferências já realizadas
    $conferencias_realizadas = array();
    $stmtConf = $pdo->query("SELECT DISTINCT nlote FROM conferencia_pacotes WHERE conf=1");
    while ($row = $stmtConf->fetch(PDO::FETCH_ASSOC)) {
        $conferencias_realizadas[] = $row['nlote'];
    }

    // Se não há filtro de datas especificado, usa filtro padrão (postos não conferidos)
    if (empty($datas_filtro)) {
        $usar_filtro_padrao = true;
        
        // Busca datas que têm postos NÃO conferidos
        $stmtDatasPendentes = $pdo->query(
            "SELECT DISTINCT DATE_FORMAT(p.dataCarga, '%d-%m-%Y') as data
             FROM ciPostosCsv p
             LEFT JOIN conferencia_pacotes c ON p.lote = c.nlote AND c.conf = 1
             WHERE c.nlote IS NULL AND p.dataCarga IS NOT NULL
             ORDER BY p.dataCarga DESC
             LIMIT 5"
        );
        
        while ($row = $stmtDatasPendentes->fetch(PDO::FETCH_ASSOC)) {
            $datas_filtro[] = $row['data'];
        }
        
        // Se não há postos não conferidos, busca últimos dias conferidos
        if (empty($datas_filtro)) {
            $stmtUltimas = $pdo->query(
                "SELECT DISTINCT DATE_FORMAT(p.dataCarga, '%d-%m-%Y') as data
                 FROM ciPostosCsv p
                 WHERE p.dataCarga IS NOT NULL
                 ORDER BY p.dataCarga DESC
                 LIMIT 5"
            );
            
            while ($row = $stmtUltimas->fetch(PDO::FETCH_ASSOC)) {
                $datas_filtro[] = $row['data'];
            }
        }
    }

    // Busca todas as datas disponíveis para o seletor
    $stmtTodasDatas = $pdo->query("SELECT DISTINCT DATE_FORMAT(dataCarga, '%d-%m-%Y') as data FROM ciPostosCsv WHERE dataCarga IS NOT NULL ORDER BY dataCarga DESC");
    while ($row = $stmtTodasDatas->fetch(PDO::FETCH_ASSOC)) {
        if (!in_array($row['data'], $datas_expedicao)) {
            $datas_expedicao[] = $row['data'];
        }
    }

    // Busca dados dos postos
    $stmt = $pdo->query("SELECT lote, posto, regional, quantidade, dataCarga FROM ciPostosCsv ORDER BY regional, lote, posto");

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (empty($row['dataCarga'])) continue;

        $data_original = $row['dataCarga'];
        $data_formatada = date('d-m-Y', strtotime($data_original));

        // Filtra pela data selecionada
        if (!in_array($data_formatada, $datas_filtro)) {
            continue;
        }

        $lote = $row['lote'];
        $posto = $row['posto']; // Mantém como número para comparação
        $posto_str = str_pad($posto, 3, '0', STR_PAD_LEFT);
        $regional = $row['regional']; // Mantém como número
        $regional_str = str_pad($regional, 3, '0', STR_PAD_LEFT);
        $quantidade = str_pad($row['quantidade'], 5, '0', STR_PAD_LEFT);

        $nome_posto = "$posto_str - Posto $posto_str";
        $isPoupaTempo = in_array($posto_str, $poupaTempoPostos) ? '1' : '0';
        $codigo_barras = $lote . $regional_str . $posto_str . $quantidade;
        $conferido = in_array($lote, $conferencias_realizadas) ? 1 : 0;

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
    <title>Conferência de Pacotes - v8.16.6</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f0f0f0; }
        .confirmado { background-color: #c6ffc6; }
        
        .filtro-datas {
            background-color: #f9f9f9;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .filtro-datas h3 {
            margin-top: 0;
            color: #333;
        }
        
        .datas-checkboxes {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .datas-checkboxes label {
            display: flex;
            align-items: center;
            cursor: pointer;
        }
        
        .datas-checkboxes input[type="checkbox"] {
            margin-right: 8px;
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .filtro-botoes {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }
        
        .filtro-botoes input[type="submit"] {
            padding: 8px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }
        
        .filtro-botoes input[type="submit"]:hover {
            background-color: #0056b3;
        }
        
        .excluir-data-form {
            background-color: #fff3cd;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #ffc107;
            border-radius: 4px;
        }
        
        .excluir-data-form input[type="text"] {
            padding: 5px 10px;
            width: 150px;
        }
        
        .excluir-data-form button {
            padding: 5px 15px;
            background-color: #dc3545;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }
        
        .excluir-data-form button:hover {
            background-color: #c82333;
        }
        
        .radio-salvar {
            margin: 20px 0;
            padding: 15px;
            background-color: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 4px;
        }
        
        .radio-salvar label {
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .radio-salvar input[type="radio"] {
            margin-right: 10px;
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        
        h2 {
            margin-top: 30px;
            margin-bottom: 10px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        
        .pacotes-info {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
        }
        
        .poupa-tempo-label {
            display: inline-block;
            background-color: #ff6b6b;
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 12px;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <h1>Conferência de Pacotes - v8.16.6</h1>

    <!-- Filtro de Datas com Checkboxes -->
    <div class="filtro-datas">
        <form method="GET">
            <h3>Selecione data(s) para filtrar:</h3>
            <div class="datas-checkboxes">
                <?php foreach ($datas_expedicao as $data): ?>
                    <label>
                        <input type="checkbox" name="datas[]" value="<?php echo $data; ?>" 
                            <?php echo (in_array($data, $datas_filtro) ? 'checked' : ''); ?>>
                        <?php echo $data; ?>
                    </label>
                <?php endforeach; ?>
            </div>
            <div class="filtro-botoes">
                <input type="submit" value="Aplicar Filtro">
                <input type="button" value="Limpar Filtro" onclick="document.querySelectorAll('.datas-checkboxes input').forEach(cb => cb.checked = false);">
            </div>
        </form>
    </div>

    <!-- Total de Pacotes -->
    <div style="margin-bottom: 20px; padding: 10px; background-color: #e8f4f8; border-left: 4px solid #2196F3; border-radius: 4px;">
        <strong>Total de pacotes exibidos: <?php echo $total_codigos; ?></strong>
    </div>

    <!-- Excluir Conferências por Data -->
    <div class="excluir-data-form">
        <form method="POST">
            <label for="data_exclusao">Digite a data para excluir conferências (dd-mm-yyyy):</label>
            <input type="text" id="data_exclusao" name="data_exclusao" placeholder="dd-mm-yyyy" pattern="\d{2}-\d{2}-\d{4}">
            <button type="submit" name="excluir_por_data" value="1">Excluir Conferências da Data</button>
        </form>
    </div>

    <!-- Radio Button Auto-Save -->
    <div class="radio-salvar">
        <label>
            <input type="radio" id="autoSalvar" name="auto_salvar" value="1" checked>
            Salvar conferências automaticamente
        </label>
    </div>

    <!-- Input para escanear código de barras -->
    <div style="margin-bottom: 20px;">
        <input type="text" id="codigo_barras" placeholder="Escaneie o código de barras" maxlength="19" autofocus style="padding: 10px; width: 300px;">
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
            
            echo "<h2>Postos Poupa Tempo ($total_pt pacotes no total / $conferidos_pt conferidos) <span class='poupa-tempo-label'>POUPA TEMPO</span></h2>";
            echo '<table>';
            echo '<thead><tr>';
            echo '<th>Salvar</th><th>Regional</th><th>Lote</th><th>Posto</th><th>Data</th><th>Qtd</th><th>Código</th>';
            echo '</tr></thead><tbody>';
            
            foreach ($postos_pt_todos as $post) {
                $classe = $post['conferido'] ? 'confirmado' : '';
                $checked = $post['conferido'] ? 'checked' : '';
                echo "<tr data-lote='{$post['lote']}' data-codigo='{$post['codigo_barras']}' data-poupatempo='1' class='$classe'>";
                echo "<td><input type='radio' name='salvar_{$post['lote']}' class='radio-conferencia' data-lote='{$post['lote']}' $checked></td>";
                echo "<td>{$post['regional_str']}</td><td>{$post['lote']}</td><td>{$post['posto']}</td>";
                echo "<td>{$post['data_expedicao']}</td><td>{$post['quantidade']}</td><td>{$post['codigo_barras']}</td>";
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
                
                echo "<h2>001 - Regional 001 ($total_r1 pacotes no total / $conferidos_r1 conferidos)</h2>";
                echo '<table>';
                echo '<thead><tr>';
                echo '<th>Salvar</th><th>Regional</th><th>Lote</th><th>Posto</th><th>Data</th><th>Qtd</th><th>Código</th>';
                echo '</tr></thead><tbody>';
                
                foreach ($postos_r1 as $post) {
                    $classe = $post['conferido'] ? 'confirmado' : '';
                    $checked = $post['conferido'] ? 'checked' : '';
                    echo "<tr data-lote='{$post['lote']}' data-codigo='{$post['codigo_barras']}' data-poupatempo='0' class='$classe'>";
                    echo "<td><input type='radio' name='salvar_{$post['lote']}' class='radio-conferencia' data-lote='{$post['lote']}' $checked></td>";
                    echo "<td>{$post['regional_str']}</td><td>{$post['lote']}</td><td>{$post['posto']}</td>";
                    echo "<td>{$post['data_expedicao']}</td><td>{$post['quantidade']}</td><td>{$post['codigo_barras']}</td>";
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
                
                echo "<h2>Posto(s) da Capital ($total_capital pacotes no total / $conferidos_capital conferidos)</h2>";
                echo '<table>';
                echo '<thead><tr>';
                echo '<th>Salvar</th><th>Regional</th><th>Lote</th><th>Posto</th><th>Data</th><th>Qtd</th><th>Código</th>';
                echo '</tr></thead><tbody>';
                
                foreach ($postos_capital as $post) {
                    $classe = $post['conferido'] ? 'confirmado' : '';
                    $checked = $post['conferido'] ? 'checked' : '';
                    echo "<tr data-lote='{$post['lote']}' data-codigo='{$post['codigo_barras']}' data-poupatempo='0' class='$classe'>";
                    echo "<td><input type='radio' name='salvar_{$post['lote']}' class='radio-conferencia' data-lote='{$post['lote']}' $checked></td>";
                    echo "<td>{$post['regional_str']}</td><td>{$post['lote']}</td><td>{$post['posto']}</td>";
                    echo "<td>{$post['data_expedicao']}</td><td>{$post['quantidade']}</td><td>{$post['codigo_barras']}</td>";
                    echo '</tr>';
                }
                echo '</tbody></table>';
            }
        }
        
        // 4. CENTRAL IIPR (Regional 999)
        if (isset($regionais_data[999])) {
            $total_central = count($regionais_data[999]);
            $conferidos_central = count(array_filter($regionais_data[999], function($p) { return $p['conferido']; }));
            
            echo "<h2>Central IIPR ($total_central pacotes no total / $conferidos_central conferidos)</h2>";
            echo '<table>';
            echo '<thead><tr>';
            echo '<th>Salvar</th><th>Regional</th><th>Lote</th><th>Posto</th><th>Data</th><th>Qtd</th><th>Código</th>';
            echo '</tr></thead><tbody>';
            
            foreach ($regionais_data[999] as $post) {
                $classe = $post['conferido'] ? 'confirmado' : '';
                $checked = $post['conferido'] ? 'checked' : '';
                echo "<tr data-lote='{$post['lote']}' data-codigo='{$post['codigo_barras']}' data-poupatempo='0' class='$classe'>";
                echo "<td><input type='radio' name='salvar_{$post['lote']}' class='radio-conferencia' data-lote='{$post['lote']}' $checked></td>";
                echo "<td>{$post['regional_str']}</td><td>{$post['lote']}</td><td>{$post['posto']}</td>";
                echo "<td>{$post['data_expedicao']}</td><td>{$post['quantidade']}</td><td>{$post['codigo_barras']}</td>";
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
                    
                    echo "<h2>$regional_str - Regional $regional_str ($total_reg pacotes no total / $conferidos_reg conferidos)</h2>";
                    echo '<table>';
                    echo '<thead><tr>';
                    echo '<th>Salvar</th><th>Regional</th><th>Lote</th><th>Posto</th><th>Data</th><th>Qtd</th><th>Código</th>';
                    echo '</tr></thead><tbody>';
                    
                    foreach ($postos_reg as $post) {
                        $classe = $post['conferido'] ? 'confirmado' : '';
                        $checked = $post['conferido'] ? 'checked' : '';
                        echo "<tr data-lote='{$post['lote']}' data-codigo='{$post['codigo_barras']}' data-poupatempo='0' class='$classe'>";
                        echo "<td><input type='radio' name='salvar_{$post['lote']}' class='radio-conferencia' data-lote='{$post['lote']}' $checked></td>";
                        echo "<td>{$post['regional_str']}</td><td>{$post['lote']}</td><td>{$post['posto']}</td>";
                        echo "<td>{$post['data_expedicao']}</td><td>{$post['quantidade']}</td><td>{$post['codigo_barras']}</td>";
                        echo '</tr>';
                    }
                    echo '</tbody></table>';
                }
            }
        }
    ?>
    </div>

    <!-- Áudios -->
    </div>

    <!-- Áudios -->
    <audio id="beep" src="beep.mp3" preload="auto"></audio>
    <audio id="concluido" src="concluido.mp3" preload="auto"></audio>
    <audio id="pacotejaconferido" src="pacotejaconferido.mp3" preload="auto"></audio>
    <audio id="pacotedeoutraregional" src="pacotedeoutraregional.mp3" preload="auto"></audio>
    <audio id="posto_poupatempo" src="posto_poupatempo.mp3" preload="auto"></audio>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const radioElements = document.querySelectorAll('.radio-conferencia');
        const autoSalvarRadio = document.getElementById('autoSalvar');
        const codigoBarrasInput = document.getElementById('codigo_barras');
        const beep = document.getElementById('beep');
        const concluido = document.getElementById('concluido');
        const pacoteJaConferido = document.getElementById('pacotejaconferido');
        const pacoteOutraRegional = document.getElementById('pacotedeoutraregional');
        const postoPoupaTempo = document.getElementById('posto_poupatempo');

        let regionalAtual = null;
        let linhasRegiaoAtual = [];

        // Listener para mudança de estado do radio auto-save
        radioElements.forEach(radio => {
            radio.addEventListener('change', function() {
                if (!autoSalvarRadio.checked) return; // Só salva se auto-save está ativo

                const lote = this.getAttribute('data-lote');
                const row = this.closest('tr');
                
                if (this.checked) {
                    // Marcar = Salvar e adicionar classe confirmado
                    row.classList.add('confirmado');
                    
                    fetch('', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'salvar_lote_ajax=1&lote=' + encodeURIComponent(lote)
                    })
                    .catch(error => console.error('Erro ao salvar:', error));
                } else {
                    // Desmarcar = Deletar e remover classe confirmado
                    row.classList.remove('confirmado');
                    
                    fetch('', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'excluir_lote_ajax=1&lote=' + encodeURIComponent(lote)
                    })
                    .catch(error => console.error('Erro ao excluir:', error));
                }
            });
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

            if (radio.checked) {
                // Já conferido
                pacoteJaConferido.currentTime = 0;
                pacoteJaConferido.play();
            } else if (isPoupaTempo && autoSalvarRadio.checked) {
                // Poupa Tempo alerta
                postoPoupaTempo.currentTime = 0;
                postoPoupaTempo.play();
            } else {
                // Marca como conferido
                radio.checked = true;
                radio.dispatchEvent(new Event('change'));
                
                beep.currentTime = 0;
                beep.play();

                // Centraliza a linha
                linha.scrollIntoView({ behavior: 'smooth', block: 'center' });

                // Verifica se todos os pacotes da região foram conferidos
                if (autoSalvarRadio.checked) {
                    setTimeout(() => {
                        const allRows = document.querySelectorAll('tbody tr');
                        const confirmedRows = document.querySelectorAll('tbody tr.confirmado');
                        
                        if (allRows.length === confirmedRows.length) {
                            concluido.currentTime = 0;
                            concluido.play();
                            regionalAtual = null;
                        }
                    }, 120);
                }
            }

            this.value = '';
            this.focus();
        });
    });
    </script>

</body>
</html>
