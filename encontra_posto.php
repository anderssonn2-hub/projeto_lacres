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

function normalizarDataIso($s) {
    $s = trim((string)$s);
    if ($s === '') return '';
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

function montarLayoutEstante($pdo, $whereEstante, $params_estante) {
    $layout = array(
        'correios' => array(),
        'poupatempo' => array(),
        'totais' => array('correios_lotes' => 0, 'poupatempo_lotes' => 0)
    );
    $postos_pt = array('005','006','023','024','025','026','028','080','110','315','375','487','526','527','667','730','747','790','825','880');
    $correios_keys = array('022','060','100','105','150','200','250','300','350','400','450','490','500','501','507','550','600','650','700','701','710','750','755','758','779','800','808','809','850','900','950');
    $stmt = $pdo->prepare("SELECT l.posto, l.regional, l.quantidade, c.regional_csv
        FROM lotes_na_estante l
        LEFT JOIN (
            SELECT lote, MAX(regional) AS regional_csv
            FROM ciPostosCsv
            GROUP BY lote
        ) c ON c.lote = l.lote
        $whereEstante");
    $stmt->execute($params_estante);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $qtd = 1;
        $posto = (int)$row['posto'];
        $regional = (int)$row['regional'];
        $regional_csv = isset($row['regional_csv']) ? (int)$row['regional_csv'] : 0;
        $posto_pad = str_pad((string)$posto, 3, '0', STR_PAD_LEFT);
        $is_pt = in_array($posto_pad, $postos_pt, true);
        if ($is_pt) {
            $key = $posto_pad;
            if (!isset($layout['poupatempo'][$key])) { $layout['poupatempo'][$key] = 0; }
            $layout['poupatempo'][$key] += $qtd;
            $layout['totais']['poupatempo_lotes'] += $qtd;
        } else {
            if ($regional_csv === 0 || $regional === 0) {
                if (!isset($layout['correios']['capital'])) { $layout['correios']['capital'] = 0; }
                $layout['correios']['capital'] += $qtd;
            } elseif ($regional_csv === 999 || $regional === 999) {
                if (!isset($layout['correios']['central'])) { $layout['correios']['central'] = 0; }
                $layout['correios']['central'] += $qtd;
            } else {
                $key_regional_csv = str_pad((string)$regional_csv, 3, '0', STR_PAD_LEFT);
                $key_regional = str_pad((string)$regional, 3, '0', STR_PAD_LEFT);
                $key = in_array($key_regional_csv, $correios_keys, true) ? $key_regional_csv : null;
                if ($key === null && in_array($key_regional, $correios_keys, true)) {
                    $key = $key_regional;
                }
                if ($key === null && in_array($posto_pad, $correios_keys, true)) {
                    $key = $posto_pad;
                }
                if ($key === null) {
                    $key = $key_regional_csv !== '000' ? $key_regional_csv : $key_regional;
                }
                if (!isset($layout['correios'][$key])) { $layout['correios'][$key] = 0; }
                $layout['correios'][$key] += $qtd;
            }
            if ($posto === 1) {
                if (!isset($layout['correios']['posto001'])) { $layout['correios']['posto001'] = 0; }
                $layout['correios']['posto001'] += $qtd;
            }
            $layout['totais']['correios_lotes'] += $qtd;
        }
    }
    return $layout;
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

    if (isset($_POST['ajax_estante_status'])) {
        header('Content-Type: application/json');
        $datas_alvo = parseDatasAlvo(isset($_POST['datas_alvo']) ? $_POST['datas_alvo'] : '');
        $data_ini = normalizarDataIso(isset($_POST['data_ini']) ? $_POST['data_ini'] : '');
        $data_fim = normalizarDataIso(isset($_POST['data_fim']) ? $_POST['data_fim'] : '');
        if ($data_ini !== '' && $data_fim === '') {
            $data_fim = $data_ini;
        }
        if ($data_fim !== '' && $data_ini === '') {
            $data_ini = $data_fim;
        }
        if (empty($datas_alvo) && $data_ini === '') {
            die(json_encode(array(
                'success' => true,
                'estante' => array('total' => 0, 'capital' => 0, 'central' => 0, 'regional' => 0, 'poupatempo' => 0),
                'layout' => array('correios' => array(), 'poupatempo' => array(), 'totais' => array('correios_lotes' => 0, 'poupatempo_lotes' => 0))
            )));
        }
        $estante_stats = array('total' => 0, 'capital' => 0, 'central' => 0, 'regional' => 0, 'poupatempo' => 0);
        $sem_upload = array('total' => 0, 'lotes' => array());
        $historico = array();
        $layout = array('correios' => array(), 'poupatempo' => array(), 'totais' => array('correios_lotes' => 0, 'poupatempo_lotes' => 0));
        try {
            $params_estante = array();
            if ($data_ini !== '') {
                $whereEstante = "WHERE DATE(triado_em) BETWEEN ? AND ?";
                $params_estante[] = $data_ini;
                $params_estante[] = $data_fim;
            } else {
                $ph = implode(',', array_fill(0, count($datas_alvo), '?'));
                $whereEstante = "WHERE DATE(triado_em) IN ($ph)";
                $params_estante = $datas_alvo;
            }
            $stmtTot = $pdo->prepare("SELECT COUNT(DISTINCT lote) FROM lotes_na_estante $whereEstante");
            $stmtTot->execute($params_estante);
            $estante_stats['total'] = (int)$stmtTot->fetchColumn();

            $stmtTipos = $pdo->prepare("SELECT DISTINCT l.lote, l.posto, l.regional, r.entrega
                FROM lotes_na_estante l
                LEFT JOIN ciRegionais r ON LPAD(r.posto,3,'0') = LPAD(l.posto,3,'0')
                $whereEstante");
            $stmtTipos->execute($params_estante);
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

            $params_upload = array();
            $joinCarga = '';
            if ($data_ini !== '') {
                $joinCarga = 'AND DATE(c.dataCarga) BETWEEN ? AND ?';
                $params_upload[] = $data_ini;
                $params_upload[] = $data_fim;
            } else {
                $joinCarga = "AND DATE(c.dataCarga) IN ($ph)";
                $params_upload = $datas_alvo;
            }
            $stmtSem = $pdo->prepare("SELECT DISTINCT LPAD(l.lote,8,'0') AS lote
                FROM lotes_na_estante l
                WHERE 1=1
                $whereEstante
                AND NOT EXISTS (
                    SELECT 1 FROM ciPostosCsv c
                    WHERE c.lote = l.lote $joinCarga
                )
                ORDER BY l.lote LIMIT 50");
            $stmtSem->execute(array_merge($params_estante, $params_upload));
            while ($row = $stmtSem->fetch(PDO::FETCH_ASSOC)) {
                $sem_upload['lotes'][] = $row['lote'];
            }
            $stmtSemTot = $pdo->prepare("SELECT COUNT(DISTINCT l.lote)
                FROM lotes_na_estante l
                WHERE 1=1
                $whereEstante
                AND NOT EXISTS (
                    SELECT 1 FROM ciPostosCsv c
                    WHERE c.lote = l.lote $joinCarga
                )");
            $stmtSemTot->execute(array_merge($params_estante, $params_upload));
            $sem_upload['total'] = (int)$stmtSemTot->fetchColumn();

            $stmtHist = $pdo->prepare("SELECT LPAD(lote,8,'0') AS lote, LPAD(posto,3,'0') AS posto, LPAD(regional,3,'0') AS regional, producao_de, triado_em
                FROM lotes_na_estante
                $whereEstante
                ORDER BY triado_em DESC, id DESC
                LIMIT 100");
            $stmtHist->execute($params_estante);
            while ($row = $stmtHist->fetch(PDO::FETCH_ASSOC)) {
                $historico[] = array(
                    'lote' => $row['lote'],
                    'posto' => $row['posto'],
                    'regional' => $row['regional'],
                    'producao_de' => $row['producao_de'],
                    'triado_em' => $row['triado_em']
                );
            }

            $layout = montarLayoutEstante($pdo, $whereEstante, $params_estante);
        } catch (Exception $e) {
            // ignore
        }
        die(json_encode(array('success' => true, 'estante' => $estante_stats, 'sem_upload' => $sem_upload, 'historico' => $historico, 'layout' => $layout)));
    }

    if (isset($_POST['ajax_buscar_posto'])) {
        header('Content-Type: application/json');
        $codbar = isset($_POST['codbar']) ? trim($_POST['codbar']) : '';
        $codbar_limpo = preg_replace('/\D+/', '', $codbar);
        $datas_alvo = parseDatasAlvo(isset($_POST['datas_alvo']) ? $_POST['datas_alvo'] : '');
        $data_ini = normalizarDataIso(isset($_POST['data_ini']) ? $_POST['data_ini'] : '');
        $data_fim = normalizarDataIso(isset($_POST['data_fim']) ? $_POST['data_fim'] : '');
        if ($data_ini !== '' && $data_fim === '') {
            $data_fim = $data_ini;
        }
        if ($data_fim !== '' && $data_ini === '') {
            $data_ini = $data_fim;
        }

        if (empty($datas_alvo) && $data_ini === '') {
            die(json_encode(array('success' => false, 'erro' => 'Informe o periodo da estante')));
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

        if ($data_producao && $data_ini !== '' && ($data_producao < $data_ini || $data_producao > $data_fim)) {
            $data_br = date('d-m-Y', strtotime($data_producao));
            die(json_encode(array(
                'success' => false,
                'erro' => 'Pacote de outra data: ' . $data_br,
                'data_producao' => $data_br
            )));
        }

        if ($data_producao && $data_ini === '' && !in_array($data_producao, $datas_alvo)) {
            $data_br = date('d-m-Y', strtotime($data_producao));
            die(json_encode(array(
                'success' => false,
                'erro' => 'Pacote de outra data: ' . $data_br,
                'data_producao' => $data_br
            )));
        }

        $status_estante = $data_producao ? 'ok' : 'sem_upload';
        $data_alvo = $data_producao ? $data_producao : ($data_ini !== '' ? $data_ini : $datas_alvo[0]);

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
        $historico = array();
        $layout = array('correios' => array(), 'poupatempo' => array(), 'totais' => array('correios_lotes' => 0, 'poupatempo_lotes' => 0));
        try {
            $params_estante = array();
            if ($data_ini !== '') {
                $whereEstante = "WHERE DATE(triado_em) BETWEEN ? AND ?";
                $params_estante[] = $data_ini;
                $params_estante[] = $data_fim;
            } else {
                $ph = implode(',', array_fill(0, count($datas_alvo), '?'));
                $whereEstante = "WHERE DATE(triado_em) IN ($ph)";
                $params_estante = $datas_alvo;
            }
            $stmtTot = $pdo->prepare("SELECT COUNT(DISTINCT lote) FROM lotes_na_estante $whereEstante");
            $stmtTot->execute($params_estante);
            $estante_stats['total'] = (int)$stmtTot->fetchColumn();

            $stmtTipos = $pdo->prepare("SELECT DISTINCT l.lote, l.posto, l.regional, r.entrega
                FROM lotes_na_estante l
                LEFT JOIN ciRegionais r ON LPAD(r.posto,3,'0') = LPAD(l.posto,3,'0')
                $whereEstante");
            $stmtTipos->execute($params_estante);
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

            $params_upload = array();
            $joinCarga = '';
            if ($data_ini !== '') {
                $joinCarga = 'AND DATE(c.dataCarga) BETWEEN ? AND ?';
                $params_upload[] = $data_ini;
                $params_upload[] = $data_fim;
            } else {
                $joinCarga = "AND DATE(c.dataCarga) IN ($ph)";
                $params_upload = $datas_alvo;
            }
            $stmtSem = $pdo->prepare("SELECT DISTINCT LPAD(l.lote,8,'0') AS lote
                FROM lotes_na_estante l
                WHERE 1=1
                $whereEstante
                AND NOT EXISTS (
                    SELECT 1 FROM ciPostosCsv c
                    WHERE c.lote = l.lote $joinCarga
                )
                ORDER BY l.lote LIMIT 50");
            $stmtSem->execute(array_merge($params_estante, $params_upload));
            while ($row = $stmtSem->fetch(PDO::FETCH_ASSOC)) {
                $sem_upload['lotes'][] = $row['lote'];
            }
            $stmtSemTot = $pdo->prepare("SELECT COUNT(DISTINCT l.lote)
                FROM lotes_na_estante l
                WHERE 1=1
                $whereEstante
                AND NOT EXISTS (
                    SELECT 1 FROM ciPostosCsv c
                    WHERE c.lote = l.lote $joinCarga
                )");
            $stmtSemTot->execute(array_merge($params_estante, $params_upload));
            $sem_upload['total'] = (int)$stmtSemTot->fetchColumn();

            $stmtHist = $pdo->prepare("SELECT LPAD(lote,8,'0') AS lote, LPAD(posto,3,'0') AS posto, LPAD(regional,3,'0') AS regional, producao_de, triado_em
                FROM lotes_na_estante
                $whereEstante
                ORDER BY triado_em DESC, id DESC
                LIMIT 100");
            $stmtHist->execute($params_estante);
            while ($row = $stmtHist->fetch(PDO::FETCH_ASSOC)) {
                $historico[] = array(
                    'lote' => $row['lote'],
                    'posto' => $row['posto'],
                    'regional' => $row['regional'],
                    'producao_de' => $row['producao_de'],
                    'triado_em' => $row['triado_em']
                );
            }

            $layout = montarLayoutEstante($pdo, $whereEstante, $params_estante);
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
            'historico' => $historico,
            'layout' => $layout,
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
            font-family: "Trebuchet MS", "Tahoma", "Verdana", sans-serif;
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

        .area-principal { max-width: 1200px; margin: 0 auto; }

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
        .data-estante {
            width: 100%; max-width: 220px;
            padding: 10px 12px; font-size: 14px;
            border: 2px solid #3949ab; border-radius: 6px;
            background: #e8eaf6; font-weight: 700;
        }
        .linha-datas {
            display:flex; flex-wrap:wrap; gap:10px; align-items:center;
        }
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

        .estantes-container {
            background: #ffffff; border-radius: 12px;
            padding: 20px; margin: 0 auto 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.1);
            width: 100%;
        }
        .estantes-header {
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            flex-wrap: wrap; margin-bottom: 8px;
        }
        .estantes-header h3 {
            color: #1b1f3b; font-size: 16px; font-weight: 800;
            padding-left: 10px; border-left: 4px solid #ff6f00; margin: 0;
        }
        .estantes-toggle { display: flex; gap: 8px; flex-wrap: wrap; }
        .estantes-toggle button {
            border: 2px solid #1b1f3b; background: #fff; color: #1b1f3b;
            padding: 6px 12px; border-radius: 20px; font-weight: 800; cursor: pointer;
        }
        .estantes-toggle button.ativo {
            background: #1b1f3b; color: #fff;
        }
        .estantes-resumo {
            font-size: 12px; color: #4e4e4e; margin-bottom: 12px;
        }
        .estantes-grid {
            display: grid; gap: 16px;
        }
        .estante {
            background: linear-gradient(160deg, #fff8e1 0%, #ffffff 100%);
            border: 1px solid #f1e4c8; border-radius: 12px; padding: 14px;
            display: grid; gap: 12px;
            grid-template-columns: repeat(2, minmax(320px, 1fr));
        }
        .estante[data-grupo="poupatempo"] {
            background: linear-gradient(160deg, #e8f5e9 0%, #ffffff 100%);
            border-color: #c8e6c9;
        }
        .estante-coluna {
            display: grid; gap: 10px; min-width: 320px;
        }
        .estante-titulo {
            font-weight: 800; color: #1b1f3b; text-transform: uppercase; font-size: 12px;
            letter-spacing: 0.6px;
        }
        .prateleira {
            display: grid;
            grid-template-columns: repeat(4, minmax(70px, 1fr));
            gap: 8px;
            padding: 8px;
            background: rgba(27,31,59,0.06);
            border-radius: 10px;
            border: 1px dashed rgba(27,31,59,0.15);
        }
        .slot {
            background: #1b1f3b;
            color: #fff;
            border-radius: 8px;
            padding: 6px 4px;
            text-align: center;
            box-shadow: inset 0 -2px 0 rgba(255,255,255,0.15);
        }
        .slot-label {
            font-size: 10px; font-weight: 700; letter-spacing: 0.4px;
            text-transform: uppercase; opacity: 0.8;
        }
        .slot-valor {
            font-size: 16px; font-weight: 800; margin-top: 4px;
        }
        .slot-vazio {
            background: #90a4ae;
            color: #fff;
        }

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
        .overlay-datas .data-estante {
            max-width: 180px;
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
        <div class="datas-ativas" id="datasAtivasTexto">Periodo ativo:</div>
        <button type="button" id="btnAlterarDatas">Alterar datas</button>
    </div>

    <div class="painel-leitura">
        <label>Codigo de Barras do Pacote (19 digitos):</label>
        <input type="text" id="input_codbar" placeholder="Escaneie ou digite o codigo..." autocomplete="off" autofocus>
        <div id="indicadorFoco" style="margin-top:8px; font-size:13px; font-weight:700; color:#4caf50;">Pronto para leitura</div>
    </div>

    <div class="painel-datas">
        <label>Periodo da estante (inicio e fim):</label>
        <div class="linha-datas">
            <input type="date" id="data_ini_estante" class="data-estante">
            <input type="date" id="data_fim_estante" class="data-estante">
        </div>
        <div class="nota-datas">As leituras serao contabilizadas apenas no periodo informado.</div>
    </div>

    <div class="stats-bar">
        <div class="stat-card">
            <h4>Lotes na estante</h4>
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

    <div class="estantes-container">
        <div class="estantes-header">
            <h3>Mapa de Estantes</h3>
            <div class="estantes-toggle" id="estantesToggle">
                <button type="button" data-view="todas" class="ativo">Todas</button>
                <button type="button" data-view="correios">Correios</button>
                <button type="button" data-view="poupatempo">Poupa Tempo</button>
            </div>
        </div>
        <div class="estantes-resumo" id="estantesResumo">Correios: 0 lotes | Poupa Tempo: 0 lotes</div>
        <div class="estantes-grid">
            <div class="estante" data-grupo="correios">
                <div class="estante-coluna">
                    <div class="estante-titulo">Correios - Estante 1</div>
                    <div id="estanteCorreiosA"></div>
                </div>
                <div class="estante-coluna">
                    <div class="estante-titulo">Correios - Estante 2</div>
                    <div id="estanteCorreiosB"></div>
                </div>
            </div>
            <div class="estante" data-grupo="poupatempo">
                <div class="estante-coluna">
                    <div class="estante-titulo">Poupa Tempo</div>
                    <div id="estantePoupaTempo"></div>
                </div>
            </div>
        </div>
    </div>

</div>

<audio id="audioBeep" src="beep.mp3" preload="auto"></audio>

<div class="overlay-datas" id="overlayDatas" style="display:flex;">
    <div class="card">
        <h3>Periodo da Estante</h3>
        <div class="hint">Informe o periodo que sera triado (formato yyyy-mm-dd).</div>
        <div class="linha-datas">
            <input type="date" id="data_ini_modal" class="data-estante">
            <input type="date" id="data_fim_modal" class="data-estante">
        </div>
        <div class="acoes">
            <button type="button" class="btn-sec" id="btnCancelarDatas">Cancelar</button>
            <button type="button" class="btn-primario" id="btnConfirmarDatas">Aplicar</button>
        </div>
    </div>
</div>

<script>
var vozAtiva = true;
var estanteLayout = { correios: {}, poupatempo: {}, totais: {} };
var estanteView = 'todas';
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
    var yyyy = d.getFullYear();
    var mm = (d.getMonth() + 1 < 10 ? '0' : '') + (d.getMonth() + 1);
    var dd = (d.getDate() < 10 ? '0' : '') + d.getDate();
    return yyyy + '-' + mm + '-' + dd;
}

function obterDataIni() {
    var input = document.getElementById('data_ini_estante');
    return input ? input.value.trim() : '';
}

function obterDataFim() {
    var input = document.getElementById('data_fim_estante');
    return input ? input.value.trim() : '';
}

function salvarDatasAlvo() {
    var ini = obterDataIni();
    var fim = obterDataFim();
    if (ini && !fim) fim = ini;
    if (fim && !ini) ini = fim;
    if (ini && fim && ini > fim) {
        var tmp = ini;
        ini = fim;
        fim = tmp;
    }
    if (ini) {
        localStorage.setItem('estante_data_ini', ini);
        localStorage.setItem('estante_data_fim', fim);
    }
    atualizarBannerDatas();
    carregarEstanteInicial();
}

function atualizarBannerDatas() {
    var banner = document.getElementById('bannerDatas');
    var texto = document.getElementById('datasAtivasTexto');
    var ini = obterDataIni();
    var fim = obterDataFim();
    if (!banner || !texto) return;
    if (ini) {
        texto.textContent = 'Periodo ativo: ' + ini + ' a ' + (fim || ini);
        banner.style.display = 'flex';
    } else {
        banner.style.display = 'none';
    }
}

function abrirModalDatas() {
    var overlay = document.getElementById('overlayDatas');
    var inputIni = document.getElementById('data_ini_modal');
    var inputFim = document.getElementById('data_fim_modal');
    if (inputIni) {
        inputIni.value = obterDataIni() || '';
        inputIni.focus();
    }
    if (inputFim) {
        inputFim.value = obterDataFim() || '';
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
    var dataIni = obterDataIni();
    var dataFim = obterDataFim();
    if (!dataIni) {
        exibirErro('Informe o periodo da estante');
        return;
    }
    if (!dataFim) {
        dataFim = dataIni;
    }
    if (dataIni > dataFim) {
        var tmp = dataIni;
        dataIni = dataFim;
        dataFim = tmp;
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
    xhr.send('ajax_buscar_posto=1&codbar=' + encodeURIComponent(codbar) + '&data_ini=' + encodeURIComponent(dataIni) + '&data_fim=' + encodeURIComponent(dataFim));
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

    if (dados.layout) {
        estanteLayout = dados.layout || { correios: {}, poupatempo: {}, totais: {} };
        renderizarEstantes();
    }

    atualizarStats();
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

var prateleirasCorreiosA = [
    [
        { key: 'capital', label: 'Capital' },
        { key: 'posto001', label: 'Posto 001' },
        { key: 'central', label: 'Central IIPR' },
        { key: '__vazio1', label: 'Livre', empty: true }
    ],
    [
        { key: 'r022', label: 'R 022' },
        { key: 'r060', label: 'R 060' },
        { key: 'r100', label: 'R 100' },
        { key: 'r105', label: 'R 105' }
    ],
    [
        { key: 'r150', label: 'R 150' },
        { key: 'r200', label: 'R 200' },
        { key: 'r250', label: 'R 250' },
        { key: 'r300', label: 'R 300' }
    ],
    [
        { key: 'r350', label: 'R 350' },
        { key: 'r400', label: 'R 400' },
        { key: 'r450', label: 'R 450' },
        { key: 'r490', label: 'R 490' }
    ],
    [
        { key: 'r500', label: 'R 500' },
        { key: 'r501', label: 'R 501' },
        { key: 'r507', label: 'R 507' },
        { key: 'r550', label: 'R 550' }
    ]
];

var prateleirasCorreiosB = [
    [
        { key: 'r600', label: 'R 600' },
        { key: 'r650', label: 'R 650' },
        { key: 'r700', label: 'R 700' },
        { key: 'r701', label: 'R 701' }
    ],
    [
        { key: 'r710', label: 'R 710' },
        { key: 'r750', label: 'R 750' },
        { key: 'r755', label: 'R 755' },
        { key: 'r758', label: 'R 758' }
    ],
    [
        { key: 'r779', label: 'R 779' },
        { key: 'r800', label: 'R 800' },
        { key: 'r808', label: 'R 808' },
        { key: 'r809', label: 'R 809' }
    ],
    [
        { key: 'r850', label: 'R 850' },
        { key: 'r900', label: 'R 900' },
        { key: 'r950', label: 'R 950' },
        { key: '__vazio2', label: 'Livre', empty: true }
    ]
];

var prateleirasPoupaTempo = [
    [
        { key: 'p005', label: '005' },
        { key: 'p006', label: '006' },
        { key: 'p023', label: '023' },
        { key: 'p024', label: '024' }
    ],
    [
        { key: 'p025', label: '025' },
        { key: 'p026', label: '026' },
        { key: 'p028', label: '028' },
        { key: 'p080', label: '080' }
    ],
    [
        { key: 'p110', label: '110' },
        { key: 'p315', label: '315' },
        { key: 'p375', label: '375' },
        { key: 'p487', label: '487' }
    ],
    [
        { key: 'p526', label: '526' },
        { key: 'p527', label: '527' },
        { key: 'p667', label: '667' },
        { key: 'p730', label: '730' }
    ],
    [
        { key: 'p747', label: '747' },
        { key: 'p790', label: '790' },
        { key: 'p825', label: '825' },
        { key: 'p880', label: '880' }
    ]
];

function obterValorLayout(grupo, chave) {
    if (!estanteLayout || !estanteLayout[grupo]) return 0;
    var v = estanteLayout[grupo][chave];
    return v ? v : 0;
}

function montarPrateleirasHtml(prateleiras, grupo) {
    var html = '';
    for (var i = 0; i < prateleiras.length; i++) {
        html += '<div class="prateleira">';
        for (var j = 0; j < prateleiras[i].length; j++) {
            var slot = prateleiras[i][j];
            var valor = slot.empty ? 0 : obterValorLayout(grupo, slot.key);
            var classe = slot.empty ? 'slot slot-vazio' : 'slot';
            html += '<div class="' + classe + '">' +
                '<div class="slot-label">' + slot.label + '</div>' +
                '<div class="slot-valor">' + valor + '</div>' +
                '</div>';
        }
        html += '</div>';
    }
    return html;
}

function atualizarResumoEstantes() {
    var resumo = document.getElementById('estantesResumo');
    if (!resumo) return;
    var totalCorreios = 0;
    var totalPT = 0;
    if (estanteLayout && estanteLayout.totais) {
        totalCorreios = estanteLayout.totais.correios_lotes || 0;
        totalPT = estanteLayout.totais.poupatempo_lotes || 0;
    }
    resumo.textContent = 'Correios: ' + totalCorreios + ' lotes | Poupa Tempo: ' + totalPT + ' lotes';
}

function renderizarEstantes() {
    var elA = document.getElementById('estanteCorreiosA');
    var elB = document.getElementById('estanteCorreiosB');
    var elPT = document.getElementById('estantePoupaTempo');
    if (elA) { elA.innerHTML = montarPrateleirasHtml(prateleirasCorreiosA, 'correios'); }
    if (elB) { elB.innerHTML = montarPrateleirasHtml(prateleirasCorreiosB, 'correios'); }
    if (elPT) { elPT.innerHTML = montarPrateleirasHtml(prateleirasPoupaTempo, 'poupatempo'); }
    atualizarResumoEstantes();
    aplicarVisaoEstantes(estanteView);
}

function aplicarVisaoEstantes(view) {
    estanteView = view || 'todas';
    var botoes = document.querySelectorAll('#estantesToggle button[data-view]');
    for (var i = 0; i < botoes.length; i++) {
        var alvo = botoes[i].getAttribute('data-view');
        if (alvo === estanteView) {
            botoes[i].classList.add('ativo');
        } else {
            botoes[i].classList.remove('ativo');
        }
    }
    var estantes = document.querySelectorAll('.estante[data-grupo]');
    for (var j = 0; j < estantes.length; j++) {
        var grupo = estantes[j].getAttribute('data-grupo');
        if (estanteView === 'todas' || estanteView === grupo) {
            estantes[j].style.display = 'grid';
        } else {
            estantes[j].style.display = 'none';
        }
    }
}

function carregarEstanteInicial() {
    var dataIni = obterDataIni();
    var dataFim = obterDataFim();
    if (!dataIni) {
        contTotal = 0; contCapital = 0; contCentral = 0; contRegional = 0; contPT = 0; contSemUpload = 0; lotesSemUpload = [];
        renderizarSemUpload();
        atualizarStats();
        estanteLayout = { correios: {}, poupatempo: {}, totais: {} };
        renderizarEstantes();
        return;
    }
    if (!dataFim) {
        dataFim = dataIni;
    }
    if (dataIni > dataFim) {
        var tmp = dataIni;
        dataIni = dataFim;
        dataFim = tmp;
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
                    if (resp.layout) {
                        estanteLayout = resp.layout || { correios: {}, poupatempo: {}, totais: {} };
                        renderizarEstantes();
                    }
                    atualizarStats();
                }
            } catch (e) {}
        }
    };
    xhr.send('ajax_estante_status=1&data_ini=' + encodeURIComponent(dataIni) + '&data_fim=' + encodeURIComponent(dataFim));
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
var inputIni = document.getElementById('data_ini_estante');
var inputFim = document.getElementById('data_fim_estante');
if (inputIni && inputFim) {
    var salvaIni = localStorage.getItem('estante_data_ini') || '';
    var salvaFim = localStorage.getItem('estante_data_fim') || '';
    var hoje = formatarHoje();
    inputIni.value = salvaIni !== '' ? salvaIni : hoje;
    inputFim.value = salvaFim !== '' ? salvaFim : inputIni.value;
    inputIni.addEventListener('change', salvarDatasAlvo);
    inputFim.addEventListener('change', salvarDatasAlvo);
    inputIni.addEventListener('blur', salvarDatasAlvo);
    inputFim.addEventListener('blur', salvarDatasAlvo);
}
var btnAlterar = document.getElementById('btnAlterarDatas');
if (btnAlterar) {
    btnAlterar.addEventListener('click', abrirModalDatas);
}
var btnConfirmar = document.getElementById('btnConfirmarDatas');
if (btnConfirmar) {
    btnConfirmar.addEventListener('click', function() {
        var iniModal = document.getElementById('data_ini_modal');
        var fimModal = document.getElementById('data_fim_modal');
        if (iniModal && inputIni) {
            inputIni.value = iniModal.value.trim();
        }
        if (fimModal && inputFim) {
            inputFim.value = fimModal.value.trim();
        }
        salvarDatasAlvo();
        fecharModalDatas();
    });
}
var btnCancelar = document.getElementById('btnCancelarDatas');
if (btnCancelar) {
    btnCancelar.addEventListener('click', fecharModalDatas);
}
atualizarBannerDatas();
abrirModalDatas();
var toggleBtns = document.querySelectorAll('#estantesToggle button[data-view]');
for (var i = 0; i < toggleBtns.length; i++) {
    toggleBtns[i].addEventListener('click', function() {
        var view = this.getAttribute('data-view');
        aplicarVisaoEstantes(view);
    });
}
carregarEstanteInicial();

</script>

</body>
</html>