<?php
/* gera_oficio_poupa_tempo.php — v1.0
 * Fluxo direto: escolher datas, selecionar postos e abrir o modelo de oficio.
 */

error_reporting(E_ALL & ~E_NOTICE);
header('Content-Type: text/html; charset=utf-8');

if (!isset($_SESSION)) {
    session_start();
}

function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

$pdo_controle = null;
try {
    $pdo_controle = new PDO(
        "mysql:host=10.15.61.169;dbname=controle;charset=utf8",
        "controle_mat",
        "375256"
    );
    $pdo_controle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo_controle->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $ex) {
    die("Erro ao conectar ao banco de dados: " . e($ex->getMessage()));
}

function normalizar_data_sql($d) {
    $d = trim((string)$d);
    if ($d === '') return '';
    if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $d, $m)) {
        return $m[3] . '-' . $m[2] . '-' . $m[1];
    }
    if (preg_match('/^(\d{2})\-(\d{2})\-(\d{4})$/', $d, $m)) {
        return $m[3] . '-' . $m[2] . '-' . $m[1];
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) {
        return $d;
    }
    return '';
}

function formatar_data_exib($d) {
    $d = trim((string)$d);
    if ($d === '') return '';
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $d, $m)) {
        return $m[3] . '-' . $m[2] . '-' . $m[1];
    }
    return $d;
}

$erro = '';
$datas_norm = array();
$datas_exib = array();
$postos_pt = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gerar_oficio_pt'])) {
    $datas_norm = isset($_POST['datas_norm']) ? array_filter(array_map('trim', explode(',', (string)$_POST['datas_norm']))) : array();
    $datas_norm = array_values(array_unique(array_filter($datas_norm)));
    $postos_sel = isset($_POST['postos']) && is_array($_POST['postos']) ? $_POST['postos'] : array();
    $postos_sel = array_values(array_unique(array_filter(array_map('trim', $postos_sel))));

    if (empty($datas_norm)) {
        $erro = 'Informe o periodo antes de gerar o oficio.';
    } elseif (empty($postos_sel)) {
        $erro = 'Selecione ao menos um posto.';
    } else {
        $ph_datas = implode(',', array_fill(0, count($datas_norm), '?'));
        $ph_postos = implode(',', array_fill(0, count($postos_sel), '?'));
        $params = array_merge($datas_norm, $postos_sel);

        $sql = "
            SELECT 
                LPAD(c.posto,3,'0') AS codigo,
                COALESCE(r.nome, CONCAT('POUPA TEMPO - ', LPAD(c.posto,3,'0'))) AS nome,
                SUM(COALESCE(c.quantidade,0)) AS quantidade,
                r.endereco AS endereco
            FROM ciPostosCsv c
            INNER JOIN ciRegionais r 
                    ON LPAD(r.posto,3,'0') = LPAD(c.posto,3,'0')
            WHERE DATE(c.dataCarga) IN ($ph_datas)
              AND REPLACE(LOWER(r.entrega),' ','') LIKE 'poupa%tempo'
              AND LPAD(c.posto,3,'0') IN ($ph_postos)
            GROUP BY 
                LPAD(c.posto,3,'0'), r.nome, r.endereco
            ORDER BY 
                LPAD(c.posto,3,'0')
        ";

        $stmt = $pdo_controle->prepare($sql);
        $stmt->execute($params);

        $payload = array();
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $payload[] = array(
                'codigo'     => (string)$r['codigo'],
                'nome'       => (string)$r['nome'],
                'quantidade' => (int)$r['quantidade'],
                'lacre'      => '',
                'endereco'   => (string)$r['endereco']
            );
        }

        $payload_json = json_encode($payload ?: array(), JSON_UNESCAPED_UNICODE);
        $datas_join = implode(',', $datas_norm);
        ?>
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Gerando Oficio Poupa Tempo</title>
            <style>
                body{font-family:"Trebuchet MS","Segoe UI",Arial,sans-serif;background:#f5f5f5;padding:30px;color:#333;}
                .card{max-width:520px;margin:40px auto;background:#fff;border-radius:10px;padding:20px;box-shadow:0 6px 16px rgba(0,0,0,0.12);text-align:center;}
                .btn{display:inline-block;margin-top:12px;padding:8px 14px;border-radius:6px;background:#1f2b6d;color:#fff;text-decoration:none;font-weight:600;font-size:12px;}
            </style>
        </head>
        <body>
            <div class="card">
                <h3>Gerando oficio Poupa Tempo...</h3>
                <p>Se nao abrir automaticamente, use o botao abaixo.</p>
                <form id="oficioPTForm" method="post" action="modelo_oficio_poupa_tempo.php" target="_blank">
                    <input type="hidden" name="acao" value="oficio_poupatempo" />
                    <input type="hidden" name="pt_datas" value="<?php echo e($datas_join); ?>" />
                    <input type="hidden" name="<?php echo e(session_name()); ?>" value="<?php echo e(session_id()); ?>" />
                    <textarea name="poupatempo_payload" style="display:none;"><?php echo e($payload_json ?: '[]'); ?></textarea>
                    <button type="submit" class="btn">Abrir modelo</button>
                </form>
            </div>
            <script>
                var f = document.getElementById('oficioPTForm');
                if (f) { f.submit(); }
            </script>
        </body>
        </html>
        <?php
        exit;
    }
}

$data_inicial_cal = isset($_GET['data_inicial_cal']) ? trim((string)$_GET['data_inicial_cal']) : '';
$data_final_cal = isset($_GET['data_final_cal']) ? trim((string)$_GET['data_final_cal']) : '';
$datas_alternadas = isset($_GET['datas_alternadas']) ? trim((string)$_GET['datas_alternadas']) : '';

if ($data_inicial_cal !== '' || $data_final_cal !== '' || $datas_alternadas !== '') {
    if ($data_inicial_cal !== '' && $data_final_cal === '') {
        $data_final_cal = $data_inicial_cal;
    }
    if ($data_final_cal !== '' && $data_inicial_cal === '') {
        $data_inicial_cal = $data_final_cal;
    }

    if ($data_inicial_cal !== '' && $data_final_cal !== '') {
        try {
            $dt_ini = new DateTime($data_inicial_cal);
            $dt_fim = new DateTime($data_final_cal);
            if ($dt_ini > $dt_fim) {
                $tmp = $dt_ini; $dt_ini = $dt_fim; $dt_fim = $tmp;
            }
            while ($dt_ini <= $dt_fim) {
                $datas_norm[] = $dt_ini->format('Y-m-d');
                $dt_ini->modify('+1 day');
            }
        } catch (Exception $e) {
        }
    }

    if ($datas_alternadas !== '') {
        $partes = preg_split('/[\s,;]+/', $datas_alternadas);
        foreach ($partes as $p) {
            $ds = normalizar_data_sql($p);
            if ($ds !== '') $datas_norm[] = $ds;
        }
    }

    $datas_norm = array_values(array_unique(array_filter($datas_norm)));
    foreach ($datas_norm as $ds) {
        $datas_exib[] = formatar_data_exib($ds);
    }

    if (!empty($datas_norm)) {
        $ph = implode(',', array_fill(0, count($datas_norm), '?'));
        $sql = "
            SELECT 
                LPAD(c.posto,3,'0') AS codigo,
                COALESCE(r.nome, CONCAT('POUPA TEMPO - ', LPAD(c.posto,3,'0'))) AS nome,
                SUM(COALESCE(c.quantidade,0)) AS quantidade,
                r.endereco AS endereco
            FROM ciPostosCsv c
            INNER JOIN ciRegionais r 
                    ON LPAD(r.posto,3,'0') = LPAD(c.posto,3,'0')
            WHERE DATE(c.dataCarga) IN ($ph)
              AND REPLACE(LOWER(r.entrega),' ','') LIKE 'poupa%tempo'
            GROUP BY 
                LPAD(c.posto,3,'0'), r.nome, r.endereco
            ORDER BY 
                LPAD(c.posto,3,'0')
        ";
        $stmt = $pdo_controle->prepare($sql);
        $stmt->execute($datas_norm);
        $postos_pt = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerar Oficio Poupa Tempo</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:"Trebuchet MS","Segoe UI",Arial,sans-serif;background:#f4f6f9;padding:24px;color:#222}
        .wrap{max-width:980px;margin:0 auto}
        .topo{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px}
        .btn-voltar{display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:6px;background:#1f2b6d;color:#fff;text-decoration:none;font-size:12px;font-weight:600}
        .card{background:#fff;border-radius:12px;padding:18px;box-shadow:0 8px 18px rgba(0,0,0,0.12);margin-bottom:16px}
        h1{font-size:20px;margin-bottom:6px;color:#1f2b6d}
        .sub{font-size:12px;color:#666}
        .linha{display:flex;flex-wrap:wrap;gap:12px;margin-top:12px}
        .linha label{font-size:12px;color:#333;font-weight:600}
        input[type="date"], input[type="text"]{padding:6px 8px;border:1px solid #cfd8dc;border-radius:6px;font-size:12px;min-width:160px}
        .btn{padding:8px 14px;border:none;border-radius:6px;background:#2f80ed;color:#fff;font-weight:700;cursor:pointer;font-size:12px}
        table{width:100%;border-collapse:collapse}
        th,td{border-bottom:1px solid #e0e0e0;padding:8px;font-size:12px;text-align:left}
        th{background:#f1f5f9}
        .acoes{display:flex;align-items:center;gap:12px;margin-top:12px}
        .tag{display:inline-block;background:#eef2ff;color:#1f2b6d;font-size:11px;padding:2px 8px;border-radius:999px}
        .msg{padding:10px 12px;border-radius:8px;background:#fff3cd;border:1px solid #ffeeba;color:#6b5b00;margin-top:10px;font-size:12px}
    </style>
</head>
<body>
<div class="wrap">
    <div class="topo">
        <a class="btn-voltar" href="inicio.php">&larr; Inicio</a>
        <span class="tag">Fluxo direto</span>
    </div>

    <div class="card">
        <h1>Gerar Oficio Poupa Tempo</h1>
        <div class="sub">Selecione o periodo e escolha os postos para gerar o oficio.</div>
        <form method="get" action="">
            <div class="linha">
                <div>
                    <label>Data inicial</label><br>
                    <input type="date" name="data_inicial_cal" value="<?php echo e($data_inicial_cal); ?>">
                </div>
                <div>
                    <label>Data final</label><br>
                    <input type="date" name="data_final_cal" value="<?php echo e($data_final_cal); ?>">
                </div>
                <div style="flex:1;min-width:240px;">
                    <label>Datas alternadas (dd/mm/aaaa)</label><br>
                    <input type="text" name="datas_alternadas" value="<?php echo e($datas_alternadas); ?>" placeholder="Ex: 20/01/2026, 22/01/2026">
                </div>
                <div style="align-self:flex-end;">
                    <button type="submit" class="btn">Aplicar periodo</button>
                </div>
            </div>
        </form>
    </div>

    <?php if ($erro): ?>
        <div class="msg"><?php echo e($erro); ?></div>
    <?php endif; ?>

    <?php if (!empty($datas_norm)): ?>
        <div class="card">
            <h2 style="font-size:16px;margin-bottom:8px;">Postos encontrados</h2>
            <div class="sub">Datas: <?php echo e(implode(', ', $datas_exib)); ?></div>

            <?php if (!empty($postos_pt)): ?>
                <form method="post" action="">
                    <input type="hidden" name="datas_norm" value="<?php echo e(implode(',', $datas_norm)); ?>">
                    <table>
                        <thead>
                            <tr>
                                <th style="width:40px;"><input type="checkbox" id="sel_todos" checked></th>
                                <th>Posto</th>
                                <th>Nome</th>
                                <th>Qtd</th>
                                <th>Endereco</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($postos_pt as $p): ?>
                                <tr>
                                    <td><input type="checkbox" name="postos[]" value="<?php echo e($p['codigo']); ?>" checked></td>
                                    <td><?php echo e($p['codigo']); ?></td>
                                    <td><?php echo e($p['nome']); ?></td>
                                    <td><?php echo e($p['quantidade']); ?></td>
                                    <td><?php echo e($p['endereco']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="acoes">
                        <button type="submit" name="gerar_oficio_pt" class="btn">Gerar oficio</button>
                        <span class="sub">Selecione os postos desejados antes de gerar.</span>
                    </div>
                </form>
            <?php else: ?>
                <div class="msg">Nenhum posto Poupa Tempo encontrado para o periodo.</div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    var sel = document.getElementById('sel_todos');
    if (sel) {
        sel.addEventListener('change', function() {
            var cbs = document.querySelectorAll('input[name="postos[]"]');
            for (var i = 0; i < cbs.length; i++) {
                cbs[i].checked = sel.checked;
            }
        });
    }
</script>
</body>
</html>
