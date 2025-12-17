<?php
/* conferencia_pacotes.php — v8.17.3
 * AGRUPAMENTO PERFEITO POR TIPO DE ENTREGA
 * 
 * Ordem de exibição:
 * 1. Postos Poupa Tempo (entrega='poupa-tempo' em ciRegionais)
 * 2. Postos do Posto 01 / Regional 01 (entrega='correios', regional=1)
 * 3. Postos da Capital (regional=0)
 * 4. Postos da Central IIPR (regional=999)
 * 5. Postos das Regionais (demais regionais agrupadas)
 * 
 * Formato: "650 - Regional 650 (9 pacotes / 9 conferidos)"
 * Auto-save AJAX + Sons diferenciados
 */

// Inicializa variáveis
$total_codigos = 0;
$datas_expedicao = array();
$regionais_data = array();
$datas_filtro = isset($_GET['datas']) ? $_GET['datas'] : array();
$poupaTempoPostos = array();
$conferencias = array();

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
        
        $sql = "INSERT INTO conferencia_pacotes (regional, nlote, nposto, dataexp, qtd, codbar, conf) 
                VALUES (?, ?, ?, ?, ?, ?, 's')
                ON DUPLICATE KEY UPDATE conf='s', qtd=VALUES(qtd), codbar=VALUES(codbar)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($regional, $lote, $posto, $dataexp, $qtd, $codbar));
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
        die(json_encode(array('success' => true)));
    }

    // v8.17.3: Busca postos com tipo de entrega (Poupa Tempo e Correios)
    $postosTipoEntrega = array(); // posto => 'poupatempo' ou 'correios'
    $sql = "SELECT LPAD(posto,3,'0') AS posto, LOWER(TRIM(REPLACE(entrega,' ',''))) AS tipo 
            FROM ciRegionais WHERE entrega IS NOT NULL LIMIT 500";
    $stmt = $pdo->query($sql);
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $tipo_limpo = $r['tipo'];
        if (strpos($tipo_limpo, 'poupa') !== false || strpos($tipo_limpo, 'tempo') !== false) {
            $postosTipoEntrega[$r['posto']] = 'poupatempo';
            $poupaTempoPostos[] = $r['posto'];
        } elseif (strpos($tipo_limpo, 'correio') !== false) {
            $postosTipoEntrega[$r['posto']] = 'correios';
        }
    }

    // Busca conferências já realizadas (com LIMIT)
    $stmt = $pdo->query("SELECT nlote, regional, nposto FROM conferencia_pacotes WHERE conf='s' LIMIT 5000");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $key = $row['nlote'] . '|' . str_pad($row['regional'], 3, '0', STR_PAD_LEFT) . '|' . str_pad($row['nposto'], 3, '0', STR_PAD_LEFT);
        $conferencias[$key] = 1;
    }

    // v8.17.2: Se nenhum filtro, carrega APENAS última data (rápido)
    if (empty($datas_filtro)) {
        $stmt = $pdo->query("SELECT DISTINCT DATE_FORMAT(dataCarga, '%d-%m-%Y') as data 
                             FROM ciPostosCsv 
                             WHERE dataCarga IS NOT NULL 
                             ORDER BY dataCarga DESC 
                             LIMIT 1");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $datas_filtro[] = $row['data'];
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
    if (!empty($datas_filtro)) {
        $placeholders = implode(',', array_fill(0, count($datas_filtro), '?'));
        
        // Converte datas para SQL
        $datas_sql = array();
        foreach ($datas_filtro as $df) {
            $partes = explode('-', $df);
            if (count($partes) == 3) {
                $datas_sql[] = $partes[2] . '-' . $partes[1] . '-' . $partes[0];
            }
        }
        
        if (!empty($datas_sql)) {
            $sql = "SELECT lote, posto, regional, quantidade, dataCarga 
                    FROM ciPostosCsv 
                    WHERE DATE(dataCarga) IN ($placeholders)
                    ORDER BY regional, lote, posto 
                    LIMIT 3000";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($datas_sql);

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (empty($row['dataCarga'])) continue;

                $data_formatada = date('d-m-Y', strtotime($row['dataCarga']));

                $lote = $row['lote'];
                $posto = str_pad($row['posto'], 3, '0', STR_PAD_LEFT);
                $regional = (int)$row['regional'];
                $regional_str = str_pad($regional, 3, '0', STR_PAD_LEFT);
                $quantidade = str_pad($row['quantidade'], 5, '0', STR_PAD_LEFT);

                $codigo_barras = $lote . $regional_str . $posto . $quantidade;
                $isPT = in_array($posto, $poupaTempoPostos) ? 1 : 0;
                $tipoEntrega = isset($postosTipoEntrega[$posto]) ? $postosTipoEntrega[$posto] : 'outros';
                
                // Verifica se já foi conferido
                $key = $lote . '|' . $regional_str . '|' . $posto;
                $conferido = isset($conferencias[$key]) ? 1 : 0;

                if (!isset($regionais_data[$regional])) {
                    $regionais_data[$regional] = array();
                }

                $regionais_data[$regional][] = array(
                    'lote' => $lote,
                    'posto' => $posto,
                    'regional' => $regional_str,
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
    <title>Conferência de Pacotes v8.17.2</title>
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
        
        .tag-pt {
            background: #e74c3c;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 700;
            margin-left: 8px;
        }
    </style>
</head>
<body>

<h2>📋 Conferência de Pacotes v8.17.2</h2>

<!-- Radio Auto-Save -->
<div class="radio-box">
    <label>
        <input type="radio" id="autoSalvar" checked>
        Auto-salvar conferências durante leitura
    </label>
</div>

<!-- Filtro de datas -->
<div class="filtro-datas">
    <form method="get" action="">
        <strong>📅 Filtrar por data(s):</strong>
        <?php 
        $dataFormatada = array();
        foreach($datas_expedicao as $dt){
            $dataFormatada[] = implode('-', array_reverse(explode('-', $dt)));
        }
        sort($dataFormatada);
        foreach (array_slice($dataFormatada, -5) as $data): ?>
            <label>
                <input type="checkbox" name="datas[]" value="<?php echo implode('-', array_reverse(explode('-', $data))); ?>" 
                    <?php echo (empty($datas_filtro) || in_array(implode('-', array_reverse(explode('-', $data))), $datas_filtro)) ? 'checked' : ''; ?>>
                <?php echo $data; ?>
            </label>
        <?php endforeach; ?>
        <input type="submit" value="🔍 Aplicar Filtro">
    </form>
</div>

<div class="info">
    📦 Total de pacotes: <strong><?php echo $total_codigos; ?></strong>
</div>

<div>
    <input type="text" id="codigo_barras" placeholder="Escaneie o código de barras (19 dígitos)" maxlength="19" autofocus>
    <button id="resetar">🔄 Resetar Conferência</button>
</div>

<!-- Tabelas Agrupadas -->
<div id="tabelas">
<?php
// ========================================
// v8.17.3: AGRUPAMENTO PERFEITO
// Ordem: PT → Regional 01 (Correios) → Capital → Central IIPR → Demais
// ========================================

// Separar dados por grupos
$grupo_pt = array();           // Poupa Tempo (tipoEntrega='poupatempo')
$grupo_r01_correios = array(); // Regional 01 COM entrega=correios
$grupo_capital = array();      // Regional 0
$grupo_999 = array();          // Central IIPR (999)
$grupo_outros = array();       // Demais regionais

foreach ($regionais_data as $regional => $postos) {
    foreach ($postos as $posto) {
        // 1. Poupa Tempo (PRIORIDADE MÁXIMA)
        if ($posto['tipoEntrega'] == 'poupatempo') {
            if (!isset($grupo_pt['PT'])) {
                $grupo_pt['PT'] = array();
            }
            $grupo_pt['PT'][] = $posto;
        }
        // 2. Regional 01 com entrega=correios
        elseif ($regional == 1 && $posto['tipoEntrega'] == 'correios') {
            if (!isset($grupo_r01_correios['R01'])) {
                $grupo_r01_correios['R01'] = array();
            }
            $grupo_r01_correios['R01'][] = $posto;
        }
        // 3. Capital (regional = 0)
        elseif ($regional == 0) {
            if (!isset($grupo_capital['CAP'])) {
                $grupo_capital['CAP'] = array();
            }
            $grupo_capital['CAP'][] = $posto;
        }
        // 4. Central IIPR (regional = 999)
        elseif ($regional == 999) {
            if (!isset($grupo_999['IIPR'])) {
                $grupo_999['IIPR'] = array();
            }
            $grupo_999['IIPR'][] = $posto;
        }
        // 5. Demais regionais
        else {
            if (!isset($grupo_outros[$regional])) {
                $grupo_outros[$regional] = array();
            }
            $grupo_outros[$regional][] = $posto;
        }
    }
}

// v8.17.3: Função para renderizar tabela COM CONTADORES
function renderizarTabela($titulo, $dados, $ehPoupaTempo = false) {
    if (empty($dados)) {
        return;
    }
    
    // Conta total de pacotes e conferidos
    $total_pacotes = 0;
    $total_conferidos = 0;
    foreach ($dados as $grupo => $postos) {
        foreach ($postos as $posto) {
            $total_pacotes++;
            if ($posto['conf'] == 1) {
                $total_conferidos++;
            }
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
    
    foreach ($dados as $regional => $postos) {
        foreach ($postos as $posto) {
            $classeConf = ($posto['conf'] == 1) ? ' confirmado' : '';
            echo '<tr class="linha-conferencia' . $classeConf . '" ';
            echo 'data-codigo="' . htmlspecialchars($posto['codigo'], ENT_QUOTES, 'UTF-8') . '" ';
            echo 'data-regional="' . htmlspecialchars($posto['regional'], ENT_QUOTES, 'UTF-8') . '" ';
            echo 'data-lote="' . htmlspecialchars($posto['lote'], ENT_QUOTES, 'UTF-8') . '" ';
            echo 'data-posto="' . htmlspecialchars($posto['posto'], ENT_QUOTES, 'UTF-8') . '" ';
            echo 'data-data="' . htmlspecialchars($posto['data'], ENT_QUOTES, 'UTF-8') . '" ';
            echo 'data-qtd="' . htmlspecialchars($posto['qtd'], ENT_QUOTES, 'UTF-8') . '" ';
            echo 'data-ispt="' . $posto['isPT'] . '">';
            echo '<td>' . htmlspecialchars($posto['regional'], ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars($posto['lote'], ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars($posto['posto'], ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars($posto['data'], ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars($posto['qtd'], ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars($posto['codigo'], ENT_QUOTES, 'UTF-8') . '</td>';
            echo '</tr>';
        }
    }
    
    echo '</tbody></table>';
}

// v8.17.3: Renderizar na ordem correta com títulos apropriados
if (!empty($grupo_pt)) {
    renderizarTabela('Postos Poupa Tempo', $grupo_pt, true);
}
if (!empty($grupo_r01_correios)) {
    renderizarTabela('001 - Postos do Posto 01 (Correios)', $grupo_r01_correios);
}
if (!empty($grupo_capital)) {
    renderizarTabela('000 - Postos da Capital', $grupo_capital);
}
if (!empty($grupo_999)) {
    renderizarTabela('999 - Postos da Central IIPR', $grupo_999);
}
if (!empty($grupo_outros)) {
    ksort($grupo_outros);
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
// v8.17.1: JavaScript com AJAX auto-save
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
    
    var regionalAtual = null;
    
    input.focus();
    
    // Função para salvar conferência via AJAX
    function salvarConferencia(lote, regional, posto, dataexp, qtd, codbar) {
        var formData = new FormData();
        formData.append('salvar_lote_ajax', '1');
        formData.append('lote', lote);
        formData.append('regional', regional);
        formData.append('posto', posto);
        formData.append('dataexp', dataexp);
        formData.append('qtd', qtd);
        formData.append('codbar', codbar);
        
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
        
        var regionalDoPacote = linha.getAttribute("data-regional");
        var isPoupaTempo = linha.getAttribute("data-ispt") === "1";
        
        // Verifica se já foi conferido
        if (linha.classList.contains("confirmado")) {
            pacoteJaConferido.play();
            input.value = "";
            return;
        }
        
        // Define regional atual se não estiver definido
        if (!regionalAtual) {
            regionalAtual = regionalDoPacote;
        }
        
        // Verifica se o pacote é de outra regional
        if (regionalDoPacote !== regionalAtual) {
            pacoteOutraRegional.play();
            input.value = "";
            return;
        }
        
        // Marca como conferido
        linha.classList.add("confirmado");
        
        // Toca áudio diferenciado para Poupa Tempo
        if (isPoupaTempo) {
            postoPoupaTempo.play();
        } else {
            beep.play();
        }
        
        input.value = "";
        
        // Centraliza a linha na tela
        linha.scrollIntoView({ behavior: "smooth", block: "center" });
        
        // Salvar no banco se auto-save estiver ativo
        if (radioAutoSalvar.checked) {
            var lote = linha.getAttribute("data-lote");
            var regional = linha.getAttribute("data-regional");
            var posto = linha.getAttribute("data-posto");
            var dataexp = linha.getAttribute("data-data");
            var qtd = linha.getAttribute("data-qtd");
            var codbar = linha.getAttribute("data-codigo");
            
            salvarConferencia(lote, regional, posto, dataexp, qtd, codbar);
        }
        
        // Verifica se completou a regional
        var linhasDaRegional = document.querySelectorAll('tr[data-regional="' + regionalAtual + '"]');
        var conferidos = [];
        
        for (var i = 0; i < linhasDaRegional.length; i++) {
            if (linhasDaRegional[i].classList.contains("confirmado")) {
                conferidos.push(linhasDaRegional[i]);
            }
        }
        
        if (conferidos.length === linhasDaRegional.length) {
            concluido.play();
            regionalAtual = null;
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
});
</script>

</body>
</html>
