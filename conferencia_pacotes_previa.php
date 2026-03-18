<?php
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prévia de Ofício dos Malotes</title>
    <style>
        :root {
            --bg: #f2f6fb;
            --card: #ffffff;
            --line: #d9e5f2;
            --text: #17324d;
            --sub: #5b7188;
            --accent: #0f766e;
            --warn: #9a6700;
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            font-family: Georgia, "Times New Roman", serif;
            background:
                radial-gradient(circle at top right, rgba(15,118,110,0.14), transparent 28%),
                linear-gradient(180deg, #f9fbfe 0%, var(--bg) 100%);
            color: var(--text);
        }
        .wrap {
            max-width: 1400px;
            margin: 0 auto;
            padding: 28px 24px 40px;
        }
        .hero {
            display: grid;
            grid-template-columns: minmax(320px, 1.2fr) minmax(240px, 0.8fr);
            gap: 16px;
            margin-bottom: 18px;
        }
        .card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 18px;
            box-shadow: 0 10px 24px rgba(13,38,59,0.08);
        }
        h1 {
            margin: 0;
            font-size: 34px;
            letter-spacing: 0.5px;
        }
        .sub {
            margin-top: 8px;
            color: var(--sub);
            font-size: 14px;
            line-height: 1.5;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }
        .stat {
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 14px;
            background: linear-gradient(180deg, #ffffff 0%, #f4f8fc 100%);
        }
        .stat .label {
            color: var(--sub);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .stat .value {
            margin-top: 8px;
            font-size: 28px;
            font-weight: 700;
        }
        .meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 14px;
        }
        .meta span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 10px;
            border-radius: 999px;
            background: #eef4fb;
            color: #204264;
            font-size: 12px;
            font-weight: 700;
        }
        .grid {
            display: grid;
            grid-template-columns: minmax(0, 1.5fr) minmax(320px, 0.7fr);
            gap: 16px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th,
        td {
            text-align: left;
            padding: 10px 8px;
            border-bottom: 1px solid #e7eef5;
            vertical-align: top;
            font-size: 14px;
        }
        th {
            font-size: 11px;
            color: var(--sub);
            text-transform: uppercase;
            letter-spacing: 0.7px;
        }
        .pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 999px;
            background: #e6f7f4;
            color: var(--accent);
            padding: 4px 9px;
            font-size: 11px;
            font-weight: 700;
        }
        .pill.warn {
            background: #fff4d6;
            color: var(--warn);
        }
        .empty {
            margin-top: 12px;
            padding: 18px;
            border: 1px dashed var(--line);
            border-radius: 14px;
            color: var(--sub);
            line-height: 1.6;
            background: #f8fbfe;
        }
        .lista-pendencias {
            display: grid;
            gap: 10px;
            margin-top: 12px;
        }
        .pendencia {
            border: 1px solid #f2e2b0;
            border-radius: 14px;
            background: #fffaf0;
            padding: 12px 14px;
        }
        .pendencia strong {
            display: block;
            font-size: 15px;
        }
        .pendencia .detalhe {
            margin-top: 6px;
            color: var(--sub);
            font-size: 13px;
            line-height: 1.5;
        }
        @media (max-width: 900px) {
            .hero,
            .grid {
                grid-template-columns: 1fr;
            }
            .stats {
                grid-template-columns: 1fr;
            }
            h1 {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="hero">
            <section class="card">
                <h1>Prévia dinâmica do ofício</h1>
                <div class="sub" id="subtituloPrevia">Aguardando dados da tela de conferência em modo chips.</div>
                <div class="meta">
                    <span id="metaUsuario">Responsável: -</span>
                    <span id="metaAtualizacao">Atualização: -</span>
                    <span id="metaPosto">Posto atual: -</span>
                </div>
            </section>
            <section class="card">
                <div class="stats">
                    <div class="stat">
                        <div class="label">Pacotes confirmados</div>
                        <div class="value" id="statConfirmados">0</div>
                    </div>
                    <div class="stat">
                        <div class="label">Linhas prontas</div>
                        <div class="value" id="statLinhas">0</div>
                    </div>
                    <div class="stat">
                        <div class="label">Pendências</div>
                        <div class="value" id="statPendencias">0</div>
                    </div>
                </div>
            </section>
        </div>

        <div class="grid">
            <section class="card">
                <div class="pill warn">Pendências sem lacre IIPR</div>
                <div id="areaPendencias"></div>
            </section>
        </div>
    </div>

    <script>
    (function() {
        var storageKey = 'conferencia_previa_malotes_v1';
        var channel = null;
        var subtituloPrevia = document.getElementById('subtituloPrevia');
        var metaUsuario = document.getElementById('metaUsuario');
        var metaAtualizacao = document.getElementById('metaAtualizacao');
        var metaPosto = document.getElementById('metaPosto');
        var statConfirmados = document.getElementById('statConfirmados');
        var statLinhas = document.getElementById('statLinhas');
        var statPendencias = document.getElementById('statPendencias');

        var areaPendencias = document.getElementById('areaPendencias');

        function escapeHtml(valor) {
            return String(valor || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function lerSnapshot() {
            try {
                var bruto = localStorage.getItem(storageKey);
                return bruto ? JSON.parse(bruto) : null;
            } catch (e) {
                return null;
            }
        }

        function renderizar(snapshot) {
            if (!snapshot) {
                subtituloPrevia.textContent = 'Aguardando dados da tela de conferência em modo chips.';
                metaUsuario.textContent = 'Responsável: -';
                metaAtualizacao.textContent = 'Atualização: -';
                metaPosto.textContent = 'Posto atual: -';
                statConfirmados.textContent = '0';
                statLinhas.textContent = '0';
                statPendencias.textContent = '0';

                areaPendencias.innerHTML = '<div class="empty">Nenhuma pendência recebida.</div>';
                return;
            }

            subtituloPrevia.textContent = snapshot.datas_filtro && snapshot.datas_filtro.length
                ? 'Datas filtradas: ' + snapshot.datas_filtro.join(', ')
                : 'Prévia local sincronizada com a tela de conferência.';
            metaUsuario.textContent = 'Responsável: ' + (snapshot.usuario || '-');
            metaAtualizacao.textContent = 'Atualização: ' + (snapshot.gerado_em || '-');
            metaPosto.textContent = 'Posto atual: ' + (snapshot.posto_selecionado || '-');
            statConfirmados.textContent = String(snapshot.total_confirmados || 0);
            statLinhas.textContent = String(snapshot.total_fechados || 0);
            statPendencias.textContent = String((snapshot.pendentes || []).length);

            if (snapshot.pendentes && snapshot.pendentes.length) {
                var cards = [];
                for (var j = 0; j < snapshot.pendentes.length; j++) {
                    var pendencia = snapshot.pendentes[j];
                    cards.push(
                        '<div class="pendencia">' +
                            '<strong>Posto ' + escapeHtml(pendencia.posto || '-') + ' • ' + escapeHtml(pendencia.regional || '-') + '</strong>' +
                            '<div class="detalhe">Lotes aguardando fechamento IIPR: ' + escapeHtml((pendencia.lotes || []).join(', ')) + '</div>' +
                            '<div class="detalhe">Quantidade total: ' + escapeHtml(String(pendencia.qtd_total || 0)) + '</div>' +
                        '</div>'
                    );
                }
                areaPendencias.innerHTML = '<div class="lista-pendencias">' + cards.join('') + '</div>';
            } else {
                areaPendencias.innerHTML = '<div class="empty">Nenhum lote confirmado está aguardando lacre IIPR.</div>';
            }
        }

        function atualizar() {
            renderizar(lerSnapshot());
        }

        if (window.BroadcastChannel) {
            try {
                channel = new BroadcastChannel('conferencia_previa_malotes');
                channel.onmessage = function(event) {
                    renderizar(event.data || null);
                };
            } catch (e) {
                channel = null;
            }
        }

        window.addEventListener('storage', function(event) {
            if (event.key === storageKey) {
                atualizar();
            }
        });

        atualizar();
        window.setInterval(atualizar, 2000);
    })();
    </script>
</body>
</html>