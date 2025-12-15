<?php
/* conferencia_pacotes.php — v8.16.3
 * Melhorias:
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

// Conexão com o banco de dados
$host = '10.15.61.169';
$dbname = 'controle';
$user = 'controle_mat';
$pass = '375256';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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
        #resetar {
            margin-top: 15px;
            padding: 10px 20px;
            background-color: red;
            color: white;
            border: none;
            cursor: pointer;
        }
        #resetar:hover {
            background-color: darkred;
        }
    </style>
</head>
<body>

<h2>Conferência de Pacotes</h2>

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
         //foreach ($datas_expedicao as $data): ?>
        <?php foreach (array_slice($dataFormatada, -5) as $data): ?>
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

<input type="text" id="codigo_barras" placeholder="Escaneie o código de barras" maxlength="19" autofocus>
<button id="resetar">Resetar Conferência</button>

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
                    <tr data-codigo="<?php echo $posto['Código de Barras']; ?>" data-regional="<?php echo $regional; ?>" data-poupatempo="<?php echo $posto['PoupaTempo']; ?>">
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

<!-- Áudios -->
<audio id="beep" src="beep.mp3" preload="auto"></audio>
<audio id="concluido" src="concluido.mp3" preload="auto"></audio>
<audio id="pacotejaconferido" src="pacotejaconferido.mp3" preload="auto"></audio>
<audio id="pacotedeoutraregional" src="pacotedeoutraregional.mp3" preload="auto"></audio>
<audio id="posto_poupatempo" src="posto_poupatempo.mp3" preload="auto"></audio>

<script>
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
