<?php
/* conferencia_pacotes.php — v8.16.5
 * Retorna ao modelo original com radio button pré-selecionado + auto-save
 * Mantém todas as funcionalidades:
 * 1) Radio button auto-selecionado salva automaticamente enquanto marca pacotes
 * 2) Desmarcar o radio = não salva mais
 * 3) Exclui conferências por data (input dd-mm-yyyy)
 * 4) Segregação correta de postos:
 *    - Postos Poupa Tempo aparecem PRIMEIRO com rótulo
 *    - Depois Posto(s) da Capital (regional 0)
 *    - Depois Central IIPR (regional 999)
 *    - Depois demais regionais
 * 5) Informação de pacotes: "N pacotes no total / M conferidos"
 * 6) Melhorias de som mantidas (v8.16.3+):
 *    - Som conclusão garantido para 1 pacote
 *    - Alerta Poupa Tempo específico
 */

// Inicializa as variáveis
$total_codigos = 0;
$datas_expedicao = array();
$regionais_data = array();
$datas_filtro = isset($_GET['datas']) ? $_GET['datas'] : array();
$poupaTempoPostos = array();

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

    // Busca dados dos postos
    $stmt = $pdo->query("SELECT lote, posto, regional, quantidade, dataCarga FROM ciPostosCsv ORDER BY regional, lote, posto");

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (empty($row['dataCarga'])) continue;

        $data_original = $row['dataCarga'];
        $data_formatada = date('d-m-Y', strtotime($data_original));

        if (!in_array($data_formatada, $datas_expedicao)) {
            $datas_expedicao[] = $data_formatada;
        }

        if (!empty($datas_filtro) && !in_array($data_formatada, $datas_filtro)) {
            continue;
        }

        $lote = $row['lote'];
        $posto = str_pad($row['posto'], 3, '0', STR_PAD_LEFT);
        $regional = $row['regional']; // Mantém como número
        $regional_str = str_pad($regional, 3, '0', STR_PAD_LEFT);
        $quantidade = str_pad($row['quantidade'], 5, '0', STR_PAD_LEFT);

        $nome_posto = "$posto - Posto $posto";
        $isPoupaTempo = in_array($posto, $poupaTempoPostos) ? '1' : '0';
        $codigo_barras = $lote . $regional_str . $posto . $quantidade;

        // Classifica por regional
        if (!isset($regionais_data[$regional])) {
            $regionais_data[$regional] = array();
        }

        $regionais_data[$regional][] = array(
            'regional_str' => $regional_str,
            'lote' => $lote,
            'posto' => $posto,
            'nome_posto' => $nome_posto,
            'data_expedicao' => $data_formatada,
            'quantidade' => ltrim($quantidade, '0'),
            'codigo_barras' => $codigo_barras,
            'isPoupaTempo' => $isPoupaTempo,
            'conferido' => in_array($lote, $conferencias_realizadas) ? 1 : 0
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
    <title>Conferência de Pacotes - v8.16.5</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f0f0f0; }
        .confirmado { background-color: #c6ffc6; }
        .filtro-datas { margin-bottom: 20px; }
        .filtro-datas form { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
        .filtro-datas label { margin-right: 10px; }
        .filtro-datas input[type="text"] { padding: 5px 10px; }
        .filtro-datas input[type="submit"] { padding: 5px 15px; cursor: pointer; }
        
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
    <h1>Conferência de Pacotes - v8.16.5</h1>

    <!-- Filtro de Datas -->
    <div class="filtro-datas">
        <form method="GET">
            <label for="datas">Filtrar por data(s):</label>
            <select name="datas[]" multiple size="5">
                <?php foreach ($datas_expedicao as $data): ?>
                    <option value="<?php echo $data; ?>" <?php echo (in_array($data, $datas_filtro) ? 'selected' : ''); ?>>
                        <?php echo $data; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="submit" value="Filtrar">
        </form>
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
        // Reordena regionais: Postos Poupa Tempo primeiro, depois Capital (0), depois Central IIPR (999), depois demais
        $order = array();
        
        // Primeiro, postos Poupa Tempo
        foreach ($regionais_data as $regional => $postos) {
            $tem_poupa_tempo = false;
            foreach ($postos as $p) {
                if ($p['isPoupaTempo'] == '1') {
                    $tem_poupa_tempo = true;
                    break;
                }
            }
            if ($tem_poupa_tempo) {
                $order['PT_' . $regional] = $regional; // Poupa Tempo da regional específica
            }
        }
        
        // Capital (regional 0)
        if (isset($regionais_data[0])) {
            $order['CAPITAL'] = 0;
        }
        
        // Central IIPR (regional 999)
        if (isset($regionais_data[999])) {
            $order['CENTRAL'] = 999;
        }
        
        // Demais regionais
        foreach (array_keys($regionais_data) as $regional) {
            if ($regional != 0 && $regional != 999) {
                $order['REG_' . $regional] = $regional;
            }
        }
        
        // Processa cada grupo
        foreach ($order as $key => $regional) {
            if ($regional === 0) {
                // Capital - postos não-Poupa Tempo
                $postos_capital = array_filter($regionais_data[0], function($p) {
                    return $p['isPoupaTempo'] != '1';
                });
                
                if (!empty($postos_capital)) {
                    $total_capital = count($postos_capital);
                    $conferidos_capital = count(array_filter($postos_capital, function($p) { return $p['conferido']; }));
                    
                    echo "<h2>Posto(s) da Capital ($total_capital pacotes no total / $conferidos_capital conferidos)</h2>";
                    echo '<table>';
                    echo '<thead>';
                    echo '<tr>';
                    echo '<th>Salvar</th>';
                    echo '<th>Regional</th>';
                    echo '<th>Número do Lote</th>';
                    echo '<th>Número e Nome do Posto</th>';
                    echo '<th>Data Expedição</th>';
                    echo '<th>Quantidade</th>';
                    echo '<th>Código de Barras</th>';
                    echo '</tr>';
                    echo '</thead>';
                    echo '<tbody>';
                    
                    foreach ($postos_capital as $post) {
                        $classe = $post['conferido'] ? 'confirmado' : '';
                        $checked = $post['conferido'] ? 'checked' : '';
                        echo "<tr data-lote='{$post['lote']}' data-codigo='{$post['codigo_barras']}' data-poupatempo='{$post['isPoupaTempo']}' class='$classe'>";
                        echo "<td><input type='radio' name='salvar_{$post['lote']}' class='radio-conferencia' data-lote='{$post['lote']}' $checked></td>";
                        echo "<td>{$post['regional_str']}</td>";
                        echo "<td>{$post['lote']}</td>";
                        echo "<td>{$post['nome_posto']}</td>";
                        echo "<td>{$post['data_expedicao']}</td>";
                        echo "<td>{$post['quantidade']}</td>";
                        echo "<td>{$post['codigo_barras']}</td>";
                        echo '</tr>';
                    }
                    
                    echo '</tbody>';
                    echo '</table>';
                }
            } elseif ($regional === 999) {
                // Central IIPR - todos os postos
                if (!empty($regionais_data[999])) {
                    $total_central = count($regionais_data[999]);
                    $conferidos_central = count(array_filter($regionais_data[999], function($p) { return $p['conferido']; }));
                    
                    echo "<h2>Central IIPR ($total_central pacotes no total / $conferidos_central conferidos)</h2>";
                    echo '<table>';
                    echo '<thead>';
                    echo '<tr>';
                    echo '<th>Salvar</th>';
                    echo '<th>Regional</th>';
                    echo '<th>Número do Lote</th>';
                    echo '<th>Número e Nome do Posto</th>';
                    echo '<th>Data Expedição</th>';
                    echo '<th>Quantidade</th>';
                    echo '<th>Código de Barras</th>';
                    echo '</tr>';
                    echo '</thead>';
                    echo '<tbody>';
                    
                    foreach ($regionais_data[999] as $post) {
                        $classe = $post['conferido'] ? 'confirmado' : '';
                        $checked = $post['conferido'] ? 'checked' : '';
                        echo "<tr data-lote='{$post['lote']}' data-codigo='{$post['codigo_barras']}' data-poupatempo='{$post['isPoupaTempo']}' class='$classe'>";
                        echo "<td><input type='radio' name='salvar_{$post['lote']}' class='radio-conferencia' data-lote='{$post['lote']}' $checked></td>";
                        echo "<td>{$post['regional_str']}</td>";
                        echo "<td>{$post['lote']}</td>";
                        echo "<td>{$post['nome_posto']}</td>";
                        echo "<td>{$post['data_expedicao']}</td>";
                        echo "<td>{$post['quantidade']}</td>";
                        echo "<td>{$post['codigo_barras']}</td>";
                        echo '</tr>';
                    }
                    
                    echo '</tbody>';
                    echo '</table>';
                }
            } elseif (strpos($key, 'PT_') === 0) {
                // Postos Poupa Tempo de uma regional específica
                $postos_pt = array_filter($regionais_data[$regional], function($p) {
                    return $p['isPoupaTempo'] == '1';
                });
                
                if (!empty($postos_pt)) {
                    $total_pt = count($postos_pt);
                    $conferidos_pt = count(array_filter($postos_pt, function($p) { return $p['conferido']; }));
                    
                    $regional_str = str_pad($regional, 3, '0', STR_PAD_LEFT);
                    echo "<h2>$regional_str - Regional $regional_str ($total_pt pacotes no total / $conferidos_pt conferidos) <span class='poupa-tempo-label'>POUPA TEMPO</span></h2>";
                    echo '<table>';
                    echo '<thead>';
                    echo '<tr>';
                    echo '<th>Salvar</th>';
                    echo '<th>Regional</th>';
                    echo '<th>Número do Lote</th>';
                    echo '<th>Número e Nome do Posto</th>';
                    echo '<th>Data Expedição</th>';
                    echo '<th>Quantidade</th>';
                    echo '<th>Código de Barras</th>';
                    echo '</tr>';
                    echo '</thead>';
                    echo '<tbody>';
                    
                    foreach ($postos_pt as $post) {
                        $classe = $post['conferido'] ? 'confirmado' : '';
                        $checked = $post['conferido'] ? 'checked' : '';
                        echo "<tr data-lote='{$post['lote']}' data-codigo='{$post['codigo_barras']}' data-poupatempo='{$post['isPoupaTempo']}' class='$classe'>";
                        echo "<td><input type='radio' name='salvar_{$post['lote']}' class='radio-conferencia' data-lote='{$post['lote']}' $checked></td>";
                        echo "<td>{$post['regional_str']}</td>";
                        echo "<td>{$post['lote']}</td>";
                        echo "<td>{$post['nome_posto']}</td>";
                        echo "<td>{$post['data_expedicao']}</td>";
                        echo "<td>{$post['quantidade']}</td>";
                        echo "<td>{$post['codigo_barras']}</td>";
                        echo '</tr>';
                    }
                    
                    echo '</tbody>';
                    echo '</table>';
                }
            } else {
                // Demais regionais - postos não-Poupa Tempo
                if (!empty($regionais_data[$regional])) {
                    $postos_reg = array_filter($regionais_data[$regional], function($p) {
                        return $p['isPoupaTempo'] != '1';
                    });
                    
                    if (!empty($postos_reg)) {
                        $total_reg = count($postos_reg);
                        $conferidos_reg = count(array_filter($postos_reg, function($p) { return $p['conferido']; }));
                        
                        $regional_str = str_pad($regional, 3, '0', STR_PAD_LEFT);
                        echo "<h2>$regional_str - Regional $regional_str ($total_reg pacotes no total / $conferidos_reg conferidos)</h2>";
                        echo '<table>';
                        echo '<thead>';
                        echo '<tr>';
                        echo '<th>Salvar</th>';
                        echo '<th>Regional</th>';
                        echo '<th>Número do Lote</th>';
                        echo '<th>Número e Nome do Posto</th>';
                        echo '<th>Data Expedição</th>';
                        echo '<th>Quantidade</th>';
                        echo '<th>Código de Barras</th>';
                        echo '</tr>';
                        echo '</thead>';
                        echo '<tbody>';
                        
                        foreach ($postos_reg as $post) {
                            $classe = $post['conferido'] ? 'confirmado' : '';
                            $checked = $post['conferido'] ? 'checked' : '';
                            echo "<tr data-lote='{$post['lote']}' data-codigo='{$post['codigo_barras']}' data-poupatempo='{$post['isPoupaTempo']}' class='$classe'>";
                            echo "<td><input type='radio' name='salvar_{$post['lote']}' class='radio-conferencia' data-lote='{$post['lote']}' $checked></td>";
                            echo "<td>{$post['regional_str']}</td>";
                            echo "<td>{$post['lote']}</td>";
                            echo "<td>{$post['nome_posto']}</td>";
                            echo "<td>{$post['data_expedicao']}</td>";
                            echo "<td>{$post['quantidade']}</td>";
                            echo "<td>{$post['codigo_barras']}</td>";
                            echo '</tr>';
                        }
                        
                        echo '</tbody>';
                        echo '</table>';
                    }
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
