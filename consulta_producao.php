<?php
/**
 * consulta_producao.php - Versao 9.22.5
 * 
 * CHANGELOG v9.22.5:
 * - [MELHORADO] Detalhes PT buscam lotes/responsaveis/data_carga em ciPostosCsv quando necessario
 * - [MELHORADO] Tabela de lotes exibe badge correto para Poupa Tempo
 *
 * CHANGELOG v9.22.4:
 * - [MELHORADO] Conferido no Poupa Tempo baseado em lacre/conferido_oficio
 * - [MELHORADO] Detalhes PT exibem data carga e responsaveis quando gravados
 *
 * CHANGELOG v9.22.3:
 * - [REFATORADO] Detalhes do despacho com fallback entre ciDespachoItens e ciDespachoLotes
 * - [CORRIGIDO] Busca de nome do posto sem depender de colunas inexistentes
 * - [MELHORADO] Resumo e tabela de detalhes mostram dados mesmo quando itens estao vazios
 * 
 * CHANGELOG v8.17.0:
 * - [CORRIGIDO] Detalhes do despacho não apareciam - queries retornando vazio
 * - [CORRIGIDO] Campos necessários adicionados às queries de itens e lotes
 * - [MANTIDO] Funcionalidade completa de estatísticas e filtros
 * 
 * CHANGELOG v8.16.1:
 * - [NOVO] Aba Estatisticas expandida com múltiplos painéis de dados
 *   * Produção de Carteiras por Dia (últimos 30 dias)
 *   * Produção por Usuário (ranking de produtividade com top 20)
 *   * Postos com Maior Demanda (últimos 30 dias, top 20)
 *   * Distribuição por Tipo (Correios vs Poupa Tempo)
 *   * Estatísticas Resumidas (totais, médias, picos)
 * - [ADICIONADO] Queries otimizadas com GROUP BY e agregações
 * - [MELHORADO] Apresentação visual com múltiplas tabelas e cards
 * 
 * CHANGELOG v8.16.0:
 * - [ALTERADO] Formato do número do ofício no cabeçalho Correios: "Nº #101"
 * 
 * CHANGELOG v8.15.8:
 * - [CORRIGIDO] Layout dos filtros restaurado (display table horizontal)
 * - [ALTERADO] Links para Correios e Poupatempo apontam para /var/www/dipro/controle/cioficios/
 * - [MANTIDO] Datas extraídas de criado_at para coincidir com o arquivo salvo
 * 
 * CHANGELOG v8.15.5:
 * - [CORRIGIDO] Link #ID clicável abre em nova aba
 * - [MANTIDO] Estrutura de pastas/nomes: ID_tipo_dd-mm-yyyy.pdf, pastas lowercase
 * - [MANTIDO] Datas extraídas de criado_at para coincidir com o arquivo salvo
 * 
 * CHANGELOG v8.15.4:
 * - [CORRIGIDO] Link para PDF usa criado_at em vez de datas_str
 * - [ADICIONADO] Campo criado_at na SELECT principal
 * 
 * CHANGELOG v8.15.3:
 * - [ALTERADO] Formato de nome de arquivos: removido # do início
 * - [ALTERADO] Estrutura de pastas em lowercase
 * - [CORRIGIDO] Links atualizados para nova estrutura
 * 
 * Funcionalidades:
 * - Busca por etiqueta dos correios
 * - Busca por data (dia, mes, ano, intervalo)
 * - Busca por lote, posto, usuario (com dropdown)
 * - Integracao com conferencia_pacotes
 * - Estatisticas de producao por dia, mes, usuario
 * - Filtros avancados
 * - Link para PDF na rede
 * - Suporte completo a CORREIOS e POUPA TEMPO
 * 
 * Versao 8.14.9.1 (Dezembro 2025):
 * - Detalhes Poupa Tempo: lote, data carga, responsaveis, conferido, conferido por
 * - Detalhes Correios: adiciona colunas Lacre IIPR e Lacre Correios
 * - Badge visual indicando tipo de posto (POUPA TEMPO / CORREIOS) nos detalhes
 */

error_reporting(E_ALL & ~E_NOTICE);
header('Content-Type: text/html; charset=utf-8');

if (!isset($_SESSION)) {
    session_start();
}

function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

// Conexao com banco de dados
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

// Filtros (Versao 5: adicionar filtro por usuario e tipo de periodo)
$f_grupo      = isset($_GET['grupo']) ? trim($_GET['grupo']) : '';
$f_data_ini   = isset($_GET['data_ini']) ? trim($_GET['data_ini']) : '';
$f_data_fim   = isset($_GET['data_fim']) ? trim($_GET['data_fim']) : '';
$f_etiqueta   = isset($_GET['etiqueta']) ? trim($_GET['etiqueta']) : '';
$f_lote       = isset($_GET['lote']) ? trim($_GET['lote']) : '';
$f_posto      = isset($_GET['posto']) ? trim($_GET['posto']) : '';
$f_usuario    = isset($_GET['usuario']) ? trim($_GET['usuario']) : '';
$f_periodo    = isset($_GET['periodo']) ? trim($_GET['periodo']) : '';
$id_despacho  = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Converter datas para SQL
$data_ini_sql = '';
$data_fim_sql = '';

function converterDataSQL($data) {
    if (empty($data)) return '';
    // dd/mm/yyyy -> yyyy-mm-dd
    if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $data, $m)) {
        return $m[3] . '-' . $m[2] . '-' . $m[1];
    }
    // yyyy-mm-dd -> manter
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
        return $data;
    }
    return '';
}

if (!empty($f_data_ini)) {
    $data_ini_sql = converterDataSQL($f_data_ini);
}
if (!empty($f_data_fim)) {
    $data_fim_sql = converterDataSQL($f_data_fim);
}

// Busca principal - Versao 8.15.4: query hibrida que busca em ciDespachoLotes (Correios) e ciDespachoItens (Poupa Tempo)
$params = array();
$sqlLista = "
    SELECT 
        d.id,
        d.grupo,
        d.datas_str,
        d.criado_at,
        d.usuario,
        d.ativo,
        CASE 
            WHEN d.grupo = 'POUPA TEMPO' THEN 
                (SELECT COUNT(DISTINCT i.posto) FROM ciDespachoItens i WHERE i.id_despacho = d.id)
            ELSE 
                (SELECT COUNT(DISTINCT l2.posto) FROM ciDespachoLotes l2 WHERE l2.id_despacho = d.id)
        END AS num_postos,
        CASE 
            WHEN d.grupo = 'POUPA TEMPO' THEN 
                (SELECT COALESCE(SUM(i.quantidade),0) FROM ciDespachoItens i WHERE i.id_despacho = d.id)
            ELSE 
                (SELECT COALESCE(SUM(l2.quantidade),0) FROM ciDespachoLotes l2 WHERE l2.id_despacho = d.id)
        END AS total_carteiras
    FROM ciDespachos d
";

// Join com lotes apenas se buscar por lote ou etiqueta
if ($f_lote !== '' || $f_etiqueta !== '') {
    $sqlLista .= " LEFT JOIN ciDespachoLotes l ON l.id_despacho = d.id ";
}

$sqlLista .= " WHERE 1=1 ";

// Filtro por grupo (apenas Poupa Tempo ou Correios)
if ($f_grupo !== '' && $f_grupo !== 'TODOS') {
    if ($f_grupo === 'CORREIOS') {
        $sqlLista .= " AND d.grupo = 'CORREIOS' ";
    } else {
        $sqlLista .= " AND d.grupo = 'POUPA TEMPO' ";
    }
}

// Filtro por intervalo de datas
if (!empty($data_ini_sql)) {
    $sqlLista .= " AND (
        d.datas_str LIKE ? 
        OR d.datas_str LIKE ?
        OR EXISTS (
            SELECT 1 FROM ciDespachoLotes l2 
            WHERE l2.id_despacho = d.id 
            AND l2.data_carga >= ? 
            AND l2.data_carga <= ?
        )
    ) ";
    // Formato brasileiro para busca em datas_str
    $data_ini_br = date('d/m/Y', strtotime($data_ini_sql));
    $data_fim_br = !empty($data_fim_sql) ? date('d/m/Y', strtotime($data_fim_sql)) : $data_ini_br;
    
    $params[] = '%' . $data_ini_br . '%';
    $params[] = '%' . $data_fim_br . '%';
    $params[] = $data_ini_sql;
    $params[] = !empty($data_fim_sql) ? $data_fim_sql : $data_ini_sql;
}

// Filtro por lote
if ($f_lote !== '') {
    $sqlLista .= " AND l.lote LIKE ? ";
    $params[] = '%' . $f_lote . '%';
}

// Filtro por etiqueta
if ($f_etiqueta !== '') {
    $sqlLista .= " AND (l.etiquetaiipr LIKE ? OR l.etiquetacorreios LIKE ? OR l.etiqueta_correios LIKE ?) ";
    $params[] = '%' . $f_etiqueta . '%';
    $params[] = '%' . $f_etiqueta . '%';
    $params[] = '%' . $f_etiqueta . '%';
}

// Aplicar filtro de periodo predefinido (Versao 5)
if ($f_periodo !== '' && empty($f_data_ini)) {
    // Placeholder: período predefinido será aplicado nos filtros abaixo
}


// Filtro por posto - Versao 8.15.0: busca em ambas tabelas
if ($f_posto !== '') {
    $sqlLista .= " AND (
        EXISTS (
            SELECT 1 FROM ciDespachoLotes lp 
            WHERE lp.id_despacho = d.id AND lp.posto LIKE ?
        )
        OR EXISTS (
            SELECT 1 FROM ciDespachoItens ip
            WHERE ip.id_despacho = d.id AND ip.posto LIKE ?
        )
    ) ";
    $params[] = '%' . $f_posto . '%';
    $params[] = '%' . $f_posto . '%';
}

// Filtro por usuario - Versao 8.15.0: busca em ciDespachos, ciDespachoLotes e ciDespachoItens
if ($f_usuario !== '') {
    $sqlLista .= " AND (
        d.usuario LIKE ? 
        OR EXISTS (
            SELECT 1 FROM ciDespachoLotes lu 
            WHERE lu.id_despacho = d.id AND lu.responsaveis LIKE ?
        )
        OR EXISTS (
            SELECT 1 FROM ciDespachoItens iu
            WHERE iu.id_despacho = d.id AND iu.usuario LIKE ?
        )
    ) ";
    $params[] = '%' . $f_usuario . '%';
    $params[] = '%' . $f_usuario . '%';
    $params[] = '%' . $f_usuario . '%';
}

// Versao 6: Manter GROUP BY para evitar duplicatas quando filtros de lote/etiqueta estao ativos
$sqlLista .= "
    GROUP BY d.id, d.grupo, d.datas_str, d.usuario, d.ativo
    ORDER BY d.id DESC
    LIMIT 200
";

$stmtLista = $pdo_controle->prepare($sqlLista);
$stmtLista->execute($params);
$despachos = $stmtLista->fetchAll();

// Buscar conferencia de pacotes
$conferidos = array();
$stmtLista = $pdo_controle->prepare($sqlLista);
$stmtLista->execute($params);
$despachos = $stmtLista->fetchAll();

// ===== v8.16.1: QUERIES DE ESTATÍSTICAS COMPLETAS =====
$estatisticas = array();
$stats_usuario = array();
$stats_postos = array();
$stats_tipo = array();
$stats_resumo = array('total_oficios' => 0, 'total_carteiras' => 0, 'media_diaria' => 0, 'pico_dia' => 0);

// 1) Produção por dia (últimos 30 dias)
try {
    $sqlEstat = "SELECT DATE(d.criado_at) AS data_producao, COUNT(DISTINCT d.id) AS total_oficios, COALESCE(SUM(CASE WHEN d.grupo = 'POUPA TEMPO' THEN (SELECT COALESCE(SUM(i.quantidade),0) FROM ciDespachoItens i WHERE i.id_despacho = d.id) ELSE (SELECT COALESCE(SUM(l2.quantidade),0) FROM ciDespachoLotes l2 WHERE l2.id_despacho = d.id) END), 0) AS total_carteiras, COALESCE(SUM(CASE WHEN d.grupo = 'CORREIOS' THEN (SELECT COUNT(DISTINCT l3.lote) FROM ciDespachoLotes l3 WHERE l3.id_despacho = d.id) ELSE 0 END), 0) AS total_lotes, COALESCE(SUM(CASE WHEN d.grupo = 'POUPA TEMPO' THEN (SELECT COUNT(DISTINCT i2.posto) FROM ciDespachoItens i2 WHERE i2.id_despacho = d.id) ELSE (SELECT COUNT(DISTINCT l4.posto) FROM ciDespachoLotes l4 WHERE l4.id_despacho = d.id) END), 0) AS total_postos FROM ciDespachos d WHERE DATE(d.criado_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY DATE(d.criado_at) ORDER BY data_producao DESC";
    $stmtEstat = $pdo_controle->prepare($sqlEstat);
    $stmtEstat->execute();
    $estatisticas = $stmtEstat->fetchAll();
} catch (Exception $ex) { $estatisticas = array(); }

// 2) Produção por usuário
try {
    $sqlUsuario = "SELECT COALESCE(d.usuario, 'Sem usuario') AS usuario, COUNT(DISTINCT d.id) AS total_oficios, COALESCE(SUM(CASE WHEN d.grupo = 'POUPA TEMPO' THEN (SELECT COALESCE(SUM(i.quantidade),0) FROM ciDespachoItens i WHERE i.id_despacho = d.id) ELSE (SELECT COALESCE(SUM(l2.quantidade),0) FROM ciDespachoLotes l2 WHERE l2.id_despacho = d.id) END), 0) AS total_carteiras FROM ciDespachos d WHERE DATE(d.criado_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY d.usuario ORDER BY total_carteiras DESC LIMIT 20";
    $stmtUsuario = $pdo_controle->prepare($sqlUsuario);
    $stmtUsuario->execute();
    $stats_usuario = $stmtUsuario->fetchAll();
} catch (Exception $ex) { $stats_usuario = array(); }

// 3) Postos com maior demanda
try {
    $sqlPostos = "SELECT LPAD(COALESCE(l.posto, i.posto), 3, '0') AS codigo_posto, COUNT(DISTINCT COALESCE(l.id_despacho, i.id_despacho)) AS total_oficio, COALESCE(SUM(COALESCE(l.quantidade, i.quantidade)), 0) AS total_carteiras FROM (SELECT id_despacho, posto, quantidade FROM ciDespachoLotes WHERE id_despacho IN (SELECT id FROM ciDespachos WHERE DATE(criado_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)) UNION ALL SELECT id_despacho, posto, quantidade FROM ciDespachoItens WHERE id_despacho IN (SELECT id FROM ciDespachos WHERE DATE(criado_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY))) AS combined_data LEFT JOIN ciDespachoLotes l ON combined_data.id_despacho = l.id_despacho AND combined_data.posto = l.posto LEFT JOIN ciDespachoItens i ON combined_data.id_despacho = i.id_despacho AND combined_data.posto = i.posto GROUP BY COALESCE(l.posto, i.posto) ORDER BY total_carteiras DESC LIMIT 20";
    $stmtPostos = $pdo_controle->prepare($sqlPostos);
    $stmtPostos->execute();
    $stats_postos = $stmtPostos->fetchAll();
} catch (Exception $ex) { $stats_postos = array(); }

// 4) Distribuição por tipo
try {
    $sqlTipo = "SELECT d.grupo, COUNT(DISTINCT d.id) AS total_oficios, COALESCE(SUM(CASE WHEN d.grupo = 'POUPA TEMPO' THEN (SELECT COALESCE(SUM(i.quantidade),0) FROM ciDespachoItens i WHERE i.id_despacho = d.id) ELSE (SELECT COALESCE(SUM(l2.quantidade),0) FROM ciDespachoLotes l2 WHERE l2.id_despacho = d.id) END), 0) AS total_carteiras FROM ciDespachos d WHERE DATE(d.criado_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY d.grupo";
    $stmtTipo = $pdo_controle->prepare($sqlTipo);
    $stmtTipo->execute();
    $stats_tipo = $stmtTipo->fetchAll();
} catch (Exception $ex) { $stats_tipo = array(); }

// 5) Totais resumidos
try {
    $sqlResumo = "SELECT COUNT(DISTINCT d.id) AS total_oficios, COALESCE(SUM(CASE WHEN d.grupo = 'POUPA TEMPO' THEN (SELECT COALESCE(SUM(i.quantidade),0) FROM ciDespachoItens i WHERE i.id_despacho = d.id) ELSE (SELECT COALESCE(SUM(l2.quantidade),0) FROM ciDespachoLotes l2 WHERE l2.id_despacho = d.id) END), 0) AS total_carteiras FROM ciDespachos d WHERE DATE(d.criado_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
    $stmtResumo = $pdo_controle->prepare($sqlResumo);
    $stmtResumo->execute();
    $row = $stmtResumo->fetch();
    if ($row) {
        $stats_resumo['total_oficios'] = (int)$row['total_oficios'];
        $stats_resumo['total_carteiras'] = (int)$row['total_carteiras'];
        $dias_com_producao = count($estatisticas);
        $stats_resumo['media_diaria'] = $dias_com_producao > 0 ? (int)($stats_resumo['total_carteiras'] / $dias_com_producao) : 0;
        if (!empty($estatisticas)) $stats_resumo['pico_dia'] = (int)$estatisticas[0]['total_carteiras'];
    }
} catch (Exception $ex) {}

try {
    // Placeholder para busca de conferência
} catch (Exception $ex) {
    // Ignorar erro
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>Consulta de Producao de Cedulas - Versao 8.15.8</title>
<style>
    * { box-sizing: border-box; }
    body {
        font-family: Arial, Helvetica, sans-serif;
        background: #f0f2f5;
        margin: 0;
        padding: 0;
    }
    .container {
        max-width: 1400px;
        margin: 20px auto;
        padding: 0 15px;
    }
    h1 {
        margin: 0 0 20px 0;
        font-size: 24px;
        color: #1a1a2e;
        text-align: center;
    }
    .painel {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    .painel-titulo {
        font-size: 16px;
        font-weight: bold;
        margin-bottom: 15px;
        color: #333;
        border-bottom: 2px solid #007bff;
        padding-bottom: 8px;
    }
    
    /* Filtros */
    .filtros {
        display: table;
        width: 100%;
    }
    .filtro-grupo {
        display: table-cell;
        vertical-align: top;
        padding-right: 15px;
    }
    .filtro-grupo:last-child {
        padding-right: 0;
    }
    .filtro-grupo label {
        display: block;
        font-size: 12px;
        font-weight: bold;
        color: #555;
        margin-bottom: 5px;
    }
    .filtro-grupo input[type="text"],
    .filtro-grupo input[type="date"],
    .filtro-grupo select {
        width: 100%;
        padding: 8px 10px;
        font-size: 13px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }
    .filtro-grupo input:focus,
    .filtro-grupo select:focus {
        border-color: #007bff;
        outline: none;
    }
    .btn {
        padding: 10px 20px;
        font-size: 13px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        margin-right: 8px;
    }
    .btn-primary {
        background: #007bff;
        color: #fff;
    }
    .btn-primary:hover {
        background: #0056b3;
    }
    .btn-secondary {
        background: #6c757d;
        color: #fff;
    }
    .btn-secondary:hover {
        background: #545b62;
    }
    .btn-success {
        background: #28a745;
        color: #fff;
    }
    
    /* Tabelas */
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        border: 1px solid #ddd;
        padding: 8px 10px;
        font-size: 12px;
        text-align: left;
    }
    th {
        background: #f8f9fa;
        font-weight: bold;
        color: #333;
    }
    tr:nth-child(even) {
        background: #fafafa;
    }
    tr:hover {
        background: #f0f7ff;
    }
    
    /* Destaque verde para lotes encontrados/conferidos */
    tr.lote-encontrado {
        background: #d4edda !important;
    }
    tr.lote-conferido {
        background: #c3e6cb !important;
    }
    
    /* Badges */
    .badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: bold;
        color: #fff;
    }
    .badge-ativo { background: #28a745; }
    .badge-inativo { background: #dc3545; }
    .badge-conferido { background: #17a2b8; }
    .badge-pendente { background: #ffc107; color: #333; }
    .badge-poupa { background: #6f42c1; }
    .badge-correios { background: #fd7e14; }
    
    /* Acoes */
    .acoes a {
        display: inline-block;
        padding: 4px 8px;
        margin-right: 4px;
        font-size: 11px;
        text-decoration: none;
        color: #fff;
        border-radius: 3px;
        background: #17a2b8;
    }
    .acoes a:hover {
        background: #138496;
    }
    
    /* Estatisticas */
    .stats-grid {
        display: table;
        width: 100%;
    }
    .stat-card {
        display: table-cell;
        padding: 15px;
        text-align: center;
        border-right: 1px solid #eee;
    }
    .stat-card:last-child {
        border-right: none;
    }
    .stat-valor {
        font-size: 28px;
        font-weight: bold;
        color: #007bff;
    }
    .stat-label {
        font-size: 12px;
        color: #666;
        margin-top: 5px;
    }
    
    /* Abas */
    .tabs {
        margin-bottom: 20px;
    }
    .tab-btn {
        display: inline-block;
        padding: 10px 20px;
        background: #e9ecef;
        border: none;
        border-radius: 4px 4px 0 0;
        cursor: pointer;
        font-size: 13px;
        margin-right: 2px;
    }
    .tab-btn.active {
        background: #007bff;
        color: #fff;
    }
    .tab-content {
        display: none;
    }
    .tab-content.active {
        display: block;
    }
    
    /* Totais */
    .totais {
        font-size: 13px;
        margin: 10px 0;
        padding: 10px;
        background: #e9ecef;
        border-radius: 4px;
    }
    .totais strong {
        color: #007bff;
    }
    
    /* Calendario inline para IE */
    .calendario-grupo {
        display: inline-block;
        margin-right: 10px;
    }
    
    @media print {
        .filtros, .tabs, .btn, .acoes { display: none; }
        .painel { box-shadow: none; }
    }
</style>
</head>
<body>

<div class="container">
    <h1>Consulta de Producao de Cedulas - Versao 8.15.8</h1>
    
    <!-- Painel de Filtros (Versao 6: periodo, usuario com dropdown, link PDF) -->
    <div class="painel">
        <div class="painel-titulo">Filtros de Busca</div>
        <form method="get" action="">
            <div class="filtros">
                <div class="filtro-grupo" style="width:10%;">
                    <label>Tipo:</label>
                    <select name="grupo">
                        <option value="">Todos</option>
                        <option value="POUPA TEMPO"<?php if ($f_grupo=='POUPA TEMPO') echo ' selected'; ?>>Poupa Tempo</option>
                        <option value="CORREIOS"<?php if ($f_grupo=='CORREIOS') echo ' selected'; ?>>Correios</option>
                    </select>
                </div>
                <div class="filtro-grupo" style="width:10%;">
                    <label>Periodo Rapido:</label>
                    <select name="periodo">
                        <option value="">Personalizado</option>
                        <option value="hoje"<?php if ($f_periodo=='hoje') echo ' selected'; ?>>Hoje</option>
                        <option value="semana"<?php if ($f_periodo=='semana') echo ' selected'; ?>>Esta Semana</option>
                        <option value="mes"<?php if ($f_periodo=='mes') echo ' selected'; ?>>Este Mes</option>
                        <option value="ano"<?php if ($f_periodo=='ano') echo ' selected'; ?>>Este Ano</option>
                    </select>
                </div>
                <div class="filtro-grupo" style="width:11%;">
                    <label>Data Inicial:</label>
                    <input type="date" name="data_ini" value="<?php echo e($f_data_ini); ?>">
                </div>
                <div class="filtro-grupo" style="width:11%;">
                    <label>Data Final:</label>
                    <input type="date" name="data_fim" value="<?php echo e($f_data_fim); ?>">
                </div>
                <div class="filtro-grupo" style="width:12%;">
                    <label>Usuario:</label>
                    <select name="usuario">
                        <option value="">Todos</option>
                        <?php foreach ($usuarios as $u): ?>
                            <option value="<?php echo e($u['usuario']); ?>"<?php if ($f_usuario==$u['usuario']) echo ' selected'; ?>><?php echo e($u['usuario']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filtro-grupo" style="width:14%;">
                    <label>Etiqueta Correios:</label>
                    <input type="text" name="etiqueta" value="<?php echo e($f_etiqueta); ?>" placeholder="Ex: 1325467...">
                </div>
                <div class="filtro-grupo" style="width:10%;">
                    <label>Lote:</label>
                    <input type="text" name="lote" value="<?php echo e($f_lote); ?>" placeholder="00752835">
                </div>
                <div class="filtro-grupo" style="width:10%;">
                    <label>Posto:</label>
                    <input type="text" name="posto" value="<?php echo e($f_posto); ?>" placeholder="041">
                </div>
                <div class="filtro-grupo" style="width:14%;">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary">Buscar</button>
                    <a href="consulta_producao.php" class="btn btn-secondary" style="text-decoration:none;">Limpar</a>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Estatisticas Resumo -->
    <?php if (!empty($estatisticas)): ?>
    <div class="painel">
        <div class="painel-titulo">Producao dos Ultimos 30 Dias</div>
        <div class="stats-grid">
            <?php
            $total_carteiras_30d = 0;
            $total_lotes_30d = 0;
            foreach ($estatisticas as $est) {
                $total_carteiras_30d += (int)$est['total_carteiras'];
                $total_lotes_30d += (int)$est['total_lotes'];
            }
            ?>
            <div class="stat-card">
                <div class="stat-valor"><?php echo number_format($total_carteiras_30d, 0, ',', '.'); ?></div>
                <div class="stat-label">Carteiras Produzidas</div>
            </div>
            <div class="stat-card">
                <div class="stat-valor"><?php echo number_format($total_lotes_30d, 0, ',', '.'); ?></div>
                <div class="stat-label">Lotes Processados</div>
            </div>
            <div class="stat-card">
                <div class="stat-valor"><?php echo count($estatisticas); ?></div>
                <div class="stat-label">Dias com Producao</div>
            </div>
            <div class="stat-card">
                <div class="stat-valor"><?php echo count($estatisticas) > 0 ? number_format($total_carteiras_30d / count($estatisticas), 0, ',', '.') : 0; ?></div>
                <div class="stat-label">Media Diaria</div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Tabs de Navegacao -->
    <div class="tabs">
        <button type="button" class="tab-btn active" onclick="showTab('despachos')">Despachos</button>
        <button type="button" class="tab-btn" onclick="showTab('estatisticas')">Estatisticas por Dia</button>
    </div>
    
    <!-- Tab Despachos -->
    <div id="tab-despachos" class="tab-content active">
        <div class="painel">
            <div class="painel-titulo">Lista de Despachos (Ofícios)</div>
            <div class="totais">
                <strong><?php echo count($despachos); ?></strong> despacho(s) encontrado(s)
                <?php if ($f_etiqueta): ?>| Buscando etiqueta: <strong><?php echo e($f_etiqueta); ?></strong><?php endif; ?>
                <?php if ($f_lote): ?>| Buscando lote: <strong><?php echo e($f_lote); ?></strong><?php endif; ?>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tipo</th>
                        <th>Datas</th>
                        <th>Usuario</th>
                        <th>Status</th>
                        <th>Postos</th>
                        <th>Total Carteiras</th>
                        <th>PDF Oficio</th>
                        <th>Acoes</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($despachos)): ?>
                    <tr><td colspan="9" style="text-align:center;">Nenhum despacho encontrado com os filtros informados.</td></tr>
                <?php else: ?>
                    <?php foreach ($despachos as $d): ?>
                        <?php
                        $row_class = '';
                        if ($f_lote !== '' || $f_etiqueta !== '') {
                            $row_class = 'lote-encontrado';
                        }
                        ?>
                        <tr class="<?php echo $row_class; ?>">
                            <td><?php echo (int)$d['id']; ?></td>
                            <td>
                                <?php if ($d['grupo'] === 'POUPA TEMPO'): ?>
                                    <span class="badge badge-poupa">Poupa Tempo</span>
                                <?php else: ?>
                                    <span class="badge badge-correios"><?php echo e($d['grupo']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                // v8.15.2: Formatar datas como dd-mm-yyyy (suporta yyyy-mm-dd e dd/mm/yyyy)
                                if (!empty($d['datas_str'])) {
                                    $datas_formatadas = array();
                                    $datas_arr = explode(',', $d['datas_str']);
                                    foreach ($datas_arr as $dt) {
                                        $dt = trim($dt);
                                        // Formato dd/mm/yyyy -> dd-mm-yyyy
                                        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $dt, $m)) {
                                            $datas_formatadas[] = $m[1] . '-' . $m[2] . '-' . $m[3];
                                        }
                                        // Formato yyyy-mm-dd -> dd-mm-yyyy (Poupa Tempo)
                                        elseif (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $dt, $m)) {
                                            $datas_formatadas[] = $m[3] . '-' . $m[2] . '-' . $m[1];
                                        }
                                        // Fallback: tenta converter qualquer formato
                                        else {
                                            // Tentar strtotime como último recurso
                                            $timestamp = strtotime($dt);
                                            if ($timestamp !== false) {
                                                $datas_formatadas[] = date('d-m-Y', $timestamp);
                                            } else {
                                                $datas_formatadas[] = $dt;
                                            }
                                        }
                                    }
                                    echo htmlspecialchars(implode(', ', $datas_formatadas), ENT_QUOTES, 'UTF-8');
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <td><?php echo e($d['usuario']); ?></td>
                            <td>
                                <?php if ($d['ativo']): ?>
                                    <span class="badge badge-ativo">Finalizado</span>
                                <?php else: ?>
                                    <span class="badge badge-inativo">Inativo</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:center;"><?php echo (int)$d['num_postos']; ?></td>
                            <td style="text-align:right;"><?php echo number_format((int)$d['total_carteiras'], 0, ',', '.'); ?></td>
                            <td style="text-align:center;">
                                <?php
                                // v8.15.3: Link com data de criado_at (data de criação do ofício)
                                // Nome do arquivo usa criado_at (não datas_str)
                                // Formato: Q:\cosep\IIPR\Oficios\{Ano}\{Mes}\{tipo}\ID_tipo_dd-mm-yyyy.pdf
                                
                                $dia = null;
                                $mes_num = null;
                                $ano = null;
                                
                                // Extrair data de criado_at (formato: 2025-12-10 15:12:30)
                                if (!empty($d['criado_at'])) {
                                    // Formato yyyy-mm-dd hh:mm:ss ou yyyy-mm-dd
                                    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $d['criado_at'], $m)) {
                                        $ano = $m[1];
                                        $mes_num = $m[2];
                                        $dia = $m[3];
                                    }
                                }
                                
                                // Só gera link se conseguiu extrair data válida
                                if ($dia && $mes_num && $ano) {
                                    // Nome do mes em portugues
                                    $meses = array('01'=>'Janeiro','02'=>'Fevereiro','03'=>'Marco','04'=>'Abril','05'=>'Maio','06'=>'Junho','07'=>'Julho','08'=>'Agosto','09'=>'Setembro','10'=>'Outubro','11'=>'Novembro','12'=>'Dezembro');
                                    $mes_nome = isset($meses[$mes_num]) ? $meses[$mes_num] : $mes_num;
                                    
                                    // v8.15.3: Determinar tipo em lowercase para pasta
                                    $tipo_pasta = $d['grupo'] === 'POUPA TEMPO' ? 'poupatempo' : 'correios';
                                    $tipo_lower = strtolower(str_replace(' ', '', $d['grupo'])); // 'correios' ou 'poupatempo'
                                    
                                    // v8.15.3: Nome do arquivo SEM # (padrão: ID_tipo_dd-mm-yyyy.pdf)
                                    $nome_arquivo = $d['id'] . '_' . $tipo_lower . '_' . $dia . '-' . $mes_num . '-' . $ano . '.pdf';
                                    
                                    // v8.15.3: Caminho correto Windows (lowercase, sem #)
                                    // Q:\cosep\IIPR\Oficios\2025\Dezembro\correios\88_correios_11-12-2025.pdf
                                    $caminho_windows = 'Q:\\cosep\\IIPR\\Oficios\\' . $ano . '\\' . $mes_nome . '\\' . $tipo_pasta . '\\' . $nome_arquivo;
                                    
                                    // v8.15.3: Converter para file:/// URL (lowercase, sem #)
                                    // file:///Q:cosep/IIPR/Oficios/2025/Dezembro/correios/88_correios_11-12-2025.pdf
                                    // v8.15.7: Correção de local de salvamento para CORREIOS e POUPA TEMPO
                                    if ($tipo_pasta === 'correios') {
                                        $pdf_link = '/var/www/dipro/controle/cioficios/correios/' . rawurlencode($nome_arquivo);
                                    } elseif ($tipo_pasta === 'poupatempo') {
                                        $pdf_link = '/var/www/dipro/controle/cioficios/poupatempo/' . rawurlencode($nome_arquivo);
                                    } else {
                                        $pdf_link = 'file:///Q:cosep/IIPR/Oficios/' . $ano . '/' . $mes_nome . '/' . $tipo_pasta . '/' . rawurlencode($nome_arquivo);
                                    }
                                    
                                    // ID visual do link
                                    $link_visual = '#' . $d['id'];
                                    ?>
                                    <!-- v8.15.5: Link abre em nova aba -->
                                    <a href="<?php echo htmlspecialchars($pdf_link, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" title="<?php echo htmlspecialchars($caminho_windows, ENT_QUOTES, 'UTF-8'); ?>" style="color:#007bff; text-decoration:underline; font-weight:bold; font-size:14px; cursor:pointer;">
                                        <?php echo htmlspecialchars($link_visual, ENT_QUOTES, 'UTF-8'); ?>
                                    </a>
                                    <?php
                                } else {
                                    echo '<span style="color:#999; font-size:11px;">Sem data</span>';
                                }
                                ?>
                            </td>
                            <td class="acoes">
                                <a href="?grupo=<?php echo urlencode($f_grupo); ?>&data_ini=<?php echo urlencode($f_data_ini); ?>&data_fim=<?php echo urlencode($f_data_fim); ?>&etiqueta=<?php echo urlencode($f_etiqueta); ?>&lote=<?php echo urlencode($f_lote); ?>&posto=<?php echo urlencode($f_posto); ?>&id=<?php echo (int)$d['id']; ?>">
                                    Ver Detalhes
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Detalhes do Despacho -->
        <?php if ($id_despacho > 0): ?>
        <?php
            // v8.17.0: Carregar tipo e dados do despacho selecionado - QUERIES CORRIGIDAS
            $despacho_tipo = '';
            $itens = array();
            $lotes = array();
            try {
                // Busca o tipo do despacho
                $stmtTipo = $pdo_controle->prepare("SELECT grupo, datas_str FROM ciDespachos WHERE id = ? LIMIT 1");
                $stmtTipo->execute(array($id_despacho));
                $rowTipo = $stmtTipo->fetch();
                if ($rowTipo && isset($rowTipo['grupo'])) {
                    $despacho_tipo = $rowTipo['grupo'];
                }
                $despacho_datas = '';
                if ($rowTipo && isset($rowTipo['datas_str'])) {
                    $despacho_datas = $rowTipo['datas_str'];
                }
                
                // Busca itens (Poupa Tempo)
                $colunasItens = array();
                $hasNomePosto = false;
                $hasCsvNome = false;
                $hasCsvPosto = false;
                try {
                    $stmtCols = $pdo_controle->query("SHOW COLUMNS FROM ciDespachoItens");
                    $cols = $stmtCols->fetchAll();
                    foreach ($cols as $c) {
                        if (isset($c['Field'])) {
                            $colunasItens[] = $c['Field'];
                        }
                    }
                    $hasNomePosto = in_array('nome_posto', $colunasItens);
                } catch (Exception $exCols) {
                    $hasNomePosto = false;
                }

                try {
                    $stmtColsCsv = $pdo_controle->query("SHOW COLUMNS FROM ciPostosCsv");
                    $colsCsv = $stmtColsCsv->fetchAll();
                    $colunasCsv = array();
                    foreach ($colsCsv as $c2) {
                        if (isset($c2['Field'])) {
                            $colunasCsv[] = $c2['Field'];
                        }
                    }
                    $hasCsvNome = in_array('nome', $colunasCsv);
                    $hasCsvPosto = in_array('posto', $colunasCsv);
                } catch (Exception $exColsCsv) {
                    $hasCsvNome = false;
                    $hasCsvPosto = false;
                }

                if ($despacho_tipo === 'POUPA TEMPO') {
                    $stmtItens = $pdo_controle->prepare("
                        SELECT 
                            i.*, 
                            l.data_carga AS l_data_carga,
                            l.responsaveis AS l_responsaveis,
                            l.etiquetaiipr AS l_etiquetaiipr,
                            l.etiquetacorreios AS l_etiquetacorreios,
                            l.etiqueta_correios AS l_etiqueta_correios
                        FROM ciDespachoItens i
                        LEFT JOIN ciDespachoLotes l ON l.id_despacho = i.id_despacho 
                            AND l.posto = i.posto 
                            AND l.lote = i.lote
                        WHERE i.id_despacho = ?
                        ORDER BY i.posto, i.lote
                    ");
                    $stmtItens->execute(array($id_despacho));
                    $rowsItens = $stmtItens->fetchAll();
                    $itens = array();

                    foreach ($rowsItens as $r) {
                        $posto = isset($r['posto']) ? $r['posto'] : '';
                        $lote = isset($r['lote']) ? $r['lote'] : '';
                        $quantidade = isset($r['quantidade']) ? $r['quantidade'] : 0;
                        $usuario = isset($r['usuario']) ? $r['usuario'] : '';
                        $lacre_iipr = '';
                        if (isset($r['lacre_iipr']) && $r['lacre_iipr'] !== '') {
                            $lacre_iipr = $r['lacre_iipr'];
                        } elseif (isset($r['l_etiquetaiipr']) && $r['l_etiquetaiipr'] !== '') {
                            $lacre_iipr = $r['l_etiquetaiipr'];
                        }
                        $lacre_correios = '';
                        if (isset($r['lacre_correios']) && $r['lacre_correios'] !== '') {
                            $lacre_correios = $r['lacre_correios'];
                        } elseif (isset($r['l_etiquetacorreios']) && $r['l_etiquetacorreios'] !== '') {
                            $lacre_correios = $r['l_etiquetacorreios'];
                        }
                        $etiqueta_correios = '';
                        if (isset($r['etiqueta_correios']) && $r['etiqueta_correios'] !== '') {
                            $etiqueta_correios = $r['etiqueta_correios'];
                        } elseif (isset($r['l_etiqueta_correios']) && $r['l_etiqueta_correios'] !== '') {
                            $etiqueta_correios = $r['l_etiqueta_correios'];
                        }
                        $nome_posto = '';
                        if ($hasNomePosto && isset($r['nome_posto']) && $r['nome_posto'] !== '') {
                            $nome_posto = $r['nome_posto'];
                        }
                        $data_carga = isset($r['l_data_carga']) ? $r['l_data_carga'] : '';
                        $responsaveis = isset($r['l_responsaveis']) ? $r['l_responsaveis'] : '';

                        $conferido = 'N';
                        if (isset($r['conferido_oficio']) && $r['conferido_oficio'] === 'S') {
                            $conferido = 'S';
                        } elseif ($lacre_iipr !== '') {
                            $conferido = 'S';
                        }

                        $itens[] = array(
                            'posto' => $posto,
                            'lote' => $lote,
                            'quantidade' => $quantidade,
                            'usuario' => $usuario,
                            'lacre_iipr' => $lacre_iipr,
                            'lacre_correios' => $lacre_correios,
                            'etiqueta_correios' => $etiqueta_correios,
                            'nome_posto' => $nome_posto,
                            'data_carga' => $data_carga,
                            'responsaveis' => $responsaveis,
                            'conferido' => $conferido,
                            'conferido_por' => ''
                        );
                    }
                }
                
                // Busca lotes (Correios - SEMPRE busca para mostrar)
                $stmtLotes = $pdo_controle->prepare("
                    SELECT 
                        l.posto, 
                        l.lote, 
                        l.quantidade, 
                        COALESCE(l.data_carga, '') AS data_carga,
                        COALESCE(l.responsaveis, '') AS responsaveis,
                        COALESCE(l.etiquetaiipr, 0) AS etiquetaiipr,
                        COALESCE(l.etiquetacorreios, 0) AS etiquetacorreios,
                        COALESCE(l.etiqueta_correios, '') AS etiqueta_correios,
                        'N' AS conferido,
                        '' AS conferido_por
                    FROM ciDespachoLotes l
                    WHERE l.id_despacho = ?
                    ORDER BY l.posto, CAST(l.lote AS UNSIGNED)
                ");
                $stmtLotes->execute(array($id_despacho));
                $lotes = $stmtLotes->fetchAll();

                // v9.22.5: fallback para lotes PT via ciPostosCsv quando vazio
                if ($despacho_tipo === 'POUPA TEMPO' && empty($lotes)) {
                    $datasSql = array();
                    if (!empty($despacho_datas)) {
                        $tmp = explode(',', $despacho_datas);
                        foreach ($tmp as $d) {
                            $d = trim($d);
                            if ($d === '') continue;
                            if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $d, $m)) {
                                $datasSql[] = $m[3] . '-' . $m[2] . '-' . $m[1];
                            } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) {
                                $datasSql[] = $d;
                            }
                        }
                    }

                    $postosFiltro = array();
                    foreach ($itens as $i) {
                        if (isset($i['posto']) && $i['posto'] !== '') {
                            $postosFiltro[str_pad((string)$i['posto'], 3, '0', STR_PAD_LEFT)] = true;
                        }
                    }

                    if (!empty($datasSql)) {
                        $phDatas = implode(',', array_fill(0, count($datasSql), '?'));
                        $paramsPt = $datasSql;

                        $sqlLotesPt = "
                            SELECT 
                                LPAD(c.posto,3,'0') AS posto,
                                c.lote,
                                SUM(COALESCE(c.quantidade,0)) AS quantidade,
                                MIN(DATE(c.dataCarga)) AS data_carga,
                                GROUP_CONCAT(DISTINCT c.usuario SEPARATOR ', ') AS responsaveis,
                                0 AS etiquetaiipr,
                                0 AS etiquetacorreios,
                                '' AS etiqueta_correios,
                                'N' AS conferido,
                                '' AS conferido_por
                            FROM ciPostosCsv c
                            INNER JOIN ciRegionais r 
                                ON LPAD(r.posto,3,'0') = LPAD(c.posto,3,'0')
                            WHERE DATE(c.dataCarga) IN ($phDatas)
                              AND REPLACE(LOWER(r.entrega),' ','') LIKE 'poupa%tempo'
                        ";

                        if (!empty($postosFiltro)) {
                            $postosList = array_keys($postosFiltro);
                            $phPostos = implode(',', array_fill(0, count($postosList), '?'));
                            $sqlLotesPt .= " AND LPAD(c.posto,3,'0') IN ($phPostos) ";
                            $paramsPt = array_merge($paramsPt, $postosList);
                        }

                        $sqlLotesPt .= " GROUP BY LPAD(c.posto,3,'0'), c.lote ORDER BY LPAD(c.posto,3,'0'), c.lote ";

                        $stmtLotesPt = $pdo_controle->prepare($sqlLotesPt);
                        $stmtLotesPt->execute($paramsPt);
                        $lotes = $stmtLotesPt->fetchAll();
                    }
                }

                // v9.22.5: preencher data_carga/responsaveis nos itens PT usando lotes
                if ($despacho_tipo === 'POUPA TEMPO' && !empty($itens) && !empty($lotes)) {
                    $mapaLotes = array();
                    foreach ($lotes as $l) {
                        $k = (string)$l['posto'] . '|' . (string)$l['lote'];
                        $mapaLotes[$k] = $l;
                    }

                    foreach ($itens as $ix => $i) {
                        $dataOk = !empty($i['data_carga']);
                        $respOk = !empty($i['responsaveis']);
                        if ($dataOk && $respOk) {
                            continue;
                        }
                        $lotesStr = isset($i['lote']) ? (string)$i['lote'] : '';
                        if ($lotesStr === '') {
                            continue;
                        }
                        $lotesList = array();
                        foreach (explode(',', $lotesStr) as $lt) {
                            $lt = trim($lt);
                            if ($lt !== '') {
                                $lotesList[] = $lt;
                            }
                        }
                        foreach ($lotesList as $lt) {
                            $k = (string)$i['posto'] . '|' . $lt;
                            if (isset($mapaLotes[$k])) {
                                if (!$dataOk && !empty($mapaLotes[$k]['data_carga'])) {
                                    $itens[$ix]['data_carga'] = $mapaLotes[$k]['data_carga'];
                                    $dataOk = true;
                                }
                                if (!$respOk && !empty($mapaLotes[$k]['responsaveis'])) {
                                    $itens[$ix]['responsaveis'] = $mapaLotes[$k]['responsaveis'];
                                    $respOk = true;
                                }
                            }
                            if ($dataOk && $respOk) {
                                break;
                            }
                        }
                    }
                }

                // v9.22.3: completar nome do posto via ciPostosCsv quando existir
                if (($hasCsvNome && $hasCsvPosto) && (!empty($itens) || !empty($lotes))) {
                    $postosParaBuscar = array();
                    foreach ($itens as $ix => $i) {
                        if (empty($i['nome_posto']) && isset($i['posto']) && $i['posto'] !== '') {
                            $postosParaBuscar[(string)$i['posto']] = true;
                        }
                    }
                    foreach ($lotes as $lx => $l) {
                        if ((!isset($l['nome_posto']) || $l['nome_posto'] === '') && isset($l['posto']) && $l['posto'] !== '') {
                            $postosParaBuscar[(string)$l['posto']] = true;
                        }
                    }

                    if (!empty($postosParaBuscar)) {
                        $postosList = array_keys($postosParaBuscar);
                        $placeholders = implode(',', array_fill(0, count($postosList), '?'));
                        $stmtPostosCsv = $pdo_controle->prepare("SELECT posto, nome FROM ciPostosCsv WHERE posto IN ($placeholders)");
                        $stmtPostosCsv->execute($postosList);
                        $mapaNomes = array();
                        $rowsCsv = $stmtPostosCsv->fetchAll();
                        foreach ($rowsCsv as $rc) {
                            $key = str_pad((string)$rc['posto'], 3, '0', STR_PAD_LEFT);
                            $mapaNomes[$key] = (string)$rc['nome'];
                        }

                        foreach ($itens as $ix => $i) {
                            if (empty($i['nome_posto']) && isset($i['posto'])) {
                                $k = str_pad((string)$i['posto'], 3, '0', STR_PAD_LEFT);
                                if (isset($mapaNomes[$k])) {
                                    $itens[$ix]['nome_posto'] = $mapaNomes[$k];
                                }
                            }
                        }

                        foreach ($lotes as $lx => $l) {
                            if ((!isset($l['nome_posto']) || $l['nome_posto'] === '') && isset($l['posto'])) {
                                $k = str_pad((string)$l['posto'], 3, '0', STR_PAD_LEFT);
                                if (isset($mapaNomes[$k])) {
                                    $lotes[$lx]['nome_posto'] = $mapaNomes[$k];
                                }
                            }
                        }
                    }
                }

                // v9.22.3: fallback para PT quando nao houver itens, usando lotes
                if ($despacho_tipo === 'POUPA TEMPO' && empty($itens) && !empty($lotes)) {
                    foreach ($lotes as $l) {
                        $confLote = (isset($l['etiquetaiipr']) && $l['etiquetaiipr'] !== 0 && $l['etiquetaiipr'] !== '0') ? 'S' : 'N';
                        $itens[] = array(
                            'posto' => isset($l['posto']) ? $l['posto'] : '',
                            'lote' => isset($l['lote']) ? $l['lote'] : '',
                            'quantidade' => isset($l['quantidade']) ? $l['quantidade'] : 0,
                            'usuario' => '',
                            'lacre_iipr' => isset($l['etiquetaiipr']) ? $l['etiquetaiipr'] : '',
                            'lacre_correios' => isset($l['etiquetacorreios']) ? $l['etiquetacorreios'] : '',
                            'etiqueta_correios' => isset($l['etiqueta_correios']) ? $l['etiqueta_correios'] : '',
                            'nome_posto' => isset($l['nome_posto']) ? $l['nome_posto'] : '',
                            'data_carga' => isset($l['data_carga']) ? $l['data_carga'] : '',
                            'responsaveis' => isset($l['responsaveis']) ? $l['responsaveis'] : '',
                            'conferido' => $confLote,
                            'conferido_por' => ''
                        );
                    }
                }

                // v9.22.3: garantir nome do posto padrao quando vazio
                foreach ($itens as $ix => $i) {
                    if (empty($i['nome_posto']) && isset($i['posto']) && $i['posto'] !== '') {
                        $itens[$ix]['nome_posto'] = 'Posto ' . $i['posto'];
                    }
                }
                
            } catch (Exception $e) {
                // v8.17.0: Exibe erro para debug em vez de falha silenciosa
                echo "<div style='background:#f8d7da; color:#721c24; padding:10px; margin:10px 0; border:1px solid #f5c6cb;'>";
                echo "<strong>Erro ao carregar detalhes:</strong> " . htmlspecialchars($e->getMessage());
                echo "</div>";
            }
        ?>
        <div class="painel">
            <div class="painel-titulo">Detalhes do Despacho #<?php echo (int)$id_despacho; ?></div>
            
            <!-- Versao 8.15.2: Resumo funciona para ambos (usa lotes ou itens conforme tipo) -->
            <?php
            $totalPostosLotes = 0;
            $totalCarteirasLotes = 0;
            $postosUnicos = array();
            $totalLotes = 0;
            
            // Para Poupa Tempo, usa ciDespachoItens
            if ($despacho_tipo === 'POUPA TEMPO' && !empty($itens)) {
                foreach ($itens as $i) {
                    $totalCarteirasLotes += (int)$i['quantidade'];
                    if (!isset($postosUnicos[$i['posto']])) {
                        $postosUnicos[$i['posto']] = true;
                        $totalPostosLotes++;
                    }
                }
                $totalLotes = count($itens);
            }
            // Para Correios, usa ciDespachoLotes
            else {
                if (!empty($lotes)) {
                    foreach ($lotes as $l) {
                        $totalCarteirasLotes += (int)$l['quantidade'];
                        if (!isset($postosUnicos[$l['posto']])) {
                            $postosUnicos[$l['posto']] = true;
                            $totalPostosLotes++;
                        }
                    }
                    $totalLotes = count($lotes);
                } else {
                    $totalLotes = 0;
                }
            }
            ?>
            <div class="totais" style="background:#d4edda; border:1px solid #28a745;">
                <strong style="color:#155724;">Resumo do Despacho:</strong>
                Total de postos: <strong><?php echo $totalPostosLotes; ?></strong> |
                Total de carteiras: <strong><?php echo number_format($totalCarteirasLotes, 0, ',', '.'); ?></strong> |
                Total de lotes: <strong><?php echo $totalLotes; ?></strong>
            </div>
            
            <!-- Itens por Posto (ciDespachoItens - usado para Poupa Tempo) -->
            <?php if (!empty($itens)): ?>
            <!-- v8.14.9.1: Adicionar badge indicando Poupa Tempo + mostrar totais -->
            <!-- v8.14.9.4: Título simplificado (sem ciDespachoItens) -->
            <h3 style="font-size:14px; margin:15px 0 10px 0;">
                Postos
                <?php if ($despacho_tipo === 'POUPA TEMPO'): ?>
                    <span style="background:#17a2b8;color:white;padding:3px 8px;border-radius:3px;font-size:12px;margin-left:10px;">POUPA TEMPO</span>
                <?php endif; ?>
            </h3>
            <?php
            $totalCart = 0;
            foreach ($itens as $i) {
                $totalCart += (int)$i['quantidade'];
            }
            ?>
            <!-- v8.14.9.1: Mostrar totais para Poupa Tempo -->
            <div class="totais">
                Total de postos: <strong><?php echo count($itens); ?></strong> |
                Total de carteiras: <strong><?php echo number_format($totalCart, 0, ',', '.'); ?></strong>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Posto</th>
                        <th>Lote</th>
                        <th>Nome do Posto</th>
                        <th>Quantidade</th>
                        <th>Data Carga</th>
                        <th>Responsaveis</th>
                        <th>Lacre IIPR</th>
                        <?php if ($despacho_tipo !== 'POUPA TEMPO'): ?>
                        <th>Lacre Correios</th>
                        <th>Etiqueta Correios</th>
                        <?php endif; ?>
                        <th>Conferido</th>
                        <th>Conferido Por</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($itens as $i): ?>
                        <?php
                        $item_class = '';
                        $is_conferido = (isset($i['conferido']) && $i['conferido'] === 'S');
                        if ($is_conferido) {
                            $item_class = 'lote-conferido';
                        }
                        if ($f_etiqueta !== '' && isset($i['etiqueta_correios']) && strpos($i['etiqueta_correios'], $f_etiqueta) !== false) {
                            $item_class = 'lote-encontrado';
                        }
                        if ($f_posto !== '' && (strpos($i['posto'], $f_posto) !== false || stripos($i['nome_posto'], $f_posto) !== false)) {
                            $item_class = 'lote-encontrado';
                        }
                        ?>
                        <tr class="<?php echo $item_class; ?>">
                            <td><?php echo e($i['posto']); ?></td>
                            <td><strong><?php echo e($i['lote']); ?></strong></td>
                            <td><?php echo e($i['nome_posto']); ?></td>
                            <td style="text-align:right;"><?php echo (int)$i['quantidade']; ?></td>
                            <td>
                                <?php
                                if (!empty($i['data_carga']) && $i['data_carga'] !== '0000-00-00') {
                                    $dt = DateTime::createFromFormat('Y-m-d', $i['data_carga']);
                                    echo $dt ? $dt->format('d-m-Y') : e($i['data_carga']);
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <td><?php echo e(isset($i['responsaveis']) ? $i['responsaveis'] : '-'); ?></td>
                            <td><?php echo e($i['lacre_iipr']); ?></td>
                            <?php if ($despacho_tipo !== 'POUPA TEMPO'): ?>
                            <td><?php echo e($i['lacre_correios']); ?></td>
                            <td style="font-size:10px;"><?php echo e($i['etiqueta_correios']); ?></td>
                            <?php endif; ?>
                            <td style="text-align:center;">
                                <?php if ($is_conferido): ?>
                                    <span class="badge badge-conferido">Sim</span>
                                <?php else: ?>
                                    <span class="badge badge-pendente">Nao</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e(isset($i['conferido_por']) ? $i['conferido_por'] : '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <!-- Versao 6: Mensagem para despachos sem ciDespachoItens (ex: CORREIOS) -->
            <div class="totais" style="background:#fff3cd; border:1px solid #ffc107;">
                <strong style="color:#856404;">Nota:</strong> Este despacho nao possui dados em ciDespachoItens.
                <?php if ($despacho_tipo === 'CORREIOS'): ?>
                Os dados de postos Correios sao armazenados diretamente na tabela de lotes abaixo.
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- Lotes (Versao 6: conferencia e responsavel) -->
            <!-- v8.14.9.3: Mostrar ciDespachoLotes SOMENTE se houver lotes (evita cabeçalho vazio em PT) -->
            <?php if (!empty($lotes)): ?>
            <!-- v8.14.9.4: Título simplificado (sem ciDespachoLotes) -->
            <h3 style="font-size:14px; margin:20px 0 10px 0;">
                Lotes
                <?php if ($despacho_tipo === 'POUPA TEMPO'): ?>
                    <span style="background:#17a2b8;color:white;padding:3px 8px;border-radius:3px;font-size:12px;margin-left:10px;">POUPA TEMPO</span>
                <?php else: ?>
                    <span style="background:#ffc107;color:#000;padding:3px 8px;border-radius:3px;font-size:12px;margin-left:10px;">CORREIOS</span>
                <?php endif; ?>
            </h3>
            <table>
                <thead>
                    <tr>
                        <th>Posto</th>
                        <th>Lote</th>
                        <th>Quantidade</th>
                        <th>Data Carga</th>
                        <th>Responsaveis</th>
                        <?php if ($despacho_tipo === 'CORREIOS'): ?>
                        <th>Lacre IIPR</th>
                        <th>Lacre Correios</th>
                        <?php endif; ?>
                        <th>Etiqueta Correios</th>
                        <th>Conferido</th>
                        <th>Conferido Por</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($lotes)): ?>
                    <tr><td colspan="8">Nenhum lote encontrado.</td></tr>
                <?php else: ?>
                    <?php foreach ($lotes as $l): ?>
                        <?php
                        $lote_class = '';
                        $is_conferido = (isset($l['conferido']) && $l['conferido'] === 'S');
                        if ($is_conferido) {
                            $lote_class = 'lote-conferido';
                        }
                        if ($f_lote !== '' && strpos($l['lote'], $f_lote) !== false) {
                            $lote_class = 'lote-encontrado';
                        }
                        ?>
                        <tr class="<?php echo $lote_class; ?>">
                            <td><?php echo e($l['posto']); ?></td>
                            <td><strong><?php echo e($l['lote']); ?></strong></td>
                            <td style="text-align:right;"><?php echo (int)$l['quantidade']; ?></td>
                            <td>
                                <?php
                                if (!empty($l['data_carga']) && $l['data_carga'] !== '0000-00-00') {
                                    $dt = DateTime::createFromFormat('Y-m-d', $l['data_carga']);
                                    echo $dt ? $dt->format('d-m-Y') : e($l['data_carga']);
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <td><?php echo e($l['responsaveis']); ?></td>
                            <?php if ($despacho_tipo === 'CORREIOS'): ?>
                            <td><?php echo e(isset($l['etiquetaiipr']) ? $l['etiquetaiipr'] : '-'); ?></td>
                            <td><?php echo e(isset($l['etiquetacorreios']) ? $l['etiquetacorreios'] : '-'); ?></td>
                            <?php endif; ?>
                            <td style="font-size:10px; max-width:100px; word-break:break-all;"><?php echo e($l['etiqueta_correios']); ?></td>
                            <td style="text-align:center;">
                                <?php if ($is_conferido): ?>
                                    <span class="badge badge-conferido">Sim</span>
                                <?php else: ?>
                                    <span class="badge badge-pendente">Nao</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e(isset($l['conferido_por']) ? $l['conferido_por'] : ''); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- Tab Estatisticas -->
    <div id="tab-estatisticas" class="tab-content">
        <!-- Painel: Resumo 30 dias -->
        <?php if (!empty($estatisticas)): ?>
        <div class="painel">
            <div class="painel-titulo">Resumo dos Últimos 30 Dias</div>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-valor"><?php echo number_format($stats_resumo['total_carteiras'], 0, ',', '.'); ?></div>
                    <div class="stat-label">Carteiras Produzidas</div>
                </div>
                <div class="stat-card">
                    <div class="stat-valor"><?php echo number_format($stats_resumo['total_oficios'], 0, ',', '.'); ?></div>
                    <div class="stat-label">Ofícios</div>
                </div>
                <div class="stat-card">
                    <div class="stat-valor"><?php echo number_format($stats_resumo['media_diaria'], 0, ',', '.'); ?></div>
                    <div class="stat-label">Média Diária</div>
                </div>
                <div class="stat-card">
                    <div class="stat-valor"><?php echo number_format($stats_resumo['pico_dia'], 0, ',', '.'); ?></div>
                    <div class="stat-label">Pico (Dia)</div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Painel: Produção por Dia -->
        <div class="painel">
            <div class="painel-titulo">Produção por Dia (Últimos 30 dias)</div>
            <table>
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Total de Carteiras</th>
                        <th>Lotes Processados</th>
                        <th>Postos Atendidos</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($estatisticas)): ?>
                    <tr><td colspan="4">Nenhuma estatística disponível.</td></tr>
                <?php else: ?>
                    <?php foreach ($estatisticas as $est): ?>
                        <tr>
                            <td>
                                <?php
                                if (!empty($est['data_producao'])) {
                                    $dt = DateTime::createFromFormat('Y-m-d', $est['data_producao']);
                                    echo $dt ? $dt->format('d/m/Y') : e($est['data_producao']);
                                }
                                ?>
                            </td>
                            <td style="text-align:right; font-weight:bold; color:#007bff;">
                                <?php echo number_format((int)$est['total_carteiras'], 0, ',', '.'); ?>
                            </td>
                            <td style="text-align:center;"><?php echo (int)$est['total_lotes']; ?></td>
                            <td style="text-align:center;"><?php echo (int)$est['total_postos']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Painel: Produção por Usuário -->
        <div class="painel">
            <div class="painel-titulo">Produção por Usuário (Últimos 30 dias)</div>
            <table>
                <thead>
                    <tr>
                        <th>Usuário</th>
                        <th>Ofícios</th>
                        <th>Carteiras</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($stats_usuario)): ?>
                    <tr><td colspan="3">Nenhum dado disponível para usuários.</td></tr>
                <?php else: ?>
                    <?php foreach ($stats_usuario as $u): ?>
                        <tr>
                            <td><?php echo e($u['usuario']); ?></td>
                            <td style="text-align:center;">&nbsp;<?php echo (int)$u['total_oficios']; ?></td>
                            <td style="text-align:right; font-weight:bold; color:#007bff;">&nbsp;<?php echo number_format((int)$u['total_carteiras'], 0, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Painel: Postos com Maior Demanda -->
        <div class="painel">
            <div class="painel-titulo">Postos com Maior Demanda (Últimos 30 dias)</div>
            <table>
                <thead>
                    <tr>
                        <th>Posto</th>
                        <th>Ofícios</th>
                        <th>Carteiras</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($stats_postos)): ?>
                    <tr><td colspan="3">Nenhum dado disponível para postos.</td></tr>
                <?php else: ?>
                    <?php foreach ($stats_postos as $p): ?>
                        <tr>
                            <td><?php echo e($p['codigo_posto']); ?></td>
                            <td style="text-align:center;">&nbsp;<?php echo (int)$p['total_oficio']; ?></td>
                            <td style="text-align:right; font-weight:bold; color:#007bff;">&nbsp;<?php echo number_format((int)$p['total_carteiras'], 0, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Painel: Distribuição por Tipo -->
        <div class="painel">
            <div class="painel-titulo">Distribuição por Tipo (Últimos 30 dias)</div>
            <table>
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Ofícios</th>
                        <th>Carteiras</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($stats_tipo)): ?>
                    <tr><td colspan="3">Nenhum dado disponível para tipos.</td></tr>
                <?php else: ?>
                    <?php foreach ($stats_tipo as $t): ?>
                        <tr>
                            <td><?php echo e($t['grupo']); ?></td>
                            <td style="text-align:center;">&nbsp;<?php echo (int)$t['total_oficios']; ?></td>
                            <td style="text-align:right; font-weight:bold; color:#007bff;">&nbsp;<?php echo number_format((int)$t['total_carteiras'], 0, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
</div>

<script type="text/javascript">
// Funcao para trocar abas (compativel com IE8)
function showTab(tabName) {
    // Esconder todas as tabs
    var contents = document.getElementsByClassName('tab-content');
    for (var i = 0; i < contents.length; i++) {
        contents[i].className = contents[i].className.replace(/\s*active/g, '');
    }
    
    // Remover active de todos os botoes
    var btns = document.getElementsByClassName('tab-btn');
    for (var i = 0; i < btns.length; i++) {
        btns[i].className = btns[i].className.replace(/\s*active/g, '');
    }
    
    // Mostrar tab selecionada
    var tab = document.getElementById('tab-' + tabName);
    if (tab) {
        tab.className = tab.className + ' active';
    }
    
    // Marcar botao como ativo
    for (var i = 0; i < btns.length; i++) {
        if (btns[i].getAttribute('onclick') && btns[i].getAttribute('onclick').indexOf(tabName) >= 0) {
            btns[i].className = btns[i].className + ' active';
        }
    }
}
</script>

</body>
</html>
<?php
$pdo_controle = null;
?>
