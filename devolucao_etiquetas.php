<?php
header('Cache-Control: no-cache, no-store, must-revalidate');
session_start();

define('DB_HOST', '10.15.61.169');
define('DB_NAME', 'controle');
define('DB_USER', 'controle_mat');
define('DB_PASS', '375256');

function normalizarTextoUtf8Seguro($s) {
    $s = (string)$s;
    if ($s === '' || preg_match('//u', $s)) {
        return $s;
    }
    if (function_exists('iconv')) {
        $tmp = @iconv('UTF-8', 'UTF-8//IGNORE', $s);
        if ($tmp !== false && $tmp !== '') return $tmp;
        $tmp = @iconv('ISO-8859-1', 'UTF-8//IGNORE', $s);
        if ($tmp !== false && $tmp !== '') return $tmp;
        $tmp = @iconv('Windows-1252', 'UTF-8//IGNORE', $s);
        if ($tmp !== false && $tmp !== '') return $tmp;
    }
    if (function_exists('utf8_encode')) {
        return @utf8_encode($s);
    }
    return $s;
}

function e($s) {
    return htmlspecialchars(normalizarTextoUtf8Seguro($s), ENT_QUOTES, 'UTF-8');
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
$ultimo_movimento = array();
$responsavel_salvo = isset($_SESSION['ultimo_responsavel_devolucao']) ? trim((string)$_SESSION['ultimo_responsavel_devolucao']) : '';
$mostrar_transito = isset($_GET['mostrar_transito']) && $_GET['mostrar_transito'] === '1';
$etiquetas_transito = array();

function resolverPostoMalote($pdo, $leitura) {
    try {
        $stmtPosto = $pdo->prepare('SELECT posto FROM cadastroMalotes WHERE leitura = ? ORDER BY id DESC LIMIT 1');
        $stmtPosto->execute(array($leitura));
        $postoDb = $stmtPosto->fetchColumn();
        if ($postoDb !== false && $postoDb !== null && $postoDb !== '') {
            return str_pad(preg_replace('/\D+/', '', (string)$postoDb), 3, '0', STR_PAD_LEFT);
        }
    } catch (Exception $e) {
    }
    return null;
}

function registrarMovimentoEtiqueta($pdo, $leitura_raw, $responsavel, $tipo, &$mensagem, &$mensagem_tipo, &$responsavel_salvo, &$ultimo_movimento) {
    $leitura = preg_replace('/\D+/', '', (string)$leitura_raw);
    if ($responsavel === '') {
        $mensagem = 'Informe o responsavel.';
        $mensagem_tipo = 'erro';
        return;
    }
    if (strlen($leitura) !== 35) {
        $mensagem = 'Etiqueta invalida. Informe 35 digitos.';
        $mensagem_tipo = 'erro';
        return;
    }

    $cep = substr($leitura, 0, 8);
    $sequencial = substr($leitura, -5);
    $posto = resolverPostoMalote($pdo, $leitura);

    $stmtIns = $pdo->prepare('INSERT INTO ciMalotes (leitura, data, observacao, login, tipo, cep, sequencial, posto)
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmtIns->execute(array(
        $leitura,
        date('Y-m-d'),
        null,
        $responsavel,
        $tipo,
        $cep,
        $sequencial,
        $posto
    ));

    $_SESSION['ultimo_responsavel_devolucao'] = $responsavel;
    $responsavel_salvo = $responsavel;
    $mensagem = $tipo === 1 ? 'Envio de etiqueta registrado com sucesso.' : 'Recebimento de etiqueta registrado com sucesso.';
    $mensagem_tipo = 'ok';
    $ultimo_movimento = array(
        'tipo' => $tipo,
        'leitura' => $leitura,
        'posto' => $posto,
        'responsavel' => $responsavel,
        'data' => date('d-m-Y')
    );
}

function contarEtiquetasEmTransito($pdo) {
    $stmtTransito = $pdo->query("SELECT COUNT(DISTINCT m1.leitura) AS total\n                                 FROM ciMalotes m1\n                                 WHERE m1.tipo = 1\n                                   AND NOT EXISTS (\n                                       SELECT 1\n                                       FROM ciMalotes m2\n                                       WHERE m2.leitura = m1.leitura AND m2.tipo = 2\n                                   )");
    return (int)$stmtTransito->fetchColumn();
}

function buscarEtiquetasEmTransito($pdo, $limite) {
    $stmt = $pdo->prepare("SELECT m1.leitura, m1.posto, m1.login, DATE(m1.data) AS data_mov\n                           FROM ciMalotes m1\n                           WHERE m1.tipo = 1\n                             AND NOT EXISTS (\n                                 SELECT 1\n                                 FROM ciMalotes m2\n                                 WHERE m2.leitura = m1.leitura AND m2.tipo = 2\n                             )\n                           ORDER BY m1.data DESC, m1.id DESC\n                           LIMIT " . (int)$limite);
    $stmt->execute();
    return $stmt->fetchAll();
}

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
    $responsavel = isset($_POST['responsavel']) ? trim($_POST['responsavel']) : '';

    if ($acao === 'registrar_envio') {
        registrarMovimentoEtiqueta($pdo, isset($_POST['leitura_envio']) ? $_POST['leitura_envio'] : '', $responsavel, 1, $mensagem, $mensagem_tipo, $responsavel_salvo, $ultimo_movimento);
    } elseif ($acao === 'registrar_recebimento') {
        registrarMovimentoEtiqueta($pdo, isset($_POST['leitura_recebimento']) ? $_POST['leitura_recebimento'] : '', $responsavel, 2, $mensagem, $mensagem_tipo, $responsavel_salvo, $ultimo_movimento);
    }

    if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array(
            'ok' => ($mensagem_tipo === 'ok'),
            'mensagem' => $mensagem,
            'mensagem_tipo' => $mensagem_tipo,
            'ultimo_movimento' => $ultimo_movimento,
            'transito' => contarEtiquetasEmTransito($pdo)
        ));
        exit;
    }
}

$contagem = array('transito' => 0);
if ($dbOk) {
    $contagem['transito'] = contarEtiquetasEmTransito($pdo);
    if ($mostrar_transito) {
        $etiquetas_transito = buscarEtiquetasEmTransito($pdo, 250);
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devolucao de Etiquetas v1.0.2 - Projeto Lacres</title>
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
            position: relative;
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
        .painel-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 14px;
            border-radius: 12px;
            background: #eef4fb;
            color: #0b3d91;
            text-decoration: none;
            font-weight: 700;
            border: 1px solid #d9e5f4;
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
            padding: 14px 16px;
            border: 1px solid #d8dde3;
            border-radius: 10px;
            font-size: 22px;
            font-family: "Courier New", Courier, monospace;
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
        .status-live {
            margin-top: 14px;
            padding: 12px 14px;
            border-radius: 12px;
            background: #eef7ee;
            border: 1px solid #cce4cf;
            color: #1f5f2a;
            min-height: 48px;
            font-size: 14px;
        }
        .status-live.erro {
            background: #fff4f4;
            border-color: #f1c7c7;
            color: #a12828;
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
        .mono, .tabela .mono { font-family: "Courier New", Courier, monospace; }
        .home-link {
            position: absolute;
            top: 18px;
            right: 18px;
            width: 42px;
            height: 42px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: #0b3d91;
            background: #eef4fb;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            font-size: 22px;
            font-weight: 700;
        }
        .home-link:hover {
            background: #e3eefb;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 12px;
        }
        .campo-bloco {
            background: #f7f9fb;
            border: 1px solid #e3e7ec;
            border-radius: 12px;
            padding: 14px;
        }
        .campo-bloco h2 {
            font-size: 15px;
            color: #1d2b53;
            margin-bottom: 10px;
        }
        .status-box {
            background: #f7f9fb;
            border: 1px solid #e3e7ec;
            border-radius: 12px;
            padding: 12px 14px;
            margin-top: 14px;
        }
        @media (max-width: 600px) {
            .card { padding: 20px; }
            form { grid-template-columns: 1fr; }
            .full { grid-column: span 1; }
            .form-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="card">
        <a class="home-link" href="inicio.php" title="Voltar ao inicio">⌂</a>
        <h1>Devolucao de etiquetas (Correios) v1.0.2</h1>
        <div class="sub">Leia a etiqueta de envio ou recebimento. O posto sera identificado automaticamente pelo cadastro do malote.</div>

        <div class="painel">
            <div class="kpi">
                <div class="label">Em transito</div>
                <div class="valor" id="kpi-transito"><?php echo (int)$contagem['transito']; ?></div>
            </div>
            <a class="painel-link" href="devolucao_etiquetas.php?mostrar_transito=1#transito">Ver etiquetas em transito</a>
        </div>

        <div>
            <label for="responsavel">Responsavel</label>
            <input type="text" id="responsavel" name="responsavel" autocomplete="off" value="<?php echo e($responsavel_salvo); ?>">
        </div>

        <div class="form-grid">
            <form method="post" action="" id="formEnvio" class="campo-bloco">
                <input type="hidden" name="acao" value="registrar_envio">
                <input type="hidden" name="responsavel" value="<?php echo e($responsavel_salvo); ?>" class="responsavel-hidden">
                <h2>Registrar envio de etiquetas</h2>
                <label for="leitura_envio">Leitura da etiqueta (35 digitos) - tipo 1</label>
                <input type="text" id="leitura_envio" name="leitura_envio" autocomplete="off" autofocus>
            </form>

            <form method="post" action="" id="formRecebimento" class="campo-bloco">
                <input type="hidden" name="acao" value="registrar_recebimento">
                <input type="hidden" name="responsavel" value="<?php echo e($responsavel_salvo); ?>" class="responsavel-hidden">
                <h2>Registrar recebimento de etiquetas</h2>
                <label for="leitura_recebimento">Leitura da etiqueta (35 digitos) - tipo 2</label>
                <input type="text" id="leitura_recebimento" name="leitura_recebimento" autocomplete="off">
            </form>
        </div>

        <div class="status-live<?php echo ($mensagem_tipo === 'erro') ? ' erro' : ''; ?>" id="status-live"><?php echo $mensagem !== '' ? e($mensagem) : 'Pronto para nova leitura.'; ?></div>

        <?php if ($mensagem !== ''): ?>
            <div class="msg <?php echo e($mensagem_tipo); ?>"><?php echo e($mensagem); ?></div>
        <?php endif; ?>

        <div class="status-box ok" id="ultimo-movimento-box"<?php echo empty($ultimo_movimento) ? ' style="display:none;"' : ''; ?>>
            <?php if (!empty($ultimo_movimento)): ?>
                <div class="status-title"><?php echo e(((int)$ultimo_movimento['tipo'] === 1) ? 'Envio registrado' : 'Recebimento registrado'); ?></div>
                <div>Etiqueta: <span class="mono"><?php echo e($ultimo_movimento['leitura']); ?></span></div>
                <div>Responsavel: <?php echo e($ultimo_movimento['responsavel']); ?></div>
                <div>Posto: <?php echo e($ultimo_movimento['posto'] !== null && $ultimo_movimento['posto'] !== '' ? $ultimo_movimento['posto'] : '-'); ?></div>
                <div>Data: <?php echo e($ultimo_movimento['data']); ?></div>
            <?php endif; ?>
        </div>

        <?php if ($mostrar_transito): ?>
            <div class="secao" id="transito">
                <h2>Etiquetas em transito</h2>
                <table class="tabela">
                    <thead>
                        <tr>
                            <th>Etiqueta</th>
                            <th>Posto</th>
                            <th>Responsavel</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($etiquetas_transito)): ?>
                            <?php foreach ($etiquetas_transito as $linhaTransito): ?>
                            <tr>
                                <td class="mono"><?php echo e($linhaTransito['leitura']); ?></td>
                                <td><?php echo e($linhaTransito['posto'] !== null && $linhaTransito['posto'] !== '' ? $linhaTransito['posto'] : '-'); ?></td>
                                <td><?php echo e($linhaTransito['login'] !== null && $linhaTransito['login'] !== '' ? $linhaTransito['login'] : '-'); ?></td>
                                <td><?php echo e(formatarDataBr($linhaTransito['data_mov'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4">Nenhuma etiqueta em transito no momento.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script>
        (function() {
            var inputEnvio = document.getElementById('leitura_envio');
            var inputRecebimento = document.getElementById('leitura_recebimento');
            var responsavelInput = document.getElementById('responsavel');
            var responsaveisHidden = document.querySelectorAll('.responsavel-hidden');
            var statusLive = document.getElementById('status-live');
            var kpiTransito = document.getElementById('kpi-transito');

            function sincronizarResponsavel() {
                var valor = responsavelInput ? responsavelInput.value : '';
                for (var i = 0; i < responsaveisHidden.length; i++) {
                    responsaveisHidden[i].value = valor;
                }
            }

            function escaparHtml(valor) {
                return String(valor || '').replace(/[&<>"']/g, function(char) {
                    return {
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#039;'
                    }[char];
                });
            }

            function atualizarStatusLive(texto, tipo) {
                if (!statusLive) return;
                statusLive.className = 'status-live' + (tipo === 'erro' ? ' erro' : '');
                statusLive.textContent = texto;
            }

            function renderizarUltimoMovimento(dados) {
                var box = document.getElementById('ultimo-movimento-box');
                if (!box || !dados) return;
                box.style.display = 'block';
                box.innerHTML = '' +
                    '<div class="status-title">' + (((parseInt(dados.tipo, 10) || 0) === 1) ? 'Envio registrado' : 'Recebimento registrado') + '</div>' +
                    '<div>Etiqueta: <span class="mono">' + escaparHtml(dados.leitura || '-') + '</span></div>' +
                    '<div>Responsavel: ' + escaparHtml(dados.responsavel || '-') + '</div>' +
                    '<div>Posto: ' + escaparHtml((dados.posto !== null && dados.posto !== '') ? dados.posto : '-') + '</div>' +
                    '<div>Data: ' + escaparHtml(dados.data || '-') + '</div>';
            }

            function enviarLeituraAjax(input) {
                if (!input || !input.form) return;
                var digits = input.value.replace(/\D+/g, '');
                if (digits.length !== 35) return;

                sincronizarResponsavel();
                if (responsavelInput) {
                    localStorage.setItem('responsavel_devolucao', responsavelInput.value);
                }

                var formData = new FormData(input.form);
                formData.append('ajax', '1');
                formData.set(input.name, digits);

                input.value = '';
                atualizarStatusLive('Salvando etiqueta...', 'ok');
                input.focus();

                fetch(window.location.pathname + window.location.search, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                }).then(function(response) {
                    return response.json();
                }).then(function(data) {
                    if (data && data.ok) {
                        atualizarStatusLive(data.mensagem || 'Etiqueta salva com sucesso.', 'ok');
                        if (kpiTransito && typeof data.transito !== 'undefined') {
                            kpiTransito.textContent = String(data.transito);
                        }
                        renderizarUltimoMovimento(data.ultimo_movimento || null);
                    } else {
                        atualizarStatusLive((data && data.mensagem) ? data.mensagem : 'Falha ao salvar etiqueta.', 'erro');
                    }
                }).catch(function() {
                    atualizarStatusLive('Falha de comunicacao ao salvar etiqueta.', 'erro');
                }).then(function() {
                    input.focus();
                });
            }

            function prepararLeitura(input) {
                if (!input) return;
                input.addEventListener('input', function() {
                    var digits = this.value.replace(/\D+/g, '');
                    if (digits.length === 35) {
                        this.value = digits;
                        enviarLeituraAjax(this);
                    }
                });
            }

            if (responsavelInput) {
                var salvo = localStorage.getItem('responsavel_devolucao');
                if (salvo && responsavelInput.value.replace(/^\s+|\s+$/g, '') === '') {
                    responsavelInput.value = salvo;
                }
                responsavelInput.addEventListener('input', function() {
                    localStorage.setItem('responsavel_devolucao', this.value);
                    sincronizarResponsavel();
                });
            }
            sincronizarResponsavel();
            prepararLeitura(inputEnvio);
            prepararLeitura(inputRecebimento);
        })();
    </script>
</body>
</html>
