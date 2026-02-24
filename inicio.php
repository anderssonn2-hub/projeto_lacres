<?php
// Pagina inicial do sistema
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - Projeto Lacres</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: "Trebuchet MS", "Segoe UI", Arial, sans-serif;
            background: #f2f5f8;
            color: #222;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .card {
            width: 100%;
            max-width: 640px;
            background: #fff;
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.12);
        }
        h1 {
            font-size: 22px;
            margin-bottom: 6px;
            color: #0d47a1;
        }
        .sub {
            font-size: 13px;
            color: #555;
            margin-bottom: 18px;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
        }
        a.btn {
            display: flex;
            flex-direction: column;
            gap: 6px;
            text-decoration: none;
            padding: 14px 16px;
            border-radius: 12px;
            color: #fff;
            font-weight: 700;
            min-height: 96px;
            justify-content: center;
            box-shadow: 0 6px 14px rgba(0,0,0,0.14);
        }
        .btn span { font-size: 12px; font-weight: 500; opacity: 0.95; }
        .btn-conf { background: linear-gradient(135deg, #2f80ed 0%, #56ccf2 100%); }
        .btn-cons { background: linear-gradient(135deg, #00b09b 0%, #96c93d 100%); }
        .btn-lacres { background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%); color: #4a3200; }
        .btn-vocal { background: linear-gradient(135deg, #c62828 0%, #ef5350 100%); }
        .btn-bloq { background: linear-gradient(135deg, #512da8 0%, #7e57c2 100%); }
        .btn-oficio { background: linear-gradient(135deg, #0f2027 0%, #2c5364 100%); }
        @media (max-width: 600px) {
            .card { padding: 20px; }
            h1 { font-size: 20px; }
            a.btn { min-height: 88px; }
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Projeto Lacres</h1>
        <div class="sub">Escolha a operacao para iniciar.</div>
        <div class="grid">
            <a class="btn btn-conf" href="conferencia_pacotes.php">
                Iniciar conferencia
                <span>Leitura e validacao de pacotes</span>
            </a>
            <a class="btn btn-cons" href="consulta_producao.php">
                Consultar producao
                <span>Busca por lotes, postos e datas</span>
            </a>
            <a class="btn btn-lacres" href="lacres_novo.php">
                Lacres e oficios
                <span>Geracao e controle de lacres</span>
            </a>
            <a class="btn btn-vocal" href="encontra_posto.php">
                Quem eu sou?
                <span>Leitura rapida com voz</span>
            </a>
            <a class="btn btn-oficio" href="gera_oficio_poupa_tempo.php">
                Gerar oficio Poupa Tempo
                <span>Selecionar datas e imprimir</span>
            </a>
            <a class="btn btn-bloq" href="bloqueados.php">
                Bloqueio de postos
                <span>Definir postos nao enviados</span>
            </a>
        </div>
    </div>
</body>
</html>
