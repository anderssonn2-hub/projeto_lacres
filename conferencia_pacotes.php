<?php
/* conferencia_pacotes.php — v0.9.25.16
 * CHANGELOG v9.25.14:
 * - [AJUSTE] Áudio concluído por regional no fluxo Correios
 * - [NOVO] Créditos finais estilo filme com trilha final_conferencia.mp3
 * - [NOVO] Opção para desativar créditos finais na tela inicial
 * - [AJUSTE] Versão sincronizada para 0.9.25.14
 *
 * CHANGELOG v9.25.12:
 * - [NOVO] Controle remoto por celular com comandos de malote sincronizados via servidor
 * - [NOVO] Canal remoto para operar lacres e etiqueta sem depender de voz no navegador
 *
 * CHANGELOG v9.25.11:
 * - [NOVO] Comandos de voz para armar e preencher lacres/etiqueta no painel de malotes
 * - [NOVO] Prévia dinâmica do ofício em segunda tela via sincronização local do navegador
 *
 * CHANGELOG v9.25.10:
 * - [NOVO] Grupos de malote IIPR e malote Correios para separar linhas repetidas do mesmo posto
 * - [NOVO] Persistência dos grupos no modo chips para futura renderização fiel do ofício
 *
 * CHANGELOG v9.25.9:
 * - [NOVO] Atribuição de lotes a lacres IIPR e malotes Correios no modo chips
 * - [NOVO] Persistência por lote em conferencia_pacotes_lacres para reaproveitar no ofício
 * - [AJUSTE] Reset da conferência também limpa vínculos do período filtrado
 *
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
$controle_canal = isset($_GET['canal_controle']) ? preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)$_GET['canal_controle']) : 'principal';
if ($controle_canal === '') {
    $controle_canal = 'principal';
}

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

function normalizarListaDatasPacotes($datasRaw) {
    $datas = array();
    if (!is_array($datasRaw)) {
        return $datas;
    }
    foreach ($datasRaw as $d) {
        $d = trim((string)$d);
        if ($d === '') {
            continue;
        }
        $normalizada = normalizarDataSqlPacote($d);
        if ($normalizada === '') {
            $normalizada = $d;
        }
        if (!in_array($normalizada, $datas, true)) {
            $datas[] = $normalizada;
        }
    }
    return $datas;
}

function montarChaveLinhaResumoPreview($linha, $indice) {
    if (isset($linha['row_key']) && trim((string)$linha['row_key']) !== '') {
        return trim((string)$linha['row_key']);
    }
    $grupoCorreios = isset($linha['grupo_correios']) ? trim((string)$linha['grupo_correios']) : '';
    if ($grupoCorreios !== '') {
        return 'gc:' . $grupoCorreios;
    }
    $grupoIipr = isset($linha['grupo_iipr']) ? trim((string)$linha['grupo_iipr']) : '';
    if ($grupoIipr !== '') {
        return 'gi:' . $grupoIipr;
    }
    $posto = isset($linha['posto']) ? trim((string)$linha['posto']) : '';
    $regional = isset($linha['regional_codigo']) ? trim((string)$linha['regional_codigo']) : '';
    return 'ln:' . $posto . ':' . $regional . ':' . ((int)$indice);
}

function normalizarGruposResumoPreview($valor) {
    $saida = array();
    if (is_array($valor)) {
        foreach ($valor as $item) {
            $item = trim((string)$item);
            if ($item !== '' && !in_array($item, $saida, true)) {
                $saida[] = $item;
            }
        }
        return $saida;
    }
    $valor = trim((string)$valor);
    if ($valor === '') {
        return $saida;
    }
    $partes = explode(',', $valor);
    foreach ($partes as $parte) {
        $parte = trim((string)$parte);
        if ($parte !== '' && !in_array($parte, $saida, true)) {
            $saida[] = $parte;
        }
    }
    return $saida;
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

    // v9.25.9: Vinculo fino entre lote conferido e malotes/lacres usados no modo chips
    $pdo->exec("CREATE TABLE IF NOT EXISTS conferencia_pacotes_lacres (
        id INT NOT NULL AUTO_INCREMENT,
        codbar VARCHAR(25) NOT NULL,
        lote VARCHAR(8) NOT NULL,
        regional VARCHAR(3) NOT NULL,
        posto VARCHAR(10) NOT NULL,
        dataexp DATE NOT NULL,
        qtd INT(5) NOT NULL DEFAULT 0,
        lacre_iipr INT(11) DEFAULT NULL,
        grupo_iipr VARCHAR(40) DEFAULT NULL,
        lacre_correios INT(11) DEFAULT NULL,
        grupo_correios VARCHAR(40) DEFAULT NULL,
        etiqueta_correios VARCHAR(35) DEFAULT NULL,
        usuario_lacre VARCHAR(120) DEFAULT NULL,
        atualizado_em DATETIME NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY uniq_codbar (codbar),
        KEY idx_periodo (dataexp),
        KEY idx_posto_lote (posto, lote)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8");

    $colsGrupoIipr = $pdo->query("SHOW COLUMNS FROM conferencia_pacotes_lacres LIKE 'grupo_iipr'")->fetchAll();
    if (count($colsGrupoIipr) === 0) {
        $pdo->exec("ALTER TABLE conferencia_pacotes_lacres ADD COLUMN grupo_iipr VARCHAR(40) DEFAULT NULL AFTER lacre_iipr");
    }
    $colsGrupoCorreios = $pdo->query("SHOW COLUMNS FROM conferencia_pacotes_lacres LIKE 'grupo_correios'")->fetchAll();
    if (count($colsGrupoCorreios) === 0) {
        $pdo->exec("ALTER TABLE conferencia_pacotes_lacres ADD COLUMN grupo_correios VARCHAR(40) DEFAULT NULL AFTER lacre_correios");
    }

    $colsDespLoteGrupoIipr = $pdo->query("SHOW COLUMNS FROM ciDespachoLotes LIKE 'grupo_iipr'")->fetchAll();
    if (count($colsDespLoteGrupoIipr) === 0) {
        $pdo->exec("ALTER TABLE ciDespachoLotes ADD COLUMN grupo_iipr VARCHAR(40) DEFAULT NULL AFTER etiquetaiipr");
    }
    $colsDespLoteGrupoCorreios = $pdo->query("SHOW COLUMNS FROM ciDespachoLotes LIKE 'grupo_correios'")->fetchAll();
    if (count($colsDespLoteGrupoCorreios) === 0) {
        $pdo->exec("ALTER TABLE ciDespachoLotes ADD COLUMN grupo_correios VARCHAR(40) DEFAULT NULL AFTER etiquetacorreios");
    }
    $colsDespLoteEtiqueta = $pdo->query("SHOW COLUMNS FROM ciDespachoLotes LIKE 'etiqueta_correios'")->fetchAll();
    if (count($colsDespLoteEtiqueta) === 0) {
        $pdo->exec("ALTER TABLE ciDespachoLotes ADD COLUMN etiqueta_correios VARCHAR(35) DEFAULT NULL AFTER grupo_correios");
    }

    if (isset($_POST['salvar_oficio_correios_preview_ajax'])) {
        header('Content-Type: application/json; charset=utf-8');

        $usuario = isset($_POST['usuario']) ? trim((string)$_POST['usuario']) : '';
        if ($usuario === '') {
            $usuario = 'Equipe de Conferencia';
        }

        $modoOficio = isset($_POST['modo_oficio']) ? strtolower(trim((string)$_POST['modo_oficio'])) : 'sobrescrever';
        if ($modoOficio !== 'novo') {
            $modoOficio = 'sobrescrever';
        }
        $idSobrescrever = isset($_POST['id_oficio_sobrescrever']) ? (int)$_POST['id_oficio_sobrescrever'] : 0;

        $datasRecebidas = array();
        if (isset($_POST['datas_json'])) {
            $datasRecebidas = json_decode((string)$_POST['datas_json'], true);
        }
        if (!is_array($datasRecebidas)) {
            $datasRecebidas = array();
        }
        $datasSql = normalizarListaDatasPacotes($datasRecebidas);
        if (empty($datasSql) && isset($_POST['datas_str'])) {
            $datasSql = normalizarListaDatasPacotes(explode(',', (string)$_POST['datas_str']));
        }

        $snapshot = array();
        if (isset($_POST['snapshot_oficio'])) {
            $snapshot = json_decode((string)$_POST['snapshot_oficio'], true);
        }
        if (!is_array($snapshot)) {
            $snapshot = array();
        }
        $linhasResumo = isset($snapshot['resumo']) && is_array($snapshot['resumo']) ? $snapshot['resumo'] : array();

        if (empty($datasSql)) {
            die(json_encode(array('success' => false, 'erro' => 'Nenhuma data valida foi informada para o oficio.')));
        }
        if (empty($linhasResumo)) {
            die(json_encode(array('success' => false, 'erro' => 'Nao ha linhas prontas para gravar o oficio.')));
        }

        $datasStr = implode(',', $datasSql);

        try {
            $stUlt = $pdo->prepare("SELECT id FROM ciDespachos WHERE grupo = 'CORREIOS' ORDER BY id DESC LIMIT 1");
            $stUlt->execute();
            $ultimoIdDespachoCorreios = (int)$stUlt->fetchColumn();
            $stUlt = null;

            if ($modoOficio === 'novo') {
                $hash = sha1('CORREIOS|' . $datasStr . '|' . time() . '|' . mt_rand());
                $stCab = $pdo->prepare("INSERT INTO ciDespachos (usuario, grupo, datas_str, hash_chave, ativo, obs) VALUES (?,?,?,?,1,?)");
                $stCab->execute(array($usuario, 'CORREIOS', $datasStr, $hash, 'Origem: conferencia_pacotes_previa'));
                $idDespacho = (int)$pdo->lastInsertId();
                $stCab = null;
            } else {
                $idDespacho = $idSobrescrever > 0 ? $idSobrescrever : $ultimoIdDespachoCorreios;
                if ($idDespacho > 0) {
                    $stCheck = $pdo->prepare("SELECT id FROM ciDespachos WHERE id = ? AND grupo = 'CORREIOS' LIMIT 1");
                    $stCheck->execute(array($idDespacho));
                    $idExistente = (int)$stCheck->fetchColumn();
                    $stCheck = null;
                    if ($idExistente <= 0) {
                        die(json_encode(array('success' => false, 'erro' => 'Numero de oficio nao encontrado para sobrescrever.')));
                    }
                    $stCab = $pdo->prepare("UPDATE ciDespachos SET usuario = ?, grupo = 'CORREIOS', datas_str = ?, ativo = 1, obs = ? WHERE id = ?");
                    $stCab->execute(array($usuario, $datasStr, 'Origem: conferencia_pacotes_previa', $idDespacho));
                    $stCab = null;

                    $stDelItens = $pdo->prepare("DELETE FROM ciDespachoItens WHERE id_despacho = ?");
                    $stDelItens->execute(array($idDespacho));
                    $stDelItens = null;

                    $stDelLotes = $pdo->prepare("DELETE FROM ciDespachoLotes WHERE id_despacho = ?");
                    $stDelLotes->execute(array($idDespacho));
                    $stDelLotes = null;
                } else {
                    $hash = sha1('CORREIOS|' . $datasStr);
                    $stCab = $pdo->prepare("INSERT INTO ciDespachos (usuario, grupo, datas_str, hash_chave, ativo, obs) VALUES (?,?,?,?,1,?)");
                    $stCab->execute(array($usuario, 'CORREIOS', $datasStr, $hash, 'Origem: conferencia_pacotes_previa'));
                    $idDespacho = (int)$pdo->lastInsertId();
                    $stCab = null;
                }
            }

            $placeholdersDatas = implode(',', array_fill(0, count($datasSql), '?'));
            $sqlSelectLacres = "SELECT codbar, posto, lote, qtd, dataexp, usuario_lacre, lacre_iipr, grupo_iipr, lacre_correios, grupo_correios, etiqueta_correios\n                FROM conferencia_pacotes_lacres\n                WHERE grupo_correios = ? AND dataexp IN ($placeholdersDatas)\n                ORDER BY CAST(posto AS UNSIGNED), CAST(lote AS UNSIGNED), id";
            $stSelLacres = $pdo->prepare($sqlSelectLacres);

            $sqlUpdateEtiqueta = "UPDATE conferencia_pacotes_lacres\n                SET etiqueta_correios = ?, usuario_lacre = ?, atualizado_em = NOW()\n                WHERE grupo_correios = ? AND dataexp IN ($placeholdersDatas)";
            $stUpdEtiqueta = $pdo->prepare($sqlUpdateEtiqueta);

            $stInsLote = $pdo->prepare("\n                INSERT INTO ciDespachoLotes\n                    (id_despacho, posto, lote, quantidade, data_carga, responsaveis, etiquetaiipr, grupo_iipr, etiquetacorreios, grupo_correios, etiqueta_correios)\n                VALUES\n                    (?,?,?,?,?,?,?,?,?,?,?)\n            ");

            $gruposInseridos = array();
            $linhasGravadas = 0;
            $lotesGravados = 0;

            for ($iLinha = 0; $iLinha < count($linhasResumo); $iLinha++) {
                $linha = is_array($linhasResumo[$iLinha]) ? $linhasResumo[$iLinha] : array();
                $grupoCorreios = isset($linha['grupo_correios']) ? trim((string)$linha['grupo_correios']) : '';
                $gruposCorreiosLinha = normalizarGruposResumoPreview(isset($linha['grupos_correios']) ? $linha['grupos_correios'] : $grupoCorreios);
                if (empty($gruposCorreiosLinha) && $grupoCorreios !== '') {
                    $gruposCorreiosLinha[] = $grupoCorreios;
                }
                $grupoIipr = isset($linha['grupo_iipr']) ? trim((string)$linha['grupo_iipr']) : '';
                $postoLinha = isset($linha['posto']) ? trim((string)$linha['posto']) : '';
                $etiquetaLinha = isset($linha['etiqueta_correios']) ? substr(trim((string)$linha['etiqueta_correios']), 0, 35) : '';
                $lacreIiprLinha = isset($linha['lacre_iipr']) ? trim((string)$linha['lacre_iipr']) : '';
                $lacreCorreiosLinha = isset($linha['lacre_correios']) ? trim((string)$linha['lacre_correios']) : '';

                if (!empty($gruposCorreiosLinha)) {
                    for ($iGrupoUpdate = 0; $iGrupoUpdate < count($gruposCorreiosLinha); $iGrupoUpdate++) {
                        $paramsUpdate = array($etiquetaLinha === '' ? null : $etiquetaLinha, $usuario, $gruposCorreiosLinha[$iGrupoUpdate]);
                        for ($iData = 0; $iData < count($datasSql); $iData++) {
                            $paramsUpdate[] = $datasSql[$iData];
                        }
                        $stUpdEtiqueta->execute($paramsUpdate);
                    }
                }

                $chaveGrupo = !empty($gruposCorreiosLinha) ? implode('|', $gruposCorreiosLinha) : montarChaveLinhaResumoPreview($linha, $iLinha);
                if (isset($gruposInseridos[$chaveGrupo])) {
                    continue;
                }
                $gruposInseridos[$chaveGrupo] = true;
                $linhasGravadas++;

                $rowsLacres = array();
                if (!empty($gruposCorreiosLinha)) {
                    $rowsLacresMap = array();
                    for ($iGrupoSel = 0; $iGrupoSel < count($gruposCorreiosLinha); $iGrupoSel++) {
                        $paramsSelect = array($gruposCorreiosLinha[$iGrupoSel]);
                        for ($iDataSel = 0; $iDataSel < count($datasSql); $iDataSel++) {
                            $paramsSelect[] = $datasSql[$iDataSel];
                        }
                        $stSelLacres->execute($paramsSelect);
                        $rowsGrupo = $stSelLacres->fetchAll(PDO::FETCH_ASSOC);
                        $stSelLacres->closeCursor();
                        for ($iRow = 0; $iRow < count($rowsGrupo); $iRow++) {
                            $rowGrupo = $rowsGrupo[$iRow];
                            $chaveRow = isset($rowGrupo['codbar']) && trim((string)$rowGrupo['codbar']) !== ''
                                ? trim((string)$rowGrupo['codbar'])
                                : (trim((string)$rowGrupo['posto']) . '|' . trim((string)$rowGrupo['lote']) . '|' . trim((string)$rowGrupo['grupo_iipr']));
                            $rowsLacresMap[$chaveRow] = $rowGrupo;
                        }
                    }
                    $rowsLacres = array_values($rowsLacresMap);
                }

                if (!empty($rowsLacres)) {
                    for ($iLacre = 0; $iLacre < count($rowsLacres); $iLacre++) {
                        $rowLacre = $rowsLacres[$iLacre];
                        $stInsLote->execute(array(
                            $idDespacho,
                            isset($rowLacre['posto']) ? $rowLacre['posto'] : $postoLinha,
                            isset($rowLacre['lote']) ? $rowLacre['lote'] : '',
                            isset($rowLacre['qtd']) ? (int)$rowLacre['qtd'] : 0,
                            isset($rowLacre['dataexp']) ? $rowLacre['dataexp'] : (isset($datasSql[0]) ? $datasSql[0] : null),
                            isset($rowLacre['usuario_lacre']) && trim((string)$rowLacre['usuario_lacre']) !== '' ? trim((string)$rowLacre['usuario_lacre']) : $usuario,
                            isset($rowLacre['lacre_iipr']) && $rowLacre['lacre_iipr'] !== '' ? (int)$rowLacre['lacre_iipr'] : null,
                            isset($rowLacre['grupo_iipr']) ? $rowLacre['grupo_iipr'] : ($grupoIipr === '' ? null : $grupoIipr),
                            isset($rowLacre['lacre_correios']) && $rowLacre['lacre_correios'] !== '' ? (int)$rowLacre['lacre_correios'] : null,
                            isset($rowLacre['grupo_correios']) ? $rowLacre['grupo_correios'] : ($grupoCorreios === '' ? null : $grupoCorreios),
                            $etiquetaLinha === '' ? null : $etiquetaLinha
                        ));
                        $lotesGravados++;
                    }
                } else {
                    $lotesLinha = array();
                    if (isset($linha['lotes']) && is_array($linha['lotes'])) {
                        $lotesLinha = $linha['lotes'];
                    }
                    $stInsLote->execute(array(
                        $idDespacho,
                        $postoLinha,
                        !empty($lotesLinha) ? implode(',', $lotesLinha) : '',
                        isset($linha['qtd_total']) ? (int)$linha['qtd_total'] : 0,
                        isset($datasSql[0]) ? $datasSql[0] : null,
                        $usuario,
                        $lacreIiprLinha === '' ? null : (int)preg_replace('/\D+/', '', $lacreIiprLinha),
                        $grupoIipr === '' ? null : $grupoIipr,
                        $lacreCorreiosLinha === '' ? null : (int)preg_replace('/\D+/', '', $lacreCorreiosLinha),
                        $grupoCorreios === '' ? null : $grupoCorreios,
                        $etiquetaLinha === '' ? null : $etiquetaLinha
                    ));
                    $lotesGravados++;
                }
            }

            $stSelLacres = null;
            $stUpdEtiqueta = null;
            $stInsLote = null;

            die(json_encode(array(
                'success' => true,
                'id_oficio' => $idDespacho,
                'numero_oficio' => $idDespacho,
                'linhas_gravadas' => $linhasGravadas,
                'lotes_gravados' => $lotesGravados,
                'modo_oficio' => $modoOficio
            )));
        } catch (Exception $e) {
            die(json_encode(array('success' => false, 'erro' => $e->getMessage())));
        }
    }

    // v9.25.13: Comandos remotos para o painel de malotes
    $pdo->exec("CREATE TABLE IF NOT EXISTS conferencia_pacotes_controle (
        id INT NOT NULL AUTO_INCREMENT,
        canal VARCHAR(40) NOT NULL,
        comando VARCHAR(40) NOT NULL,
        valor VARCHAR(120) DEFAULT NULL,
        valor_aux VARCHAR(120) DEFAULT NULL,
        usuario VARCHAR(120) DEFAULT NULL,
        criado_em DATETIME NOT NULL,
        processado_em DATETIME DEFAULT NULL,
        PRIMARY KEY (id),
        KEY idx_canal_processado (canal, processado_em, id)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8");

    $pdo->exec("CREATE TABLE IF NOT EXISTS conferencia_pacotes_controle_estado (
        canal VARCHAR(40) NOT NULL,
        usuario VARCHAR(120) DEFAULT NULL,
        posto VARCHAR(10) DEFAULT NULL,
        regional VARCHAR(120) DEFAULT NULL,
        resumo VARCHAR(255) DEFAULT NULL,
        lacre_iipr VARCHAR(20) DEFAULT NULL,
        lacre_correios VARCHAR(20) DEFAULT NULL,
        etiqueta_correios VARCHAR(35) DEFAULT NULL,
        atualizado_em DATETIME NOT NULL,
        PRIMARY KEY (canal)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8");

    if (isset($_POST['enviar_comando_remoto_ajax'])) {
        $canal = isset($_POST['canal']) ? preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)$_POST['canal']) : 'principal';
        $comando = isset($_POST['comando']) ? preg_replace('/[^a-z_]/', '', strtolower((string)$_POST['comando'])) : '';
        $valor = isset($_POST['valor']) ? substr(trim((string)$_POST['valor']), 0, 120) : '';
        $valorAux = isset($_POST['valor_aux']) ? substr(trim((string)$_POST['valor_aux']), 0, 120) : '';
        $usuario = isset($_POST['usuario']) ? substr(trim((string)$_POST['usuario']), 0, 120) : '';
        if ($canal === '') {
            $canal = 'principal';
        }
        if ($comando === '') {
            die(json_encode(array('success' => false, 'erro' => 'Comando obrigatorio')));
        }
        $stmt = $pdo->prepare("INSERT INTO conferencia_pacotes_controle (canal, comando, valor, valor_aux, usuario, criado_em) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute(array($canal, $comando, ($valor === '' ? null : $valor), ($valorAux === '' ? null : $valorAux), ($usuario === '' ? null : $usuario)));
        $id = (int)$pdo->lastInsertId();
        $stmt = null;
        $pdo = null;
        die(json_encode(array('success' => true, 'id' => $id)));
    }

    if (isset($_GET['buscar_comandos_remoto_ajax'])) {
        $canal = isset($_GET['canal']) ? preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)$_GET['canal']) : 'principal';
        if ($canal === '') {
            $canal = 'principal';
        }
        $stmt = $pdo->prepare("SELECT id, comando, valor, valor_aux, usuario, criado_em FROM conferencia_pacotes_controle WHERE canal = ? AND processado_em IS NULL ORDER BY id ASC LIMIT 30");
        $stmt->execute(array($canal));
        $comandos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $ids = array();
        foreach ($comandos as $cmd) {
            $ids[] = (int)$cmd['id'];
        }
        if (!empty($ids)) {
            $ph = implode(',', array_fill(0, count($ids), '?'));
            $stmtUpd = $pdo->prepare("UPDATE conferencia_pacotes_controle SET processado_em = NOW() WHERE id IN ($ph)");
            $stmtUpd->execute($ids);
            $stmtUpd = null;
        }
        $stmt = null;
        $pdo = null;
        die(json_encode(array('success' => true, 'comandos' => $comandos)));
    }

    if (isset($_POST['atualizar_estado_remoto_ajax'])) {
        $canal = isset($_POST['canal']) ? preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)$_POST['canal']) : 'principal';
        if ($canal === '') {
            $canal = 'principal';
        }
        $usuario = isset($_POST['usuario']) ? substr(trim((string)$_POST['usuario']), 0, 120) : '';
        $posto = isset($_POST['posto']) ? substr(trim((string)$_POST['posto']), 0, 10) : '';
        $regional = isset($_POST['regional']) ? substr(trim((string)$_POST['regional']), 0, 120) : '';
        $resumo = isset($_POST['resumo']) ? substr(trim((string)$_POST['resumo']), 0, 255) : '';
        $lacreIipr = isset($_POST['lacre_iipr']) ? substr(trim((string)$_POST['lacre_iipr']), 0, 20) : '';
        $lacreCorreios = isset($_POST['lacre_correios']) ? substr(trim((string)$_POST['lacre_correios']), 0, 20) : '';
        $etiquetaCorreios = isset($_POST['etiqueta_correios']) ? substr(trim((string)$_POST['etiqueta_correios']), 0, 35) : '';
        $stmt = $pdo->prepare("INSERT INTO conferencia_pacotes_controle_estado (canal, usuario, posto, regional, resumo, lacre_iipr, lacre_correios, etiqueta_correios, atualizado_em)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                usuario = VALUES(usuario),
                posto = VALUES(posto),
                regional = VALUES(regional),
                resumo = VALUES(resumo),
                lacre_iipr = VALUES(lacre_iipr),
                lacre_correios = VALUES(lacre_correios),
                etiqueta_correios = VALUES(etiqueta_correios),
                atualizado_em = NOW()");
        $stmt->execute(array(
            $canal,
            ($usuario === '' ? null : $usuario),
            ($posto === '' ? null : $posto),
            ($regional === '' ? null : $regional),
            ($resumo === '' ? null : $resumo),
            ($lacreIipr === '' ? null : $lacreIipr),
            ($lacreCorreios === '' ? null : $lacreCorreios),
            ($etiquetaCorreios === '' ? null : $etiquetaCorreios)
        ));
        $stmt = null;
        $pdo = null;
        die(json_encode(array('success' => true)));
    }

    if (isset($_GET['ler_estado_remoto_ajax'])) {
        $canal = isset($_GET['canal']) ? preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)$_GET['canal']) : 'principal';
        if ($canal === '') {
            $canal = 'principal';
        }
        $stmt = $pdo->prepare("SELECT canal, usuario, posto, regional, resumo, lacre_iipr, lacre_correios, etiqueta_correios, atualizado_em FROM conferencia_pacotes_controle_estado WHERE canal = ? LIMIT 1");
        $stmt->execute(array($canal));
        $estado = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = null;
        $pdo = null;
        die(json_encode(array('success' => true, 'estado' => $estado ? $estado : null)));
    }

    // Handler AJAX salvar
    if (isset($_POST['salvar_lote_ajax'])) {
        $lote = isset($_POST['lote']) ? trim((string)$_POST['lote']) : '';
        $regional = isset($_POST['regional']) ? trim((string)$_POST['regional']) : '';
        $posto = isset($_POST['posto']) ? trim((string)$_POST['posto']) : '';
        $dataexp = isset($_POST['dataexp']) ? trim((string)$_POST['dataexp']) : '';
        $qtd = isset($_POST['qtd']) ? (int)$_POST['qtd'] : 0;
        $codbar = isset($_POST['codbar']) ? preg_replace('/\D+/', '', (string)$_POST['codbar']) : '';
        $usuario_conf = isset($_POST['usuario']) ? trim((string)$_POST['usuario']) : '';

        if (strlen($codbar) >= 19) {
            $codbar19 = substr($codbar, -19);
            if ($lote === '') {
                $lote = substr($codbar19, 0, 8);
            }
            if ($regional === '') {
                $regional = substr($codbar19, 8, 3);
            }
            if ($posto === '') {
                $posto = substr($codbar19, 11, 3);
            }
            if ($qtd <= 0) {
                $qtd = (int)substr($codbar19, 14, 5);
            }
            $codbar = $codbar19;
        }

        if ($dataexp === '') {
            $dataexp = date('d-m-Y');
        }
        $dataexp_sql = normalizarDataSqlPacote($dataexp);
        if ($dataexp_sql !== '') {
            $dataexp = $dataexp_sql;
        }
        if ($usuario_conf === '') {
            die(json_encode(array('success' => false, 'erro' => 'Usuario obrigatorio')));
        }
        if ($lote === '' || $regional === '' || $posto === '' || strlen($codbar) !== 19) {
            die(json_encode(array('success' => false, 'erro' => 'Dados do pacote incompletos')));
        }
        
        $sql = "INSERT INTO conferencia_pacotes (regional, nlote, nposto, dataexp, qtd, codbar, conf, usuario, conferido_em) 
            VALUES (?, ?, ?, ?, ?, ?, 's', ?, NOW())
            ON DUPLICATE KEY UPDATE conf='s', qtd=VALUES(qtd), codbar=VALUES(codbar), dataexp=VALUES(dataexp), usuario=VALUES(usuario), conferido_em=NOW()";
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array($regional, $lote, $posto, $dataexp, $qtd, $codbar, $usuario_conf));
            $stmt = null; // v8.17.4: Libera statement
            $pdo = null;  // v8.17.4: Fecha conexão
            die(json_encode(array('success' => true, 'sucesso' => true)));
        } catch (Exception $ex) {
            die(json_encode(array('success' => false, 'erro' => 'Falha ao gravar conferencia: ' . $ex->getMessage())));
        }
    }

    if (isset($_POST['salvar_atribuicao_lacres_ajax'])) {
        $payload = isset($_POST['pacotes']) ? $_POST['pacotes'] : '';
        $usuario_lacre = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
        if ($usuario_lacre === '') {
            die(json_encode(array('success' => false, 'erro' => 'Usuario obrigatorio')));
        }

        $pacotes = json_decode($payload, true);
        if (!is_array($pacotes) || empty($pacotes)) {
            die(json_encode(array('success' => false, 'erro' => 'Nenhum lote informado')));
        }

        $sqlUpsert = "INSERT INTO conferencia_pacotes_lacres
            (codbar, lote, regional, posto, dataexp, qtd, lacre_iipr, grupo_iipr, lacre_correios, grupo_correios, etiqueta_correios, usuario_lacre, atualizado_em)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                lote = VALUES(lote),
                regional = VALUES(regional),
                posto = VALUES(posto),
                dataexp = VALUES(dataexp),
                qtd = VALUES(qtd),
                lacre_iipr = VALUES(lacre_iipr),
                grupo_iipr = VALUES(grupo_iipr),
                lacre_correios = VALUES(lacre_correios),
                grupo_correios = VALUES(grupo_correios),
                etiqueta_correios = VALUES(etiqueta_correios),
                usuario_lacre = VALUES(usuario_lacre),
                atualizado_em = NOW()";
        $stmtUpsert = $pdo->prepare($sqlUpsert);
        $stmtDelete = $pdo->prepare("DELETE FROM conferencia_pacotes_lacres WHERE codbar = ?");

        $salvos = 0;
        foreach ($pacotes as $pacote) {
            $codbar = isset($pacote['codbar']) ? preg_replace('/\D+/', '', (string)$pacote['codbar']) : '';
            $lote = isset($pacote['lote']) ? str_pad(preg_replace('/\D+/', '', (string)$pacote['lote']), 8, '0', STR_PAD_LEFT) : '';
            $regional = isset($pacote['regional']) ? str_pad(preg_replace('/\D+/', '', (string)$pacote['regional']), 3, '0', STR_PAD_LEFT) : '';
            $posto = isset($pacote['posto']) ? str_pad(preg_replace('/\D+/', '', (string)$pacote['posto']), 3, '0', STR_PAD_LEFT) : '';
            $dataexp = isset($pacote['dataexp']) ? normalizarDataSqlPacote((string)$pacote['dataexp']) : '';
            $qtd = isset($pacote['qtd']) ? (int)$pacote['qtd'] : 0;
            $lacreI = isset($pacote['lacre_iipr']) ? preg_replace('/\D+/', '', (string)$pacote['lacre_iipr']) : '';
            $grupoI = isset($pacote['grupo_iipr']) ? trim((string)$pacote['grupo_iipr']) : '';
            $lacreC = isset($pacote['lacre_correios']) ? preg_replace('/\D+/', '', (string)$pacote['lacre_correios']) : '';
            $grupoC = isset($pacote['grupo_correios']) ? trim((string)$pacote['grupo_correios']) : '';
            $etiqueta = isset($pacote['etiqueta_correios']) ? trim((string)$pacote['etiqueta_correios']) : '';

            if ($codbar === '' && $lote !== '' && $regional !== '' && $posto !== '' && $qtd > 0) {
                $codbar = $lote . $regional . $posto . str_pad((string)$qtd, 5, '0', STR_PAD_LEFT);
            }
            if ($codbar === '' || $lote === '' || $posto === '' || $dataexp === '') {
                continue;
            }

            $lacreIVal = ($lacreI === '' ? null : (int)$lacreI);
            $lacreCVal = ($lacreC === '' ? null : (int)$lacreC);
            $etiquetaVal = ($etiqueta === '' ? null : $etiqueta);

            if ($lacreIVal === null && $lacreCVal === null && $etiquetaVal === null) {
                $stmtDelete->execute(array($codbar));
                $salvos++;
                continue;
            }

            $stmtUpsert->execute(array(
                $codbar,
                $lote,
                $regional,
                $posto,
                $dataexp,
                $qtd,
                $lacreIVal,
                ($grupoI === '' ? null : $grupoI),
                $lacreCVal,
                ($grupoC === '' ? null : $grupoC),
                $etiquetaVal,
                $usuario_lacre
            ));
            $salvos++;
        }

        $stmtUpsert = null;
        $stmtDelete = null;
        $pdo = null;
        die(json_encode(array('success' => true, 'salvos' => $salvos)));
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
        $datasFiltro = array();
        if (isset($_POST['datas']) && trim((string)$_POST['datas']) !== '') {
            $partesDatas = explode(',', (string)$_POST['datas']);
            foreach ($partesDatas as $parteData) {
                $dataNorm = normalizarDataSqlPacote($parteData);
                if ($dataNorm !== '') {
                    $datasFiltro[] = $dataNorm;
                }
            }
        }

        if (!empty($datasFiltro)) {
            $datasFiltro = array_values(array_unique($datasFiltro));
            $phDatas = implode(',', array_fill(0, count($datasFiltro), '?'));

            $stmt = $pdo->prepare("DELETE FROM conferencia_pacotes WHERE DATE(dataexp) IN ($phDatas)");
            $stmt->execute($datasFiltro);

            $stmt = $pdo->prepare("DELETE FROM conferencia_pacotes_lacres WHERE dataexp IN ($phDatas)");
            $stmt->execute($datasFiltro);

            $stmt = null;
            $pdo = null;
            die(json_encode(array('success' => true)));
        }

        $lote = trim($_POST['lote']);
        $regional = trim($_POST['regional']);
        $posto = trim($_POST['posto']);

        $sql = "DELETE FROM conferencia_pacotes WHERE nlote = ? AND regional = ? AND nposto = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($lote, $regional, $posto));

        $codbar = isset($_POST['codbar']) ? preg_replace('/\D+/', '', (string)$_POST['codbar']) : '';
        if ($codbar !== '') {
            $stmt = $pdo->prepare("DELETE FROM conferencia_pacotes_lacres WHERE codbar = ?");
            $stmt->execute(array($codbar));
        }

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

    $atribuicoes_lacres_por_codigo = array();
    $atribuicoes_lacres_por_chave = array();

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

        $sqlLacres = "SELECT codbar, lote, regional, posto, dataexp, qtd, lacre_iipr, grupo_iipr, lacre_correios, grupo_correios, etiqueta_correios, usuario_lacre, atualizado_em
            FROM conferencia_pacotes_lacres
            WHERE (" . implode(' OR ', array_map(function($condicao) {
                return str_replace('DATE(dataCarga)', 'dataexp', $condicao);
            }, $condicoes_data)) . ")";
        $stmtLacres = $pdo->prepare($sqlLacres);
        $stmtLacres->execute($params_data);
        while ($rowLacre = $stmtLacres->fetch(PDO::FETCH_ASSOC)) {
            $codbarLacre = preg_replace('/\D+/', '', (string)$rowLacre['codbar']);
            $loteLacre = str_pad(preg_replace('/\D+/', '', (string)$rowLacre['lote']), 8, '0', STR_PAD_LEFT);
            $postoLacre = str_pad(preg_replace('/\D+/', '', (string)$rowLacre['posto']), 3, '0', STR_PAD_LEFT);
            $dataLacre = isset($rowLacre['dataexp']) ? trim((string)$rowLacre['dataexp']) : '';
            $atrib = array(
                'lacre_iipr' => isset($rowLacre['lacre_iipr']) && $rowLacre['lacre_iipr'] !== null ? (int)$rowLacre['lacre_iipr'] : 0,
                'grupo_iipr' => isset($rowLacre['grupo_iipr']) ? (string)$rowLacre['grupo_iipr'] : '',
                'lacre_correios' => isset($rowLacre['lacre_correios']) && $rowLacre['lacre_correios'] !== null ? (int)$rowLacre['lacre_correios'] : 0,
                'grupo_correios' => isset($rowLacre['grupo_correios']) ? (string)$rowLacre['grupo_correios'] : '',
                'etiqueta_correios' => isset($rowLacre['etiqueta_correios']) ? (string)$rowLacre['etiqueta_correios'] : '',
                'usuario_lacre' => isset($rowLacre['usuario_lacre']) ? (string)$rowLacre['usuario_lacre'] : '',
                'atualizado_em' => isset($rowLacre['atualizado_em']) ? (string)$rowLacre['atualizado_em'] : ''
            );
            if ($codbarLacre !== '') {
                $atribuicoes_lacres_por_codigo[$codbarLacre] = $atrib;
            }
            $atribuicoes_lacres_por_chave[$postoLacre . '|' . $loteLacre . '|' . $dataLacre] = $atrib;
            $atribuicoes_lacres_por_chave[$postoLacre . '|' . $loteLacre] = $atrib;
        }

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
                $atribuicao_lacre = null;
                if (isset($atribuicoes_lacres_por_codigo[$codigo_barras])) {
                    $atribuicao_lacre = $atribuicoes_lacres_por_codigo[$codigo_barras];
                } elseif (isset($atribuicoes_lacres_por_chave[$posto . '|' . $lote . '|' . $data_sql_row])) {
                    $atribuicao_lacre = $atribuicoes_lacres_por_chave[$posto . '|' . $lote . '|' . $data_sql_row];
                } elseif (isset($atribuicoes_lacres_por_chave[$posto . '|' . $lote])) {
                    $atribuicao_lacre = $atribuicoes_lacres_por_chave[$posto . '|' . $lote];
                }
                
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
                    'lacre_iipr' => $atribuicao_lacre ? (int)$atribuicao_lacre['lacre_iipr'] : 0,
                    'grupo_iipr' => $atribuicao_lacre ? (string)$atribuicao_lacre['grupo_iipr'] : '',
                    'lacre_correios' => $atribuicao_lacre ? (int)$atribuicao_lacre['lacre_correios'] : 0,
                    'grupo_correios' => $atribuicao_lacre ? (string)$atribuicao_lacre['grupo_correios'] : '',
                    'etiqueta_correios' => $atribuicao_lacre ? (string)$atribuicao_lacre['etiqueta_correios'] : '',
                    'usuario_lacre' => $atribuicao_lacre ? (string)$atribuicao_lacre['usuario_lacre'] : '',
                    'atualizado_lacre_em' => $atribuicao_lacre ? (string)$atribuicao_lacre['atualizado_em'] : '',
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
    <title>Conferência de Pacotes v0.9.25.16</title>
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
        .controle-creditos {
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #fff;
            font-size: 12px;
            font-weight: 600;
        }
        .controle-creditos input[type="checkbox"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        .creditos-overlay {
            position: fixed;
            z-index: 6000;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: #000;
            color: #fff;
            display: none;
            overflow: hidden;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.45s ease;
        }
        .creditos-overlay.ativo {
            display: block;
            opacity: 1;
            pointer-events: auto;
        }
        .creditos-starfield {
            position: absolute;
            inset: 0;
            z-index: 0;
            overflow: hidden;
        }
        .creditos-star {
            position: absolute;
            border-radius: 50%;
            background: #fff;
            animation: creditosTwinkle 3s ease-in-out infinite alternate;
            opacity: 0.72;
        }
        @keyframes creditosTwinkle {
            0% { opacity: 0.15; transform: scale(0.85); }
            100% { opacity: 0.95; transform: scale(1.15); }
        }
        .creditos-fade {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 18vh;
            background: linear-gradient(180deg, rgba(0,0,0,1) 12%, rgba(0,0,0,0.42) 58%, rgba(0,0,0,0) 100%);
            pointer-events: none;
            z-index: 12;
        }
        .creditos-fade.bottom {
            top: auto;
            bottom: 0;
            background: linear-gradient(0deg, rgba(0,0,0,1) 12%, rgba(0,0,0,0.42) 58%, rgba(0,0,0,0) 100%);
        }
        .creditos-trilha {
            position: absolute;
            left: 50%;
            width: min(760px, 88vw);
            top: 100vh;
            transform: translate(-50%, 0);
            text-align: center;
            font-family: "Cormorant Garamond", Georgia, serif;
            letter-spacing: 0.5px;
            line-height: 1.6;
            will-change: transform, opacity;
            opacity: 1;
            transition: opacity 0.8s ease;
            z-index: 5;
        }
        .creditos-overlay.final .creditos-trilha,
        .creditos-overlay.final .creditos-fade {
            opacity: 0;
        }
        .creditos-titulo {
            font-family: "Cinzel", "Times New Roman", serif;
            font-size: clamp(34px, 5vw, 48px);
            font-weight: 700;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            color: #d4af37;
            text-shadow: 0 0 30px rgba(212,175,55,0.3);
        }
        .creditos-subtitulo {
            font-size: 17px;
            margin-bottom: 80px;
            color: rgba(240,236,226,0.62);
            letter-spacing: 0.28em;
            text-transform: uppercase;
        }
        .creditos-secao {
            margin: 0 0 62px 0;
        }
        .creditos-divisor {
            width: 76px;
            height: 1px;
            margin: 44px auto 52px auto;
            background: linear-gradient(90deg, transparent, #d4af37, transparent);
        }
        .creditos-grupo {
            font-family: "Cinzel", "Times New Roman", serif;
            color: #d4af37;
            font-size: 17px;
            letter-spacing: 0.34em;
            text-transform: uppercase;
            margin-bottom: 22px;
        }
        .creditos-bloco {
            margin: 20px 0;
            font-size: 27px;
            font-weight: 300;
            color: #f0ece2;
        }
        .creditos-role {
            font-family: "Cinzel", "Times New Roman", serif;
            color: #d4af37;
            font-size: 12px;
            letter-spacing: 0.38em;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        .creditos-pair {
            display: flex;
            justify-content: center;
            gap: 34px;
            flex-wrap: wrap;
            margin: 10px 0;
        }
        .creditos-pair-role {
            min-width: 200px;
            text-align: right;
            color: rgba(240,236,226,0.52);
            font-size: 16px;
        }
        .creditos-pair-name {
            min-width: 220px;
            text-align: left;
            color: #f0ece2;
            font-size: 24px;
        }
        .creditos-quote {
            max-width: 560px;
            margin: 0 auto;
            font-size: 21px;
            font-style: italic;
            line-height: 1.8;
            color: rgba(240,236,226,0.58);
        }
        .creditos-sub {
            font-size: 18px;
            opacity: 0.88;
            margin-top: 8px;
            color: rgba(240,236,226,0.78);
        }
        .creditos-dica {
            position: absolute;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 13px;
            color: rgba(255,255,255,0.5);
            opacity: 0.84;
            text-align: center;
            transition: opacity 0.4s ease;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            z-index: 22;
        }
        .creditos-end-screen {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            font-family: "Cinzel", "Times New Roman", serif;
            font-size: clamp(54px, 10vw, 120px);
            font-weight: 700;
            letter-spacing: 0.34em;
            text-indent: 0.34em;
            color: #d4af37;
            opacity: 0;
            pointer-events: none;
            transition: opacity 1.6s ease;
            text-align: center;
            background: #000;
            z-index: 20;
            text-shadow: 0 0 40px rgba(212,175,55,0.4), 0 0 80px rgba(212,175,55,0.15);
        }
        .creditos-end-hint {
            margin-top: 36px;
            font-family: "Cormorant Garamond", Georgia, serif;
            font-size: 14px;
            letter-spacing: 0.24em;
            text-indent: 0;
            color: rgba(255,255,255,0.42);
            animation: creditosHintPulse 2.6s ease-in-out infinite;
        }
        @keyframes creditosHintPulse {
            0%, 100% { opacity: 0.28; }
            50% { opacity: 0.78; }
        }
        .creditos-overlay.final .creditos-end-screen {
            opacity: 1;
        }

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
            display: inline-flex;
            align-items: center;
            gap: 6px;
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
        .operacao-chip.confirmado.tem-iipr {
            background: linear-gradient(180deg, #ffe082 0%, #ffbf47 100%);
            color: #4d2d00;
        }
        .operacao-chip.confirmado.tem-correios {
            background: linear-gradient(180deg, #a6e8ff 0%, #51c7ff 100%);
            color: #06263a;
        }
        .operacao-chip.ativo {
            outline: 2px solid #ffe66d;
            outline-offset: 1px;
            animation: pulseChipAtivo 1.8s ease-in-out infinite;
        }
        .operacao-chip.sem-upload {
            border-style: dashed;
        }
        .operacao-chip.tem-iipr {
            box-shadow: inset 0 0 0 1px rgba(255,209,102,0.9);
        }
        .operacao-chip.tem-correios {
            box-shadow: inset 0 0 0 1px rgba(255,255,255,0.12), 0 0 0 2px rgba(83,194,255,0.28);
        }
        .operacao-chip-texto {
            display: inline-flex;
            align-items: center;
        }
        .operacao-chip-indicadores {
            display: inline-flex;
            gap: 4px;
            align-items: center;
        }
        .operacao-chip-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 18px;
            height: 18px;
            border-radius: 999px;
            padding: 0 6px;
            font-size: 9px;
            font-weight: 900;
            letter-spacing: 0.4px;
            background: rgba(3, 34, 15, 0.18);
            color: inherit;
        }
        .operacao-chip-badge.iipr {
            background: rgba(122, 77, 0, 0.18);
        }
        .operacao-chip-badge.correios {
            background: rgba(6, 38, 58, 0.18);
        }
        .operacao-posto-row.selecionado-malote {
            border-color: #7bdff2;
            box-shadow: 0 0 0 2px rgba(123,223,242,0.18);
        }
        .operacao-posto-mapa {
            grid-column: 1 / -1;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid rgba(255,255,255,0.08);
        }
        .operacao-posto-workspace {
            display: grid;
            grid-template-columns: minmax(260px, 1.1fr) minmax(320px, 1.2fr) minmax(320px, 1.2fr);
            gap: 10px;
        }
        .operacao-posto-card {
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 12px;
            background: rgba(6,16,28,0.46);
            padding: 12px;
            min-width: 0;
        }
        .operacao-posto-card h5 {
            margin: 0;
            font-size: 14px;
            font-weight: 900;
            color: #f8fbff;
        }
        .operacao-posto-card-sub {
            margin-top: 3px;
            font-size: 11px;
            color: #a7cae7;
        }
        .operacao-malote-head {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            align-items: flex-start;
            margin-bottom: 10px;
        }
        .operacao-malote-contador {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 9px;
            border-radius: 999px;
            background: rgba(123,223,242,0.12);
            color: #c6edff;
            font-size: 10px;
            font-weight: 900;
            white-space: nowrap;
        }
        .operacao-malote-bolsa {
            min-height: 88px;
            border: 1px dashed rgba(255,255,255,0.18);
            border-radius: 14px 14px 18px 18px;
            background: linear-gradient(180deg, rgba(255,255,255,0.04) 0%, rgba(0,0,0,0.10) 100%);
            padding: 12px;
        }
        .operacao-malote-bolsa.pronto-iipr {
            border-color: rgba(255,191,71,0.65);
            background: linear-gradient(180deg, rgba(255,224,130,0.18) 0%, rgba(255,191,71,0.08) 100%);
        }
        .operacao-malote-bolsa.pronto-correios {
            border-color: rgba(81,199,255,0.65);
            background: linear-gradient(180deg, rgba(166,232,255,0.18) 0%, rgba(81,199,255,0.08) 100%);
        }
        .operacao-malote-bolsa-vazio {
            color: #95b7d1;
            font-size: 12px;
        }
        .operacao-malote-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .operacao-malote-tag {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 9px;
            border-radius: 999px;
            background: rgba(255,255,255,0.08);
            color: #f8fbff;
            font-size: 11px;
            font-weight: 800;
        }
        .operacao-malote-tag i {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 16px;
            height: 16px;
            border-radius: 999px;
            background: rgba(255,255,255,0.16);
            font-style: normal;
            font-size: 9px;
        }
        .operacao-malote-tag.iipr {
            background: rgba(255,191,71,0.18);
            color: #ffe9b0;
        }
        .operacao-malote-tag.correios {
            background: rgba(81,199,255,0.18);
            color: #d8f5ff;
        }
        .operacao-malote-tag.fechado {
            background: rgba(42,255,117,0.16);
            color: #d7ffe5;
        }
        .operacao-malote-form-inline {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 8px;
            margin-top: 10px;
        }
        .operacao-malote-form-inline.duplo {
            grid-template-columns: minmax(0, 1fr) minmax(0, 1.3fr) auto auto;
        }
        .operacao-malote-form-inline input {
            width: 100%;
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.14);
            background: rgba(255,255,255,0.08);
            color: #fff;
            padding: 10px 12px;
            font-size: 13px;
            font-weight: 700;
        }
        .operacao-malote-form-inline button,
        .operacao-malote-rodape button {
            border: 0;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 12px;
            font-weight: 900;
            cursor: pointer;
        }
        .btn-inline-iipr {
            background: #ffbf47;
            color: #4d2d00;
        }
        .btn-inline-correios {
            background: #51c7ff;
            color: #06263a;
        }
        .btn-inline-salvar-posto {
            background: #21ff8b;
            color: #03220f;
        }
        .operacao-malote-historico {
            margin-top: 10px;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .operacao-malote-rodape {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
            flex-wrap: wrap;
        }
        .operacao-malote-pendencia {
            font-size: 11px;
            color: #ffd7aa;
            font-weight: 800;
        }
        .operacao-oficio-tabela {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            background: rgba(8, 25, 40, 0.92);
            border: 1px solid rgba(255,255,255,0.12);
        }
        .operacao-oficio-tabela th,
        .operacao-oficio-tabela td {
            border-bottom: 1px solid rgba(255,255,255,0.08);
            padding: 7px 6px;
            text-align: left;
            font-size: 11px;
            color: #f7fbff;
            vertical-align: top;
        }
        .operacao-oficio-tabela th {
            color: #a7cae7;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.6px;
            background: rgba(255,255,255,0.06);
        }
        .operacao-oficio-tabela td {
            background: rgba(8, 25, 40, 0.86);
        }
        .operacao-oficio-lacres {
            white-space: normal;
            word-break: break-word;
            line-height: 1.35;
            min-width: 170px;
        }
        .operacao-oficio-vazio {
            margin-top: 10px;
            color: #95b7d1;
            font-size: 12px;
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
        .painel-malotes {
            background: #ffffff;
            border: 1px solid #d8e4ef;
            border-radius: 14px;
            padding: 16px;
            margin: 12px 0 8px;
            box-shadow: 0 8px 20px rgba(8,32,58,0.08);
        }
        .painel-malotes-topo {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }
        .painel-malotes-topo h3 {
            margin: 0;
            color: #16324f;
            font-size: 20px;
        }
        .painel-malotes-topo .sub {
            margin-top: 4px;
            font-size: 12px;
            color: #5b7188;
        }
        .painel-malotes-resumo {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .painel-malotes-badge {
            min-width: 98px;
            border-radius: 10px;
            padding: 10px 12px;
            background: linear-gradient(180deg, #eef6ff 0%, #dbeafe 100%);
            color: #173a57;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .painel-malotes-badge strong {
            display: block;
            margin-top: 4px;
            font-size: 22px;
            color: #0b3b66;
        }
        .painel-malotes-grid {
            display: grid;
            grid-template-columns: minmax(320px, 1.2fr) minmax(280px, 0.8fr);
            gap: 16px;
        }
        .painel-malotes-coluna {
            border: 1px solid #e6edf5;
            border-radius: 12px;
            padding: 12px;
            background: #f9fbfd;
        }
        .painel-malotes-coluna h4 {
            margin: 0 0 8px;
            color: #153754;
            font-size: 14px;
        }
        .painel-malotes-vazio {
            color: #60758b;
            font-size: 12px;
            background: #f2f6fa;
            border: 1px dashed #c8d6e5;
            border-radius: 10px;
            padding: 14px;
        }
        .tabela-malotes {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 12px;
        }
        .tabela-malotes th,
        .tabela-malotes td {
            border-bottom: 1px solid #e3ebf3;
            padding: 8px 6px;
            text-align: left;
            vertical-align: top;
        }
        .tabela-malotes th {
            color: #5a7086;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .malote-status {
            display: inline-flex;
            border-radius: 999px;
            padding: 3px 8px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .malote-status.pendente { background: #fff4d6; color: #946200; }
        .malote-status.montagem { background: #efe6ff; color: #5d2e91; }
        .malote-status.iipr { background: #e8f2ff; color: #0f4d85; }
        .malote-status.correios { background: #dbf8e5; color: #136c3a; }
        .malote-status.pendente-salvar { background: #ffe7cc; color: #a14a00; }
        .painel-malotes-rascunho {
            margin: 8px 0 10px;
            border: 1px dashed #c7d7e6;
            border-radius: 10px;
            background: #fff;
            padding: 10px 12px;
            font-size: 12px;
            color: #385067;
        }
        .painel-malotes-rascunho strong {
            display: block;
            margin-bottom: 6px;
            color: #14324c;
        }
        .painel-malotes-rascunho .muted {
            color: #6b7f93;
            font-size: 11px;
        }
        .painel-malotes-rascunho ul {
            margin: 6px 0 0 16px;
            padding: 0;
        }
        .painel-malotes-rascunho li {
            margin: 3px 0;
        }
        .painel-malotes-form {
            display: grid;
            gap: 10px;
            margin-top: 10px;
        }
        .painel-malotes-form label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: #51677c;
            margin-bottom: 4px;
        }
        .painel-malotes-form input {
            width: 100%;
            box-sizing: border-box;
            padding: 9px 10px;
            border-radius: 8px;
            border: 1px solid #c8d6e5;
            background: #fff;
            font-size: 13px;
        }
        .painel-malotes-acoes {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .painel-malotes-acoes button {
            border: none;
            border-radius: 8px;
            padding: 10px 12px;
            font-weight: 800;
            cursor: pointer;
        }
        .btn-malote-iipr { background: #0d6efd; color: #fff; }
        .btn-malote-correios { background: #198754; color: #fff; }
        .btn-malote-montar { background: #6f42c1; color: #fff; }
        .btn-malote-salvar-tudo { background: #14532d; color: #fff; }
        .btn-malote-descartar { background: #fff3cd; color: #7a4d00; }
        .btn-malote-limpar { background: #f1f3f5; color: #495057; }
        .painel-malotes-rodape {
            margin-top: 14px;
            border-top: 1px solid #e4edf6;
            padding-top: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .painel-malotes-pendencias {
            font-size: 12px;
            color: #4b6075;
            font-weight: 700;
        }
        .painel-malotes-pendencias strong {
            color: #0f4d85;
            font-size: 18px;
            margin-left: 6px;
        }
        .operacao-chip.malote-pendente {
            box-shadow: inset 0 0 0 2px #ffb869;
        }
        .painel-malotes-ajuda {
            margin-top: 10px;
            font-size: 11px;
            color: #687b8d;
            line-height: 1.5;
        }
        .painel-malotes-utilitarios {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 14px;
            margin-bottom: 14px;
        }
        .painel-voz,
        .painel-controle-remoto,
        .painel-previsao-malotes {
            border: 1px solid #e6edf5;
            border-radius: 12px;
            padding: 12px;
            background: #f9fbfd;
        }
        .painel-voz-topo,
        .painel-controle-topo,
        .painel-previsao-topo {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            flex-wrap: wrap;
        }
        .painel-voz h4,
        .painel-controle-remoto h4,
        .painel-previsao-malotes h4 {
            margin: 0;
            color: #153754;
            font-size: 14px;
        }
        .painel-voz-sub,
        .painel-controle-sub,
        .painel-previsao-sub {
            margin-top: 4px;
            font-size: 12px;
            color: #5b7188;
        }
        .btn-voz-toggle,
        .btn-controle-remoto,
        .btn-previsao-malotes {
            border: none;
            border-radius: 8px;
            padding: 10px 12px;
            font-weight: 800;
            cursor: pointer;
        }
        .btn-voz-toggle {
            background: #173a57;
            color: #fff;
        }
        .btn-voz-toggle.ativo {
            background: #b42318;
        }
        .btn-previsao-malotes {
            background: #0f766e;
            color: #fff;
        }
        .btn-controle-remoto {
            background: #7c3aed;
            color: #fff;
        }
        .controle-canal-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 10px;
            padding: 8px 10px;
            border-radius: 999px;
            background: #efe7ff;
            color: #5b21b6;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .voz-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
            padding: 8px 10px;
            border-radius: 999px;
            background: #eef4fb;
            color: #204264;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .voz-status-pill.escutando {
            background: #fee4e2;
            color: #b42318;
        }
        .voz-status-pill.aguardando {
            background: #e8f2ff;
            color: #0f4d85;
        }
        .voz-status-pill.erro {
            background: #fff4d6;
            color: #946200;
        }
        .painel-voz-dicas,
        .painel-controle-dicas,
        .painel-previsao-dicas {
            margin-top: 10px;
            font-size: 11px;
            color: #687b8d;
            line-height: 1.5;
        }
        .painel-voz-diagnostico {
            margin-top: 10px;
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid #d6e2ee;
            background: #fff;
            font-size: 11px;
            color: #4e657d;
            line-height: 1.55;
        }
        .painel-voz-diagnostico strong {
            display: block;
            margin-bottom: 6px;
            color: #173a57;
            font-size: 12px;
        }
        .painel-voz-diagnostico ul {
            margin: 8px 0 0;
            padding-left: 18px;
        }
        .painel-voz-diagnostico li {
            margin: 4px 0;
        }
        .painel-voz-diagnostico .ok {
            color: #136c3a;
            font-weight: 700;
        }
        .painel-voz-diagnostico .erro {
            color: #b42318;
            font-weight: 700;
        }
        .painel-voz-diagnostico .aviso {
            color: #946200;
            font-weight: 700;
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
            .painel-malotes-utilitarios { grid-template-columns: 1fr; }
            .painel-malotes-grid { grid-template-columns: 1fr; }
            .painel-malotes-resumo { width: 100%; }
            .painel-malotes-badge { flex: 1 1 96px; }
            .operacao-posto-workspace { grid-template-columns: 1fr; }
            .operacao-malote-form-inline,
            .operacao-malote-form-inline.duplo { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="topo-status">
    <div class="versao">v0.9.25.16</div>
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

<h2>📋 Conferência de Pacotes v0.9.25.16</h2>

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
        <label class="controle-creditos">
            <input type="checkbox" id="desativarCreditosFinais">
            Desativar animação final (créditos)
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
            oninput="if(window.iniciarConferenciaPacotes&&!window.processarLeituraCodigo){window.iniciarConferenciaPacotes();} if(window.processarLeituraCodigo){window.processarLeituraCodigo(this.value);}"
            onchange="if(window.iniciarConferenciaPacotes&&!window.processarLeituraCodigo){window.iniciarConferenciaPacotes();} if(window.processarLeituraCodigo){window.processarLeituraCodigo(this.value);}"
            onkeydown="if(event && event.keyCode===13){event.preventDefault(); if(window.iniciarConferenciaPacotes&&!window.processarLeituraCodigo){window.iniciarConferenciaPacotes();} if(window.processarLeituraCodigo){window.processarLeituraCodigo(this.value);} }">
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

<div class="painel-malotes" id="painelMalotesChips">
    <div class="painel-malotes-topo">
        <div>
            <h3>Malotes por lote no modo chips</h3>
            <div class="sub" id="painelMalotesSubtitulo">Selecione um chip ou continue a conferência para abrir o posto atual.</div>
        </div>
        <div class="painel-malotes-resumo">
            <div class="painel-malotes-badge">Confirmados<strong id="malotesResumoConfirmados">0</strong></div>
            <div class="painel-malotes-badge">Com IIPR<strong id="malotesResumoIipr">0</strong></div>
            <div class="painel-malotes-badge">Com Correios<strong id="malotesResumoCorreios">0</strong></div>
        </div>
    </div>
    <div class="painel-malotes-utilitarios">
        <div class="painel-voz" id="painelVozMalotes">
            <div class="painel-voz-topo">
                <div>
                    <h4>Comando de voz</h4>
                    <div class="painel-voz-sub" id="statusVozComando">Microfone desligado.</div>
                </div>
                <button type="button" class="btn-voz-toggle" id="btnAlternarVozMalotes">Ativar microfone</button>
            </div>
            <div class="voz-status-pill" id="vozCampoAtual">Nenhum campo armado.</div>
            <div class="painel-voz-dicas">Diga frases como fechar malote IIPR, fechar malote Correios, etiqueta Correios, salvar malote IIPR, salvar malote Correios ou cancelar comando.</div>
            <div class="painel-voz-diagnostico" id="painelDiagnosticoVoz">
                <strong>Diagnóstico de voz</strong>
                Clique em Ativar microfone para testar a compatibilidade deste navegador.
            </div>
        </div>
        <div class="painel-controle-remoto" id="painelControleRemoto">
            <div class="painel-controle-topo">
                <div>
                    <h4>Controle remoto por celular</h4>
                    <div class="painel-controle-sub" id="statusControleRemoto">Use o celular como painel de comandos para lacres e etiqueta.</div>
                </div>
                <button type="button" class="btn-controle-remoto" id="btnAbrirControleRemoto">Abrir controle</button>
            </div>
            <div class="controle-canal-badge">Canal: <?php echo e($controle_canal); ?></div>
            <div class="painel-controle-dicas">Abra a página de controle remoto no celular com o mesmo canal. Nela você toca três vezes nos botões para enviar ações como salvar malote IIPR, salvar malote Correios e limpar vínculos.</div>
        </div>
        <div class="painel-previsao-malotes" id="painelPrevisaoMalotes">
            <div class="painel-previsao-topo">
                <div>
                    <h4>Prévia ao vivo do ofício</h4>
                    <div class="painel-previsao-sub" id="statusPreviaMalotes">Sincronização local pronta para a segunda tela.</div>
                </div>
                <button type="button" class="btn-previsao-malotes" id="btnAbrirPreviaMalotes">Abrir prévia</button>
            </div>
            <div class="painel-previsao-dicas">Abra a prévia em outro monitor. Ela recebe o resumo consolidado conforme os malotes vão sendo fechados no modo chips.</div>
        </div>
    </div>
    <div class="painel-malotes-grid">
        <div class="painel-malotes-coluna">
            <h4>Lotes confirmados do posto</h4>
            <div id="painelMaloteIiprRascunho" class="painel-malotes-vazio">Nenhum malote IIPR em montagem.</div>
            <div id="painelMalotesLotes" class="painel-malotes-vazio">Nenhum posto selecionado.</div>
            <div class="painel-malotes-form">
                <div>
                    <label for="inputLacreIiprMalote">Lacre IIPR do malote selecionado</label>
                    <input type="text" id="inputLacreIiprMalote" maxlength="12" placeholder="Ex.: 51115">
                </div>
                <div class="painel-malotes-acoes">
                    <button type="button" class="btn-malote-montar" id="btnMontarMaloteIipr">Colocar lotes marcados no malote</button>
                    <button type="button" class="btn-malote-iipr" id="btnSalvarMaloteIipr">Fechar malote IIPR</button>
                    <button type="button" class="btn-malote-limpar" id="btnLimparMaloteLote">Limpar vínculo dos lotes marcados</button>
                </div>
            </div>
        </div>
        <div class="painel-malotes-coluna">
            <h4>Malotes IIPR já fechados</h4>
            <div id="painelMaloteCorreiosRascunho" class="painel-malotes-vazio">Nenhum malote Correios em montagem.</div>
            <div id="painelMalotesIipr" class="painel-malotes-vazio">Os malotes IIPR aparecerão aqui depois do primeiro fechamento.</div>
            <div class="painel-malotes-form">
                <div>
                    <label for="inputLacreCorreiosMalote">Lacre Correios do malote maior</label>
                    <input type="text" id="inputLacreCorreiosMalote" maxlength="12" placeholder="Ex.: 51119">
                </div>
                <div>
                    <label for="inputEtiquetaCorreiosMalote">Etiqueta dos Correios</label>
                    <input type="text" id="inputEtiquetaCorreiosMalote" maxlength="35" placeholder="Ex.: 87051030441050000104648810000101292">
                </div>
                <div class="painel-malotes-acoes">
                    <button type="button" class="btn-malote-montar" id="btnMontarMaloteCorreios">Colocar IIPR marcados no malote Correios</button>
                    <button type="button" class="btn-malote-correios" id="btnSalvarMaloteCorreios">Fechar malote Correios</button>
                </div>
            </div>
        </div>
    </div>
    <div class="painel-malotes-rodape">
        <div class="painel-malotes-pendencias">Pendentes para salvar<strong id="malotesResumoPendentesSalvar">0</strong></div>
        <div class="painel-malotes-acoes">
            <button type="button" class="btn-malote-salvar-tudo" id="btnSalvarTudoMalotes">Salvar tudo no banco</button>
            <button type="button" class="btn-malote-descartar" id="btnDescartarRascunhoMalotes">Descartar rascunho local</button>
        </div>
    </div>
    <div class="painel-malotes-ajuda">
        No modo chips, o fluxo principal agora acontece na própria linha do posto: feche o IIPR com os chips verdes do posto, depois feche o Correios com os IIPR já prontos e use a linha pronta do ofício como conferência visual. O botão Salvar tudo no banco continua disponível para persistir tudo de uma vez.
    </div>
</div>

</div>

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
        echo '<div class="operacao-posto-row" data-posto="' . htmlspecialchars($postoKey, ENT_QUOTES, 'UTF-8') . '" data-grupo="' . htmlspecialchars($tituloGrupo, ENT_QUOTES, 'UTF-8') . '">';
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
            if (!empty($item['lacre_iipr'])) {
                $chipClasses .= ' tem-iipr';
            }
            if (!empty($item['lacre_correios']) || !empty($item['etiqueta_correios'])) {
                $chipClasses .= ' tem-correios';
            }
            if ($semUploadCount > 0) {
                $chipClasses .= ' sem-upload';
            }
            echo '<button type="button" class="' . $chipClasses . '"';
            echo ' data-codigo="' . htmlspecialchars($item['codigo'], ENT_QUOTES, 'UTF-8') . '"';
            echo ' data-lote="' . htmlspecialchars($item['lote'], ENT_QUOTES, 'UTF-8') . '"';
            echo ' data-posto="' . htmlspecialchars($item['posto'], ENT_QUOTES, 'UTF-8') . '"';
            echo ' data-regional="' . htmlspecialchars($item['regional_label'], ENT_QUOTES, 'UTF-8') . '"';
            echo ' data-regional-codigo="' . htmlspecialchars($item['regional_grupo'], ENT_QUOTES, 'UTF-8') . '"';
            echo ' data-qtd="' . htmlspecialchars($item['qtd'], ENT_QUOTES, 'UTF-8') . '"';
            echo ' data-data="' . htmlspecialchars($item['data'], ENT_QUOTES, 'UTF-8') . '"';
            echo ' data-data-sql="' . htmlspecialchars($item['data_sql'], ENT_QUOTES, 'UTF-8') . '"';
            echo ' data-usuario="' . htmlspecialchars($item['usuario_prod'], ENT_QUOTES, 'UTF-8') . '"';
            echo ' data-ispt="' . (!empty($item['isPT']) ? '1' : '0') . '"';
            echo ' data-lacre-iipr="' . htmlspecialchars((string)$item['lacre_iipr'], ENT_QUOTES, 'UTF-8') . '"';
            echo ' data-grupo-iipr="' . htmlspecialchars((string)$item['grupo_iipr'], ENT_QUOTES, 'UTF-8') . '"';
            echo ' data-lacre-correios="' . htmlspecialchars((string)$item['lacre_correios'], ENT_QUOTES, 'UTF-8') . '"';
            echo ' data-grupo-correios="' . htmlspecialchars((string)$item['grupo_correios'], ENT_QUOTES, 'UTF-8') . '"';
            echo ' data-etiqueta-correios="' . htmlspecialchars((string)$item['etiqueta_correios'], ENT_QUOTES, 'UTF-8') . '"';
            echo ' data-usuario-lacre="' . htmlspecialchars((string)$item['usuario_lacre'], ENT_QUOTES, 'UTF-8') . '"';
            echo ' data-atualizado-lacre="' . htmlspecialchars((string)$item['atualizado_lacre_em'], ENT_QUOTES, 'UTF-8') . '"';
            echo ' data-conferido-em="' . htmlspecialchars($item['conferido_em'], ENT_QUOTES, 'UTF-8') . '"';
            echo ' data-conf="' . (!empty($item['conf']) ? '1' : '0') . '">';
            echo '<span class="operacao-chip-texto">' . htmlspecialchars($item['lote'], ENT_QUOTES, 'UTF-8') . '</span><span class="operacao-chip-indicadores"></span>';
            echo '</button>';
        }
        echo '</div>';
        echo '<div class="operacao-numero"><span data-role="pacotes">' . $totalPacotes . '</span><span class="operacao-numero-label">PACOTES</span></div>';
        echo '<div class="operacao-numero"><span data-role="conferidos">' . $conferidos . '</span><span class="operacao-numero-label">CONFERIDOS</span></div>';
        echo '<div class="operacao-pendentes"><span data-role="pendentes">' . $pendentes . '</span><span class="operacao-numero-label">PENDENTES</span></div>';
        echo '<div class="operacao-posto-mapa" data-row-posto="' . htmlspecialchars($postoKey, ENT_QUOTES, 'UTF-8') . '"></div>';
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
    echo '<th>Malote / Lacre</th>';
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
        echo 'data-lacre-iipr="' . htmlspecialchars((string)$posto['lacre_iipr'], ENT_QUOTES, 'UTF-8') . '" ';
        echo 'data-grupo-iipr="' . htmlspecialchars((string)$posto['grupo_iipr'], ENT_QUOTES, 'UTF-8') . '" ';
        echo 'data-lacre-correios="' . htmlspecialchars((string)$posto['lacre_correios'], ENT_QUOTES, 'UTF-8') . '" ';
        echo 'data-grupo-correios="' . htmlspecialchars((string)$posto['grupo_correios'], ENT_QUOTES, 'UTF-8') . '" ';
        echo 'data-etiqueta-correios="' . htmlspecialchars((string)$posto['etiqueta_correios'], ENT_QUOTES, 'UTF-8') . '" ';
        echo 'data-usuario-lacre="' . htmlspecialchars((string)$posto['usuario_lacre'], ENT_QUOTES, 'UTF-8') . '" ';
        echo 'data-atualizado-lacre="' . htmlspecialchars((string)$posto['atualizado_lacre_em'], ENT_QUOTES, 'UTF-8') . '" ';
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
        echo '<td class="col-malote-vinculo"></td>';
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
<audio id="beep" src="beep_correio.mp3" preload="auto"></audio>
<audio id="concluido" src="concluido.mp3" preload="auto"></audio>
<audio id="pacotejaconferido" src="pacotejaconferido.mp3" preload="auto"></audio>
<audio id="pacotedeoutraregional" src="pacotedeoutraregional.mp3" preload="auto"></audio>
<audio id="posto_poupatempo" src="posto_poupatempo.mp3" preload="auto"></audio>
<audio id="pertence_correios" src="pertence_aos_correios.mp3" preload="auto"></audio>
<audio id="pacote_nao_encontrado" src="pacote_nao_foi_encontrado.mp3" preload="auto"></audio>
<audio id="final_conferencia" src="final_conferencia.mp3" preload="auto"></audio>

<div class="creditos-overlay" id="creditosOverlay" aria-hidden="true">
    <div class="creditos-starfield" id="creditosStarfield"></div>
    <div class="creditos-fade"></div>
    <div class="creditos-fade bottom"></div>
    <div class="creditos-trilha" id="creditosTrilha"></div>
    <div class="creditos-dica">mova o mouse, pressione uma tecla ou toque na tela para fechar</div>
    <div class="creditos-end-screen" id="creditosEndScreen">THE END<div class="creditos-end-hint">qualquer interação fecha os créditos</div></div>
</div>

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
    var musicaFinalConferencia = document.getElementById("final_conferencia");
    var muteBeep = document.getElementById("muteBeep");
    var desativarCreditosFinais = document.getElementById("desativarCreditosFinais");
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
    var painelMalotesChips = document.getElementById('painelMalotesChips');
    var painelMalotesSubtitulo = document.getElementById('painelMalotesSubtitulo');
    var painelMaloteIiprRascunho = document.getElementById('painelMaloteIiprRascunho');
    var painelMalotesLotes = document.getElementById('painelMalotesLotes');
    var painelMaloteCorreiosRascunho = document.getElementById('painelMaloteCorreiosRascunho');
    var painelMalotesIipr = document.getElementById('painelMalotesIipr');
    var malotesResumoConfirmados = document.getElementById('malotesResumoConfirmados');
    var malotesResumoIipr = document.getElementById('malotesResumoIipr');
    var malotesResumoCorreios = document.getElementById('malotesResumoCorreios');
    var malotesResumoPendentesSalvar = document.getElementById('malotesResumoPendentesSalvar');
    var inputLacreIiprMalote = document.getElementById('inputLacreIiprMalote');
    var inputLacreCorreiosMalote = document.getElementById('inputLacreCorreiosMalote');
    var inputEtiquetaCorreiosMalote = document.getElementById('inputEtiquetaCorreiosMalote');
    var btnMontarMaloteIipr = document.getElementById('btnMontarMaloteIipr');
    var btnSalvarMaloteIipr = document.getElementById('btnSalvarMaloteIipr');
    var btnMontarMaloteCorreios = document.getElementById('btnMontarMaloteCorreios');
    var btnSalvarMaloteCorreios = document.getElementById('btnSalvarMaloteCorreios');
    var btnSalvarTudoMalotes = document.getElementById('btnSalvarTudoMalotes');
    var btnDescartarRascunhoMalotes = document.getElementById('btnDescartarRascunhoMalotes');
    var btnLimparMaloteLote = document.getElementById('btnLimparMaloteLote');
    var btnAlternarVozMalotes = document.getElementById('btnAlternarVozMalotes');
    var statusVozComando = document.getElementById('statusVozComando');
    var vozCampoAtual = document.getElementById('vozCampoAtual');
    var painelDiagnosticoVoz = document.getElementById('painelDiagnosticoVoz');
    var btnAbrirControleRemoto = document.getElementById('btnAbrirControleRemoto');
    var statusControleRemoto = document.getElementById('statusControleRemoto');
    var btnAbrirPreviaMalotes = document.getElementById('btnAbrirPreviaMalotes');
    var statusPreviaMalotes = document.getElementById('statusPreviaMalotes');
    var creditosOverlay = document.getElementById('creditosOverlay');
    var creditosStarfield = document.getElementById('creditosStarfield');
    var creditosTrilha = document.getElementById('creditosTrilha');
    var creditosEndScreen = document.getElementById('creditosEndScreen');
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
    var storageCreditosKey = 'conferencia_desativar_creditos_finais';
    var previewStorageKey  = 'conferencia_previa_malotes_v1';
    var storageMalotesRascunhoKey = 'conferencia_malotes_rascunho_v2::' + ((datasFiltroSql && datasFiltroSql.length) ? datasFiltroSql.join(',') : 'hoje');
    var controleCanal = <?php echo json_encode($controle_canal); ?>;
    var postoSelecionadoMalote = '';
    var grupoSelecionadoMalote = '';
    var malotesRascunho = { postos: {}, atribuicoes: {} };
    var historicoMalotes = [];
    var previewChannel = null;
    var previewWindowRef = null;
    var reconhecimentoVoz = null;
    var creditosJaExibidos = false;
    var creditosAtivos = false;
    var timerInicioCreditos = null;
    var creditosIniciadoEm = 0;
    var creditosAnimando = false;
    var timerFinalCreditos = null;
    var creditosEstrelasConstruidas = false;
    var vozEscutaAtiva = false;
    var vozModoAtual = '';
    var vozReinicioManual = false;
    var pollingRemotoAtivo = false;

    if (window.BroadcastChannel) {
        try {
            previewChannel = new BroadcastChannel('conferencia_previa_malotes');
        } catch (e0) {
            previewChannel = null;
        }
    }

    function creditosDesativados() {
        return !!(desativarCreditosFinais && desativarCreditosFinais.checked);
    }

    function carregarPreferenciaCreditos() {
        var desativado = false;
        try {
            desativado = localStorage.getItem(storageCreditosKey) === '1';
        } catch (e) {
            desativado = false;
        }
        if (desativarCreditosFinais) {
            desativarCreditosFinais.checked = desativado;
        }
    }

    function salvarPreferenciaCreditos() {
        try {
            localStorage.setItem(storageCreditosKey, creditosDesativados() ? '1' : '0');
        } catch (e) {}
    }
    function salvarPreferenciaCreditos() {
        try {
            localStorage.setItem(storageCreditosKey, creditosDesativados() ? '1' : '0');
        } catch (e) {}
    }

    function detectarTurnoAutomatico() {
        var h = new Date().getHours();
        if (h < 6)  return 'Madrugada';
        if (h < 12) return 'Manhã';
        if (h < 18) return 'Tarde';
        return 'Noite';
    }



    function elementoVisivelNaTela(el) {
        if (!el) return false;
        if (el.offsetParent !== null) return true;
        if (typeof window.getComputedStyle === 'function') {
            var estilo = window.getComputedStyle(el);
            if (estilo && estilo.display === 'none') return false;
            if (estilo && estilo.visibility === 'hidden') return false;
        }
        return !!(el.getClientRects && el.getClientRects().length);
    }

    function obterLinhasCorreiosParaCreditos(somenteVisiveis) {
        var selector = 'table[data-view="correios"] tbody tr[data-codigo][data-ispt!="1"]';
        var todas = document.querySelectorAll(selector);
        var linhas = [];
        for (var i = 0; i < todas.length; i++) {
            if (!somenteVisiveis || elementoVisivelNaTela(todas[i])) {
                linhas.push(todas[i]);
            }
        }
        if (!linhas.length && somenteVisiveis) {
            return obterLinhasCorreiosParaCreditos(false);
        }
        return linhas;
    }

    function obterTabelasCorreiosVisiveis() {
        var todas = document.querySelectorAll('#tabelas table[data-view="correios"]');
        var tabelas = [];
        for (var i = 0; i < todas.length; i++) {
            if (elementoVisivelNaTela(todas[i])) {
                tabelas.push(todas[i]);
            }
        }
        if (!tabelas.length) {
            for (var j = 0; j < todas.length; j++) {
                tabelas.push(todas[j]);
            }
        }
        return tabelas;
    }

    function todosCorreiosConferidos() {
        // Créditos finais entram quando todos os lotes dos Correios visíveis estiverem conferidos.
        // Lotes do Poupa Tempo não devem bloquear esse encerramento.
        var tabelasVisiveis = obterTabelasCorreiosVisiveis();
        if (tabelasVisiveis.length) {
            var totalGeral = 0;
            var conferidosGeral = 0;
            for (var t = 0; t < tabelasVisiveis.length; t++) {
                var titulo = obterTituloTabela(tabelasVisiveis[t]);
                var span = titulo ? titulo.querySelector('.contagem-pacotes') : null;
                if (!span) continue;
                var totalTabela = parseInt(span.getAttribute('data-total') || '0', 10) || 0;
                var conferidosTabela = parseInt(span.getAttribute('data-conferidos') || '0', 10) || 0;
                totalGeral += totalTabela;
                conferidosGeral += conferidosTabela;
            }
            if (totalGeral > 0) {
                return conferidosGeral >= totalGeral;
            }
        }

        var linhas = obterLinhasCorreiosParaCreditos(true);
        if (!linhas || !linhas.length) return false;
        for (var i = 0; i < linhas.length; i++) {
            if (!linhas[i].classList.contains('confirmado')) {
                return false;
            }
        }
        return true;
    }

    function montarResumoFinalConferencia() {
        var linhas = obterLinhasCorreiosParaCreditos(true);
        var totalPacotes = linhas.length;
        var postos = {};
        var regionais = {};

        for (var i = 0; i < linhas.length; i++) {
            var posto = String(linhas[i].getAttribute('data-posto') || '').trim();
            var regional = String(linhas[i].getAttribute('data-regional-real') || linhas[i].getAttribute('data-regional') || '').trim();
            if (posto) postos[posto] = true;
            if (regional) regionais[regional] = true;
        }

        var totalPostos = Object.keys(postos).length;
        var totalRegionais = Object.keys(regionais).length;
        var resumo = montarResumoPreviaMalotes();
        var totalMalotes = resumo && resumo.total_fechados ? resumo.total_fechados : 0;
        var pendencias = resumo && resumo.pendentes ? resumo.pendentes.length : 0;

        return {
            totalPacotes: totalPacotes,
            totalPostos: totalPostos,
            totalRegionais: totalRegionais,
            totalMalotes: totalMalotes,
            pendencias: pendencias,
            usuario: usuarioAtual || 'Equipe de Conferência',
            geradoEm: formatarDataHoraAtual(),
            datas: (datasFiltroSql && datasFiltroSql.length) ? datasFiltroSql.join(', ') : 'N/A'
        };
    }

    function garantirEstrelasCreditos() {
        if (creditosEstrelasConstruidas || !creditosStarfield) return;
        creditosEstrelasConstruidas = true;
        for (var i = 0; i < 120; i++) {
            var estrela = document.createElement('div');
            var tamanho = (Math.random() * 2.2) + 0.5;
            estrela.className = 'creditos-star';
            estrela.style.width = tamanho + 'px';
            estrela.style.height = tamanho + 'px';
            estrela.style.left = (Math.random() * 100) + '%';
            estrela.style.top = (Math.random() * 100) + '%';
            estrela.style.animationDelay = (Math.random() * 4) + 's';
            estrela.style.animationDuration = (2 + Math.random() * 3) + 's';
            creditosStarfield.appendChild(estrela);
        }
    }

    function resetarVisualCreditos() {
        if (timerFinalCreditos) {
            clearTimeout(timerFinalCreditos);
            timerFinalCreditos = null;
        }
        if (creditosTrilha) {
            creditosTrilha.style.transition = 'none';
            creditosTrilha.style.transform = 'translate(-50%, 0)';
            creditosTrilha.style.opacity = '1';
        }
        if (creditosOverlay) {
            creditosOverlay.classList.remove('ativo');
            creditosOverlay.classList.remove('final');
            creditosOverlay.setAttribute('aria-hidden', 'true');
        }
        if (creditosEndScreen) {
            creditosEndScreen.style.opacity = '';
        }
    }

    function renderizarCreditosFinais() {
        if (!creditosTrilha) return;
        var r = montarResumoFinalConferencia();
        var secaoResumo = [
            '<div class="creditos-secao">',
                '<div class="creditos-titulo">Encerramento da Conferência</div>',
                '<div class="creditos-subtitulo">Operação concluída com sucesso</div>',
            '</div>',
            '<div class="creditos-divisor"></div>',
            '<div class="creditos-secao">',
                '<div class="creditos-grupo">Resumo da operação</div>',
                '<div class="creditos-pair"><div class="creditos-pair-role">Pacotes conferidos</div><div class="creditos-pair-name">' + escapeHtml(String(r.totalPacotes)) + '</div></div>',
                '<div class="creditos-pair"><div class="creditos-pair-role">Postos atendidos</div><div class="creditos-pair-name">' + escapeHtml(String(r.totalPostos)) + '</div></div>',
                '<div class="creditos-pair"><div class="creditos-pair-role">Regionais fechadas</div><div class="creditos-pair-name">' + escapeHtml(String(r.totalRegionais)) + '</div></div>',
                '<div class="creditos-pair"><div class="creditos-pair-role">Malotes consolidados</div><div class="creditos-pair-name">' + escapeHtml(String(r.totalMalotes)) + '</div></div>',
                '<div class="creditos-pair"><div class="creditos-pair-role">Pendências</div><div class="creditos-pair-name">' + escapeHtml(String(r.pendencias)) + '</div></div>',
            '</div>',
            '<div class="creditos-divisor"></div>'
        ].join('');

        var secaoDatas = [
            '<div class="creditos-secao">',
                '<div class="creditos-grupo">Janela da conferência</div>',
                '<div class="creditos-role">Datas processadas</div>',
                '<div class="creditos-bloco">' + escapeHtml(r.datas) + '</div>',
                '<div class="creditos-role">Encerramento</div>',
                '<div class="creditos-sub">' + escapeHtml(r.geradoEm) + '</div>',
            '</div>',
            '<div class="creditos-divisor"></div>'
        ].join('');

        var secaoCreditos = [
            '<div class="creditos-secao">',
                '<div class="creditos-grupo">Créditos</div>',
                '<div class="creditos-pair"><div class="creditos-pair-role">Conferência e coordenação</div><div class="creditos-pair-name">' + escapeHtml(r.usuario) + '</div></div>',
                '<div class="creditos-pair"><div class="creditos-pair-role">Impressão e apoio operacional</div><div class="creditos-pair-name">Andre Agra</div></div>',
                '<div class="creditos-pair"><div class="creditos-pair-role">Fechamento, conferência e despacho</div><div class="creditos-pair-name">Equipe de Expedição</div></div>',
                '<div class="creditos-pair"><div class="creditos-pair-role">Sistema e melhorias contínuas</div><div class="creditos-pair-name">Equipe de Tecnologia</div></div>',
            '</div>',
            '<div class="creditos-divisor"></div>'
        ].join('');

        var secaoFinal = [
            '<div class="creditos-secao">',
                '<div class="creditos-grupo">Mensagem final</div>',
                '<div class="creditos-quote">"Todos os lotes dos Correios que estavam na tela foram conferidos e encerrados. Missão cumprida."</div>',
                '<div class="creditos-sub">Sistema de Conferência de Pacotes v0.9.25.16</div>',
            '</div>',
            '<div style="height:180px"></div>'
        ].join('');

        creditosTrilha.innerHTML = '' +
            '<div style="height:120px"></div>' +
            secaoResumo +
            secaoDatas +
            secaoCreditos +
            secaoFinal;
    }

    function concluirCreditosComTheEnd() {
        if (!creditosAtivos || !creditosOverlay) return;
        creditosAnimando = false;
        creditosOverlay.classList.add('final');
    }

    function pararCreditosFinais(forcar) {
        if (!creditosAtivos) return;
        if (!forcar && creditosAnimando) return;
        creditosAtivos = false;
        creditosAnimando = false;
        resetarVisualCreditos();
        if (musicaFinalConferencia) {
            try {
                musicaFinalConferencia.pause();
                musicaFinalConferencia.currentTime = 0;
            } catch (e) {}
        }
    }

    function iniciarCreditosFinais() {
        if (creditosAtivos || creditosJaExibidos || creditosDesativados()) return;
        creditosJaExibidos = true;
        creditosAtivos = true;
        creditosAnimando = true;
        creditosIniciadoEm = Date.now();
        garantirEstrelasCreditos();
        renderizarCreditosFinais();

        if (creditosOverlay) {
            creditosOverlay.classList.add('ativo');
            creditosOverlay.classList.remove('final');
            creditosOverlay.setAttribute('aria-hidden', 'false');
        }
        if (creditosTrilha) {
            creditosTrilha.style.transition = 'none';
            creditosTrilha.style.transform = 'translate(-50%, 0)';
            creditosTrilha.style.opacity = '1';
            void creditosTrilha.offsetHeight;
            var deslocamento = window.innerHeight + creditosTrilha.scrollHeight + 220;
            var duracaoMs = Math.max(30000, Math.min(85000, deslocamento * 24));
            creditosTrilha.style.transition = 'transform ' + duracaoMs + 'ms linear';
            requestAnimationFrame(function() {
                requestAnimationFrame(function() {
                    creditosTrilha.style.transform = 'translate(-50%, -' + deslocamento + 'px)';
                });
            });
            timerFinalCreditos = setTimeout(function() {
                concluirCreditosComTheEnd();
            }, duracaoMs + 250);
        }
        if (musicaFinalConferencia) {
            try {
                musicaFinalConferencia.currentTime = 0;
                musicaFinalConferencia.play();
            } catch (e) {}
        }
    }

    function aguardarFilaSonsLivre(callback, tentativa) {
        var t = tentativa || 0;
        if (!tocando && filaSons.length === 0) {
            callback();
            return;
        }
        if (t > 60) {
            callback();
            return;
        }
        setTimeout(function() {
            aguardarFilaSonsLivre(callback, t + 1);
        }, 250);
    }

    function verificarConclusaoFinalCorreios() {
        if (!todosCorreiosConferidos()) {
            if (timerInicioCreditos) {
                clearTimeout(timerInicioCreditos);
                timerInicioCreditos = null;
            }
            if (creditosAtivos) {
                pararCreditosFinais(true);
            }
            creditosJaExibidos = false;
            return;
        }

        if (creditosDesativados()) {
            if (mensagemLeitura) {
                mensagemLeitura.innerHTML = '<strong>Conferência finalizada:</strong> créditos finais estão desativados no topo da tela.';
            }
            return;
        }

        if (creditosAtivos || creditosJaExibidos || timerInicioCreditos) return;

        timerInicioCreditos = setTimeout(function() {
            timerInicioCreditos = null;
            if (!todosCorreiosConferidos()) return;
            aguardarFilaSonsLivre(function() {
                iniciarCreditosFinais();
            }, 0);
        }, 1000);
    }

    function aplicarInterrupcaoCreditos() {
        if (!creditosAtivos) return;
        if ((Date.now() - creditosIniciadoEm) < 350) return;
        pararCreditosFinais(true);
    }

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

    // Expõe funções críticas cedo para que handlers inline e fallbacks consigam operar
    // mesmo se alguma parte tardia da inicialização falhar.
    window.processarLeituraCodigo = function(valor) {
        return processarLeituraCodigo(valor);
    };
    window.liberarPaginaComUsuario = function(nome, restaurar) {
        return liberarPaginaComUsuario(nome, restaurar);
    };
    window.selecionarTipoConferencia = function(tipo) {
        return selecionarTipoConferencia(tipo);
    };

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

    function escapeHtml(texto) {
        return String(texto || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function normalizarNumeroLacre(valor) {
        var digitos = String(valor || '').replace(/\D+/g, '');
        // Tratar "0" (ou "0000") como sem lacre para não bloquear novos salvamentos
        if (!digitos || /^0+$/.test(digitos)) {
            return '';
        }
        return digitos;
    }

    function normalizarTextoVoz(texto) {
        return String(texto || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9\s]/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function extrairDigitosFalados(texto) {
        var direto = String(texto || '').replace(/\D+/g, '');
        if (direto) return direto;
        var mapa = {
            zero: '0',
            um: '1',
            uma: '1',
            dois: '2',
            duas: '2',
            tres: '3',
            quatro: '4',
            cinco: '5',
            seis: '6',
            meia: '6',
            sete: '7',
            oito: '8',
            nove: '9'
        };
        var normalizado = normalizarTextoVoz(texto);
        if (!normalizado) return '';
        var partes = normalizado.split(' ');
        var digitos = [];
        for (var i = 0; i < partes.length; i++) {
            if (mapa[partes[i]]) {
                digitos.push(mapa[partes[i]]);
            }
        }
        return digitos.join('');
    }

    function atualizarStatusVoz(texto, tipo) {
        if (statusVozComando) {
            statusVozComando.textContent = texto;
        }
        if (btnAlternarVozMalotes) {
            btnAlternarVozMalotes.classList.toggle('ativo', vozEscutaAtiva);
            btnAlternarVozMalotes.textContent = vozEscutaAtiva ? 'Desligar microfone' : 'Ativar microfone';
        }
        if (vozCampoAtual) {
            vozCampoAtual.className = 'voz-status-pill';
            if (tipo) {
                vozCampoAtual.classList.add(tipo);
            }
        }
    }

    function atualizarCampoVoz(texto, tipo) {
        if (!vozCampoAtual) return;
        vozCampoAtual.textContent = texto;
        vozCampoAtual.className = 'voz-status-pill';
        if (tipo) {
            vozCampoAtual.classList.add(tipo);
        }
    }

    function obterDiagnosticoVoz() {
        var SpeechRecognitionApi = window.SpeechRecognition || window.webkitSpeechRecognition;
        var protocolo = window.location && window.location.protocol ? window.location.protocol : '';
        var host = window.location && window.location.host ? window.location.host : '';
        var hostname = window.location && window.location.hostname ? window.location.hostname : '';
        var secure = !!window.isSecureContext;
        var localhost = hostname === 'localhost' || hostname === '127.0.0.1';
        var mediaDevices = !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
        var userAgent = navigator.userAgent || '';
        var navegador = 'Navegador não identificado';

        if (/Edg\//.test(userAgent)) {
            navegador = 'Microsoft Edge';
        } else if (/Chrome\//.test(userAgent) && !/Edg\//.test(userAgent)) {
            navegador = 'Google Chrome';
        } else if (/Firefox\//.test(userAgent)) {
            navegador = 'Mozilla Firefox';
        } else if (/Safari\//.test(userAgent) && !/Chrome\//.test(userAgent)) {
            navegador = 'Safari';
        } else if (/OPR\//.test(userAgent)) {
            navegador = 'Opera';
        }

        var causas = [];
        if (!SpeechRecognitionApi) {
            causas.push('A API SpeechRecognition não existe neste navegador.');
        }
        if (!secure && !localhost) {
            causas.push('A página não está em HTTPS nem em localhost.');
        }
        if (/Firefox\//.test(userAgent)) {
            causas.push('Firefox normalmente não expõe a API usada nesta implementação.');
        }
        if (/OPR\//.test(userAgent)) {
            causas.push('Opera pode esconder essa API mesmo com microfone liberado.');
        }
        if (!mediaDevices) {
            causas.push('O navegador não expõe getUserMedia para testes de microfone.');
        }

        return {
            speech: !!SpeechRecognitionApi,
            media: mediaDevices,
            secure: secure,
            localhost: localhost,
            protocolo: protocolo,
            host: host,
            navegador: navegador,
            userAgent: userAgent,
            causas: causas
        };
    }

    function renderizarDiagnosticoVoz() {
        if (!painelDiagnosticoVoz) return;
        var diag = obterDiagnosticoVoz();
        var linhas = [];
        linhas.push('<li><span class="' + (diag.speech ? 'ok' : 'erro') + '">SpeechRecognition:</span> ' + (diag.speech ? 'disponível' : 'indisponível') + '</li>');
        linhas.push('<li><span class="' + (diag.media ? 'ok' : 'erro') + '">Microfone do navegador:</span> ' + (diag.media ? 'API disponível' : 'API indisponível') + '</li>');
        linhas.push('<li><span class="' + ((diag.secure || diag.localhost) ? 'ok' : 'aviso') + '">Origem da página:</span> ' + (diag.protocolo || '-') + '//' + (diag.host || '-') + '</li>');
        linhas.push('<li><span class="ok">Navegador detectado:</span> ' + diag.navegador + '</li>');

        if (diag.causas.length) {
            linhas.push('<li><span class="erro">Causa provável:</span> ' + diag.causas.join(' ')+ '</li>');
        } else {
            linhas.push('<li><span class="ok">Compatibilidade:</span> o navegador parece apto para reconhecimento de voz.</li>');
        }

        painelDiagnosticoVoz.innerHTML = '<strong>Diagnóstico de voz</strong><ul>' + linhas.join('') + '</ul>';
    }

    function obterInputPorModoVoz(modo) {
        if (modo === 'iipr') return inputLacreIiprMalote;
        if (modo === 'correios_lacre') return inputLacreCorreiosMalote;
        if (modo === 'correios_etiqueta') return inputEtiquetaCorreiosMalote;
        return null;
    }

    function limparModoVoz(motivo) {
        vozModoAtual = '';
        atualizarCampoVoz('Nenhum campo armado.', '');
        if (motivo) {
            atualizarStatusVoz(motivo, vozEscutaAtiva ? 'escutando' : '');
        }
    }

    function definirModoVoz(modo, mensagem) {
        var input = obterInputPorModoVoz(modo);
        vozModoAtual = modo;
        if (input) {
            input.focus();
            input.select();
        }
        atualizarCampoVoz(mensagem, 'aguardando');
        atualizarStatusVoz('Microfone ativo. Aguardando o valor do campo armado.', 'escutando');
        falarTexto(mensagem);
    }

    function preencherCampoPorVoz(valor) {
        var input = obterInputPorModoVoz(vozModoAtual);
        if (!input) {
            atualizarStatusVoz('Nenhum campo de voz está armado no momento.', 'erro');
            return false;
        }
        var limite = vozModoAtual === 'correios_etiqueta' ? 35 : 12;
        var valorFinal = String(valor || '').replace(/\D+/g, '').slice(0, limite);
        if (!valorFinal) {
            atualizarStatusVoz('Nenhum dígito válido foi reconhecido.', 'erro');
            return false;
        }
        input.value = valorFinal;
        input.focus();
        atualizarCampoVoz('Campo preenchido por voz: ' + valorFinal, 'aguardando');
        atualizarStatusVoz('Valor recebido. Você pode salvar pelo botão ou por voz.', 'escutando');
        falarTexto('valor preenchido');
        return true;
    }

    function compactarSequenciaNumerica(valores) {
        var numeros = [];
        var textos = [];
        var vistos = {};
        for (var i = 0; i < valores.length; i++) {
            var atual = String(valores[i] || '').trim();
            if (!atual || vistos[atual]) continue;
            vistos[atual] = true;
            if (/^\d+$/.test(atual)) {
                numeros.push(parseInt(atual, 10));
            } else {
                textos.push(atual);
            }
        }
        numeros.sort(function(a, b) { return a - b; });
        var partes = [];
        var inicio = null;
        var anterior = null;
        for (var j = 0; j < numeros.length; j++) {
            var numero = numeros[j];
            if (inicio === null) {
                inicio = numero;
                anterior = numero;
                continue;
            }
            if (numero === anterior + 1) {
                anterior = numero;
                continue;
            }
            partes.push(inicio === anterior ? String(inicio) : (inicio + '-' + anterior));
            inicio = numero;
            anterior = numero;
        }
        if (inicio !== null) {
            partes.push(inicio === anterior ? String(inicio) : (inicio + '-' + anterior));
        }
        for (var k = 0; k < textos.length; k++) {
            partes.push(textos[k]);
        }
        return partes.join(', ');
    }

    function montarChaveConsolidacaoCorreios(dados) {
        var posto = String(dados && dados.posto || '').trim();
        var regional = String(dados && (dados.regional_codigo || dados.regional) || '').trim();
        var lacreCorreios = String(dados && dados.lacre_correios || '').trim();
        var etiquetaCorreios = String(dados && dados.etiqueta_correios || '').trim();
        if (lacreCorreios || etiquetaCorreios) {
            return ['COR', posto || '-', regional || '-', lacreCorreios || '-', etiquetaCorreios || '-'].join('|');
        }
        var grupoCorreios = String(dados && dados.grupo_correios || '').trim();
        if (grupoCorreios) {
            return ['GC', posto || '-', regional || '-', grupoCorreios].join('|');
        }
        var grupoIipr = String(dados && dados.grupo_iipr || '').trim();
        if (grupoIipr) {
            return ['GI', posto || '-', regional || '-', grupoIipr].join('|');
        }
        var lacreIipr = String(dados && dados.lacre_iipr || '').trim();
        return ['LI', posto || '-', regional || '-', lacreIipr || '-'].join('|');
    }

    function criarEstruturaRascunhoMalotes() {
        return { postos: {}, atribuicoes: {} };
    }

    function clonarEstruturaSimples(valor) {
        try {
            return JSON.parse(JSON.stringify(valor || null));
        } catch (e) {
            return null;
        }
    }

    function mostrarAvisoMalotes(texto, tipo) {
        if (!mensagemLeitura) {
            return;
        }
        var classe = tipo === 'erro' ? 'Aviso' : 'Aviso';
        mensagemLeitura.innerHTML = '<strong>' + classe + ':</strong> ' + escapeHtml(String(texto || ''));
    }

    function capturarEstadoChipMalote(chip) {
        if (!chip) return null;
        return {
            codigo: chip.getAttribute('data-codigo') || '',
            posto: chip.getAttribute('data-posto') || '',
            lacre_iipr: chip.getAttribute('data-lacre-iipr') || '',
            grupo_iipr: chip.getAttribute('data-grupo-iipr') || '',
            lacre_correios: chip.getAttribute('data-lacre-correios') || '',
            grupo_correios: chip.getAttribute('data-grupo-correios') || '',
            etiqueta_correios: chip.getAttribute('data-etiqueta-correios') || '',
            usuario_lacre: chip.getAttribute('data-usuario-lacre') || '',
            atualizado_lacre: chip.getAttribute('data-atualizado-lacre') || '',
            pendente: String(chip.getAttribute('data-malote-pendente') || '') === '1'
        };
    }

    function registrarHistoricoMalote(posto, tipo, chips, descricao) {
        if (!chips || !chips.length) return;
        var estados = [];
        for (var i = 0; i < chips.length; i++) {
            var estado = capturarEstadoChipMalote(chips[i]);
            if (estado && estado.codigo) {
                estados.push(estado);
            }
        }
        if (!estados.length) return;
        historicoMalotes.push({
            posto: String(posto || ''),
            tipo: String(tipo || ''),
            descricao: String(descricao || ''),
            rascunho: clonarEstruturaSimples(malotesRascunho) || criarEstruturaRascunhoMalotes(),
            estados: estados
        });
        if (historicoMalotes.length > 60) {
            historicoMalotes.shift();
        }
    }

    function limparHistoricoMaloteDoPosto(posto) {
        var alvo = String(posto || '');
        if (!alvo) return;
        var filtrado = [];
        for (var i = 0; i < historicoMalotes.length; i++) {
            if (String(historicoMalotes[i].posto || '') !== alvo) {
                filtrado.push(historicoMalotes[i]);
            }
        }
        historicoMalotes = filtrado;
    }

    function desfazerUltimaAcaoMalote(posto) {
        var alvo = String(posto || '');
        for (var i = historicoMalotes.length - 1; i >= 0; i--) {
            var item = historicoMalotes[i];
            if (String(item.posto || '') !== alvo) continue;
            historicoMalotes.splice(i, 1);
            malotesRascunho = clonarEstruturaSimples(item.rascunho) || criarEstruturaRascunhoMalotes();
            for (var j = 0; j < item.estados.length; j++) {
                var chip = obterChipPorCodigo(item.estados[j].codigo || '');
                if (!chip) continue;
                aplicarAtribuicaoNoChip(chip, item.estados[j]);
            }
            salvarRascunhoMalotesLocal();
            renderizarPainelMalotes();
            atualizarColunaMaloteTradicional();
            renderizarMapasMalotesPostos();
            publicarResumoPrevia();
            mostrarAvisoMalotes('Última ação desfeita no posto ' + alvo + (item.descricao ? ' (' + item.descricao + ')' : '') + '.', 'info');
            return true;
        }
        mostrarAvisoMalotes('Não há ação pendente para desfazer neste posto.', 'info');
        return false;
    }

    function coletarOcorrenciasLacre(valor, postoAtual) {
        var lacre = normalizarNumeroLacre(valor || '');
        var ocorrencias = [];
        var vistos = {};
        if (!lacre) return ocorrencias;

        var chips = document.querySelectorAll('.operacao-chip[data-codigo]');
        for (var i = 0; i < chips.length; i++) {
            var lacreIipr = normalizarNumeroLacre(chips[i].getAttribute('data-lacre-iipr') || '');
            var lacreCorreios = normalizarNumeroLacre(chips[i].getAttribute('data-lacre-correios') || '');
            if (lacreIipr !== lacre && lacreCorreios !== lacre) continue;
            var posto = String(chips[i].getAttribute('data-posto') || '').trim();
            var grupo = String(chips[i].getAttribute(lacreIipr === lacre ? 'data-grupo-iipr' : 'data-grupo-correios') || '').trim();
            var chave = (lacreIipr === lacre ? 'iipr' : 'correios') + '|' + (grupo || posto || chips[i].getAttribute('data-codigo') || '');
            if (vistos[chave]) continue;
            vistos[chave] = true;
            ocorrencias.push((lacreIipr === lacre ? 'Lacre IIPR' : 'Lacre Correios') + ' já usado no posto ' + (posto || '-') + (postoAtual && posto === postoAtual ? ' (mesmo posto)' : ''));
        }

        var campos = document.querySelectorAll('.input-inline-lacre-iipr, .input-inline-lacre-correios');
        for (var j = 0; j < campos.length; j++) {
            var valorCampo = normalizarNumeroLacre(campos[j].value || '');
            if (!valorCampo || valorCampo !== lacre) continue;
            var postoCampo = String(campos[j].getAttribute('data-posto') || '').trim();
            if (postoAtual && postoCampo === postoAtual && document.activeElement === campos[j]) {
                continue;
            }
            var chaveCampo = 'campo|' + postoCampo + '|' + campos[j].className;
            if (vistos[chaveCampo]) continue;
            vistos[chaveCampo] = true;
            ocorrencias.push('Campo aberto do posto ' + (postoCampo || '-') + ' já contém este lacre');
        }

        return ocorrencias;
    }

    function coletarOcorrenciasLacreIiprEntrePostos(valor, postoAtual) {
        var lacre = normalizarNumeroLacre(valor || '');
        var ocorrencias = [];
        var vistos = {};
        var postoBase = String(postoAtual || '').trim();
        if (!lacre) return ocorrencias;

        var chips = document.querySelectorAll('.operacao-chip[data-codigo]');
        for (var i = 0; i < chips.length; i++) {
            var lacreIipr = normalizarNumeroLacre(chips[i].getAttribute('data-lacre-iipr') || '');
            if (lacreIipr !== lacre) continue;
            var posto = String(chips[i].getAttribute('data-posto') || '').trim();
            if (postoBase && posto && posto === postoBase) continue;
            var grupo = String(chips[i].getAttribute('data-grupo-iipr') || '').trim();
            var chave = 'chip|' + (grupo || posto || chips[i].getAttribute('data-codigo') || '');
            if (vistos[chave]) continue;
            vistos[chave] = true;
            ocorrencias.push('Lacre IIPR já usado no posto ' + (posto || '-'));
        }

        var campos = document.querySelectorAll('.input-inline-lacre-iipr');
        for (var j = 0; j < campos.length; j++) {
            var valorCampo = normalizarNumeroLacre(campos[j].value || '');
            if (!valorCampo || valorCampo !== lacre) continue;
            var postoCampo = String(campos[j].getAttribute('data-posto') || '').trim();
            if (postoBase && postoCampo && postoCampo === postoBase) {
                if (document.activeElement === campos[j]) {
                    continue;
                }
                continue;
            }
            var chaveCampo = 'campo|' + postoCampo;
            if (vistos[chaveCampo]) continue;
            vistos[chaveCampo] = true;
            ocorrencias.push('Campo aberto do posto ' + (postoCampo || '-') + ' já contém este lacre IIPR');
        }

        return ocorrencias;
    }

    function anunciarLacresRepetidos() {
        var chave = 'lacres repetidos';
        var agora = Date.now();
        if (!window.__ultimoAvisoLacreRepetido || window.__ultimoAvisoLacreRepetido.texto !== chave || (agora - window.__ultimoAvisoLacreRepetido.tempo) > 2500) {
            window.__ultimoAvisoLacreRepetido = { texto: chave, tempo: agora };
            falarTexto('lacres repetidos');
        }
    }

    function validarLacreIiprUnicoEntrePostos(valor, postoAtual, silenciarAlerta) {
        var ocorrencias = coletarOcorrenciasLacreIiprEntrePostos(valor, postoAtual);
        if (!ocorrencias.length) {
            return true;
        }
        if (!silenciarAlerta) {
            mostrarAvisoMalotes('Há lacres IIPR repetidos entre postos distintos: ' + ocorrencias.join(' | '), 'erro');
            anunciarLacresRepetidos();
        }
        return false;
    }

    function coletarOcorrenciasEtiqueta(valor, postoAtual) {
        var etiqueta = String(valor || '').trim();
        var ocorrencias = [];
        var vistos = {};
        if (!etiqueta) return ocorrencias;

        var chips = document.querySelectorAll('.operacao-chip[data-codigo]');
        for (var i = 0; i < chips.length; i++) {
            var etiquetaAtual = String(chips[i].getAttribute('data-etiqueta-correios') || '').trim();
            if (!etiquetaAtual || etiquetaAtual !== etiqueta) continue;
            var posto = String(chips[i].getAttribute('data-posto') || '').trim();
            var grupo = String(chips[i].getAttribute('data-grupo-correios') || '').trim();
            var chave = 'et|' + (grupo || posto || chips[i].getAttribute('data-codigo') || '');
            if (vistos[chave]) continue;
            vistos[chave] = true;
            ocorrencias.push('Etiqueta já aparece no posto ' + (posto || '-') + (postoAtual && posto === postoAtual ? ' (mesmo posto)' : ''));
        }

        var campos = document.querySelectorAll('.input-inline-etiqueta-correios');
        for (var j = 0; j < campos.length; j++) {
            var valorCampo = String(campos[j].value || '').trim();
            if (!valorCampo || valorCampo !== etiqueta) continue;
            var postoCampo = String(campos[j].getAttribute('data-posto') || '').trim();
            if (postoAtual && postoCampo === postoAtual && document.activeElement === campos[j]) {
                continue;
            }
            var chaveCampo = 'campo|' + postoCampo;
            if (vistos[chaveCampo]) continue;
            vistos[chaveCampo] = true;
            ocorrencias.push('Campo aberto do posto ' + (postoCampo || '-') + ' já contém esta etiqueta');
        }

        return ocorrencias;
    }

    function avisarSeHaDuplicidadeLacre(valor, postoAtual) {
        var ocorrencias = coletarOcorrenciasLacre(valor, postoAtual);
        if (ocorrencias.length) {
            mostrarAvisoMalotes('Há lacre repetido: ' + ocorrencias.join(' | '), 'info');
            if (!validarLacreIiprUnicoEntrePostos(valor, postoAtual, true)) {
                anunciarLacresRepetidos();
            }
        }
    }

    function avisarSeHaDuplicidadeEtiqueta(valor, postoAtual) {
        var ocorrencias = coletarOcorrenciasEtiqueta(valor, postoAtual);
        if (ocorrencias.length) {
            mostrarAvisoMalotes('Há etiqueta repetida: ' + ocorrencias.join(' | '), 'info');
        }
    }

    function criarIdMalote(prefixo) {
        return String(prefixo || 'M') + '_' + Date.now() + '_' + Math.floor(Math.random() * 100000);
    }

    function obterEstadoPostoRascunho(posto) {
        var chave = String(posto || '').trim();
        if (!chave) {
            return { iipr_atual: null, correios_atual: null };
        }
        if (!malotesRascunho || typeof malotesRascunho !== 'object') {
            malotesRascunho = criarEstruturaRascunhoMalotes();
        }
        if (!malotesRascunho.postos) {
            malotesRascunho.postos = {};
        }
        if (!malotesRascunho.postos[chave]) {
            malotesRascunho.postos[chave] = { iipr_atual: null, correios_atual: null };
        }
        return malotesRascunho.postos[chave];
    }

    function salvarRascunhoMalotesLocal() {
        try {
            localStorage.setItem(storageMalotesRascunhoKey, JSON.stringify(malotesRascunho));
        } catch (e) {}
    }

    function carregarRascunhoMalotesLocal() {
        malotesRascunho = criarEstruturaRascunhoMalotes();
        try {
            var bruto = localStorage.getItem(storageMalotesRascunhoKey);
            if (!bruto) return;
            var salvo = JSON.parse(bruto);
            if (!salvo || typeof salvo !== 'object') return;
            malotesRascunho.postos = salvo.postos && typeof salvo.postos === 'object' ? salvo.postos : {};
            malotesRascunho.atribuicoes = salvo.atribuicoes && typeof salvo.atribuicoes === 'object' ? salvo.atribuicoes : {};
        } catch (e) {
            malotesRascunho = criarEstruturaRascunhoMalotes();
        }
    }

    function limparRascunhoMalotesLocal() {
        malotesRascunho = criarEstruturaRascunhoMalotes();
        try {
            localStorage.removeItem(storageMalotesRascunhoKey);
        } catch (e) {}
    }

    function normalizarListaUnica(valores) {
        var saida = [];
        var vistos = {};
        for (var i = 0; i < valores.length; i++) {
            var valor = String(valores[i] || '').trim();
            if (!valor || vistos[valor]) continue;
            vistos[valor] = true;
            saida.push(valor);
        }
        return saida;
    }

    function contarAtribuicoesPendentesMalotes() {
        var total = 0;
        if (!malotesRascunho || !malotesRascunho.atribuicoes) return total;
        for (var codigo in malotesRascunho.atribuicoes) {
            if (Object.prototype.hasOwnProperty.call(malotesRascunho.atribuicoes, codigo)) {
                total++;
            }
        }
        return total;
    }

    function atualizarResumoPendenciasMalotes() {
        if (malotesResumoPendentesSalvar) {
            malotesResumoPendentesSalvar.textContent = String(contarAtribuicoesPendentesMalotes());
        }
    }

    function obterChipPorCodigo(codigo) {
        var chave = String(codigo || '').trim();
        if (!chave) return null;
        return document.querySelector('.operacao-chip[data-codigo="' + chave + '"]');
    }

    function obterChipsPorGrupoIipr(posto, grupoIipr) {
        var grupo = String(grupoIipr || '').trim();
        if (!posto || !grupo) return [];
        return Array.prototype.slice.call(document.querySelectorAll('.operacao-chip[data-posto="' + posto + '"][data-grupo-iipr="' + grupo + '"]'));
    }

    function limparGruposVaziosRascunho(posto) {
        var estado = obterEstadoPostoRascunho(posto);
        if (estado.iipr_atual && (!estado.iipr_atual.codigos || !estado.iipr_atual.codigos.length)) {
            estado.iipr_atual = null;
        }
        if (estado.correios_atual && estado.correios_atual.grupos_iipr) {
            var gruposValidos = [];
            for (var i = 0; i < estado.correios_atual.grupos_iipr.length; i++) {
                if (obterChipsPorGrupoIipr(posto, estado.correios_atual.grupos_iipr[i]).length) {
                    gruposValidos.push(estado.correios_atual.grupos_iipr[i]);
                }
            }
            estado.correios_atual.grupos_iipr = gruposValidos;
            if (!estado.correios_atual.grupos_iipr.length) {
                estado.correios_atual = null;
            }
        }
    }

    function removerCodigoDosRascunhosPosto(posto, codigo, grupoIipr) {
        var estado = obterEstadoPostoRascunho(posto);
        var cod = String(codigo || '').trim();
        var grupo = String(grupoIipr || '').trim();
        if (estado.iipr_atual && estado.iipr_atual.codigos) {
            estado.iipr_atual.codigos = estado.iipr_atual.codigos.filter(function(item) {
                return String(item || '').trim() !== cod;
            });
        }
        if (estado.correios_atual && estado.correios_atual.grupos_iipr && grupo) {
            var aindaExisteGrupo = obterChipsPorGrupoIipr(posto, grupo).some(function(chip) {
                return String(chip.getAttribute('data-codigo') || '').trim() !== cod;
            });
            if (!aindaExisteGrupo) {
                estado.correios_atual.grupos_iipr = estado.correios_atual.grupos_iipr.filter(function(item) {
                    return String(item || '').trim() !== grupo;
                });
            }
        }
        limparGruposVaziosRascunho(posto);
    }

    function aplicarAtribuicoesPendentesDoRascunho() {
        if (!malotesRascunho || !malotesRascunho.atribuicoes) return;
        for (var codigo in malotesRascunho.atribuicoes) {
            if (!Object.prototype.hasOwnProperty.call(malotesRascunho.atribuicoes, codigo)) continue;
            var chip = obterChipPorCodigo(codigo);
            var payload = malotesRascunho.atribuicoes[codigo];
            if (!chip || !payload) continue;
            aplicarAtribuicaoNoChip(chip, {
                lacre_iipr: payload.lacre_iipr || '',
                grupo_iipr: payload.grupo_iipr || '',
                lacre_correios: payload.lacre_correios || '',
                grupo_correios: payload.grupo_correios || '',
                etiqueta_correios: payload.etiqueta_correios || '',
                usuario_lacre: payload.usuario_lacre || usuarioAtual || '',
                atualizado_lacre: payload.atualizado_lacre || '',
                pendente: true
            });
        }
    }

    function registrarAtribuicaoPendenteNoChip(chip, sobrescritas) {
        var payload = montarPacoteParaPersistencia(chip, sobrescritas);
        if (!payload || !payload.codbar) return null;
        payload.usuario_lacre = usuarioAtual || '';
        payload.atualizado_lacre = formatarDataHoraAtual();
        malotesRascunho.atribuicoes[payload.codbar] = payload;
        aplicarAtribuicaoNoChip(chip, {
            lacre_iipr: payload.lacre_iipr || '',
            grupo_iipr: payload.grupo_iipr || '',
            lacre_correios: payload.lacre_correios || '',
            grupo_correios: payload.grupo_correios || '',
            etiqueta_correios: payload.etiqueta_correios || '',
            usuario_lacre: payload.usuario_lacre || '',
            atualizado_lacre: payload.atualizado_lacre || '',
            pendente: true
        });
        return payload;
    }

    function limparAtribuicaoPendenteSalva(chip) {
        if (!chip) return;
        var codigo = String(chip.getAttribute('data-codigo') || '').trim();
        var dados = obterDadosChipOperacao(chip);
        if (codigo && malotesRascunho && malotesRascunho.atribuicoes && malotesRascunho.atribuicoes[codigo]) {
            delete malotesRascunho.atribuicoes[codigo];
        }
        aplicarAtribuicaoNoChip(chip, {
            lacre_iipr: dados ? dados.lacre_iipr : '',
            grupo_iipr: dados ? dados.grupo_iipr : '',
            lacre_correios: dados ? dados.lacre_correios : '',
            grupo_correios: dados ? dados.grupo_correios : '',
            etiqueta_correios: dados ? dados.etiqueta_correios : '',
            usuario_lacre: dados ? dados.usuario_lacre : '',
            atualizado_lacre: dados ? dados.atualizado_lacre : '',
            pendente: false
        });
    }

    function abrirPreviaMalotes() {
        previewWindowRef = window.open('conferencia_pacotes_previa.php', '_blank');
        if (previewWindowRef) {
            if (statusPreviaMalotes) {
                statusPreviaMalotes.textContent = 'Prévia aberta. Arraste a janela para a segunda tela.';
            }
            publicarResumoPrevia();
        } else if (statusPreviaMalotes) {
            statusPreviaMalotes.textContent = 'O navegador bloqueou a abertura automática da prévia.';
        }
    }

    function abrirControleRemoto() {
        var alvo = 'conferencia_pacotes_controle.php?canal_controle=' + encodeURIComponent(controleCanal || 'principal');
        var janela = window.open(alvo, '_blank');
        if (janela) {
            if (statusControleRemoto) {
                statusControleRemoto.textContent = 'Controle remoto aberto. Use o mesmo canal no celular.';
            }
        } else if (statusControleRemoto) {
            statusControleRemoto.textContent = 'O navegador bloqueou a abertura do controle remoto.';
        }
    }

    function montarResumoPreviaMalotes() {
        var chips = document.querySelectorAll('.operacao-chip');
        var grupos = {};
        var pendentes = {};
        var totalConfirmados = 0;
        for (var i = 0; i < chips.length; i++) {
            var dados = obterDadosChipOperacao(chips[i]);
            if (!dados || !dados.conferido || dados.isPT) continue;
            totalConfirmados++;
            if (!dados.lacre_iipr) {
                var chavePendente = (dados.posto || '-') + '|' + (dados.regional || '-');
                if (!pendentes[chavePendente]) {
                    pendentes[chavePendente] = {
                        posto: dados.posto || '',
                        regional: dados.regional || '',
                        regional_codigo: dados.regional_codigo || '',
                        lotes: [],
                        qtd_total: 0
                    };
                }
                pendentes[chavePendente].lotes.push(dados.lote || '');
                pendentes[chavePendente].qtd_total += parseInt(dados.qtd || 0, 10) || 0;
                continue;
            }

            var chaveGrupo = montarChaveConsolidacaoCorreios(dados);
            if (!grupos[chaveGrupo]) {
                grupos[chaveGrupo] = {
                    regional: dados.regional || '',
                    regional_codigo: dados.regional_codigo || '',
                    posto: dados.posto || '',
                    lotes: [],
                    qtd_total: 0,
                    lacres_iipr: [],
                    lacres_correios: [],
                    etiqueta_correios: '',
                    grupo_iipr: [],
                    grupo_correios: dados.grupo_correios || '',
                    grupos_correios: [],
                    row_key: chaveGrupo
                };
            }
            grupos[chaveGrupo].lotes.push(dados.lote || '');
            grupos[chaveGrupo].qtd_total += parseInt(dados.qtd || 0, 10) || 0;
            if (dados.lacre_iipr && grupos[chaveGrupo].lacres_iipr.indexOf(String(dados.lacre_iipr)) === -1) grupos[chaveGrupo].lacres_iipr.push(String(dados.lacre_iipr));
            if (dados.lacre_correios && grupos[chaveGrupo].lacres_correios.indexOf(String(dados.lacre_correios)) === -1) grupos[chaveGrupo].lacres_correios.push(String(dados.lacre_correios));
            if (dados.grupo_iipr && grupos[chaveGrupo].grupo_iipr.indexOf(dados.grupo_iipr) === -1) grupos[chaveGrupo].grupo_iipr.push(dados.grupo_iipr);
            if (dados.grupo_correios && grupos[chaveGrupo].grupos_correios.indexOf(dados.grupo_correios) === -1) grupos[chaveGrupo].grupos_correios.push(dados.grupo_correios);
            if (!grupos[chaveGrupo].etiqueta_correios && dados.etiqueta_correios) {
                grupos[chaveGrupo].etiqueta_correios = dados.etiqueta_correios;
            }
        }

        var resumo = [];
        for (var chave in grupos) {
            if (!Object.prototype.hasOwnProperty.call(grupos, chave)) continue;
            resumo.push({
                regional: grupos[chave].regional,
                regional_codigo: grupos[chave].regional_codigo,
                posto: grupos[chave].posto,
                lotes: grupos[chave].lotes,
                qtd_total: grupos[chave].qtd_total,
                lacre_iipr: compactarSequenciaNumerica(grupos[chave].lacres_iipr),
                lacre_correios: compactarSequenciaNumerica(grupos[chave].lacres_correios),
                etiqueta_correios: grupos[chave].etiqueta_correios,
                grupo_iipr: grupos[chave].grupo_iipr.join(','),
                grupo_correios: grupos[chave].grupo_correios,
                grupos_correios: grupos[chave].grupos_correios.slice(0),
                row_key: grupos[chave].row_key
            });
        }

        resumo.sort(function(a, b) {
            var postoA = parseInt(a.posto || 0, 10) || 0;
            var postoB = parseInt(b.posto || 0, 10) || 0;
            if (postoA !== postoB) return postoA - postoB;
            if (a.regional < b.regional) return -1;
            if (a.regional > b.regional) return 1;
            return 0;
        });

        var listaPendentes = [];
        for (var pendente in pendentes) {
            if (!Object.prototype.hasOwnProperty.call(pendentes, pendente)) continue;
            listaPendentes.push(pendentes[pendente]);
        }
        listaPendentes.sort(function(a, b) {
            var postoA = parseInt(a.posto || 0, 10) || 0;
            var postoB = parseInt(b.posto || 0, 10) || 0;
            return postoA - postoB;
        });

        return {
            versao: '0.9.25.16',
            gerado_em: formatarDataHoraAtual(),
            usuario: usuarioAtual || '',
            posto_selecionado: postoSelecionadoMalote || '',
            datas_filtro: datasFiltroSql.slice(0),
            total_confirmados: totalConfirmados,
            total_fechados: resumo.length,
            resumo: resumo,
            pendentes: listaPendentes
        };
    }

    function publicarResumoPrevia() {
        var snapshot = montarResumoPreviaMalotes();
        try {
            localStorage.setItem(previewStorageKey, JSON.stringify(snapshot));
        } catch (e1) {}
        if (previewChannel) {
            try {
                previewChannel.postMessage(snapshot);
            } catch (e2) {}
        }
        if (statusPreviaMalotes) {
            statusPreviaMalotes.textContent = 'Última atualização: ' + snapshot.gerado_em + ' • linhas prontas: ' + snapshot.total_fechados;
        }
        publicarEstadoControleRemoto();
    }

    function publicarEstadoControleRemoto() {
        if (!controleCanal) return;
        var resumo = montarResumoPreviaMalotes();
        var primeiro = resumo && resumo.resumo && resumo.resumo.length ? resumo.resumo[0] : null;
        var formData = new FormData();
        formData.append('atualizar_estado_remoto_ajax', '1');
        formData.append('canal', controleCanal);
        formData.append('usuario', usuarioAtual || '');
        formData.append('posto', postoSelecionadoMalote || '');
        formData.append('regional', grupoSelecionadoMalote || '');
        formData.append('resumo', resumo.total_confirmados + ' confirmados / ' + resumo.total_fechados + ' linhas prontas');
        formData.append('lacre_iipr', inputLacreIiprMalote ? String(inputLacreIiprMalote.value || '').trim() : '');
        formData.append('lacre_correios', inputLacreCorreiosMalote ? String(inputLacreCorreiosMalote.value || '').trim() : '');
        formData.append('etiqueta_correios', inputEtiquetaCorreiosMalote ? String(inputEtiquetaCorreiosMalote.value || '').trim() : '');
        fetch('conferencia_pacotes.php', { method: 'POST', body: formData }).catch(function() {});
        if (statusControleRemoto) {
            statusControleRemoto.textContent = 'Canal ' + controleCanal + ' ativo. Posto atual: ' + (postoSelecionadoMalote || 'nenhum');
        }
    }

    function aplicarComandoRemoto(comando) {
        if (!comando || !comando.comando) return;
        var nome = String(comando.comando || '').toLowerCase();
        var valor = String(comando.valor || '').trim();
        var valorAux = String(comando.valor_aux || '').trim();

        if (nome === 'armar_iipr') {
            definirModoVoz('iipr', 'Aguardando lacre IIPR');
        } else if (nome === 'salvar_iipr') {
            // v0.9.25.14+: Se controle enviou o posto (valorAux) e PC não tem posto selecionado,
            // auto-selecionar antes de salvar para que chips do posto sejam encontrados
            if (!postoSelecionadoMalote && valorAux && valorAux !== '-') {
                selecionarPostoMalote(valorAux);
            }
            if (inputLacreIiprMalote && valor) inputLacreIiprMalote.value = normalizarNumeroLacre(valor);
            if (btnSalvarMaloteIipr) btnSalvarMaloteIipr.click();
        } else if (nome === 'armar_correios') {
            definirModoVoz('correios_lacre', 'Aguardando lacre Correios');
        } else if (nome === 'preencher_correios') {
            if (inputLacreCorreiosMalote) inputLacreCorreiosMalote.value = normalizarNumeroLacre(valor);
            if (inputLacreCorreiosMalote) inputLacreCorreiosMalote.focus();
        } else if (nome === 'armar_etiqueta') {
            definirModoVoz('correios_etiqueta', 'Aguardando etiqueta Correios');
        } else if (nome === 'preencher_etiqueta') {
            if (inputEtiquetaCorreiosMalote) inputEtiquetaCorreiosMalote.value = String(valor || '').trim();
            if (inputEtiquetaCorreiosMalote) inputEtiquetaCorreiosMalote.focus();
        } else if (nome === 'salvar_correios') {
            if (inputLacreCorreiosMalote && valor) inputLacreCorreiosMalote.value = normalizarNumeroLacre(valor);
            if (inputEtiquetaCorreiosMalote && valorAux) inputEtiquetaCorreiosMalote.value = String(valorAux || '').trim();
            if (btnSalvarMaloteCorreios) btnSalvarMaloteCorreios.click();
        } else if (nome === 'limpar_lotes') {
            if (btnLimparMaloteLote) btnLimparMaloteLote.click();
        }

        if (statusControleRemoto) {
            statusControleRemoto.textContent = 'Comando remoto aplicado: ' + nome.replace(/_/g, ' ');
        }
        publicarEstadoControleRemoto();
    }

    function iniciarPollingControleRemoto() {
        if (pollingRemotoAtivo || !controleCanal) return;
        pollingRemotoAtivo = true;

        function ciclo() {
            fetch('conferencia_pacotes.php?buscar_comandos_remoto_ajax=1&canal=' + encodeURIComponent(controleCanal), { cache: 'no-store' })
                .then(function(resp) { return resp.json(); })
                .then(function(data) {
                    if (data && data.success && data.comandos && data.comandos.length) {
                        for (var i = 0; i < data.comandos.length; i++) {
                            aplicarComandoRemoto(data.comandos[i]);
                        }
                    }
                })
                .catch(function() {})
                .then(function() {
                    window.setTimeout(ciclo, 1200);
                });
        }

        ciclo();
    }

    function interpretarComandoVoz(transcricao) {
        var comando = normalizarTextoVoz(transcricao);
        if (!comando) return;

        if (comando.indexOf('abrir previa') !== -1 || comando.indexOf('abrir pre via') !== -1) {
            abrirPreviaMalotes();
            return;
        }
        if (comando.indexOf('cancelar comando') !== -1 || comando.indexOf('parar comando') !== -1 || comando.indexOf('limpar comando') !== -1) {
            limparModoVoz('Comando de voz cancelado.');
            falarTexto('comando cancelado');
            return;
        }
        if (comando.indexOf('salvar malote iipr') !== -1 || comando.indexOf('gravar malote iipr') !== -1) {
            if (btnSalvarMaloteIipr) btnSalvarMaloteIipr.click();
            return;
        }
        if (comando.indexOf('salvar malote correios') !== -1 || comando.indexOf('gravar malote correios') !== -1 || comando.indexOf('vincular malote correios') !== -1) {
            if (btnSalvarMaloteCorreios) btnSalvarMaloteCorreios.click();
            return;
        }
        if (comando.indexOf('fechar malote iipr') !== -1 || comando.indexOf('lacre iipr') !== -1) {
            definirModoVoz('iipr', 'Aguardando lacre IIPR');
            return;
        }
        if (comando.indexOf('fechar malote correios') !== -1 || comando.indexOf('lacre correios') !== -1) {
            definirModoVoz('correios_lacre', 'Aguardando lacre Correios');
            return;
        }
        if (comando.indexOf('etiqueta correios') !== -1) {
            definirModoVoz('correios_etiqueta', 'Aguardando etiqueta Correios');
            return;
        }
        if (vozModoAtual) {
            var digitos = extrairDigitosFalados(transcricao);
            if (digitos) {
                preencherCampoPorVoz(digitos);
                return;
            }
        }
        atualizarStatusVoz('Comando não reconhecido: ' + transcricao, 'erro');
    }

    function configurarReconhecimentoVoz() {
        if (reconhecimentoVoz) return reconhecimentoVoz;
        var SpeechRecognitionApi = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SpeechRecognitionApi) {
            renderizarDiagnosticoVoz();
            atualizarStatusVoz('Reconhecimento de voz indisponível neste navegador ou nesta origem da página.', 'erro');
            if (btnAlternarVozMalotes) btnAlternarVozMalotes.disabled = true;
            return null;
        }
        reconhecimentoVoz = new SpeechRecognitionApi();
        reconhecimentoVoz.lang = 'pt-BR';
        reconhecimentoVoz.continuous = true;
        reconhecimentoVoz.interimResults = false;

        reconhecimentoVoz.onstart = function() {
            vozEscutaAtiva = true;
            renderizarDiagnosticoVoz();
            atualizarStatusVoz('Microfone ativo. Aguardando comando.', 'escutando');
        };
        reconhecimentoVoz.onresult = function(event) {
            for (var i = event.resultIndex; i < event.results.length; i++) {
                if (!event.results[i].isFinal) continue;
                var texto = event.results[i][0] ? event.results[i][0].transcript : '';
                interpretarComandoVoz(texto);
            }
        };
        reconhecimentoVoz.onerror = function(event) {
            renderizarDiagnosticoVoz();
            atualizarStatusVoz('Falha no reconhecimento de voz: ' + (event && event.error ? event.error : 'erro desconhecido'), 'erro');
        };
        reconhecimentoVoz.onend = function() {
            if (vozEscutaAtiva && !vozReinicioManual) {
                try {
                    reconhecimentoVoz.start();
                    return;
                } catch (e3) {}
            }
            vozEscutaAtiva = false;
            vozReinicioManual = false;
            renderizarDiagnosticoVoz();
            atualizarStatusVoz('Microfone desligado.', '');
        };
        return reconhecimentoVoz;
    }

    function alternarReconhecimentoVoz() {
        var reconhecimento = configurarReconhecimentoVoz();
        if (!reconhecimento) return;
        if (vozEscutaAtiva) {
            vozReinicioManual = true;
            vozEscutaAtiva = false;
            try {
                reconhecimento.stop();
            } catch (e4) {}
            limparModoVoz('Microfone desligado.');
            return;
        }
        vozReinicioManual = false;
        try {
            reconhecimento.start();
        } catch (e5) {
            atualizarStatusVoz('Não foi possível ativar o microfone agora.', 'erro');
        }
    }

    function obterChipsPosto(posto) {
        if (!posto) return [];
        return Array.prototype.slice.call(document.querySelectorAll('.operacao-chip[data-posto="' + posto + '"]'));
    }

    function obterLinhasPosto(posto) {
        if (!posto) return [];
        return Array.prototype.slice.call(document.querySelectorAll('tr[data-posto="' + posto + '"]'));
    }

    function obterDadosChipOperacao(chip) {
        if (!chip) return null;
        return {
            codigo: chip.getAttribute('data-codigo') || '',
            lote: chip.getAttribute('data-lote') || '',
            posto: chip.getAttribute('data-posto') || '',
            regional: chip.getAttribute('data-regional') || '',
            regional_codigo: chip.getAttribute('data-regional-codigo') || '',
            qtd: chip.getAttribute('data-qtd') || '',
            data: chip.getAttribute('data-data') || '',
            data_sql: chip.getAttribute('data-data-sql') || '',
            isPT: chip.getAttribute('data-ispt') === '1',
            conferido: chip.getAttribute('data-conf') === '1',
            lacre_iipr: normalizarNumeroLacre(chip.getAttribute('data-lacre-iipr') || ''),
            grupo_iipr: chip.getAttribute('data-grupo-iipr') || '',
            lacre_correios: normalizarNumeroLacre(chip.getAttribute('data-lacre-correios') || ''),
            grupo_correios: chip.getAttribute('data-grupo-correios') || '',
            etiqueta_correios: String(chip.getAttribute('data-etiqueta-correios') || '').trim(),
            usuario_lacre: chip.getAttribute('data-usuario-lacre') || '',
            atualizado_lacre: chip.getAttribute('data-atualizado-lacre') || ''
        };
    }

    function atualizarVisualChipOperacao(chip) {
        if (!chip) return;
        var texto = chip.querySelector('.operacao-chip-texto');
        var indicadores = chip.querySelector('.operacao-chip-indicadores');
        if (!texto) {
            texto = document.createElement('span');
            texto.className = 'operacao-chip-texto';
            chip.appendChild(texto);
        }
        if (!indicadores) {
            indicadores = document.createElement('span');
            indicadores.className = 'operacao-chip-indicadores';
            chip.appendChild(indicadores);
        }
        texto.textContent = chip.getAttribute('data-lote') || '';
        var badges = [];
        if (chip.classList.contains('tem-iipr')) {
            badges.push('<span class="operacao-chip-badge iipr" title="Chip dentro de malote IIPR">I</span>');
        }
        if (chip.classList.contains('tem-correios')) {
            badges.push('<span class="operacao-chip-badge correios" title="Chip dentro de malote Correios">C</span>');
        }
        indicadores.innerHTML = badges.join('');
    }

    function contarPendenciasDoPosto(posto) {
        if (!posto || !malotesRascunho || !malotesRascunho.atribuicoes) return 0;
        var total = 0;
        for (var codigo in malotesRascunho.atribuicoes) {
            if (!Object.prototype.hasOwnProperty.call(malotesRascunho.atribuicoes, codigo)) continue;
            if (String(malotesRascunho.atribuicoes[codigo].posto || '') === String(posto)) {
                total++;
            }
        }
        return total;
    }

    function obterChipsConfirmadosSemIiprDoPosto(posto) {
        if (!posto) return [];
        var chipsPosto = obterChipsPosto(posto);
        var chips = [];
        for (var i = 0; i < chipsPosto.length; i++) {
            var dados = obterDadosChipOperacao(chipsPosto[i]);
            if (!dados || dados.isPT || !dados.conferido || dados.lacre_iipr) continue;
            chips.push(chipsPosto[i]);
        }
        return chips;
    }

    function obterGruposIiprSemCorreiosDoPosto(posto) {
        if (!posto) return [];
        var chipsPosto = obterChipsPosto(posto);
        var grupos = {};
        for (var i = 0; i < chipsPosto.length; i++) {
            var dados = obterDadosChipOperacao(chipsPosto[i]);
            if (!dados || dados.isPT || !dados.conferido || !dados.lacre_iipr) continue;
            if (dados.lacre_correios || dados.etiqueta_correios) continue;
            var chave = dados.grupo_iipr || ('GI|' + dados.lacre_iipr);
            if (!grupos[chave]) {
                grupos[chave] = {
                    chave: chave,
                    lacre_iipr: dados.lacre_iipr,
                    lotes: [],
                    chips: []
                };
            }
            if (grupos[chave].lotes.indexOf(dados.lote) === -1) {
                grupos[chave].lotes.push(dados.lote);
            }
            grupos[chave].chips.push(chipsPosto[i]);
        }
        var lista = [];
        for (var grupo in grupos) {
            if (!Object.prototype.hasOwnProperty.call(grupos, grupo)) continue;
            lista.push(grupos[grupo]);
        }
        lista.sort(function(a, b) {
            var lacreA = parseInt(a.lacre_iipr || 0, 10) || 0;
            var lacreB = parseInt(b.lacre_iipr || 0, 10) || 0;
            return lacreA - lacreB;
        });
        return lista;
    }

    function obterResumoMalotesDoPosto(posto) {
        var resumo = {
            posto: posto || '',
            regional: '',
            pendentesIipr: [],
            iiprFechados: [],
            iiprPendentesCorreios: [],
            linhasOficio: [],
            pendenciasSalvar: contarPendenciasDoPosto(posto)
        };
        if (!posto) return resumo;

        var chips = obterChipsPosto(posto);
        var gruposIipr = {};
        var gruposCorreios = {};
        for (var i = 0; i < chips.length; i++) {
            var dados = obterDadosChipOperacao(chips[i]);
            if (!dados || dados.isPT) continue;
            if (!resumo.regional && dados.regional) resumo.regional = dados.regional;
            if (!dados.conferido) continue;

            if (!dados.lacre_iipr) {
                resumo.pendentesIipr.push({ codigo: dados.codigo, lote: dados.lote, qtd: dados.qtd });
                continue;
            }

            var chaveIipr = dados.grupo_iipr || ('GI|' + dados.lacre_iipr);
            if (!gruposIipr[chaveIipr]) {
                gruposIipr[chaveIipr] = {
                    chave: chaveIipr,
                    lacre_iipr: dados.lacre_iipr,
                    lotes: [],
                    qtd_total: 0,
                    pendente: false,
                    temCorreios: false
                };
            }
            if (gruposIipr[chaveIipr].lotes.indexOf(dados.lote) === -1) {
                gruposIipr[chaveIipr].lotes.push(dados.lote);
            }
            gruposIipr[chaveIipr].qtd_total += parseInt(dados.qtd || 0, 10) || 0;
            if (chips[i].getAttribute('data-malote-pendente') === '1') {
                gruposIipr[chaveIipr].pendente = true;
            }

            if (dados.lacre_correios || dados.etiqueta_correios) {
                gruposIipr[chaveIipr].temCorreios = true;
                var chaveCorreios = montarChaveConsolidacaoCorreios(dados);
                if (!gruposCorreios[chaveCorreios]) {
                    gruposCorreios[chaveCorreios] = {
                        posto: dados.posto || '',
                        regional: dados.regional || '',
                        regional_codigo: dados.regional_codigo || '',
                        destino_oficio: obterDestinoLinhaOficio(dados),
                        lacres_iipr: [],
                        lacre_correios: dados.lacre_correios || '',
                        etiqueta_correios: dados.etiqueta_correios || '',
                        lotes: [],
                        pendente: false,
                        grupos_correios: [],
                        row_key: chaveCorreios
                    };
                }
                if (dados.lacre_iipr && gruposCorreios[chaveCorreios].lacres_iipr.indexOf(String(dados.lacre_iipr)) === -1) {
                    gruposCorreios[chaveCorreios].lacres_iipr.push(String(dados.lacre_iipr));
                }
                if (dados.grupo_correios && gruposCorreios[chaveCorreios].grupos_correios.indexOf(dados.grupo_correios) === -1) {
                    gruposCorreios[chaveCorreios].grupos_correios.push(dados.grupo_correios);
                }
                if (dados.lote && gruposCorreios[chaveCorreios].lotes.indexOf(dados.lote) === -1) {
                    gruposCorreios[chaveCorreios].lotes.push(dados.lote);
                }
                if (!gruposCorreios[chaveCorreios].lacre_correios && dados.lacre_correios) {
                    gruposCorreios[chaveCorreios].lacre_correios = dados.lacre_correios;
                }
                if (!gruposCorreios[chaveCorreios].etiqueta_correios && dados.etiqueta_correios) {
                    gruposCorreios[chaveCorreios].etiqueta_correios = dados.etiqueta_correios;
                }
                if (chips[i].getAttribute('data-malote-pendente') === '1') {
                    gruposCorreios[chaveCorreios].pendente = true;
                }
            }
        }

        for (var chave in gruposIipr) {
            if (!Object.prototype.hasOwnProperty.call(gruposIipr, chave)) continue;
            resumo.iiprFechados.push(gruposIipr[chave]);
            if (!gruposIipr[chave].temCorreios) {
                resumo.iiprPendentesCorreios.push(gruposIipr[chave]);
            }
        }
        for (var chaveC in gruposCorreios) {
            if (!Object.prototype.hasOwnProperty.call(gruposCorreios, chaveC)) continue;
            gruposCorreios[chaveC].lacres_iipr = compactarSequenciaNumerica(gruposCorreios[chaveC].lacres_iipr);
            resumo.linhasOficio.push(gruposCorreios[chaveC]);
        }

        resumo.iiprFechados.sort(function(a, b) {
            var lacreA = parseInt(a.lacre_iipr || 0, 10) || 0;
            var lacreB = parseInt(b.lacre_iipr || 0, 10) || 0;
            return lacreA - lacreB;
        });
        resumo.iiprPendentesCorreios.sort(function(a, b) {
            var lacreA = parseInt(a.lacre_iipr || 0, 10) || 0;
            var lacreB = parseInt(b.lacre_iipr || 0, 10) || 0;
            return lacreA - lacreB;
        });
        resumo.linhasOficio.sort(function(a, b) {
            var lacreA = parseInt(a.lacre_correios || 0, 10) || 0;
            var lacreB = parseInt(b.lacre_correios || 0, 10) || 0;
            return lacreA - lacreB;
        });

        return resumo;
    }

    function obterDestinoLinhaOficio(dados) {
        var codigo = parseInt(dados && dados.regional_codigo ? dados.regional_codigo : 0, 10) || 0;
        if (codigo === 0) return 'CAPITAL';
        if (codigo === 1) return 'METROPOLITANA';
        if (codigo === 999) return 'CENTRAL IIPR';
        return String(codigo).padStart(3, '0');
    }

    function renderizarMapaMalotesPosto(posto) {
        if (!posto) return;
        var container = document.querySelector('.operacao-posto-row[data-posto="' + posto + '"] .operacao-posto-mapa');
        if (!container) return;
        var chips = obterChipsPosto(posto);
        if (!chips.length) {
            container.innerHTML = '';
            return;
        }
        var primeiro = obterDadosChipOperacao(chips[0]);
        if (!primeiro || primeiro.isPT) {
            container.innerHTML = '<div class="operacao-posto-card"><h5>Fluxo Correios</h5><div class="operacao-posto-card-sub">A linha inline de malotes vale apenas para postos dos Correios.</div></div>';
            return;
        }

        var resumo = obterResumoMalotesDoPosto(posto);
        var tagsPendentes = [];
        for (var i = 0; i < resumo.pendentesIipr.length; i++) {
            tagsPendentes.push('<span class="operacao-malote-tag iipr"><i>CH</i>Lote ' + escapeHtml(resumo.pendentesIipr[i].lote) + ' • qtd ' + escapeHtml(resumo.pendentesIipr[i].qtd) + '</span>');
        }
        var historicoIipr = [];
        for (var j = 0; j < resumo.iiprFechados.length; j++) {
            historicoIipr.push('<span class="operacao-malote-tag fechado"><i>I</i>' + escapeHtml(String(resumo.iiprFechados[j].lacre_iipr || '-')) + ' • lotes ' + escapeHtml(resumo.iiprFechados[j].lotes.join(', ')) + (resumo.iiprFechados[j].temCorreios ? ' • no Correios' : '') + '</span>');
        }
        var tagsCorreios = [];
        for (var k = 0; k < resumo.iiprPendentesCorreios.length; k++) {
            tagsCorreios.push('<span class="operacao-malote-tag correios"><i>I</i>' + escapeHtml(String(resumo.iiprPendentesCorreios[k].lacre_iipr || '-')) + ' • lotes ' + escapeHtml(resumo.iiprPendentesCorreios[k].lotes.join(', ')) + '</span>');
        }

        var htmlLinhasOficio = '';
        if (resumo.linhasOficio.length) {
            var linhas = [];
            var totaisPorDestino = {};
            var ordemPorDestino = {};
            for (var l0 = 0; l0 < resumo.linhasOficio.length; l0++) {
                var destino0 = String(resumo.linhasOficio[l0].destino_oficio || '-');
                totaisPorDestino[destino0] = (totaisPorDestino[destino0] || 0) + 1;
            }
            for (var l = 0; l < resumo.linhasOficio.length; l++) {
                var linha = resumo.linhasOficio[l];
                var destino = String(linha.destino_oficio || '-');
                ordemPorDestino[destino] = (ordemPorDestino[destino] || 0) + 1;
                var rotuloDestino = destino;
                if ((totaisPorDestino[destino] || 0) > 1) {
                    rotuloDestino += ' - linha ' + ordemPorDestino[destino];
                }
                linhas.push('<tr><td>' + escapeHtml(rotuloDestino) + '</td><td><div class="operacao-oficio-lacres">' + escapeHtml(linha.lacres_iipr || '-') + '</div></td><td>' + escapeHtml(linha.lacre_correios || '-') + '</td><td style="word-break:break-all;">' + escapeHtml(linha.etiqueta_correios || '-') + '</td></tr>');
            }
            htmlLinhasOficio = '<table class="operacao-oficio-tabela"><thead><tr><th>Regional / Linha</th><th>Lacre IIPR</th><th>Lacre Correios</th><th>Etiqueta</th></tr></thead><tbody>' + linhas.join('') + '</tbody></table>';
        } else {
            htmlLinhasOficio = '<div class="operacao-oficio-vazio">A linha pronta do ofício aparece depois que o malote Correios recebe lacre e etiqueta.</div>';
        }

        container.innerHTML = '<div class="operacao-posto-workspace">' +
            '<div class="operacao-posto-card">' +
                '<div class="operacao-malote-head"><div><h5>Malote IIPR na linha do posto</h5><div class="operacao-posto-card-sub">Fecha todos os chips verdes deste posto que ainda não entraram em um malote IIPR.</div></div><span class="operacao-malote-contador">Prontos ' + resumo.pendentesIipr.length + '</span></div>' +
                '<div class="operacao-malote-bolsa ' + (resumo.pendentesIipr.length ? 'pronto-iipr' : '') + '">' +
                    (tagsPendentes.length ? '<div class="operacao-malote-tags">' + tagsPendentes.join('') + '</div>' : '<div class="operacao-malote-bolsa-vazio">Nenhum chip verde aguardando IIPR neste posto.</div>') +
                '</div>' +
                '<div class="operacao-malote-form-inline"><input type="text" class="input-inline-lacre-iipr" data-posto="' + escapeHtml(posto) + '" maxlength="12" placeholder="Digite o lacre IIPR"><button type="button" class="btn-inline-iipr btn-inline-fechar-iipr" data-posto="' + escapeHtml(posto) + '">Fechar IIPR agora</button></div>' +
                '<div class="operacao-malote-historico">' + (historicoIipr.length ? historicoIipr.join('') : '<span class="operacao-malote-bolsa-vazio">Os IIPR fechados deste posto aparecerão aqui.</span>') + '</div>' +
            '</div>' +
            '<div class="operacao-posto-card">' +
                '<div class="operacao-malote-head"><div><h5>Malote Correios do posto</h5><div class="operacao-posto-card-sub">Quando terminar o posto, feche o malote maior com todos os IIPR ainda sem lacre Correios.</div></div><span class="operacao-malote-contador">IIPR ' + resumo.iiprPendentesCorreios.length + '</span></div>' +
                '<div class="operacao-malote-bolsa ' + (resumo.iiprPendentesCorreios.length ? 'pronto-correios' : '') + '">' +
                    (tagsCorreios.length ? '<div class="operacao-malote-tags">' + tagsCorreios.join('') + '</div>' : '<div class="operacao-malote-bolsa-vazio">Nenhum malote IIPR pendente para o Correios neste posto.</div>') +
                '</div>' +
                '<div class="operacao-malote-form-inline duplo"><input type="text" class="input-inline-lacre-correios" data-posto="' + escapeHtml(posto) + '" maxlength="12" placeholder="Lacre Correios"><input type="text" class="input-inline-etiqueta-correios" data-posto="' + escapeHtml(posto) + '" maxlength="35" placeholder="Etiqueta Correios"><button type="button" class="btn-inline-correios btn-inline-fechar-correios" data-posto="' + escapeHtml(posto) + '">Fechar Correios</button><button type="button" class="btn-inline-salvar-posto" data-posto="' + escapeHtml(posto) + '">Salvar posto</button><button type="button" class="btn-inline-desfazer-posto" data-posto="' + escapeHtml(posto) + '">Desfazer último</button></div>' +
                '<div class="operacao-malote-rodape"><div class="operacao-malote-pendencia">Pendentes para salvar neste posto: ' + resumo.pendenciasSalvar + '</div></div>' +
            '</div>' +
            '<div class="operacao-posto-card">' +
                '<div class="operacao-malote-head"><div><h5>Linha pronta do ofício Correios</h5><div class="operacao-posto-card-sub">Se houver mais de um IIPR dentro do mesmo malote Correios, os lacres saem juntos na mesma linha.</div></div><span class="operacao-malote-contador">Linhas ' + resumo.linhasOficio.length + '</span></div>' +
                htmlLinhasOficio +
            '</div>' +
        '</div>';
    }

    function renderizarMapasMalotesPostos() {
        var linhas = document.querySelectorAll('.operacao-posto-row[data-posto]');
        for (var i = 0; i < linhas.length; i++) {
            renderizarMapaMalotesPosto(linhas[i].getAttribute('data-posto') || '');
        }
    }

    function fecharMaloteIiprDoPosto(posto, lacreIipr) {
        var lacre = normalizarNumeroLacre(lacreIipr || '');
        if (!posto) {
            alert('Selecione um posto válido.');
            return false;
        }
        if (!lacre) {
            alert('Informe o lacre IIPR.');
            return false;
        }
        var chips = obterChipsConfirmadosSemIiprDoPosto(posto);
        if (!chips.length) {
            alert('Este posto não possui chips verdes pendentes para fechar no IIPR.');
            return false;
        }
        if (!validarLacreIiprUnicoEntrePostos(lacre, posto, false)) {
            return false;
        }
        registrarHistoricoMalote(posto, 'iipr', chips, 'fechamento IIPR');
        avisarSeHaDuplicidadeLacre(lacre, posto);
        var grupoIipr = criarIdMalote('GI');
        for (var i = 0; i < chips.length; i++) {
            registrarAtribuicaoPendenteNoChip(chips[i], {
                lacre_iipr: lacre,
                grupo_iipr: grupoIipr,
                lacre_correios: '',
                grupo_correios: '',
                etiqueta_correios: ''
            });
        }
        var estado = obterEstadoPostoRascunho(posto);
        estado.iipr_atual = null;
        salvarRascunhoMalotesLocal();
        renderizarPainelMalotes();
        atualizarColunaMaloteTradicional();
        renderizarMapasMalotesPostos();
        limparModoVoz('Malote IIPR fechado direto na linha do posto.');
        return true;
    }

    function fecharMaloteCorreiosDoPosto(posto, lacreCorreios, etiquetaCorreios) {
        var lacre = normalizarNumeroLacre(lacreCorreios || '');
        var etiqueta = String(etiquetaCorreios || '').trim();
        if (!posto) {
            alert('Selecione um posto válido.');
            return false;
        }
        if (!lacre && !etiqueta) {
            alert('Informe ao menos o lacre ou a etiqueta Correios.');
            return false;
        }
        var gruposPendentes = obterGruposIiprSemCorreiosDoPosto(posto);
        if (!gruposPendentes.length) {
            alert('Este posto não possui malotes IIPR pendentes para o Correios.');
            return false;
        }
        var grupoCorreios = criarIdMalote('GC');
        var chips = [];
        var vistos = {};
        for (var i = 0; i < gruposPendentes.length; i++) {
            for (var j = 0; j < gruposPendentes[i].chips.length; j++) {
                var codigo = gruposPendentes[i].chips[j].getAttribute('data-codigo') || '';
                if (!codigo || vistos[codigo]) continue;
                vistos[codigo] = true;
                chips.push(gruposPendentes[i].chips[j]);
            }
        }
        if (!chips.length) {
            alert('Não há chips válidos para fechar o malote Correios deste posto.');
            return false;
        }
        registrarHistoricoMalote(posto, 'correios', chips, 'fechamento Correios');
        if (lacre) {
            avisarSeHaDuplicidadeLacre(lacre, posto);
        }
        if (etiqueta) {
            avisarSeHaDuplicidadeEtiqueta(etiqueta, posto);
        }
        for (var k = 0; k < chips.length; k++) {
            var dadosAtuais = obterDadosChipOperacao(chips[k]);
            registrarAtribuicaoPendenteNoChip(chips[k], {
                lacre_iipr: dadosAtuais ? dadosAtuais.lacre_iipr : '',
                grupo_iipr: dadosAtuais ? dadosAtuais.grupo_iipr : '',
                lacre_correios: lacre || (dadosAtuais ? dadosAtuais.lacre_correios : ''),
                grupo_correios: grupoCorreios,
                etiqueta_correios: etiqueta || (dadosAtuais ? dadosAtuais.etiqueta_correios : '')
            });
        }
        var estado = obterEstadoPostoRascunho(posto);
        estado.correios_atual = null;
        salvarRascunhoMalotesLocal();
        renderizarPainelMalotes();
        atualizarColunaMaloteTradicional();
        renderizarMapasMalotesPostos();
        limparModoVoz('Malote Correios fechado direto na linha do posto.');
        return true;
    }

    function salvarPendenciasDoPosto(posto) {
        if (!posto) {
            alert('Selecione um posto válido.');
            return;
        }
        var pacotes = [];
        var codigos = [];
        for (var codigo in malotesRascunho.atribuicoes) {
            if (!Object.prototype.hasOwnProperty.call(malotesRascunho.atribuicoes, codigo)) continue;
            if (String(malotesRascunho.atribuicoes[codigo].posto || '') !== String(posto)) continue;
            pacotes.push(malotesRascunho.atribuicoes[codigo]);
            codigos.push(codigo);
        }
        if (!pacotes.length) {
            alert('Não há vínculos pendentes para salvar neste posto.');
            return;
        }
        persistirAtribuicoesLote(pacotes, function() {
            for (var i = 0; i < codigos.length; i++) {
                var chip = obterChipPorCodigo(codigos[i]);
                if (!chip) continue;
                chip.setAttribute('data-malote-pendente', '0');
                chip.classList.remove('malote-pendente');
                delete malotesRascunho.atribuicoes[codigos[i]];
                atualizarVisualChipOperacao(chip);
                var linha = document.querySelector('tr[data-codigo="' + codigos[i] + '"]');
                if (linha) {
                    linha.setAttribute('data-malote-pendente', '0');
                    atualizarColunaMaloteLinha(linha);
                }
            }
            limparHistoricoMaloteDoPosto(posto);
            salvarRascunhoMalotesLocal();
            renderizarPainelMalotes();
            atualizarColunaMaloteTradicional();
            renderizarMapasMalotesPostos();
            publicarResumoPrevia();
        });
    }

    function aplicarAtribuicaoNoChip(chip, dados) {
        if (!chip) return;
        var lacreIipr = dados && dados.lacre_iipr ? normalizarNumeroLacre(dados.lacre_iipr) : '';
        var lacreCorreios = dados && dados.lacre_correios ? normalizarNumeroLacre(dados.lacre_correios) : '';
        var etiquetaCorreios = dados && dados.etiqueta_correios ? String(dados.etiqueta_correios).trim() : '';
        var usuarioLacre = dados && dados.usuario_lacre ? String(dados.usuario_lacre).trim() : '';
        var atualizadoLacre = dados && dados.atualizado_lacre ? String(dados.atualizado_lacre).trim() : '';
        var pendente = !!(dados && dados.pendente);
        chip.setAttribute('data-lacre-iipr', lacreIipr);
        chip.setAttribute('data-grupo-iipr', dados && dados.grupo_iipr ? String(dados.grupo_iipr) : '');
        chip.setAttribute('data-lacre-correios', lacreCorreios);
        chip.setAttribute('data-grupo-correios', dados && dados.grupo_correios ? String(dados.grupo_correios) : '');
        chip.setAttribute('data-etiqueta-correios', etiquetaCorreios);
        chip.setAttribute('data-usuario-lacre', usuarioLacre);
        chip.setAttribute('data-atualizado-lacre', atualizadoLacre);
        chip.setAttribute('data-malote-pendente', pendente ? '1' : '0');
        chip.classList.toggle('tem-iipr', lacreIipr !== '');
        chip.classList.toggle('tem-correios', lacreCorreios !== '' || etiquetaCorreios !== '');
        chip.classList.toggle('malote-pendente', pendente);
        atualizarVisualChipOperacao(chip);

        var linhaTabela = document.querySelector('tr[data-codigo="' + chip.getAttribute('data-codigo') + '"]');
        if (linhaTabela) {
            linhaTabela.setAttribute('data-lacre-iipr', lacreIipr);
            linhaTabela.setAttribute('data-grupo-iipr', dados && dados.grupo_iipr ? String(dados.grupo_iipr) : '');
            linhaTabela.setAttribute('data-lacre-correios', lacreCorreios);
            linhaTabela.setAttribute('data-grupo-correios', dados && dados.grupo_correios ? String(dados.grupo_correios) : '');
            linhaTabela.setAttribute('data-etiqueta-correios', etiquetaCorreios);
            linhaTabela.setAttribute('data-usuario-lacre', usuarioLacre);
            linhaTabela.setAttribute('data-atualizado-lacre', atualizadoLacre);
            linhaTabela.setAttribute('data-malote-pendente', pendente ? '1' : '0');
            atualizarColunaMaloteLinha(linhaTabela);
        }
        renderizarMapaMalotesPosto(chip.getAttribute('data-posto') || '');
    }

    function obterIndiceGrupoNoPosto(posto, atributoGrupo, grupoValor) {
        if (!posto || !atributoGrupo || !grupoValor) return 0;
        var linhasPosto = document.querySelectorAll('tr[data-posto="' + posto + '"]');
        var grupos = [];
        var vistos = {};
        for (var i = 0; i < linhasPosto.length; i++) {
            var valor = String(linhasPosto[i].getAttribute(atributoGrupo) || '').trim();
            if (!valor || vistos[valor]) continue;
            vistos[valor] = true;
            grupos.push(valor);
        }
        for (var j = 0; j < grupos.length; j++) {
            if (grupos[j] === grupoValor) {
                return j + 1;
            }
        }
        return 0;
    }

    function montarTextoMaloteLinha(linha) {
        if (!linha) return '-';
        var posto = String(linha.getAttribute('data-posto') || '').trim();
        var lacreIipr = normalizarNumeroLacre(linha.getAttribute('data-lacre-iipr') || '');
        var grupoIipr = String(linha.getAttribute('data-grupo-iipr') || '').trim();
        var lacreCorreios = normalizarNumeroLacre(linha.getAttribute('data-lacre-correios') || '');
        var grupoCorreios = String(linha.getAttribute('data-grupo-correios') || '').trim();
        var etiquetaCorreios = String(linha.getAttribute('data-etiqueta-correios') || '').trim();
        var pendente = String(linha.getAttribute('data-malote-pendente') || '') === '1';

        var partes = [];
        if (lacreIipr) {
            var idxIipr = obterIndiceGrupoNoPosto(posto, 'data-grupo-iipr', grupoIipr);
            var tituloIipr = idxIipr > 0 ? ('Malote IIPR ' + idxIipr) : 'Malote IIPR';
            partes.push(tituloIipr + ' - Lacre ' + lacreIipr);
        }
        if (lacreCorreios || etiquetaCorreios) {
            var idxCorreios = obterIndiceGrupoNoPosto(posto, 'data-grupo-correios', grupoCorreios);
            var tituloCorreios = idxCorreios > 0 ? ('Malote Correios ' + idxCorreios) : 'Malote Correios';
            var sufixo = lacreCorreios ? (' - Lacre ' + lacreCorreios) : '';
            if (etiquetaCorreios) {
                sufixo += (sufixo ? ' | ' : ' - ') + 'Etiqueta ' + etiquetaCorreios;
            }
            partes.push(tituloCorreios + sufixo);
        }
        if (!partes.length) {
            return pendente ? 'Pendente limpar/salvar' : '-';
        }
        if (pendente) {
            partes.push('Pendente salvar');
        }
        return partes.join(' | ');
    }

    function atualizarColunaMaloteLinha(linha) {
        if (!linha) return;
        var td = linha.querySelector('.col-malote-vinculo');
        if (!td) return;
        td.textContent = montarTextoMaloteLinha(linha);
    }

    function atualizarColunaMaloteTradicional() {
        var linhas = document.querySelectorAll('tr.linha-conferencia');
        for (var i = 0; i < linhas.length; i++) {
            atualizarColunaMaloteLinha(linhas[i]);
        }
    }

    function selecionarPostoMalote(posto, grupo) {
        postoSelecionadoMalote = posto || '';
        grupoSelecionadoMalote = grupo || '';

        var linhas = document.querySelectorAll('.operacao-posto-row.selecionado-malote');
        for (var i = 0; i < linhas.length; i++) {
            linhas[i].classList.remove('selecionado-malote');
        }
        if (postoSelecionadoMalote) {
            var linhaSelecionada = document.querySelector('.operacao-posto-row[data-posto="' + postoSelecionadoMalote + '"]');
            if (linhaSelecionada) {
                linhaSelecionada.classList.add('selecionado-malote');
            }
        }
        renderizarPainelMalotes();
    }

    function montarPacoteParaPersistencia(chip, sobrescritas) {
        var dados = obterDadosChipOperacao(chip);
        if (!dados) return null;
        var payload = {
            codbar: dados.codigo,
            lote: dados.lote,
            regional: normalizarRegionalValor(dados.regional_codigo || dados.regional),
            posto: dados.posto,
            dataexp: dados.data_sql || dados.data,
            qtd: dados.qtd,
            lacre_iipr: dados.lacre_iipr,
            grupo_iipr: dados.grupo_iipr,
            lacre_correios: dados.lacre_correios,
            grupo_correios: dados.grupo_correios,
            etiqueta_correios: dados.etiqueta_correios
        };
        if (sobrescritas) {
            for (var chave in sobrescritas) {
                if (Object.prototype.hasOwnProperty.call(sobrescritas, chave)) {
                    payload[chave] = sobrescritas[chave];
                }
            }
        }
        return payload;
    }

    function persistirAtribuicoesLote(pacotes, callback) {
        if (!pacotes || !pacotes.length) {
            alert('Selecione pelo menos um lote.');
            return;
        }
        if (!usuarioAtual) {
            alert('Informe o responsável da conferência antes de salvar os malotes.');
            return;
        }
        var formData = new FormData();
        formData.append('salvar_atribuicao_lacres_ajax', '1');
        formData.append('pacotes', JSON.stringify(pacotes));
        formData.append('usuario', usuarioAtual);

        fetch(window.location.href, { method: 'POST', body: formData })
            .then(function(resp) { return resp.json(); })
            .then(function(data) {
                if (!data || !data.success) {
                    alert((data && data.erro) ? data.erro : 'Não foi possível salvar a atribuição.');
                    return;
                }
                if (callback) callback();
                renderizarPainelMalotes();
                mostrarConfirmacao('Vínculos de malote salvos com sucesso.', true);
            })
            .catch(function() {
                alert('Erro ao salvar a atribuição dos malotes.');
            });
    }

    function renderizarPainelMalotes() {
        if (!painelMalotesChips || !painelMalotesLotes || !painelMalotesIipr) return;

        atualizarResumoPendenciasMalotes();
        renderizarMapasMalotesPostos();

        if (!postoSelecionadoMalote) {
            if (painelMalotesSubtitulo) painelMalotesSubtitulo.textContent = 'Selecione um chip ou continue a conferência para abrir o posto atual.';
            if (painelMaloteIiprRascunho) painelMaloteIiprRascunho.className = 'painel-malotes-vazio';
            if (painelMaloteIiprRascunho) painelMaloteIiprRascunho.innerHTML = 'Nenhum malote IIPR em montagem.';
            if (painelMalotesLotes) painelMalotesLotes.className = 'painel-malotes-vazio';
            if (painelMalotesLotes) painelMalotesLotes.innerHTML = 'Nenhum posto selecionado.';
            if (painelMaloteCorreiosRascunho) painelMaloteCorreiosRascunho.className = 'painel-malotes-vazio';
            if (painelMaloteCorreiosRascunho) painelMaloteCorreiosRascunho.innerHTML = 'Nenhum malote Correios em montagem.';
            if (painelMalotesIipr) painelMalotesIipr.className = 'painel-malotes-vazio';
            if (painelMalotesIipr) painelMalotesIipr.innerHTML = 'Os malotes IIPR aparecerão aqui depois do primeiro fechamento.';
            if (malotesResumoConfirmados) malotesResumoConfirmados.textContent = '0';
            if (malotesResumoIipr) malotesResumoIipr.textContent = '0';
            if (malotesResumoCorreios) malotesResumoCorreios.textContent = '0';
            publicarResumoPrevia();
            return;
        }

        var chips = obterChipsPosto(postoSelecionadoMalote);
        if (!chips.length) {
            if (painelMaloteIiprRascunho) painelMaloteIiprRascunho.className = 'painel-malotes-vazio';
            if (painelMaloteIiprRascunho) painelMaloteIiprRascunho.innerHTML = 'Nenhum pacote localizado para este posto.';
            if (painelMalotesLotes) painelMalotesLotes.className = 'painel-malotes-vazio';
            if (painelMalotesLotes) painelMalotesLotes.innerHTML = 'Nenhum pacote localizado para este posto no painel de chips.';
            if (painelMaloteCorreiosRascunho) painelMaloteCorreiosRascunho.className = 'painel-malotes-vazio';
            if (painelMaloteCorreiosRascunho) painelMaloteCorreiosRascunho.innerHTML = 'Nenhum malote Correios em montagem.';
            publicarResumoPrevia();
            return;
        }

        var primeiro = obterDadosChipOperacao(chips[0]);
        if (primeiro && primeiro.isPT) {
            if (painelMalotesSubtitulo) painelMalotesSubtitulo.textContent = 'Posto ' + postoSelecionadoMalote + ' pertence ao Poupa Tempo. O painel de malotes vale apenas para Correios.';
            if (painelMaloteIiprRascunho) painelMaloteIiprRascunho.className = 'painel-malotes-vazio';
            if (painelMaloteIiprRascunho) painelMaloteIiprRascunho.innerHTML = 'Sem uso para o fluxo Poupa Tempo.';
            if (painelMalotesLotes) painelMalotesLotes.className = 'painel-malotes-vazio';
            if (painelMalotesLotes) painelMalotesLotes.innerHTML = 'Selecione um posto dos Correios para atribuir malotes.';
            if (painelMaloteCorreiosRascunho) painelMaloteCorreiosRascunho.className = 'painel-malotes-vazio';
            if (painelMaloteCorreiosRascunho) painelMaloteCorreiosRascunho.innerHTML = 'Sem uso para o fluxo Poupa Tempo.';
            if (painelMalotesIipr) painelMalotesIipr.className = 'painel-malotes-vazio';
            if (painelMalotesIipr) painelMalotesIipr.innerHTML = 'Sem uso para o fluxo Poupa Tempo.';
            if (malotesResumoConfirmados) malotesResumoConfirmados.textContent = '0';
            if (malotesResumoIipr) malotesResumoIipr.textContent = '0';
            if (malotesResumoCorreios) malotesResumoCorreios.textContent = '0';
            publicarResumoPrevia();
            return;
        }

        var confirmados = [];
        var comIipr = 0;
        var comCorreios = 0;
        var gruposIipr = {};
        var linhasHtml = [];
        var estadoPosto = obterEstadoPostoRascunho(postoSelecionadoMalote);
        limparGruposVaziosRascunho(postoSelecionadoMalote);
        estadoPosto = obterEstadoPostoRascunho(postoSelecionadoMalote);
        var codigosRascunhoIipr = estadoPosto.iipr_atual && estadoPosto.iipr_atual.codigos ? normalizarListaUnica(estadoPosto.iipr_atual.codigos) : [];
        var gruposRascunhoCorreios = estadoPosto.correios_atual && estadoPosto.correios_atual.grupos_iipr ? normalizarListaUnica(estadoPosto.correios_atual.grupos_iipr) : [];

        for (var i = 0; i < chips.length; i++) {
            var dados = obterDadosChipOperacao(chips[i]);
            if (!dados) continue;
            if (!dados.conferido) continue;
            confirmados.push({ chip: chips[i], dados: dados });
            if (dados.lacre_iipr) comIipr++;
            if (dados.lacre_correios || dados.etiqueta_correios) comCorreios++;
            if (dados.lacre_iipr) {
                var chaveGrupoIipr = dados.grupo_iipr || ('LACRE_' + dados.lacre_iipr);
                if (!gruposIipr[chaveGrupoIipr]) {
                    gruposIipr[chaveGrupoIipr] = { chave: chaveGrupoIipr, lacre_iipr: dados.lacre_iipr, chips: [], lacre_correios: '', etiqueta_correios: '', pendente: false };
                }
                gruposIipr[chaveGrupoIipr].chips.push(chips[i]);
                if (!gruposIipr[chaveGrupoIipr].lacre_correios && dados.lacre_correios) {
                    gruposIipr[chaveGrupoIipr].lacre_correios = dados.lacre_correios;
                }
                if (!gruposIipr[chaveGrupoIipr].etiqueta_correios && dados.etiqueta_correios) {
                    gruposIipr[chaveGrupoIipr].etiqueta_correios = dados.etiqueta_correios;
                }
                if (chips[i].getAttribute('data-malote-pendente') === '1') {
                    gruposIipr[chaveGrupoIipr].pendente = true;
                }
            }
            var emMontagemIipr = codigosRascunhoIipr.indexOf(dados.codigo) !== -1;
            var emMontagemCorreios = dados.grupo_iipr && gruposRascunhoCorreios.indexOf(dados.grupo_iipr) !== -1;
            var pendenteSalvar = chips[i].getAttribute('data-malote-pendente') === '1';
            var classeStatus = 'pendente';
            var textoStatus = 'Sem lacre';
            if (emMontagemCorreios) {
                classeStatus = 'montagem';
                textoStatus = 'Em montagem Correios';
            } else if (emMontagemIipr) {
                classeStatus = 'montagem';
                textoStatus = 'Em montagem IIPR';
            } else if (dados.lacre_correios || dados.etiqueta_correios) {
                classeStatus = pendenteSalvar ? 'pendente-salvar' : 'correios';
                textoStatus = pendenteSalvar ? 'Correios pendente salvar' : 'Fechado Correios';
            } else if (dados.lacre_iipr) {
                classeStatus = pendenteSalvar ? 'pendente-salvar' : 'iipr';
                textoStatus = pendenteSalvar ? 'IIPR pendente salvar' : 'Fechado IIPR';
            }
            linhasHtml.push(
                '<tr>' +
                    '<td><input type="checkbox" class="check-malote-lote" data-codigo="' + escapeHtml(dados.codigo) + '"' + (emMontagemIipr ? ' checked' : '') + '></td>' +
                    '<td>' + escapeHtml(dados.lote) + '</td>' +
                    '<td>' + escapeHtml(dados.qtd) + '</td>' +
                    '<td>' + escapeHtml(dados.lacre_iipr || '-') + '</td>' +
                    '<td>' + escapeHtml(dados.lacre_correios || '-') + '</td>' +
                    '<td style="word-break:break-all;">' + escapeHtml(dados.etiqueta_correios || '-') + '</td>' +
                    '<td><span class="malote-status ' + classeStatus + '">' +
                        textoStatus +
                    '</span></td>' +
                '</tr>'
            );
        }

        if (painelMalotesSubtitulo) {
            painelMalotesSubtitulo.textContent = 'Posto ' + postoSelecionadoMalote + ' • ' + (grupoSelecionadoMalote || primeiro.regional || 'Correios');
        }
        if (malotesResumoConfirmados) malotesResumoConfirmados.textContent = String(confirmados.length);
        if (malotesResumoIipr) malotesResumoIipr.textContent = String(comIipr);
        if (malotesResumoCorreios) malotesResumoCorreios.textContent = String(comCorreios);

        if (!confirmados.length) {
            if (painelMaloteIiprRascunho) painelMaloteIiprRascunho.className = 'painel-malotes-vazio';
            if (painelMaloteIiprRascunho) painelMaloteIiprRascunho.innerHTML = 'Nenhum malote IIPR em montagem.';
            painelMalotesLotes.className = 'painel-malotes-vazio';
            painelMalotesLotes.innerHTML = 'Este posto ainda não possui lotes confirmados em verde.';
        } else {
            painelMalotesLotes.className = '';
            painelMalotesLotes.innerHTML = '<table class="tabela-malotes"><thead><tr><th></th><th>Lote</th><th>Qtd</th><th>Lacre IIPR</th><th>Lacre Correios</th><th>Etiqueta Correios</th><th>Status</th></tr></thead><tbody>' + linhasHtml.join('') + '</tbody></table>';
        }

        if (estadoPosto.iipr_atual && codigosRascunhoIipr.length) {
            var lotesRascunhoIipr = [];
            for (var i1 = 0; i1 < codigosRascunhoIipr.length; i1++) {
                var chipRascunho = obterChipPorCodigo(codigosRascunhoIipr[i1]);
                var dadosRascunho = obterDadosChipOperacao(chipRascunho);
                if (!dadosRascunho) continue;
                lotesRascunhoIipr.push('<li>Lote ' + escapeHtml(dadosRascunho.lote) + ' • qtd ' + escapeHtml(dadosRascunho.qtd) + '</li>');
            }
            painelMaloteIiprRascunho.className = 'painel-malotes-rascunho';
            painelMaloteIiprRascunho.innerHTML = '<strong>Malote IIPR em montagem</strong><div class="muted">Lotes separados aguardando lacre e fechamento.</div><ul>' + lotesRascunhoIipr.join('') + '</ul>';
        } else {
            painelMaloteIiprRascunho.className = 'painel-malotes-vazio';
            painelMaloteIiprRascunho.innerHTML = 'Nenhum malote IIPR em montagem.';
        }

        var htmlGrupos = [];
        for (var lacre in gruposIipr) {
            if (!Object.prototype.hasOwnProperty.call(gruposIipr, lacre)) continue;
            var grupo = gruposIipr[lacre];
            var lotesAgrupados = [];
            for (var j = 0; j < grupo.chips.length; j++) {
                lotesAgrupados.push(grupo.chips[j].getAttribute('data-lote') || '');
            }
            var grupoEmMontagemCorreios = gruposRascunhoCorreios.indexOf(grupo.chave) !== -1;
            var grupoStatusClasse = grupoEmMontagemCorreios ? 'montagem' : (grupo.lacre_correios || grupo.etiqueta_correios ? (grupo.pendente ? 'pendente-salvar' : 'correios') : (grupo.pendente ? 'pendente-salvar' : 'iipr'));
            var grupoStatusTexto = grupoEmMontagemCorreios ? 'Em montagem Correios' : (grupo.lacre_correios || grupo.etiqueta_correios ? (grupo.pendente ? 'Correios pendente salvar' : 'Fechado Correios') : (grupo.pendente ? 'IIPR pendente salvar' : 'Fechado IIPR'));
            htmlGrupos.push(
                '<tr>' +
                    '<td><input type="checkbox" class="check-malote-iipr" data-lacre-iipr="' + escapeHtml(grupo.lacre_iipr) + '" data-grupo-iipr="' + escapeHtml(grupo.chave) + '"' + (grupoEmMontagemCorreios ? ' checked' : '') + '></td>' +
                    '<td>' + escapeHtml(grupo.lacre_iipr) + '</td>' +
                    '<td>' + escapeHtml(lotesAgrupados.join(', ')) + '</td>' +
                    '<td>' + escapeHtml(grupo.lacre_correios || '-') + '</td>' +
                    '<td style="word-break:break-all;">' + escapeHtml(grupo.etiqueta_correios || '-') + '</td>' +
                    '<td><span class="malote-status ' + grupoStatusClasse + '">' + grupoStatusTexto + '</span></td>' +
                '</tr>'
            );
        }

        if (estadoPosto.correios_atual && gruposRascunhoCorreios.length) {
            var gruposRascunhoHtml = [];
            for (var g = 0; g < gruposRascunhoCorreios.length; g++) {
                var grupoAtual = gruposIipr[gruposRascunhoCorreios[g]];
                if (!grupoAtual) continue;
                var lotesGrupoAtual = [];
                for (var cg = 0; cg < grupoAtual.chips.length; cg++) {
                    lotesGrupoAtual.push(grupoAtual.chips[cg].getAttribute('data-lote') || '');
                }
                gruposRascunhoHtml.push('<li>Lacre IIPR ' + escapeHtml(grupoAtual.lacre_iipr || '-') + ' • lotes ' + escapeHtml(lotesGrupoAtual.join(', ')) + '</li>');
            }
            painelMaloteCorreiosRascunho.className = 'painel-malotes-rascunho';
            painelMaloteCorreiosRascunho.innerHTML = '<strong>Malote Correios em montagem</strong><div class="muted">Malotes IIPR separados aguardando lacre e etiqueta do Correios.</div><ul>' + gruposRascunhoHtml.join('') + '</ul>';
        } else {
            painelMaloteCorreiosRascunho.className = 'painel-malotes-vazio';
            painelMaloteCorreiosRascunho.innerHTML = 'Nenhum malote Correios em montagem.';
        }

        if (!htmlGrupos.length) {
            painelMalotesIipr.className = 'painel-malotes-vazio';
            painelMalotesIipr.innerHTML = 'Os malotes IIPR aparecerão aqui depois do primeiro fechamento.';
        } else {
            painelMalotesIipr.className = '';
            painelMalotesIipr.innerHTML = '<table class="tabela-malotes"><thead><tr><th></th><th>Lacre IIPR</th><th>Lotes</th><th>Lacre Correios</th><th>Etiqueta Correios</th><th>Status</th></tr></thead><tbody>' + htmlGrupos.join('') + '</tbody></table>';
        }

        publicarResumoPrevia();
    }

    function obterChipsMarcadosNoPainel() {
        var checks = painelMalotesLotes ? painelMalotesLotes.querySelectorAll('.check-malote-lote:checked') : [];
        var chips = [];
        for (var i = 0; i < checks.length; i++) {
            var codigo = checks[i].getAttribute('data-codigo') || '';
            var chip = codigo ? document.querySelector('.operacao-chip[data-codigo="' + codigo + '"]') : null;
            if (chip) chips.push(chip);
        }
        return chips;
    }

    function obterChipsConfirmadosSemIiprDoPostoAtual() {
        if (!postoSelecionadoMalote) return [];
        return obterChipsConfirmadosSemIiprDoPosto(postoSelecionadoMalote);
    }

    function obterChipsDosIiprMarcados() {
        var checks = painelMalotesIipr ? painelMalotesIipr.querySelectorAll('.check-malote-iipr:checked') : [];
        var chips = [];
        var vistos = {};
        for (var i = 0; i < checks.length; i++) {
            var grupoIipr = checks[i].getAttribute('data-grupo-iipr') || '';
            if (!grupoIipr) continue;
            var chipsMesmoLacre = document.querySelectorAll('.operacao-chip[data-posto="' + postoSelecionadoMalote + '"][data-grupo-iipr="' + grupoIipr + '"]');
            for (var j = 0; j < chipsMesmoLacre.length; j++) {
                var codigo = chipsMesmoLacre[j].getAttribute('data-codigo') || '';
                if (!vistos[codigo]) {
                    vistos[codigo] = true;
                    chips.push(chipsMesmoLacre[j]);
                }
            }
        }
        return chips;
    }

    function obterChipsIiprSemCorreiosDoPostoAtual() {
        if (!postoSelecionadoMalote) return [];
        var chipsPosto = obterChipsPosto(postoSelecionadoMalote);
        var chips = [];
        for (var i = 0; i < chipsPosto.length; i++) {
            var dados = obterDadosChipOperacao(chipsPosto[i]);
            if (!dados) continue;
            if (!dados.conferido) continue;
            if (!dados.lacre_iipr) continue;
            // v0.9.25.14+: Correios pode ser fechado em duas etapas:
            // 1) salvar lacre do malote
            // 2) depois salvar a etiqueta do mesmo malote
            // Então só ignora quando o chip já tiver ambos os campos preenchidos.
            if (dados.lacre_correios && dados.etiqueta_correios) continue;
            chips.push(chipsPosto[i]);
        }
        return chips;
    }

    if (btnMontarMaloteIipr) {
        btnMontarMaloteIipr.addEventListener('click', function() {
            if (!postoSelecionadoMalote) {
                alert('Selecione um posto antes de montar o malote IIPR.');
                return;
            }
            var chips = obterChipsMarcadosNoPainel();
            if (!chips.length) {
                alert('Marque os lotes que entrarão neste malote IIPR.');
                return;
            }
            var estado = obterEstadoPostoRascunho(postoSelecionadoMalote);
            if (!estado.iipr_atual) {
                estado.iipr_atual = { id: criarIdMalote('GI'), codigos: [] };
            }
            for (var i = 0; i < chips.length; i++) {
                var dados = obterDadosChipOperacao(chips[i]);
                if (!dados || !dados.conferido) {
                    alert('Somente lotes já conferidos em verde podem entrar no malote IIPR.');
                    return;
                }
                if (dados.grupo_iipr && estado.iipr_atual.codigos.indexOf(dados.codigo) === -1) {
                    alert('O lote ' + dados.lote + ' já está vinculado a um malote IIPR. Limpe o vínculo antes de remontar.');
                    return;
                }
            }
            for (var j = 0; j < chips.length; j++) {
                var codigo = chips[j].getAttribute('data-codigo') || '';
                if (codigo && estado.iipr_atual.codigos.indexOf(codigo) === -1) {
                    estado.iipr_atual.codigos.push(codigo);
                }
            }
            estado.iipr_atual.codigos = normalizarListaUnica(estado.iipr_atual.codigos);
            salvarRascunhoMalotesLocal();
            renderizarPainelMalotes();
            if (inputLacreIiprMalote) inputLacreIiprMalote.focus();
        });
    }

    if (btnSalvarMaloteIipr) {
        btnSalvarMaloteIipr.addEventListener('click', function() {
            var lacreIipr = normalizarNumeroLacre(inputLacreIiprMalote ? inputLacreIiprMalote.value : '');
            var estado = obterEstadoPostoRascunho(postoSelecionadoMalote);
            if (!lacreIipr) {
                alert('Informe o lacre IIPR.');
                if (inputLacreIiprMalote) inputLacreIiprMalote.focus();
                return;
            }
            if (!estado.iipr_atual || !estado.iipr_atual.codigos || !estado.iipr_atual.codigos.length) {
                alert('Monte primeiro o malote IIPR com os lotes corretos.');
                return;
            }
            var grupoIipr = estado.iipr_atual.id || criarIdMalote('GI');
            var chips = [];
            for (var i0 = 0; i0 < estado.iipr_atual.codigos.length; i0++) {
                var chipAtual = obterChipPorCodigo(estado.iipr_atual.codigos[i0]);
                if (chipAtual) chips.push(chipAtual);
            }
            if (!chips.length) {
                alert('Não há lotes válidos no malote IIPR em montagem.');
                return;
            }

            for (var i = 0; i < chips.length; i++) {
                registrarAtribuicaoPendenteNoChip(chips[i], {
                    lacre_iipr: lacreIipr,
                    grupo_iipr: grupoIipr,
                    lacre_correios: '',
                    grupo_correios: '',
                    etiqueta_correios: ''
                });
            }
            estado.iipr_atual = null;
            salvarRascunhoMalotesLocal();
            renderizarPainelMalotes();
            atualizarColunaMaloteTradicional();
            if (inputLacreIiprMalote) inputLacreIiprMalote.value = '';
            limparModoVoz('Malote IIPR fechado no rascunho.');
        });
    }

    if (btnMontarMaloteCorreios) {
        btnMontarMaloteCorreios.addEventListener('click', function() {
            if (!postoSelecionadoMalote) {
                alert('Selecione um posto antes de montar o malote Correios.');
                return;
            }
            var checks = painelMalotesIipr ? painelMalotesIipr.querySelectorAll('.check-malote-iipr:checked') : [];
            if (!checks.length) {
                alert('Marque os malotes IIPR que entrarão no malote maior dos Correios.');
                return;
            }
            var estado = obterEstadoPostoRascunho(postoSelecionadoMalote);
            if (!estado.correios_atual) {
                estado.correios_atual = { id: criarIdMalote('GC'), grupos_iipr: [] };
            }
            for (var i = 0; i < checks.length; i++) {
                var grupoIipr = String(checks[i].getAttribute('data-grupo-iipr') || '').trim();
                if (!grupoIipr) continue;
                var chipsGrupo = obterChipsPorGrupoIipr(postoSelecionadoMalote, grupoIipr);
                var grupoComCorreios = false;
                for (var j = 0; j < chipsGrupo.length; j++) {
                    var dadosGrupo = obterDadosChipOperacao(chipsGrupo[j]);
                    if (dadosGrupo && (dadosGrupo.lacre_correios || dadosGrupo.etiqueta_correios)) {
                        grupoComCorreios = true;
                        break;
                    }
                }
                if (grupoComCorreios && estado.correios_atual.grupos_iipr.indexOf(grupoIipr) === -1) {
                    alert('Um dos malotes IIPR marcados já está vinculado a um malote Correios. Limpe o vínculo antes de remontar.');
                    return;
                }
            }
            for (var k = 0; k < checks.length; k++) {
                var grupoSelecionado = String(checks[k].getAttribute('data-grupo-iipr') || '').trim();
                if (grupoSelecionado && estado.correios_atual.grupos_iipr.indexOf(grupoSelecionado) === -1) {
                    estado.correios_atual.grupos_iipr.push(grupoSelecionado);
                }
            }
            estado.correios_atual.grupos_iipr = normalizarListaUnica(estado.correios_atual.grupos_iipr);
            salvarRascunhoMalotesLocal();
            renderizarPainelMalotes();
            if (inputLacreCorreiosMalote) inputLacreCorreiosMalote.focus();
        });
    }

    if (btnSalvarMaloteCorreios) {
        btnSalvarMaloteCorreios.addEventListener('click', function() {
            var lacreCorreios = normalizarNumeroLacre(inputLacreCorreiosMalote ? inputLacreCorreiosMalote.value : '');
            var etiquetaCorreios = inputEtiquetaCorreiosMalote ? String(inputEtiquetaCorreiosMalote.value || '').trim() : '';
            var estado = obterEstadoPostoRascunho(postoSelecionadoMalote);
            if (!lacreCorreios && !etiquetaCorreios) {
                alert('Informe ao menos o lacre Correios ou a etiqueta Correios.');
                if (inputLacreCorreiosMalote) inputLacreCorreiosMalote.focus();
                return;
            }
            if (!estado.correios_atual || !estado.correios_atual.grupos_iipr || !estado.correios_atual.grupos_iipr.length) {
                alert('Monte primeiro o malote Correios com os malotes IIPR corretos.');
                return;
            }

            var grupoCorreios = estado.correios_atual.id || criarIdMalote('GC');
            var chips = [];
            var vistos = {};
            for (var i = 0; i < estado.correios_atual.grupos_iipr.length; i++) {
                var chipsGrupo = obterChipsPorGrupoIipr(postoSelecionadoMalote, estado.correios_atual.grupos_iipr[i]);
                for (var j = 0; j < chipsGrupo.length; j++) {
                    var codigo = chipsGrupo[j].getAttribute('data-codigo') || '';
                    if (codigo && !vistos[codigo]) {
                        vistos[codigo] = true;
                        chips.push(chipsGrupo[j]);
                    }
                }
            }
            if (!chips.length) {
                alert('Não há lotes IIPR válidos no malote Correios em montagem.');
                return;
            }

            for (var k = 0; k < chips.length; k++) {
                var dadosAtuais = obterDadosChipOperacao(chips[k]);
                registrarAtribuicaoPendenteNoChip(chips[k], {
                    lacre_iipr: dadosAtuais ? dadosAtuais.lacre_iipr : '',
                    grupo_iipr: dadosAtuais ? dadosAtuais.grupo_iipr : '',
                    lacre_correios: lacreCorreios || (dadosAtuais ? dadosAtuais.lacre_correios : ''),
                    grupo_correios: grupoCorreios,
                    etiqueta_correios: etiquetaCorreios || (dadosAtuais ? dadosAtuais.etiqueta_correios : '')
                });
            }
            estado.correios_atual = null;
            salvarRascunhoMalotesLocal();
            renderizarPainelMalotes();
            atualizarColunaMaloteTradicional();
            if (inputLacreCorreiosMalote) inputLacreCorreiosMalote.value = '';
            if (inputEtiquetaCorreiosMalote) inputEtiquetaCorreiosMalote.value = '';
            limparModoVoz('Malote Correios fechado no rascunho.');
        });
    }

    if (btnSalvarTudoMalotes) {
        btnSalvarTudoMalotes.addEventListener('click', function() {
            var pacotes = [];
            for (var codigo in malotesRascunho.atribuicoes) {
                if (!Object.prototype.hasOwnProperty.call(malotesRascunho.atribuicoes, codigo)) continue;
                pacotes.push(malotesRascunho.atribuicoes[codigo]);
            }
            if (!pacotes.length) {
                alert('Não há vínculos pendentes para salvar.');
                return;
            }
            persistirAtribuicoesLote(pacotes, function() {
                var codigos = Object.keys(malotesRascunho.atribuicoes || {});
                for (var i = 0; i < codigos.length; i++) {
                    var chip = obterChipPorCodigo(codigos[i]);
                    if (!chip) continue;
                    chip.setAttribute('data-malote-pendente', '0');
                    chip.classList.remove('malote-pendente');
                    var linha = document.querySelector('tr[data-codigo="' + codigos[i] + '"]');
                    if (linha) {
                        linha.setAttribute('data-malote-pendente', '0');
                        atualizarColunaMaloteLinha(linha);
                    }
                }
                limparRascunhoMalotesLocal();
                salvarRascunhoMalotesLocal();
                renderizarPainelMalotes();
                atualizarColunaMaloteTradicional();
                publicarResumoPrevia();
            });
        });
    }

    if (btnDescartarRascunhoMalotes) {
        btnDescartarRascunhoMalotes.addEventListener('click', function() {
            if (!contarAtribuicoesPendentesMalotes() && !confirm('Não há nada pendente. Deseja recarregar a página mesmo assim?')) {
                return;
            }
            if (contarAtribuicoesPendentesMalotes() && !confirm('Descartar o rascunho local vai remover os malotes em montagem e restaurar a página para o que já está salvo. Continuar?')) {
                return;
            }
            limparRascunhoMalotesLocal();
            window.location.reload();
        });
    }

    if (btnLimparMaloteLote) {
        btnLimparMaloteLote.addEventListener('click', function() {
            var chips = obterChipsMarcadosNoPainel();
            if (!chips.length) {
                alert('Selecione os lotes que terão os vínculos apagados.');
                return;
            }
            for (var i = 0; i < chips.length; i++) {
                var dadosAtuais = obterDadosChipOperacao(chips[i]);
                removerCodigoDosRascunhosPosto(postoSelecionadoMalote, chips[i].getAttribute('data-codigo') || '', dadosAtuais ? dadosAtuais.grupo_iipr : '');
                registrarAtribuicaoPendenteNoChip(chips[i], {
                    lacre_iipr: '',
                    grupo_iipr: '',
                    lacre_correios: '',
                    grupo_correios: '',
                    etiqueta_correios: ''
                });
            }
            salvarRascunhoMalotesLocal();
            renderizarPainelMalotes();
            atualizarColunaMaloteTradicional();
            limparModoVoz('Vínculos removidos no rascunho.');
        });
    }

    if (btnAlternarVozMalotes) {
        btnAlternarVozMalotes.addEventListener('click', function() {
            alternarReconhecimentoVoz();
        });
    }

    if (btnAbrirPreviaMalotes) {
        btnAbrirPreviaMalotes.addEventListener('click', function() {
            abrirPreviaMalotes();
        });
    }

    if (btnAbrirControleRemoto) {
        btnAbrirControleRemoto.addEventListener('click', function() {
            abrirControleRemoto();
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
        var campoInlineMalote = e.target && e.target.closest ? e.target.closest('.operacao-posto-mapa input, .operacao-posto-mapa textarea, .operacao-posto-mapa select') : null;
        if (campoInlineMalote) {
            return;
        }
        var botaoFecharIiprInline = e.target && e.target.closest ? e.target.closest('.btn-inline-fechar-iipr') : null;
        if (botaoFecharIiprInline) {
            var postoIipr = botaoFecharIiprInline.getAttribute('data-posto') || '';
            var campoIipr = document.querySelector('.input-inline-lacre-iipr[data-posto="' + postoIipr + '"]');
            selecionarPostoMalote(postoIipr);
            if (fecharMaloteIiprDoPosto(postoIipr, campoIipr ? campoIipr.value : '')) {
                if (campoIipr) campoIipr.value = '';
            }
            return;
        }
        var botaoFecharCorreiosInline = e.target && e.target.closest ? e.target.closest('.btn-inline-fechar-correios') : null;
        if (botaoFecharCorreiosInline) {
            var postoCorreios = botaoFecharCorreiosInline.getAttribute('data-posto') || '';
            var campoLacreCorreios = document.querySelector('.input-inline-lacre-correios[data-posto="' + postoCorreios + '"]');
            var campoEtiquetaCorreios = document.querySelector('.input-inline-etiqueta-correios[data-posto="' + postoCorreios + '"]');
            selecionarPostoMalote(postoCorreios);
            if (fecharMaloteCorreiosDoPosto(postoCorreios, campoLacreCorreios ? campoLacreCorreios.value : '', campoEtiquetaCorreios ? campoEtiquetaCorreios.value : '')) {
                if (campoLacreCorreios) campoLacreCorreios.value = '';
                if (campoEtiquetaCorreios) campoEtiquetaCorreios.value = '';
            }
            return;
        }
        var botaoSalvarPostoInline = e.target && e.target.closest ? e.target.closest('.btn-inline-salvar-posto') : null;
        var botaoDesfazerPostoInline = e.target && e.target.closest ? e.target.closest('.btn-inline-desfazer-posto') : null;
        if (botaoDesfazerPostoInline) {
            var postoDesfazer = botaoDesfazerPostoInline.getAttribute('data-posto') || '';
            selecionarPostoMalote(postoDesfazer);
            desfazerUltimaAcaoMalote(postoDesfazer);
            return;
        }
        if (botaoSalvarPostoInline) {
            var postoSalvar = botaoSalvarPostoInline.getAttribute('data-posto') || '';
            selecionarPostoMalote(postoSalvar);
            salvarPendenciasDoPosto(postoSalvar);
            return;
        }
        var chip = e.target && e.target.closest ? e.target.closest('.operacao-chip') : null;
        if (chip) {
            selecionarPostoMalote(chip.getAttribute('data-posto') || '', chip.getAttribute('data-regional') || '');
            destacarChipOperacao(chip.getAttribute('data-codigo') || '');
            abrirModalChipDetalhe(chip);
            return;
        }
        var linhaPosto = e.target && e.target.closest ? e.target.closest('.operacao-posto-row') : null;
        if (linhaPosto) {
            var cliqueDentroMapa = e.target && e.target.closest ? e.target.closest('.operacao-posto-mapa') : null;
            if (cliqueDentroMapa) {
                return;
            }
            selecionarPostoMalote(linhaPosto.getAttribute('data-posto') || '', linhaPosto.getAttribute('data-grupo') || '');
        }
    });

    document.addEventListener('blur', function(e) {
        var alvo = e.target;
        if (!alvo || !alvo.classList) return;
        if (alvo.classList.contains('input-inline-lacre-iipr')) {
            validarLacreIiprUnicoEntrePostos(alvo.value || '', alvo.getAttribute('data-posto') || '', false);
            avisarSeHaDuplicidadeLacre(alvo.value || '', alvo.getAttribute('data-posto') || '');
            return;
        }
        if (alvo.classList.contains('input-inline-lacre-correios')) {
            avisarSeHaDuplicidadeLacre(alvo.value || '', alvo.getAttribute('data-posto') || '');
            return;
        }
        if (alvo.classList.contains('input-inline-etiqueta-correios')) {
            avisarSeHaDuplicidadeEtiqueta(alvo.value || '', alvo.getAttribute('data-posto') || '');
        }
    }, true);

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
            atualizarVisualChipOperacao(chips[i]);
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
        renderizarMapaMalotesPosto(row.getAttribute('data-posto') || '');
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
        atualizarVisualChipOperacao(chip);
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
        selecionarPostoMalote(chip.getAttribute('data-posto') || '', chip.getAttribute('data-regional') || '');
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
        renderizarPainelMalotes();
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
            ['Lacre IIPR', chip.getAttribute('data-lacre-iipr') || 'Pendente'],
            ['Grupo IIPR', chip.getAttribute('data-grupo-iipr') || 'Pendente'],
            ['Lacre Correios', chip.getAttribute('data-lacre-correios') || 'Pendente'],
            ['Grupo Correios', chip.getAttribute('data-grupo-correios') || 'Pendente'],
            ['Etiqueta Correios', chip.getAttribute('data-etiqueta-correios') || 'Pendente'],
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
        var finalizado = false;
        var watchdog = null;

        function concluirSom() {
            if (finalizado) return;
            finalizado = true;
            if (watchdog) {
                clearTimeout(watchdog);
                watchdog = null;
            }
            tocando = false;
            tocarProximoSom();
        }

        try {
            som.currentTime = 0;
            som.onended = concluirSom;
            som.onerror = concluirSom;
            var durMs = 2500;
            if (isFinite(som.duration) && som.duration > 0) {
                durMs = Math.max(1200, Math.floor(som.duration * 1000) + 350);
            }
            watchdog = setTimeout(concluirSom, durMs);
            var playPromise = som.play();
            if (playPromise && playPromise.then) {
                playPromise.catch(concluirSom);
            }
        } catch (e) {
            concluirSom();
        }
    }

    function enfileirarSom(som) {
        if (!som) return;
        filaSons.push(som);
        if (!tocando) {
            tocarProximoSom();
        }
    }

    function tocarBeepConfirmacao() {
        if (!beep || (muteBeep && muteBeep.checked)) return;
        try {
            var srcBeep = beep.getAttribute('src') || beep.currentSrc || 'beep_correio.mp3';
            var beepInstancia = new Audio(srcBeep);
            beepInstancia.preload = 'auto';
            beepInstancia.volume = 1;
            var playPromise = beepInstancia.play();
            if (playPromise && playPromise.catch) {
                playPromise.catch(function() {});
            }
        } catch (e) {}
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
            } else {
                falarTexto('posto dos correios');
            }
            return;
        }
        if (mensagemLeitura) {
            mensagemLeitura.innerHTML = '<strong>Posto do Poupa Tempo:</strong> altere o tipo para Poupa Tempo ou Todos.';
        }
        if (postoPoupaTempo) {
            enfileirarSom(postoPoupaTempo);
        } else {
            falarTexto('posto do poupa tempo');
        }
    }

    var listaSons = [];
    if (beep) listaSons.push(beep);
    if (concluido) listaSons.push(concluido);
    if (pacoteJaConferido) listaSons.push(pacoteJaConferido);
    if (pacoteOutraRegional) listaSons.push(pacoteOutraRegional);
    if (postoPoupaTempo) listaSons.push(postoPoupaTempo);
    if (pertenceCorreios) listaSons.push(pertenceCorreios);
    if (pacoteNaoEncontradoAudio) listaSons.push(pacoteNaoEncontradoAudio);

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

        if (musicaFinalConferencia) {
            try {
                musicaFinalConferencia.volume = 0;
                var pf = musicaFinalConferencia.play();
                if (pf && pf.then) {
                    pf.then(function() {
                        musicaFinalConferencia.pause();
                        musicaFinalConferencia.currentTime = 0;
                        musicaFinalConferencia.volume = 1;
                    }).catch(function() {
                        musicaFinalConferencia.volume = 1;
                    });
                }
            } catch (e3) {}
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
            var turnoSalvar = 'Manhã';  // Turno padrão
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

    if (desativarCreditosFinais) {
        desativarCreditosFinais.addEventListener('change', function() {
            salvarPreferenciaCreditos();
            if (creditosDesativados()) {
                pararCreditosFinais();
            }
        });
    }

    document.addEventListener('mousemove', aplicarInterrupcaoCreditos);
    document.addEventListener('keydown', aplicarInterrupcaoCreditos);
    document.addEventListener('touchstart', aplicarInterrupcaoCreditos, { passive: true });
    document.addEventListener('click', aplicarInterrupcaoCreditos);

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

    carregarPreferenciaCreditos();
    carregarRascunhoMalotesLocal();
    aplicarAtribuicoesPendentesDoRascunho();

    var chipsOperacaoIniciais = document.querySelectorAll('.operacao-chip');
    for (var iInit = 0; iInit < chipsOperacaoIniciais.length; iInit++) {
        atualizarVisualChipOperacao(chipsOperacaoIniciais[iInit]);
    }

    aplicarFiltroTipoVisual(obterTipoInicioSelecionado());
    atualizarResumoTodasTabelas();
    sincronizarPainelOperacao();
    atualizarColunaMaloteTradicional();
    renderizarMapasMalotesPostos();
    renderizarDiagnosticoVoz();
    publicarResumoPrevia();
    iniciarPollingControleRemoto();
    verificarConclusaoFinalCorreios();
    
    // Função para salvar conferência via AJAX
    function salvarConferencia(lote, regional, posto, dataexp, qtd, codbar, usuario) {
        var codigo = String(codbar || '').replace(/\D+/g, '');
        if (codigo.length > 19) {
            codigo = codigo.substr(codigo.length - 19, 19);
        }
        if (!lote && codigo.length === 19) lote = codigo.substr(0, 8);
        if (!regional && codigo.length === 19) regional = codigo.substr(8, 3);
        if (!posto && codigo.length === 19) posto = codigo.substr(11, 3);
        if ((!qtd || parseInt(qtd, 10) <= 0) && codigo.length === 19) qtd = parseInt(codigo.substr(14, 5), 10) || 1;
        if (!dataexp) {
            var now = new Date();
            dataexp = String(now.getDate()).padStart(2, '0') + '-' + String(now.getMonth() + 1).padStart(2, '0') + '-' + now.getFullYear();
        }
        if (!usuario) {
            try { usuario = sessionStorage.getItem(storageUsuarioKey) || ''; } catch (e1) { usuario = ''; }
            if (!usuario) {
                var badge = document.getElementById('usuarioBadge');
                if (badge) usuario = (badge.textContent || '').trim();
            }
        }
        if (codigo.length !== 19 || !usuario) {
            console.error('Salvar conferência ignorado por dados incompletos', { lote: lote, regional: regional, posto: posto, dataexp: dataexp, qtd: qtd, codbar: codigo, usuario: usuario });
            return;
        }

        var formData = new FormData();
        formData.append('salvar_lote_ajax', '1');
        formData.append('lote', lote);
        formData.append('regional', regional);
        formData.append('posto', posto);
        formData.append('dataexp', dataexp);
        formData.append('qtd', qtd);
        formData.append('codbar', codigo);
        formData.append('usuario', usuario);
        
        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if (!(data && (data.success || data.sucesso))) {
                console.error('Erro ao salvar:', data && data.erro ? data.erro : data);
                if (mensagemLeitura) {
                    mensagemLeitura.innerHTML = '<strong>Falha ao salvar no banco:</strong> ' + ((data && data.erro) ? data.erro : 'erro desconhecido');
                }
            }
        })
        .catch(function(error) {
            console.error('Erro AJAX:', error);
            if (mensagemLeitura) {
                mensagemLeitura.innerHTML = '<strong>Falha ao salvar no banco:</strong> erro de comunicação AJAX.';
            }
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
        try {
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
            // Resíduo comum de scanner: um dígito sobrando logo após leitura válida.
            // Limpa automaticamente para não contaminar a próxima leitura.
            var deltaUltimaLeitura = Date.now() - ultimaLeituraProcessadaEm;
            if (valor.length > 0 && valor.length <= 3 && deltaUltimaLeitura >= 0 && deltaUltimaLeitura <= 1200) {
                input.value = '';
            }
            return;
        }
        if (valor.length > 19) {
            // Resíduos de leitura anterior podem ficar no início do campo.
            // Priorizamos os últimos 19 dígitos, que normalmente são da leitura atual.
            valor = valor.substr(valor.length - 19, 19);
        }
        if (valor.length !== 19) {
            input.value = '';
            return;
        }

        var agoraLeitura = Date.now();
        if (codigosEmProcessamento[valor]) {
            input.value = '';
            return;
        }
        if (ultimoCodigoProcessado === valor && (agoraLeitura - ultimaLeituraProcessadaEm) < 700) {
            input.value = '';
            return;
        }
        codigosEmProcessamento[valor] = true;
        ultimoCodigoProcessado = valor;
        ultimaLeituraProcessadaEm = agoraLeitura;
        // Limpa o input IMEDIATAMENTE ao receber código válido.
        // Não espera o processamento concluir (evita que código fique preso no campo durante async).
        input.value = '';
        input.focus();

        function finalizarProcessamento(limparInput) {
            delete codigosEmProcessamento[valor];
            if (limparInput && input) {
                input.value = '';
                input.focus();
                setTimeout(function() { if (input) input.value = ''; }, 20);
                setTimeout(function() { if (input) input.value = ''; }, 80);
                setTimeout(function() { if (input) input.value = ''; }, 180);
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

        // Regra operacional: não alterna regional de Correios sem aviso + confirmação.
        // Se ainda há regional em andamento, tocar alerta e exigir confirmação explícita.

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
        } else if (podeConferir && tipoAtual === 'correios' && tipoPacote === 'correios') {
            var regionalAtualNorm = normalizarRegionalValor(regionalAtual);
            var regionalNovaNorm = regionalDoPacoteNorm || normalizarRegionalValor(regionalDoPacote);
            if (regionalAtualNorm && regionalNovaNorm && regionalAtualNorm !== regionalNovaNorm) {
                if (mensagemLeitura) {
                    mensagemLeitura.innerHTML = '<strong>Pacote de outra regional:</strong> regional atual ' + escapeHtml(regionalAtualNorm) + ', pacote da regional ' + escapeHtml(regionalNovaNorm) + '.';
                }
                somAlerta = pacoteOutraRegional;
                enfileirarSom(somAlerta);
                somAlerta = null;
                if (!pacoteOutraRegional) {
                    falarTexto('pacote de outra regional');
                }

                var desejaTrocarRegional = window.confirm(
                    'ATENÇÃO: pacote de outra regional.\n\n' +
                    'Regional em conferência: ' + regionalAtualNorm + '\n' +
                    'Regional do pacote lido: ' + regionalNovaNorm + '\n\n' +
                    'OK = Trocar para a regional ' + regionalNovaNorm + ' e continuar.\n' +
                    'Cancelar = Manter regional ' + regionalAtualNorm + ' (pacote não será conferido).'
                );

                if (desejaTrocarRegional) {
                    regionalAtual = regionalNovaNorm;
                    if (mensagemLeitura) {
                        mensagemLeitura.innerHTML = '<strong>Troca confirmada:</strong> conferência alterada para regional ' + escapeHtml(regionalNovaNorm) + '.';
                    }
                } else {
                    podeConferir = false;
                    if (mensagemLeitura) {
                        mensagemLeitura.innerHTML = '<strong>Troca cancelada:</strong> regional ' + escapeHtml(regionalAtualNorm) + ' mantida. Pacote da regional ' + escapeHtml(regionalNovaNorm) + ' não foi conferido.';
                    }
                }
            }
        } else if (!modoTodos && podeConferir && tipoAtual === 'correios' && tipoPacote === 'poupatempo') {
            somAlerta = postoPoupaTempo;
            podeConferir = false;
            if (mensagemLeitura) {
                mensagemLeitura.innerHTML = '<strong>Posto do Poupa Tempo:</strong> altere o tipo para Poupa Tempo ou Todos.';
            }
            if (!postoPoupaTempo) {
                falarTexto('posto do poupa tempo');
            }
        } else if (!modoTodos && podeConferir && tipoAtual === 'poupatempo' && tipoPacote === 'correios') {
            somAlerta = pertenceCorreios;
            podeConferir = false;
            if (mensagemLeitura) {
                mensagemLeitura.innerHTML = '<strong>Posto dos Correios:</strong> altere o tipo para Correios ou Todos.';
            }
            if (!pertenceCorreios) {
                falarTexto('posto dos correios');
            }
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

        tocarBeepConfirmacao();
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
        var todasLinhas = document.querySelectorAll('tbody tr[data-codigo]');
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
            // v0.9.25.14: Para Correios, concluído dispara somente quando terminar TODA a regional.
            grupoAtual = linha.getAttribute('data-regional-real') || linha.getAttribute('data-regional-codigo') || linha.getAttribute('data-regional');
            for (var i2 = 0; i2 < todasLinhas.length; i2++) {
                var regLinha = todasLinhas[i2].getAttribute('data-regional-real') || todasLinhas[i2].getAttribute('data-regional-codigo') || todasLinhas[i2].getAttribute('data-regional');
                if (todasLinhas[i2].getAttribute('data-ispt') !== '1' && regLinha === grupoAtual) {
                    linhasDoGrupo.push(todasLinhas[i2]);
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
            // v0.9.25.14+: Para Correios, auto-seleciona posto no painel de malotes
            // assim salvar_iipr remoto já encontra o posto sem precisar de seleção manual no PC
            if (tipoAtual !== 'poupatempo' && grupoAtual) {
                selecionarPostoMalote(linha.getAttribute('data-posto') || '');
            }
            regionalAtual = null;
            tipoAtual = null;
            primeiroConferido = false;
        }

        verificarConclusaoFinalCorreios();
        finalizarProcessamento(true);
        } catch (erroProcessamentoLeitura) {
            console.error('Erro em processarLeituraCodigo:', erroProcessamentoLeitura);
            if (input) {
                input.value = '';
                input.focus();
            }
        }
    }

    window.processarLeituraCodigo = processarLeituraCodigo;

    var scanBuffer = '';
    var scanTimer = null;
    document.addEventListener('keydown', function(e) {
        if (!e) return;
        var alvo = e.target;
        // Se o foco está no campo principal, o próprio listener do input já processa.
        // Evita processamento duplicado (input + keydown global), que causa resíduos e inconsistências.
        if (alvo && alvo.id === 'codigo_barras') {
            return;
        }
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
            if (input) input.value = ''; // Limpar input visual para evitar resíduos
        }
    });
    
    // Resetar conferência
    btnResetar.addEventListener("click", function() {
        if (confirm("Tem certeza que deseja reiniciar a conferência? Isso irá APAGAR todos os dados conferidos do banco!")) {
            // Obter datas filtradas
            var datas = [];

            for (var i = 0; i < datasFiltroSql.length; i++) {
                if (datasFiltroSql[i]) {
                    datas.push(datasFiltroSql[i]);
                }
            }
            
            // Resetar visualmente
            var trsConfirmados = document.querySelectorAll("tr.confirmado");
            for (var j = 0; j < trsConfirmados.length; j++) {
                trsConfirmados[j].classList.remove("confirmado");
            }
            var chipsAtribuidos = document.querySelectorAll('.operacao-chip');
            for (var c = 0; c < chipsAtribuidos.length; c++) {
                chipsAtribuidos[c].setAttribute('data-conf', '0');
                aplicarAtribuicaoNoChip(chipsAtribuidos[c], {
                    lacre_iipr: '',
                    grupo_iipr: '',
                    lacre_correios: '',
                    grupo_correios: '',
                    etiqueta_correios: '',
                    usuario_lacre: '',
                    atualizado_lacre: ''
                });
            }
            atualizarResumoTodasTabelas();
            sincronizarPainelOperacao();
            
            regionalAtual = null;
            tipoAtual = null; // v9.2: Reseta tipo
            primeiroConferido = false; // v9.2: Reseta flag
            input.value = "";
            input.focus();
            postoSelecionadoMalote = '';
            grupoSelecionadoMalote = '';
            renderizarPainelMalotes();
            
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
        window.__conferenciaInit = false;
        try {
            if (mensagemLeitura) {
                mensagemLeitura.innerHTML = '<strong>Falha ao iniciar conferência:</strong> ' + (e && e.message ? e.message : 'erro inesperado');
            }
        } catch (e1) {}
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
        if (typeof window.liberarPaginaComUsuario === 'function') {
            window.liberarPaginaComUsuario(nome, false);
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
        if (typeof window.selecionarTipoConferencia === 'function') {
            window.selecionarTipoConferencia(tipo);
            return;
        }
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
