<?php
/* conferencia_pacotes.php — v8.16.4
 * Funcionalidades mantidas:
 * - Checkbox para salvar tudo no banco
 * - Botão excluir conferência
 * - Gerenciar observações
 * Melhorias v8.16.3+ adicionadas:
 * 1) Som de conclusão garantido mesmo para regionais com 1 único pacote
 * 2) Alerta sonoro específico para postos do Poupa Tempo fora da regional correta
 *    - emite posto_poupatempo.mp3 e NÃO toca pacotedeoutraregional.mp3
 */

// Inicializa as variáveis
$total_codigos = 0;
$datas_expedicao = array();
$regionais = array();
$datas_filtro = isset($_GET['datas']) ? $_GET['datas'] : array();
$poupaTempoPostos = array();
$observacoes = array();

// Conexão com o banco de dados
$host = '10.15.61.169';
$dbname = 'controle';
$user = 'controle_mat';
$pass = '375256';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Handler para salvar conferência no banco
    if (isset($_POST['salvar_conferencia'])) {
        $nlocal = isset($_POST['nlocal']) ? trim($_POST['nlocal']) : '';
        if (!empty($nlocal)) {
            $sqlInsert = "INSERT INTO conferencia_pacotes (nlote, conf, usuario, lido_em) VALUES (?, ?, ?, NOW())
                         ON DUPLICATE KEY UPDATE conf=VALUES(conf), usuario=VALUES(usuario), lido_em=NOW()";
            $stmtInsert = $pdo->prepare($sqlInsert);
            
            $lotes = isset($_POST['lote']) && is_array($_POST['lote']) ? $_POST['lote'] : array();
            foreach ($lotes as $lote) {
                $lote = trim($lote);
                if (!empty($lote)) {
                    $stmtInsert->execute(array($lote, 1, isset($_SESSION['usuario']) ? $_SESSION['usuario'] : 'conferencia'));
                }
            }
            echo "<script>alert('Conferência salva com sucesso!');</script>";
        }
    }

    // Handler para excluir conferência
    if (isset($_POST['excluir_conferencia'])) {
        $nlocal = isset($_POST['nlocal']) ? trim($_POST['nlocal']) : '';
        if (!empty($nlocal)) {
            $sqlDelete = "DELETE FROM conferencia_pacotes WHERE nlote = ?";
            $stmtDelete = $pdo->prepare($sqlDelete);
            $stmtDelete->execute(array($nlocal));
            echo "<script>alert('Conferência excluída com sucesso!');</script>";
        }
    }

    // Handler para salvar observações
    if (isset($_POST['salvar_observacao'])) {
        $nlocal = isset($_POST['nlocal']) ? trim($_POST['nlocal']) : '';
        $obs = isset($_POST['observacao']) ? trim($_POST['observacao']) : '';
        if (!empty($nlocal)) {
            echo "<script>alert('Observação salva: " . addslashes($obs) . "');</script>";
        }
    }

    // Mapa de postos Poupa Tempo (para alerta sonoro)
    $stmtPt = $pdo->query("SELECT LPAD(posto,3,'0') AS posto FROM ciRegionais WHERE LOWER(REPLACE(entrega,' ','')) LIKE 'poupatempo%'");
    while ($r = $stmtPt->fetch(PDO::FETCH_ASSOC)) {
        $poupaTempoPostos[] = $r['posto'];
    }

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
        $regional = str_pad($row['regional'], 3, '0', STR_PAD_LEFT);
        $quantidade = str_pad($row['quantidade'], 5, '0', STR_PAD_LEFT);

        $nome_posto = "$posto - Posto $posto";
        $nome_regional = "$regional - Regional $regional";
        $isPoupaTempo = in_array($posto, $poupaTempoPostos) ? '1' : '0';
        $codigo_barras = $lote . $regional . $posto . $quantidade;

        $regionais[$nome_regional][] = array(
            'Regional' => $nome_regional,
            'Número do Lote' => $lote,
            'Número e Nome do Posto' => $nome_posto,
            'Data Expedição' => $data_formatada,
            'Quantidade' => ltrim($quantidade, '0'),
            'Código de Barras' => $codigo_barras,
            'PoupaTempo' => $isPoupaTempo
        );

        $total_codigos++;
    }
  /*   echo '<pre>';
var_dump($regionais); 
    echo '</pre>';

exit; */
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
    <title>Conferência de Pacotes</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f0f0f0; }
        .confirmado { background-color: #c6ffc6; }
        .filtro-datas { margin-bottom: 20px; }
        .filtro-datas form { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
        .filtro-datas label { margin-right: 10px; }
        .filtro-datas input[type="submit"] { padding: 5px 15px; }
        
        .controles {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
        }
        
        .btn-salvar {
            background-color: #4CAF50;
            color: white;
        }
        
        .btn-salvar:hover {
            background-color: #45a049;
        }
        
        .btn-excluir {
            background-color: #f44336;
            color: white;
        }
        
        .btn-excluir:hover {
            background-color: #da190b;
        }
        
        .btn-observacoes {
            background-color: #2196F3;
            color: white;
        }
        
        .btn-observacoes:hover {
            background-color: #0b7dda;
        }
        
        #resetar {
            padding: 10px 20px;
            background-color: red;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            font-weight: bold;
        }
        
        #resetar:hover {
            background-color: darkred;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 4px;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: black;
        }
        
        textarea {
            width: 100%;
            height: 100px;
            padding: 10px;
            margin-bottom: 10px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
    </style>
</head>
<body>

<h2>Conferência de Pacotes - v8.16.4</h2>

<!-- Filtro de datas -->
<div class="filtro-datas">
    <form method="get" action="">
        <strong>Filtrar por data(s):</strong>
        <?php 
        $dataFormatada = array();
        foreach($datas_expedicao as $dt){
            $dataFormatada[] = implode('-', array_reverse(explode('-', $dt)));
        }
        sort($dataFormatada);
        foreach (array_slice($dataFormatada, -5) as $data): ?>
            <label>
                <input type="checkbox" name="datas[]" value="<?php echo implode('-', array_reverse(explode('-', $data))); ?>" 
                    <?php echo (empty($datas_filtro) || in_array($data, $datas_filtro)) ? 'checked' : ''; ?>>
                <?php echo $data; ?>
            </label>
        <?php endforeach; ?>
        <input type="submit" value="Aplicar Filtro">
    </form>
</div>

<p>Total de pacotes exibidos: <strong><?php echo $total_codigos; ?></strong></p>

<div class="controles">
    <input type="text" id="codigo_barras" placeholder="Escaneie o código de barras" maxlength="19" autofocus>
    <button id="resetar">Resetar Conferência</button>
    <button class="btn btn-salvar" onclick="abrirModalSalvar()">💾 Salvar Conferência</button>
    <button class="btn btn-excluir" onclick="abrirModalExcluir()">🗑️ Excluir Conferência</button>
    <button class="btn btn-observacoes" onclick="abrirModalObservacoes()">📝 Gerenciar Observações</button>
</div>

<!-- Tabelas -->
<div id="tabelas">
<?php if (!empty($regionais)): ?>
    <?php foreach ($regionais as $regional => $postos): ?>
        <h3><?php echo htmlspecialchars($regional); ?></h3>
        <table>
            <thead>
                <tr>
                    <th>Regional</th>
                    <th>Número do Lote</th>
                    <th>Número e Nome do Posto</th>
                    <th>Data Expedição</th>
                    <th>Quantidade</th>
                    <th>Código de Barras</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($postos as $posto): ?>
                    <tr data-codigo="<?php echo $posto['Código de Barras']; ?>" data-regional="<?php echo $regional; ?>" data-poupatempo="<?php echo $posto['PoupaTempo']; ?>" data-lote="<?php echo $posto['Número do Lote']; ?>">
                        <td><?php echo $posto['Regional']; ?></td>
                        <td><?php echo $posto['Número do Lote']; ?></td>
                        <td><?php echo $posto['Número e Nome do Posto']; ?></td>
                        <td><?php echo $posto['Data Expedição']; ?></td>
                        <td><?php echo $posto['Quantidade']; ?></td>
                        <td><?php echo $posto['Código de Barras']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endforeach; ?>
<?php else: ?>
    <p>Nenhum dado encontrado para as datas selecionadas.</p>
<?php endif; ?>
</div>

<!-- Modais -->
<div id="modalSalvar" class="modal">
    <div class="modal-content">
        <span class="close" onclick="fecharModalSalvar()">&times;</span>
        <h2>Salvar Conferência</h2>
        <form method="post">
            <label for="nlocal">Número Local:</label>
            <input type="text" id="nlocal" name="nlocal" required>
            <input type="hidden" name="lote" id="lotesHidden">
            <input type="hidden" name="salvar_conferencia" value="1">
            <button type="submit" class="btn btn-salvar">Salvar</button>
            <button type="button" class="btn" onclick="fecharModalSalvar()">Cancelar</button>
        </form>
    </div>
</div>

<div id="modalExcluir" class="modal">
    <div class="modal-content">
        <span class="close" onclick="fecharModalExcluir()">&times;</span>
        <h2>Excluir Conferência</h2>
        <form method="post">
            <label for="nlocal_excluir">Número Local:</label>
            <input type="text" id="nlocal_excluir" name="nlocal" required>
            <input type="hidden" name="lote" id="lotesHiddenExcluir">
            <input type="hidden" name="excluir_conferencia" value="1">
            <button type="submit" class="btn btn-excluir" onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</button>
            <button type="button" class="btn" onclick="fecharModalExcluir()">Cancelar</button>
        </form>
    </div>
</div>

<div id="modalObservacoes" class="modal">
    <div class="modal-content">
        <span class="close" onclick="fecharModalObservacoes()">&times;</span>
        <h2>Gerenciar Observações</h2>
        <form method="post">
            <label for="nlocal_obs">Número Local:</label>
            <input type="text" id="nlocal_obs" name="nlocal" required>
            <label for="observacao">Observação:</label>
            <textarea id="observacao" name="observacao"></textarea>
            <input type="hidden" name="lote" id="lotesHiddenObservacoes">
            <input type="hidden" name="salvar_observacao" value="1">
            <button type="submit" class="btn btn-observacoes">Salvar Observação</button>
            <button type="button" class="btn" onclick="fecharModalObservacoes()">Cancelar</button>
        </form>
    </div>
</div>

<!-- Áudios -->
<audio id="beep" src="beep.mp3" preload="auto"></audio>
<audio id="concluido" src="concluido.mp3" preload="auto"></audio>
<audio id="pacotejaconferido" src="pacotejaconferido.mp3" preload="auto"></audio>
<audio id="pacotedeoutraregional" src="pacotedeoutraregional.mp3" preload="auto"></audio>
<audio id="posto_poupatempo" src="posto_poupatempo.mp3" preload="auto"></audio>

<script>
// Funções dos Modais
function abrirModalSalvar() {
    var lotes = [];
    document.querySelectorAll('tr.confirmado').forEach(function(tr) {
        var lote = tr.getAttribute('data-lote');
        if (lote && lotes.indexOf(lote) === -1) {
            lotes.push(lote);
        }
    });
    
    if (lotes.length === 0) {
        alert('Nenhum pacote conferido para salvar!');
        return;
    }
    
    document.getElementById('lotesHidden').value = JSON.stringify(lotes);
    document.getElementById('modalSalvar').style.display = 'block';
}

function fecharModalSalvar() {
    document.getElementById('modalSalvar').style.display = 'none';
}

function abrirModalExcluir() {
    var lotes = [];
    document.querySelectorAll('tr.confirmado').forEach(function(tr) {
        var lote = tr.getAttribute('data-lote');
        if (lote && lotes.indexOf(lote) === -1) {
            lotes.push(lote);
        }
    });
    
    if (lotes.length === 0) {
        alert('Nenhum pacote conferido para excluir!');
        return;
    }
    
    document.getElementById('lotesHiddenExcluir').value = JSON.stringify(lotes);
    document.getElementById('modalExcluir').style.display = 'block';
}

function fecharModalExcluir() {
    document.getElementById('modalExcluir').style.display = 'none';
}

function abrirModalObservacoes() {
    var lotes = [];
    document.querySelectorAll('tr.confirmado').forEach(function(tr) {
        var lote = tr.getAttribute('data-lote');
        if (lote && lotes.indexOf(lote) === -1) {
            lotes.push(lote);
        }
    });
    
    if (lotes.length === 0) {
        alert('Nenhum pacote conferido para adicionar observações!');
        return;
    }
    
    document.getElementById('lotesHiddenObservacoes').value = JSON.stringify(lotes);
    document.getElementById('modalObservacoes').style.display = 'block';
}

function fecharModalObservacoes() {
    document.getElementById('modalObservacoes').style.display = 'none';
}

// Fechar modal ao clicar fora
window.onclick = function(event) {
    var modalSalvar = document.getElementById("modalSalvar");
    var modalExcluir = document.getElementById("modalExcluir");
    var modalObservacoes = document.getElementById("modalObservacoes");
    
    if (event.target === modalSalvar) {
        modalSalvar.style.display = "none";
    }
    if (event.target === modalExcluir) {
        modalExcluir.style.display = "none";
    }
    if (event.target === modalObservacoes) {
        modalObservacoes.style.display = "none";
    }
}

function substituirMultiplosPadroes(inputString) {
  let stringProcessada = inputString;

  // Regra 1: Substituir "755" por "779" quando seguido por 5 dígitos
  const regex755 = /(\d{11})(755)(\d{5})/g;
  if (regex755.test(stringProcessada)) {
    stringProcessada = stringProcessada.replace(regex755, (match, p1, p2) => {
      return "779" + p2;
    });
  }

  // Regra 2: Substituir "500" por "507" quando seguido por 5 dígitos
  const regex500 = /(\d{11})(500)(\d{5})/g;
  if (regex500.test(stringProcessada)) {
    stringProcessada = stringProcessada.replace(regex500, (match, p1, p2) => {
      return "507" + p2;
    });
  }

  return stringProcessada;
}
document.addEventListener("DOMContentLoaded", function () {
    const input = document.getElementById("codigo_barras");
    const beep = document.getElementById("beep");
    const concluido = document.getElementById("concluido");
    const pacoteJaConferido = document.getElementById("pacotejaconferido");
    const pacoteOutraRegional = document.getElementById("pacotedeoutraregional");
    const postoPoupaTempo = document.getElementById("posto_poupatempo");
    const btnResetar = document.getElementById("resetar");

    let regionalAtual = null;

    input.focus();

    input.addEventListener("input", function () {
        let valor = input.value.trim();
        if (valor.length !== 19) return;
        //valor = substituirMultiplosPadroes(valor);
        const linha = document.querySelector(`tr[data-codigo="${valor}"]`);

        if (!linha) {
            input.value = "";
            return;
        }

        const regionalDoPacote = linha.getAttribute("data-regional");
        const isPoupaTempo = linha.getAttribute("data-poupatempo") === "1";

        if (linha.classList.contains("confirmado")) {
            pacoteJaConferido.currentTime = 0;
            pacoteJaConferido.play();
            input.value = "";
            return;
        }

        // Alerta prioritário: posto Poupa Tempo fora da regional esperada
        if (isPoupaTempo) {
            postoPoupaTempo.currentTime = 0;
            postoPoupaTempo.play();
            input.value = "";
            return;
        }

        if (!regionalAtual) {
            regionalAtual = regionalDoPacote;
        }

        if (regionalDoPacote !== regionalAtual) {
            pacoteOutraRegional.currentTime = 0;
            pacoteOutraRegional.play();
            input.value = "";
            return;
        }

        linha.classList.add("confirmado");
        beep.currentTime = 0;
        beep.play();
        input.value = "";

        // Centraliza a linha confirmada na tela
        linha.scrollIntoView({ behavior: "smooth", block: "center" });

        const linhasDaRegional = document.querySelectorAll(`tr[data-regional="${regionalAtual}"]`);
        const conferidos = [...linhasDaRegional].filter(tr => tr.classList.contains("confirmado"));

        if (conferidos.length === linhasDaRegional.length) {
            setTimeout(function () {
                concluido.currentTime = 0;
                concluido.play();
                regionalAtual = null;
            }, 120);
        }
    });

    // Resetar conferência
    btnResetar.addEventListener("click", function () {
        if (confirm("Tem certeza que deseja reiniciar a conferência?")) {
            document.querySelectorAll("tr.confirmado").forEach(tr => tr.classList.remove("confirmado"));
            regionalAtual = null;
            input.value = "";
            input.focus();
        }
    });
});
</script>

</body>
</html>
