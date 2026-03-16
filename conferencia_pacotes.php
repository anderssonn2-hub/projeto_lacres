<?php
/* conferencia_pacotes.php — v0.9.25.8
 * CHANGELOG v9.25.8:
 * - [AJUSTE] Filtro inicial usa a data do dia por padrao
 * - [AJUSTE] Versao atualizada para 0.9.25.8
 *
 * CHANGELOG v9.25.7:
 * - [CORRIGIDO] Aviso de pacote não encontrado emitido apenas uma vez por leitura
 * - [NOVO] Classificação por chips e modo tradicional iniciam recolhidos e alternam entre si
 * - [AJUSTE] Painel de operação com títulos destacados, contadores de Pacotes, Conferidos e Pendentes
 * - [AJUSTE] Datas exibidas no padrão brasileiro e reset visual sincronizado entre tabela e chips
 *
 * CHANGELOG v9.25.6:
 * - [NOVO] Card Operação com conferência por chips e detalhamento por lote
 * - [NOVO] Rolagem automática para o chip correspondente ao código lido
 * - [NOVO] Indicador por posto com pacotes, conferidos e sem upload
 *
 * CHANGELOG v9.25.5:
 * - [NOVO] Opção "Todos" no início da conferência
 * - [CORRIGIDO] Aviso explícito para código dos Correios durante modo Poupa Tempo
 * - [CORRIGIDO] Áudio mp3 para "pacote não encontrado"
 *
 * CHANGELOG v9.25.4:
 * - [NOVO] Aviso visual e fala para "pacote não encontrado"
 * - [NOVO] Salvamento em fila com autor, turno e data de criação
 * - [NOVO] Consolidação opcional de lançamentos em ciPostos no momento do salvamento
 * - [NOVO] Inserção dos novos lotes em ciPostosCsv ao finalizar a fila
 * - [AJUSTE] Pacotes não encontrados não são mais salvos imediatamente ao adicionar
 *
 * CHANGELOG v9.24.8:
 * - [NOVO] Total de pacotes na estante por leitura (encontra_posto)
 * - [NOVO] Lotes na estante sem upload no filtro atual
 * - [AJUSTE] Versao atualizada
 *
 * CHANGELOG v9.24.6:
 * - [NOVO] Coluna com responsavel pela producao do lote
 * - [NOVO] Coluna com data/hora da conferencia
 * - [NOVO] Capital e Central separados por posto
 *
 * CHANGELOG v0.9.25.1:
 * - [CORRIGIDO] Confirmacao verde somente quando conferido no banco
 * - [CORRIGIDO] Persistencia das conferencias em conferencia_pacotes
 * - [CORRIGIDO] Desbloqueio de audio para beep e voz
 * - [NOVO] Historico de bloqueios com responsavel
 * - [AJUSTE] Bloqueio/desbloqueio exige responsavel
 *
 * CHANGELOG v9.24.5:
 * - [AJUSTE] Responsavel aparece apenas uma vez por sessao
 * - [AJUSTE] Contagem por tabela atualiza ao conferir
 * - [NOVO] Ordenacao por Lote e Data de Expedicao
 * - [NOVO] Link "Voltar ao Inicio" na barra superior
 *
 * CHANGELOG v9.24.2:
 * - [CORRIGIDO] Pacotes nao listados salvam no ciPostosCsv ao adicionar
 * - [NOVO] Aviso "Pacote de outra data" quando filtro nao inclui o pacote
 *
 * CHANGELOG v9.24.1:
 * - [NOVO] Escolha obrigatoria de tipo apos informar responsavel
 * - [NOVO] Tela inicial separada (inicio.php)
 *
 * CHANGELOG v9.24.0:
 * - [NOVO] Postos bloqueados (nao enviar este posto) com audio dinamico
 * - [NOVO] Pacotes nao encontrados acumulados para salvar ao final
 * - [NOVO] Audio dinamico para pacote nao encontrado
 * - [MELHORIA] Layout responsivo para uso em celulares
 * - [AJUSTE] Status de conferencias recolhido no topo esquerdo
 * - [AJUSTE] Toggle deslizante para beep e rotulo de responsavel
 * - [MELHORIA] Banners para grupos Correios e Poupa Tempo
 *
 * CHANGELOG v9.23.6:
 * - [CORRIGIDO] Fallback por lote para linhas já conferidas
 *
 * CHANGELOG v9.23.5:
 * - [CORRIGIDO] Correspondência de conferência com/sem zeros à esquerda
 *
 * CHANGELOG v9.23.4:
 * - [CORRIGIDO] Linhas verdes por codbar e chave normalizada
 * - [CORRIGIDO] Não marca como conferido quando pacote é de outra regional/tipo
 * - [CORRIGIDO] Áudio pertence_aos_correios para PT selecionado
 *
 * CHANGELOG v9.23.3:
 * - [CORRIGIDO] Alerta PT no primeiro pacote quando tipo inicial é Correios
 * - [REMOVIDO] Card de últimas conferências
 * - [MANTIDO] Pacotes já conferidos em verde
 *
 * CHANGELOG v9.23.2:
 * - [NOVO] Inserção de pacotes não listados (ciPostosCsv + ciPostos)
 * - [NOVO] Seleção do tipo de conferência (Correios/PT)
 * - [NOVO] Alerta pertence_aos_correios.mp3 (Correios no meio de PT)
 * - [NOVO] Opção de silenciar beep.mp3
 * - [AJUSTE] Status de conferências no topo (não acompanha scroll)
 *
 * CHANGELOG v9.23.1:
 * - [NOVO] Bloqueio inicial até informar usuário
 * - [NOVO] Usuário exibido após liberação
 *
 * CHANGELOG v9.23.0:
 * - [NOVO] Usuário obrigatório para iniciar conferência
 * - [NOVO] Card Status de Conferências (últimas/pendentes)
 * - [CORRIGIDO] Salvamento de dataexp na conferência
 *
 * CHANGELOG v9.22.9:
 * - [CORRIGIDO] Inputs do filtro visíveis
 * - [MELHORADO] PT segregado por posto (concluido por grupo)
 *
 * CHANGELOG v9.22.8:
 * - [NOVO] Filtro por intervalo + datas avulsas
 * - [NOVO] Cards de resumo (carteiras, conferidas, postos)
 * - [NOVO] Lista das últimas conferências
 * - [MELHORADO] Scroll central e pulsação da última leitura
 * - [MELHORADO] Desbloqueio de áudio para beep
 *
 * CHANGELOG v9.22.7:
 * - [NOVO] Fila de áudio sem sobreposição
 * - [NOVO] beep.mp3 em toda leitura válida de código
 * - [NOVO] concluido.mp3 ao finalizar grupo (mesmo com 1 pacote)
 * - [NOVO] pacotedeoutraregional.mp3 para regional diferente
 * - [NOVO] posto_poupatempo.mp3 para PT no meio de correios (e PT único)
 * 
 * LÓGICA INTELIGENTE DE SONS BASEADA NO CONTEXTO:
 * - beep.mp3: toda leitura válida de código de barras
 * - posto_poupatempo.mp3: PT aparece enquanto confere correios (misturado!)
 * - pacotedeoutraregional.mp3: Regional diferente OU correios no meio do PT
 * - pacotejaconferido.mp3: Pacote já conferido
 * - concluido.mp3: Grupo completo conferido (PT/Capital/R01/999/regionais)
 * 
 * Agrupamento (fonte: ciRegionais):
 * 1. Postos do Poupa Tempo - UMA tabela (ciRegionais.entrega)
 * 2. Postos do Posto 01 - UMA tabela (ciRegionais.regional = 1, exceto PT)
 * 3. Postos da Capital - UMA tabela (ciRegionais.regional = 0)
 * 4. Postos da Central IIPR - UMA tabela (ciRegionais.regional = 999)
 * 5. Regionais - ordem crescente (100, 105, 200...)
 */

// Inicializa variáveis
$total_codigos = 0;
$datas_expedicao = array();
$regionais_data = array();
$data_ini = isset($_GET['data_ini']) ? trim($_GET['data_ini']) : '';
$data_fim = isset($_GET['data_fim']) ? trim($_GET['data_fim']) : '';
$datas_avulsas = isset($_GET['datas_avulsas']) ? trim($_GET['datas_avulsas']) : '';
$datas_sql = array();
$datas_exib = array();
$poupaTempoPostos = array();
$conferencias = array();
$conferencias_info = array();
$conferencias_lote = array();
$dias_com_conferencia = array();
$dias_sem_conferencia = array();
$metadados_dias = array();

function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function obterColunasTabela($pdo, $tabela) {
    static $cache = array();
    if (!isset($cache[$tabela])) {
        $stmtCols = $pdo->query("SHOW COLUMNS FROM `" . $tabela . "`");
        $cols = $stmtCols->fetchAll(PDO::FETCH_COLUMN, 0);
        $cache[$tabela] = is_array($cols) ? $cols : array();
    }
    return $cache[$tabela];
}

function tabelaTemColuna($pdo, $tabela, $coluna) {
    return in_array($coluna, obterColunasTabela($pdo, $tabela), true);
}

function mapearTurnoCiPostos($turno) {
    $turno = trim((string)$turno);
    if ($turno === 'Madrugada') {
        return 0;
    }
    if ($turno === 'Tarde') {
        return 2;
    }
    if ($turno === 'Noite') {
        return 3;
    }
    return 1;
}

function normalizarDataSqlPacote($valor) {
    $valor = trim((string)$valor);
    if ($valor === '') {
        return '';
    }
    if (preg_match('/^(\d{2})\-(\d{2})\-(\d{4})$/', $valor, $m)) {
        return $m[3] . '-' . $m[2] . '-' . $m[1];
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)) {
        return $valor;
    }
    return '';
}

function normalizarDataHoraSql($valor) {
    $valor = trim((string)$valor);
    if ($valor === '') {
        return '';
    }
    $valor = str_replace('T', ' ', $valor);
    if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $valor)) {
        return $valor . ':00';
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $valor)) {
        return $valor;
    }
    return '';
}

function resolverNomePostoCiPostos($pdo, $posto) {
    $posto = trim((string)$posto);
    if ($posto === '') {
        return '';
    }
    if (!preg_match('/^\d+$/', $posto)) {
        return $posto;
    }
    $postoPad = str_pad((string)((int)$posto), 3, '0', STR_PAD_LEFT);
    try {
        $stmt = $pdo->prepare("SELECT posto FROM ciPostos WHERE posto LIKE ? ORDER BY id DESC LIMIT 1");
        $stmt->execute(array($postoPad . ' -%'));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && !empty($row['posto'])) {
            return $row['posto'];
        }
    } catch (Exception $e) {
    }
    return $postoPad . ' - POSTO';
}

// Conexão
$host = '10.15.61.169';
$dbname = 'controle';
$user = 'controle_mat';
$pass = '375256';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // v9.24.0: Postos bloqueados
    $pdo->exec("CREATE TABLE IF NOT EXISTS ciPostosBloqueados (
        id INT NOT NULL AUTO_INCREMENT,
        posto VARCHAR(10) NOT NULL,
        nome VARCHAR(120) DEFAULT NULL,
        motivo VARCHAR(255) DEFAULT NULL,
        ativo TINYINT(1) NOT NULL DEFAULT 1,
        criado DATETIME NOT NULL,
        atualizado DATETIME DEFAULT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY posto (posto)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8");

    $colsMotivo = $pdo->query("SHOW COLUMNS FROM ciPostosBloqueados LIKE 'motivo'")->fetchAll();
    if (count($colsMotivo) === 0) {
        $pdo->exec("ALTER TABLE ciPostosBloqueados ADD COLUMN motivo VARCHAR(255) DEFAULT NULL AFTER nome");
    }

    // v9.24.6: Historico de bloqueios
    $pdo->exec("CREATE TABLE IF NOT EXISTS ciPostosBloqueadosHistorico (
        id INT NOT NULL AUTO_INCREMENT,
        posto VARCHAR(10) NOT NULL,
        acao VARCHAR(20) NOT NULL,
        motivo VARCHAR(255) DEFAULT NULL,
        responsavel VARCHAR(120) NOT NULL,
        criado DATETIME NOT NULL,
        PRIMARY KEY (id),
        KEY idx_posto (posto),
        KEY idx_criado (criado)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8");

    // v9.24.6: Data/hora de conferencia
    $colsConf = $pdo->query("SHOW COLUMNS FROM conferencia_pacotes LIKE 'conferido_em'")->fetchAll();
    if (count($colsConf) === 0) {
        $pdo->exec("ALTER TABLE conferencia_pacotes ADD COLUMN conferido_em DATETIME DEFAULT NULL");
    }

    // v9.24.8: Controle de pacotes lidos na estante
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

    // Handler AJAX salvar
    if (isset($_POST['salvar_lote_ajax'])) {
        $lote = trim($_POST['lote']);
        $regional = trim($_POST['regional']);
        $posto = trim($_POST['posto']);
        $dataexp = trim($_POST['dataexp']);
        $qtd = (int)$_POST['qtd'];
        $codbar = trim($_POST['codbar']);
        $usuario_conf = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';

        if ($dataexp === '') {
            $dataexp = date('d-m-Y');
        }
        if ($usuario_conf === '') {
            die(json_encode(array('success' => false, 'erro' => 'Usuario obrigatorio')));
        }
        
        $sql = "INSERT INTO conferencia_pacotes (regional, nlote, nposto, dataexp, qtd, codbar, conf, usuario, conferido_em) 
            VALUES (?, ?, ?, ?, ?, ?, 's', ?, NOW())
            ON DUPLICATE KEY UPDATE conf='s', qtd=VALUES(qtd), codbar=VALUES(codbar), dataexp=VALUES(dataexp), usuario=VALUES(usuario), conferido_em=NOW()";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($regional, $lote, $posto, $dataexp, $qtd, $codbar, $usuario_conf));
        $stmt = null; // v8.17.4: Libera statement
        $pdo = null;  // v8.17.4: Fecha conexão
        die(json_encode(array('success' => true)));
    }

    // v9.23.2: Inserir pacotes não listados (ciPostosCsv + ciPostos)
    if (isset($_POST['inserir_pacotes_nao_listados'])) {
        $payload = isset($_POST['pacotes']) ? $_POST['pacotes'] : '';
        $usuario_conf = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
        $autor_salvamento = isset($_POST['autor_salvamento']) ? trim($_POST['autor_salvamento']) : '';
        $criado_salvamento = isset($_POST['criado_salvamento']) ? trim($_POST['criado_salvamento']) : '';
        $turno_salvamento = isset($_POST['turno_salvamento']) ? trim($_POST['turno_salvamento']) : 'Manhã';
        $consolidar_salvamento = !empty($_POST['consolidar_salvamento']);
        if ($usuario_conf === '') {
            die(json_encode(array('success' => false, 'erro' => 'Usuario obrigatorio')));
        }
        if ($autor_salvamento === '') {
            $autor_salvamento = $usuario_conf;
        }
        $pacotes = json_decode($payload, true);
        if (!is_array($pacotes)) {
            die(json_encode(array('success' => false, 'erro' => 'Payload invalido')));
        }

        $criado_sql = normalizarDataHoraSql($criado_salvamento);
        if ($criado_sql === '') {
            $criado_sql = date('Y-m-d H:i:s');
        }
        $turno_codigo = mapearTurnoCiPostos($turno_salvamento);

        $ok = 0;
        $ok_postos = 0;
        $erros = array();
        $stmtCsv = $pdo->prepare("INSERT INTO ciPostosCsv (lote, posto, regional, quantidade, dataCarga, data, usuario) VALUES (?,?,?,?,?,NOW(),?)");
        $gruposCiPostos = array();

        foreach ($pacotes as $p) {
            try {
                $lote = isset($p['lote']) ? trim($p['lote']) : '';
                $posto = isset($p['posto']) ? trim($p['posto']) : '';
                $regional = isset($p['regional']) ? trim($p['regional']) : '';
                $quantidade = isset($p['quantidade']) ? (int)$p['quantidade'] : 0;
                $dataexp = isset($p['dataexp']) ? trim($p['dataexp']) : '';
                $usuario_pacote = isset($p['responsavel']) ? trim($p['responsavel']) : '';
                if ($usuario_pacote === '') {
                    $usuario_pacote = $usuario_conf;
                }

                if ($lote === '' || $posto === '' || $regional === '' || $quantidade <= 0 || $dataexp === '') {
                    throw new Exception('Campos obrigatorios ausentes');
                }

                $data_sql = normalizarDataSqlPacote($dataexp);
                if ($data_sql === '') {
                    throw new Exception('Data invalida');
                }

                $stmtCsv->execute(array($lote, $posto, $regional, $quantidade, $data_sql, $usuario_pacote));
                $ok++;

                $chaveGrupo = $posto . '|' . $data_sql . '|' . ($consolidar_salvamento ? $usuario_pacote : $lote . '|' . $regional);
                if (!isset($gruposCiPostos[$chaveGrupo])) {
                    $gruposCiPostos[$chaveGrupo] = array(
                        'posto' => $posto,
                        'dia' => $data_sql,
                        'quantidade' => 0,
                        'turno' => $turno_codigo,
                        'autor' => $autor_salvamento,
                        'criado' => $criado_sql,
                        'regional' => $regional,
                        'lote' => $lote,
                        'responsavel' => $usuario_pacote
                    );
                }
                $gruposCiPostos[$chaveGrupo]['quantidade'] += $quantidade;
            } catch (Exception $ex) {
                $erros[] = $ex->getMessage();
            }
        }

        foreach ($gruposCiPostos as $grupo) {
            try {
                $campos = array();
                $vals = array();
                $pars = array();

                if (tabelaTemColuna($pdo, 'ciPostos', 'posto')) {
                    $campos[] = 'posto';
                    $vals[] = '?';
                    $pars[] = resolverNomePostoCiPostos($pdo, $grupo['posto']);
                }
                if (tabelaTemColuna($pdo, 'ciPostos', 'dia')) {
                    $campos[] = 'dia';
                    $vals[] = '?';
                    $pars[] = $grupo['dia'];
                }
                if (tabelaTemColuna($pdo, 'ciPostos', 'quantidade')) {
                    $campos[] = 'quantidade';
                    $vals[] = '?';
                    $pars[] = (int)$grupo['quantidade'];
                }
                if (tabelaTemColuna($pdo, 'ciPostos', 'turno')) {
                    $campos[] = 'turno';
                    $vals[] = '?';
                    $pars[] = (int)$grupo['turno'];
                }
                if (tabelaTemColuna($pdo, 'ciPostos', 'autor')) {
                    $campos[] = 'autor';
                    $vals[] = '?';
                    $pars[] = $grupo['autor'];
                }
                if (tabelaTemColuna($pdo, 'ciPostos', 'criado')) {
                    $campos[] = 'criado';
                    $vals[] = '?';
                    $pars[] = $grupo['criado'];
                }
                if (tabelaTemColuna($pdo, 'ciPostos', 'regional')) {
                    $campos[] = 'regional';
                    $vals[] = '?';
                    $pars[] = is_numeric($grupo['regional']) ? (int)$grupo['regional'] : null;
                }
                if (tabelaTemColuna($pdo, 'ciPostos', 'lote') && !$consolidar_salvamento) {
                    $campos[] = 'lote';
                    $vals[] = '?';
                    $pars[] = is_numeric($grupo['lote']) ? (int)$grupo['lote'] : 0;
                }
                if (tabelaTemColuna($pdo, 'ciPostos', 'situacao')) {
                    $campos[] = 'situacao';
                    $vals[] = '?';
                    $pars[] = 0;
                }

                if (!empty($campos)) {
                    $sqlPostos = "INSERT INTO ciPostos (" . implode(',', $campos) . ") VALUES (" . implode(',', $vals) . ")";
                    $stmtPostos = $pdo->prepare($sqlPostos);
                    $stmtPostos->execute($pars);
                    $ok_postos++;
                }
            } catch (Exception $ex) {
                $erros[] = $ex->getMessage();
            }
        }

        $stmtCsv = null;
        $pdo = null;
        die(json_encode(array(
            'success' => $ok > 0,
            'inseridos' => $ok,
            'inseridos_postos' => $ok_postos,
            'consolidado' => $consolidar_salvamento,
            'erros' => $erros
        )));
    }

    // v9.24.2: Verificar se pacote existe em outra data
    if (isset($_POST['verificar_pacote_data'])) {
        $codbar = isset($_POST['codbar']) ? preg_replace('/\D+/', '', $_POST['codbar']) : '';
        $datasFiltro = array();
        if (isset($_POST['datas_sql'])) {
            $tmp = json_decode($_POST['datas_sql'], true);
            if (is_array($tmp)) { $datasFiltro = $tmp; }
        }

        if (strlen($codbar) !== 19) {
            die(json_encode(array('success' => false, 'status' => 'invalido')));
        }

        $lote = substr($codbar, 0, 8);
        $regional = substr($codbar, 8, 3);
        $posto = substr($codbar, 11, 3);

        $status = 'nao_encontrado';
        $dataEncontrada = '';

        try {
            if (!empty($datasFiltro)) {
                $ph = implode(',', array_fill(0, count($datasFiltro), '?'));
                $sqlCheck = "SELECT COUNT(*) FROM ciPostosCsv WHERE lote = ? AND regional = ? AND posto = ? AND DATE(dataCarga) IN ($ph)";
                $stmtCheck = $pdo->prepare($sqlCheck);
                $params = array_merge(array($lote, $regional, $posto), $datasFiltro);
                $stmtCheck->execute($params);
                $existeNaData = (int)$stmtCheck->fetchColumn();
                if ($existeNaData > 0) {
                    $status = 'na_data';
                }
            }

            if (empty($datasFiltro)) {
                $stmtAny = $pdo->prepare("SELECT DATE(dataCarga) as data FROM ciPostosCsv WHERE lote = ? AND regional = ? AND posto = ? ORDER BY dataCarga DESC LIMIT 1");
                $stmtAny->execute(array($lote, $regional, $posto));
                $rowAny = $stmtAny->fetch(PDO::FETCH_ASSOC);
                if ($rowAny && !empty($rowAny['data'])) {
                    $status = 'na_data';
                }
            } elseif ($status !== 'na_data') {
                $stmtAny = $pdo->prepare("SELECT DATE(dataCarga) as data FROM ciPostosCsv WHERE lote = ? AND regional = ? AND posto = ? ORDER BY dataCarga DESC LIMIT 1");
                $stmtAny->execute(array($lote, $regional, $posto));
                $rowAny = $stmtAny->fetch(PDO::FETCH_ASSOC);
                if ($rowAny && !empty($rowAny['data'])) {
                    $status = 'outra_data';
                    $dataEncontrada = $rowAny['data'];
                }
            }
        } catch (Exception $ex) {
            $status = 'erro';
        }

        die(json_encode(array('success' => true, 'status' => $status, 'data' => $dataEncontrada)));
    }

    // Handler AJAX excluir
    if (isset($_POST['excluir_lote_ajax'])) {
        $lote = trim($_POST['lote']);
        $regional = trim($_POST['regional']);
        $posto = trim($_POST['posto']);
        
        $sql = "DELETE FROM conferencia_pacotes WHERE nlote = ? AND regional = ? AND nposto = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($lote, $regional, $posto));
        $stmt = null; // v8.17.4: Libera statement
        $pdo = null;  // v8.17.4: Fecha conexão
        die(json_encode(array('success' => true)));
    }

    // v9.24.0: Salvar posto bloqueado
    if (isset($_POST['salvar_posto_bloqueado'])) {
        $posto = trim($_POST['posto']);
        $motivo = isset($_POST['motivo']) ? trim($_POST['motivo']) : '';
        $responsavel = isset($_POST['responsavel']) ? trim($_POST['responsavel']) : '';
        if ($posto === '') {
            die(json_encode(array('success' => false, 'erro' => 'Posto obrigatorio')));
        }
        if ($responsavel === '') {
            die(json_encode(array('success' => false, 'erro' => 'Responsavel obrigatorio')));
        }
        if ($motivo === '') {
            die(json_encode(array('success' => false, 'erro' => 'Motivo obrigatorio')));
        }
        $stmt = $pdo->prepare("SELECT id FROM ciPostosBloqueados WHERE posto = ?");
        $stmt->execute(array($posto));
        if ($stmt->fetch()) {
            $stmt = $pdo->prepare("UPDATE ciPostosBloqueados SET nome = ?, motivo = ?, ativo = 1, atualizado = NOW() WHERE posto = ?");
            $stmt->execute(array($motivo, $motivo, $posto));
        } else {
            $stmt = $pdo->prepare("INSERT INTO ciPostosBloqueados (posto, nome, motivo, ativo, criado) VALUES (?, ?, ?, 1, NOW())");
            $stmt->execute(array($posto, $motivo, $motivo));
        }
        $stmtHist = $pdo->prepare("INSERT INTO ciPostosBloqueadosHistorico (posto, acao, motivo, responsavel, criado) VALUES (?, 'BLOQUEIO', ?, ?, NOW())");
        $stmtHist->execute(array($posto, $motivo, $responsavel));
        $stmt = null;
        $pdo = null;
        die(json_encode(array('success' => true)));
    }

    // v9.24.0: Excluir posto bloqueado
    if (isset($_POST['excluir_posto_bloqueado'])) {
        $posto = trim($_POST['posto']);
        $responsavel = isset($_POST['responsavel']) ? trim($_POST['responsavel']) : '';
        $motivo = isset($_POST['motivo']) ? trim($_POST['motivo']) : '';
        if ($posto === '') {
            die(json_encode(array('success' => false, 'erro' => 'Posto obrigatorio')));
        }
        if ($responsavel === '') {
            die(json_encode(array('success' => false, 'erro' => 'Responsavel obrigatorio')));
        }
        $stmt = $pdo->prepare("DELETE FROM ciPostosBloqueados WHERE posto = ?");
        $stmt->execute(array($posto));
        $stmtHist = $pdo->prepare("INSERT INTO ciPostosBloqueadosHistorico (posto, acao, motivo, responsavel, criado) VALUES (?, 'DESBLOQUEIO', ?, ?, NOW())");
        $stmtHist->execute(array($posto, $motivo, $responsavel));
        $stmt = null;
        $pdo = null;
        die(json_encode(array('success' => true)));
    }

    // v9.0: Busca REGIONAL e ENTREGA de ciRegionais (fonte da verdade)
    $postosInfo = array(); // posto => array('regional' => X, 'entrega' => 'poupatempo'/'correios'/null)
    $sql = "SELECT LPAD(posto,3,'0') AS posto, 
                   CAST(regional AS UNSIGNED) AS regional,
                   LOWER(TRIM(REPLACE(entrega,' ',''))) AS entrega
            FROM ciRegionais 
            LIMIT 1000";
    $stmt = $pdo->query($sql);
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $posto_pad = $r['posto'];
        $regional_real = (int)$r['regional'];
        $entrega_tipo = null;
        
        if (!empty($r['entrega'])) {
            $entrega_limpo = $r['entrega'];
            if (strpos($entrega_limpo, 'poupa') !== false || strpos($entrega_limpo, 'tempo') !== false) {
                $entrega_tipo = 'poupatempo';
            } elseif (strpos($entrega_limpo, 'correio') !== false) {
                $entrega_tipo = 'correios';
            }
        }
        
        $postosInfo[$posto_pad] = array(
            'regional' => $regional_real,
            'entrega' => $entrega_tipo
        );
    }

    // Busca conferências já realizadas (sem LIMIT)
    $stmt = $pdo->query("SELECT nlote, regional, nposto, codbar, conferido_em, dataexp FROM conferencia_pacotes WHERE conf='s'");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $nlote_raw = trim((string)$row['nlote']);
        $regional_raw = trim((string)$row['regional']);
        $posto_raw = trim((string)$row['nposto']);
        $conferido_em = isset($row['conferido_em']) ? trim((string)$row['conferido_em']) : '';
        $dataexp_row = isset($row['dataexp']) ? trim((string)$row['dataexp']) : '';
        if ($conferido_em === '' && $dataexp_row !== '') {
            $conferido_em = $dataexp_row . ' 00:00:00';
        }

        $nlote_pad = str_pad($nlote_raw, 8, '0', STR_PAD_LEFT);
        $regional_pad = str_pad($regional_raw, 3, '0', STR_PAD_LEFT);
        $posto_pad = str_pad($posto_raw, 3, '0', STR_PAD_LEFT);

        $keys = array();
        $keys[] = $nlote_pad . '|' . $regional_pad . '|' . $posto_pad;
        $keys[] = $nlote_raw . '|' . $regional_pad . '|' . $posto_pad;
        $keys[] = $nlote_pad . '|' . $posto_pad;
        $keys[] = $nlote_raw . '|' . $posto_pad;

        if (!empty($row['codbar'])) {
            $cb = preg_replace('/\D+/', '', (string)$row['codbar']);
            if (strlen($cb) >= 14) {
                $lote_cb = substr($cb, 0, 8);
                $reg_cb = substr($cb, 8, 3);
                $pst_cb = substr($cb, 11, 3);
                $lote_cb_pad = str_pad($lote_cb, 8, '0', STR_PAD_LEFT);
                $reg_cb_pad = str_pad($reg_cb, 3, '0', STR_PAD_LEFT);
                $pst_cb_pad = str_pad($pst_cb, 3, '0', STR_PAD_LEFT);
                $keys[] = $lote_cb_pad . '|' . $reg_cb_pad . '|' . $pst_cb_pad;
                $keys[] = $lote_cb_pad . '|' . $pst_cb_pad;
            }
        }

        foreach ($keys as $k) {
            $conferencias[$k] = 1;
            if ($conferido_em !== '') {
                $conferencias_info[$k] = $conferido_em;
            }
        }

        // v0.9.25.1: remove conferencias_lote para evitar marcar lote inteiro como conferido
    }

    // v9.22.8: Normalizar datas (intervalo + avulsas)
    function normalizarDataSql($d) {
        $d = trim($d);
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

    function normalizarDataExib($d) {
        $d = trim($d);
        if ($d === '') return '';
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $d, $m)) {
            return $m[3] . '-' . $m[2] . '-' . $m[1];
        }
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $d, $m)) {
            return $m[1] . '-' . $m[2] . '-' . $m[3];
        }
        if (preg_match('/^(\d{2})\-(\d{2})\-(\d{4})$/', $d)) {
            return $d;
        }
        return '';
    }

    $data_ini_sql = normalizarDataSql($data_ini);
    $data_fim_sql = normalizarDataSql($data_fim);

    if ($data_ini_sql !== '' && $data_fim_sql === '') {
        $data_fim_sql = $data_ini_sql;
    }

    if ($data_ini_sql === '' && $data_fim_sql !== '') {
        $data_ini_sql = $data_fim_sql;
    }

    if (!empty($datas_avulsas)) {
        $partes = preg_split('/[\s,;]+/', $datas_avulsas);
        foreach ($partes as $p) {
            $ds = normalizarDataSql($p);
            if ($ds !== '') {
                $datas_sql[] = $ds;
            }
        }
    }

    // v9.25.8: Se nenhum filtro, usa a data atual
    if ($data_ini_sql === '' && $data_fim_sql === '' && empty($datas_sql)) {
        $hoje = date('Y-m-d');
        $data_ini_sql = $hoje;
        $data_fim_sql = $hoje;
        if ($data_ini === '') {
            $data_ini = $hoje;
        }
        if ($data_fim === '') {
            $data_fim = $hoje;
        }
    }

    // Busca últimas 5 datas para seletor
    $stmt = $pdo->query("SELECT DISTINCT DATE_FORMAT(dataCarga, '%d-%m-%Y') as data 
                         FROM ciPostosCsv 
                         WHERE dataCarga IS NOT NULL 
                         ORDER BY dataCarga DESC 
                         LIMIT 5");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $datas_expedicao[] = $row['data'];
    }

    // Busca dados do ciPostosCsv (com LIMIT)
    $condicoes_data = array();
    $params_data = array();
    if ($data_ini_sql !== '' && $data_fim_sql !== '') {
        $condicoes_data[] = "DATE(dataCarga) BETWEEN ? AND ?";
        $params_data[] = $data_ini_sql;
        $params_data[] = $data_fim_sql;
    }
    if (!empty($datas_sql)) {
        $ph = implode(',', array_fill(0, count($datas_sql), '?'));
        $condicoes_data[] = "DATE(dataCarga) IN ($ph)";
        $params_data = array_merge($params_data, $datas_sql);
    }

    if (!empty($condicoes_data)) {
        $whereData = "WHERE (" . implode(' OR ', $condicoes_data) . ")";
        $sql = "SELECT lote, posto, regional, quantidade, dataCarga, usuario 
                FROM ciPostosCsv 
                $whereData
                ORDER BY regional, lote, posto 
                LIMIT 3000";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params_data);

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (empty($row['dataCarga'])) continue;

                $data_formatada = date('d-m-Y', strtotime($row['dataCarga']));
                $data_sql_row = date('Y-m-d', strtotime($row['dataCarga']));

                $lote = str_pad($row['lote'], 8, '0', STR_PAD_LEFT);
                $posto = str_pad($row['posto'], 3, '0', STR_PAD_LEFT);
                $regional_csv = (int)$row['regional']; // Regional do CSV (para código de barras)
                $regional_str = str_pad($regional_csv, 3, '0', STR_PAD_LEFT);
                $quantidade = str_pad($row['quantidade'], 5, '0', STR_PAD_LEFT);

                $codigo_barras = $lote . $regional_str . $posto . $quantidade;
                $usuario_prod = isset($row['usuario']) ? trim((string)$row['usuario']) : '';
                
                // v9.0: Usa informações CORRETAS de ciRegionais
                $regional_real = isset($postosInfo[$posto]) ? $postosInfo[$posto]['regional'] : $regional_csv;
                $tipoEntrega = isset($postosInfo[$posto]) ? $postosInfo[$posto]['entrega'] : null;
                $isPT = ($tipoEntrega == 'poupatempo') ? 1 : 0;
                
                // Verifica se já foi conferido
                $lote_pad = str_pad($lote, 8, '0', STR_PAD_LEFT);
                $posto_pad = str_pad($posto, 3, '0', STR_PAD_LEFT);
                $regional_pad_csv = str_pad($regional_str, 3, '0', STR_PAD_LEFT);

                // v9.3: Poupa Tempo usa próprio posto como regional na exibição
                $regional_exibida = ($isPT == 1) ? $posto : $regional_str;
                $regional_pad_exib = str_pad($regional_exibida, 3, '0', STR_PAD_LEFT);
                $regional_grupo = str_pad((string)$regional_real, 3, '0', STR_PAD_LEFT);
                $regional_label = $regional_exibida;
                if ($isPT != 1) {
                    if ((int)$regional_real === 0) {
                        $regional_label = 'Postos Capital';
                    } elseif ((int)$regional_real === 999) {
                        $regional_label = 'Postos Central';
                    } elseif ((int)$regional_real === 1) {
                        $regional_label = 'Posto 01';
                    }
                }

                $keysToTry = array(
                    $lote_pad . '|' . $regional_pad_exib . '|' . $posto_pad,
                    $lote . '|' . $regional_pad_exib . '|' . $posto_pad,
                    $lote_pad . '|' . $regional_pad_csv . '|' . $posto_pad,
                    $lote . '|' . $regional_pad_csv . '|' . $posto_pad,
                    $lote_pad . '|' . $posto_pad,
                    $lote . '|' . $posto_pad
                );

                $conferido = 0;
                $conferido_em = '';
                foreach ($keysToTry as $kTry) {
                    if (isset($conferencias[$kTry])) {
                        $conferido = 1;
                        if (isset($conferencias_info[$kTry])) {
                            $conferido_em = $conferencias_info[$kTry];
                        }
                        break;
                    }
                }
                // v0.9.25.1: nao marcar por lote inteiro, apenas por chave exata

                // v9.0: Agrupa por REGIONAL REAL (de ciRegionais)
                if (!isset($regionais_data[$regional_real])) {
                    $regionais_data[$regional_real] = array();
                }


                $regionais_data[$regional_real][] = array(
                    'lote' => $lote,
                    'posto' => $posto,
                    'regional' => $regional_exibida,
                    'regional_grupo' => $regional_grupo,
                    'regional_label' => $regional_label,
                    'tipoEntrega' => $tipoEntrega,
                    'data' => $data_formatada,
                    'data_sql' => $data_sql_row,
                    'qtd' => ltrim($quantidade, '0'),
                    'codigo' => $codigo_barras,
                    'usuario_prod' => $usuario_prod,
                    'conferido_em' => $conferido_em,
                    'isPT' => $isPT,
                    'conf' => $conferido
                );

            $total_codigos++;
        }
    }

    sort($datas_expedicao);

    // v9.22.8: Montar datas exibidas para filtros/estatísticas
    if ($data_ini_sql !== '' && $data_fim_sql !== '') {
        try {
            $dtIni = new DateTime($data_ini_sql);
            $dtFim = new DateTime($data_fim_sql);
            while ($dtIni <= $dtFim) {
                $datas_exib[] = $dtIni->format('d-m-Y');
                $dtIni->modify('+1 day');
            }
        } catch (Exception $e) {}
    }
    if (!empty($datas_sql)) {
        foreach ($datas_sql as $ds) {
            $datas_exib[] = normalizarDataExib($ds);
        }
    }
    $datas_exib = array_values(array_unique(array_filter($datas_exib)));

    // v9.22.8: Estatísticas
    $stats = array(
        'carteiras_emitidas' => 0,
        'carteiras_conferidas' => 0,
        'postos_conferidos' => 0,
        'pacotes_conferidos' => 0
    );

    $estante_stats = array(
        'total' => 0,
        'capital' => 0,
        'central' => 0,
        'regional' => 0,
        'poupatempo' => 0
    );
    $estante_lotes_sem_upload = array();
    $estante_sem_upload_por_posto = array();

    $periodo_operacao_label = 'Periodo nao informado';
    if ($data_ini !== '' && $data_fim !== '') {
        $periodo_operacao_label = normalizarDataExib($data_ini) . ' a ' . normalizarDataExib($data_fim);
    } elseif (!empty($datas_exib)) {
        if (count($datas_exib) === 1) {
            $periodo_operacao_label = $datas_exib[0];
        } else {
            $periodo_operacao_label = $datas_exib[0] . ' a ' . $datas_exib[count($datas_exib) - 1];
        }
    }

    // v9.23.0: Status de conferências (últimos 30 dias)
    try {
        $stmt_conferidos = $pdo->query("
            SELECT DISTINCT 
                DATE(dataCarga) as data,
                DAYOFWEEK(dataCarga) as dia_semana
            FROM ciPostosCsv 
            WHERE dataCarga >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ORDER BY data DESC
            LIMIT 15
        ");
        $dias_com_producao = array();
        while ($row = $stmt_conferidos->fetch(PDO::FETCH_ASSOC)) {
            $data_fmt = date('d-m-Y', strtotime($row['data']));
            $dias_com_producao[] = $data_fmt;

            $dia_num = (int)$row['dia_semana'];
            $labels = array(
                1 => 'DOM',
                2 => 'SEG',
                3 => 'TER',
                4 => 'QUA',
                5 => 'QUI',
                6 => 'SEX',
                7 => 'SAB'
            );
            $label = isset($labels[$dia_num]) ? $labels[$dia_num] : '';

            $metadados_dias[$data_fmt] = array(
                'dia_semana_num' => $dia_num,
                'label' => $label
            );
        }

        try {
            $stmt_conf = $pdo->query("
                SELECT DISTINCT DATE(csv.dataCarga) as data
                FROM ciPostosCsv csv
                INNER JOIN conferencia_pacotes cp ON csv.lote = cp.nlote
                WHERE csv.dataCarga >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                  AND cp.conf = 's'
                ORDER BY data DESC
            ");
            while ($row = $stmt_conf->fetch(PDO::FETCH_ASSOC)) {
                $dias_com_conferencia[] = date('d-m-Y', strtotime($row['data']));
            }
        } catch (Exception $e) {
            $dias_com_conferencia = array();
        }

        $dias_sem_conferencia = array_diff($dias_com_producao, $dias_com_conferencia);
        $dias_sem_conferencia = array_values($dias_sem_conferencia);
        $dias_sem_conferencia = array_slice($dias_sem_conferencia, 0, 10);
    } catch (Exception $e) {
        // ignore
    }

    if (!empty($condicoes_data)) {
        $sqlEmitidas = "SELECT COALESCE(SUM(quantidade),0) AS total FROM ciPostosCsv $whereData";
        $stmtEmit = $pdo->prepare($sqlEmitidas);
        $stmtEmit->execute($params_data);
        $stats['carteiras_emitidas'] = (int)$stmtEmit->fetchColumn();
    }

    if (!empty($datas_exib)) {
        $phEx = implode(',', array_fill(0, count($datas_exib), '?'));
        $sqlConf = "SELECT 
                        COALESCE(SUM(qtd),0) AS total_qtd,
                        COUNT(*) AS total_pacotes,
                        COUNT(DISTINCT nposto) AS total_postos
                    FROM conferencia_pacotes
                    WHERE conf='s' AND dataexp IN ($phEx)";
        $stmtConf = $pdo->prepare($sqlConf);
        $stmtConf->execute($datas_exib);
        $rowConf = $stmtConf->fetch(PDO::FETCH_ASSOC);
        if ($rowConf) {
            $stats['carteiras_conferidas'] = (int)$rowConf['total_qtd'];
            $stats['pacotes_conferidos'] = (int)$rowConf['total_pacotes'];
            $stats['postos_conferidos'] = (int)$rowConf['total_postos'];
        }
    }

    // v9.24.8: Estatisticas da estante (leituras do encontra_posto)
    try {
        if (!empty($condicoes_data)) {
            $condicoes_estante = array();
            $params_estante = array();
            if ($data_ini_sql !== '' && $data_fim_sql !== '') {
                $condicoes_estante[] = "producao_de BETWEEN ? AND ?";
                $params_estante[] = $data_ini_sql;
                $params_estante[] = $data_fim_sql;
            }
            if (!empty($datas_sql)) {
                $phEst = implode(',', array_fill(0, count($datas_sql), '?'));
                $condicoes_estante[] = "producao_de IN ($phEst)";
                $params_estante = array_merge($params_estante, $datas_sql);
            }

            if (!empty($condicoes_estante)) {
                $whereEstante = "WHERE (" . implode(' OR ', $condicoes_estante) . ")";

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
                if ($data_ini_sql !== '' && $data_fim_sql !== '') {
                    $joinCarga = 'AND DATE(c.dataCarga) BETWEEN ? AND ?';
                    $params_upload[] = $data_ini_sql;
                    $params_upload[] = $data_fim_sql;
                } elseif (!empty($datas_sql)) {
                    $phUpload = implode(',', array_fill(0, count($datas_sql), '?'));
                    $joinCarga = "AND DATE(c.dataCarga) IN ($phUpload)";
                    $params_upload = $datas_sql;
                }
                $stmtSem = $pdo->prepare("SELECT DISTINCT LPAD(l.lote,8,'0') AS lote, LPAD(l.posto,3,'0') AS posto, LPAD(l.regional,3,'0') AS regional
                    FROM lotes_na_estante l
                    $whereEstante
                    AND NOT EXISTS (
                        SELECT 1 FROM ciPostosCsv c
                        WHERE c.lote = l.lote $joinCarga
                    )
                    ORDER BY l.lote");
                $stmtSem->execute(array_merge($params_estante, $params_upload));
                while ($row = $stmtSem->fetch(PDO::FETCH_ASSOC)) {
                    $estante_lotes_sem_upload[] = $row['lote'];
                    $posto_sem_upload = isset($row['posto']) ? $row['posto'] : '';
                    if ($posto_sem_upload !== '') {
                        if (!isset($estante_sem_upload_por_posto[$posto_sem_upload])) {
                            $estante_sem_upload_por_posto[$posto_sem_upload] = 0;
                        }
                        $estante_sem_upload_por_posto[$posto_sem_upload]++;
                    }
                }
            }
        }
    } catch (Exception $e) {
        $estante_lotes_sem_upload = array();
        $estante_sem_upload_por_posto = array();
    }

    // v9.24.0: Carregar postos bloqueados
    $postos_bloqueados = array();
    try {
        $stmtBloq = $pdo->query("SELECT posto, nome, motivo FROM ciPostosBloqueados WHERE ativo = 1 ORDER BY posto ASC");
        while ($row = $stmtBloq->fetch(PDO::FETCH_ASSOC)) {
            $postos_bloqueados[] = array(
                'posto' => $row['posto'],
                'nome' => $row['nome'],
                'motivo' => $row['motivo']
            );
        }
    } catch (Exception $e) {
        $postos_bloqueados = array();
    }

    $grupo_pt = array();
    $grupo_r01 = array();
    $grupo_capital = array();
    $grupo_999 = array();
    $grupo_outros = array();

    foreach ($regionais_data as $regional => $postos) {
        foreach ($postos as $posto) {
            if ($posto['tipoEntrega'] == 'poupatempo') {
                $postoKey = $posto['posto'];
                if (!isset($grupo_pt[$postoKey])) {
                    $grupo_pt[$postoKey] = array();
                }
                $grupo_pt[$postoKey][] = $posto;
                continue;
            }
            if ($regional == 1) {
                $grupo_r01[] = $posto;
                continue;
            }
            if ($regional == 0) {
                $grupo_capital[] = $posto;
                continue;
            }
            if ($regional == 999) {
                $grupo_999[] = $posto;
                continue;
            }
            if (!isset($grupo_outros[$regional])) {
                $grupo_outros[$regional] = array();
            }
            $grupo_outros[$regional][] = $posto;
        }
    }
    ksort($grupo_outros);

} catch (PDOException $e) {
    echo "Erro ao conectar ao banco de dados: " . $e->getMessage();
    die();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conferência de Pacotes v0.9.25.8</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: "Trebuchet MS", "Segoe UI", Arial, sans-serif; padding: 20px; padding-top: 90px; background: #f5f5f5; }
        h2 { color: #333; border-bottom: 3px solid #007bff; padding-bottom: 10px; margin-bottom: 20px; }
        h3 { 
            color: #555; 
            margin: 30px 0 10px; 
            padding-left: 10px; 
            border-left: 4px solid #007bff; 
        }

        .btn-voltar {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 6px;
            text-decoration: none;
            background: #1f2b6d;
            color: #fff;
            font-weight: 600;
            font-size: 12px;
        }
        .btn-voltar:hover { background: #162057; }

        .grupo-capital-wrapper,
        .grupo-central-wrapper {
            background: #ffffff;
            border: 2px solid #cfd8dc;
            border-radius: 10px;
            padding: 12px;
            margin: 10px 0 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .grupo-capital-titulo,
        .grupo-central-titulo {
            font-weight: 700;
            color: #37474f;
            margin-bottom: 8px;
        }
        .subgrupo-posto {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 10px;
            margin: 8px 0;
            background: #fafafa;
        }

        th.sortable { cursor: pointer; user-select: none; }
        th.sortable .sort-indicator { margin-left: 6px; font-size: 11px; opacity: 0.7; }
        
        .radio-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 12px 16px;
            margin-bottom: 12px;
            border-radius: 6px;
        }
        .barras-topo {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 10px;
            margin-bottom: 10px;
        }
        .radio-box label {
            color: white;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
        }
        .radio-box input[type="radio"] { margin-right: 10px; width: 18px; height: 18px; cursor: pointer; }
        .radio-box input[type="text"] {
            width: 260px;
            max-width: 100%;
            padding: 8px 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
            background: #fff;
            color: #333;
        }
        .modo-consulta .linha-conferencia:not(.confirmado) { display: none; }
        .modo-consulta #codigo_barras,
        .modo-consulta #resetar {
            display: none;
        }
        #modoConsultaBadge {
            display: none;
            margin-top: 6px;
            padding: 4px 10px;
            border-radius: 12px;
            background: #ffd54f;
            color: #4a3b00;
            font-size: 11px;
            font-weight: 700;
        }
        .modo-consulta #modoConsultaBadge { display: inline-flex; }
        #btnAtivarConferencia {
            display: none;
            margin-top: 6px;
            padding: 6px 10px;
            border-radius: 6px;
            border: none;
            background: #1f2b6d;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
        }
        .modo-consulta #btnAtivarConferencia { display: inline-flex; }
        
        .filtro-datas { 
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .filtro-datas form { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
        .filtro-datas label { margin-right: 10px; cursor: pointer; }
        .filtro-row { display:flex; flex-wrap:wrap; gap:10px; align-items:center; width:100%; }
        .filtro-datas input[type="date"],
        .filtro-datas input[type="text"] {
            padding: 8px 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            min-width: 180px;
            background: #fff;
        }
        .filtro-datas input[type="submit"] { padding: 8px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .filtro-datas input[type="submit"]:hover { background: #0056b3; }
        
        .info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 6px;
            font-weight: 600;
        }
        
        #codigo_barras { 
            padding: 12px; 
            font-size: 16px; 
            width: 100%;
            max-width: 400px;
            border: 2px solid #007bff; 
            border-radius: 4px;
            margin: 10px 0;
        }
        
        #resetar {
            padding: 10px 20px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            margin-left: 10px;
        }
        #resetar:hover { background-color: #c82333; }
        
        table { 
            border-collapse: collapse; 
            width: 100%; 
            margin-top: 15px;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        thead { background: #343a40; color: white; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        tbody tr { cursor: pointer; transition: background 0.2s; }
        tbody tr:hover { background: #f8f9fa; }
        tbody tr.confirmado { background-color: #d4edda !important; font-weight: 500; }
        tbody tr.ultimo-lido { animation: pulse 1.2s ease-in-out infinite; }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(0,123,255,0.6); }
            70% { box-shadow: 0 0 0 10px rgba(0,123,255,0); }
            100% { box-shadow: 0 0 0 0 rgba(0,123,255,0); }
        }
        @keyframes pulseChipAtivo {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255,230,109,0.42); }
            50% { transform: scale(1.03); box-shadow: 0 0 0 8px rgba(255,230,109,0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255,230,109,0); }
        }
        
        .tag-pt {
            background: #e74c3c;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 700;
            margin-left: 8px;
        }
        .topo-status {
            position: fixed;
            top: 10px;
            left: 10px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            z-index: 1200;
        }
        .versao {
            background: #28a745;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        .cards-resumo {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 12px;
            margin: 15px 0 10px;
        }
        .card-resumo {
            background: #fff;
            border-radius: 8px;
            padding: 14px 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-left: 4px solid #007bff;
        }
        .card-resumo h4 { margin: 0; font-size: 12px; color: #555; text-transform: uppercase; }
        .card-resumo .valor { font-size: 20px; font-weight: 700; color: #007bff; margin-top: 6px; }

        .painel-estante {
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px;
            margin-top: 12px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
        }
        .painel-estante h4 { margin: 0 0 8px; color: #333; font-size: 13px; }
        .painel-estante .breakdown { font-size: 11px; color: #666; margin-bottom: 8px; }
        .lista-lotes { display: flex; flex-wrap: wrap; gap: 6px; }
        .lote-badge {
            background: #263238;
            color: #fff;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
        }
        .painel-leitura {
            background: #fff;
            border-radius: 12px;
            padding: 16px;
            margin: 16px 0 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.08);
            border: 1px solid #dde6f1;
        }
        .painel-leitura-topo {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
        }
        .painel-leitura-acoes {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }
        .modos-visualizacao {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .secao-visualizacao {
            display: block;
            margin-top: 10px;
        }
        .secao-visualizacao.oculta {
            display: none;
        }
        .painel-operacao {
            background: linear-gradient(180deg, #0b2d4d 0%, #071a2d 100%);
            border-radius: 14px;
            padding: 14px;
            margin: 14px 0 12px;
            color: #fff;
            box-shadow: 0 10px 24px rgba(0,0,0,0.18);
            overflow: hidden;
        }
        .painel-operacao-topo {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 12px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }
        .painel-operacao .operacao-tag {
            display: inline-block;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #86d8ff;
            margin-bottom: 4px;
        }
        .painel-operacao .operacao-titulo {
            font-size: 26px;
            font-weight: 900;
            letter-spacing: 0.5px;
            color: #f9fbff;
        }
        .painel-operacao .operacao-periodo {
            font-size: 12px;
            color: #c9def4;
            font-weight: 600;
        }
        .operacao-grade {
            display: grid;
            gap: 10px;
        }
        .operacao-grupo {
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 12px;
            background: rgba(255,255,255,0.04);
            padding: 10px;
        }
        .operacao-grupo.tipo-pt { border-left: 5px solid #8de96b; }
        .operacao-grupo.tipo-r01 { border-left: 5px solid #ffd54f; }
        .operacao-grupo.tipo-capital { border-left: 5px solid #53c2ff; }
        .operacao-grupo.tipo-central { border-left: 5px solid #ff8a65; }
        .operacao-grupo.tipo-regional { border-left: 5px solid #b39ddb; }
        .operacao-grupo-titulo,
        .operacao-grade-header,
        .operacao-posto-row {
            display: grid;
            grid-template-columns: 72px minmax(220px, 1.1fr) minmax(240px, 2fr) 72px 72px 112px;
            gap: 10px;
            align-items: center;
        }
        .operacao-grupo-titulo {
            padding: 6px 0 12px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            margin-bottom: 6px;
        }
        .operacao-grupo-info {
            grid-column: 1 / span 3;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .operacao-grupo-nome {
            font-size: 24px;
            font-weight: 900;
            color: #f8fbff;
            line-height: 1.1;
        }
        .operacao-grupo-resumo {
            font-size: 12px;
            color: #a7cae7;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.9px;
        }
        .operacao-grade-header {
            font-size: 11px;
            font-weight: 800;
            color: #b7d8f8;
            text-transform: uppercase;
            padding: 0 0 6px;
        }
        .operacao-posto-row {
            background: linear-gradient(90deg, rgba(0,198,255,0.18) 0%, rgba(6,16,28,0.3) 52%, rgba(0,198,255,0.12) 100%);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 12px;
            padding: 8px 10px;
            margin-top: 8px;
            scroll-margin-top: 110px;
        }
        .operacao-posto-row.ativo {
            border-color: #ffe66d;
            box-shadow: 0 0 0 2px rgba(255,230,109,0.18);
            animation: pulse 1.8s ease-in-out infinite;
        }
        .operacao-posicao {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            min-width: 54px;
            height: 38px;
            border-radius: 10px;
            background: linear-gradient(180deg, #21ff8b 0%, #11c55d 100%);
            color: #03220f;
            font-size: 20px;
            font-weight: 900;
            box-shadow: inset 0 -2px 0 rgba(0,0,0,0.18);
        }
        .operacao-posto-meta { min-width: 0; }
        .operacao-posto-nome { font-size: 18px; font-weight: 900; color: #ffffff; }
        .operacao-posto-sub { font-size: 11px; color: #b9cde0; margin-top: 2px; }
        .operacao-posto-aux { font-size: 10px; color: #ffe39a; margin-top: 3px; font-weight: 700; }
        .operacao-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            min-height: 34px;
        }
        .operacao-chip {
            border: 1px solid rgba(255,255,255,0.12);
            background: linear-gradient(180deg, #1f2937 0%, #111827 100%);
            color: #f4f7fb;
            border-radius: 999px;
            padding: 6px 10px;
            font-size: 11px;
            font-weight: 800;
            cursor: pointer;
            transition: transform .15s ease, box-shadow .15s ease, background .15s ease;
            white-space: nowrap;
        }
        .operacao-chip:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.16);
        }
        .operacao-chip.confirmado {
            background: linear-gradient(180deg, #2aff75 0%, #18b958 100%);
            color: #03220f;
            border-color: rgba(255,255,255,0.22);
        }
        .operacao-chip.ativo {
            outline: 2px solid #ffe66d;
            outline-offset: 1px;
            animation: pulseChipAtivo 1.8s ease-in-out infinite;
        }
        .operacao-chip.sem-upload {
            border-style: dashed;
        }
        .operacao-numero {
            text-align: center;
            font-size: 22px;
            font-weight: 900;
            color: #ffffff;
        }
        .operacao-numero-label {
            display: block;
            font-size: 9px;
            color: #b9cde0;
            margin-top: 1px;
            letter-spacing: 0.7px;
        }
        .operacao-pendentes {
            text-align: center;
            font-size: 22px;
            font-weight: 900;
            color: #ffe39a;
        }
        .operacao-pendentes .operacao-numero-label { color: #f4d68b; }
        .modal-chip {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.55);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2300;
        }
        .modal-chip .card {
            background: #fff;
            width: 560px;
            max-width: 94%;
            border-radius: 12px;
            padding: 18px;
            box-shadow: 0 12px 24px rgba(0,0,0,0.28);
        }
        .modal-chip h3 { margin: 0 0 10px; color: #16324f; }
        .modal-chip table { margin-top: 8px; }
        .modal-chip .acoes { margin-top: 12px; text-align: right; }
        .modal-chip .acoes button {
            padding: 8px 14px;
            border: none;
            border-radius: 6px;
            background: #0d6efd;
            color: #fff;
            font-weight: 700;
            cursor: pointer;
        }
        .painel-ultimas {
            background: #fff;
            border-radius: 8px;
            padding: 12px 16px;
            margin-top: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .painel-ultimas ul { margin: 8px 0 0; padding-left: 18px; }
        .btn-toggle {
            padding: 10px 16px;
            background: #1f5f8b;
            color: #fff;
            border: none;
            border-radius: 999px;
            cursor: pointer;
            font-weight: 700;
            margin-top: 6px;
            transition: background .15s ease, transform .15s ease;
        }
        .btn-toggle:hover { transform: translateY(-1px); }
        .btn-toggle.ativo { background: #0d2f4f; box-shadow: inset 0 0 0 2px rgba(255,255,255,0.16); }
        #indicador-dias {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 10px 12px;
            width: 280px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        #indicador-dias.collapsed .indicador-conteudo { display: none; }
        .indicador-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            cursor: pointer;
            font-weight: 700;
            color: #333;
            font-size: 13px;
        }
        .badge-data { display:inline-block; padding:4px 8px; border-radius:6px; font-size:11px; margin:2px 4px 2px 0; }
        .badge-data.conferida { background:#28a745; color:#fff; }
        .badge-data.pendente { background:#ffc107; color:#333; font-weight:bold; }
        .badge-dia { display:inline-flex; align-items:center; justify-content:center; min-width:28px; height:16px; margin-left:6px; background:#343a40; color:#fff; font-size:9px; border-radius:3px; padding:0 4px; }
        .indicador-toggle { font-size: 14px; color: #666; }
        .usuario-badge {
            display:inline-block;
            padding:6px 10px;
            background:#28a745;
            color:#fff;
            border-radius:14px;
            font-weight:700;
            font-size:12px;
        }
        .switch {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
            margin-right: 8px;
        }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .2s;
            border-radius: 999px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .2s;
            border-radius: 50%;
        }
        .switch input:checked + .slider { background-color: #dc3545; }
        .switch input:checked + .slider:before { transform: translateX(20px); }
        .banner-grupo {
            margin: 16px 0 8px;
            padding: 12px 16px;
            border-radius: 10px;
            text-align: center;
            color: #fff;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            box-shadow: 0 6px 16px rgba(0,0,0,0.15);
        }
        .banner-correios { background: linear-gradient(135deg, #2f80ed 0%, #56ccf2 100%); }
        .banner-pt { background: linear-gradient(135deg, #00b09b 0%, #96c93d 100%); }
        .mensagem-leitura { margin: 6px 0 0; font-size: 12px; color: #555; }
        .mensagem-leitura strong { color: #dc3545; }
        .page-locked {
            pointer-events: none;
            filter: blur(1px);
        }
        .overlay-usuario,
        .overlay-tipo {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.55);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2000;
        }
        .overlay-usuario .card,
        .overlay-tipo .card {
            background:#fff;
            padding:20px 24px;
            border-radius:8px;
            width: 360px;
            max-width: 90%;
            box-shadow: 0 4px 16px rgba(0,0,0,0.25);
        }
        .overlay-usuario .card h3,
        .overlay-tipo .card h3 { margin:0 0 10px 0; }
        .overlay-usuario input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-top: 6px;
        }
        .overlay-usuario button,
        .overlay-tipo button {
            margin-top: 12px;
            padding: 10px 14px;
            background:#007bff;
            color:#fff;
            border:none;
            border-radius:4px;
            cursor:pointer;
            width:100%;
            font-weight:700;
        }
        .overlay-usuario .btn-opcao-sec {
            background:#f3f3f3;
            color:#333;
            border:1px solid #bbb;
        }
        .overlay-tipo .btn-opcao { background:#28a745; }
        .overlay-tipo .btn-opcao.pt { background:#17a2b8; }
        .painel-pacotes-novos {
            background:#fff;
            border-radius:8px;
            padding:12px 16px;
            margin:15px 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .painel-pacotes-novos table { margin-top: 8px; }
        .btn-acao {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
        }
        .btn-salvar { background:#28a745; color:#fff; }
        .btn-cancelar { background:#dc3545; color:#fff; }
        .modal-pacote {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.55);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2100;
        }
        .modal-pacote .card {
            background:#fff;
            padding:18px;
            border-radius:8px;
            width: 380px;
            max-width: 92%;
        }
        .modal-pacote label { display:block; margin-top:8px; font-size:12px; color:#555; }
        .modal-pacote input { width:100%; padding:8px; border:1px solid #ccc; border-radius:4px; }
        .btn-secundario { background:#6c757d; color:#fff; }
        .overlay-confirmacao {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.55);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2200;
        }
        .overlay-confirmacao .card {
            background:#fff;
            padding:18px 20px;
            border-radius:10px;
            width: 360px;
            max-width: 92%;
            text-align: center;
            box-shadow: 0 4px 16px rgba(0,0,0,0.25);
        }
        .overlay-confirmacao h3 { margin:0 0 8px 0; color:#28a745; }
        .overlay-confirmacao p { margin:0 0 12px 0; font-size:13px; color:#333; }
        .overlay-confirmacao button {
            padding: 8px 14px;
            background:#28a745;
            color:#fff;
            border:none;
            border-radius:4px;
            cursor:pointer;
            font-weight:700;
        }
        @media (max-width: 768px) {
            body { padding: 12px; padding-top: 20px; }
            .topo-status { position: static; flex-direction: column; align-items: stretch; }
            #indicador-dias { width: 100%; }
            .barras-topo { grid-template-columns: 1fr; }
            .radio-box { padding: 10px 12px; }
            #codigo_barras { max-width: 100%; font-size: 18px; }
            table { display: block; overflow-x: auto; white-space: nowrap; }
            th, td { font-size: 12px; padding: 8px; }
            h2 { font-size: 18px; }
            .cards-resumo { grid-template-columns: 1fr 1fr; }
            .operacao-grupo-titulo,
            .operacao-grade-header,
            .operacao-posto-row {
                grid-template-columns: 58px minmax(140px, 1fr);
            }
            .operacao-grupo-info {
                grid-column: 1 / -1;
            }
            .operacao-grade-header .hide-mobile,
            .operacao-posto-row .operacao-chips,
            .operacao-posto-row .operacao-numero,
            .operacao-posto-row .operacao-pendentes,
            .operacao-grupo-titulo .operacao-numero,
            .operacao-grupo-titulo .operacao-pendentes {
                grid-column: 1 / -1;
            }
            .operacao-numero,
            .operacao-pendentes { text-align: left; }
            .operacao-posto-row.subnivel { margin-left: 0; }
            #resetar { margin-left: 0; margin-top: 8px; width: 100%; }
        }
    </style>
</head>
<body>
<div class="topo-status">
    <div class="versao">v0.9.25.8</div>
    <div id="indicador-dias" class="collapsed">
        <div class="indicador-header" onclick="toggleIndicadorDias()" title="Recolher/Expandir">
            <span>📅 Status de Conferências</span>
            <span class="indicador-toggle">▼</span>
        </div>
        <div class="indicador-conteudo">
            <div style="margin:10px 0;">
                <strong style="color:#28a745;font-size:12px;">✓ Últimas Conferências:</strong><br>
                <div style="margin-top:5px;">
                    <?php 
                    $ultimas_cinco = array_slice($dias_com_conferencia, 0, 5);
                    if (!empty($ultimas_cinco)) {
                        foreach ($ultimas_cinco as $data) {
                            $label_dia = isset($metadados_dias[$data]) ? $metadados_dias[$data]['label'] : '';
                            $badge_label = !empty($label_dia) ? " <span class='badge-dia'>$label_dia</span>" : '';
                            echo '<span class="badge-data conferida">' . htmlspecialchars($data) . $badge_label . '</span>';
                        }
                    } else {
                        echo '<span style="color:#999;font-size:11px;">Nenhuma</span>';
                    }
                    ?>
                </div>
            </div>
            <div style="margin:10px 0;">
                <strong style="color:#ffc107;font-size:12px;">⚠ Conferências Pendentes:</strong><br>
                <div style="margin-top:5px;">
                    <?php 
                    $ultimas_pendentes = array_slice($dias_sem_conferencia, 0, 5);
                    if (!empty($ultimas_pendentes)) {
                        foreach ($ultimas_pendentes as $data) {
                            $label_dia = isset($metadados_dias[$data]) ? $metadados_dias[$data]['label'] : '';
                            $badge_label = !empty($label_dia) ? " <span class='badge-dia'>$label_dia</span>" : '';
                            echo '<span class="badge-data pendente">' . htmlspecialchars($data) . $badge_label . '</span>';
                        }
                    } else {
                        echo '<span style="color:#999;font-size:11px;">Nenhuma</span>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<h2>📋 Conferência de Pacotes v0.9.25.8</h2>

<div class="overlay-usuario" id="overlayUsuario">
    <div class="card">
        <h3>👤 Informe o responsável</h3>
        <div style="font-size:12px; color:#666;">Obrigatório para iniciar a conferência.</div>
        <input type="text" id="usuario_conf_modal" placeholder="Digite o responsável" autocomplete="off">
        <button type="button" id="btnConfirmarUsuario">Confirmar</button>
        <button type="button" id="btnSomenteVisualizar" class="btn-opcao-sec">Somente visualizar</button>
    </div>
</div>

<div class="overlay-tipo" id="overlayTipo" style="display:none;">
    <div class="card">
        <h3>🎯 Tipo de conferência</h3>
        <div style="font-size:12px; color:#666;">Escolha para iniciar.</div>
        <button type="button" class="btn-opcao" data-tipo="todos">Todos</button>
        <button type="button" class="btn-opcao" data-tipo="correios">Correios</button>
        <button type="button" class="btn-opcao pt" data-tipo="poupatempo">Poupa Tempo</button>
    </div>
</div>

<div id="conteudoPagina" class="page-locked">

<!-- Barras no topo -->
<div class="barras-topo">
    <div class="radio-box">
        <a class="btn-voltar" href="inicio.php">← Inicio</a>
    </div>
    <div class="radio-box">
        <div style="color:#fff; font-weight:600; margin-bottom:8px;">👤 Responsável da conferência</div>
        <span class="usuario-badge" id="usuarioBadge">Não informado</span>
        <div id="modoConsultaBadge">Modo consulta: somente conferidos</div>
        <button type="button" id="btnAtivarConferencia">Iniciar conferência</button>
    </div>

    <div class="radio-box">
        <div style="color:#fff; font-weight:600; margin-bottom:8px;">🎯 Tipo de conferência</div>
        <label style="gap:8px; margin-right:16px;">
            <input type="radio" name="tipo_inicio" value="todos">
            Todos
        </label>
        <label style="gap:8px; margin-right:16px;">
            <input type="radio" name="tipo_inicio" value="correios" checked>
            Correios
        </label>
        <label style="gap:8px;">
            <input type="radio" name="tipo_inicio" value="poupatempo">
            Poupa Tempo
        </label>
    </div>

    <div class="radio-box">
        <div style="color:#fff; font-weight:600; margin-bottom:8px;">🔔 Beep de leitura</div>
        <label style="gap:10px;">
            <span class="switch">
                <input type="checkbox" id="muteBeep">
                <span class="slider"></span>
            </span>
            Silenciar
        </label>
    </div>
</div>
<input type="checkbox" id="autoSalvar" checked style="display:none;">

<!-- Filtro de datas -->
<div class="filtro-datas">
    <form method="get" action="">
        <strong>📅 Filtrar por intervalo:</strong>
        <div class="filtro-row">
            <input type="date" name="data_ini" value="<?php echo e($data_ini); ?>">
            <input type="date" name="data_fim" value="<?php echo e($data_fim); ?>">
            <input type="submit" value="🔍 Aplicar Filtro">
        </div>
        <label style="min-width:100%;">
            Datas avulsas (dd-mm-aaaa ou yyyy-mm-dd, separadas por vírgula):
            <input type="text" name="datas_avulsas" value="<?php echo e($datas_avulsas); ?>" style="width:100%; margin-top:4px;">
        </label>
    </form>
</div>

<div class="cards-resumo" id="cardsResumoFixos">
    <div class="card-resumo">
        <h4>Pacotes na tela</h4>
        <div class="valor"><?php echo (int)$total_codigos; ?></div>
    </div>
    <div class="card-resumo">
        <h4>Carteiras emitidas</h4>
        <div class="valor"><?php echo number_format((int)$stats['carteiras_emitidas'], 0, ',', '.'); ?></div>
    </div>
    <div class="card-resumo">
        <h4>Carteiras conferidas</h4>
        <div class="valor"><?php echo number_format((int)$stats['carteiras_conferidas'], 0, ',', '.'); ?></div>
    </div>
    <div class="card-resumo">
        <h4>Postos com retirada</h4>
        <div class="valor"><?php echo (int)$stats['postos_conferidos']; ?></div>
    </div>
    <div class="card-resumo">
        <h4>Pacotes conferidos</h4>
        <div class="valor"><?php echo (int)$stats['pacotes_conferidos']; ?></div>
    </div>
    <div class="card-resumo">
        <h4>Pacotes na estante</h4>
        <div class="valor"><?php echo (int)$estante_stats['total']; ?></div>
    </div>
    <div class="card-resumo">
        <h4>Lotes sem upload</h4>
        <div class="valor"><?php echo (int)count($estante_lotes_sem_upload); ?></div>
    </div>
</div>

<div class="painel-estante" id="painelEstanteFixo">
    <h4>🔎 Lotes na estante sem upload</h4>
    <div class="breakdown">
        Capital: <?php echo (int)$estante_stats['capital']; ?> | Central: <?php echo (int)$estante_stats['central']; ?> | Regional: <?php echo (int)$estante_stats['regional']; ?> | PT: <?php echo (int)$estante_stats['poupatempo']; ?>
    </div>
    <?php if (!empty($estante_lotes_sem_upload)) { ?>
        <div class="lista-lotes">
            <?php
            $limite = 50;
            $total_lotes = count($estante_lotes_sem_upload);
            $mostrar = array_slice($estante_lotes_sem_upload, 0, $limite);
            foreach ($mostrar as $lote) {
                echo '<span class="lote-badge">' . e($lote) . '</span>';
            }
            if ($total_lotes > $limite) {
                echo '<span class="lote-badge">+ ' . e($total_lotes - $limite) . ' outros</span>';
            }
            ?>
        </div>
    <?php } else { ?>
        <div style="font-size:12px; color:#666;">Nenhum lote pendente no filtro atual.</div>
    <?php } ?>
</div>


<div class="painel-pacotes-novos" id="painelPacotesNovos" style="display:none;">
    <strong>📥 Pacotes não listados</strong>
    <div style="margin-top:6px; font-size:12px; color:#666;" id="resumoPacotesPendentes">Pacotes aguardando carga em ciPostos e ciPostosCsv.</div>
    <div style="margin-top:8px;">
        <table>
            <thead>
                <tr>
                    <th>Lote</th>
                    <th>Regional</th>
                    <th>Posto</th>
                    <th>Qtd</th>
                    <th>Data</th>
                    <th>Responsável</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody id="listaPacotesNovos"></tbody>
        </table>
    </div>
    <div style="margin-top:10px; padding:10px; border:1px solid #d7e2f2; border-radius:8px; background:#f7fbff; display:grid; grid-template-columns:repeat(auto-fit, minmax(170px, 1fr)); gap:10px; align-items:end;">
        <div>
            <label for="autor_salvamento_pacotes" style="display:block; font-size:12px; color:#555; margin-bottom:4px;">Autor</label>
            <input type="text" id="autor_salvamento_pacotes" placeholder="Responsável pela carga">
        </div>
        <div>
            <label for="turno_salvamento_pacotes" style="display:block; font-size:12px; color:#555; margin-bottom:4px;">Turno</label>
            <select id="turno_salvamento_pacotes" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
                <option value="Madrugada">Madrugada</option>
                <option value="Manhã" selected>Manhã</option>
                <option value="Tarde">Tarde</option>
                <option value="Noite">Noite</option>
            </select>
        </div>
        <div>
            <label for="criado_salvamento_pacotes" style="display:block; font-size:12px; color:#555; margin-bottom:4px;">Criado em</label>
            <input type="datetime-local" id="criado_salvamento_pacotes">
        </div>
        <div style="display:flex; align-items:center; gap:8px; min-height:40px;">
            <input type="checkbox" id="consolidar_salvamento_pacotes">
            <label for="consolidar_salvamento_pacotes" style="margin:0; font-size:12px; color:#333;">Consolidar lançamentos por responsável</label>
        </div>
    </div>
    <div style="margin-top:10px; display:flex; gap:8px;">
        <button type="button" class="btn-acao btn-salvar" id="btnSalvarPacotes">Salvar pacotes</button>
        <button type="button" class="btn-acao btn-cancelar" id="btnCancelarPacotes">Cancelar</button>
    </div>
</div>

<div class="modal-pacote" id="modalPacote">
    <div class="card">
        <h3>📦 Pacote não encontrado</h3>
        <div style="font-size:12px; color:#666;">Informe os dados para inserir nas bases.</div>
        <label>Código de barras</label>
        <input type="text" id="pacote_codbar" readonly>
        <label>Lote</label>
        <input type="text" id="pacote_lote">
        <label>Regional</label>
        <input type="text" id="pacote_regional">
        <label>Posto</label>
        <input type="text" id="pacote_posto">
        <label>Quantidade</label>
        <input type="number" id="pacote_qtd" min="1">
        <label>Data de expedição</label>
        <input type="date" id="pacote_dataexp">
        <label>Responsável</label>
        <input type="text" id="pacote_responsavel" placeholder="Opcional">
        <input type="hidden" id="pacote_idx" value="">
        <div style="margin-top:10px; display:flex; gap:8px;">
            <button type="button" class="btn-acao btn-salvar" id="btnAdicionarPacote">Adicionar à fila</button>
            <button type="button" class="btn-acao btn-cancelar" id="btnCancelarPacote">Cancelar</button>
        </div>
    </div>
</div>

<div class="overlay-confirmacao" id="overlayConfirmacao">
    <div class="card">
        <h3>Pacotes salvos</h3>
        <p id="confirmacaoTexto">Dados salvos com sucesso!</p>
        <button type="button" id="btnConfirmacaoOk">OK</button>
    </div>
</div>

<div class="modal-chip" id="modalChipDetalhe">
    <div class="card">
        <h3>Detalhes do lote</h3>
        <table>
            <tbody id="tabelaDetalheChip"></tbody>
        </table>
        <div class="acoes">
            <button type="button" id="btnFecharModalChip">Fechar</button>
        </div>
    </div>
</div>

<div class="painel-leitura">
    <div class="painel-leitura-topo">
        <input type="text" id="codigo_barras" placeholder="Escaneie o código de barras (19 dígitos)" maxlength="19" autofocus
            oninput="if(window.processarLeituraCodigo){window.processarLeituraCodigo(this.value);}"
            onchange="if(window.processarLeituraCodigo){window.processarLeituraCodigo(this.value);}"
            onkeydown="if(event && event.keyCode===13){event.preventDefault(); if(window.processarLeituraCodigo){window.processarLeituraCodigo(this.value);} }">
        <div class="painel-leitura-acoes">
            <button id="resetar">🔄 Resetar Conferência</button>
        </div>
    </div>
    <div class="mensagem-leitura" id="mensagemLeitura"></div>
    <div class="modos-visualizacao">
        <button class="btn-toggle" type="button" id="btnMostrarClassificacao" data-target="classificacao" aria-expanded="false">🏆 Classificação por chips</button>
        <button class="btn-toggle" type="button" id="btnMostrarTradicional" data-target="tradicional" aria-expanded="false">📋 Modo tradicional por regional</button>
    </div>
</div>

<div id="secaoClassificacao" class="secao-visualizacao oculta">
<div id="painel-estatisticas" style="display:block;">
    <div class="painel-operacao" id="painelOperacao">
        <div class="painel-operacao-topo">
            <div>
                <span class="operacao-tag">Operação</span>
                <div class="operacao-titulo">Conferência Produção Período</div>
                <div class="operacao-periodo"><?php echo e($periodo_operacao_label); ?></div>
            </div>
        </div>
        <div class="operacao-grade" id="operacaoGrade">
            <?php
            if (!empty($grupo_pt)) {
                renderizarLinhasOperacao('Poupa Tempo', $grupo_pt, $estante_sem_upload_por_posto);
            }
            if (!empty($grupo_r01)) {
                renderizarLinhasOperacao('Posto 001', $grupo_r01, $estante_sem_upload_por_posto);
            }
            if (!empty($grupo_capital)) {
                renderizarLinhasOperacao('Capital', $grupo_capital, $estante_sem_upload_por_posto);
            }
            if (!empty($grupo_999)) {
                renderizarLinhasOperacao('Central IIPR', $grupo_999, $estante_sem_upload_por_posto);
            }
            if (!empty($grupo_outros)) {
                foreach ($grupo_outros as $regional => $postosGrupo) {
                    $regionalStrCard = str_pad($regional, 3, '0', STR_PAD_LEFT);
                    renderizarLinhasOperacao('Regional ' . $regionalStrCard, $postosGrupo, $estante_sem_upload_por_posto);
                }
            }
            if (empty($regionais_data)) {
                echo '<div style="font-size:12px; color:#d7e6f5;">Nenhum pacote encontrado para o período selecionado.</div>';
            }
            ?>
        </div>
    </div>
</div>

</div>

    <script>
    (function() {
        function formatarAgora() {
            var d = new Date();
            var dd = String(d.getDate()).padStart(2, '0');
            var mm = String(d.getMonth() + 1).padStart(2, '0');
            var yy = d.getFullYear();
            var hh = String(d.getHours()).padStart(2, '0');
            var mi = String(d.getMinutes()).padStart(2, '0');
            return dd + '-' + mm + '-' + yy + ' ' + hh + ':' + mi;
        }

        function bindFallback() {
            var input = document.getElementById('codigo_barras');
            if (!input || input.__fallbackBound) return;
            input.__fallbackBound = true;
            var audioDesbloqueado = false;

            function normalize(val) {
                return String(val || '').replace(/\D+/g, '');
            }

            function desbloquearAudios() {
                if (audioDesbloqueado) return;
                audioDesbloqueado = true;
                var ids = ['beep', 'concluido', 'pacotejaconferido', 'pacotedeoutraregional', 'posto_poupatempo', 'pertence_correios', 'pacote_nao_encontrado'];
                for (var i = 0; i < ids.length; i++) {
                    var a = document.getElementById(ids[i]);
                    if (!a) continue;
                    try {
                        a.volume = 0;
                        var p = a.play();
                        (function(audio){
                            if (p && p.then) {
                                p.then(function() {
                                    audio.pause();
                                    audio.currentTime = 0;
                                    audio.volume = 1;
                                }).catch(function() { audio.volume = 1; });
                            } else {
                                audio.pause();
                                audio.currentTime = 0;
                                audio.volume = 1;
                            }
                        })(a);
                    } catch (e) {}
                }
            }

            function handle() {
                var digits = normalize(input.value);
                if (digits.length < 19) return;
                if (digits.length > 19) digits = digits.substr(0, 19);

                desbloquearAudios();

                if (!window.__conferenciaInit && typeof window.iniciarConferenciaPacotes === 'function') {
                    try { window.iniciarConferenciaPacotes(); } catch (e) {}
                }
                if (window.processarLeituraCodigo) {
                    window.processarLeituraCodigo(digits);
                    return;
                }

                var linha = document.querySelector('tr[data-codigo="' + digits + '"]');
                var msg = document.getElementById('mensagemLeitura');
                var pacoteJaConferido = document.getElementById('pacotejaconferido');
                var muteBeep = document.getElementById('muteBeep');
                var beep = document.getElementById('beep');
                if (!linha) {
                    if (msg) {
                        msg.innerHTML = '<strong>Pacote não encontrado:</strong> adicionado à lista pendente.';
                    }
                    if (window.adicionarPacotePendente) {
                        var now = new Date();
                        var mm = String(now.getMonth() + 1).padStart(2, '0');
                        var dd = String(now.getDate()).padStart(2, '0');
                        var dataPadrao = now.getFullYear() + '-' + mm + '-' + dd;
                        var obj = {
                            codbar: digits,
                            lote: digits.substr(0, 8),
                            regional: digits.substr(8, 3),
                            posto: digits.substr(11, 3),
                            quantidade: parseInt(digits.substr(14, 5), 10) || 1,
                            dataexp: dataPadrao,
                            responsavel: ''
                        };
                        window.adicionarPacotePendente(obj);
                        var painel = document.getElementById('painelPacotesNovos');
                        if (painel) {
                            painel.style.display = 'block';
                            painel.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }
                    var audioNaoEncontrado = document.getElementById('pacote_nao_encontrado');
                    if (audioNaoEncontrado) {
                        try {
                            audioNaoEncontrado.currentTime = 0;
                            audioNaoEncontrado.play();
                        } catch (e) {}
                    } else if (window.speechSynthesis) {
                        try {
                            var ut = new SpeechSynthesisUtterance('pacote não encontrado');
                            ut.lang = 'pt-BR';
                            window.speechSynthesis.cancel();
                            window.speechSynthesis.speak(ut);
                        } catch (e) {}
                    }
                    input.value = '';
                    return;
                }

                if (linha.classList.contains('confirmado')) {
                    if (pacoteJaConferido) {
                        try { pacoteJaConferido.currentTime = 0; pacoteJaConferido.play(); } catch (e) {}
                    }
                    destacarChipOperacao(linha.getAttribute('data-codigo') || digits);
                    input.value = '';
                    return;
                }

                linha.classList.add('confirmado');
                var tdConf = linha.querySelector('.col-conferido-em');
                if (tdConf) tdConf.textContent = formatarAgora();
                var chipFallback = atualizarChipOperacaoPorCodigo(linha.getAttribute('data-codigo') || digits, true);
                if (chipFallback) {
                    chipFallback.setAttribute('data-conferido-em', formatarAgora());
                }

                var ultimas = document.querySelectorAll('tr.ultimo-lido');
                for (var u = 0; u < ultimas.length; u++) {
                    ultimas[u].classList.remove('ultimo-lido');
                }
                linha.classList.add('ultimo-lido');
                linha.scrollIntoView({ behavior: 'smooth', block: 'center' });
                destacarChipOperacao(linha.getAttribute('data-codigo') || digits);

                if (msg) msg.textContent = '';

                if (beep && (!muteBeep || !muteBeep.checked)) {
                    try { beep.currentTime = 0; beep.play(); } catch (e) {}
                }

                var usuario = '';
                try { usuario = sessionStorage.getItem('conferencia_responsavel') || ''; } catch (e) {}
                if (!usuario) {
                    var badge = document.getElementById('usuarioBadge');
                    if (badge) usuario = (badge.textContent || '').trim();
                }
                if (usuario) {
                    var formData = new FormData();
                    formData.append('salvar_lote_ajax', '1');
                    formData.append('lote', linha.getAttribute('data-lote') || '');
                    formData.append('regional', linha.getAttribute('data-regional') || '');
                    formData.append('posto', linha.getAttribute('data-posto') || '');
                    formData.append('dataexp', linha.getAttribute('data-data-sql') || linha.getAttribute('data-data') || '');
                    formData.append('qtd', linha.getAttribute('data-qtd') || '');
                    formData.append('codbar', linha.getAttribute('data-codigo') || digits);
                    formData.append('usuario', usuario);
                    fetch(window.location.href, { method: 'POST', body: formData }).catch(function(){});
                }
                input.value = '';
            }

            input.addEventListener('input', handle);
            input.addEventListener('change', handle);
            input.addEventListener('keydown', function(e) {
                if (e.keyCode === 13) {
                    e.preventDefault();
                    handle();
                }
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', bindFallback);
        } else {
            bindFallback();
        }
    })();
    </script>

<div id="secaoTradicional" class="secao-visualizacao oculta">

<!-- Tabelas Agrupadas -->
<div id="tabelas">
<?php
// ========================================
// v9.0: AGRUPAMENTO USANDO DADOS DE ciRegionais
// Classificação baseada em regional e entrega REAIS
// ========================================

// v9.24.0: Banner por grupo
function renderizarBanner($texto, $classe) {
    $tipoView = ($classe === 'banner-pt') ? 'poupatempo' : 'correios';
    echo '<div class="banner-grupo ' . $classe . '" data-view="' . $tipoView . '">' . htmlspecialchars($texto, ENT_QUOTES, 'UTF-8') . '</div>';
}

function obterClasseGrupoOperacao($tituloGrupo) {
    if ($tituloGrupo === 'Poupa Tempo') return 'tipo-pt';
    if ($tituloGrupo === 'Posto 001') return 'tipo-r01';
    if ($tituloGrupo === 'Capital') return 'tipo-capital';
    if ($tituloGrupo === 'Central IIPR') return 'tipo-central';
    return 'tipo-regional';
}

function renderizarLinhasOperacao($tituloGrupo, $dados, $estanteSemUploadPorPosto) {
    if (empty($dados)) {
        return;
    }

    $primeiro = reset($dados);
    $eh_array_plano = isset($primeiro['lote']);
    $postos = array();
    if ($eh_array_plano) {
        foreach ($dados as $item) {
            $postos[] = $item;
        }
    } else {
        foreach ($dados as $lista) {
            foreach ($lista as $item) {
                $postos[] = $item;
            }
        }
    }

    $porPosto = array();
    foreach ($postos as $item) {
        $postoKey = isset($item['posto']) ? $item['posto'] : '000';
        if (!isset($porPosto[$postoKey])) {
            $porPosto[$postoKey] = array();
        }
        $porPosto[$postoKey][] = $item;
    }
    ksort($porPosto);

    $totalGrupo = count($postos);
    $conferidosGrupo = 0;
    foreach ($postos as $itemGrupo) {
        if (!empty($itemGrupo['conf'])) {
            $conferidosGrupo++;
        }
    }
    $pendentesGrupo = max(0, $totalGrupo - $conferidosGrupo);
    $classeGrupo = obterClasseGrupoOperacao($tituloGrupo);
    $tipoViewGrupo = (!empty($postos[0]['isPT']) && (int)$postos[0]['isPT'] === 1) ? 'poupatempo' : 'correios';

    echo '<div class="operacao-grupo ' . $classeGrupo . '" data-view="' . $tipoViewGrupo . '">';
    echo '<div class="operacao-grupo-titulo">';
    echo '<div class="operacao-grupo-info">';
    echo '<div class="operacao-grupo-nome">' . htmlspecialchars($tituloGrupo, ENT_QUOTES, 'UTF-8') . '</div>';
    echo '<div class="operacao-grupo-resumo">Total de pacotes ' . $totalGrupo . '</div>';
    echo '</div>';
    echo '<div class="operacao-numero"><span>' . $totalGrupo . '</span><span class="operacao-numero-label">PACOTES</span></div>';
    echo '<div class="operacao-numero"><span>' . $conferidosGrupo . '</span><span class="operacao-numero-label">CONFERIDOS</span></div>';
    echo '<div class="operacao-pendentes"><span>' . $pendentesGrupo . '</span><span class="operacao-numero-label">PENDENTES</span></div>';
    echo '</div>';
    echo '<div class="operacao-grade-header">';
    echo '<div>Pos.</div>';
    echo '<div>Operação</div>';
    echo '<div>Chips</div>';
    echo '<div class="hide-mobile">Pacotes</div>';
    echo '<div class="hide-mobile">Conferidos</div>';
    echo '<div class="hide-mobile">Pendentes</div>';
    echo '</div>';

    foreach ($porPosto as $postoKey => $listaPosto) {
        $totalPacotes = count($listaPosto);
        $conferidos = 0;
        foreach ($listaPosto as $item) {
            if (!empty($item['conf'])) {
                $conferidos++;
            }
        }
        $pendentes = max(0, $totalPacotes - $conferidos);
        $semUploadCount = isset($estanteSemUploadPorPosto[$postoKey]) ? (int)$estanteSemUploadPorPosto[$postoKey] : 0;
        echo '<div class="operacao-posto-row" data-posto="' . htmlspecialchars($postoKey, ENT_QUOTES, 'UTF-8') . '">';
        echo '<div><span class="operacao-posicao">' . htmlspecialchars($postoKey, ENT_QUOTES, 'UTF-8') . '</span></div>';
        echo '<div class="operacao-posto-meta">';
        echo '<div class="operacao-posto-nome">Posto ' . htmlspecialchars($postoKey, ENT_QUOTES, 'UTF-8') . '</div>';
        echo '<div class="operacao-posto-sub">' . htmlspecialchars($tituloGrupo, ENT_QUOTES, 'UTF-8') . '</div>';
        if ($semUploadCount > 0) {
            echo '<div class="operacao-posto-aux">Sem upload: ' . $semUploadCount . '</div>';
        }
        echo '</div>';
        echo '<div class="operacao-chips">';
        foreach ($listaPosto as $item) {
            $chipClasses = 'operacao-chip';
            if (!empty($item['conf'])) {
                $chipClasses .= ' confirmado';
            }
            if ($semUploadCount > 0) {
                $chipClasses .= ' sem-upload';
            }
            echo '<button type="button" class="' . $chipClasses . '"';
            echo ' data-codigo="' . htmlspecialchars($item['codigo'], ENT_QUOTES, 'UTF-8') . '"';
            echo ' data-lote="' . htmlspecialchars($item['lote'], ENT_QUOTES, 'UTF-8') . '"';
            echo ' data-posto="' . htmlspecialchars($item['posto'], ENT_QUOTES, 'UTF-8') . '"';
            echo ' data-regional="' . htmlspecialchars($item['regional_label'], ENT_QUOTES, 'UTF-8') . '"';
            echo ' data-qtd="' . htmlspecialchars($item['qtd'], ENT_QUOTES, 'UTF-8') . '"';
            echo ' data-data="' . htmlspecialchars($item['data'], ENT_QUOTES, 'UTF-8') . '"';
            echo ' data-usuario="' . htmlspecialchars($item['usuario_prod'], ENT_QUOTES, 'UTF-8') . '"';
            echo ' data-conferido-em="' . htmlspecialchars($item['conferido_em'], ENT_QUOTES, 'UTF-8') . '"';
            echo ' data-conf="' . (!empty($item['conf']) ? '1' : '0') . '">';
            echo htmlspecialchars($item['lote'], ENT_QUOTES, 'UTF-8');
            echo '</button>';
        }
        echo '</div>';
        echo '<div class="operacao-numero"><span data-role="pacotes">' . $totalPacotes . '</span><span class="operacao-numero-label">PACOTES</span></div>';
        echo '<div class="operacao-numero"><span data-role="conferidos">' . $conferidos . '</span><span class="operacao-numero-label">CONFERIDOS</span></div>';
        echo '<div class="operacao-pendentes"><span data-role="pendentes">' . $pendentes . '</span><span class="operacao-numero-label">PENDENTES</span></div>';
        echo '</div>';
    }

    echo '</div>';
}

// v8.17.5: Função para renderizar tabela (aceita array plano OU aninhado)
function renderizarTabela($titulo, $dados, $ehPoupaTempo = false, $ptGroup = '') {
    if (empty($dados)) {
        return;
    }
    
    // Verifica se é array plano (lista de postos) ou aninhado (regional => postos)
    $primeiro = reset($dados);
    $eh_array_plano = isset($primeiro['lote']); // Se tem 'lote', é um posto
    
    // Normaliza para formato de iteração
    $postos_para_exibir = array();
    if ($eh_array_plano) {
        // Array plano: já é lista de postos
        $postos_para_exibir = $dados;
    } else {
        // Array aninhado: achatar
        foreach ($dados as $regional => $postos) {
            foreach ($postos as $posto) {
                $postos_para_exibir[] = $posto;
            }
        }
    }
    
    // Conta total de pacotes e conferidos
    $total_pacotes = count($postos_para_exibir);
    $total_conferidos = 0;
    foreach ($postos_para_exibir as $posto) {
        if ($posto['conf'] == 1) {
            $total_conferidos++;
        }
    }
    $tipoView = $ehPoupaTempo ? 'poupatempo' : 'correios';
    
    echo '<h3 data-view="' . $tipoView . '">' . htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8');
    echo ' <span class="contagem-pacotes" data-total="' . $total_pacotes . '" data-conferidos="' . $total_conferidos . '" style="color:#666; font-weight:normal; font-size:14px;">(' . $total_pacotes . ' pacotes / ' . $total_conferidos . ' conferidos / ' . max(0, $total_pacotes - $total_conferidos) . ' pendentes)</span>';
    if ($ehPoupaTempo) {
        echo ' <span class="tag-pt">POUPA TEMPO</span>';
    }
    echo '</h3>';
    echo '<table data-view="' . $tipoView . '">';
    echo '<thead><tr>';
    echo '<th>Regional</th>';
    echo '<th class="sortable" data-sort="lote">Lote <span class="sort-indicator">↕</span></th>';
    echo '<th>Posto</th>';
    echo '<th class="sortable" data-sort="data">Data Expedição <span class="sort-indicator">↕</span></th>';
    echo '<th>Quantidade</th>';
    echo '<th>Responsável Produção</th>';
    echo '<th>Código de Barras</th>';
    echo '<th>Conferido em</th>';
    echo '</tr></thead>';
    echo '<tbody>';
    
    foreach ($postos_para_exibir as $posto) {
        $classeConf = ($posto['conf'] == 1) ? ' confirmado' : '';
        echo '<tr class="linha-conferencia' . $classeConf . '" ';
        echo 'data-codigo="' . htmlspecialchars($posto['codigo'], ENT_QUOTES, 'UTF-8') . '" ';
        $regional_grupo_attr = isset($posto['regional_grupo']) ? $posto['regional_grupo'] : $posto['regional'];
        echo 'data-regional="' . htmlspecialchars($posto['regional'], ENT_QUOTES, 'UTF-8') . '" ';
        echo 'data-regional-real="' . htmlspecialchars($regional_grupo_attr, ENT_QUOTES, 'UTF-8') . '" ';
        echo 'data-lote="' . htmlspecialchars($posto['lote'], ENT_QUOTES, 'UTF-8') . '" ';
        echo 'data-posto="' . htmlspecialchars($posto['posto'], ENT_QUOTES, 'UTF-8') . '" ';
        echo 'data-data="' . htmlspecialchars($posto['data'], ENT_QUOTES, 'UTF-8') . '" ';
        $data_sql_attr = isset($posto['data_sql']) ? $posto['data_sql'] : '';
        echo 'data-data-sql="' . htmlspecialchars($data_sql_attr, ENT_QUOTES, 'UTF-8') . '" ';
        echo 'data-qtd="' . htmlspecialchars($posto['qtd'], ENT_QUOTES, 'UTF-8') . '" ';
        echo 'data-usuario-prod="' . htmlspecialchars($posto['usuario_prod'], ENT_QUOTES, 'UTF-8') . '" ';
        echo 'data-conferido-em="' . htmlspecialchars($posto['conferido_em'], ENT_QUOTES, 'UTF-8') . '" ';
        echo 'data-ispt="' . $posto['isPT'] . '" ';
        echo 'data-pt-group="' . htmlspecialchars($ptGroup, ENT_QUOTES, 'UTF-8') . '">';
        $regional_label = isset($posto['regional_label']) ? $posto['regional_label'] : $posto['regional'];
        echo '<td>' . htmlspecialchars($regional_label, ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars($posto['lote'], ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars($posto['posto'], ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars($posto['data'], ENT_QUOTES, 'UTF-8') . '</td>';
        $conferido_em_fmt = '';
        if (!empty($posto['conferido_em'])) {
            $ts = strtotime($posto['conferido_em']);
            if ($ts) { $conferido_em_fmt = date('d-m-Y H:i', $ts); }
        }
        echo '<td>' . htmlspecialchars($posto['qtd'], ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars($posto['usuario_prod'], ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars($posto['codigo'], ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td class="col-conferido-em">' . htmlspecialchars($conferido_em_fmt, ENT_QUOTES, 'UTF-8') . '</td>';
        echo '</tr>';
    }
    
    echo '</tbody></table>';
}

// v8.17.4: Renderizar na ordem correta (cada grupo = UMA tabela)
$banner_correios_exibido = false;
$banner_pt_exibido = false;
if (!empty($grupo_pt)) {
    ksort($grupo_pt);
    if (!$banner_pt_exibido) {
        renderizarBanner('POSTOS DO POUPA TEMPO', 'banner-pt');
        $banner_pt_exibido = true;
    }
    foreach ($grupo_pt as $postoKey => $postosPt) {
        renderizarTabela('Posto ' . $postoKey . ' - Poupa Tempo', $postosPt, true, $postoKey);
    }
}
if (!empty($grupo_r01)) {
    if (!$banner_correios_exibido) {
        renderizarBanner('POSTOS DOS CORREIOS', 'banner-correios');
        $banner_correios_exibido = true;
    }
    renderizarTabela('Postos do Posto 01', $grupo_r01);
}
if (!empty($grupo_capital)) {
    if (!$banner_correios_exibido) {
        renderizarBanner('POSTOS DOS CORREIOS', 'banner-correios');
        $banner_correios_exibido = true;
    }
    $capital_por_posto = array();
    foreach ($grupo_capital as $p) {
        $capital_por_posto[$p['posto']][] = $p;
    }
    ksort($capital_por_posto);
    echo '<div class="grupo-capital-wrapper" data-view="correios">';
    echo '<div class="grupo-capital-titulo">Capital</div>';
    foreach ($capital_por_posto as $postoKey => $lista) {
        echo '<div class="subgrupo-posto">';
        renderizarTabela('Posto ' . $postoKey . ' - Capital', $lista);
        echo '</div>';
    }
    echo '</div>';
}
if (!empty($grupo_999)) {
    if (!$banner_correios_exibido) {
        renderizarBanner('POSTOS DOS CORREIOS', 'banner-correios');
        $banner_correios_exibido = true;
    }
    $central_por_posto = array();
    foreach ($grupo_999 as $p) {
        $central_por_posto[$p['posto']][] = $p;
    }
    ksort($central_por_posto);
    echo '<div class="grupo-central-wrapper" data-view="correios">';
    echo '<div class="grupo-central-titulo">Central IIPR</div>';
    foreach ($central_por_posto as $postoKey => $lista) {
        echo '<div class="subgrupo-posto">';
        renderizarTabela('Posto ' . $postoKey . ' - Central', $lista);
        echo '</div>';
    }
    echo '</div>';
}
// v8.17.5: Demais regionais já ordenadas (ksort aplicado na linha 367)
if (!empty($grupo_outros)) {
    if (!$banner_correios_exibido) {
        renderizarBanner('POSTOS DOS CORREIOS', 'banner-correios');
        $banner_correios_exibido = true;
    }
    foreach ($grupo_outros as $regional => $postos) {
        $regionalStr = str_pad($regional, 3, '0', STR_PAD_LEFT);
        renderizarTabela($regionalStr . ' - Regional ' . $regionalStr, array($regional => $postos));
    }
}

if (empty($regionais_data)) {
    echo '<p style="text-align:center; margin-top:40px; color:#999;">Nenhum dado encontrado para as datas selecionadas.</p>';
}
?>

</div>

</div>

<!-- Áudios -->
<audio id="beep" src="beep.mp3" preload="auto"></audio>
<audio id="concluido" src="concluido.mp3" preload="auto"></audio>
<audio id="pacotejaconferido" src="pacotejaconferido.mp3" preload="auto"></audio>
<audio id="pacotedeoutraregional" src="pacotedeoutraregional.mp3" preload="auto"></audio>
<audio id="posto_poupatempo" src="posto_poupatempo.mp3" preload="auto"></audio>
<audio id="pertence_correios" src="pertence_aos_correios.mp3" preload="auto"></audio>
<audio id="pacote_nao_encontrado" src="pacote_nao_foi_encontrado.mp3" preload="auto"></audio>

<script>
// ========================================
// v9.22.7: JavaScript com fila de sons sem sobreposição
// ========================================

function substituirMultiplosPadroes(inputString) {
    var stringProcessada = inputString;
    
    // Regra 1: Substituir "755" por "779" quando seguido por 5 dígitos
    var regex755 = /(\d{11})(755)(\d{5})/g;
    if (regex755.test(stringProcessada)) {
        stringProcessada = stringProcessada.replace(regex755, function(match, p1, p2) {
            return "779" + p2;
        });
    }
    
    // Regra 2: Substituir "500" por "507" quando seguido por 5 dígitos
    var regex500 = /(\d{11})(500)(\d{5})/g;
    if (regex500.test(stringProcessada)) {
        stringProcessada = stringProcessada.replace(regex500, function(match, p1, p2) {
            return "507" + p2;
        });
    }
    
    return stringProcessada;
}

function iniciarConferenciaPacotes() {
    if (window.__conferenciaInit) return;
    window.__conferenciaInit = true;
    try {
    var input = document.getElementById("codigo_barras");
    var radioAutoSalvar = document.getElementById("autoSalvar");
    var beep = document.getElementById("beep");
    var concluido = document.getElementById("concluido");
    var pacoteJaConferido = document.getElementById("pacotejaconferido");
    var pacoteOutraRegional = document.getElementById("pacotedeoutraregional");
    var postoPoupaTempo = document.getElementById("posto_poupatempo");
    var pertenceCorreios = document.getElementById("pertence_correios");
    var pacoteNaoEncontradoAudio = document.getElementById("pacote_nao_encontrado");
    var muteBeep = document.getElementById("muteBeep");
    var btnResetar = document.getElementById("resetar");
    var usuarioBadge = document.getElementById("usuarioBadge");
    var overlayUsuario = document.getElementById("overlayUsuario");
    var conteudoPagina = document.getElementById("conteudoPagina");
    var usuarioInputModal = document.getElementById("usuario_conf_modal");
    var btnConfirmarUsuario = document.getElementById("btnConfirmarUsuario");
    var btnSomenteVisualizar = document.getElementById("btnSomenteVisualizar");
    var btnAtivarConferencia = document.getElementById("btnAtivarConferencia");
    var overlayTipo = document.getElementById("overlayTipo");
    var usuarioAtual = '';
    var audioDesbloqueado = false;
    var modoConsulta = false;
    var modalPacote = document.getElementById('modalPacote');
    var overlayConfirmacao = document.getElementById('overlayConfirmacao');
    var confirmacaoTexto = document.getElementById('confirmacaoTexto');
    var btnConfirmacaoOk = document.getElementById('btnConfirmacaoOk');
    var modalChipDetalhe = document.getElementById('modalChipDetalhe');
    var tabelaDetalheChip = document.getElementById('tabelaDetalheChip');
    var btnFecharModalChip = document.getElementById('btnFecharModalChip');
    var btnMostrarClassificacao = document.getElementById('btnMostrarClassificacao');
    var btnMostrarTradicional = document.getElementById('btnMostrarTradicional');
    var secaoClassificacao = document.getElementById('secaoClassificacao');
    var secaoTradicional = document.getElementById('secaoTradicional');
    var pacoteCodbar = document.getElementById('pacote_codbar');
    var pacoteLote = document.getElementById('pacote_lote');
    var pacoteRegional = document.getElementById('pacote_regional');
    var pacotePosto = document.getElementById('pacote_posto');
    var pacoteQtd = document.getElementById('pacote_qtd');
    var pacoteDataexp = document.getElementById('pacote_dataexp');
    var pacoteResponsavel = document.getElementById('pacote_responsavel');
    var pacoteIdx = document.getElementById('pacote_idx');
    var btnAdicionarPacote = document.getElementById('btnAdicionarPacote');
    var btnCancelarPacote = document.getElementById('btnCancelarPacote');
    var painelPacotesNovos = document.getElementById('painelPacotesNovos');
    var listaPacotesNovos = document.getElementById('listaPacotesNovos');
    var btnSalvarPacotes = document.getElementById('btnSalvarPacotes');
    var btnCancelarPacotes = document.getElementById('btnCancelarPacotes');
    var resumoPacotesPendentes = document.getElementById('resumoPacotesPendentes');
    var autorSalvamentoPacotes = document.getElementById('autor_salvamento_pacotes');
    var turnoSalvamentoPacotes = document.getElementById('turno_salvamento_pacotes');
    var criadoSalvamentoPacotes = document.getElementById('criado_salvamento_pacotes');
    var consolidarSalvamentoPacotes = document.getElementById('consolidar_salvamento_pacotes');
    var pacotesPendentes = [];
    var mensagemLeitura = document.getElementById('mensagemLeitura');
    var postoBloqueioNumero = document.getElementById('postoBloqueioNumero');
    var postoBloqueioNome = document.getElementById('postoBloqueioNome');
    var postoBloqueioResponsavel = document.getElementById('postoBloqueioResponsavel');
    var postoDesbloqueioResponsavel = document.getElementById('postoDesbloqueioResponsavel');
    var postoDesbloqueioMotivo = document.getElementById('postoDesbloqueioMotivo');
    var btnAdicionarBloqueio = document.getElementById('btnAdicionarBloqueio');
    var listaPostosBloqueados = document.getElementById('listaPostosBloqueados');
    var postosBloqueados = <?php echo json_encode($postos_bloqueados); ?>;
    var postosBloqueadosMap = {};
    var tipoEscolhido = false;
    var datasFiltroSql = <?php echo json_encode($datas_sql); ?>;
    var storageUsuarioKey = 'conferencia_responsavel';
    var storageTipoKey = 'conferencia_tipo_inicio';
    var storageModoKey = 'conferencia_modo';

    function aplicarModoConsulta(ativo) {
        modoConsulta = !!ativo;
        if (modoConsulta) {
            document.body.classList.add('modo-consulta');
            if (overlayUsuario) overlayUsuario.style.display = 'none';
            if (conteudoPagina) conteudoPagina.classList.remove('page-locked');
            tipoEscolhido = false;
            usuarioAtual = '';
        } else {
            document.body.classList.remove('modo-consulta');
        }
    }

    function ativarConsulta() {
        try { localStorage.setItem(storageModoKey, 'consulta'); } catch (e) {}
        aplicarModoConsulta(true);
        atualizarResumoTodasTabelas();
    }

    function ativarConferencia() {
        try { localStorage.removeItem(storageModoKey); } catch (e) {}
        aplicarModoConsulta(false);
        if (overlayUsuario) overlayUsuario.style.display = 'flex';
        if (conteudoPagina) conteudoPagina.classList.add('page-locked');
        if (usuarioInputModal) usuarioInputModal.focus();
    }

    // v9.22.7: Fila de áudio para evitar sobreposição
    var filaSons = [];
    var tocando = false;
    var ultimoCodLido = '';
    var codigosEmProcessamento = {};
    var ultimoCodigoProcessado = '';
    var ultimaLeituraProcessadaEm = 0;

    function mostrarConfirmacao(texto, autoFechar) {
        if (confirmacaoTexto) {
            confirmacaoTexto.textContent = texto || 'Dados salvos com sucesso!';
        }
        if (overlayConfirmacao) {
            overlayConfirmacao.style.display = 'flex';
        }
        if (autoFechar) {
            setTimeout(function() {
                if (overlayConfirmacao) overlayConfirmacao.style.display = 'none';
            }, 1200);
        }
    }

    if (btnConfirmacaoOk) {
        btnConfirmacaoOk.addEventListener('click', function() {
            if (overlayConfirmacao) overlayConfirmacao.style.display = 'none';
        });
    }

    if (btnFecharModalChip) {
        btnFecharModalChip.addEventListener('click', function() {
            if (modalChipDetalhe) modalChipDetalhe.style.display = 'none';
        });
    }

    if (modalChipDetalhe) {
        modalChipDetalhe.addEventListener('click', function(e) {
            if (e.target === modalChipDetalhe) {
                modalChipDetalhe.style.display = 'none';
            }
        });
    }

    document.addEventListener('click', function(e) {
        var chip = e.target && e.target.closest ? e.target.closest('.operacao-chip') : null;
        if (!chip) return;
        destacarChipOperacao(chip.getAttribute('data-codigo') || '');
        abrirModalChipDetalhe(chip);
    });

    function alternarModoVisualizacao(modo) {
        var abrirClassificacao = modo === 'classificacao';
        var abrirTradicional = modo === 'tradicional';
        if (secaoClassificacao) {
            secaoClassificacao.classList.toggle('oculta', !abrirClassificacao);
        }
        if (secaoTradicional) {
            secaoTradicional.classList.toggle('oculta', !abrirTradicional);
        }
        if (btnMostrarClassificacao) {
            btnMostrarClassificacao.classList.toggle('ativo', abrirClassificacao);
            btnMostrarClassificacao.setAttribute('aria-expanded', abrirClassificacao ? 'true' : 'false');
        }
        if (btnMostrarTradicional) {
            btnMostrarTradicional.classList.toggle('ativo', abrirTradicional);
            btnMostrarTradicional.setAttribute('aria-expanded', abrirTradicional ? 'true' : 'false');
        }
    }

    function nomeResponsavelValido(nome) {
        var texto = String(nome || '').trim();
        if (!texto) return false;
        var invalido = texto.toLowerCase();
        return invalido !== 'teste' && invalido !== 'não informado' && invalido !== 'nao informado';
    }

    function centralizarElemento(elemento) {
        if (!elemento || elemento.offsetParent === null) return;
        var rect = elemento.getBoundingClientRect();
        var topo = rect.top + window.pageYOffset - (window.innerHeight / 2) + (rect.height / 2);
        window.scrollTo({ top: Math.max(0, topo), behavior: 'smooth' });
    }

    function correspondeTipoVisual(tipo, isPt) {
        if (tipo === 'todos') return true;
        if (tipo === 'poupatempo') return !!isPt;
        return !isPt;
    }

    function aplicarFiltroTipoVisual(tipo) {
        var tipoAtualVisual = tipo || obterTipoInicioSelecionado();
        var linhas = document.querySelectorAll('#tabelas tbody tr');
        for (var i = 0; i < linhas.length; i++) {
            var linha = linhas[i];
            var isPt = linha.getAttribute('data-ispt') === '1';
            linha.style.display = correspondeTipoVisual(tipoAtualVisual, isPt) ? '' : 'none';
        }

        var tabelas = document.querySelectorAll('#tabelas table[data-view]');
        for (var j = 0; j < tabelas.length; j++) {
            var tabela = tabelas[j];
            var visiveis = tabela.querySelectorAll('tbody tr:not([style*="display: none"])').length;
            var exibirTabela = visiveis > 0;
            tabela.style.display = exibirTabela ? '' : 'none';
            var titulo = obterTituloTabela(tabela);
            if (titulo) titulo.style.display = exibirTabela ? '' : 'none';
        }

        var blocosCorreios = document.querySelectorAll('.grupo-capital-wrapper[data-view], .grupo-central-wrapper[data-view]');
        for (var k = 0; k < blocosCorreios.length; k++) {
            var bloco = blocosCorreios[k];
            var temTabelaVisivel = bloco.querySelector('table[data-view]:not([style*="display: none"])');
            bloco.style.display = temTabelaVisivel ? '' : 'none';
        }

        var banners = document.querySelectorAll('.banner-grupo[data-view]');
        for (var b = 0; b < banners.length; b++) {
            var banner = banners[b];
            var view = banner.getAttribute('data-view') || 'todos';
            banner.style.display = (tipoAtualVisual === 'todos' || view === tipoAtualVisual) ? '' : 'none';
        }

        var gruposOperacao = document.querySelectorAll('.operacao-grupo[data-view]');
        for (var g = 0; g < gruposOperacao.length; g++) {
            var grupo = gruposOperacao[g];
            var groupView = grupo.getAttribute('data-view') || 'todos';
            grupo.style.display = (tipoAtualVisual === 'todos' || groupView === tipoAtualVisual) ? '' : 'none';
        }

        atualizarResumoTodasTabelas();
        sincronizarPainelOperacao();
    }

    if (btnMostrarClassificacao) {
        btnMostrarClassificacao.addEventListener('click', function() {
            var aberto = secaoClassificacao && !secaoClassificacao.classList.contains('oculta');
            alternarModoVisualizacao(aberto ? '' : 'classificacao');
        });
    }

    if (btnMostrarTradicional) {
        btnMostrarTradicional.addEventListener('click', function() {
            var aberto = secaoTradicional && !secaoTradicional.classList.contains('oculta');
            alternarModoVisualizacao(aberto ? '' : 'tradicional');
        });
    }

    function obterTituloTabela(tabela) {
        var titulo = tabela ? tabela.previousElementSibling : null;
        while (titulo && titulo.tagName !== 'H3') {
            titulo = titulo.previousElementSibling;
        }
        return titulo;
    }

    function atualizarResumoTabela(tabela) {
        if (!tabela) return;
        var tbody = tabela.tBodies && tabela.tBodies[0] ? tabela.tBodies[0] : null;
        if (!tbody) return;
        var linhas = tbody.rows;
        var total = 0;
        var conferidos = 0;
        for (var i = 0; i < linhas.length; i++) {
            if (linhas[i].style.display === 'none') {
                continue;
            }
            total++;
            if (linhas[i].classList.contains('confirmado')) {
                conferidos++;
            }
        }
        var titulo = obterTituloTabela(tabela);
        if (!titulo) return;
        var span = titulo.querySelector('.contagem-pacotes');
        if (!span) return;
        span.textContent = '(' + total + ' pacotes / ' + conferidos + ' conferidos / ' + Math.max(0, total - conferidos) + ' pendentes)';
        span.setAttribute('data-total', String(total));
        span.setAttribute('data-conferidos', String(conferidos));
        sincronizarPainelOperacao();
    }

    function atualizarResumoTodasTabelas() {
        var tabelas = document.querySelectorAll('#tabelas table');
        for (var i = 0; i < tabelas.length; i++) {
            atualizarResumoTabela(tabelas[i]);
        }
    }

    function atualizarLinhaOperacao(row) {
        if (!row) return;
        var chips = row.querySelectorAll('.operacao-chip');
        var total = chips.length;
        var conferidos = 0;
        for (var i = 0; i < chips.length; i++) {
            if (chips[i].getAttribute('data-conf') === '1') {
                conferidos++;
            }
        }
        var pacotes = row.querySelector('[data-role="pacotes"]');
        var conferidosEl = row.querySelector('[data-role="conferidos"]');
        var pendentes = row.querySelector('[data-role="pendentes"]');
        if (pacotes) pacotes.textContent = String(total);
        if (conferidosEl) conferidosEl.textContent = String(conferidos);
        if (pendentes) pendentes.textContent = String(Math.max(0, total - conferidos));
    }

    function atualizarChipOperacaoPorCodigo(codigo, confirmado) {
        if (!codigo) return null;
        var chip = document.querySelector('.operacao-chip[data-codigo="' + codigo + '"]');
        if (!chip) return null;
        chip.setAttribute('data-conf', confirmado ? '1' : '0');
        if (confirmado) {
            chip.classList.add('confirmado');
        } else {
            chip.classList.remove('confirmado');
        }
        atualizarLinhaOperacao(chip.closest('.operacao-posto-row'));
        return chip;
    }

    function destacarChipOperacao(codigo) {
        var chipsAtivos = document.querySelectorAll('.operacao-chip.ativo');
        for (var i = 0; i < chipsAtivos.length; i++) {
            chipsAtivos[i].classList.remove('ativo');
        }
        var linhasAtivas = document.querySelectorAll('.operacao-posto-row.ativo');
        for (var j = 0; j < linhasAtivas.length; j++) {
            linhasAtivas[j].classList.remove('ativo');
        }
        if (!codigo) return;
        var chip = document.querySelector('.operacao-chip[data-codigo="' + codigo + '"]');
        if (!chip) return;
        chip.classList.add('ativo');
        var linha = chip.closest('.operacao-posto-row');
        if (linha) {
            linha.classList.add('ativo');
            centralizarElemento(linha);
        } else {
            centralizarElemento(chip);
        }
    }

    function sincronizarPainelOperacao() {
        var chips = document.querySelectorAll('.operacao-chip');
        for (var i = 0; i < chips.length; i++) {
            var chip = chips[i];
            var codigo = chip.getAttribute('data-codigo') || '';
            var linhaTabela = codigo ? document.querySelector('tr[data-codigo="' + codigo + '"]') : null;
            var confirmado = !!(linhaTabela && linhaTabela.classList.contains('confirmado'));
            chip.setAttribute('data-conf', confirmado ? '1' : '0');
            if (confirmado) {
                chip.classList.add('confirmado');
            } else {
                chip.classList.remove('confirmado');
            }
            atualizarLinhaOperacao(chip.closest('.operacao-posto-row'));
        }
    }

    function abrirModalChipDetalhe(chip) {
        if (!chip || !modalChipDetalhe || !tabelaDetalheChip) return;
        var itens = [
            ['Lote', chip.getAttribute('data-lote') || ''],
            ['Posto', chip.getAttribute('data-posto') || ''],
            ['Regional', chip.getAttribute('data-regional') || ''],
            ['Quantidade', chip.getAttribute('data-qtd') || ''],
            ['Data', formatarDataExibicao(chip.getAttribute('data-data') || '')],
            ['Responsável produção', chip.getAttribute('data-usuario') || ''],
            ['Código de barras', chip.getAttribute('data-codigo') || ''],
            ['Conferido em', formatarDataExibicao(chip.getAttribute('data-conferido-em') || '') || 'Pendente']
        ];
        tabelaDetalheChip.innerHTML = '';
        for (var i = 0; i < itens.length; i++) {
            var tr = document.createElement('tr');
            tr.innerHTML = '<th style="text-align:left; width:170px;">' + itens[i][0] + '</th><td>' + itens[i][1] + '</td>';
            tabelaDetalheChip.appendChild(tr);
        }
        modalChipDetalhe.style.display = 'flex';
    }

    function normalizarDataOrdem(valor) {
        if (!valor) return '';
        if (/^\d{4}-\d{2}-\d{2}$/.test(valor)) return valor;
        if (/^\d{2}-\d{2}-\d{4}$/.test(valor)) {
            var p = valor.split('-');
            return p[2] + '-' + p[1] + '-' + p[0];
        }
        return valor;
    }

    function formatarDataExibicao(valor) {
        if (!valor) return '';
        var texto = String(valor).trim();
        var m = texto.match(/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2})(?::\d{2})?)?$/);
        if (m) {
            var base = m[3] + '-' + m[2] + '-' + m[1];
            if (m[4] && m[5]) {
                return base + ' ' + m[4] + ':' + m[5];
            }
            return base;
        }
        return texto;
    }

    function formatarDataHoraAtual() {
        var d = new Date();
        var dd = String(d.getDate()).padStart(2, '0');
        var mm = String(d.getMonth() + 1).padStart(2, '0');
        var yy = d.getFullYear();
        var hh = String(d.getHours()).padStart(2, '0');
        var mi = String(d.getMinutes()).padStart(2, '0');
        return dd + '-' + mm + '-' + yy + ' ' + hh + ':' + mi;
    }

    function obterValorOrdenacao(linha, chave) {
        if (!linha) return '';
        if (chave === 'lote') {
            return linha.getAttribute('data-lote') || '';
        }
        if (chave === 'data') {
            var ds = linha.getAttribute('data-data-sql') || linha.getAttribute('data-data') || '';
            return normalizarDataOrdem(ds);
        }
        return '';
    }

    function ordenarTabela(tabela, chave, asc) {
        if (!tabela) return;
        var tbody = tabela.tBodies && tabela.tBodies[0] ? tabela.tBodies[0] : null;
        if (!tbody) return;
        var linhas = Array.prototype.slice.call(tbody.rows);
        linhas.sort(function(a, b) {
            var va = obterValorOrdenacao(a, chave);
            var vb = obterValorOrdenacao(b, chave);
            if (va < vb) return asc ? -1 : 1;
            if (va > vb) return asc ? 1 : -1;
            return 0;
        });
        for (var i = 0; i < linhas.length; i++) {
            tbody.appendChild(linhas[i]);
        }
    }

    function tocarProximoSom() {
        if (filaSons.length === 0) {
            tocando = false;
            return;
        }
        tocando = true;
        var som = filaSons.shift();
        try {
            som.currentTime = 0;
            var playPromise = som.play();
            if (playPromise && playPromise.then) {
                playPromise.catch(function() {
                    tocando = false;
                    tocarProximoSom();
                });
            }
        } catch (e) {
            tocando = false;
            tocarProximoSom();
        }
    }

    function enfileirarSom(som) {
        if (!som) return;
        filaSons.push(som);
        if (!tocando) {
            tocarProximoSom();
        }
    }

    function falarTexto(texto) {
        if (!window.speechSynthesis || !texto) return;
        try {
            var ut = new SpeechSynthesisUtterance(texto);
            ut.lang = 'pt-BR';
            window.speechSynthesis.cancel();
            window.speechSynthesis.speak(ut);
        } catch (e) {}
    }

    function tocarPacoteNaoEncontrado() {
        if (pacoteNaoEncontradoAudio) {
            enfileirarSom(pacoteNaoEncontradoAudio);
            return;
        }
        falarTexto('pacote não encontrado');
    }

    function avisarIncompatibilidadeTipo(tipoPacote) {
        if (tipoPacote === 'correios') {
            if (mensagemLeitura) {
                mensagemLeitura.innerHTML = '<strong>Posto dos Correios:</strong> altere o tipo para Correios ou Todos.';
            }
            if (pertenceCorreios) {
                enfileirarSom(pertenceCorreios);
            }
            falarTexto('posto dos correios');
            return;
        }
        if (mensagemLeitura) {
            mensagemLeitura.innerHTML = '<strong>Posto do Poupa Tempo:</strong> altere o tipo para Poupa Tempo ou Todos.';
        }
        if (postoPoupaTempo) {
            enfileirarSom(postoPoupaTempo);
        }
        falarTexto('posto do poupa tempo');
    }

    // Encadeia para tocar o próximo som quando o atual terminar
    var listaSons = [];
    if (beep) listaSons.push(beep);
    if (concluido) listaSons.push(concluido);
    if (pacoteJaConferido) listaSons.push(pacoteJaConferido);
    if (pacoteOutraRegional) listaSons.push(pacoteOutraRegional);
    if (postoPoupaTempo) listaSons.push(postoPoupaTempo);
    if (pertenceCorreios) listaSons.push(pertenceCorreios);
    if (pacoteNaoEncontradoAudio) listaSons.push(pacoteNaoEncontradoAudio);
    for (var si = 0; si < listaSons.length; si++) {
        listaSons[si].addEventListener('ended', function() {
            tocarProximoSom();
        });
    }

    function desbloquearAudio() {
        if (audioDesbloqueado) return;
        audioDesbloqueado = true;
        for (var i = 0; i < listaSons.length; i++) {
            try {
                listaSons[i].volume = 0;
                var p = listaSons[i].play();
                if (p && p.then) {
                    p.then(function() {
                        for (var j = 0; j < listaSons.length; j++) {
                            listaSons[j].pause();
                            listaSons[j].currentTime = 0;
                            listaSons[j].volume = 1;
                        }
                    }).catch(function() {
                        for (var k = 0; k < listaSons.length; k++) {
                            listaSons[k].volume = 1;
                        }
                    });
                }
            } catch (e) {
                for (var k2 = 0; k2 < listaSons.length; k2++) {
                    listaSons[k2].volume = 1;
                }
            }
        }
    }

    if (input) {
        input.addEventListener('focus', desbloquearAudio);
        input.addEventListener('click', desbloquearAudio);
    }
    document.addEventListener('keydown', desbloquearAudio, { once: true });
    
    // v9.23.2: Variáveis de contexto para sons inteligentes
    var regionalAtual = null;
    var tipoAtual = null; // 'poupatempo' ou 'correios'
    var primeiroConferido = false;
    var ultimaRegionalLida = null;
    var ultimoTipoLido = null;

    function obterTipoInicioSelecionado() {
        var radios = document.querySelectorAll('input[name="tipo_inicio"]');
        for (var i = 0; i < radios.length; i++) {
            if (radios[i].checked) return radios[i].value;
        }
        return 'correios';
    }

    if (radioAutoSalvar) {
        radioAutoSalvar.checked = true;
    }

    function selecionarTipoConferencia(tipo) {
        var radios = document.querySelectorAll('input[name="tipo_inicio"]');
        for (var i = 0; i < radios.length; i++) {
            if (radios[i].value === tipo) {
                radios[i].checked = true;
                break;
            }
        }
        tipoEscolhido = true;
        if (overlayTipo) overlayTipo.style.display = 'none';
        regionalAtual = null;
        tipoAtual = null;
        primeiroConferido = false;
        ultimaRegionalLida = null;
        ultimoTipoLido = null;
        try {
            sessionStorage.setItem(storageTipoKey, tipo);
        } catch (e) {}
        aplicarFiltroTipoVisual(tipo);
        if (input) input.focus();
    }

    function abrirModalPacote(codigo, idx) {
        if (!modalPacote) return;
        var cod = codigo || '';
        if (pacoteIdx) pacoteIdx.value = (typeof idx === 'number') ? String(idx) : '';
        if (pacoteCodbar) pacoteCodbar.value = cod;
        if (cod.length === 19 && (typeof idx !== 'number')) {
            if (pacoteLote) pacoteLote.value = cod.substr(0, 8);
            if (pacoteRegional) pacoteRegional.value = cod.substr(8, 3);
            if (pacotePosto) pacotePosto.value = cod.substr(11, 3);
            if (pacoteQtd) pacoteQtd.value = parseInt(cod.substr(14, 5), 10) || '';
        }
        if (pacoteDataexp && !pacoteDataexp.value) {
            var now = new Date();
            var mm = String(now.getMonth() + 1).padStart(2, '0');
            var dd = String(now.getDate()).padStart(2, '0');
            pacoteDataexp.value = now.getFullYear() + '-' + mm + '-' + dd;
        }
        modalPacote.style.display = 'flex';
        if (pacoteLote) pacoteLote.focus();
    }

    function fecharModalPacote() {
        if (modalPacote) modalPacote.style.display = 'none';
        if (pacoteIdx) pacoteIdx.value = '';
        if (pacoteResponsavel) pacoteResponsavel.value = '';
    }

    function formatarDateTimeLocal(data) {
        var ano = data.getFullYear();
        var mes = String(data.getMonth() + 1).padStart(2, '0');
        var dia = String(data.getDate()).padStart(2, '0');
        var hora = String(data.getHours()).padStart(2, '0');
        var minuto = String(data.getMinutes()).padStart(2, '0');
        return ano + '-' + mes + '-' + dia + 'T' + hora + ':' + minuto;
    }

    function atualizarOpcoesSalvamentoPendentes() {
        if (autorSalvamentoPacotes && !autorSalvamentoPacotes.value && usuarioAtual) {
            autorSalvamentoPacotes.value = usuarioAtual;
        }
        if (criadoSalvamentoPacotes && !criadoSalvamentoPacotes.value) {
            criadoSalvamentoPacotes.value = formatarDateTimeLocal(new Date());
        }
    }

    function renderizarPacotesPendentes() {
        if (!listaPacotesNovos) return;
        listaPacotesNovos.innerHTML = '';
        for (var i = 0; i < pacotesPendentes.length; i++) {
            var p = pacotesPendentes[i];
            var tr = document.createElement('tr');
            tr.innerHTML = '<td>' + p.lote + '</td>' +
                '<td>' + p.regional + '</td>' +
                '<td>' + p.posto + '</td>' +
                '<td>' + p.quantidade + '</td>' +
                '<td>' + formatarDataExibicao(p.dataexp) + '</td>' +
                '<td>' + (p.responsavel || '') + '</td>' +
                '<td>' +
                '<button type="button" class="btn-acao btn-salvar" data-editar="' + i + '">Editar</button> ' +
                '<button type="button" class="btn-acao btn-cancelar" data-remover="' + i + '">Remover</button>' +
                '</td>';
            listaPacotesNovos.appendChild(tr);
        }
        if (painelPacotesNovos) {
            painelPacotesNovos.style.display = pacotesPendentes.length ? 'block' : 'none';
        }
        if (resumoPacotesPendentes) {
            resumoPacotesPendentes.textContent = pacotesPendentes.length
                ? (pacotesPendentes.length + ' pacote(s) aguardando carga em ciPostos e ciPostosCsv.')
                : 'Pacotes aguardando carga em ciPostos e ciPostosCsv.';
        }
        if (pacotesPendentes.length) {
            atualizarOpcoesSalvamentoPendentes();
        }
    }

    function adicionarPacotePendente(obj) {
        if (!obj || !obj.lote || !obj.regional || !obj.posto || !obj.quantidade || !obj.dataexp) {
            return false;
        }
        for (var i = 0; i < pacotesPendentes.length; i++) {
            if (pacotesPendentes[i].codbar === obj.codbar) {
                return false;
            }
        }
        pacotesPendentes.push(obj);
        renderizarPacotesPendentes();
        if (mensagemLeitura) {
            mensagemLeitura.innerHTML = '<strong>Pacote não encontrado:</strong> adicionado à lista pendente.';
        }
        return true;
    }

    window.adicionarPacotePendente = adicionarPacotePendente;

    function removerPendentePorCodbar(codbar) {
        if (!codbar) return;
        for (var i = pacotesPendentes.length - 1; i >= 0; i--) {
            if (pacotesPendentes[i].codbar === codbar) {
                pacotesPendentes.splice(i, 1);
            }
        }
        renderizarPacotesPendentes();
    }

    function formatarDataBr(dataSql) {
        if (!dataSql || typeof dataSql !== 'string') return '';
        if (/^\d{4}-\d{2}-\d{2}$/.test(dataSql)) {
            var p = dataSql.split('-');
            return p[2] + '-' + p[1] + '-' + p[0];
        }
        return dataSql;
    }

    function verificarPacoteOutraData(codbar, callback) {
        var formData = new FormData();
        formData.append('verificar_pacote_data', '1');
        formData.append('codbar', codbar || '');
        formData.append('datas_sql', JSON.stringify(datasFiltroSql || []));
        fetch(window.location.href, { method: 'POST', body: formData })
            .then(function(resp){ return resp.json(); })
            .then(function(data){ if (callback) callback(data); })
            .catch(function(){ if (callback) callback({ success:false, status:'erro' }); });
    }

    if (btnAdicionarPacote) {
        btnAdicionarPacote.addEventListener('click', function() {
            var obj = {
                codbar: pacoteCodbar ? pacoteCodbar.value.trim() : '',
                lote: pacoteLote ? pacoteLote.value.trim() : '',
                regional: pacoteRegional ? pacoteRegional.value.trim() : '',
                posto: pacotePosto ? pacotePosto.value.trim() : '',
                quantidade: pacoteQtd ? pacoteQtd.value.trim() : '',
                dataexp: pacoteDataexp ? pacoteDataexp.value.trim() : '',
                responsavel: pacoteResponsavel ? pacoteResponsavel.value.trim() : ''
            };
            if (!obj.lote || !obj.regional || !obj.posto || !obj.quantidade || !obj.dataexp) {
                alert('Preencha todos os campos do pacote.');
                return;
            }
            var idx = pacoteIdx ? parseInt(pacoteIdx.value, 10) : -1;
            if (!isNaN(idx) && idx >= 0 && pacotesPendentes[idx]) {
                pacotesPendentes[idx] = obj;
                renderizarPacotesPendentes();
            } else {
                adicionarPacotePendente(obj);
            }
            fecharModalPacote();
        });
    }

    if (btnCancelarPacote) {
        btnCancelarPacote.addEventListener('click', function() {
            fecharModalPacote();
        });
    }

    if (listaPacotesNovos) {
        listaPacotesNovos.addEventListener('click', function(e) {
            var target = e.target;
            if (!target) return;
            if (target.getAttribute('data-remover')) {
                var idxRem = parseInt(target.getAttribute('data-remover'), 10);
                if (!isNaN(idxRem)) {
                    pacotesPendentes.splice(idxRem, 1);
                    renderizarPacotesPendentes();
                }
            }
            if (target.getAttribute('data-editar')) {
                var idxEdit = parseInt(target.getAttribute('data-editar'), 10);
                if (!isNaN(idxEdit) && pacotesPendentes[idxEdit]) {
                    var p = pacotesPendentes[idxEdit];
                    if (pacoteCodbar) pacoteCodbar.value = p.codbar || '';
                    if (pacoteLote) pacoteLote.value = p.lote || '';
                    if (pacoteRegional) pacoteRegional.value = p.regional || '';
                    if (pacotePosto) pacotePosto.value = p.posto || '';
                    if (pacoteQtd) pacoteQtd.value = p.quantidade || '';
                    if (pacoteDataexp) pacoteDataexp.value = p.dataexp || '';
                    if (pacoteResponsavel) pacoteResponsavel.value = p.responsavel || '';
                    abrirModalPacote(p.codbar || '', idxEdit);
                }
            }
        });
    }

    if (btnCancelarPacotes) {
        btnCancelarPacotes.addEventListener('click', function() {
            pacotesPendentes = [];
            if (autorSalvamentoPacotes && usuarioAtual) {
                autorSalvamentoPacotes.value = usuarioAtual;
            }
            renderizarPacotesPendentes();
        });
    }

    if (btnSalvarPacotes) {
        btnSalvarPacotes.addEventListener('click', function() {
            if (!usuarioAtual) {
                alert('Informe o responsável da conferência.');
                return;
            }
            if (!pacotesPendentes.length) return;
            atualizarOpcoesSalvamentoPendentes();
            var autorSalvar = autorSalvamentoPacotes ? autorSalvamentoPacotes.value.trim() : '';
            var criadoSalvar = criadoSalvamentoPacotes ? criadoSalvamentoPacotes.value.trim() : '';
            var turnoSalvar = turnoSalvamentoPacotes ? turnoSalvamentoPacotes.value : 'Manhã';
            var consolidarSalvar = consolidarSalvamentoPacotes ? !!consolidarSalvamentoPacotes.checked : false;
            if (!autorSalvar) {
                alert('Informe o autor do salvamento.');
                if (autorSalvamentoPacotes) autorSalvamentoPacotes.focus();
                return;
            }
            if (!criadoSalvar) {
                alert('Informe a data de criação.');
                if (criadoSalvamentoPacotes) criadoSalvamentoPacotes.focus();
                return;
            }
            var formData = new FormData();
            formData.append('inserir_pacotes_nao_listados', '1');
            formData.append('usuario', usuarioAtual);
            formData.append('autor_salvamento', autorSalvar);
            formData.append('turno_salvamento', turnoSalvar);
            formData.append('criado_salvamento', criadoSalvar);
            if (consolidarSalvar) {
                formData.append('consolidar_salvamento', '1');
            }
            formData.append('pacotes', JSON.stringify(pacotesPendentes));
            fetch(window.location.href, { method: 'POST', body: formData })
                .then(function(resp){ return resp.json(); })
                .then(function(data){
                    if (data && data.success) {
                        alert('Dados salvos com sucesso!');
                        mostrarConfirmacao('Dados salvos com sucesso! ' + data.inseridos + ' lote(s) enviados para ciPostosCsv e ' + (data.inseridos_postos || 0) + ' lançamento(s) em ciPostos.', true);
                        pacotesPendentes = [];
                        renderizarPacotesPendentes();
                        setTimeout(function() { window.location.reload(); }, 1400);
                    } else {
                        alert((data && data.erro) ? data.erro : 'Erro ao inserir pacotes.');
                    }
                })
                .catch(function(){ alert('Erro ao inserir pacotes.'); });
        });
    }
    
    if (usuarioInputModal) {
        usuarioInputModal.focus();
    }

    function liberarPaginaComUsuario(nome, restaurar) {
        if (!nomeResponsavelValido(nome)) {
            if (usuarioInputModal) usuarioInputModal.focus();
            return;
        }
        aplicarModoConsulta(false);
        try { localStorage.removeItem(storageModoKey); } catch (e) {}
        usuarioAtual = nome;
        if (usuarioBadge) {
            usuarioBadge.textContent = nome;
        }
        if (overlayUsuario) {
            overlayUsuario.style.display = 'none';
        }
        if (conteudoPagina) {
            conteudoPagina.classList.remove('page-locked');
        }
        tipoEscolhido = false;
        try {
            sessionStorage.setItem(storageUsuarioKey, nome);
        } catch (e) {}
        if (restaurar) {
            var tipoSalvo = '';
            try {
                tipoSalvo = sessionStorage.getItem(storageTipoKey) || '';
            } catch (e2) {}
            if (tipoSalvo) {
                selecionarTipoConferencia(tipoSalvo);
                return;
            }
        }
        if (overlayTipo) {
            overlayTipo.style.display = 'flex';
        }
    }

    if (btnConfirmarUsuario) {
        btnConfirmarUsuario.addEventListener('click', function() {
            var nome = usuarioInputModal ? usuarioInputModal.value.trim() : '';
            if (!nomeResponsavelValido(nome)) {
                alert('Informe o responsável da conferência.');
                if (usuarioInputModal) usuarioInputModal.focus();
                return;
            }
            liberarPaginaComUsuario(nome, false);
        });
    }

    if (btnSomenteVisualizar) {
        btnSomenteVisualizar.addEventListener('click', function() {
            ativarConsulta();
        });
    }

    if (btnAtivarConferencia) {
        btnAtivarConferencia.addEventListener('click', function() {
            ativarConferencia();
        });
    }

    if (overlayTipo) {
        overlayTipo.addEventListener('click', function(e) {
            var target = e.target;
            if (!target) return;
            var tipo = target.getAttribute('data-tipo');
            if (tipo) {
                selecionarTipoConferencia(tipo);
            }
        });
    }

    var radiosTipo = document.querySelectorAll('input[name="tipo_inicio"]');
    for (var rt = 0; rt < radiosTipo.length; rt++) {
        radiosTipo[rt].addEventListener('change', function() {
            selecionarTipoConferencia(this.value);
        });
    }

    if (usuarioInputModal) {
        usuarioInputModal.addEventListener('keydown', function(e) {
            if (e.keyCode === 13) {
                e.preventDefault();
                if (btnConfirmarUsuario) btnConfirmarUsuario.click();
            }
        });
    }

    var thSort = document.querySelectorAll('th.sortable');
    for (var ti = 0; ti < thSort.length; ti++) {
        thSort[ti].addEventListener('click', function() {
            var chave = this.getAttribute('data-sort') || '';
            if (!chave) return;
            var atual = this.getAttribute('data-order') || 'asc';
            var asc = atual !== 'asc';
            this.setAttribute('data-order', asc ? 'asc' : 'desc');
            var tabela = this.closest('table');
            ordenarTabela(tabela, chave, asc);
        });
    }

    try {
        var modoSalvo = localStorage.getItem(storageModoKey) || '';
        if (modoSalvo === 'consulta') {
            ativarConsulta();
        } else {
            var nomeSalvo = sessionStorage.getItem(storageUsuarioKey) || '';
            if (nomeResponsavelValido(nomeSalvo)) {
                if (usuarioInputModal) usuarioInputModal.value = nomeSalvo;
                liberarPaginaComUsuario(nomeSalvo, true);
            } else {
                try { sessionStorage.removeItem(storageUsuarioKey); } catch (e4) {}
            }
        }
    } catch (e3) {}

    aplicarFiltroTipoVisual(obterTipoInicioSelecionado());
    atualizarResumoTodasTabelas();
    sincronizarPainelOperacao();
    
    // Função para salvar conferência via AJAX
    function salvarConferencia(lote, regional, posto, dataexp, qtd, codbar, usuario) {
        var formData = new FormData();
        formData.append('salvar_lote_ajax', '1');
        formData.append('lote', lote);
        formData.append('regional', regional);
        formData.append('posto', posto);
        formData.append('dataexp', dataexp);
        formData.append('qtd', qtd);
        formData.append('codbar', codbar);
        formData.append('usuario', usuario);
        
        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if (!data.sucesso) {
                console.error('Erro ao salvar:', data.erro);
            }
        })
        .catch(function(error) {
            console.error('Erro AJAX:', error);
        });
    }
    
    function normalizarRegionalValor(valor) {
        var d = String(valor || '').replace(/\D+/g, '');
        if (!d) return '';
        if (d.length > 3) d = d.substr(0, 3);
        return d.padStart(3, '0');
    }

    function obterRegionalLinha(linha) {
        if (!linha) return '';
        var v = linha.getAttribute('data-regional') || '';
        var n = normalizarRegionalValor(v);
        if (!n) {
            n = normalizarRegionalValor(linha.getAttribute('data-pt-group') || '');
        }
        if (!n) {
            n = normalizarRegionalValor(linha.getAttribute('data-posto') || '');
        }
        return n;
    }

    function processarLeituraCodigo(valorBruto) {
        if (!input) return;
        if (modoConsulta) {
            if (mensagemLeitura) {
                mensagemLeitura.textContent = 'Modo consulta ativo.';
            }
            input.value = '';
            return;
        }
        var valor = (valorBruto || '').trim();
        valor = valor.replace(/\D+/g, '');
        if (valor.length < 19) {
            return;
        }
        if (valor.length > 19) {
            valor = valor.substr(0, 19);
        }
        if (valor.length !== 19) {
            input.value = '';
            return;
        }

        var agoraLeitura = Date.now();
        if (codigosEmProcessamento[valor]) {
            return;
        }
        if (ultimoCodigoProcessado === valor && (agoraLeitura - ultimaLeituraProcessadaEm) < 700) {
            return;
        }
        codigosEmProcessamento[valor] = true;
        ultimoCodigoProcessado = valor;
        ultimaLeituraProcessadaEm = agoraLeitura;

        function finalizarProcessamento(limparInput) {
            delete codigosEmProcessamento[valor];
            if (limparInput) {
                input.value = '';
            }
        }

        desbloquearAudio();

        var postoLido = valor.substr(11, 3);
        if (postosBloqueadosMap[postoLido]) {
            var dadosBloq = postosBloqueadosMap[postoLido] || {};
            var motivoBloq = (dadosBloq.motivo || dadosBloq.nome || '').toString().trim();
            var textoVoz = motivoBloq ? motivoBloq : 'posto bloqueado';
            falarTexto(textoVoz);
            if (mensagemLeitura) {
                mensagemLeitura.innerHTML = '<strong>Posto bloqueado:</strong> ' + postoLido + ' ' + (motivoBloq || '');
            }
            finalizarProcessamento(true);
            return;
        }

        var linha = document.querySelector('tr[data-codigo="' + valor + '"]');

        if (!linha) {
            verificarPacoteOutraData(valor, function(resp) {
                if (resp && resp.success && resp.status === 'outra_data') {
                    if (mensagemLeitura) {
                        mensagemLeitura.innerHTML = '<strong>Pacote de outra data:</strong> ' + formatarDataBr(resp.data || '');
                    }
                    falarTexto('pacote de outra data');
                    finalizarProcessamento(true);
                    return;
                }

                var now = new Date();
                var mm = String(now.getMonth() + 1).padStart(2, '0');
                var dd = String(now.getDate()).padStart(2, '0');
                var dataPadrao = now.getFullYear() + '-' + mm + '-' + dd;

                var obj = {
                    codbar: valor,
                    lote: valor.substr(0, 8),
                    regional: valor.substr(8, 3),
                    posto: valor.substr(11, 3),
                    quantidade: parseInt(valor.substr(14, 5), 10) || 1,
                    dataexp: dataPadrao,
                    responsavel: ''
                };
                adicionarPacotePendente(obj);
                if (painelPacotesNovos) {
                    painelPacotesNovos.style.display = 'block';
                    painelPacotesNovos.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                tocarPacoteNaoEncontrado();
                if (mensagemLeitura) {
                    mensagemLeitura.innerHTML = '<strong>Pacote não encontrado:</strong> adicionado à lista pendente.';
                }
                finalizarProcessamento(true);
            });
            return;
        }

        if (!usuarioAtual) {
            alert('Informe o responsável da conferência para iniciar.');
            if (overlayUsuario) { overlayUsuario.style.display = 'flex'; }
            if (conteudoPagina) { conteudoPagina.classList.add('page-locked'); }
            if (usuarioInputModal) { usuarioInputModal.focus(); }
            finalizarProcessamento(true);
            return;
        }

        if (!tipoEscolhido) {
            if (overlayTipo) overlayTipo.style.display = 'flex';
            finalizarProcessamento(true);
            return;
        }

        var tipoSelecionado = obterTipoInicioSelecionado();
        var modoTodos = tipoSelecionado === 'todos';
        var regionalDoPacote = obterRegionalLinha(linha);
        var regionalDoPacoteNorm = normalizarRegionalValor(regionalDoPacote);
        var isPoupaTempo = linha.getAttribute('data-ispt') === '1';
        var tipoPacote = isPoupaTempo ? 'poupatempo' : 'correios';

        if (linha.classList.contains('confirmado')) {
            enfileirarSom(pacoteJaConferido);
            destacarChipOperacao(linha.getAttribute('data-codigo') || valor);
            finalizarProcessamento(true);
            return;
        }

        var somAlerta = null;
        var podeConferir = true;

        if (podeConferir && tipoPacote === 'correios' && tipoAtual === 'correios') {
            var regionalAtualNormCheck = normalizarRegionalValor(regionalAtual);
            if (regionalAtualNormCheck && regionalDoPacoteNorm && regionalDoPacoteNorm !== regionalAtualNormCheck) {
                somAlerta = pacoteOutraRegional;
                podeConferir = false;
            }
        }

        if (ultimaRegionalLida && ultimoTipoLido === tipoPacote && tipoPacote === 'correios' && regionalDoPacoteNorm && regionalDoPacoteNorm !== ultimaRegionalLida) {
            somAlerta = pacoteOutraRegional;
            podeConferir = false;
        }

        if (podeConferir && !primeiroConferido) {
            tipoAtual = tipoSelecionado;
            if (modoTodos || tipoAtual === tipoPacote) {
                tipoAtual = tipoPacote;
                regionalAtual = regionalDoPacoteNorm || regionalDoPacote;
            } else {
                podeConferir = false;
                avisarIncompatibilidadeTipo(tipoPacote);
            }
            if (podeConferir) {
                primeiroConferido = true;
            }
        } else if (!modoTodos && podeConferir && tipoAtual === 'correios' && tipoPacote === 'poupatempo') {
            somAlerta = postoPoupaTempo;
            podeConferir = false;
            if (mensagemLeitura) {
                mensagemLeitura.innerHTML = '<strong>Posto do Poupa Tempo:</strong> altere o tipo para Poupa Tempo ou Todos.';
            }
            falarTexto('posto do poupa tempo');
        } else if (!modoTodos && podeConferir && tipoAtual === 'poupatempo' && tipoPacote === 'correios') {
            somAlerta = pertenceCorreios;
            podeConferir = false;
            if (mensagemLeitura) {
                mensagemLeitura.innerHTML = '<strong>Posto dos Correios:</strong> altere o tipo para Correios ou Todos.';
            }
            falarTexto('posto dos correios');
        } else if (!modoTodos && podeConferir && regionalDoPacoteNorm && regionalDoPacoteNorm !== normalizarRegionalValor(regionalAtual) && tipoPacote === tipoAtual) {
            somAlerta = pacoteOutraRegional;
            podeConferir = false;
        }

        if (podeConferir && tipoPacote === 'poupatempo') {
            var totalPT = document.querySelectorAll('tbody tr[data-ispt="1"]').length;
            if (totalPT === 1 && !somAlerta) {
                somAlerta = postoPoupaTempo;
            }
        }

        if (!podeConferir) {
            if (somAlerta) {
                enfileirarSom(somAlerta);
            }
            finalizarProcessamento(true);
            return;
        }

        linha.classList.add('confirmado');
        var conferidoAgora = formatarDataHoraAtual();
        linha.setAttribute('data-conferido-em', conferidoAgora);
        var tdConf = linha.querySelector('.col-conferido-em');
        if (tdConf) tdConf.textContent = conferidoAgora;
        var chipPrincipal = atualizarChipOperacaoPorCodigo(linha.getAttribute('data-codigo') || valor, true);
        if (chipPrincipal) {
            chipPrincipal.setAttribute('data-conferido-em', conferidoAgora);
        }
        tipoAtual = tipoPacote;
        if (tipoPacote === 'correios') {
            regionalAtual = regionalDoPacoteNorm || regionalDoPacote;
        }
        atualizarResumoTabela(linha.closest('table'));

        if (!muteBeep || !muteBeep.checked) {
            enfileirarSom(beep);
        }
        if (somAlerta) {
            enfileirarSom(somAlerta);
        }

        if (mensagemLeitura) {
            mensagemLeitura.textContent = '';
        }

        var ultimas = document.querySelectorAll('tr.ultimo-lido');
        for (var u = 0; u < ultimas.length; u++) {
            ultimas[u].classList.remove('ultimo-lido');
        }
        linha.classList.add('ultimo-lido');

        centralizarElemento(linha);
        destacarChipOperacao(linha.getAttribute('data-codigo') || valor);

        ultimaRegionalLida = regionalDoPacoteNorm || regionalDoPacote;
        ultimoTipoLido = tipoPacote;

        if (usuarioAtual) {
            var lote = linha.getAttribute('data-lote');
            var regional = linha.getAttribute('data-regional');
            var posto = linha.getAttribute('data-posto');
            var dataexp = linha.getAttribute('data-data-sql') || linha.getAttribute('data-data');
            var qtd = linha.getAttribute('data-qtd');
            var codbar = linha.getAttribute('data-codigo');
            salvarConferencia(lote, regional, posto, dataexp, qtd, codbar, usuarioAtual);
        }

        var grupoAtual = null;
        var todasLinhas = document.querySelectorAll('tbody tr');
        var linhasDoGrupo = [];

        if (tipoAtual === 'poupatempo') {
            grupoAtual = linha.getAttribute('data-pt-group') || linha.getAttribute('data-posto');
            for (var i = 0; i < todasLinhas.length; i++) {
                if (todasLinhas[i].getAttribute('data-ispt') === '1' &&
                    (todasLinhas[i].getAttribute('data-pt-group') === grupoAtual || todasLinhas[i].getAttribute('data-posto') === grupoAtual)) {
                    linhasDoGrupo.push(todasLinhas[i]);
                }
            }
        } else {
            var regionalAtualNorm = normalizarRegionalValor(regionalAtual || regionalDoPacoteNorm);
            if (regionalAtualNorm === '000' || regionalAtualNorm === '999' || regionalAtualNorm === '001') {
                grupoAtual = linha.getAttribute('data-posto');
                for (var i2 = 0; i2 < todasLinhas.length; i2++) {
                    if (obterRegionalLinha(todasLinhas[i2]) === regionalAtualNorm &&
                        todasLinhas[i2].getAttribute('data-ispt') !== '1' &&
                        todasLinhas[i2].getAttribute('data-posto') === grupoAtual) {
                        linhasDoGrupo.push(todasLinhas[i2]);
                    }
                }
            } else {
                grupoAtual = regionalAtualNorm;
                for (var i3 = 0; i3 < todasLinhas.length; i3++) {
                    if (obterRegionalLinha(todasLinhas[i3]) === regionalAtualNorm &&
                        todasLinhas[i3].getAttribute('data-ispt') !== '1') {
                        linhasDoGrupo.push(todasLinhas[i3]);
                    }
                }
            }
        }

        var conferidosDoGrupo = 0;
        for (var j = 0; j < linhasDoGrupo.length; j++) {
            if (linhasDoGrupo[j].classList.contains('confirmado')) {
                conferidosDoGrupo++;
            }
        }

        if (conferidosDoGrupo === linhasDoGrupo.length && linhasDoGrupo.length > 0) {
            enfileirarSom(concluido);
            regionalAtual = null;
            tipoAtual = null;
            primeiroConferido = false;
        }

        finalizarProcessamento(true);
    }

    window.processarLeituraCodigo = processarLeituraCodigo;

    // Scanner de código de barras
    if (input) {
        input.addEventListener("input", function() {
            processarLeituraCodigo(input.value);
        });
        input.addEventListener("change", function() {
            processarLeituraCodigo(input.value);
        });
        input.addEventListener("paste", function() {
            setTimeout(function() {
                processarLeituraCodigo(input.value);
            }, 0);
        });
        input.addEventListener("keydown", function(e) {
            if (e.keyCode === 13) {
                e.preventDefault();
                processarLeituraCodigo(input.value);
            }
        });
        setInterval(function() {
            if (!input) return;
            var valorAtual = (input.value || '').trim();
            if (!valorAtual || valorAtual === ultimoCodLido) return;
            ultimoCodLido = valorAtual;
            processarLeituraCodigo(valorAtual);
            if ((input.value || '').trim() === '') {
                ultimoCodLido = '';
            }
        }, 300);
    }

    var scanBuffer = '';
    var scanTimer = null;
    document.addEventListener('keydown', function(e) {
        if (!e) return;
        var alvo = e.target;
        if (alvo && (alvo.id === 'usuario_conf_modal' || alvo.id === 'pacote_lote' || alvo.id === 'pacote_regional' || alvo.id === 'pacote_posto' || alvo.id === 'pacote_qtd' || alvo.id === 'pacote_dataexp' || alvo.id === 'pacote_responsavel')) {
            return;
        }
        if (e.keyCode === 13) {
            if (scanBuffer.length >= 19) {
                processarLeituraCodigo(scanBuffer);
                scanBuffer = '';
            }
            return;
        }
        var digit = null;
        var k = e.key;
        if (k && k.length === 1 && k >= '0' && k <= '9') {
            digit = k;
        } else if (e.keyCode >= 48 && e.keyCode <= 57) {
            digit = String.fromCharCode(e.keyCode);
        } else if (e.keyCode >= 96 && e.keyCode <= 105) {
            digit = String(e.keyCode - 96);
        }
        if (!digit) return;
        scanBuffer += digit;
        if (scanTimer) clearTimeout(scanTimer);
        scanTimer = setTimeout(function() { scanBuffer = ''; }, 300);
        if (scanBuffer.length >= 19) {
            processarLeituraCodigo(scanBuffer);
            scanBuffer = '';
        }
    });
    
    // Resetar conferência
    btnResetar.addEventListener("click", function() {
        if (confirm("Tem certeza que deseja reiniciar a conferência? Isso irá APAGAR todos os dados conferidos do banco!")) {
            // Obter datas filtradas
            var checkboxes = document.querySelectorAll('.filtro-datas input[type="checkbox"]:checked');
            var datas = [];
            
            for (var i = 0; i < checkboxes.length; i++) {
                datas.push(checkboxes[i].value);
            }
            
            // Resetar visualmente
            var trsConfirmados = document.querySelectorAll("tr.confirmado");
            for (var j = 0; j < trsConfirmados.length; j++) {
                trsConfirmados[j].classList.remove("confirmado");
            }
            atualizarResumoTodasTabelas();
            sincronizarPainelOperacao();
            
            regionalAtual = null;
            tipoAtual = null; // v9.2: Reseta tipo
            primeiroConferido = false; // v9.2: Reseta flag
            input.value = "";
            input.focus();
            
            // Excluir do banco via AJAX
            if (datas.length > 0) {
                var formData = new FormData();
                formData.append('excluir_lote_ajax', '1');
                formData.append('datas', datas.join(','));
                
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    if (data.sucesso) {
                        alert('Conferências resetadas com sucesso!');
                    } else {
                        console.error('Erro ao resetar:', data.erro);
                    }
                })
                .catch(function(error) {
                    console.error('Erro AJAX:', error);
                });
            }
        }
    });

    window.toggleIndicadorDias = function() {
        var el = document.getElementById('indicador-dias');
        if (!el) return;
        if (el.classList.contains('collapsed')) {
            el.classList.remove('collapsed');
        } else {
            el.classList.add('collapsed');
        }
    };

    function atualizarMapaBloqueados() {
        postosBloqueadosMap = {};
        for (var i = 0; i < postosBloqueados.length; i++) {
            postosBloqueadosMap[postosBloqueados[i].posto] = postosBloqueados[i];
        }
    }

    function renderizarPostosBloqueados() {
        if (!listaPostosBloqueados) return;
        listaPostosBloqueados.innerHTML = '';
        for (var i = 0; i < postosBloqueados.length; i++) {
            var p = postosBloqueados[i];
            var div = document.createElement('div');
            div.className = 'bloqueio-item';
            div.setAttribute('data-posto', p.posto);
            var motivoTxt = (p.motivo || p.nome || '').toString().trim();
            div.innerHTML = '<div><span class="posto">' + p.posto + '</span> ' + motivoTxt + '</div>' +
                '<button type="button" class="btn-acao btn-cancelar" data-remover="' + p.posto + '">Remover</button>';
            listaPostosBloqueados.appendChild(div);
        }
        atualizarMapaBloqueados();
    }

    if (btnAdicionarBloqueio) {
        btnAdicionarBloqueio.addEventListener('click', function() {
            var posto = postoBloqueioNumero ? postoBloqueioNumero.value.trim() : '';
            var motivo = postoBloqueioNome ? postoBloqueioNome.value.trim() : '';
            var responsavel = postoBloqueioResponsavel ? postoBloqueioResponsavel.value.trim() : '';
            if (!posto) {
                alert('Informe o numero do posto.');
                if (postoBloqueioNumero) postoBloqueioNumero.focus();
                return;
            }
            if (!motivo) {
                alert('Informe o motivo do bloqueio.');
                if (postoBloqueioNome) postoBloqueioNome.focus();
                return;
            }
            if (!responsavel) {
                alert('Informe o responsavel pelo bloqueio.');
                if (postoBloqueioResponsavel) postoBloqueioResponsavel.focus();
                return;
            }
            var formData = new FormData();
            formData.append('salvar_posto_bloqueado', '1');
            formData.append('posto', posto);
            formData.append('motivo', motivo);
            formData.append('responsavel', responsavel);
            fetch(window.location.href, { method: 'POST', body: formData })
                .then(function(resp){ return resp.json(); })
                .then(function(data){
                    if (data && data.success) {
                        postosBloqueados.push({ posto: posto, nome: motivo, motivo: motivo });
                        renderizarPostosBloqueados();
                        if (postoBloqueioNumero) postoBloqueioNumero.value = '';
                        if (postoBloqueioNome) postoBloqueioNome.value = '';
                        if (postoBloqueioResponsavel) postoBloqueioResponsavel.value = '';
                    } else {
                        alert(data && data.erro ? data.erro : 'Erro ao salvar posto bloqueado.');
                    }
                })
                .catch(function(){ alert('Erro ao salvar posto bloqueado.'); });
        });
    }

    if (listaPostosBloqueados) {
        listaPostosBloqueados.addEventListener('click', function(e) {
            var target = e.target;
            if (!target) return;
            var posto = target.getAttribute('data-remover');
            if (!posto) return;
            var responsavel = postoDesbloqueioResponsavel ? postoDesbloqueioResponsavel.value.trim() : '';
            var motivo = postoDesbloqueioMotivo ? postoDesbloqueioMotivo.value.trim() : '';
            if (!responsavel) {
                alert('Informe o responsavel pelo desbloqueio.');
                if (postoDesbloqueioResponsavel) postoDesbloqueioResponsavel.focus();
                return;
            }
            var formData = new FormData();
            formData.append('excluir_posto_bloqueado', '1');
            formData.append('posto', posto);
            formData.append('responsavel', responsavel);
            formData.append('motivo', motivo);
            fetch(window.location.href, { method: 'POST', body: formData })
                .then(function(resp){ return resp.json(); })
                .then(function(data){
                    if (data && data.success) {
                        postosBloqueados = postosBloqueados.filter(function(p){ return p.posto !== posto; });
                        renderizarPostosBloqueados();
                        if (postoDesbloqueioMotivo) postoDesbloqueioMotivo.value = '';
                    } else {
                        alert(data && data.erro ? data.erro : 'Erro ao remover posto bloqueado.');
                    }
                })
                .catch(function(){ alert('Erro ao remover posto bloqueado.'); });
        });
    }

    renderizarPostosBloqueados();
    } catch (e) {
        try { console.error('Erro ao iniciar conferência', e); } catch (e2) {}
    }
}

window.iniciarConferenciaPacotes = iniciarConferenciaPacotes;

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', iniciarConferenciaPacotes);
} else {
    iniciarConferenciaPacotes();
}
</script>

<script>
(function() {
    function confirmarResponsavelFallback() {
        var input = document.getElementById('usuario_conf_modal');
        var nome = input ? input.value.trim() : '';
        if (!nome) {
            alert('Informe o responsável da conferência.');
            if (input) input.focus();
            return;
        }
        var badge = document.getElementById('usuarioBadge');
        if (badge) badge.textContent = nome;
        var overlay = document.getElementById('overlayUsuario');
        if (overlay) overlay.style.display = 'none';
        var conteudo = document.getElementById('conteudoPagina');
        if (conteudo) conteudo.classList.remove('page-locked');
        try {
            sessionStorage.setItem('conferencia_responsavel', nome);
        } catch (e) {}
        var overlayTipo = document.getElementById('overlayTipo');
        if (overlayTipo) overlayTipo.style.display = 'flex';
    }

    var btn = document.getElementById('btnConfirmarUsuario');
    if (btn && !btn.__fallbackBound) {
        btn.addEventListener('click', confirmarResponsavelFallback);
        btn.__fallbackBound = true;
    }

    var input = document.getElementById('usuario_conf_modal');
    if (input && !input.__fallbackBound) {
        input.addEventListener('keydown', function(e) {
            if (e.keyCode === 13) {
                e.preventDefault();
                confirmarResponsavelFallback();
            }
        });
        input.__fallbackBound = true;
    }
})();
</script>

<script>
(function() {
    function selecionarTipoFallback(tipo) {
        if (!tipo) return;
        var radios = document.querySelectorAll('input[name="tipo_inicio"]');
        for (var i = 0; i < radios.length; i++) {
            if (radios[i].value === tipo) {
                radios[i].checked = true;
                break;
            }
        }
        var overlay = document.getElementById('overlayTipo');
        if (overlay) overlay.style.display = 'none';
        try {
            sessionStorage.setItem('conferencia_tipo_inicio', tipo);
        } catch (e) {}
        var input = document.getElementById('codigo_barras');
        if (input) input.focus();
    }

    var overlayTipo = document.getElementById('overlayTipo');
    if (overlayTipo && !overlayTipo.__fallbackBound) {
        overlayTipo.addEventListener('click', function(e) {
            var target = e.target;
            if (!target) return;
            var tipo = target.getAttribute('data-tipo');
            if (!tipo && target.parentNode && target.parentNode.getAttribute) {
                tipo = target.parentNode.getAttribute('data-tipo');
            }
            if (tipo) {
                selecionarTipoFallback(tipo);
            }
        });
        overlayTipo.__fallbackBound = true;
    }
})();
</script>

</body>
</html>
