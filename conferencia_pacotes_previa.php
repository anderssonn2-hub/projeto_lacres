<?php
$nomesPostos = array();
$ultimoOficioCorreios = 0;
try {
    $pdo_controle = new PDO('mysql:host=10.15.61.169;dbname=controle;charset=utf8', 'root', 'vazio');
    $pdo_controle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stNomes = $pdo_controle->query("SELECT LPAD(CAST(posto AS UNSIGNED), 3, '0') AS posto, MAX(nome) AS nome FROM ciPostosCsv GROUP BY LPAD(CAST(posto AS UNSIGNED), 3, '0') ORDER BY LPAD(CAST(posto AS UNSIGNED), 3, '0')");
    while ($rowNome = $stNomes->fetch(PDO::FETCH_ASSOC)) {
        $nomesPostos[$rowNome['posto']] = trim((string)$rowNome['nome']);
    }

    $stUltimo = $pdo_controle->query("SELECT id FROM ciDespachos WHERE LOWER(grupo) = 'correios' ORDER BY id DESC LIMIT 1");
    $ultimoOficioCorreios = (int)$stUltimo->fetchColumn();
} catch (Exception $e) {
    $nomesPostos = array();
    $ultimoOficioCorreios = 0;
}
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prévia do Ofício dos Correios</title>
    <style>
        * { box-sizing: border-box; }
        :root {
            --papel: #f4efe4;
            --papel-sombra: #d8d0c0;
            --tinta: #171717;
            --tinta-suave: #5a574f;
            --grade: #2d2b27;
            --tarja: #ebe4d2;
            --destaque: #193c2d;
            --destaque-claro: #2f6a50;
            --aviso: #b46a13;
            --erro: #972d2d;
        }
        html, body {
            margin: 0;
            padding: 0;
            background: #ece8de;
            color: var(--tinta);
            font-family: Georgia, "Times New Roman", serif;
        }
        .pagina {
            min-height: 100vh;
            padding: 28px 18px 48px;
        }
        .barra-acoes {
            max-width: 1180px;
            margin: 0 auto 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }
        .barra-esquerda {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .titulo-pagina {
            font-size: 24px;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }
        .subtitulo-pagina {
            font-size: 13px;
            color: var(--tinta-suave);
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .acoes {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn {
            border: 1px solid var(--grade);
            background: rgba(255,255,255,0.72);
            color: var(--tinta);
            padding: 11px 18px;
            font-size: 13px;
            font-family: Arial, sans-serif;
            font-weight: bold;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            cursor: pointer;
            transition: transform 0.15s ease, background 0.15s ease, color 0.15s ease;
        }
        .btn:hover {
            transform: translateY(-1px);
            background: #fff;
        }
        .btn-principal {
            background: var(--destaque);
            color: #fff;
            border-color: var(--destaque);
        }
        .btn-principal:hover {
            background: var(--destaque-claro);
        }
        .status-barra {
            max-width: 1180px;
            margin: 0 auto 16px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: var(--tinta-suave);
        }
        .status-item {
            padding: 8px 12px;
            background: rgba(255,255,255,0.62);
            border: 1px solid rgba(45,43,39,0.18);
        }
        .status-item strong {
            color: var(--tinta);
        }
        .aviso {
            max-width: 1180px;
            margin: 0 auto 16px;
            padding: 12px 14px;
            border: 1px solid rgba(180,106,19,0.35);
            background: rgba(255,249,232,0.88);
            font-family: Arial, sans-serif;
            font-size: 13px;
            color: #6b460f;
            display: none;
        }
        .aviso.erro {
            border-color: rgba(151,45,45,0.35);
            background: rgba(255,239,239,0.9);
            color: var(--erro);
        }
        .documento {
            max-width: 1080px;
            margin: 0 auto;
            background: #fff;
            box-shadow: 0 12px 32px rgba(24, 23, 19, 0.14);
            padding: 28px 28px 34px;
            position: relative;
        }
        .documento::before {
            content: '';
            position: absolute;
            inset: 10px;
            border: 1px solid rgba(45,43,39,0.1);
            pointer-events: none;
        }
        .cabecalho {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 180px;
            gap: 24px;
            align-items: start;
            margin-bottom: 22px;
        }
        .cabecalho-bloco {
            border: 1px solid var(--grade);
            background: #fff;
        }
        .cabecalho-linha {
            display: grid;
            grid-template-columns: 124px 1fr;
            border-bottom: 1px solid rgba(45,43,39,0.4);
            min-height: 44px;
        }
        .cabecalho-linha:last-child {
            border-bottom: 0;
        }
        .cabecalho-rotulo {
            padding: 10px 12px;
            font-family: Arial, sans-serif;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            border-right: 1px solid rgba(45,43,39,0.4);
            background: #f4f1ea;
        }
        .cabecalho-valor {
            padding: 10px 14px;
            font-size: 14px;
            line-height: 1.35;
        }
        .numero-oficio {
            border: 1px solid var(--grade);
            min-height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: #fff;
        }
        .numero-topo {
            padding: 12px 14px 6px;
            font-family: Arial, sans-serif;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            text-align: center;
        }
        .numero-valor {
            padding: 10px 14px 4px;
            text-align: center;
            font-size: 46px;
            line-height: 1;
        }
        .numero-rodape {
            padding: 10px 14px 14px;
            font-family: Arial, sans-serif;
            font-size: 11px;
            text-align: center;
            color: var(--tinta-suave);
            text-transform: uppercase;
        }
        .documento-meta {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
            margin-bottom: 10px;
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: var(--tinta-suave);
        }
        .texto-abertura {
            margin-bottom: 12px;
            font-size: 13px;
            line-height: 1.5;
            text-align: left;
        }
        .secao {
            margin-bottom: 18px;
            page-break-inside: avoid;
        }
        .secao-titulo {
            padding: 7px 12px;
            border: 1px solid var(--grade);
            border-bottom: 0;
            background: #efefef;
            font-family: Arial, sans-serif;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            background: #fff;
        }
        th, td {
            border: 1px solid var(--grade);
            padding: 8px 10px;
            font-size: 13px;
            vertical-align: middle;
        }
        thead th {
            background: #f3f3f3;
            font-family: Arial, sans-serif;
            font-size: 11px;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }
        .col-posto { width: 32%; }
        .col-iipr { width: 28%; }
        .col-correios { width: 14%; }
        .col-etiqueta { width: 26%; }
        .destino {
            font-weight: bold;
            letter-spacing: 0.02em;
        }
        .posto-linha {
            color: var(--tinta-suave);
        }
        .campo-impressao {
            width: 100%;
            border: 0;
            background: transparent;
            color: var(--tinta);
            font-size: 13px;
            font-family: Georgia, "Times New Roman", serif;
            padding: 0;
            outline: none;
        }
        .campo-impressao[readonly] {
            cursor: default;
        }
        .campo-lacres-iipr {
            resize: none;
            overflow: hidden;
            white-space: pre-wrap;
            line-height: 1.3;
            min-height: 24px;
        }
        .campo-etiqueta {
            border-bottom: 1px dashed rgba(45,43,39,0.45);
            padding-bottom: 2px;
        }
        .campo-etiqueta:focus {
            border-bottom-color: var(--destaque);
        }
        .rodape {
            margin-top: 28px;
            display: flex;
            justify-content: space-between;
            gap: 18px;
            flex-wrap: wrap;
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: var(--tinta-suave);
        }
        .assinatura {
            min-width: 280px;
            padding-top: 26px;
            border-top: 1px solid var(--grade);
            text-align: center;
            color: var(--tinta);
        }
        .vazio {
            border: 1px dashed rgba(45,43,39,0.4);
            padding: 28px;
            background: rgba(255,255,255,0.42);
            text-align: center;
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: var(--tinta-suave);
        }
        .modal {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(18, 18, 18, 0.55);
            z-index: 20;
            padding: 18px;
        }
        .modal.ativo {
            display: flex;
        }
        .modal-conteudo {
            width: min(520px, 100%);
            background: #f7f1e4;
            border: 1px solid var(--grade);
            box-shadow: 0 24px 60px rgba(0,0,0,0.28);
            padding: 24px;
        }
        .modal-titulo {
            margin: 0 0 10px;
            font-size: 24px;
        }
        .modal-texto {
            margin: 0 0 18px;
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: var(--tinta-suave);
        }
        .modal-opcoes {
            display: grid;
            gap: 10px;
        }
        .modal-opcao {
            width: 100%;
            text-align: left;
            padding: 12px 14px;
            background: rgba(255,255,255,0.82);
            border: 1px solid rgba(45,43,39,0.3);
            cursor: pointer;
        }
        .modal-opcao strong {
            display: block;
            font-family: Arial, sans-serif;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 4px;
        }
        .modal-opcao span {
            display: block;
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: var(--tinta-suave);
        }
        .modal-rodape {
            margin-top: 16px;
            display: flex;
            justify-content: flex-end;
        }
        @media (max-width: 900px) {
            .cabecalho {
                grid-template-columns: 1fr;
            }
            .documento {
                padding: 22px 18px 28px;
            }
            .cabecalho-linha {
                grid-template-columns: 96px 1fr;
            }
            th, td {
                padding: 7px 6px;
                font-size: 12px;
            }
        }
        @media print {
            body {
                background: #fff;
            }
            .pagina {
                padding: 0;
            }
            .barra-acoes,
            .status-barra,
            .aviso,
            .modal {
                display: none !important;
            }
            .documento {
                max-width: none;
                box-shadow: none;
                padding: 0;
                background: #fff;
            }
            .documento::before {
                display: none;
            }
            .campo-etiqueta {
                border-bottom: 0;
            }
            .campo-impressao {
                appearance: none;
            }
        }
    </style>
</head>
<body>
    <div class="pagina">
        <div class="barra-acoes">
            <div class="barra-esquerda">
                <div class="titulo-pagina">Prévia do Ofício dos Correios</div>
                <div class="subtitulo-pagina">Documento operacional gerado a partir dos malotes fechados na conferência</div>
            </div>
            <div class="acoes">
                <button type="button" class="btn" id="btnImprimir">Apenas Imprimir</button>
                <button type="button" class="btn btn-principal" id="btnGravarImprimir">Gravar e Imprimir Correios</button>
            </div>
        </div>

        <div class="status-barra">
            <div class="status-item"><strong id="statusNumeroRotulo">Número:</strong> <span id="statusNumeroValor">Prévia</span></div>
            <div class="status-item"><strong>Linhas prontas:</strong> <span id="statusLinhas">0</span></div>
            <div class="status-item"><strong>Datas:</strong> <span id="statusDatas">-</span></div>
            <div class="status-item"><strong>Responsável:</strong> <span id="statusUsuario">-</span></div>
            <div class="status-item"><strong>Atualização:</strong> <span id="statusAtualizacao">-</span></div>
        </div>

        <div class="aviso" id="caixaAviso"></div>

        <div class="documento">
            <div class="cabecalho">
                <div class="cabecalho-bloco">
                    <div class="cabecalho-linha">
                        <div class="cabecalho-rotulo">Cliente</div>
                        <div class="cabecalho-valor">CORREIOS</div>
                    </div>
                    <div class="cabecalho-linha">
                        <div class="cabecalho-rotulo">Referência</div>
                        <div class="cabecalho-valor">Conferência de pacotes e fechamento de malotes de despacho</div>
                    </div>
                    <div class="cabecalho-linha">
                        <div class="cabecalho-rotulo">Período</div>
                        <div class="cabecalho-valor" id="textoPeriodo">Aguardando datas da conferência</div>
                    </div>
                    <div class="cabecalho-linha">
                        <div class="cabecalho-rotulo">Emitido por</div>
                        <div class="cabecalho-valor" id="textoUsuario">Equipe de Conferência</div>
                    </div>
                </div>
                <div class="numero-oficio">
                    <div class="numero-topo">Ofício</div>
                    <div class="numero-valor" id="numeroOficio">Prévia</div>
                    <div class="numero-rodape" id="numeroRodape">Grave para numerar</div>
                </div>
            </div>

            <div class="documento-meta">
                <div>Documento gerado a partir dos grupos Correios fechados na operação.</div>
                <div id="metaUltimoOficio">Último ofício Correios: <?php echo (int)$ultimoOficioCorreios; ?></div>
            </div>

            <div class="texto-abertura">
                Encaminhamos abaixo a composição final dos malotes de despacho dos Correios, já consolidada por destino de ofício. As etiquetas podem ser ajustadas diretamente nesta prévia antes da gravação definitiva do número do ofício.
            </div>

            <div id="areaGrade" class="vazio">Aguardando dados da conferência.</div>

            <div class="rodape">
                <div id="textoRodapeLotes">Nenhuma linha pronta foi consolidada.</div>
                <div class="assinatura">Expedição / Conferência de Pacotes</div>
            </div>
        </div>
    </div>

    <div class="modal" id="modalGravacao" aria-hidden="true">
        <div class="modal-conteudo">
            <h2 class="modal-titulo">Gravar ofício Correios</h2>
            <p class="modal-texto" id="modalTextoBase">Escolha se o documento deve sobrescrever o último número existente ou se deve criar um novo ofício.</p>
            <div class="modal-opcoes">
                <button type="button" class="modal-opcao" id="btnSobrescrever">
                    <strong id="textoSobrescrever">Sobrescrever último</strong>
                    <span id="detalheSobrescrever">Atualiza o último ofício Correios disponível.</span>
                </button>
                <button type="button" class="modal-opcao" id="btnCriarNovo">
                    <strong id="textoCriarNovo">Criar novo</strong>
                    <span id="detalheCriarNovo">Gera um novo número de ofício sem alterar o anterior.</span>
                </button>
            </div>
            <div class="modal-rodape">
                <button type="button" class="btn" id="btnCancelarModal">Cancelar</button>
            </div>
        </div>
    </div>

    <script>
    (function() {
        var nomesPostos = <?php echo json_encode($nomesPostos); ?> || {};
        var ultimoOficioInicial = <?php echo (int)$ultimoOficioCorreios; ?> || 0;
        var paramsUrl = new URLSearchParams(window.location.search || '');
        var canalControle = paramsUrl.get('canal_controle') || 'principal';
        var storageKey = 'conferencia_previa_malotes_v1';
        var areaGrade = document.getElementById('areaGrade');
        var statusNumeroRotulo = document.getElementById('statusNumeroRotulo');
        var statusNumeroValor = document.getElementById('statusNumeroValor');
        var statusLinhas = document.getElementById('statusLinhas');
        var statusDatas = document.getElementById('statusDatas');
        var statusUsuario = document.getElementById('statusUsuario');
        var statusAtualizacao = document.getElementById('statusAtualizacao');
        var caixaAviso = document.getElementById('caixaAviso');
        var textoPeriodo = document.getElementById('textoPeriodo');
        var textoUsuario = document.getElementById('textoUsuario');
        var numeroOficio = document.getElementById('numeroOficio');
        var numeroRodape = document.getElementById('numeroRodape');
        var textoRodapeLotes = document.getElementById('textoRodapeLotes');
        var metaUltimoOficio = document.getElementById('metaUltimoOficio');
        var modalGravacao = document.getElementById('modalGravacao');
        var modalTextoBase = document.getElementById('modalTextoBase');
        var textoSobrescrever = document.getElementById('textoSobrescrever');
        var detalheSobrescrever = document.getElementById('detalheSobrescrever');
        var textoCriarNovo = document.getElementById('textoCriarNovo');
        var detalheCriarNovo = document.getElementById('detalheCriarNovo');
        var estadoOficio = {
            ultimoConhecido: ultimoOficioInicial,
            salvoId: 0,
            salvoNumero: 0,
            salvando: false
        };
        var channel = null;

        function escapeHtml(valor) {
            return String(valor || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function exibirAviso(texto, erro) {
            caixaAviso.textContent = String(texto || '');
            caixaAviso.className = erro ? 'aviso erro' : 'aviso';
            caixaAviso.style.display = texto ? 'block' : 'none';
        }

        function lerSnapshot() {
            try {
                var bruto = localStorage.getItem(storageKey);
                return bruto ? JSON.parse(bruto) : null;
            } catch (e) {
                return null;
            }
        }

        function salvarSnapshot(snapshot) {
            if (!snapshot) return;
            try {
                localStorage.setItem(storageKey, JSON.stringify(snapshot));
            } catch (e1) {}
            if (channel) {
                try {
                    channel.postMessage(snapshot);
                } catch (e2) {}
            }
        }

        function normalizarRegionalTexto(valor) {
            var digitos = String(valor || '').replace(/\D+/g, '');
            if (!digitos) return '';
            return digitos.padStart(3, '0');
        }

        function aplicarEstadoRemotoAoSnapshot(snapshot, estado) {
            if (!snapshot || !snapshot.resumo || !snapshot.resumo.length || !estado) return snapshot;
            var regionalAtiva = normalizarRegionalTexto(estado.regional || '');
            if (!regionalAtiva) return snapshot;
            var alterou = false;
            for (var i = 0; i < snapshot.resumo.length; i++) {
                var item = snapshot.resumo[i] || {};
                var regionalItem = normalizarRegionalTexto(item.regional_codigo || item.contexto_chave || item.regional || '');
                if (regionalItem !== regionalAtiva) continue;
                if (estado.lacre_iipr && item.lacre_iipr !== estado.lacre_iipr) {
                    item.lacre_iipr = String(estado.lacre_iipr || '').trim();
                    alterou = true;
                }
                if (estado.lacre_correios && item.lacre_correios !== estado.lacre_correios) {
                    item.lacre_correios = String(estado.lacre_correios || '').trim();
                    alterou = true;
                }
                if (estado.etiqueta_correios && item.etiqueta_correios !== estado.etiqueta_correios) {
                    item.etiqueta_correios = String(estado.etiqueta_correios || '').trim();
                    alterou = true;
                }
                snapshot.resumo[i] = item;
            }
            if (alterou) {
                salvarSnapshot(snapshot);
            }
            return snapshot;
        }

        function sincronizarComEstadoRemoto() {
            fetch('conferencia_pacotes.php?ler_estado_remoto_ajax=1&canal=' + encodeURIComponent(canalControle), { cache: 'no-store' })
                .then(function(resp) { return resp.json(); })
                .then(function(data) {
                    var estado = data && data.estado ? data.estado : null;
                    if (!estado) return;
                    var snapshot = lerSnapshot();
                    if (!snapshot) return;
                    snapshot = aplicarEstadoRemotoAoSnapshot(snapshot, estado);
                    renderizarQuandoPossivel(snapshot);
                })
                .catch(function() {});
        }

        function nomeSecao(item) {
            var posto = String(item.posto || '').trim();
            var postoPad = posto && /^\d+$/.test(posto) ? posto.padStart(3, '0') : posto;
            if (postoPad === '001') return 'POSTO 001';
            var codigo = parseInt(item.regional_codigo || 0, 10) || 0;
            if (codigo === 0) return 'CAPITAL';
            if (codigo === 1) return 'METROPOLITANA';
            if (codigo === 999) return 'CENTRAL';
            return 'REGIONAIS';
        }

        function garantirChavesResumo(snapshot, persistir) {
            if (!snapshot || !snapshot.resumo || !snapshot.resumo.length) return snapshot;
            var alterou = false;
            for (var i = 0; i < snapshot.resumo.length; i++) {
                var item = snapshot.resumo[i] || {};
                if (!item.row_key) {
                    var contextoBase = item.contexto_chave || item.posto || item.regional_codigo || '';
                    item.row_key = item.grupo_correios ? ('gc:' + item.grupo_correios) : (item.grupo_iipr ? ('gi:' + item.grupo_iipr) : ('ln:' + i + ':' + contextoBase));
                    snapshot.resumo[i] = item;
                    alterou = true;
                }
                if (!item.grupos_correios || !item.grupos_correios.length) {
                    item.grupos_correios = item.grupo_correios ? [item.grupo_correios] : [];
                    snapshot.resumo[i] = item;
                    alterou = true;
                }
            }
            if (alterou && persistir) {
                salvarSnapshot(snapshot);
            }
            return snapshot;
        }

        function calcularLinhasLacres(valor) {
            var texto = String(valor || '').trim();
            if (!texto) return 1;
            var segmentos = texto.split(/\s*,\s*/).filter(function(item) { return !!item; }).length;
            var porTamanho = Math.ceil(texto.length / 24);
            var porSegmentos = Math.ceil(segmentos / 3);
            var linhas = Math.max(1, porTamanho, porSegmentos);
            return Math.min(6, linhas);
        }

        function montarLinhas(snapshot) {
            snapshot = garantirChavesResumo(snapshot, false);
            var resumo = snapshot && snapshot.resumo ? snapshot.resumo : [];
            var linhas = [];
            for (var i = 0; i < resumo.length; i++) {
                var item = resumo[i];
                if (!item) continue;
                var posto = String(item.posto || '').trim();
                var postoPadrao = posto && /^\d+$/.test(posto) ? posto.padStart(3, '0') : posto;
                var contextoTipo = String(item.contexto_tipo || '').trim();
                var contextoRotulo = String(item.contexto_rotulo || '').trim();
                var regionalCodigo = String(item.regional_codigo || '').trim();
                var destinoRotulo = contextoRotulo;
                if (!destinoRotulo && contextoTipo === 'regional' && regionalCodigo) {
                    destinoRotulo = 'Regional ' + regionalCodigo.padStart(3, '0');
                }
                if (!destinoRotulo) {
                    destinoRotulo = postoPadrao && nomesPostos[postoPadrao] ? (postoPadrao + ' - ' + nomesPostos[postoPadrao]) : (postoPadrao ? ('Posto ' + postoPadrao) : 'Sem posto');
                }
                linhas.push({
                    row_key: item.row_key,
                    posto: posto,
                    posto_rotulo: destinoRotulo,
                    contexto_tipo: contextoTipo,
                    contexto_rotulo: contextoRotulo,
                    regional: item.regional || '',
                    regional_codigo: item.regional_codigo || '',
                    grupo_correios: item.grupo_correios || '',
                    grupos_correios: item.grupos_correios || [],
                    grupo_iipr: item.grupo_iipr || '',
                    lacre_iipr: item.lacre_iipr || '',
                    lacre_correios: item.lacre_correios || '',
                    etiqueta_correios: item.etiqueta_correios || '',
                    lotes: item.lotes || [],
                    qtd_total: item.qtd_total || 0,
                    pendente_lacre: !!item.pendente_lacre
                });
            }

            linhas.sort(function(a, b) {
                var regA = parseInt(a.regional_codigo || 0, 10) || 0;
                var regB = parseInt(b.regional_codigo || 0, 10) || 0;
                var ordemA = regA === 0 ? 0 : (regA === 1 ? 1 : (regA === 999 ? 2 : 3));
                var ordemB = regB === 0 ? 0 : (regB === 1 ? 1 : (regB === 999 ? 2 : 3));
                if (ordemA !== ordemB) return ordemA - ordemB;
                if (ordemA === 3 && regA !== regB) return regA - regB;
                var grupoA = String(a.grupo_correios || a.grupo_iipr || a.row_key || '');
                var grupoB = String(b.grupo_correios || b.grupo_iipr || b.row_key || '');
                if (grupoA < grupoB) return -1;
                if (grupoA > grupoB) return 1;
                return 0;
            });
            return linhas;
        }

        function obterTituloPrimeiraColuna(secao) {
            return secao === 'REGIONAIS' ? 'Regionais' : 'Posto';
        }

        function montarCampoEdicao(classeExtra, rowKey, field, value, maxLength) {
            return '<input class="campo-impressao campo-editavel ' + escapeHtml(classeExtra || '') + '" type="text" value="' + escapeHtml(value || '') + '" data-row-key="' + escapeHtml(rowKey || '') + '" data-field="' + escapeHtml(field || '') + '" maxlength="' + escapeHtml(String(maxLength || 35)) + '">';
        }

        function montarTabela(secao, linhas) {
            var html = '';
            html += '<div class="secao">';
            html += '<div class="secao-titulo">' + escapeHtml(secao) + '</div>';
            html += '<table>';
            html += '<thead><tr>';
            html += '<th class="col-posto">' + escapeHtml(obterTituloPrimeiraColuna(secao)) + '</th>';
            html += '<th class="col-iipr">Lacre IIPR</th>';
            html += '<th class="col-correios">Lacre Correios</th>';
            html += '<th class="col-etiqueta">Etiqueta Correios</th>';
            html += '</tr></thead><tbody>';

            for (var i = 0; i < linhas.length; i++) {
                var item = linhas[i];
                html += '<tr data-row-key="' + escapeHtml(item.row_key) + '">';
                html += '<td><div class="destino">' + escapeHtml(item.posto_rotulo) + '</div></td>';
                html += '<td>' + montarCampoEdicao('campo-lacres-iipr', item.row_key, 'lacre_iipr', item.lacre_iipr || '', 80) + '</td>';
                html += '<td>' + montarCampoEdicao('', item.row_key, 'lacre_correios', item.lacre_correios || '', 80) + '</td>';
                html += '<td>' + montarCampoEdicao('campo-etiqueta', item.row_key, 'etiqueta_correios', item.etiqueta_correios || '', 35) + '</td>';
                html += '</tr>';
            }

            html += '</tbody></table></div>';
            return html;
        }

        function atualizarCabecalho(snapshot, linhas) {
            var datas = snapshot && snapshot.datas_filtro && snapshot.datas_filtro.length ? snapshot.datas_filtro.join(', ') : '-';
            var usuario = snapshot && snapshot.usuario ? snapshot.usuario : 'Equipe de Conferência';
            var numeroAtual = estadoOficio.salvoNumero || (snapshot && snapshot.oficio_numero ? parseInt(snapshot.oficio_numero, 10) || 0 : 0);
            var ultimoTexto = estadoOficio.ultimoConhecido > 0 ? String(estadoOficio.ultimoConhecido) : 'nenhum';

            statusLinhas.textContent = String(linhas.length || 0);
            statusDatas.textContent = datas;
            statusUsuario.textContent = usuario;
            statusAtualizacao.textContent = snapshot && snapshot.gerado_em ? snapshot.gerado_em : '-';
            textoPeriodo.textContent = datas === '-' ? 'Aguardando datas da conferência' : datas;
            textoUsuario.textContent = usuario;
            metaUltimoOficio.textContent = 'Último ofício Correios: ' + ultimoTexto;

            if (numeroAtual > 0) {
                numeroOficio.textContent = numeroAtual;
                numeroRodape.textContent = 'Documento gravado';
                statusNumeroRotulo.textContent = 'Número gravado:';
                statusNumeroValor.textContent = String(numeroAtual);
            } else {
                numeroOficio.textContent = 'Prévia';
                numeroRodape.textContent = estadoOficio.ultimoConhecido > 0 ? ('Último existente: ' + estadoOficio.ultimoConhecido) : 'Grave para numerar';
                statusNumeroRotulo.textContent = 'Número:';
                statusNumeroValor.textContent = 'Prévia';
            }

            if (linhas.length) {
                textoRodapeLotes.textContent = linhas.length + ' linha(s) visíveis na prévia. Você pode ajustar lacre IIPR, lacre Correios e etiqueta aqui antes da gravação definitiva, se necessário.';
            } else {
                textoRodapeLotes.textContent = 'Nenhuma linha pronta foi consolidada.';
            }
        }

        function renderizar(snapshot) {
            if (!snapshot) {
                areaGrade.className = 'vazio';
                areaGrade.innerHTML = 'Aguardando dados da conferência.';
                atualizarCabecalho(null, []);
                return;
            }

            snapshot = garantirChavesResumo(snapshot, false);
            var linhas = montarLinhas(snapshot);
            atualizarCabecalho(snapshot, linhas);

            if (!linhas.length) {
                areaGrade.className = 'vazio';
                areaGrade.innerHTML = 'Nenhuma linha pronta do ofício foi gerada ainda.';
                return;
            }

            var grupos = {
                'POSTO 001': [],
                'CAPITAL': [],
                'METROPOLITANA': [],
                'CENTRAL': [],
                'REGIONAIS': []
            };
            for (var i = 0; i < linhas.length; i++) {
                grupos[nomeSecao(linhas[i])].push(linhas[i]);
            }

            var html = '';
            if (grupos['POSTO 001'].length) html += montarTabela('POSTO 001', grupos['POSTO 001']);
            if (grupos['CAPITAL'].length) html += montarTabela('CAPITAL', grupos['CAPITAL']);
            if (grupos['METROPOLITANA'].length) html += montarTabela('METROPOLITANA', grupos['METROPOLITANA']);
            if (grupos['CENTRAL'].length) html += montarTabela('CENTRAL', grupos['CENTRAL']);
            if (grupos['REGIONAIS'].length) html += montarTabela('REGIONAIS', grupos['REGIONAIS']);

            areaGrade.className = '';
            areaGrade.innerHTML = html;
        }

        function atualizarCampoResumo(rowKey, field, value) {
            var snapshot = lerSnapshot();
            if (!snapshot || !snapshot.resumo || !snapshot.resumo.length) return;
            snapshot = garantirChavesResumo(snapshot, false);
            for (var i = 0; i < snapshot.resumo.length; i++) {
                if (String(snapshot.resumo[i].row_key || '') === String(rowKey || '')) {
                    snapshot.resumo[i][field] = value;
                    salvarSnapshot(snapshot);
                    return;
                }
            }
        }

        function estaEditandoCampoResumo() {
            var ativo = document.activeElement;
            return !!(ativo && ativo.classList && ativo.classList.contains('campo-editavel'));
        }

        function renderizarQuandoPossivel(snapshot) {
            if (estaEditandoCampoResumo()) {
                return;
            }
            renderizar(snapshot);
        }

        function fecharModal() {
            modalGravacao.classList.remove('ativo');
            modalGravacao.setAttribute('aria-hidden', 'true');
        }

        function abrirModal() {
            var alvoSobrescrever = estadoOficio.salvoId || estadoOficio.ultimoConhecido || 0;
            var proximo = (estadoOficio.ultimoConhecido || 0) + 1;
            modalTextoBase.textContent = 'Escolha como o número do ofício Correios deve ser tratado para esta prévia.';
            textoSobrescrever.textContent = alvoSobrescrever > 0 ? ('Sobrescrever nº ' + alvoSobrescrever) : 'Sobrescrever último';
            detalheSobrescrever.textContent = alvoSobrescrever > 0 ? ('Regrava o ofício ' + alvoSobrescrever + ' com as linhas atuais da conferência.') : 'Não existe ofício anterior disponível; neste caso será criado um novo automaticamente.';
            textoCriarNovo.textContent = 'Criar novo nº ' + (proximo > 0 ? proximo : 1);
            detalheCriarNovo.textContent = 'Cria um novo ofício sem alterar os anteriores.';
            modalGravacao.classList.add('ativo');
            modalGravacao.setAttribute('aria-hidden', 'false');
        }

        function imprimirDocumento() {
            window.print();
        }

        function gravarOficio(modo) {
            if (estadoOficio.salvando) return;
            var snapshot = lerSnapshot();
            if (!snapshot || !snapshot.resumo || !snapshot.resumo.length) {
                exibirAviso('Nao ha linhas prontas para gravar.', true);
                fecharModal();
                return;
            }

            snapshot = garantirChavesResumo(snapshot, false);
            var formData = new FormData();
            formData.append('salvar_oficio_correios_preview_ajax', '1');
            formData.append('usuario', snapshot.usuario || 'Equipe de Conferência');
            formData.append('modo_oficio', modo === 'novo' ? 'novo' : 'sobrescrever');
            formData.append('id_oficio_sobrescrever', String(estadoOficio.salvoId || estadoOficio.ultimoConhecido || 0));
            formData.append('datas_json', JSON.stringify(snapshot.datas_filtro || []));
            formData.append('datas_str', (snapshot.datas_filtro || []).join(','));
            formData.append('snapshot_oficio', JSON.stringify(snapshot));

            estadoOficio.salvando = true;
            exibirAviso('Gravando ofício Correios...', false);
            fecharModal();

            fetch('conferencia_pacotes.php', {
                method: 'POST',
                body: formData
            })
            .then(function(resp) { return resp.json(); })
            .then(function(json) {
                estadoOficio.salvando = false;
                if (!json || !json.success) {
                    exibirAviso(json && json.erro ? json.erro : 'Falha ao gravar o ofício.', true);
                    return;
                }

                estadoOficio.salvoId = parseInt(json.id_oficio || 0, 10) || 0;
                estadoOficio.salvoNumero = parseInt(json.numero_oficio || 0, 10) || 0;
                if (estadoOficio.salvoNumero > estadoOficio.ultimoConhecido) {
                    estadoOficio.ultimoConhecido = estadoOficio.salvoNumero;
                }

                snapshot.oficio_id_salvo = estadoOficio.salvoId;
                snapshot.oficio_numero = estadoOficio.salvoNumero;
                salvarSnapshot(snapshot);
                renderizar(snapshot);
                exibirAviso('Ofício ' + estadoOficio.salvoNumero + ' gravado com ' + (json.linhas_gravadas || 0) + ' linha(s) e ' + (json.lotes_gravados || 0) + ' lote(s).', false);
                window.setTimeout(function() {
                    imprimirDocumento();
                }, 120);
            })
            .catch(function() {
                estadoOficio.salvando = false;
                exibirAviso('Falha de comunicação ao gravar o ofício.', true);
            });
        }

        document.getElementById('btnImprimir').addEventListener('click', function() {
            exibirAviso('', false);
            imprimirDocumento();
        });

        document.getElementById('btnGravarImprimir').addEventListener('click', function() {
            exibirAviso('', false);
            abrirModal();
        });

        document.getElementById('btnCancelarModal').addEventListener('click', fecharModal);
        document.getElementById('btnSobrescrever').addEventListener('click', function() {
            gravarOficio('sobrescrever');
        });
        document.getElementById('btnCriarNovo').addEventListener('click', function() {
            gravarOficio('novo');
        });

        modalGravacao.addEventListener('click', function(event) {
            if (event.target === modalGravacao) {
                fecharModal();
            }
        });

        areaGrade.addEventListener('input', function(event) {
            var alvo = event.target;
            var field = alvo ? alvo.getAttribute('data-field') : '';
            if (!field) return;
            atualizarCampoResumo(alvo.getAttribute('data-row-key'), field, String(alvo.value || '').trim());
        });

        if (window.BroadcastChannel) {
            try {
                channel = new BroadcastChannel('conferencia_previa_malotes');
                channel.onmessage = function(event) {
                    var snapshot = event.data || null;
                    if (snapshot && estadoOficio.salvoNumero > 0 && !snapshot.oficio_numero) {
                        snapshot.oficio_numero = estadoOficio.salvoNumero;
                        snapshot.oficio_id_salvo = estadoOficio.salvoId;
                    }
                    renderizarQuandoPossivel(snapshot);
                };
            } catch (e) {
                channel = null;
            }
        }

        window.addEventListener('storage', function(event) {
            if (event.key === storageKey) {
                renderizarQuandoPossivel(lerSnapshot());
            }
        });

        areaGrade.addEventListener('blur', function(event) {
            var alvo = event.target;
            if (!alvo || !alvo.classList || !alvo.classList.contains('campo-editavel')) return;
            renderizar(lerSnapshot());
        }, true);

        renderizar(lerSnapshot());
        window.setInterval(function() {
            renderizarQuandoPossivel(lerSnapshot());
        }, 2000);
        sincronizarComEstadoRemoto();
        window.setInterval(sincronizarComEstadoRemoto, 1200);
    })();
    </script>
</body>
</html>