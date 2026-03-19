<?php
$nomesPostos = array();
try {
    $pdo_controle = new PDO('mysql:host=10.15.61.169;dbname=controle;charset=utf8', 'root', 'vazio');
    $pdo_controle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stNomes = $pdo_controle->query("SELECT LPAD(CAST(posto AS UNSIGNED), 3, '0') AS posto, MAX(nome) AS nome FROM ciPostosCsv GROUP BY LPAD(CAST(posto AS UNSIGNED), 3, '0') ORDER BY LPAD(CAST(posto AS UNSIGNED), 3, '0')");
    while ($rowNome = $stNomes->fetch(PDO::FETCH_ASSOC)) {
        $nomesPostos[$rowNome['posto']] = trim((string)$rowNome['nome']);
    }
} catch (Exception $e) {
    $nomesPostos = array();
}
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prévia do Ofício dos Correios</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f7f7f7;
            color: #111;
        }
        .wrap {
            max-width: 1420px;
            margin: 0 auto;
            padding: 14px;
        }
        .topo {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 10px;
            padding: 8px 10px;
            background: #fff;
            border: 1px solid #cfcfcf;
        }
        .titulo {
            font-size: 20px;
            font-weight: bold;
        }
        .meta {
            font-size: 12px;
            color: #555;
        }
        .secao {
            margin-bottom: 18px;
            page-break-inside: avoid;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
        }
        th, td {
            border: 1px solid #8a8a8a;
            padding: 4px 6px;
            font-size: 12px;
        }
        thead th {
            background: #efefef;
            font-weight: bold;
            text-align: center;
        }
        .col-posto {
            width: 38%;
            text-align: left;
            font-weight: bold;
        }
        .col-iipr { width: 13%; }
        .col-correios { width: 13%; }
        .col-etiqueta { width: 36%; }
        .secao-titulo {
            background: #efefef;
            font-weight: bold;
            text-align: left;
        }
        input[type='text'] {
            width: 100%;
            border: 1px solid #bdbdbd;
            padding: 3px 4px;
            font-size: 12px;
            font-family: Arial, sans-serif;
            background: #fff;
        }
        tr.ativo td {
            background: #fff9db;
        }
        .vazio {
            padding: 18px;
            border: 1px dashed #bbb;
            background: #fff;
            color: #666;
            font-size: 13px;
        }
        @media print {
            body { background: #fff; }
            .wrap { max-width: none; padding: 0; }
            .topo { border: none; padding: 0 0 8px 0; }
            input[type='text'] { border: none; }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="topo">
            <div>
                <div class="titulo">Prévia do Ofício dos Correios</div>
                <div class="meta" id="metaDatas">Aguardando conferência</div>
            </div>
            <div class="meta" id="metaAtualizacao">Última atualização: -</div>
        </div>
        <div id="areaGrade" class="vazio">Aguardando dados da conferência.</div>
    </div>

    <script>
    (function() {
        var nomesPostos = <?php echo json_encode($nomesPostos); ?> || {};
        var storageKey = 'conferencia_previa_malotes_v1';
        var areaGrade = document.getElementById('areaGrade');
        var metaDatas = document.getElementById('metaDatas');
        var metaAtualizacao = document.getElementById('metaAtualizacao');
        var channel = null;

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

        function expandirCompactado(texto) {
            texto = String(texto || '').trim();
            if (!texto) return [];
            var partes = texto.split(',');
            var nums = {};
            for (var i = 0; i < partes.length; i++) {
                var parte = String(partes[i] || '').trim();
                if (!parte) continue;
                if (parte.indexOf('-') !== -1) {
                    var limites = parte.split('-');
                    var ini = parseInt(limites[0], 10) || 0;
                    var fim = parseInt(limites[1], 10) || 0;
                    if (ini > 0 && fim >= ini) {
                        for (var j = ini; j <= fim; j++) nums[j] = j;
                    }
                } else {
                    var n = parseInt(parte, 10) || 0;
                    if (n > 0) nums[n] = n;
                }
            }
            var lista = [];
            for (var chave in nums) {
                if (Object.prototype.hasOwnProperty.call(nums, chave)) lista.push(nums[chave]);
            }
            lista.sort(function(a, b) { return a - b; });
            return lista;
        }

        function compactar(nums) {
            if (!nums || !nums.length) return '';
            var vistos = {};
            var limpos = [];
            for (var i = 0; i < nums.length; i++) {
                var n = parseInt(nums[i], 10) || 0;
                if (n > 0 && !vistos[n]) {
                    vistos[n] = true;
                    limpos.push(n);
                }
            }
            if (!limpos.length) return '';
            limpos.sort(function(a, b) { return a - b; });
            var partes = [];
            var inicio = limpos[0];
            var anterior = limpos[0];
            for (var j = 1; j < limpos.length; j++) {
                if (limpos[j] === anterior + 1) {
                    anterior = limpos[j];
                    continue;
                }
                partes.push(inicio === anterior ? String(inicio) : (inicio + '-' + anterior));
                inicio = limpos[j];
                anterior = limpos[j];
            }
            partes.push(inicio === anterior ? String(inicio) : (inicio + '-' + anterior));
            return partes.join(', ');
        }

        function nomeSecao(item) {
            var codigo = parseInt(item.regional_codigo || 0, 10) || 0;
            if (codigo === 0) return 'CAPITAL';
            if (codigo === 1) return 'METROPOLITANA';
            if (codigo === 999) return 'CENTRAL IIPR';
            return 'REGIONAIS';
        }

        function montarLinhas(snapshot) {
            var mapa = {};
            var resumo = snapshot && snapshot.resumo ? snapshot.resumo : [];
            var pendentes = snapshot && snapshot.pendentes ? snapshot.pendentes : [];

            for (var i = 0; i < pendentes.length; i++) {
                var p = pendentes[i];
                var postoPend = String(p.posto || '').trim();
                if (!postoPend) continue;
                if (!mapa[postoPend]) {
                    mapa[postoPend] = {
                        posto: postoPend,
                        regional: p.regional || '',
                        regional_codigo: p.regional_codigo || '',
                        lacres_iipr: [],
                        lacres_correios: [],
                        etiqueta_correios: ''
                    };
                }
            }

            for (var j = 0; j < resumo.length; j++) {
                var item = resumo[j];
                var posto = String(item.posto || '').trim();
                if (!posto) continue;
                if (!mapa[posto]) {
                    mapa[posto] = {
                        posto: posto,
                        regional: item.regional || '',
                        regional_codigo: item.regional_codigo || '',
                        lacres_iipr: [],
                        lacres_correios: [],
                        etiqueta_correios: ''
                    };
                }
                mapa[posto].regional = mapa[posto].regional || item.regional || '';
                mapa[posto].regional_codigo = mapa[posto].regional_codigo || item.regional_codigo || '';
                mapa[posto].lacres_iipr = mapa[posto].lacres_iipr.concat(expandirCompactado(item.lacre_iipr));
                mapa[posto].lacres_correios = mapa[posto].lacres_correios.concat(expandirCompactado(item.lacre_correios));
                if (!mapa[posto].etiqueta_correios && item.etiqueta_correios) {
                    mapa[posto].etiqueta_correios = item.etiqueta_correios;
                }
            }

            var linhas = [];
            for (var chave in mapa) {
                if (!Object.prototype.hasOwnProperty.call(mapa, chave)) continue;
                mapa[chave].lacre_iipr = compactar(mapa[chave].lacres_iipr);
                mapa[chave].lacre_correios = compactar(mapa[chave].lacres_correios);
                linhas.push(mapa[chave]);
            }

            linhas.sort(function(a, b) {
                var regA = parseInt(a.regional_codigo || 0, 10) || 0;
                var regB = parseInt(b.regional_codigo || 0, 10) || 0;
                var ordemA = regA === 0 ? 0 : (regA === 1 ? 1 : (regA === 999 ? 2 : 3));
                var ordemB = regB === 0 ? 0 : (regB === 1 ? 1 : (regB === 999 ? 2 : 3));
                if (ordemA !== ordemB) return ordemA - ordemB;
                if (regA !== regB && ordemA === 3) return regA - regB;
                return (parseInt(a.posto || 0, 10) || 0) - (parseInt(b.posto || 0, 10) || 0);
            });
            return linhas;
        }

        function montarTabela(secao, linhas, postoAtual) {
            var html = '';
            html += '<div class="secao">';
            html += '<table>';
            html += '<thead>';
            html += '<tr>';
            html += '<th class="col-posto secao-titulo">' + escapeHtml(secao) + '</th>';
            html += '<th class="col-iipr">Lacre IIPR</th>';
            html += '<th class="col-correios">Lacre Correios</th>';
            html += '<th class="col-etiqueta">Etiqueta Correios</th>';
            html += '</tr>';
            html += '</thead>';
            html += '<tbody>';
            for (var i = 0; i < linhas.length; i++) {
                var item = linhas[i];
                var nome = nomesPostos[item.posto] || ('Posto ' + item.posto);
                var classe = String(item.posto) === String(postoAtual || '') ? ' class="ativo"' : '';
                html += '<tr' + classe + '>';
                html += '<td class="col-posto">' + escapeHtml(item.posto + ' - ' + nome) + '</td>';
                html += '<td><input type="text" value="' + escapeHtml(item.lacre_iipr || '') + '" readonly></td>';
                html += '<td><input type="text" value="' + escapeHtml(item.lacre_correios || '') + '" readonly></td>';
                html += '<td><input type="text" value="' + escapeHtml(item.etiqueta_correios || '') + '" readonly></td>';
                html += '</tr>';
            }
            html += '</tbody>';
            html += '</table>';
            html += '</div>';
            return html;
        }

        function renderizar(snapshot) {
            if (!snapshot) {
                areaGrade.className = 'vazio';
                areaGrade.innerHTML = 'Aguardando dados da conferência.';
                metaDatas.textContent = 'Aguardando conferência';
                metaAtualizacao.textContent = 'Última atualização: -';
                return;
            }

            metaDatas.textContent = snapshot.datas_filtro && snapshot.datas_filtro.length ? ('Datas: ' + snapshot.datas_filtro.join(', ')) : 'Sem datas informadas';
            metaAtualizacao.textContent = 'Última atualização: ' + (snapshot.gerado_em || '-');

            var linhas = montarLinhas(snapshot);
            if (!linhas.length) {
                areaGrade.className = 'vazio';
                areaGrade.innerHTML = 'Nenhum posto conferido ainda.';
                return;
            }

            var grupos = {
                'CAPITAL': [],
                'METROPOLITANA': [],
                'CENTRAL IIPR': [],
                'REGIONAIS': []
            };
            for (var i = 0; i < linhas.length; i++) {
                grupos[nomeSecao(linhas[i])].push(linhas[i]);
            }

            var html = '';
            if (grupos['CAPITAL'].length) html += montarTabela('CAPITAL', grupos['CAPITAL'], snapshot.posto_selecionado);
            if (grupos['METROPOLITANA'].length) html += montarTabela('METROPOLITANA', grupos['METROPOLITANA'], snapshot.posto_selecionado);
            if (grupos['CENTRAL IIPR'].length) html += montarTabela('CENTRAL IIPR', grupos['CENTRAL IIPR'], snapshot.posto_selecionado);
            if (grupos['REGIONAIS'].length) html += montarTabela('REGIONAIS', grupos['REGIONAIS'], snapshot.posto_selecionado);

            areaGrade.className = '';
            areaGrade.innerHTML = html;
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
                renderizar(lerSnapshot());
            }
        });

        renderizar(lerSnapshot());
        window.setInterval(function() {
            renderizar(lerSnapshot());
        }, 2000);
    })();
    </script>
</body>
</html>