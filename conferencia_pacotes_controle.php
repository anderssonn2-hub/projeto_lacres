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
    <title>Controle Simplificado dos Malotes v0.9.25.13</title>
    <style>
        :root {
            --bg: #eef3f9;
            --card: #ffffff;
            --line: #d9e2ec;
            --text: #17324d;
            --sub: #62758a;
            --ok: #0f766e;
            --blue: #1d4ed8;
            --amber: #b45309;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Trebuchet MS", Verdana, sans-serif;
            background: linear-gradient(180deg, #fbfdff 0%, var(--bg) 100%);
            color: var(--text);
        }
        .wrap {
            max-width: 680px;
            margin: 0 auto;
            padding: 16px 14px 28px;
        }
        .card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 16px;
            box-shadow: 0 10px 24px rgba(17,38,56,0.08);
            margin-bottom: 14px;
        }
        h1, h2 { margin: 0; }
        h1 { font-size: 24px; }
        h2 { font-size: 17px; margin-bottom: 10px; }
        .sub {
            margin-top: 8px;
            color: var(--sub);
            font-size: 13px;
            line-height: 1.5;
        }
        .badge {
            display: inline-flex;
            padding: 8px 10px;
            border-radius: 999px;
            background: #e8eef8;
            color: var(--blue);
            font-size: 12px;
            font-weight: 800;
        }
        .status {
            margin-top: 12px;
            padding: 10px 12px;
            border-radius: 12px;
            background: #eef4fb;
            font-size: 13px;
            font-weight: 700;
            color: #204264;
        }
        .status.ok { background: #e7f7ef; color: #136c3a; }
        .status.erro { background: #fff1f2; color: #9f1239; }
        .estado-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin-top: 12px;
        }
        .estado-item {
            border: 1px solid var(--line);
            border-radius: 12px;
            background: #f8fbff;
            padding: 10px 12px;
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
            margin-top: 10px;
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
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin-top: 12px;
        }
        .btn-triplo {
            border: none;
            border-radius: 16px;
            padding: 16px 12px;
            color: #fff;
            font-size: 15px;
            font-weight: 800;
            line-height: 1.3;
            cursor: pointer;
            touch-action: manipulation;
        }
        .btn-blue { background: linear-gradient(180deg, #1d4ed8 0%, #1e40af 100%); }
        .btn-green { background: linear-gradient(180deg, #0f766e 0%, #115e59 100%); }
        .btn-amber { background: linear-gradient(180deg, #d97706 0%, #b45309 100%); }
        .ajuda {
            margin-top: 10px;
            color: var(--sub);
            font-size: 12px;
            line-height: 1.6;
        }
        @media (max-width: 640px) {
            .estado-grid,
            .acoes {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <section class="card">
            <h1>Controle simplificado</h1>
            <div class="sub">Fluxo direto: atribuir IIPR, salvar IIPR, salvar lacre Correios e depois salvar etiqueta Correios. Todos os botões continuam exigindo 3 toques.</div>
            <div style="margin-top:12px;"><span class="badge">Canal: <span id="canalAtual"><?php echo htmlspecialchars($controle_canal, ENT_QUOTES, 'UTF-8'); ?></span></span></div>
            <div class="status" id="statusEnvio">Aguardando operação.</div>
            <div class="estado-grid">
                <div class="estado-item">
                    <div class="label">Posto atual</div>
                    <div class="valor" id="estadoPosto">-</div>
                </div>
                <div class="estado-item">
                    <div class="label">Grupo</div>
                    <div class="valor" id="estadoRegional">-</div>
                </div>
                <div class="estado-item">
                    <div class="label">Atualização</div>
                    <div class="valor" id="estadoAtualizado">-</div>
                </div>
            </div>
            <div class="ajuda" id="estadoResumo">A prévia vai espelhar os lacres do posto atual.</div>
        </section>

        <section class="card">
            <h2>1. Fechar malote IIPR</h2>
            <div class="campo">
                <label for="inputLacreIiprRemoto">Lacre IIPR</label>
                <input type="text" id="inputLacreIiprRemoto" inputmode="numeric" maxlength="12" placeholder="Ex.: 100">
            </div>
            <div class="acoes">
                <button type="button" class="btn-triplo btn-blue" data-acao="atribuir_iipr">Atribuir lacre IIPR</button>
                <button type="button" class="btn-triplo btn-green" data-acao="salvar_iipr">Salvar lacre IIPR</button>
            </div>
        </section>

        <section class="card">
            <h2>2. Fechar malote Correios</h2>
            <div class="campo">
                <label for="inputLacreCorreiosRemoto">Lacre Correios</label>
                <input type="text" id="inputLacreCorreiosRemoto" inputmode="numeric" maxlength="12" placeholder="Ex.: 101">
            </div>
            <div class="acoes">
                <button type="button" class="btn-triplo btn-amber" data-acao="salvar_lacre_correios">Inserir lacre Correios</button>
            </div>
        </section>

        <section class="card">
            <h2>3. Salvar etiqueta Correios</h2>
            <div class="campo">
                <label for="inputEtiquetaCorreiosRemoto">Etiqueta Correios</label>
                <input type="text" id="inputEtiquetaCorreiosRemoto" inputmode="numeric" maxlength="35" placeholder="Leia ou digite os 35 caracteres">
            </div>
            <div class="acoes">
                <button type="button" class="btn-triplo btn-green" data-acao="salvar_etiqueta_correios">Inserir etiqueta Correios</button>
            </div>
            <div class="ajuda">Se o lacre Correios já foi salvo antes, este botão completa o mesmo malote com a etiqueta.</div>
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
        var toques = {};

        function normalizarNumero(valor, limite) {
            return String(valor || '').replace(/\D+/g, '').slice(0, limite || 35);
        }

        function atualizarStatus(texto, tipo) {
            if (!statusEnvio) return;
            statusEnvio.textContent = texto;
            statusEnvio.className = 'status' + (tipo ? ' ' + tipo : '');
        }

        function montarPayload(acao) {
            var comando = '';
            var valor = '';
            var valorAux = '';

            if (acao === 'atribuir_iipr') {
                comando = 'armar_iipr';
            } else if (acao === 'salvar_iipr') {
                comando = 'salvar_iipr';
                valor = normalizarNumero(inputLacreIiprRemoto ? inputLacreIiprRemoto.value : '', 12);
                valorAux = (estadoPosto && estadoPosto.textContent && estadoPosto.textContent.trim() !== '-') ? estadoPosto.textContent.trim() : '';
            } else if (acao === 'salvar_lacre_correios') {
                comando = 'salvar_correios';
                valor = normalizarNumero(inputLacreCorreiosRemoto ? inputLacreCorreiosRemoto.value : '', 12);
                valorAux = '';
            } else if (acao === 'salvar_etiqueta_correios') {
                comando = 'salvar_correios';
                valor = normalizarNumero(inputLacreCorreiosRemoto ? inputLacreCorreiosRemoto.value : '', 12);
                valorAux = normalizarNumero(inputEtiquetaCorreiosRemoto ? inputEtiquetaCorreiosRemoto.value : '', 35);
            }

            return { comando: comando, valor: valor, valorAux: valorAux };
        }

        function enviarAcao(acao) {
            var payload = montarPayload(acao);
            if (!payload.comando) {
                atualizarStatus('Ação inválida.', 'erro');
                return;
            }

            if (acao === 'salvar_iipr' && !payload.valor) {
                atualizarStatus('Digite o lacre IIPR antes de salvar.', 'erro');
                if (inputLacreIiprRemoto) inputLacreIiprRemoto.focus();
                return;
            }
            if (acao === 'salvar_lacre_correios' && !payload.valor) {
                atualizarStatus('Digite o lacre Correios antes de inserir.', 'erro');
                if (inputLacreCorreiosRemoto) inputLacreCorreiosRemoto.focus();
                return;
            }
            if (acao === 'salvar_etiqueta_correios' && !payload.valorAux) {
                atualizarStatus('Leia ou digite a etiqueta Correios antes de salvar.', 'erro');
                if (inputEtiquetaCorreiosRemoto) inputEtiquetaCorreiosRemoto.focus();
                return;
            }

            var formData = new FormData();
            formData.append('enviar_comando_remoto_ajax', '1');
            formData.append('canal', canal);
            formData.append('comando', payload.comando);
            formData.append('valor', payload.valor);
            formData.append('valor_aux', payload.valorAux);

            fetch('conferencia_pacotes.php', { method: 'POST', body: formData })
                .then(function(resp) { return resp.json(); })
                .then(function(data) {
                    if (!data || !data.success) {
                        atualizarStatus('Falha ao enviar operação.', 'erro');
                        return;
                    }
                    if (acao === 'salvar_iipr' && inputLacreIiprRemoto) inputLacreIiprRemoto.value = '';
                    if (acao === 'salvar_lacre_correios' && inputLacreCorreiosRemoto) inputLacreCorreiosRemoto.value = '';
                    if (acao === 'salvar_etiqueta_correios' && inputEtiquetaCorreiosRemoto) inputEtiquetaCorreiosRemoto.value = '';
                    atualizarStatus('Operação enviada: ' + acao.replace(/_/g, ' '), 'ok');
                })
                .catch(function() {
                    atualizarStatus('Erro de comunicação com a conferência.', 'erro');
                });
        }

        function registrarToque(acao) {
            var agora = Date.now();
            if (!toques[acao] || (agora - toques[acao].ultimo) > 1300) {
                toques[acao] = { total: 0, ultimo: agora };
            }
            toques[acao].total++;
            toques[acao].ultimo = agora;

            if (toques[acao].total >= 3) {
                toques[acao] = { total: 0, ultimo: agora };
                enviarAcao(acao);
                return;
            }

            atualizarStatus('Confirme a operação com 3 toques: ' + acao.replace(/_/g, ' ') + ' (' + toques[acao].total + '/3)', '');
        }

        function bindBotoes() {
            var botoes = document.querySelectorAll('[data-acao]');
            for (var i = 0; i < botoes.length; i++) {
                botoes[i].addEventListener('click', function() {
                    var acao = this.getAttribute('data-acao') || '';
                    if (acao) registrarToque(acao);
                });
            }
        }

        function carregarEstado() {
            fetch('conferencia_pacotes.php?ler_estado_remoto_ajax=1&canal=' + encodeURIComponent(canal), { cache: 'no-store' })
                .then(function(resp) { return resp.json(); })
                .then(function(data) {
                    var estado = data && data.estado ? data.estado : null;
                    if (!estado) {
                        estadoPosto.textContent = '-';
                        estadoRegional.textContent = '-';
                        estadoAtualizado.textContent = '-';
                        estadoResumo.textContent = 'Aguardando posto conferido no PC.';
                        return;
                    }
                    estadoPosto.textContent = estado.posto || '-';
                    estadoRegional.textContent = estado.regional || '-';
                    estadoAtualizado.textContent = estado.atualizado_em || '-';
                    estadoResumo.textContent = estado.resumo || 'A prévia vai espelhar os lacres do posto atual.';
                })
                .catch(function() {});
        }

        bindBotoes();
        carregarEstado();
        window.setInterval(carregarEstado, 1500);
    })();
    </script>
</body>
</html>