<?php
header('Cache-Control: no-cache, no-store, must-revalidate');
session_start();

define('DB_HOST', '10.15.61.169');
define('DB_NAME', 'controle');
define('DB_USER', 'controle_mat');
define('DB_PASS', '375256');

function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function formatarDataBr($data) {
    $data = trim((string)$data);
    if ($data === '') return '';
    $dt = DateTime::createFromFormat('Y-m-d', $data);
    if ($dt === false) return $data;
    return $dt->format('d-m-Y');
}

$dbOk = false;
$mensagem = '';
$mensagem_tipo = '';
$consulta_msg = '';
$consulta_tipo = '';
$consulta_dados = array();
$responsavel_salvo = isset($_SESSION['ultimo_responsavel_devolucao']) ? trim((string)$_SESSION['ultimo_responsavel_devolucao']) : '';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $dbOk = true;
} catch (Exception $ex) {
    $mensagem = 'Falha ao conectar no banco.';
    $mensagem_tipo = 'erro';
}

if ($dbOk && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = isset($_POST['acao']) ? trim((string)$_POST['acao']) : '';

    if ($acao === 'consultar') {
        $consulta_raw = isset($_POST['consulta_leitura']) ? trim($_POST['consulta_leitura']) : '';
        $consulta = preg_replace('/\D+/', '', $consulta_raw);
        if (strlen($consulta) !== 35) {
            $consulta_msg = 'Etiqueta invalida. Informe 35 digitos.';
            $consulta_tipo = 'erro';
        } else {
            $stmtEnvio = $pdo->prepare('SELECT data, login, posto FROM ciMalotes WHERE leitura = ? AND tipo = 1 ORDER BY data DESC, id DESC LIMIT 1');
            $stmtEnvio->execute(array($consulta));
            $envio = $stmtEnvio->fetch();

            $stmtDev = $pdo->prepare('SELECT data, login, posto FROM ciMalotes WHERE leitura = ? AND tipo = 2 ORDER BY data DESC, id DESC LIMIT 1');
            $stmtDev->execute(array($consulta));
            $devolucao = $stmtDev->fetch();

            if ($devolucao) {
                $consulta_tipo = 'ok';
                $consulta_msg = 'Etiqueta devolvida.';
                $consulta_dados = array(
                    'status' => 'Devolvida',
                    'data' => formatarDataBr($devolucao['data']),
                    'responsavel' => $devolucao['login'],
                    'posto' => $devolucao['posto']
                );
            } elseif ($envio) {
                $consulta_tipo = 'aviso';
                $consulta_msg = 'Etiqueta em transito.';
                $consulta_dados = array(
                    'status' => 'Em transito',
                    'data' => formatarDataBr($envio['data']),
                    'responsavel' => $envio['login'],
                    'posto' => $envio['posto']
                );
            } else {
                $consulta_tipo = 'erro';
                $consulta_msg = 'Etiqueta nao encontrada.';
            }
        }
    } else {
        $leitura_raw = isset($_POST['leitura']) ? trim($_POST['leitura']) : '';
        $responsavel = isset($_POST['responsavel']) ? trim($_POST['responsavel']) : '';
        $posto_raw = isset($_POST['posto']) ? trim($_POST['posto']) : '';

        if ($responsavel === '') {
            $mensagem = 'Informe o responsavel.';
            $mensagem_tipo = 'erro';
        } else {
            $leitura = preg_replace('/\D+/', '', $leitura_raw);
            if (strlen($leitura) !== 35) {
                $mensagem = 'Etiqueta invalida. Informe 35 digitos.';
                $mensagem_tipo = 'erro';
            } else {
                $cep = substr($leitura, 0, 8);
                $sequencial = substr($leitura, -5);
                $posto = preg_replace('/\D+/', '', $posto_raw);
                if ($posto !== '') {
                    $posto = str_pad($posto, 3, '0', STR_PAD_LEFT);
                } else {
                    $posto = null;
                    try {
                        $stmtPosto = $pdo->prepare('SELECT posto FROM cadastroMalotes WHERE leitura = ? ORDER BY id DESC LIMIT 1');
                        $stmtPosto->execute(array($leitura));
                        $postoDb = $stmtPosto->fetchColumn();
                        if ($postoDb !== false && $postoDb !== null && $postoDb !== '') {
                            $posto = str_pad(preg_replace('/\D+/', '', (string)$postoDb), 3, '0', STR_PAD_LEFT);
                        }
                    } catch (Exception $e) {
                        $posto = null;
                    }
                }

                $stmtIns = $pdo->prepare('INSERT INTO ciMalotes (leitura, data, observacao, login, tipo, cep, sequencial, posto)
                                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                $stmtIns->execute(array(
                    $leitura,
                    date('Y-m-d'),
                    null,
                    $responsavel,
                    2,
                    $cep,
                    $sequencial,
                    $posto
                ));

                $_SESSION['ultimo_responsavel_devolucao'] = $responsavel;
                $responsavel_salvo = $responsavel;

                $mensagem = 'Devolucao registrada com sucesso.';
                $mensagem_tipo = 'ok';
            }
        }
    }
}

$contagem = array('transito' => 0, 'devolvidos' => 0, 'envios' => 0);
$recentes = array();
if ($dbOk) {
    $stmtEnvios = $pdo->query("SELECT COUNT(DISTINCT leitura) AS total FROM ciMalotes WHERE tipo = 1");
    $contagem['envios'] = (int)$stmtEnvios->fetchColumn();

    $stmtDevolvidos = $pdo->query("SELECT COUNT(DISTINCT leitura) AS total FROM ciMalotes WHERE tipo = 2");
    $contagem['devolvidos'] = (int)$stmtDevolvidos->fetchColumn();

    $stmtTransito = $pdo->query("SELECT COUNT(DISTINCT m1.leitura) AS total\n                                 FROM ciMalotes m1\n                                 WHERE m1.tipo = 1\n                                   AND NOT EXISTS (\n                                       SELECT 1\n                                       FROM ciMalotes m2\n                                       WHERE m2.leitura = m1.leitura AND m2.tipo = 2\n                                   )");
    $contagem['transito'] = (int)$stmtTransito->fetchColumn();

    $stmtRecentes = $pdo->query("SELECT leitura, data, login, posto FROM ciMalotes WHERE tipo = 2 ORDER BY data DESC, id DESC LIMIT 20");
    $recentes = $stmtRecentes->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devolucao de Etiquetas v0.9.25.0 - Projeto Lacres</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: "Trebuchet MS", "Segoe UI", Arial, sans-serif;
            background: linear-gradient(135deg, #f1f4f8 0%, #e6eef6 100%);
            color: #222;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .card {
            width: 100%;
            max-width: 760px;
            background: #fff;
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.12);
        }
        h1 {
            font-size: 22px;
            margin-bottom: 6px;
            color: #0b3d91;
        }
        .sub {
            font-size: 13px;
            color: #555;
            margin-bottom: 18px;
        }
        .painel {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            margin-bottom: 18px;
        }
        .kpi {
            background: #f7f9fb;
            border: 1px solid #e3e7ec;
            border-radius: 12px;
            padding: 12px 14px;
        }
        .kpi .label { font-size: 12px; color: #666; margin-bottom: 6px; }
        .kpi .valor { font-size: 20px; font-weight: 700; color: #1d2b53; }
        form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        label {
            font-size: 12px;
            color: #666;
            display: block;
            margin-bottom: 6px;
        }
        input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #d8dde3;
            border-radius: 10px;
            font-size: 14px;
        }
        .full { grid-column: span 2; }
        .btn {
            background: linear-gradient(135deg, #ef6c00 0%, #ff9800 100%);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-weight: 700;
            padding: 12px 16px;
            cursor: pointer;
            box-shadow: 0 6px 14px rgba(0,0,0,0.14);
        }
        .btn-sec {
            background: linear-gradient(135deg, #1e88e5 0%, #42a5f5 100%);
        }
        .msg {
            margin-top: 12px;
            font-size: 13px;
        }
        .msg.ok { color: #2e7d32; }
        .msg.erro { color: #c62828; }
        .msg.aviso { color: #ef6c00; }
        .secao {
            margin-top: 18px;
            padding-top: 12px;
            border-top: 1px solid #edf0f4;
        }
        .secao h2 {
            font-size: 16px;
            color: #1d2b53;
            margin-bottom: 10px;
        }
        .status-box {
            background: #f7f9fb;
            border: 1px solid #e3e7ec;
            border-radius: 12px;
            padding: 12px 14px;
            margin-top: 10px;
        }
        .status-box.ok { border-color: #c8e6c9; }
        .status-box.aviso { border-color: #ffe0b2; }
        .status-box.erro { border-color: #ffcdd2; }
        .status-title { font-weight: 700; margin-bottom: 6px; }
        .tabela {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        .tabela th, .tabela td {
            padding: 8px 6px;
            border-bottom: 1px solid #eef1f5;
            text-align: left;
        }
        .tabela th { color: #444; font-weight: 700; }
        .tabela .mono { font-family: "Courier New", Courier, monospace; }
        .top-actions {
            margin-top: 14px;
        }
        .voltar {
            display: inline-block;
            margin-top: 12px;
            font-size: 12px;
            color: #0b3d91;
            text-decoration: none;
        }
        @media (max-width: 600px) {
            .card { padding: 20px; }
            form { grid-template-columns: 1fr; }
            .full { grid-column: span 1; }
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Devolucao de etiquetas (Correios) v0.9.25.0</h1>
        <div class="sub">Leia a etiqueta devolvida para registrar o retorno (tipo = 2).</div>

        <div class="painel">
            <div class="kpi">
                <div class="label">Em transito (tipo 1 sem devolucao)</div>
                <div class="valor"><?php echo (int)$contagem['transito']; ?></div>
            </div>
            <div class="kpi">
                <div class="label">Devolvidos (tipo 2)</div>
                <div class="valor"><?php echo (int)$contagem['devolvidos']; ?></div>
            </div>
            <div class="kpi">
                <div class="label">Etiquetas enviadas (tipo 1)</div>
                <div class="valor"><?php echo (int)$contagem['envios']; ?></div>
            </div>
        </div>

        <form method="post" action="">
            <input type="hidden" name="acao" value="devolver">
            <div class="full">
                <label for="leitura">Leitura da etiqueta (35 digitos)</label>
                <input type="text" id="leitura" name="leitura" autocomplete="off" autofocus>
            </div>
            <div>
                <label for="responsavel">Responsavel</label>
                <input type="text" id="responsavel" name="responsavel" autocomplete="off" value="<?php echo e($responsavel_salvo); ?>">
            </div>
            <div>
                <label for="posto">Posto (opcional)</label>
                <input type="text" id="posto" name="posto" autocomplete="off">
            </div>
            <div class="full top-actions">
                <button class="btn" type="submit">Registrar devolucao</button>
            </div>
        </form>

        <?php if ($mensagem !== ''): ?>
            <div class="msg <?php echo e($mensagem_tipo); ?>"><?php echo e($mensagem); ?></div>
        <?php endif; ?>

        <div class="secao">
            <h2>Consultar etiqueta</h2>
            <form method="post" action="">
                <input type="hidden" name="acao" value="consultar">
                <div class="full">
                    <label for="consulta_leitura">Leitura da etiqueta (35 digitos)</label>
                    <input type="text" id="consulta_leitura" name="consulta_leitura" autocomplete="off">
                </div>
                <div class="full top-actions">
                    <button class="btn btn-sec" type="submit">Consultar status</button>
                </div>
            </form>

            <?php if ($consulta_msg !== ''): ?>
                <div class="msg <?php echo e($consulta_tipo); ?>"><?php echo e($consulta_msg); ?></div>
            <?php endif; ?>

            <?php if (!empty($consulta_dados)): ?>
                <div class="status-box <?php echo e($consulta_tipo); ?>">
                    <div class="status-title">Status: <?php echo e($consulta_dados['status']); ?></div>
                    <div>Data: <?php echo e($consulta_dados['data']); ?></div>
                    <div>Responsavel: <?php echo e($consulta_dados['responsavel']); ?></div>
                    <div>Posto: <?php echo e($consulta_dados['posto']); ?></div>
                </div>
            <?php endif; ?>
        </div>

        <div class="secao">
            <h2>Ultimas devolucoes</h2>
            <?php if (empty($recentes)): ?>
                <div class="msg aviso">Nenhuma devolucao registrada.</div>
            <?php else: ?>
                <table class="tabela">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Etiqueta</th>
                            <th>Responsavel</th>
                            <th>Posto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentes as $item): ?>
                            <tr>
                                <td><?php echo e(formatarDataBr($item['data'])); ?></td>
                                <td class="mono"><?php echo e($item['leitura']); ?></td>
                                <td><?php echo e($item['login']); ?></td>
                                <td><?php echo e($item['posto']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <a class="voltar" href="inicio.php">Voltar ao inicio</a>
    </div>

    <script>
        (function() {
            var input = document.getElementById('leitura');
            var responsavelInput = document.getElementById('responsavel');
            if (!input) return;
            if (responsavelInput) {
                var salvo = localStorage.getItem('responsavel_devolucao');
                if (salvo && responsavelInput.value.replace(/^\s+|\s+$/g, '') === '') {
                    responsavelInput.value = salvo;
                }
                responsavelInput.addEventListener('input', function() {
                    localStorage.setItem('responsavel_devolucao', this.value);
                });
            }
            input.addEventListener('input', function() {
                var digits = this.value.replace(/\D+/g, '');
                if (digits.length === 35) {
                    this.value = digits;
                    if (responsavelInput) {
                        localStorage.setItem('responsavel_devolucao', responsavelInput.value);
                    }
                    this.form.submit();
                }
            });
        })();
    </script>
</body>
</html>
