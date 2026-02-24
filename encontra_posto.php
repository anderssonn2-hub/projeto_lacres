<?php
/* encontra_posto.php — v2.1
 * Triagem rapida: leitura de codigo de barras, busca em ciRegionais,
 * vocalizacao e exibicao visual do posto.
 * Sem modal de responsavel, sem salvamento de dados.
 */

function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

$dbOk = false;

try {
    $pdo = new PDO(
        "mysql:host=10.15.61.169;dbname=controle;charset=utf8",
        "controle_mat",
        "375256"
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $dbOk = true;

    if (isset($_POST['ajax_buscar_posto'])) {
        header('Content-Type: application/json');
        $codbar = isset($_POST['codbar']) ? trim($_POST['codbar']) : '';
        $codbar_limpo = preg_replace('/\D+/', '', $codbar);

        if (strlen($codbar_limpo) < 14) {
            die(json_encode(array('success' => false, 'erro' => 'Codigo de barras muito curto (minimo 14 digitos)')));
        }

        $lote = substr($codbar_limpo, 0, 8);
        $regional_csv = substr($codbar_limpo, 8, 3);
        $posto_num = substr($codbar_limpo, 11, 3);
        $quantidade = strlen($codbar_limpo) >= 19 ? substr($codbar_limpo, 14, 5) : '00001';
        $posto_pad = str_pad($posto_num, 3, '0', STR_PAD_LEFT);

        $stmt = $pdo->prepare("SELECT LPAD(posto,3,'0') AS posto,
                                      CAST(regional AS UNSIGNED) AS regional,
                                      LOWER(TRIM(REPLACE(entrega,' ',''))) AS entrega
                               FROM ciRegionais
                               WHERE LPAD(posto,3,'0') = ?
                               LIMIT 1");
        $stmt->execute(array($posto_pad));
        $postoRow = $stmt->fetch(PDO::FETCH_ASSOC);

        $regional_real = null;
        $entrega_tipo = null;
        $posto_encontrado = false;

        if ($postoRow) {
            $posto_encontrado = true;
            $regional_real = (int)$postoRow['regional'];
            $entrega_limpo = $postoRow['entrega'];
            if (!empty($entrega_limpo)) {
                if (strpos($entrega_limpo, 'poupa') !== false || strpos($entrega_limpo, 'tempo') !== false) {
                    $entrega_tipo = 'poupatempo';
                } elseif (strpos($entrega_limpo, 'correio') !== false) {
                    $entrega_tipo = 'correios';
                }
            }
        } else {
            $regional_real = (int)$regional_csv;
        }

        $posto_int = (int)$posto_num;
        $voz = '';
        $tipo_posto = 'correios';
        $label_tipo = 'Correios';

        if ($entrega_tipo === 'poupatempo') {
            $voz = 'Poupa Tempo ' . $posto_int;
            $tipo_posto = 'poupatempo';
            $label_tipo = 'Poupa Tempo';
        } elseif ($regional_real === 0) {
            $voz = 'Posto ' . $posto_int;
            $label_tipo = 'Capital';
        } elseif ($regional_real === 999) {
            $voz = 'Posto ' . $posto_int;
            $label_tipo = 'Central Metropolitana';
        } else {
            $regional_pad = str_pad((string)$regional_real, 3, '0', STR_PAD_LEFT);
            $voz = 'Regional ' . $regional_pad;
            $label_tipo = 'Regional ' . $regional_pad;
        }

        die(json_encode(array(
            'success' => true,
            'posto' => $posto_pad,
            'posto_int' => $posto_int,
            'regional' => $regional_real,
            'regional_csv' => (int)$regional_csv,
            'regional_pad' => str_pad((string)$regional_real, 3, '0', STR_PAD_LEFT),
            'entrega' => $entrega_tipo,
            'tipo_posto' => $tipo_posto,
            'label_tipo' => $label_tipo,
            'voz' => $voz,
            'lote' => $lote,
            'quantidade' => $quantidade,
            'posto_encontrado' => $posto_encontrado,
            'codbar' => $codbar_limpo
        )));
    }

} catch (PDOException $e) {
    if (isset($_POST['ajax_buscar_posto'])) {
        header('Content-Type: application/json');
        die(json_encode(array('success' => false, 'erro' => 'Erro de conexao: ' . $e->getMessage())));
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encontra Posto - Triagem Rapida</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: "Trebuchet MS", "Segoe UI", Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
            padding-top: 80px;
        }

        .topo-fixo {
            position: fixed;
            top: 0; left: 0; right: 0;
            background: linear-gradient(135deg, #1a237e 0%, #283593 100%);
            color: white;
            padding: 12px 20px;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        .topo-fixo h1 { font-size: 18px; font-weight: 700; }
        .topo-fixo .versao {
            background: #4caf50; color: white;
            padding: 4px 12px; border-radius: 20px;
            font-size: 12px; font-weight: 700;
        }

        .toggle-voz {
            display: inline-flex; align-items: center; gap: 8px;
            cursor: pointer; font-size: 13px; color: white;
        }
        .toggle-voz input { display: none; }
        .toggle-slider {
            position: relative; width: 36px; height: 20px;
            background: rgba(255,255,255,0.3); border-radius: 10px;
            transition: background 0.3s;
        }
        .toggle-slider:after {
            content: ''; position: absolute; top: 2px; left: 2px;
            width: 16px; height: 16px; background: white;
            border-radius: 50%; transition: transform 0.3s;
        }
        .toggle-voz input:checked + .toggle-slider { background: #4caf50; }
        .toggle-voz input:checked + .toggle-slider:after { transform: translateX(16px); }

        .area-principal { max-width: 800px; margin: 0 auto; }

        .painel-leitura {
            background: white; border-radius: 10px;
            padding: 20px; margin-bottom: 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.1);
        }
        .painel-leitura label {
            font-weight: 700; color: #333;
            display: block; margin-bottom: 8px; font-size: 14px;
        }
        #input_codbar {
            width: 100%; max-width: 500px;
            padding: 14px 16px; font-size: 20px;
            border: 3px solid #1a237e; border-radius: 8px;
            background: #e8eaf6; font-weight: 700; letter-spacing: 2px;
        }
        #input_codbar:focus { outline: none; border-color: #4caf50; background: #e8f5e9; }

        .stats-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 10px; margin-bottom: 20px;
        }
        .stat-card {
            background: white; border-radius: 8px;
            padding: 12px 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-left: 4px solid #1a237e;
            text-align: center;
        }
        .stat-card h4 { font-size: 11px; color: #777; text-transform: uppercase; margin-bottom: 4px; }
        .stat-card .valor { font-size: 22px; font-weight: 700; color: #1a237e; }

        .resultado-posto {
            border-radius: 12px; padding: 0; margin-bottom: 20px;
            overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            display: none;
        }
        .resultado-header {
            padding: 24px 28px; color: white;
            font-weight: 700; text-align: center;
        }
        .resultado-header .numero-posto {
            font-size: 56px; font-weight: 800; line-height: 1; margin-bottom: 8px;
        }
        .resultado-header .tipo-label { font-size: 24px; opacity: 0.9; }
        .resultado-body {
            background: white; padding: 16px 24px;
        }
        .resultado-body .info-linha {
            display: flex; justify-content: space-between;
            padding: 8px 0; border-bottom: 1px solid #eee; font-size: 14px;
        }
        .resultado-body .info-linha:last-child { border-bottom: none; }
        .resultado-body .info-label { color: #777; font-weight: 600; }
        .resultado-body .info-valor { color: #333; font-weight: 700; }

        .bg-capital { background: linear-gradient(135deg, #1565c0 0%, #0d47a1 100%); }
        .bg-central { background: linear-gradient(135deg, #6a1b9a 0%, #4a148c 100%); }
        .bg-regional { background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%); }
        .bg-poupatempo { background: linear-gradient(135deg, #e65100 0%, #bf360c 100%); }
        .bg-desconhecido { background: linear-gradient(135deg, #616161 0%, #424242 100%); }

        .historico-container {
            background: white; border-radius: 10px;
            padding: 20px; margin-bottom: 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.1);
        }
        .historico-container h3 {
            color: #333; margin-bottom: 12px;
            padding-left: 10px; border-left: 4px solid #1a237e; font-size: 16px;
        }
        .historico-lista { max-height: 300px; overflow-y: auto; }
        .historico-item {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 12px; border-bottom: 1px solid #eee;
            font-size: 14px; transition: background 0.2s;
        }
        .historico-item:hover { background: #f5f5f5; }
        .historico-item .hi-posto { font-weight: 700; font-size: 16px; min-width: 80px; }
        .historico-item .hi-tipo { min-width: 140px; }
        .historico-item .hi-hora { color: #999; font-size: 12px; }
        .msg-vazia { color: #999; text-align: center; padding: 30px; font-style: italic; }

        .btn-voltar {
            color: white;
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
            padding: 6px 14px;
            border-radius: 6px;
            background: rgba(255,255,255,0.15);
            margin-right: 10px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .btn-voltar:hover {
            background: rgba(255,255,255,0.3);
        }

        @media (max-width: 600px) {
            .topo-fixo { flex-wrap: wrap; gap: 8px; }
            .topo-fixo h1 { font-size: 15px; }
            #input_codbar { font-size: 16px; }
            .resultado-header .numero-posto { font-size: 40px; }
        }
    </style>
</head>
<body>

<div class="topo-fixo">
    <div style="display:flex; align-items:center; gap:12px;">
        <a href="inicio.php" class="btn-voltar">&larr; Inicio</a>
        <h1>Encontra Posto</h1>
        <span class="versao">v0.9.24.6</span>
    </div>
    <label class="toggle-voz">
        <input type="checkbox" id="toggleVoz" checked>
        <span class="toggle-slider"></span>
        Voz ativa
    </label>
</div>

<div class="area-principal">

    <div class="painel-leitura">
        <label>Codigo de Barras do Pacote:</label>
        <input type="text" id="input_codbar" placeholder="Escaneie ou digite o codigo..." autocomplete="off" autofocus>
        <div id="indicadorFoco" style="margin-top:8px; font-size:13px; font-weight:700; color:#4caf50;">Pronto para leitura</div>
    </div>

    <div class="stats-bar">
        <div class="stat-card">
            <h4>Total Lidos</h4>
            <div class="valor" id="statTotal">0</div>
        </div>
        <div class="stat-card" style="border-left-color:#1565c0;">
            <h4>Capital</h4>
            <div class="valor" id="statCapital" style="color:#1565c0;">0</div>
        </div>
        <div class="stat-card" style="border-left-color:#6a1b9a;">
            <h4>Metropolitana</h4>
            <div class="valor" id="statCentral" style="color:#6a1b9a;">0</div>
        </div>
        <div class="stat-card" style="border-left-color:#2e7d32;">
            <h4>Regional</h4>
            <div class="valor" id="statRegional" style="color:#2e7d32;">0</div>
        </div>
        <div class="stat-card" style="border-left-color:#e65100;">
            <h4>Poupa Tempo</h4>
            <div class="valor" id="statPT" style="color:#e65100;">0</div>
        </div>
    </div>

    <div class="resultado-posto" id="resultadoPosto">
        <div class="resultado-header" id="resultadoHeader">
            <div class="numero-posto" id="resultadoNumero"></div>
            <div class="tipo-label" id="resultadoTipo"></div>
        </div>
        <div class="resultado-body" id="resultadoBody"></div>
    </div>

    <div class="historico-container">
        <h3>Historico de Leituras</h3>
        <div class="historico-lista" id="historicoLista">
            <div class="msg-vazia">Nenhuma leitura realizada</div>
        </div>
    </div>

</div>

<audio id="audioBeep" src="beep.mp3" preload="auto"></audio>

<script>
var vozAtiva = true;
var historico = [];
var contTotal = 0;
var contCapital = 0;
var contCentral = 0;
var contRegional = 0;
var contPT = 0;
var audioFilaAtiva = false;
var audioFila = [];

var barcodeBuffer = '';
var barcodeTimer = null;
var BARCODE_TIMEOUT = 80;

document.getElementById('toggleVoz').onchange = function() {
    vozAtiva = this.checked;
};

function atualizarIndicadorFoco() {
    var campo = document.getElementById('input_codbar');
    var indicador = document.getElementById('indicadorFoco');
    if (!indicador) return;
    if (document.activeElement === campo) {
        indicador.textContent = 'Pronto para leitura';
        indicador.style.color = '#4caf50';
    } else {
        indicador.textContent = 'Toque para ativar leitura';
        indicador.style.color = '#f44336';
    }
}

function processarBuffer() {
    var val = barcodeBuffer.replace(/^\s+|\s+$/g, '');
    barcodeBuffer = '';
    barcodeTimer = null;
    if (val.length >= 14) {
        document.getElementById('input_codbar').value = '';
        buscarPosto(val);
    }
}

document.getElementById('input_codbar').onkeydown = function(ev) {
    if (ev.keyCode === 13) {
        if (barcodeTimer) { clearTimeout(barcodeTimer); barcodeTimer = null; }
        var val = (barcodeBuffer + this.value).replace(/^\s+|\s+$/g, '');
        barcodeBuffer = '';
        if (val.length >= 14) {
            buscarPosto(val);
            this.value = '';
        }
        return;
    }
    if (barcodeTimer) { clearTimeout(barcodeTimer); }
    barcodeTimer = setTimeout(function() {
        var campo = document.getElementById('input_codbar');
        barcodeBuffer = campo.value;
        processarBuffer();
        campo.value = '';
    }, BARCODE_TIMEOUT);
};

document.getElementById('input_codbar').onfocus = function() {
    atualizarIndicadorFoco();
};

document.getElementById('input_codbar').onblur = function() {
    barcodeBuffer = '';
    if (barcodeTimer) { clearTimeout(barcodeTimer); barcodeTimer = null; }
    atualizarIndicadorFoco();
};

document.addEventListener('keydown', function(ev) {
    var campo = document.getElementById('input_codbar');
    if (document.activeElement !== campo) {
        campo.focus();
    }
});

function falar(texto) {
    if (!vozAtiva) return;
    if (typeof speechSynthesis === 'undefined') return;

    var utt = new SpeechSynthesisUtterance(texto);
    utt.lang = 'pt-BR';
    utt.rate = 0.9;
    utt.pitch = 1;
    utt.volume = 1;

    audioFila.push(utt);
    processarFilaVoz();
}

function processarFilaVoz() {
    if (audioFilaAtiva) return;
    if (audioFila.length === 0) return;
    audioFilaAtiva = true;
    var utt = audioFila.shift();
    utt.onend = function() { audioFilaAtiva = false; processarFilaVoz(); };
    utt.onerror = function() { audioFilaAtiva = false; processarFilaVoz(); };
    speechSynthesis.speak(utt);
}

function tocarBeep() {
    try {
        var audio = document.getElementById('audioBeep');
        audio.currentTime = 0;
        audio.play();
    } catch (e) {}
}

function buscarPosto(codbar) {
    tocarBeep();
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'encontra_posto.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    var resp = JSON.parse(xhr.responseText);
                    if (resp.success) {
                        exibirResultado(resp);
                    } else {
                        exibirErro(resp.erro || 'Erro desconhecido');
                    }
                } catch (e) {
                    exibirErro('Erro ao processar resposta');
                }
            } else {
                exibirErro('Erro de conexao');
            }
        }
    };
    xhr.send('ajax_buscar_posto=1&codbar=' + encodeURIComponent(codbar));
}

function exibirResultado(dados) {
    var div = document.getElementById('resultadoPosto');
    var header = document.getElementById('resultadoHeader');
    var numDiv = document.getElementById('resultadoNumero');
    var tipoDiv = document.getElementById('resultadoTipo');
    var body = document.getElementById('resultadoBody');

    header.className = 'resultado-header';
    if (dados.entrega === 'poupatempo') {
        header.className += ' bg-poupatempo';
    } else if (dados.regional === 0) {
        header.className += ' bg-capital';
    } else if (dados.regional === 999) {
        header.className += ' bg-central';
    } else if (dados.posto_encontrado) {
        header.className += ' bg-regional';
    } else {
        header.className += ' bg-desconhecido';
    }

    if (dados.entrega === 'poupatempo') {
        numDiv.textContent = 'PT ' + dados.posto_int;
    } else {
        numDiv.textContent = 'Posto ' + dados.posto;
    }
    tipoDiv.textContent = dados.label_tipo;

    body.innerHTML = '';

    if (dados.voz) {
        var linhaVoz = document.createElement('div');
        linhaVoz.className = 'info-linha';
        var labelVoz = document.createElement('span');
        labelVoz.className = 'info-label';
        labelVoz.textContent = 'Vocalizar';
        var valorVoz = document.createElement('span');
        valorVoz.className = 'info-valor';
        valorVoz.textContent = dados.voz;
        linhaVoz.appendChild(labelVoz);
        linhaVoz.appendChild(valorVoz);
        body.appendChild(linhaVoz);
    }

    div.style.display = 'block';

    falar(dados.voz);

    contTotal++;
    if (dados.entrega === 'poupatempo') { contPT++; }
    else if (dados.regional === 0) { contCapital++; }
    else if (dados.regional === 999) { contCentral++; }
    else { contRegional++; }

    atualizarStats();
    adicionarHistorico(dados);
    document.getElementById('input_codbar').focus();
}

function exibirErro(msg) {
    var div = document.getElementById('resultadoPosto');
    var header = document.getElementById('resultadoHeader');
    document.getElementById('resultadoNumero').textContent = 'Erro';
    document.getElementById('resultadoTipo').textContent = msg;
    document.getElementById('resultadoBody').innerHTML = '';
    header.className = 'resultado-header bg-desconhecido';
    div.style.display = 'block';
}

function atualizarStats() {
    document.getElementById('statTotal').textContent = contTotal;
    document.getElementById('statCapital').textContent = contCapital;
    document.getElementById('statCentral').textContent = contCentral;
    document.getElementById('statRegional').textContent = contRegional;
    document.getElementById('statPT').textContent = contPT;
}

function adicionarHistorico(dados) {
    var agora = new Date();
    var hora = (agora.getHours() < 10 ? '0' : '') + agora.getHours() + ':' +
               (agora.getMinutes() < 10 ? '0' : '') + agora.getMinutes() + ':' +
               (agora.getSeconds() < 10 ? '0' : '') + agora.getSeconds();

    var tipoTag = '';
    if (dados.entrega === 'poupatempo') {
        tipoTag = '<span style="background:#e65100;color:white;padding:2px 8px;border-radius:3px;font-size:11px;font-weight:700;">POUPA TEMPO</span>';
    } else if (dados.regional === 0) {
        tipoTag = '<span style="background:#1565c0;color:white;padding:2px 8px;border-radius:3px;font-size:11px;font-weight:700;">CAPITAL</span>';
    } else if (dados.regional === 999) {
        tipoTag = '<span style="background:#6a1b9a;color:white;padding:2px 8px;border-radius:3px;font-size:11px;font-weight:700;">CENTRAL</span>';
    } else {
        tipoTag = '<span style="background:#2e7d32;color:white;padding:2px 8px;border-radius:3px;font-size:11px;font-weight:700;">REG ' + dados.regional_pad + '</span>';
    }

    historico.unshift({
        posto: dados.posto,
        postoInt: dados.posto_int,
        entrega: dados.entrega,
        tipoTag: tipoTag,
        labelTipo: dados.label_tipo,
        hora: hora
    });

    renderizarHistorico();
}

function renderizarHistorico() {
    var lista = document.getElementById('historicoLista');
    if (historico.length === 0) {
        lista.innerHTML = '<div class="msg-vazia">Nenhuma leitura realizada</div>';
        return;
    }
    var html = '';
    var max = historico.length > 50 ? 50 : historico.length;
    for (var i = 0; i < max; i++) {
        var h = historico[i];
        var postoLabel = h.entrega === 'poupatempo' ? 'PT ' + h.postoInt : 'Posto ' + h.posto;
        html += '<div class="historico-item">' +
            '<span class="hi-posto">' + postoLabel + '</span>' +
            '<span class="hi-tipo">' + h.tipoTag + '</span>' +
            '<span class="hi-hora">' + h.hora + '</span>' +
            '</div>';
    }
    lista.innerHTML = html;
}
// v2.1: Wake Lock API - manter tela ativa durante leituras
var wakeLockSentinel = null;

function solicitarWakeLock() {
    if ('wakeLock' in navigator) {
        navigator.wakeLock.request('screen').then(function(sentinel) {
            wakeLockSentinel = sentinel;
            console.log('[WakeLock] Tela mantida ativa');
            sentinel.addEventListener('release', function() {
                console.log('[WakeLock] Liberado - tentando readquirir');
                wakeLockSentinel = null;
            });
        }).catch(function(err) {
            console.log('[WakeLock] Nao suportado ou negado:', err.message);
        });
    }
}

solicitarWakeLock();

document.addEventListener('visibilitychange', function() {
    var campo = document.getElementById('input_codbar');
    if (document.visibilityState === 'visible') {
        if (!wakeLockSentinel) { solicitarWakeLock(); }
        barcodeBuffer = '';
        if (barcodeTimer) { clearTimeout(barcodeTimer); barcodeTimer = null; }
        if (campo) { campo.value = ''; campo.focus(); }
        atualizarIndicadorFoco();
    } else {
        barcodeBuffer = '';
        if (barcodeTimer) { clearTimeout(barcodeTimer); barcodeTimer = null; }
        if (campo) { campo.value = ''; }
    }
});

window.addEventListener('focus', function() {
    if (!wakeLockSentinel) { solicitarWakeLock(); }
    barcodeBuffer = '';
    if (barcodeTimer) { clearTimeout(barcodeTimer); barcodeTimer = null; }
    var campo = document.getElementById('input_codbar');
    if (campo) { campo.value = ''; campo.focus(); }
    atualizarIndicadorFoco();
});

setInterval(function() {
    if (document.visibilityState === 'visible' && !wakeLockSentinel) {
        solicitarWakeLock();
    }
    atualizarIndicadorFoco();
}, 30000);

atualizarIndicadorFoco();

</script>

</body>
</html>