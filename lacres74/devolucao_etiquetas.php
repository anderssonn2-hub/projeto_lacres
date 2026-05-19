<?php
header('Cache-Control: no-cache, no-store, must-revalidate');
session_start();

define('DB_HOST', '10.15.61.169');
define('DB_NAME', 'controle');
define('DB_USER', 'controle_mat');
define('DB_PASS', '375256');

function normalizarUtf8($s) {
    $s = (string)$s;
    if ($s === '' || preg_match('//u', $s)) return $s;
    if (function_exists('iconv')) {
        $t = @iconv('UTF-8','UTF-8//IGNORE',$s);
        if ($t !== false && $t !== '') return $t;
    }
    return $s;
}
function e($s) { return htmlspecialchars(normalizarUtf8($s), ENT_QUOTES, 'UTF-8'); }
function dataBr($d) {
    $d = trim((string)$d);
    if ($d === '') return '';
    $dt = DateTime::createFromFormat('Y-m-d', $d);
    return ($dt===false) ? $d : $dt->format('d/m/Y');
}
function diasAtras($d) {
    $dt = DateTime::createFromFormat('Y-m-d', trim((string)$d));
    if ($dt===false) return '?';
    $hoje = new DateTime('today');
    $diff = $hoje->diff($dt);
    return (int)$diff->days;
}

/* ── CONEXÃO ── */
$dbOk = false; $mensagem = ''; $mensagem_tipo = '';
$ultimo_movimento = array();
$responsavel_salvo = isset($_SESSION['ultimo_responsavel_devolucao'])
    ? trim((string)$_SESSION['ultimo_responsavel_devolucao']) : '';

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $dbOk = true;
} catch (Exception $ex) {
    $mensagem = 'Falha ao conectar no banco.'; $mensagem_tipo = 'erro';
}

/* ── FUNÇÕES DB ── */
function contarTransito($pdo, $dias = 0) {
    $filtroData = ($dias > 0) ? " AND m1.data >= DATE_SUB(CURDATE(), INTERVAL " . (int)$dias . " DAY)" : "";
    $r = $pdo->query("SELECT COUNT(*) FROM ciMalotes m1
                      WHERE m1.tipo=1$filtroData
                      AND m1.id = (SELECT MAX(m3.id) FROM ciMalotes m3 WHERE m3.leitura=m1.leitura AND m3.tipo=1)
                      AND NOT EXISTS(
                        SELECT 1 FROM ciMalotes m2 WHERE m2.leitura=m1.leitura AND m2.tipo=2 AND m2.data>=m1.data)");
    return (int)$r->fetchColumn();
}
function buscarTransito($pdo, $limite, $dias = 0) {
    $filtroData = ($dias > 0) ? " AND m1.data >= DATE_SUB(CURDATE(), INTERVAL " . (int)$dias . " DAY)" : "";
    $stmt = $pdo->prepare("SELECT m1.leitura, m1.posto, m1.login, DATE(m1.data) AS data_mov,
                           (SELECT cdl.lote FROM ciDespachoLotes cdl WHERE cdl.etiqueta_correios=m1.leitura ORDER BY cdl.id DESC LIMIT 1) AS lote
                           FROM ciMalotes m1
                           WHERE m1.tipo=1$filtroData
                           AND m1.id = (SELECT MAX(m3.id) FROM ciMalotes m3 WHERE m3.leitura=m1.leitura AND m3.tipo=1)
                           AND NOT EXISTS(
                             SELECT 1 FROM ciMalotes m2 WHERE m2.leitura=m1.leitura AND m2.tipo=2 AND m2.data>=m1.data)
                           ORDER BY m1.data DESC, m1.id DESC LIMIT " . (int)$limite);
    $stmt->execute();
    return $stmt->fetchAll();
}
function buscarStatusLote($pdo, $lote) {
    $stmt = $pdo->prepare("SELECT DISTINCT cdl.etiqueta_correios, cdl.id
                           FROM ciDespachoLotes cdl WHERE cdl.lote=? ORDER BY cdl.id ASC");
    $stmt->execute(array($lote));
    $etiquetas = $stmt->fetchAll();
    $result = array();
    foreach ($etiquetas as $row) {
        $leitura = $row['etiqueta_correios'];
        $s1 = $pdo->prepare("SELECT id, posto, login, DATE(data) AS data_mov FROM ciMalotes WHERE leitura=? AND tipo=1 ORDER BY id DESC LIMIT 1");
        $s1->execute(array($leitura));
        $envio = $s1->fetch(PDO::FETCH_ASSOC);
        $status = 'Nao enviado'; $data_envio = null; $posto = null; $enviado_por = null; $data_retorno = null;
        if ($envio) {
            $posto = $envio['posto']; $enviado_por = $envio['login']; $data_envio = $envio['data_mov'];
            $s2 = $pdo->prepare("SELECT DATE(data) AS data_mov FROM ciMalotes WHERE leitura=? AND tipo=2 AND data>=(SELECT data FROM ciMalotes WHERE id=?) ORDER BY id DESC LIMIT 1");
            $s2->execute(array($leitura, $envio['id']));
            $retorno = $s2->fetch(PDO::FETCH_ASSOC);
            $status = $retorno ? 'Retornou' : 'Em transito';
            $data_retorno = $retorno ? $retorno['data_mov'] : null;
        }
        $result[] = array('leitura'=>$leitura,'posto'=>$posto,'enviado_por'=>$enviado_por,'data_envio'=>$data_envio,'status'=>$status,'data_retorno'=>$data_retorno);
    }
    return $result;
}
function buscarUltimosEnvios($pdo, $limite) {
    $stmt = $pdo->prepare("SELECT leitura,posto,login,DATE(data) AS data_mov FROM ciMalotes
                           WHERE tipo=1 ORDER BY id DESC LIMIT " . (int)$limite);
    $stmt->execute(); return $stmt->fetchAll();
}
function buscarUltimosRecebimentos($pdo, $limite) {
    $stmt = $pdo->prepare("SELECT leitura,posto,login,DATE(data) AS data_mov FROM ciMalotes
                           WHERE tipo=2 ORDER BY id DESC LIMIT " . (int)$limite);
    $stmt->execute(); return $stmt->fetchAll();
}
function buscarPorPosto($pdo, $posto) {
    $stmt = $pdo->prepare("SELECT tipo,leitura,login,DATE(data) AS data_mov FROM ciMalotes
                           WHERE posto=? ORDER BY id DESC LIMIT 100");
    $stmt->execute(array($posto)); return $stmt->fetchAll();
}
function buscarPorLote($pdo, $lote) {
    $stmt = $pdo->prepare("SELECT cm.tipo, cm.leitura, cm.posto, cm.login, DATE(cm.data) AS data_mov
                           FROM ciMalotes cm
                           INNER JOIN ciDespachoLotes cdl ON cdl.etiqueta_correios = cm.leitura
                           WHERE cdl.lote = ?
                           ORDER BY cm.id DESC");
    $stmt->execute(array($lote)); return $stmt->fetchAll();
}
function resolverPosto($pdo, $leitura) {
    try {
        // Tentar por leitura exata primeiro
        $s = $pdo->prepare('SELECT posto FROM cadastroMalotes WHERE leitura=? ORDER BY id DESC LIMIT 1');
        $s->execute(array($leitura));
        $p = $s->fetchColumn();
        if ($p !== false && trim((string)$p) !== '') {
            return trim((string)$p);
        }
        // Fallback: buscar por CEP + sequencial (display pode ter leitura diferente no cadastro)
        if (strlen($leitura) >= 13) {
            $cep = substr($leitura, 0, 8);
            $seq = substr($leitura, -5);
            $s2 = $pdo->prepare('SELECT posto FROM cadastroMalotes WHERE cep=? AND sequencial=? ORDER BY id DESC LIMIT 1');
            $s2->execute(array($cep, $seq));
            $p2 = $s2->fetchColumn();
            if ($p2 !== false && trim((string)$p2) !== '') {
                return trim((string)$p2);
            }
            // Último fallback: só pelo CEP
            $s3 = $pdo->prepare('SELECT posto FROM cadastroMalotes WHERE cep=? ORDER BY id DESC LIMIT 1');
            $s3->execute(array($cep));
            $p3 = $s3->fetchColumn();
            if ($p3 !== false && trim((string)$p3) !== '') {
                return trim((string)$p3);
            }
        }
        // nenhuma das buscas retornou resultado
    } catch (Exception $ex) {}
    return null;
}
function registrarMovimento($pdo, $leitura_raw, $responsavel, $tipo, &$mensagem, &$mensagem_tipo, &$resp_salvo, &$ult_mov) {
    $leitura = preg_replace('/\D+/','',(string)$leitura_raw);
    if ($responsavel === '') { $mensagem='Informe o responsavel.'; $mensagem_tipo='erro'; return; }
    if (strlen($leitura) !== 35) { $mensagem='Etiqueta invalida — 35 digitos.'; $mensagem_tipo='erro'; return; }
    $cep = substr($leitura,0,8); $seq = substr($leitura,-5);
    $posto = resolverPosto($pdo,$leitura);
    $aviso = '';
    if ($tipo===1) {
        // Verificar se display já foi enviado hoje
        $cDup = $pdo->prepare('SELECT COUNT(*) FROM ciMalotes WHERE leitura=? AND tipo=1 AND DATE(data)=CURDATE()');
        $cDup->execute(array($leitura));
        if ((int)$cDup->fetchColumn() > 0) {
            $mensagem = 'Display ja registrado como enviado hoje.';
            $mensagem_tipo = 'warn';
            // Retornar info do registro existente para mostrar posto
            $rowExist = $pdo->prepare('SELECT posto, login FROM ciMalotes WHERE leitura=? AND tipo=1 AND DATE(data)=CURDATE() ORDER BY id DESC LIMIT 1');
            $rowExist->execute(array($leitura));
            $rowE = $rowExist->fetch(PDO::FETCH_ASSOC);
            $ult_mov = array('tipo'=>1,'leitura'=>$leitura,'posto'=>($rowE?$rowE['posto']:$posto),'responsavel'=>($rowE?$rowE['login']:$responsavel),'data'=>date('d/m/Y'));
            return;
        }
    }
    if ($tipo===2) {
        $c1 = $pdo->prepare('SELECT COUNT(*) FROM ciMalotes WHERE leitura=? AND tipo=1');
        $c1->execute(array($leitura));
        if ((int)$c1->fetchColumn()===0) $aviso='Aviso: sem registro de envio, recebimento gravado mesmo assim. ';
        $c2 = $pdo->prepare('SELECT COUNT(*) FROM ciMalotes WHERE leitura=? AND tipo=2 AND DATE(data)=CURDATE()');
        $c2->execute(array($leitura));
        if ((int)$c2->fetchColumn()>0) { $mensagem='Etiqueta ja recebida hoje.'; $mensagem_tipo='warn'; return; }
    }
    $pdo->prepare('INSERT INTO ciMalotes (leitura,data,observacao,login,tipo,cep,sequencial,posto) VALUES (?,?,?,?,?,?,?,?)')
        ->execute(array($leitura,date('Y-m-d'),null,$responsavel,$tipo,$cep,$seq,$posto));
    $_SESSION['ultimo_responsavel_devolucao'] = $responsavel;
    $resp_salvo = $responsavel;
    $mensagem = $aviso . (($tipo===1) ? 'Envio registrado — Posto: '.($posto?$posto:'?') : 'Recebimento registrado com sucesso.');
    $mensagem_tipo = ($aviso !== '') ? 'warn' : 'ok';
    $ult_mov = array('tipo'=>$tipo,'leitura'=>$leitura,'posto'=>$posto,'responsavel'=>$responsavel,'data'=>date('d/m/Y'));
}

/* ── AÇÕES POST ── */
if ($dbOk && $_SERVER['REQUEST_METHOD']==='POST') {
    $acao        = isset($_POST['acao'])        ? trim((string)$_POST['acao'])        : '';
    $responsavel = isset($_POST['responsavel']) ? trim((string)$_POST['responsavel']) : '';

    if ($acao==='registrar_envio') {
        registrarMovimento($pdo,isset($_POST['leitura_envio'])?$_POST['leitura_envio']:'',$responsavel,1,$mensagem,$mensagem_tipo,$responsavel_salvo,$ultimo_movimento);
    } elseif ($acao==='registrar_recebimento') {
        registrarMovimento($pdo,isset($_POST['leitura_recebimento'])?$_POST['leitura_recebimento']:'',$responsavel,2,$mensagem,$mensagem_tipo,$responsavel_salvo,$ultimo_movimento);
    } elseif ($acao==='marcar_recebido') {
        $leit = preg_replace('/\D+/','',(string)(isset($_POST['leitura'])?$_POST['leitura']:''));
        if (strlen($leit)===35 && $responsavel!=='') {
            $c = $pdo->prepare('SELECT COUNT(*) FROM ciMalotes WHERE leitura=? AND tipo=2 AND DATE(data)=CURDATE()');
            $c->execute(array($leit));
            if ((int)$c->fetchColumn()===0) {
                $cep=substr($leit,0,8); $seq=substr($leit,-5);
                $posto=resolverPosto($pdo,$leit);
                $pdo->prepare('INSERT INTO ciMalotes (leitura,data,observacao,login,tipo,cep,sequencial,posto) VALUES (?,?,?,?,?,?,?,?)')
                    ->execute(array($leit,date('Y-m-d'),'Fechado manualmente via painel',$responsavel,2,$cep,$seq,$posto));
                $mensagem='Etiqueta marcada como recebida.'; $mensagem_tipo='ok';
            } else {
                $mensagem='Ja recebida hoje.'; $mensagem_tipo='warn';
            }
        } else {
            $mensagem='Dados invalidos.'; $mensagem_tipo='erro';
        }
        if (isset($_POST['ajax'])&&$_POST['ajax']==='1') {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(array('ok'=>($mensagem_tipo==='ok'||$mensagem_tipo==='warn'),'mensagem'=>$mensagem,'transito'=>contarTransito($pdo)));
            exit;
        }
    } elseif ($acao==='buscar_posto') {
        header('Content-Type: application/json; charset=UTF-8');
        $posto = preg_replace('/\D+/','',isset($_POST['posto'])?trim($_POST['posto']):'');
        $posto = str_pad($posto,3,'0',STR_PAD_LEFT);
        $rows  = ($posto!=='000') ? buscarPorPosto($pdo,$posto) : array();
        echo json_encode(array('ok'=>true,'rows'=>$rows,'posto'=>$posto));
        exit;
    } elseif ($acao==='buscar_lote') {
        header('Content-Type: application/json; charset=UTF-8');
        $lote = preg_replace('/\D+/','',isset($_POST['lote'])?trim($_POST['lote']):'');
        $rows   = ($lote!=='') ? buscarPorLote($pdo,$lote)   : array();
        $status = ($lote!=='') ? buscarStatusLote($pdo,$lote) : array();
        echo json_encode(array('ok'=>true,'rows'=>$rows,'lote'=>$lote,'status'=>$status));
        exit;
    } elseif ($acao==='historico') {
        header('Content-Type: application/json; charset=UTF-8');
        $leit = preg_replace('/\D+/','',(string)(isset($_POST['leitura'])?$_POST['leitura']:''));
        $hist = array();
        if (strlen($leit)===35) {
            $s=$pdo->prepare("SELECT tipo,login,DATE(data) AS data_mov,observacao FROM ciMalotes WHERE leitura=? ORDER BY id ASC");
            $s->execute(array($leit)); $hist=$s->fetchAll();
        }
        echo json_encode(array('ok'=>true,'historico'=>$hist));
        exit;
    }
    if (isset($_POST['ajax'])&&$_POST['ajax']==='1') {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array(
            'ok' => ($mensagem_tipo==='ok'||$mensagem_tipo==='warn'),
            'mensagem' => $mensagem, 'mensagem_tipo' => $mensagem_tipo,
            'ultimo_movimento' => $ultimo_movimento,
            'transito' => contarTransito($pdo)
        ));
        exit;
    }
}

/* ── DADOS ── */
$aba   = isset($_GET['aba']) ? trim($_GET['aba']) : 'operacao';
$filtro_dias = (isset($_GET['dias']) && (int)$_GET['dias']>0) ? (int)$_GET['dias'] : 0;
$transito_count = 0; $lista_transito = array();
$ultimos_envios = array(); $ultimos_receb = array();

if ($dbOk) {
    $transito_count = contarTransito($pdo, $filtro_dias);
    if ($aba==='transito')      $lista_transito = buscarTransito($pdo,1000,$filtro_dias);
    if ($aba==='envios')        $ultimos_envios  = buscarUltimosEnvios($pdo,100);
    if ($aba==='recebimentos')  $ultimos_receb   = buscarUltimosRecebimentos($pdo,100);
    if ($aba==='operacao') {
        $ultimos_envios = buscarUltimosEnvios($pdo,8);
        $ultimos_receb  = buscarUltimosRecebimentos($pdo,8);
    }
}
$dias_label = array(0=>'Todos',30=>'30 dias',60=>'60 dias',90=>'90 dias');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Controle de Etiquetas Correios v1.2.2</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:"Trebuchet MS","Segoe UI",Arial,sans-serif;background:#eef2f7;color:#1a2b3c;min-height:100vh;}
a{color:#0b3d91;text-decoration:none;}
.topbar{background:#0b1a2e;color:#fff;padding:10px 20px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;}
.topbar h1{font-size:16px;font-weight:700;flex:1;}
.topbar a.home{color:#90caf9;font-size:12px;}
.abas{background:#fff;border-bottom:2px solid #d0dae4;display:flex;padding:0 16px;gap:0;overflow-x:auto;}
.aba{padding:10px 18px;font-size:12px;font-weight:700;color:#607080;border-bottom:3px solid transparent;cursor:pointer;white-space:nowrap;text-decoration:none;display:inline-block;}
.aba.ativa{color:#0b3d91;border-bottom-color:#0b3d91;}
.aba .badge{background:#e53935;color:#fff;border-radius:999px;padding:1px 6px;font-size:10px;margin-left:4px;}
.main{max-width:980px;margin:18px auto;padding:0 14px;}
.card{background:#fff;border-radius:12px;padding:20px;box-shadow:0 2px 10px rgba(0,0,0,.08);margin-bottom:14px;}
.card h2{font-size:14px;color:#0b1a2e;margin-bottom:14px;font-weight:700;}
.kpis{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:14px;}
.kpi{background:#fff;border-radius:10px;padding:12px 18px;box-shadow:0 2px 8px rgba(0,0,0,.07);flex:1;min-width:120px;text-align:center;}
.kpi .k-label{font-size:11px;color:#607080;margin-bottom:4px;}
.kpi .k-val{font-size:26px;font-weight:700;color:#0b1a2e;}
.kpi.alerta .k-val{color:#c62828;}
.resp-bloco{margin-bottom:16px;}
.resp-bloco label{display:block;font-size:12px;font-weight:700;color:#3a5068;margin-bottom:6px;}
.input-resp{width:100%;padding:11px 14px;border:2px solid #b0c4d8;border-radius:8px;font-size:14px;transition:border-color .2s;}
.input-resp:focus{border-color:#0b3d91;outline:none;}
.input-resp.erro{border-color:#e53935;background:#fff8f8;}
.op-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;}
@media(max-width:640px){.op-grid{grid-template-columns:1fr;}}
.op-bloco{border-radius:10px;padding:16px;display:flex;flex-direction:column;gap:10px;}
.op-bloco.receb{background:#e8f5e9;border:2px solid #a5d6a7;}
.op-bloco.envio{background:#e3f2fd;border:2px solid #90caf9;}
.op-bloco h3{font-size:13px;font-weight:700;margin-bottom:2px;}
.op-bloco.receb h3{color:#1b5e20;}
.op-bloco.envio h3{color:#0d47a1;}
.op-bloco .sublabel{font-size:11px;color:#607080;margin-bottom:4px;}
.input-etiq{width:100%;padding:12px 14px;border:2px solid #b0c4d8;border-radius:8px;font-size:13px;font-family:"Courier New",Courier,monospace;letter-spacing:1px;transition:border-color .2s;}
.op-bloco.receb .input-etiq:focus{outline:none;border-color:#2e7d32;box-shadow:0 0 0 3px rgba(46,125,50,.12);}
.op-bloco.envio .input-etiq:focus{outline:none;border-color:#0d47a1;box-shadow:0 0 0 3px rgba(13,71,161,.12);}
.char-count{font-size:10px;color:#90a4ae;text-align:right;font-family:monospace;}
.status-live{padding:12px 16px;border-radius:10px;background:#e8f5e9;border:1px solid #a5d6a7;color:#1b5e20;font-size:14px;font-weight:600;min-height:46px;transition:all .2s;}
.status-live.erro{background:#ffebee;border-color:#ef9a9a;color:#b71c1c;}
.status-live.warn{background:#fff8e1;border-color:#ffe082;color:#7d4e00;}
.mov-box{background:#f0f8ff;border:1px solid #b3d4f5;border-radius:10px;padding:14px;display:none;}
.mov-box .mov-title{font-weight:700;font-size:13px;color:#0b3d91;margin-bottom:8px;}
.mov-box .mov-linha{font-size:12px;color:#3a5068;margin-bottom:3px;}
.tabela{width:100%;border-collapse:collapse;font-size:12px;}
.tabela th{background:#1a2b3c;color:#fff;padding:8px 10px;text-align:left;font-size:11px;}
.tabela td{padding:7px 10px;border-bottom:1px solid #eef2f7;vertical-align:middle;}
.tabela tr:hover td{background:#f5f8fc;}
.tabela .mono{font-family:"Courier New",Courier,monospace;font-size:11px;word-break:break-all;}
.badge-tip{display:inline-block;padding:2px 8px;border-radius:999px;font-size:10px;font-weight:700;}
.badge-tip.t1{background:#e3f2fd;color:#0d47a1;}
.badge-tip.t2{background:#e8f5e9;color:#1b5e20;}
.badge-tip.transito{background:#fff8e1;color:#7d4e00;}
.badge-tip.antigo{background:#ffebee;color:#b71c1c;}
.dias-old{font-size:10px;font-weight:700;padding:2px 6px;border-radius:4px;white-space:nowrap;}
.dias-old.ok{background:#e8f5e9;color:#1b5e20;}
.dias-old.medio{background:#fff8e1;color:#7d4e00;}
.dias-old.antigo{background:#ffebee;color:#b71c1c;}
.btn-fechar{background:#e53935;color:#fff;border:none;border-radius:6px;padding:4px 10px;font-size:11px;font-weight:700;cursor:pointer;}
.btn-fechar:hover{background:#b71c1c;}
.filtros-dias{display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-bottom:14px;}
.filtro-btn{padding:6px 14px;border-radius:20px;border:1px solid #b0c4d8;font-size:12px;font-weight:700;color:#3a5068;background:#fff;cursor:pointer;text-decoration:none;}
.filtro-btn.ativo{background:#0b3d91;color:#fff;border-color:#0b3d91;}
.search-row{display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-bottom:12px;}
.search-row input{flex:1;min-width:120px;padding:9px 12px;border:2px solid #b0c4d8;border-radius:8px;font-size:13px;}
.search-row input:focus{outline:none;border-color:#0b3d91;}
.btn-buscar{padding:9px 20px;background:#0b3d91;color:#fff;border:none;border-radius:8px;font-weight:700;cursor:pointer;font-size:12px;}
.btn-buscar:hover{background:#083170;}
.resultado-busca{margin-top:10px;}
.resp-aviso{color:#e53935;font-size:12px;font-weight:700;margin-top:4px;display:none;}
/* DARK */
body.dark{background:#111114;color:#dde3ec;}
body.dark .topbar{background:#0b1020;}
body.dark .abas{background:#16191e;border-bottom-color:#2a3040;}
body.dark .aba{color:#607080;}
body.dark .aba.ativa{color:#90caf9;border-bottom-color:#4fc3f7;}
body.dark .card,.dark .kpi,.dark .mov-box{background:#16191e!important;border-color:#252d3a!important;}
body.dark .input-resp,.dark .input-etiq,.dark .search-row input{background:#1c2028;color:#dde3ec;border-color:#2a3040;}
body.dark .op-bloco.receb{background:#0a2010;border-color:#1b5e20;}
body.dark .op-bloco.envio{background:#0a1a2e;border-color:#0d47a1;}
body.dark .status-live{background:#0a2010;border-color:#1b5e20;color:#81c784;}
body.dark .status-live.erro{background:#2a0d0d;border-color:#b71c1c;color:#ef9a9a;}
body.dark .tabela th{background:#0b1a2e;}
body.dark .tabela td{border-bottom-color:#1e2430;color:#dde3ec;}
body.dark .tabela tr:hover td{background:#1a2030;}
body.dark .filtro-btn{background:#16191e;color:#90caf9;border-color:#2a3040;}
body.dark .filtro-btn.ativo{background:#0b3d91;color:#fff;}
</style>
</head>
<body>
<div class="topbar">
  <a class="home" href="inicio.php">&#8592; Início</a>
  <h1>&#128230; Controle de Etiquetas Correios</h1><span style="font-size:11px;opacity:.7;margin-left:8px;">v1.2.2</span>
  <span style="font-size:11px;opacity:.7;">v2.1</span>
</div>

<div class="abas">
  <a class="aba <?php echo ($aba==='operacao'?'ativa':''); ?>" href="?aba=operacao">&#9997; Operação</a>
  <a class="aba <?php echo ($aba==='transito'?'ativa':''); ?>" href="?aba=transito<?php echo ($filtro_dias>0?'&dias='.$filtro_dias:''); ?>">&#9992; Em Trânsito <span class="badge" id="badge-transito"><?php echo (int)$transito_count; ?></span></a>
  <a class="aba <?php echo ($aba==='envios'?'ativa':''); ?>"        href="?aba=envios">&#8593; Envios</a>
  <a class="aba <?php echo ($aba==='recebimentos'?'ativa':''); ?>"  href="?aba=recebimentos">&#8595; Recebimentos</a>
  <a class="aba <?php echo ($aba==='pesquisar'?'ativa':''); ?>"     href="?aba=pesquisar">&#128269; Pesquisar</a>
</div>

<div class="main">

<?php if (!$dbOk): ?>
  <div class="card"><p style="color:#c62828;">&#9888; <?php echo e($mensagem); ?></p></div>
<?php endif; ?>

<!-- ═══════════════ ABA OPERAÇÃO ═══════════════ -->
<?php if ($aba==='operacao'): ?>

  <div class="kpis">
    <div class="kpi <?php echo ($transito_count>0?'alerta':''); ?>">
      <div class="k-label">Em trânsito</div>
      <div class="k-val" id="kpi-transito"><?php echo (int)$transito_count; ?></div>
    </div>
    <div class="kpi">
      <div class="k-label">Ver trânsito completo</div>
      <div style="margin-top:6px;"><a href="?aba=transito" style="font-size:12px;font-weight:700;">Ver lista &#8594;</a></div>
    </div>
  </div>

  <div class="card">
    <div class="resp-bloco">
      <label for="responsavel">Responsável (obrigatório)</label>
      <input type="text" id="responsavel" class="input-resp" autocomplete="off"
             value="<?php echo e($responsavel_salvo); ?>" placeholder="Digite seu nome...">
      <div class="resp-aviso" id="resp-aviso">&#9888; Informe o responsavel antes de registrar.</div>
    </div>
    <div class="status-live" id="status-live">Pronto para leitura.</div>
    <div class="mov-box" id="mov-box">
      <div class="mov-title" id="mov-title">—</div>
      <div class="mov-linha" id="mov-linha1"></div>
      <div class="mov-linha" id="mov-linha2"></div>
    </div>
  </div>

  <div class="op-grid">
    <div class="op-bloco receb">
      <h3>&#8595; Registrar Recebimento</h3>
      <div class="sublabel">Etiqueta retornou (tipo 2)</div>
      <form id="formRecebimento">
        <input type="hidden" name="acao" value="registrar_recebimento">
        <input type="hidden" name="responsavel" value="" class="resp-hidden">
        <input type="text" id="leitura_recebimento" name="leitura_recebimento"
               class="input-etiq" autocomplete="off" maxlength="35"
               placeholder="Escaneie ou digite 35 dígitos">
        <div class="char-count"><span id="cnt-receb">0</span>/35</div>
      </form>
    </div>
    <div class="op-bloco envio">
      <h3>&#8593; Registrar Envio</h3>
      <div class="sublabel">Etiqueta sendo enviada (tipo 1)</div>
      <form id="formEnvio">
        <input type="hidden" name="acao" value="registrar_envio">
        <input type="hidden" name="responsavel" value="" class="resp-hidden">
        <input type="text" id="leitura_envio" name="leitura_envio"
               class="input-etiq" autocomplete="off" maxlength="35"
               placeholder="Escaneie ou digite 35 dígitos">
        <div class="char-count"><span id="cnt-envio">0</span>/35</div>
      </form>
    </div>
  </div>

  <!-- Mini-histórico -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
    <div class="card">
      <h2>Últimos Envios</h2>
      <table class="tabela">
        <thead><tr><th>Etiqueta</th><th>Posto</th><th>Resp.</th><th>Data</th></tr></thead>
        <tbody><?php if (!empty($ultimos_envios)): foreach ($ultimos_envios as $r): ?>
          <tr><td class="mono"><?php echo e(substr($r['leitura'],0,12)).'...'; ?></td>
              <td><?php echo e($r['posto']?:'-'); ?></td>
              <td><?php echo e($r['login']?:'-'); ?></td>
              <td><?php echo e(dataBr($r['data_mov'])); ?></td></tr>
        <?php endforeach; else: ?>
          <tr><td colspan="4">Nenhum envio.</td></tr>
        <?php endif; ?></tbody>
      </table>
    </div>
    <div class="card">
      <h2>Últimos Recebimentos</h2>
      <table class="tabela">
        <thead><tr><th>Etiqueta</th><th>Posto</th><th>Resp.</th><th>Data</th></tr></thead>
        <tbody><?php if (!empty($ultimos_receb)): foreach ($ultimos_receb as $r): ?>
          <tr><td class="mono"><?php echo e(substr($r['leitura'],0,12)).'...'; ?></td>
              <td><?php echo e($r['posto']?:'-'); ?></td>
              <td><?php echo e($r['login']?:'-'); ?></td>
              <td><?php echo e(dataBr($r['data_mov'])); ?></td></tr>
        <?php endforeach; else: ?>
          <tr><td colspan="4">Nenhum recebimento.</td></tr>
        <?php endif; ?></tbody>
      </table>
    </div>
  </div>

<!-- ═══════════════ ABA TRÂNSITO ═══════════════ -->
<?php elseif ($aba==='transito'): ?>
  <div class="card">
    <h2>&#9992; Em trânsito
      <?php if ($filtro_dias>0): ?><small style="font-weight:400;color:#607080;">(últimos <?php echo $filtro_dias; ?> dias)</small><?php endif; ?>
      — <?php echo count($lista_transito); ?> etiqueta(s)
    </h2>

    <div class="filtros-dias">
      <strong style="font-size:12px;color:#3a5068;">Filtrar por período:</strong>
      <?php foreach ($dias_label as $v=>$label): ?>
        <a href="?aba=transito<?php echo ($v>0?'&dias='.$v:''); ?>"
           class="filtro-btn <?php echo ($filtro_dias===$v?'ativo':''); ?>"><?php echo $label; ?></a>
      <?php endforeach; ?>
    </div>

    <p style="font-size:12px;color:#607080;margin-bottom:12px;">
      &#9888; Entradas antigas (aparecendo como &ldquo;em trânsito&rdquo; por muito tempo) são etiquetas enviadas mas cujo <strong>recebimento nunca foi registrado</strong> no sistema.
      Use &ldquo;Marcar como recebido&rdquo; para fechar manualmente entradas que já voltaram mas não foram escaneadas.
    </p>

    <?php if (empty($lista_transito)): ?>
      <p style="color:#2e7d32;font-weight:700;">&#10003; Nenhuma etiqueta em trânsito<?php echo $filtro_dias>0?' neste período.':'.'; ?></p>
    <?php else: ?>
    <table class="tabela">
      <thead><tr><th>#</th><th>Etiqueta</th><th>Lote</th><th>Posto</th><th>Enviado por</th><th>Data envio</th><th>Dias</th><th>Ação</th></tr></thead>
      <tbody>
      <?php $i=1; foreach ($lista_transito as $r):
        $dias = diasAtras($r['data_mov']);
        $diasClass = $dias<=14?'ok':($dias<=30?'medio':'antigo');
      ?>
        <tr>
          <td><?php echo $i++; ?></td>
          <td class="mono"><?php echo e($r['leitura']); ?></td>
          <td><?php echo e($r['lote']?$r['lote']:'—'); ?></td>
          <td><?php echo e($r['posto']?$r['posto']:'-'); ?></td>
          <td><?php echo e($r['login']&&$r['login']!==''?$r['login']:'Nao informado'); ?></td>
          <td><?php echo e(dataBr($r['data_mov'])); ?></td>
          <td><span class="dias-old <?php echo $diasClass; ?>"><?php echo $dias; ?> dias</span></td>
          <td><button class="btn-fechar" onclick="marcarRecebido('<?php echo e($r['leitura']); ?>', this)">Marcar recebido</button></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

<!-- ═══════════════ ABA ENVIOS ═══════════════ -->
<?php elseif ($aba==='envios'): ?>
  <div class="card">
    <h2>&#8593; Últimos 100 Envios</h2>
    <table class="tabela">
      <thead><tr><th>#</th><th>Etiqueta completa</th><th>CEP</th><th>Seq.</th><th>Posto</th><th>Responsável</th><th>Data</th></tr></thead>
      <tbody><?php if (!empty($ultimos_envios)): $i=1; foreach ($ultimos_envios as $r): ?>
        <tr><td><?php echo $i++; ?></td>
            <td class="mono"><?php echo e($r['leitura']); ?></td>
            <td class="mono"><?php echo e(substr($r['leitura'],0,8)); ?></td>
            <td class="mono"><?php echo e(substr($r['leitura'],-5)); ?></td>
            <td><?php echo e($r['posto']?:'-'); ?></td>
            <td><?php echo e($r['login']?:'-'); ?></td>
            <td><?php echo e(dataBr($r['data_mov'])); ?></td></tr>
      <?php endforeach; else: ?>
        <tr><td colspan="7">Nenhum envio encontrado.</td></tr>
      <?php endif; ?></tbody>
    </table>
  </div>

<!-- ═══════════════ ABA RECEBIMENTOS ═══════════════ -->
<?php elseif ($aba==='recebimentos'): ?>
  <div class="card">
    <h2>&#8595; Últimos 100 Recebimentos</h2>
    <table class="tabela">
      <thead><tr><th>#</th><th>Etiqueta completa</th><th>CEP</th><th>Seq.</th><th>Posto</th><th>Responsável</th><th>Data</th></tr></thead>
      <tbody><?php if (!empty($ultimos_receb)): $i=1; foreach ($ultimos_receb as $r): ?>
        <tr><td><?php echo $i++; ?></td>
            <td class="mono"><?php echo e($r['leitura']); ?></td>
            <td class="mono"><?php echo e(substr($r['leitura'],0,8)); ?></td>
            <td class="mono"><?php echo e(substr($r['leitura'],-5)); ?></td>
            <td><?php echo e($r['posto']?:'-'); ?></td>
            <td><?php echo e($r['login']?:'-'); ?></td>
            <td><?php echo e(dataBr($r['data_mov'])); ?></td></tr>
      <?php endforeach; else: ?>
        <tr><td colspan="7">Nenhum recebimento encontrado.</td></tr>
      <?php endif; ?></tbody>
    </table>
  </div>

<!-- ═══════════════ ABA PESQUISAR ═══════════════ -->
<?php elseif ($aba==='pesquisar'): ?>

  <!-- Pesquisa por POSTO -->
  <div class="card">
    <h2>&#128205; Pesquisar por Posto</h2>
    <div class="search-row">
      <input type="text" id="inputPosto" placeholder="Número do posto (ex: 014)" maxlength="5" oninput="this.value=this.value.replace(/\D+/g,'')">
      <button class="btn-buscar" onclick="buscarPorPosto()">Buscar</button>
    </div>
    <div id="resultado-posto" class="resultado-busca"></div>
  </div>

  <!-- Pesquisa por LOTE -->
  <div class="card">
    <h2>&#128230; Pesquisar por Lote</h2>
    <div class="search-row">
      <input type="text" id="inputLote" placeholder="Número do lote" maxlength="10" oninput="this.value=this.value.replace(/\D+/g,'')">
      <button class="btn-buscar" onclick="buscarPorLote()">Buscar</button>
    </div>
    <div id="resultado-lote" class="resultado-busca"></div>
  </div>

  <!-- Histórico por etiqueta -->
  <div class="card">
    <h2>&#128269; Histórico de uma etiqueta</h2>
    <div class="search-row">
      <input type="text" id="inputHistorico" maxlength="35" class="input-etiq"
             placeholder="Cole ou escaneie a etiqueta (35 dígitos)" style="font-size:12px;letter-spacing:.5px;">
      <button class="btn-buscar" onclick="consultarHistorico()">Consultar</button>
    </div>
    <div id="hist-resultado" class="resultado-busca"></div>
  </div>

<?php endif; ?>

</div>

<!-- Campo responsável oculto para marcar recebido -->
<input type="hidden" id="resp-global" value="<?php echo e($responsavel_salvo); ?>">

<script>
(function(){
  var respInput   = document.getElementById('responsavel');
  var statusLive  = document.getElementById('status-live');
  var kpiTransito = document.getElementById('kpi-transito');
  var movBox      = document.getElementById('mov-box');
  var respGlobal  = document.getElementById('resp-global');

  /* Restaurar responsável */
  if (respInput) {
    var salvo = localStorage.getItem('responsavel_devolucao');
    if (salvo && respInput.value.replace(/\s/g,'') === '') {
      respInput.value = salvo;
      if (respGlobal) respGlobal.value = salvo;
    }
    respInput.addEventListener('input', function(){
      localStorage.setItem('responsavel_devolucao', this.value);
      if (respGlobal) respGlobal.value = this.value;
      sincRespHidden();
      var av = document.getElementById('resp-aviso');
      if (av) av.style.display = this.value.replace(/\s/g,'')?'none':'block';
    });
  }

  function sincRespHidden() {
    var val = respInput ? respInput.value : '';
    var hs  = document.querySelectorAll('.resp-hidden');
    for (var i=0; i<hs.length; i++) hs[i].value = val;
  }
  sincRespHidden();

  function setStatus(txt, tipo) {
    if (!statusLive) return;
    statusLive.className = 'status-live' + (tipo==='erro'?' erro':(tipo==='warn'?' warn':''));
    statusLive.textContent = txt;
  }

  function showMov(d) {
    if (!movBox||!d) return;
    movBox.style.display = 'block';
    var t = parseInt(d.tipo,10);
    document.getElementById('mov-title').innerHTML = (t===1?'&#8593; Envio':'&#8595; Recebimento')+' registrado';
    document.getElementById('mov-linha1').innerHTML = 'Etiqueta: <span style="font-family:Courier New,monospace;word-break:break-all;">'+ esc(d.leitura||'-') +'</span>';
    document.getElementById('mov-linha2').textContent = 'Posto: '+(d.posto||'-')+' | Resp: '+(d.responsavel||'-')+' | '+( d.data||'');
  }

  function esc(s){ return String(s||'').replace(/[&<>"']/g,function(c){return{'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c];}); }

  function enviarAjax(input, acao) {
    if (!respInput || respInput.value.replace(/\s/g,'')==='') {
      setStatus('Informe o responsavel antes de registrar.','erro');
      var av=document.getElementById('resp-aviso'); if(av) av.style.display='block';
      if(respInput) respInput.focus();
      input.value='';
      return;
    }
    var digits = input.value.replace(/\D+/g,'');
    if (digits.length !== 35) return;
    sincRespHidden();
    var fd = new FormData(input.form);
    fd.set('ajax','1'); fd.set(input.name, digits);
    input.value = '';
    var cntId = (acao==='registrar_recebimento')?'cnt-receb':'cnt-envio';
    var cnt=document.getElementById(cntId); if(cnt) cnt.textContent='0';
    setStatus('Salvando...','ok');
    input.focus();
    fetch(window.location.pathname+window.location.search,{method:'POST',body:fd,credentials:'same-origin'})
      .then(function(r){return r.json();})
      .then(function(d){
        if(d){ setStatus(d.mensagem||(d.ok?'OK':'Erro'), d.mensagem_tipo||'ok');
               if(kpiTransito&&typeof d.transito!=='undefined') { kpiTransito.textContent=String(d.transito); var b=document.getElementById('badge-transito'); if(b) b.textContent=String(d.transito); }
               if(d.ultimo_movimento) showMov(d.ultimo_movimento); }
      }).catch(function(){ setStatus('Falha de comunicacao.','erro'); })
      .then(function(){ input.focus(); });
  }

  function prepInput(id, cntId, acao) {
    var inp = document.getElementById(id); if(!inp) return;
    inp.addEventListener('input',function(){
      var d=this.value.replace(/\D+/g,'');
      var c=document.getElementById(cntId); if(c) c.textContent=d.length;
      if(d.length===35) enviarAjax(this,acao);
    });
  }
  prepInput('leitura_recebimento','cnt-receb','registrar_recebimento');
  prepInput('leitura_envio','cnt-envio','registrar_envio');

  var inpR=document.getElementById('leitura_recebimento'); if(inpR) inpR.focus();

  /* ── MARCAR RECEBIDO (Aba Trânsito) ── */
  window.marcarRecebido = function(leitura, btn) {
    var resp = (document.getElementById('resp-global')||{}).value || localStorage.getItem('responsavel_devolucao') || '';
    if (!resp || resp.replace(/\s/g,'')==='') {
      var nome = prompt('Informe seu nome (responsavel pelo fechamento):');
      if (!nome || nome.replace(/\s/g,'')==='') { alert('Nome obrigatorio.'); return; }
      resp = nome;
      localStorage.setItem('responsavel_devolucao', resp);
    }
    if (!confirm('Marcar etiqueta como recebida?\n'+leitura)) return;
    var fd=new FormData();
    fd.append('acao','marcar_recebido');
    fd.append('leitura',leitura);
    fd.append('responsavel',resp);
    fd.append('ajax','1');
    btn.disabled=true; btn.textContent='...';
    fetch(window.location.pathname,{method:'POST',body:fd,credentials:'same-origin'})
      .then(function(r){return r.json();})
      .then(function(d){
        if(d&&d.ok){ btn.closest('tr').style.opacity='.3'; btn.textContent='OK'; var b=document.getElementById('badge-transito'); if(b) b.textContent=String(d.transito||0); }
        else { alert((d&&d.mensagem)||'Erro.'); btn.disabled=false; btn.textContent='Marcar recebido'; }
      });
  };

  /* ── PESQUISA POR POSTO ── */
  window.buscarPorPosto = function() {
    var inp=document.getElementById('inputPosto'); if(!inp) return;
    var posto=inp.value.replace(/\D+/g,'').padStart?inp.value.replace(/\D+/g,''):inp.value.replace(/\D+/g,'');
    while(posto.length<3) posto='0'+posto;
    var el=document.getElementById('resultado-posto'); if(!el) return;
    el.innerHTML='<em>Buscando...</em>';
    var fd=new FormData(); fd.append('acao','buscar_posto'); fd.append('posto',posto);
    fetch(window.location.pathname,{method:'POST',body:fd,credentials:'same-origin'})
      .then(function(r){return r.json();})
      .then(function(d){ renderTabelaBusca(el, d.rows, 'Posto '+esc(d.posto||'-')); });
  };

  /* ── PESQUISA POR LOTE ── */
  window.buscarPorLote = function() {
    var inp=document.getElementById('inputLote'); if(!inp) return;
    var lote=inp.value.replace(/\D+/g,'');
    if(!lote){ alert('Informe o numero do lote.'); return; }
    var el=document.getElementById('resultado-lote'); if(!el) return;
    el.innerHTML='<em>Buscando...</em>';
    var fd=new FormData(); fd.append('acao','buscar_lote'); fd.append('lote',lote);
    fetch(window.location.pathname,{method:'POST',body:fd,credentials:'same-origin'})
      .then(function(r){return r.json();})
      .then(function(d){
        if(!d.status||d.status.length===0){
          el.innerHTML='<p style="color:#888;margin-top:8px;">Nenhum display encontrado neste lote.</p>';
          return;
        }
        var totalTransito=0, totalRetornou=0, totalNaoEnviado=0;
        for(var i=0;i<d.status.length;i++){
          if(d.status[i].status==='Em transito') totalTransito++;
          else if(d.status[i].status==='Retornou') totalRetornou++;
          else totalNaoEnviado++;
        }
        var html='<div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:12px;">'
          +'<div style="background:#fff8e1;border:1px solid #ffe082;border-radius:8px;padding:8px 14px;font-size:12px;font-weight:700;color:#7d4e00;">&#9992; Em trânsito: '+totalTransito+'</div>'
          +'<div style="background:#e8f5e9;border:1px solid #a5d6a7;border-radius:8px;padding:8px 14px;font-size:12px;font-weight:700;color:#1b5e20;">&#10003; Retornou: '+totalRetornou+'</div>'
          +(totalNaoEnviado>0?'<div style="background:#eceff1;border:1px solid #b0bec5;border-radius:8px;padding:8px 14px;font-size:12px;font-weight:700;color:#455a64;">&#8212; Não enviado: '+totalNaoEnviado+'</div>':'')
          +'</div>';
        html+='<p style="margin-bottom:8px;"><a href="painel_lotes.php?lote='+esc(d.lote||'')+'" target="_blank" style="font-size:12px;font-weight:700;color:#0b3d91;">&#128230; Ver lote no Painel de Controle &rarr;</a></p>';
        html+='<table class="tabela"><thead><tr><th>#</th><th>Etiqueta</th><th>Posto</th><th>Enviado por</th><th>Data envio</th><th>Status</th><th>Data retorno</th></tr></thead><tbody>';
        for(var j=0;j<d.status.length;j++){
          var s=d.status[j];
          var stClass=s.status==='Em transito'?'medio':(s.status==='Retornou'?'ok':'');
          var stLabel=s.status==='Em transito'?'&#9992; Em tr&acirc;nsito':(s.status==='Retornou'?'&#10003; Retornou':'&#8212; N&atilde;o enviado');
          html+='<tr>'
            +'<td>'+(j+1)+'</td>'
            +'<td class="mono">'+esc(s.leitura||'-')+'</td>'
            +'<td>'+esc(s.posto||'-')+'</td>'
            +'<td>'+esc(s.enviado_por||'-')+'</td>'
            +'<td>'+esc(s.data_envio||'-')+'</td>'
            +'<td><span class="dias-old '+(stClass)+'">'+stLabel+'</span></td>'
            +'<td>'+esc(s.data_retorno||'-')+'</td>'
            +'</tr>';
        }
        html+='</tbody></table>';
        el.innerHTML=html;
      });
  };

  function renderTabelaBusca(el, rows, titulo) {
    if(!rows||rows.length===0){el.innerHTML='<p style="color:#888;margin-top:8px;">Nenhum registro encontrado.</p>';return;}
    var html='<p style="font-size:12px;color:#3a5068;margin-bottom:8px;font-weight:700;">'+titulo+' — '+rows.length+' registro(s)</p>';
    html+='<table class="tabela"><thead><tr><th>Tipo</th><th>Etiqueta</th><th>Posto</th><th>Responsável</th><th>Data</th></tr></thead><tbody>';
    for(var i=0;i<rows.length;i++){
      var r=rows[i]; var t=parseInt(r.tipo,10);
      html+='<tr><td><span class="badge-tip '+(t===1?'t1':'t2')+'">'+(t===1?'Envio':'Recebimento')+'</span></td>'
          +'<td class="mono">'+esc(r.leitura||'-')+'</td>'
          +'<td>'+esc(r.posto||'-')+'</td>'
          +'<td>'+esc(r.login||'-')+'</td>'
          +'<td>'+esc(r.data_mov||'-')+'</td></tr>';
    }
    html+='</tbody></table>';
    el.innerHTML=html;
  }

  /* ── HISTÓRICO POR ETIQUETA ── */
  window.consultarHistorico = function() {
    var inp=document.getElementById('inputHistorico'); if(!inp) return;
    var leit=inp.value.replace(/\D+/g,'');
    if(leit.length!==35){alert('Informe 35 digitos.');return;}
    var el=document.getElementById('hist-resultado'); if(!el) return;
    el.innerHTML='<em>Buscando...</em>';
    var fd=new FormData(); fd.append('acao','historico'); fd.append('leitura',leit);
    fetch(window.location.pathname,{method:'POST',body:fd,credentials:'same-origin'})
      .then(function(r){return r.json();})
      .then(function(d){
        if(!d.historico||d.historico.length===0){el.innerHTML='<p style="color:#888;margin-top:8px;">Nenhum registro encontrado para esta etiqueta.</p>';return;}
        var html='<p style="font-size:12px;margin-bottom:8px;font-weight:700;color:#3a5068;">Histórico: <span style="font-family:Courier New,monospace;">'+esc(leit)+'</span></p>';
        html+='<table class="tabela"><thead><tr><th>Tipo</th><th>Responsável</th><th>Data</th><th>Observação</th></tr></thead><tbody>';
        for(var i=0;i<d.historico.length;i++){
          var h=d.historico[i]; var t=parseInt(h.tipo,10);
          html+='<tr><td><span class="badge-tip '+(t===1?'t1':'t2')+'">'+(t===1?'Envio':'Recebimento')+'</span></td>'
              +'<td>'+esc(h.login||'-')+'</td><td>'+esc(h.data_mov||'-')+'</td><td>'+esc(h.observacao||'-')+'</td></tr>';
        }
        html+='</tbody></table>';
        el.innerHTML=html;
      });
  };

  /* Enter nos campos de pesquisa */
  var iP=document.getElementById('inputPosto'); if(iP) iP.addEventListener('keydown',function(e){if(e.keyCode===13) window.buscarPorPosto();});
  var iL=document.getElementById('inputLote');  if(iL) iL.addEventListener('keydown',function(e){if(e.keyCode===13) window.buscarPorLote();});
  var iH=document.getElementById('inputHistorico'); if(iH) iH.addEventListener('keydown',function(e){if(e.keyCode===13) window.consultarHistorico();});

})();
</script>
<?php include __DIR__ . '/_acess.php'; ?>
</body>
</html>
