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
    <title>Lacres Remoto v0.9.25.18</title>
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
            --purple: #7c3aed;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Trebuchet MS", Verdana, sans-serif;
            background: linear-gradient(180deg, #fbfdff 0%, var(--bg) 100%);
            color: var(--text);
        }
        .wrap {
            max-width: 760px;
            margin: 0 auto;
            padding: 14px 14px 32px;
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
        h1 { font-size: 26px; letter-spacing: 0.01em; }
        h2 { font-size: 18px; margin-bottom: 10px; }
        .hero-card {
            padding: 18px;
        }
        .hero-topo {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            flex-wrap: wrap;
        }
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
            grid-template-columns: repeat(2, minmax(0, 1fr));
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
            transition: border-color 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
        }
        .campo input:focus,
        .campo input.ativo-remoto {
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.12);
            outline: none;
        }
        .acoes {
            display: grid;
            grid-template-columns: 1fr;
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
        .btn-triplo small {
            display: block;
            margin-top: 4px;
            font-size: 12px;
            font-weight: 700;
            opacity: 0.86;
        }
        .btn-blue { background: linear-gradient(180deg, #1d4ed8 0%, #1e40af 100%); }
        .btn-green { background: linear-gradient(180deg, #0f766e 0%, #115e59 100%); }
        .btn-amber { background: linear-gradient(180deg, #d97706 0%, #b45309 100%); }
        .btn-purple { background: linear-gradient(180deg, #8b5cf6 0%, #6d28d9 100%); }
        .ajuda {
            margin-top: 10px;
            color: var(--sub);
            font-size: 12px;
            line-height: 1.6;
        }
        .passo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 30px;
            height: 30px;
            border-radius: 999px;
            background: #e8eef8;
            color: var(--blue);
            font-size: 13px;
            font-weight: 800;
            margin-bottom: 10px;
        }
        .card-compacto {
            padding-top: 14px;
            padding-bottom: 14px;
        }
        .link-comandos {
            display: inline-flex;
            margin-top: 10px;
            color: var(--blue);
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
        }
        @media (max-width: 640px) {
            .hero-card {
                padding: 16px;
            }
            .hero-topo,
            .estado-grid,
            .acoes {
                grid-template-columns: 1fr;
            }
            h1 {
                font-size: 22px;
            }
            .btn-triplo {
                padding: 18px 14px;
                font-size: 16px;
            }
            .campo input {
                font-size: 19px;
                padding: 16px 12px;
            }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <section class="card hero-card">
            <div class="hero-topo">
                <h1>Lacres Remoto</h1>
                <span class="badge">Canal: <span id="canalAtual"><?php echo htmlspecialchars($controle_canal, ENT_QUOTES, 'UTF-8'); ?></span></span>
            </div>
            <div class="status" id="statusEnvio">Aguardando operação.</div>
            <div class="estado-grid">
                <div class="estado-item">
                    <div class="label">Regional</div>
                    <div class="valor" id="estadoRegional">-</div>
                </div>
                <div class="estado-item">
                    <div class="label">Atualização</div>
                    <div class="valor" id="estadoAtualizado">-</div>
                </div>
            </div>
            <div class="ajuda" id="estadoResumo">A prévia vai espelhar os lacres do contexto atual.</div>
            <a class="link-comandos" href="conferencia_pacotes_comandos.php" target="_blank" rel="noopener">Abrir folha de comandos por código de barras</a>
        </section>

        <section class="card card-compacto">
            <div class="passo">1</div>
            <h2>Lacre IIPR</h2>
            <div class="campo">
                <label for="inputLacreIiprRemoto">Lacre IIPR</label>
                <input type="text" id="inputLacreIiprRemoto" inputmode="numeric" maxlength="12" placeholder="Ex.: 100">
            </div>
            <div class="acoes">
                <button type="button" class="btn-triplo btn-blue" data-acao="atribuir_iipr">Atribuir Lacre IIPR<small>Exige 3 toques</small></button>
            </div>
        </section>

        <section class="card card-compacto">
            <div class="passo">2</div>
            <h2>Lacre Correios</h2>
            <div class="campo">
                <label for="inputLacreCorreiosRemoto">Lacre Correios</label>
                <input type="text" id="inputLacreCorreiosRemoto" inputmode="numeric" maxlength="12" placeholder="Ex.: 101">
            </div>
            <div class="acoes">
                <button type="button" class="btn-triplo btn-amber" data-acao="atribuir_correios">Atribuir Lacre Correios<small>Exige 3 toques</small></button>
            </div>
        </section>

        <section class="card card-compacto">
            <div class="passo">3</div>
            <h2>Display Correios</h2>
            <div class="campo">
                <label for="inputEtiquetaCorreiosRemoto">Etiqueta Correios</label>
                <input type="text" id="inputEtiquetaCorreiosRemoto" inputmode="numeric" maxlength="35" placeholder="Leia ou digite os 35 caracteres">
            </div>
            <div class="acoes">
                <button type="button" class="btn-triplo btn-green" data-acao="atribuir_display">Atribuir Display Correios<small>Exige 3 toques</small></button>
            </div>
            <div class="ajuda">Ao tocar neste campo ou neste botão, a prévia ativa o input correspondente para leitura direta do código de barras.</div>
        </section>

        <section class="card card-compacto">
            <div class="passo">4</div>
            <h2>Mais uma linha no ofício</h2>
            <div class="acoes">
                <button type="button" class="btn-triplo btn-purple" data-acao="adicionar_linha">Adicionar Linha para Este Posto<small>Exige 3 toques</small></button>
            </div>
            <div class="ajuda">Use quando o mesmo posto precisar aparecer mais uma vez na prévia e no ofício final.</div>
        </section>
    </div>

    <script>
    (function() {
        var canal = <?php echo json_encode($controle_canal); ?> || 'principal';
        var statusEnvio = document.getElementById('statusEnvio');
        var estadoRegional = document.getElementById('estadoRegional');
        var estadoResumo = document.getElementById('estadoResumo');
        var estadoAtualizado = document.getElementById('estadoAtualizado');
        var inputLacreIiprRemoto = document.getElementById('inputLacreIiprRemoto');
        var inputLacreCorreiosRemoto = document.getElementById('inputLacreCorreiosRemoto');
        var inputEtiquetaCorreiosRemoto = document.getElementById('inputEtiquetaCorreiosRemoto');
        var toques = {};
        var ultimoEstadoRemoto = null;

        function formatarRegionalExibicao(valor) {
            var digitos = String(valor || '').replace(/\D+/g, '');
            if (!digitos) return '-';
            digitos = digitos.slice(-3).padStart(3, '0');
            if (digitos === '000') return 'CAPITAL';
            if (digitos === '001') return 'METROPOLITANA';
            if (digitos === '999') return 'CENTRAL IIPR';
            return 'Regional ' + digitos;
        }

        function normalizarCampoAtivo(campo) {
            campo = String(campo || '').trim().toLowerCase();
            if (campo === 'iipr' || campo === 'lacre_iipr') return 'lacre_iipr';
            if (campo === 'correios' || campo === 'correios_lacre' || campo === 'lacre_correios') return 'lacre_correios';
            if (campo === 'display' || campo === 'etiqueta' || campo === 'correios_etiqueta' || campo === 'etiqueta_correios') return 'etiqueta_correios';
            return '';
        }

        function criarTokenFoco() {
            return String(Date.now()) + '_' + String(Math.floor(Math.random() * 100000));
        }

        function acaoParaCampo(acao) {
            if (acao === 'atribuir_iipr') return 'lacre_iipr';
            if (acao === 'atribuir_correios') return 'lacre_correios';
            if (acao === 'atribuir_display') return 'etiqueta_correios';
            return '';
        }

        function destacarInputAtivo(campo) {
            var mapa = {
                lacre_iipr: inputLacreIiprRemoto,
                lacre_correios: inputLacreCorreiosRemoto,
                etiqueta_correios: inputEtiquetaCorreiosRemoto
            };
            for (var chave in mapa) {
                if (!Object.prototype.hasOwnProperty.call(mapa, chave) || !mapa[chave]) continue;
                mapa[chave].classList.toggle('ativo-remoto', chave === campo);
            }
        }

        function publicarEstadoRemotoParcial(parcial) {
            parcial = parcial || {};
            var formData = new FormData();
            formData.append('atualizar_estado_remoto_ajax', '1');
            formData.append('canal', canal);
            formData.append('usuario', Object.prototype.hasOwnProperty.call(parcial, 'usuario') ? parcial.usuario : (ultimoEstadoRemoto && ultimoEstadoRemoto.usuario ? ultimoEstadoRemoto.usuario : ''));
            formData.append('posto', Object.prototype.hasOwnProperty.call(parcial, 'posto') ? parcial.posto : (ultimoEstadoRemoto && ultimoEstadoRemoto.posto ? ultimoEstadoRemoto.posto : ''));
            formData.append('regional', Object.prototype.hasOwnProperty.call(parcial, 'regional') ? parcial.regional : (ultimoEstadoRemoto && ultimoEstadoRemoto.regional ? ultimoEstadoRemoto.regional : ''));
            formData.append('resumo', typeof parcial.resumo === 'string' ? parcial.resumo : obterResumoEstadoAtual());
            formData.append('lacre_iipr', typeof parcial.lacre_iipr === 'string' ? parcial.lacre_iipr : (ultimoEstadoRemoto && ultimoEstadoRemoto.lacre_iipr ? ultimoEstadoRemoto.lacre_iipr : ''));
            formData.append('lacre_correios', typeof parcial.lacre_correios === 'string' ? parcial.lacre_correios : (ultimoEstadoRemoto && ultimoEstadoRemoto.lacre_correios ? ultimoEstadoRemoto.lacre_correios : ''));
            formData.append('etiqueta_correios', typeof parcial.etiqueta_correios === 'string' ? parcial.etiqueta_correios : (ultimoEstadoRemoto && ultimoEstadoRemoto.etiqueta_correios ? ultimoEstadoRemoto.etiqueta_correios : ''));
            formData.append('campo_ativo', Object.prototype.hasOwnProperty.call(parcial, 'campo_ativo') ? parcial.campo_ativo : (ultimoEstadoRemoto && ultimoEstadoRemoto.campo_ativo ? ultimoEstadoRemoto.campo_ativo : ''));
            formData.append('row_key_ativo', Object.prototype.hasOwnProperty.call(parcial, 'row_key_ativo') ? parcial.row_key_ativo : (ultimoEstadoRemoto && ultimoEstadoRemoto.row_key_ativo ? ultimoEstadoRemoto.row_key_ativo : ''));
            formData.append('foco_token', Object.prototype.hasOwnProperty.call(parcial, 'foco_token') ? parcial.foco_token : (ultimoEstadoRemoto && ultimoEstadoRemoto.foco_token ? ultimoEstadoRemoto.foco_token : ''));

            return fetch('conferencia_pacotes.php', { method: 'POST', body: formData })
                .then(function(resp) { return resp.json(); })
                .then(function(data) {
                    if (!data || !data.success) {
                        throw new Error('Falha ao publicar estado remoto');
                    }
                    ultimoEstadoRemoto = ultimoEstadoRemoto || {};
                    ultimoEstadoRemoto.usuario = formData.get('usuario');
                    ultimoEstadoRemoto.posto = formData.get('posto');
                    ultimoEstadoRemoto.regional = formData.get('regional');
                    ultimoEstadoRemoto.resumo = formData.get('resumo');
                    ultimoEstadoRemoto.lacre_iipr = formData.get('lacre_iipr');
                    ultimoEstadoRemoto.lacre_correios = formData.get('lacre_correios');
                    ultimoEstadoRemoto.etiqueta_correios = formData.get('etiqueta_correios');
                    ultimoEstadoRemoto.campo_ativo = formData.get('campo_ativo');
                    ultimoEstadoRemoto.row_key_ativo = formData.get('row_key_ativo');
                    ultimoEstadoRemoto.foco_token = formData.get('foco_token');
                });
        }

        function ativarCampoNaPrevia(campo) {
            var campoNormalizado = normalizarCampoAtivo(campo);
            if (!campoNormalizado) return;
            destacarInputAtivo(campoNormalizado);
            publicarEstadoRemotoParcial({
                campo_ativo: campoNormalizado,
                row_key_ativo: '',
                foco_token: criarTokenFoco()
            }).catch(function() {});
        }

        function normalizarNumero(valor, limite) {
            return String(valor || '').replace(/\D+/g, '').slice(0, limite || 35);
        }

        function atualizarStatus(texto, tipo) {
            if (!statusEnvio) return;
            statusEnvio.textContent = texto;
            statusEnvio.className = 'status' + (tipo ? ' ' + tipo : '');
        }

        function obterResumoEstadoAtual() {
            if (ultimoEstadoRemoto && ultimoEstadoRemoto.resumo) {
                return String(ultimoEstadoRemoto.resumo || '');
            }
            return estadoResumo ? String(estadoResumo.textContent || '') : '';
        }

        function publicarEstadoDigitado(acao, payload) {
            return publicarEstadoRemotoParcial({
                lacre_iipr: acao === 'atribuir_iipr' ? payload.valor : undefined,
                lacre_correios: acao === 'atribuir_correios' ? payload.valor : undefined,
                etiqueta_correios: acao === 'atribuir_display' ? payload.valorAux : undefined,
                campo_ativo: acaoParaCampo(acao) || (ultimoEstadoRemoto && ultimoEstadoRemoto.campo_ativo ? ultimoEstadoRemoto.campo_ativo : ''),
                foco_token: acaoParaCampo(acao) ? criarTokenFoco() : (ultimoEstadoRemoto && ultimoEstadoRemoto.foco_token ? ultimoEstadoRemoto.foco_token : '')
            });
        }

        function montarPayload(acao) {
            var comando = '';
            var valor = '';
            var valorAux = '';

            if (acao === 'atribuir_iipr') {
                comando = 'atribuir_iipr';
                valor = normalizarNumero(inputLacreIiprRemoto ? inputLacreIiprRemoto.value : '', 12);
            } else if (acao === 'atribuir_correios') {
                comando = 'atribuir_correios';
                valor = normalizarNumero(inputLacreCorreiosRemoto ? inputLacreCorreiosRemoto.value : '', 12);
            } else if (acao === 'atribuir_display') {
                comando = 'atribuir_display';
                valorAux = normalizarNumero(inputEtiquetaCorreiosRemoto ? inputEtiquetaCorreiosRemoto.value : '', 35);
            } else if (acao === 'adicionar_linha') {
                comando = 'adicionar_linha';
            }

            return { comando: comando, valor: valor, valorAux: valorAux };
        }

        function enviarAcao(acao) {
            var payload = montarPayload(acao);
            if (!payload.comando) {
                atualizarStatus('Ação inválida.', 'erro');
                return;
            }

            if (acao === 'atribuir_iipr' && !payload.valor) {
                atualizarStatus('Digite o lacre IIPR antes de atribuir.', 'erro');
                if (inputLacreIiprRemoto) inputLacreIiprRemoto.focus();
                return;
            }
            if (acao === 'atribuir_correios' && !payload.valor) {
                atualizarStatus('Digite o lacre Correios antes de atribuir.', 'erro');
                if (inputLacreCorreiosRemoto) inputLacreCorreiosRemoto.focus();
                return;
            }
            if (acao === 'atribuir_display' && !payload.valorAux) {
                atualizarStatus('Leia ou digite a etiqueta Correios antes de atribuir o display.', 'erro');
                if (inputEtiquetaCorreiosRemoto) inputEtiquetaCorreiosRemoto.focus();
                return;
            }

            if (acaoParaCampo(acao)) {
                ativarCampoNaPrevia(acaoParaCampo(acao));
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
                    return publicarEstadoDigitado(acao, payload)
                        .catch(function() {})
                        .then(function() {
                            if (acao === 'atribuir_iipr' && inputLacreIiprRemoto) inputLacreIiprRemoto.value = '';
                            if (acao === 'atribuir_correios' && inputLacreCorreiosRemoto) inputLacreCorreiosRemoto.value = '';
                            if (acao === 'atribuir_display' && inputEtiquetaCorreiosRemoto) inputEtiquetaCorreiosRemoto.value = '';
                            if (acao === 'adicionar_linha') {
                                atualizarStatus('Linha extra solicitada para o contexto atual.', 'ok');
                                return carregarEstado();
                            }
                            atualizarStatus('Operação enviada: ' + acao.replace(/_/g, ' '), 'ok');
                            carregarEstado();
                        });
                })
                .catch(function() {
                    atualizarStatus('Erro de comunicação com a conferência.', 'erro');
                });
        }

        function registrarToque(acao) {
            if (acaoParaCampo(acao)) {
                ativarCampoNaPrevia(acaoParaCampo(acao));
            }
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

        function bindEntradas() {
            var mapa = [
                { input: inputLacreIiprRemoto, campo: 'lacre_iipr' },
                { input: inputLacreCorreiosRemoto, campo: 'lacre_correios' },
                { input: inputEtiquetaCorreiosRemoto, campo: 'etiqueta_correios' }
            ];
            for (var i = 0; i < mapa.length; i++) {
                (function(item) {
                    if (!item.input) return;
                    item.input.addEventListener('focus', function() {
                        ativarCampoNaPrevia(item.campo);
                    });
                    item.input.addEventListener('click', function() {
                        ativarCampoNaPrevia(item.campo);
                    });
                })(mapa[i]);
            }
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
                    ultimoEstadoRemoto = estado;
                    if (!estado) {
                        estadoRegional.textContent = '-';
                        estadoAtualizado.textContent = '-';
                        estadoResumo.textContent = 'Aguardando contexto conferido no PC.';
                        destacarInputAtivo('');
                        return;
                    }
                    estadoRegional.textContent = formatarRegionalExibicao(estado.regional || '');
                    estadoAtualizado.textContent = estado.atualizado_em || '-';
                    estadoResumo.textContent = estado.resumo || 'A prévia vai espelhar os lacres do contexto atual.';
                    destacarInputAtivo(normalizarCampoAtivo(estado.campo_ativo || ''));
                })
                .catch(function() {});
        }

        bindBotoes();
        bindEntradas();
        carregarEstado();
        window.setInterval(carregarEstado, 1500);
    })();
    </script>
</body>
</html>