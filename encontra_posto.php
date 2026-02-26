<?php
/* encontra_posto.php — v2.3
 * Triagem rapida: leitura de codigo de barras, busca em ciRegionais,
 * vocalizacao e exibicao visual do posto.
 * Registra leituras para controle da estante.
 */

function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function normalizarDataEntrada($s) {
    $s = trim((string)$s);
    if ($s === '') return '';
    if (preg_match('/^(\d{2})\-(\d{2})\-(\d{4})$/', $s, $m)) {
        return $m[3] . '-' . $m[2] . '-' . $m[1];
    }
    if (preg_match('/^(\d{4})\-(\d{2})\-(\d{2})$/', $s, $m)) {
        return $m[1] . '-' . $m[2] . '-' . $m[3];
    }
    return '';
}

function parseDatasAlvo($raw) {
    $out = array();
    $partes = preg_split('/[;,\s]+/', (string)$raw);
    foreach ($partes as $p) {
        $p = trim($p);
        if ($p === '') continue;
        $n = normalizarDataEntrada($p);
        if ($n !== '') {
            $out[] = $n;
        }
    }
    $out = array_values(array_unique($out));
    return $out;
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

    $pdo->exec("CREATE TABLE IF NOT EXISTS lotes_na_estante (
        id INT NOT NULL AUTO_INCREMENT,
        lote INT(8) NOT NULL,
        regional INT(3) NOT NULL,
        posto INT(3) NOT NULL,
        quantidade INT(5) NOT NULL,
        producao_de DATE NOT NULL,
        triado_em DATETIME NOT NULL,
        PRIMARY KEY (id)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8");

    $pdo->exec("CREATE TABLE IF NOT EXISTS ciPacotesEstanteLimpeza (
        id INT NOT NULL AUTO_INCREMENT,
        datas_alvo VARCHAR(255) NOT NULL,
        responsavel VARCHAR(120) NOT NULL,
        total_apagado INT NOT NULL,
        criado DATETIME NOT NULL,
        PRIMARY KEY (id)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8");

    if (isset($_POST['ajax_estante_status'])) {
        header('Content-Type: application/json');
        $datas_alvo = parseDatasAlvo(isset($_POST['datas_alvo']) ? $_POST['datas_alvo'] : '');
        if (empty($datas_alvo)) {
            die(json_encode(array('success' => true, 'estante' => array('total' => 0, 'capital' => 0, 'central' => 0, 'regional' => 0, 'poupatempo' => 0))));
        }
        $estante_stats = array('total' => 0, 'capital' => 0, 'central' => 0, 'regional' => 0, 'poupatempo' => 0);
        $sem_upload = array('total' => 0, 'lotes' => array());
        try {
            $ph = implode(',', array_fill(0, count($datas_alvo), '?'));
            $stmtTot = $pdo->prepare("SELECT COUNT(DISTINCT lote) FROM lotes_na_estante WHERE producao_de IN ($ph)");
            $stmtTot->execute($datas_alvo);
            $estante_stats['total'] = (int)$stmtTot->fetchColumn();

            $stmtTipos = $pdo->prepare("SELECT DISTINCT l.lote, l.posto, l.regional, r.entrega
                FROM lotes_na_estante l
                LEFT JOIN ciRegionais r ON LPAD(r.posto,3,'0') = LPAD(l.posto,3,'0')
                WHERE l.producao_de IN ($ph)");
            $stmtTipos->execute($datas_alvo);
            while ($row = $stmtTipos->fetch(PDO::FETCH_ASSOC)) {
                $entrega = strtolower(trim(str_replace(' ', '', (string)$row['entrega'])));
                if (strpos($entrega, 'poupa') !== false || strpos($entrega, 'tempo') !== false) {
                    $estante_stats['poupatempo']++;
                } elseif ((int)$row['regional'] === 0) {
                    $estante_stats['capital']++;
                } elseif ((int)$row['regional'] === 999) {
                    $estante_stats['central']++;
                } else {
                    $estante_stats['regional']++;
                }
            }

            $stmtSem = $pdo->prepare("SELECT DISTINCT LPAD(l.lote,8,'0') AS lote
                FROM lotes_na_estante l
                LEFT JOIN ciPostosCsv c ON c.lote = l.lote AND DATE(c.dataCarga) = l.producao_de
                WHERE l.producao_de IN ($ph) AND c.lote IS NULL
                ORDER BY l.lote LIMIT 50");
            $stmtSem->execute($datas_alvo);
            while ($row = $stmtSem->fetch(PDO::FETCH_ASSOC)) {
                $sem_upload['lotes'][] = $row['lote'];
            }
            $stmtSemTot = $pdo->prepare("SELECT COUNT(DISTINCT l.lote)
                FROM lotes_na_estante l
                LEFT JOIN ciPostosCsv c ON c.lote = l.lote AND DATE(c.dataCarga) = l.producao_de
                WHERE l.producao_de IN ($ph) AND c.lote IS NULL");
            $stmtSemTot->execute($datas_alvo);
            $sem_upload['total'] = (int)$stmtSemTot->fetchColumn();
        } catch (Exception $e) {
            // ignore
        }
        die(json_encode(array('success' => true, 'estante' => $estante_stats, 'sem_upload' => $sem_upload)));
    }

    if (isset($_POST['ajax_limpar_estante'])) {
        header('Content-Type: application/json');
        $responsavel = isset($_POST['responsavel']) ? trim($_POST['responsavel']) : '';
        $datas_alvo = parseDatasAlvo(isset($_POST['datas_alvo']) ? $_POST['datas_alvo'] : '');
        if ($responsavel === '') {
            die(json_encode(array('success' => false, 'erro' => 'Responsavel obrigatorio')));
        }
        if (empty($datas_alvo)) {
            die(json_encode(array('success' => false, 'erro' => 'Informe a(s) data(s) da estante')));
        }
        $ph = implode(',', array_fill(0, count($datas_alvo), '?'));
        $stmtDel = $pdo->prepare("DELETE FROM lotes_na_estante WHERE producao_de IN ($ph)");
        $stmtDel->execute($datas_alvo);
        $apagados = $stmtDel->rowCount();
        $stmtLog = $pdo->prepare("INSERT INTO ciPacotesEstanteLimpeza (datas_alvo, responsavel, total_apagado, criado) VALUES (?,?,?,NOW())");
        $stmtLog->execute(array(implode(',', $datas_alvo), $responsavel, $apagados));
        die(json_encode(array('success' => true, 'apagados' => $apagados)));
    }

    if (isset($_POST['ajax_buscar_posto'])) {
        header('Content-Type: application/json');
        $codbar = isset($_POST['codbar']) ? trim($_POST['codbar']) : '';
        $codbar_limpo = preg_replace('/\D+/', '', $codbar);
        $datas_alvo = parseDatasAlvo(isset($_POST['datas_alvo']) ? $_POST['datas_alvo'] : '');

        if (empty($datas_alvo)) {
            die(json_encode(array('success' => false, 'erro' => 'Informe a(s) data(s) da estante')));
        }

        if (!preg_match('/^\d{19}$/', $codbar_limpo)) {
            die(json_encode(array('success' => false, 'erro' => 'Codigo de barras invalido (19 digitos)')));
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

        $tipo_estante = 'regional';
        if ($entrega_tipo === 'poupatempo') {
            $tipo_estante = 'poupatempo';
        } elseif ($regional_real === 0) {
            $tipo_estante = 'capital';
        } elseif ($regional_real === 999) {
            $tipo_estante = 'central';
        }

        $data_producao = null;
        try {
            $stmtProd = $pdo->prepare("SELECT DATE(dataCarga) AS data_prod FROM ciPostosCsv WHERE lote = ? ORDER BY dataCarga DESC LIMIT 1");
            $stmtProd->execute(array((int)$lote));
            $rowProd = $stmtProd->fetch(PDO::FETCH_ASSOC);
            if ($rowProd && !empty($rowProd['data_prod'])) {
                $data_producao = $rowProd['data_prod'];
            }
        } catch (Exception $e) {
            $data_producao = null;
        }

        if ($data_producao && !in_array($data_producao, $datas_alvo)) {
            $data_br = date('d-m-Y', strtotime($data_producao));
            die(json_encode(array(
                'success' => false,
                'erro' => 'Pacote de outra data: ' . $data_br,
                'data_producao' => $data_br
            )));
        }

        $status_estante = $data_producao ? 'ok' : 'sem_upload';
        $data_alvo = $data_producao ? $data_producao : $datas_alvo[0];

        $estante_novo = false;
        try {
            $stmtCheck = $pdo->prepare("SELECT id FROM lotes_na_estante WHERE lote = ? LIMIT 1");
            $stmtCheck->execute(array((int)$lote));
            if (!$stmtCheck->fetch()) {
                $stmtIns = $pdo->prepare("INSERT INTO lotes_na_estante (lote, regional, posto, quantidade, producao_de, triado_em) VALUES (?,?,?,?,?,NOW())");
                $stmtIns->execute(array(
                    (int)$lote,
                    (int)$regional_real,
                    (int)$posto_num,
                    (int)$quantidade,
                    $data_alvo
                ));
                $estante_novo = true;
            }
        } catch (Exception $e) {
            $estante_novo = false;
        }

        $estante_stats = array('total' => 0, 'capital' => 0, 'central' => 0, 'regional' => 0, 'poupatempo' => 0);
        $sem_upload = array('total' => 0, 'lotes' => array());
        try {
            $ph = implode(',', array_fill(0, count($datas_alvo), '?'));
            $stmtTot = $pdo->prepare("SELECT COUNT(DISTINCT lote) FROM lotes_na_estante WHERE producao_de IN ($ph)");
            $stmtTot->execute($datas_alvo);
            $estante_stats['total'] = (int)$stmtTot->fetchColumn();

            $stmtTipos = $pdo->prepare("SELECT DISTINCT l.lote, l.posto, l.regional, r.entrega
                FROM lotes_na_estante l
                LEFT JOIN ciRegionais r ON LPAD(r.posto,3,'0') = LPAD(l.posto,3,'0')
                WHERE l.producao_de IN ($ph)");
            $stmtTipos->execute($datas_alvo);
            while ($row = $stmtTipos->fetch(PDO::FETCH_ASSOC)) {
                $entrega = strtolower(trim(str_replace(' ', '', (string)$row['entrega'])));
                if (strpos($entrega, 'poupa') !== false || strpos($entrega, 'tempo') !== false) {
                    $estante_stats['poupatempo']++;
                } elseif ((int)$row['regional'] === 0) {
                    $estante_stats['capital']++;
                } elseif ((int)$row['regional'] === 999) {
                    $estante_stats['central']++;
                } else {
                    $estante_stats['regional']++;
                }
            }

            $stmtSem = $pdo->prepare("SELECT DISTINCT LPAD(l.lote,8,'0') AS lote
                FROM lotes_na_estante l
                LEFT JOIN ciPostosCsv c ON c.lote = l.lote AND DATE(c.dataCarga) = l.producao_de
                WHERE l.producao_de IN ($ph) AND c.lote IS NULL
                ORDER BY l.lote LIMIT 50");
            $stmtSem->execute($datas_alvo);
            while ($row = $stmtSem->fetch(PDO::FETCH_ASSOC)) {
                $sem_upload['lotes'][] = $row['lote'];
            }
            $stmtSemTot = $pdo->prepare("SELECT COUNT(DISTINCT l.lote)
                FROM lotes_na_estante l
                LEFT JOIN ciPostosCsv c ON c.lote = l.lote AND DATE(c.dataCarga) = l.producao_de
                WHERE l.producao_de IN ($ph) AND c.lote IS NULL");
            $stmtSemTot->execute($datas_alvo);
            $sem_upload['total'] = (int)$stmtSemTot->fetchColumn();
        } catch (Exception $e) {
            // ignore
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
            'codbar' => $codbar_limpo,
            'estante_novo' => $estante_novo,
            'estante' => $estante_stats,
            'sem_upload' => $sem_upload,
            'status_estante' => $status_estante,
            'data_alvo' => $data_alvo,
            'data_producao' => $data_producao ? date('d-m-Y', strtotime($data_producao)) : null
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
    <title>Encontra Posto v0.9.25.0 - Triagem Rapida</title>
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

        .painel-datas {
            background: white; border-radius: 10px;
            padding: 16px 20px; margin-bottom: 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }
        .painel-datas label { font-weight: 700; color: #333; display:block; margin-bottom:6px; font-size:13px; }
        #datas_estante {
            width: 100%; max-width: 520px;
            padding: 10px 12px; font-size: 14px;
            border: 2px solid #3949ab; border-radius: 6px;
            background: #e8eaf6; font-weight: 700;
        }
        .acoes-estante {
            margin-top: 10px; display:flex; flex-wrap:wrap; gap:8px; align-items:center;
        }
        #responsavelLimpeza {
            padding: 8px 10px; border: 1px solid #ccc; border-radius: 6px; min-width: 220px;
        }
        #btnLimparEstante {
            background: #d32f2f; color:#fff; border:none; border-radius:6px; padding:8px 14px; font-weight:700; cursor:pointer;
        }
        #btnLimparEstante:hover { background:#b71c1c; }
        .nota-datas { font-size: 11px; color:#666; margin-top:6px; }

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

        .painel-sem-upload {
            background: #ffffff; border-radius: 10px;
            padding: 16px 20px; margin-bottom: 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }
        .painel-sem-upload h3 { margin: 0 0 8px; font-size: 15px; color:#333; }
        .lista-lotes { display:flex; flex-wrap:wrap; gap:6px; }
        .lote-badge {
            background:#263238; color:#fff; padding:4px 8px; border-radius:6px; font-size:11px; font-weight:700;
        }

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

        .banner-datas {
            position: sticky;
            top: 64px;
            z-index: 900;
            background: #0d47a1;
            color: #fff;
            padding: 10px 16px;
            border-radius: 8px;
            margin: 10px auto 16px;
            max-width: 800px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        .banner-datas .datas-ativas { font-weight: 700; font-size: 13px; }
        .banner-datas button {
            background: #ffeb3b;
            border: none;
            border-radius: 6px;
            padding: 6px 10px;
            font-weight: 800;
            cursor: pointer;
        }

        .overlay-datas {
            position: fixed; left: 0; top: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.55);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 3000;
        }
        .overlay-datas .card {
            background: #fff;
            padding: 18px;
            border-radius: 10px;
            width: 420px;
            max-width: 92%;
            box-shadow: 0 6px 18px rgba(0,0,0,0.2);
        }
        .overlay-datas h3 { margin: 0 0 8px; color:#1a237e; }
        .overlay-datas .hint { font-size: 12px; color:#666; margin-bottom: 10px; }
        .overlay-datas input {
            width: 100%; padding: 10px 12px;
            border: 2px solid #3949ab; border-radius: 6px;
            background: #e8eaf6; font-weight: 700;
        }
        .overlay-datas .acoes {
            margin-top: 12px; display:flex; gap:8px; justify-content:flex-end;
        }
        .overlay-datas .btn-primario {
            background:#1a237e; color:#fff; border:none; border-radius:6px; padding:8px 12px; font-weight:800;
        }
        .overlay-datas .btn-sec {
            background:#cfd8dc; color:#333; border:none; border-radius:6px; padding:8px 12px; font-weight:700;
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
        <span class="versao">v0.9.25.0</span>
        <span style="font-size:12px; font-weight:700; color:#ffeb3b;">versao 0.9.25.0 (em atualizacao)</span>
        <span style="font-size:11px; opacity:0.85;">build <?php echo date('d-m-Y H:i'); ?></span>
    </div>
    <label class="toggle-voz">
        <input type="checkbox" id="toggleVoz" checked>
        <span class="toggle-slider"></span>
        Voz ativa
    </label>
</div>

<div class="area-principal">

    <div class="banner-datas" id="bannerDatas" style="display:none;">
        <div class="datas-ativas" id="datasAtivasTexto">Datas ativas:</div>
        <button type="button" id="btnAlterarDatas">Alterar datas</button>
    </div>

    <div class="painel-leitura">
        <label>Codigo de Barras do Pacote (19 digitos):</label>
        <input type="text" id="input_codbar" placeholder="Escaneie ou digite o codigo..." autocomplete="off" autofocus>
        <div id="indicadorFoco" style="margin-top:8px; font-size:13px; font-weight:700; color:#4caf50;">Pronto para leitura</div>
    </div>

    <div class="painel-datas">
        <label>Datas da estante (dd-mm-aaaa ou yyyy-mm-dd, separadas por virgula):</label>
        <input type="text" id="datas_estante" placeholder="Ex: 24-02-2026, 25-02-2026">
        <div class="acoes-estante">
            <input type="text" id="responsavelLimpeza" placeholder="Responsavel pela limpeza">
            <button type="button" id="btnLimparEstante">Limpar estante</button>
        </div>
        <div class="nota-datas">As leituras serao contabilizadas apenas para as datas informadas.</div>
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
        <div class="stat-card" style="border-left-color:#b71c1c;">
            <h4>Sem Upload</h4>
            <div class="valor" id="statSemUpload" style="color:#b71c1c;">0</div>
        </div>
    </div>

    <div class="painel-sem-upload" id="painelSemUpload" style="display:none;">
        <h3>📦 Lotes sem upload (ciPostosCsv)</h3>
        <div class="lista-lotes" id="listaSemUpload"></div>
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

<div class="overlay-datas" id="overlayDatas" style="display:flex;">
    <div class="card">
        <h3>Datas da Estante</h3>
        <div class="hint">Informe as datas que serao triadas (dd-mm-aaaa ou yyyy-mm-dd). Ex: 24-02-2026, 25-02-2026</div>
        <input type="text" id="datas_estante_modal" placeholder="Ex: 24-02-2026, 25-02-2026">
        <div class="acoes">
            <button type="button" class="btn-sec" id="btnCancelarDatas">Cancelar</button>
            <button type="button" class="btn-primario" id="btnConfirmarDatas">Aplicar</button>
        </div>
    </div>
</div>

<script>
var vozAtiva = true;
var historico = [];
var contTotal = 0;
var contCapital = 0;
var contCentral = 0;
var contRegional = 0;
var contPT = 0;
var contSemUpload = 0;
var lotesSemUpload = [];
var audioFilaAtiva = false;
var audioFila = [];

var leituraFila = [];
var leituraAtiva = false;

function formatarHoje() {
    var d = new Date();
    var dd = (d.getDate() < 10 ? '0' : '') + d.getDate();
    var mm = (d.getMonth() + 1 < 10 ? '0' : '') + (d.getMonth() + 1);
    var yyyy = d.getFullYear();
    return dd + '-' + mm + '-' + yyyy;
}

function obterDatasAlvoStr() {
    var input = document.getElementById('datas_estante');
    return input ? input.value.trim() : '';
}

function salvarDatasAlvo() {
    var val = obterDatasAlvoStr();
    if (val !== '') {
        localStorage.setItem('estante_datas', val);
    }
    atualizarBannerDatas();
    carregarEstanteInicial();
}

function atualizarBannerDatas() {
    var banner = document.getElementById('bannerDatas');
    var texto = document.getElementById('datasAtivasTexto');
    var datas = obterDatasAlvoStr();
    if (!banner || !texto) return;
    if (datas) {
        texto.textContent = 'Datas ativas: ' + datas;
        banner.style.display = 'flex';
    } else {
        banner.style.display = 'none';
    }
}

function abrirModalDatas() {
    var overlay = document.getElementById('overlayDatas');
    var inputModal = document.getElementById('datas_estante_modal');
    if (inputModal) {
        inputModal.value = obterDatasAlvoStr() || '';
        inputModal.focus();
    }
    if (overlay) overlay.style.display = 'flex';
}

function fecharModalDatas() {
    var overlay = document.getElementById('overlayDatas');
    if (overlay) overlay.style.display = 'none';
}

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

function processarCodigoBruto(valor) {
    var val = (valor || '').replace(/\D+/g, '');
    if (val.length < 19) {
        return;
    }
    if (val.length > 19) {
        val = val.substr(0, 19);
    }
    buscarPosto(val);
}

document.getElementById('input_codbar').addEventListener('input', function() {
    var val = this.value;
    if (!val) return;
    var limpo = val.replace(/\D+/g, '');
    if (limpo.length >= 19) {
        this.value = '';
        processarCodigoBruto(limpo);
    }
});

document.getElementById('input_codbar').onkeydown = function(ev) {
    if (ev.keyCode === 13) {
        var val = this.value;
        this.value = '';
        processarCodigoBruto(val);
    }
};

document.getElementById('input_codbar').onfocus = function() {
    atualizarIndicadorFoco();
};

document.getElementById('input_codbar').onblur = function() {
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

function finalizarLeitura() {
    leituraAtiva = false;
    if (leituraFila.length > 0) {
        var prox = leituraFila.shift();
        buscarPosto(prox);
    }
}

function buscarPosto(codbar) {
    var datasAlvo = obterDatasAlvoStr();
    if (!datasAlvo) {
        exibirErro('Informe a(s) data(s) da estante');
        return;
    }
    if (leituraAtiva) {
        leituraFila.push(codbar);
        return;
    }
    leituraAtiva = true;
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
            finalizarLeitura();
        }
    };
    xhr.send('ajax_buscar_posto=1&codbar=' + encodeURIComponent(codbar) + '&datas_alvo=' + encodeURIComponent(datasAlvo));
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

    if (dados.estante_novo === false) {
        var linhaLido = document.createElement('div');
        linhaLido.className = 'info-linha';
        var labelLido = document.createElement('span');
        labelLido.className = 'info-label';
        labelLido.textContent = 'Status';
        var valorLido = document.createElement('span');
        valorLido.className = 'info-valor';
        valorLido.textContent = 'Lote ja contabilizado';
        linhaLido.appendChild(labelLido);
        linhaLido.appendChild(valorLido);
        body.appendChild(linhaLido);
    }

    if (dados.status_estante === 'sem_upload') {
        var linhaSem = document.createElement('div');
        linhaSem.className = 'info-linha';
        var labelSem = document.createElement('span');
        labelSem.className = 'info-label';
        labelSem.textContent = 'Status';
        var valorSem = document.createElement('span');
        valorSem.className = 'info-valor';
        valorSem.textContent = 'Sem upload (verifique data)';
        linhaSem.appendChild(labelSem);
        linhaSem.appendChild(valorSem);
        body.appendChild(linhaSem);
    }

    if (dados.data_producao) {
        var linhaData = document.createElement('div');
        linhaData.className = 'info-linha';
        var labelData = document.createElement('span');
        labelData.className = 'info-label';
        labelData.textContent = 'Data producao';
        var valorData = document.createElement('span');
        valorData.className = 'info-valor';
        valorData.textContent = dados.data_producao;
        linhaData.appendChild(labelData);
        linhaData.appendChild(valorData);
        body.appendChild(linhaData);
    }

    div.style.display = 'block';

    falar(dados.voz);

    if (dados.estante) {
        contTotal = dados.estante.total || 0;
        contCapital = dados.estante.capital || 0;
        contCentral = dados.estante.central || 0;
        contRegional = dados.estante.regional || 0;
        contPT = dados.estante.poupatempo || 0;
    }
    if (dados.sem_upload) {
        contSemUpload = dados.sem_upload.total || 0;
        lotesSemUpload = dados.sem_upload.lotes || [];
        renderizarSemUpload();
    }

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
    var elSem = document.getElementById('statSemUpload');
    if (elSem) elSem.textContent = contSemUpload;
}

function renderizarSemUpload() {
    var painel = document.getElementById('painelSemUpload');
    var lista = document.getElementById('listaSemUpload');
    if (!painel || !lista) return;
    if (!lotesSemUpload || lotesSemUpload.length === 0) {
        painel.style.display = 'none';
        lista.innerHTML = '';
        return;
    }
    painel.style.display = 'block';
    var html = '';
    for (var i = 0; i < lotesSemUpload.length; i++) {
        html += '<span class="lote-badge">' + lotesSemUpload[i] + '</span>';
    }
    lista.innerHTML = html;
}

function carregarEstanteInicial() {
    var datasAlvo = obterDatasAlvoStr();
    if (!datasAlvo) {
        contTotal = 0; contCapital = 0; contCentral = 0; contRegional = 0; contPT = 0; contSemUpload = 0; lotesSemUpload = [];
        renderizarSemUpload();
        atualizarStats();
        return;
    }
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'encontra_posto.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            try {
                var resp = JSON.parse(xhr.responseText);
                if (resp.success && resp.estante) {
                    contTotal = resp.estante.total || 0;
                    contCapital = resp.estante.capital || 0;
                    contCentral = resp.estante.central || 0;
                    contRegional = resp.estante.regional || 0;
                    contPT = resp.estante.poupatempo || 0;
                    if (resp.sem_upload) {
                        contSemUpload = resp.sem_upload.total || 0;
                        lotesSemUpload = resp.sem_upload.lotes || [];
                        renderizarSemUpload();
                    }
                    atualizarStats();
                }
            } catch (e) {}
        }
    };
    xhr.send('ajax_estante_status=1&datas_alvo=' + encodeURIComponent(datasAlvo));
}

function limparEstante() {
    var responsavel = document.getElementById('responsavelLimpeza').value.trim();
    var datasAlvo = obterDatasAlvoStr();
    if (!datasAlvo) {
        exibirErro('Informe a(s) data(s) da estante');
        return;
    }
    if (!responsavel) {
        exibirErro('Responsavel obrigatorio para limpeza');
        return;
    }
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'encontra_posto.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    var resp = JSON.parse(xhr.responseText);
                    if (resp.success) {
                        carregarEstanteInicial();
                        alert('Estante limpa. Registros apagados: ' + resp.apagados);
                    } else {
                        exibirErro(resp.erro || 'Erro ao limpar estante');
                    }
                } catch (e) {
                    exibirErro('Erro ao processar resposta');
                }
            } else {
                exibirErro('Erro de conexao');
            }
        }
    };
    xhr.send('ajax_limpar_estante=1&responsavel=' + encodeURIComponent(responsavel) + '&datas_alvo=' + encodeURIComponent(datasAlvo));
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
        hora: hora,
        jaLido: (dados.estante_novo === false),
        semUpload: (dados.status_estante === 'sem_upload')
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
        var badgeLido = h.jaLido ? '<span style="background:#757575;color:white;padding:2px 6px;border-radius:3px;font-size:10px;font-weight:700;">JA LIDO</span>' : '';
        var badgeSem = h.semUpload ? '<span style="background:#b71c1c;color:white;padding:2px 6px;border-radius:3px;font-size:10px;font-weight:700;">SEM UPLOAD</span>' : '';
        html += '<div class="historico-item">' +
            '<span class="hi-posto">' + postoLabel + '</span>' +
            '<span class="hi-tipo">' + h.tipoTag + '</span>' +
            badgeLido +
            badgeSem +
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
        if (campo) { campo.value = ''; campo.focus(); }
        atualizarIndicadorFoco();
    } else {
        if (campo) { campo.value = ''; }
    }
});

window.addEventListener('focus', function() {
    if (!wakeLockSentinel) { solicitarWakeLock(); }
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
var inputDatas = document.getElementById('datas_estante');
if (inputDatas) {
    var salva = localStorage.getItem('estante_datas') || '';
    inputDatas.value = salva !== '' ? salva : formatarHoje();
    inputDatas.addEventListener('change', salvarDatasAlvo);
    inputDatas.addEventListener('blur', salvarDatasAlvo);
}
var btnAlterar = document.getElementById('btnAlterarDatas');
if (btnAlterar) {
    btnAlterar.addEventListener('click', abrirModalDatas);
}
var btnLimpar = document.getElementById('btnLimparEstante');
if (btnLimpar) {
    btnLimpar.addEventListener('click', limparEstante);
}
var btnConfirmar = document.getElementById('btnConfirmarDatas');
if (btnConfirmar) {
    btnConfirmar.addEventListener('click', function() {
        var inputModal = document.getElementById('datas_estante_modal');
        if (inputModal && inputDatas) {
            inputDatas.value = inputModal.value.trim();
            salvarDatasAlvo();
        }
        fecharModalDatas();
    });
}
var btnCancelar = document.getElementById('btnCancelarDatas');
if (btnCancelar) {
    btnCancelar.addEventListener('click', fecharModalDatas);
}
atualizarBannerDatas();
abrirModalDatas();
carregarEstanteInicial();

</script>

</body>
</html>