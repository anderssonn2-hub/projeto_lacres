<?php
$comandos = array(
    array(
        'titulo' => 'Armar Lacre IIPR',
        'codigo' => '990000000000000000001',
        'descricao' => 'Leia este código e em seguida leia o lacre IIPR que deve ser aplicado na regional atual.'
    ),
    array(
        'titulo' => 'Armar Lacre Correios',
        'codigo' => '990000000000000000002',
        'descricao' => 'Leia este código e em seguida leia o lacre Correios que fecha o malote maior da regional atual.'
    ),
    array(
        'titulo' => 'Armar Etiqueta Correios',
        'codigo' => '990000000000000000003',
        'descricao' => 'Leia este código e em seguida leia a etiqueta de 35 dígitos do malote Correios aberto.'
    ),
    array(
        'titulo' => 'Cancelar Comando',
        'codigo' => '990000000000000000009',
        'descricao' => 'Cancela o modo de comando atual caso tenha sido armado por engano.'
    )
);
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comandos por Código de Barras v0.9.25.17</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Trebuchet MS", Verdana, sans-serif;
            background: #f3f6fb;
            color: #17324d;
        }
        .pagina {
            max-width: 1120px;
            margin: 0 auto;
            padding: 24px 18px 40px;
        }
        .topo {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 18px;
            margin-bottom: 18px;
            flex-wrap: wrap;
        }
        .topo h1 {
            margin: 0;
            font-size: 28px;
        }
        .topo p {
            margin: 8px 0 0;
            max-width: 760px;
            line-height: 1.6;
            color: #4c6278;
        }
        .btn-imprimir {
            border: 0;
            border-radius: 12px;
            background: #0f766e;
            color: #fff;
            font-weight: 700;
            padding: 12px 18px;
            cursor: pointer;
        }
        .grade {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }
        .card {
            background: #fff;
            border: 1px solid #d7e0ea;
            border-radius: 16px;
            padding: 18px;
            box-shadow: 0 12px 24px rgba(23, 50, 77, 0.08);
        }
        .card h2 {
            margin: 0 0 8px;
            font-size: 20px;
        }
        .descricao {
            min-height: 44px;
            margin: 0 0 14px;
            color: #5e7286;
            font-size: 14px;
            line-height: 1.5;
        }
        .codigo {
            margin-top: 12px;
            font-size: 13px;
            letter-spacing: 0.12em;
            color: #17324d;
            word-break: break-all;
            text-align: center;
        }
        .barcode-wrap {
            background: #fff;
            border: 1px dashed #c2cfdb;
            border-radius: 12px;
            padding: 14px 12px 8px;
            min-height: 132px;
        }
        .barcode-wrap svg {
            display: block;
            width: 100%;
            height: 86px;
        }
        .rodape {
            margin-top: 18px;
            padding: 14px 16px;
            border-radius: 14px;
            background: #e9f6f4;
            color: #0f4f4a;
            line-height: 1.6;
        }
        @media (max-width: 760px) {
            .grade {
                grid-template-columns: 1fr;
            }
        }
        @media print {
            body {
                background: #fff;
            }
            .pagina {
                max-width: none;
                padding: 0;
            }
            .btn-imprimir {
                display: none;
            }
            .card {
                box-shadow: none;
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="pagina">
        <div class="topo">
            <div>
                <h1>Folha de comandos</h1>
                <p>Use estes códigos para operar a página de conferência sem o painel inferior. A leitura do comando arma a ação. A leitura seguinte informa o lacre ou a etiqueta para a regional atual.</p>
            </div>
            <button type="button" class="btn-imprimir" onclick="window.print()">Imprimir folha</button>
        </div>

        <div class="grade">
            <?php foreach ($comandos as $item): ?>
            <section class="card">
                <h2><?php echo htmlspecialchars($item['titulo'], ENT_QUOTES, 'UTF-8'); ?></h2>
                <p class="descricao"><?php echo htmlspecialchars($item['descricao'], ENT_QUOTES, 'UTF-8'); ?></p>
                <div class="barcode-wrap">
                    <svg class="barcode" data-code="<?php echo htmlspecialchars($item['codigo'], ENT_QUOTES, 'UTF-8'); ?>" role="img" aria-label="Código <?php echo htmlspecialchars($item['codigo'], ENT_QUOTES, 'UTF-8'); ?>"></svg>
                    <div class="codigo"><?php echo htmlspecialchars($item['codigo'], ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
            </section>
            <?php endforeach; ?>
        </div>

        <div class="rodape">
            Sequência de uso: 1) leia o comando, 2) leia o valor do lacre ou da etiqueta, 3) confira a confirmação sonora/visual na tela principal. O código de cancelamento limpa o modo armado atual.
        </div>
    </div>

    <script>
    (function() {
        var padroes = {
            '0': 'nnnwwnwnn',
            '1': 'wnnwnnnnw',
            '2': 'nnwwnnnnw',
            '3': 'wnwwnnnnn',
            '4': 'nnnwwnnnw',
            '5': 'wnnwwnnnn',
            '6': 'nnwwwnnnn',
            '7': 'nnnwnnwnw',
            '8': 'wnnwnnwnn',
            '9': 'nnwwnnwnn',
            '*': 'nwnnwnwnn'
        };

        function largura(simbolo) {
            return simbolo === 'w' ? 5 : 2;
        }

        function desenhar(svg, valor) {
            var ns = 'http://www.w3.org/2000/svg';
            var texto = '*' + String(valor || '') + '*';
            var cursor = 12;
            var altura = 78;
            while (svg.firstChild) svg.removeChild(svg.firstChild);

            for (var i = 0; i < texto.length; i++) {
                var ch = texto.charAt(i);
                var padrao = padroes[ch];
                if (!padrao) continue;
                for (var j = 0; j < padrao.length; j++) {
                    var w = largura(padrao.charAt(j));
                    if (j % 2 === 0) {
                        var rect = document.createElementNS(ns, 'rect');
                        rect.setAttribute('x', cursor);
                        rect.setAttribute('y', 4);
                        rect.setAttribute('width', w);
                        rect.setAttribute('height', altura);
                        rect.setAttribute('fill', '#111');
                        svg.appendChild(rect);
                    }
                    cursor += w;
                }
                cursor += 2;
            }

            svg.setAttribute('viewBox', '0 0 ' + (cursor + 12) + ' 90');
            svg.setAttribute('preserveAspectRatio', 'none');
        }

        var barcodes = document.querySelectorAll('.barcode');
        for (var i = 0; i < barcodes.length; i++) {
            desenhar(barcodes[i], barcodes[i].getAttribute('data-code') || '');
        }
    })();
    </script>
</body>
</html>