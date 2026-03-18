<?php
$controle_canal = isset($_GET['canal_controle']) ? preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)$_GET['canal_controle']) : 'principal';
if ($controle_canal === '') {
    $controle_canal = 'principal';
}
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Controle Remoto dos Malotes v0.9.25.13</title>
    <style>
        :root {
            --bg: #eff4fa;
            --card: #ffffff;
            --line: #d8e4ef;
            --text: #17324d;
            --sub: #5b7188;
            --accent: #0f766e;
            --purple: #6d28d9;
            --danger: #b42318;
            --soft: #f6f9fc;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Trebuchet MS", Verdana, sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at top right, rgba(109,40,217,0.12), transparent 24%),
                linear-gradient(180deg, #fbfdff 0%, var(--bg) 100%);
        }
        .wrap {
            max-width: 760px;
            margin: 0 auto;
            padding: 18px 14px 28px;
        }
        .card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 16px;
            box-shadow: 0 12px 28px rgba(17,38,56,0.08);
            margin-bottom: 14px;
        }
        h1, h2 {
            margin: 0;
        }
        h1 {
            font-size: 26px;
            letter-spacing: 0.3px;
        }
        h2 {
            font-size: 18px;
            margin-bottom: 10px;
        }
        .sub {
            margin-top: 8px;
            color: var(--sub);
            font-size: 13px;
            line-height: 1.5;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 10px;
            border-radius: 999px;
            background: #ede9fe;
            color: var(--purple);
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .status {
            margin-top: 12px;
            padding: 10px 12px;
            border-radius: 12px;
            background: #eef4fb;
            color: #204264;
            font-size: 13px;
            font-weight: 700;
        }
        .status.ok { background: #e7f7ef; color: #136c3a; }
        .status.erro { background: #fee4e2; color: var(--danger); }
        .estado-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin-top: 12px;
        }
        .estado-item {
            border: 1px solid var(--line);
            border-radius: 12px;
            background: var(--soft);
            padding: 12px;
        }
        .estado-item .label {
            color: var(--sub);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .estado-item .valor {
            margin-top: 6px;
            font-size: 16px;
            font-weight: 700;
            word-break: break-word;
        }
        .campo {
            display: grid;
            gap: 6px;
            margin-top: 12px;
        }
        .campo label {
            font-size: 12px;
            color: var(--sub);
            font-weight: 700;
        }
        .campo input {
            width: 100%;
            border: 1px solid #c8d6e5;
            border-radius: 12px;
            padding: 14px 12px;
            font-size: 18px;
            background: #fff;
        }
        .acoes {
            display: grid;
            gap: 10px;
            margin-top: 12px;
        }
        .acoes.duas {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .btn-triplo {
            border: none;
            border-radius: 16px;
            padding: 16px 14px;
            color: #fff;
            font-size: 15px;
            font-weight: 800;
            line-height: 1.3;
            cursor: pointer;
            touch-action: manipulation;
        }
        .btn-triplo small {
            display: block;
            margin-top: 4px;
            font-size: 11px;
            font-weight: 600;
            opacity: 0.9;
        }
        .btn-purple { background: linear-gradient(180deg, #7c3aed 0%, #5b21b6 100%); }
        .btn-green { background: linear-gradient(180deg, #0f766e 0%, #115e59 100%); }
        .btn-blue { background: linear-gradient(180deg, #1d4ed8 0%, #1e40af 100%); }
        .btn-red { background: linear-gradient(180deg, #dc2626 0%, #991b1b 100%); }
        .contador-toques {
            margin-top: 8px;
            font-size: 12px;
            color: var(--sub);
            font-weight: 700;
        }
        .rodape {
            color: var(--sub);
            font-size: 12px;
            line-height: 1.6;
        }
        @media (max-width: 640px) {
            .estado-grid,
            .acoes.duas {
                grid-template-columns: 1fr;
            }
            h1 {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <section class="card">
            <h1>Controle remoto dos malotes</h1>
            <div class="sub" style="margin-top:4px;">Versão 0.9.25.13</div>
            <div class="sub">Use esta página no celular para enviar comandos para a conferência aberta no PC. Os botões exigem 3 toques seguidos para evitar disparos acidentais.</div>
            <div style="margin-top:12px;"><span class="badge">Canal: <span id="canalAtual"><?php echo htmlspecialchars($controle_canal, ENT_QUOTES, 'UTF-8'); ?></span></span></div>
            <div class="status" id="statusEnvio">Aguardando comandos.</div>
            <div class="estado-grid">
                <div class="estado-item">
                    <div class="label">Posto selecionado no PC</div>
                    <div class="valor" id="estadoPosto">-</div>
                </div>
                <div class="estado-item">
                    <div class="label">Grupo / regional</div>
                    <div class="valor" id="estadoRegional">-</div>
                </div>
                <div class="estado-item">
                    <div class="label">Resumo</div>
                    <div class="valor" id="estadoResumo">-</div>
                </div>
                <div class="estado-item">
                    <div class="label">Última atualização</div>
                    <div class="valor" id="estadoAtualizado">-</div>
                </div>
            </div>
        </section>

        <section class="card">
            <h2>Malote IIPR</h2>
            <div class="campo">
                <label for="inputLacreIiprRemoto">Lacre IIPR</label>
                <input type="text" id="inputLacreIiprRemoto" inputmode="numeric" maxlength="12" placeholder="Ex.: 51146">
            </div>
            <div class="acoes duas">
                <button type="button" class="btn-triplo btn-blue" data-comando="armar_iipr">Armar IIPR no PC<small>3 toques</small></button>
                <button type="button" class="btn-triplo btn-green" data-comando="salvar_iipr">Salvar lacre IIPR<small>3 toques</small></button>
            </div>
            <div class="contador-toques" id="contadorIipr">Toques: 0/3</div>
        </section>

        <section class="card">
            <h2>Malote Correios</h2>
            <div class="campo">
                <label for="inputLacreCorreiosRemoto">Lacre Correios</label>
                <input type="text" id="inputLacreCorreiosRemoto" inputmode="numeric" maxlength="12" placeholder="Ex.: 51150">
            </div>
            <div class="campo">
                <label for="inputEtiquetaCorreiosRemoto">Etiqueta Correios</label>
                <input type="text" id="inputEtiquetaCorreiosRemoto" inputmode="numeric" maxlength="35" placeholder="Ex.: 84010200441050000104011000000100998">
            </div>
            <div class="acoes duas">
                <button type="button" class="btn-triplo btn-purple" data-comando="armar_correios">Armar lacre Correios<small>3 toques</small></button>
                <button type="button" class="btn-triplo btn-purple" data-comando="armar_etiqueta">Armar etiqueta no PC<small>3 toques</small></button>
                <button type="button" class="btn-triplo btn-blue" data-comando="preencher_correios">Preencher só lacre<small>3 toques</small></button>
                <button type="button" class="btn-triplo btn-blue" data-comando="preencher_etiqueta">Preencher só etiqueta<small>3 toques</small></button>
            </div>
            <div class="acoes">
                <button type="button" class="btn-triplo btn-green" data-comando="salvar_correios">Salvar malote Correios<small>3 toques, envia lacre e etiqueta juntos</small></button>
                <button type="button" class="btn-triplo btn-red" data-comando="limpar_lotes">Limpar vínculos selecionados<small>3 toques</small></button>
            </div>
            <div class="contador-toques" id="contadorCorreios">Toques: 0/3</div>
        </section>

        <section class="card rodape">
            Na tela do PC você continua marcando os lotes e malotes IIPR normalmente. O celular só envia os comandos e valores. Se quiser usar outro par PC/celular ao mesmo tempo, abra ambos com outro valor em canal_controle.
        </section>
    </div>

    <script>
    (function() {
        var canal = <?php echo json_encode($controle_canal); ?> || 'principal';
        var statusEnvio = document.getElementById('statusEnvio');
        var estadoPosto = document.getElementById('estadoPosto');
        var estadoRegional = document.getElementById('estadoRegional');
        var estadoResumo = document.getElementById('estadoResumo');
        var estadoAtualizado = document.getElementById('estadoAtualizado');
        var inputLacreIiprRemoto = document.getElementById('inputLacreIiprRemoto');
        var inputLacreCorreiosRemoto = document.getElementById('inputLacreCorreiosRemoto');
        var inputEtiquetaCorreiosRemoto = document.getElementById('inputEtiquetaCorreiosRemoto');
        var contadorIipr = document.getElementById('contadorIipr');
        var contadorCorreios = document.getElementById('contadorCorreios');
        var estadoToques = {};

        function normalizarNumero(valor, limite) {
            return String(valor || '').replace(/\D+/g, '').slice(0, limite || 35);
        }

        function atualizarStatus(texto, tipo) {
            if (!statusEnvio) return;
            statusEnvio.textContent = texto;
            statusEnvio.className = 'status' + (tipo ? ' ' + tipo : '');
        }

        function atualizarContador(chave, total) {
            var alvo = chave.indexOf('iipr') !== -1 ? contadorIipr : contadorCorreios;
            if (alvo) {
                alvo.textContent = 'Toques: ' + total + '/3';
            }
        }

        function enviarComando(comando) {
            var valor = '';
            var valorAux = '';
            if (comando === 'salvar_iipr') {
                valor = normalizarNumero(inputLacreIiprRemoto ? inputLacreIiprRemoto.value : '', 12);
            } else if (comando === 'preencher_correios') {
                valor = normalizarNumero(inputLacreCorreiosRemoto ? inputLacreCorreiosRemoto.value : '', 12);
            } else if (comando === 'preencher_etiqueta') {
                valor = normalizarNumero(inputEtiquetaCorreiosRemoto ? inputEtiquetaCorreiosRemoto.value : '', 35);
            } else if (comando === 'salvar_correios') {
                valor = normalizarNumero(inputLacreCorreiosRemoto ? inputLacreCorreiosRemoto.value : '', 12);
                valorAux = normalizarNumero(inputEtiquetaCorreiosRemoto ? inputEtiquetaCorreiosRemoto.value : '', 35);
            }

            var formData = new FormData();
            formData.append('enviar_comando_remoto_ajax', '1');
            formData.append('canal', canal);
            formData.append('comando', comando);
            formData.append('valor', valor);
            formData.append('valor_aux', valorAux);

            fetch('conferencia_pacotes.php', { method: 'POST', body: formData })
                .then(function(resp) { return resp.json(); })
                .then(function(data) {
                    if (!data || !data.success) {
                        atualizarStatus('Falha ao enviar comando remoto.', 'erro');
                        return;
                    }
                    atualizarStatus('Comando enviado: ' + comando.replace(/_/g, ' '), 'ok');
                })
                .catch(function() {
                    atualizarStatus('Erro de comunicação com a conferência.', 'erro');
                });
        }

        function tocarTriplo(chave, comando) {
            var agora = Date.now();
            if (!estadoToques[chave] || (agora - estadoToques[chave].ultimo) > 1300) {
                estadoToques[chave] = { total: 0, ultimo: agora };
            }
            estadoToques[chave].total++;
            estadoToques[chave].ultimo = agora;
            atualizarContador(chave, estadoToques[chave].total);
            if (estadoToques[chave].total >= 3) {
                estadoToques[chave] = { total: 0, ultimo: agora };
                atualizarContador(chave, 0);
                enviarComando(comando);
            }
        }

        function bindTriplo(button) {
            var comando = button.getAttribute('data-comando') || '';
            if (!comando) return;
            var chave = comando.indexOf('iipr') !== -1 ? 'iipr' : 'correios';
            button.addEventListener('click', function() {
                tocarTriplo(chave, comando);
            });
        }

        function carregarEstado() {
            fetch('conferencia_pacotes.php?ler_estado_remoto_ajax=1&canal=' + encodeURIComponent(canal), { cache: 'no-store' })
                .then(function(resp) { return resp.json(); })
                .then(function(data) {
                    var estado = data && data.estado ? data.estado : null;
                    if (!estado) {
                        estadoPosto.textContent = '-';
                        estadoRegional.textContent = '-';
                        estadoResumo.textContent = 'Aguardando seleção no PC';
                        estadoAtualizado.textContent = '-';
                        return;
                    }
                    estadoPosto.textContent = estado.posto || '-';
                    estadoRegional.textContent = estado.regional || '-';
                    estadoResumo.textContent = estado.resumo || '-';
                    estadoAtualizado.textContent = estado.atualizado_em || '-';
                    if (!document.activeElement || document.activeElement !== inputLacreIiprRemoto) {
                        if (!inputLacreIiprRemoto.value && estado.lacre_iipr) inputLacreIiprRemoto.value = estado.lacre_iipr;
                    }
                    if (!document.activeElement || document.activeElement !== inputLacreCorreiosRemoto) {
                        if (!inputLacreCorreiosRemoto.value && estado.lacre_correios) inputLacreCorreiosRemoto.value = estado.lacre_correios;
                    }
                    if (!document.activeElement || document.activeElement !== inputEtiquetaCorreiosRemoto) {
                        if (!inputEtiquetaCorreiosRemoto.value && estado.etiqueta_correios) inputEtiquetaCorreiosRemoto.value = estado.etiqueta_correios;
                    }
                })
                .catch(function() {});
        }

        var botoes = document.querySelectorAll('.btn-triplo[data-comando]');
        for (var i = 0; i < botoes.length; i++) {
            bindTriplo(botoes[i]);
        }

        carregarEstado();
        window.setInterval(carregarEstado, 1500);
    })();
    </script>
</body>
</html>