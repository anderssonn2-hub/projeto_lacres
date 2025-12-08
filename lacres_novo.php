<?php
/* lacres_novo.php — Versão 1.6
   Patch: liberar etiqueta ao apagar (mover entre inputs)
   Gerado em 2025-11-07T12:28:56 */

// MELHORIAS ANTERIORES: v8.7 (fallback 0 para lacres), v8.6 (mapa de lacres), v8.5 (persistência confirmada)
// v8.8: Corrige captura de lacres e etiquetas dos Correios (HTML + POST + gravação)
// - Introduz arrays alinhados no formulário: posto_lacres[], lacre_iipr[], lacre_correios[], etiqueta_correios[]
// - Função JS preenche esses arrays antes do submit sem alterar o comportamento existente
// - Backend usa esses arrays para montar $mapaLacresPorPosto e gravar em ciDespachoLotes
// - Mantém validações, SPLIT e foco automático inalterados
// v8.9: Lacres/etiqueta por regional - aplica a TODOS os lotes da regional
// - Estende JS para capturar regional_lacres[] alinhado com postos
// - Backend monta $mapaLacresPorRegional além de $mapaLacresPorPosto
// - No INSERT: prioridade 1º lacre por posto, 2º lacre por regional, 3º defaults
// - Todos os lotes de uma regional recebem os mesmos lacres/etiqueta (a menos que o posto tenha lacre específico)
// v8.10: Corrige salvamento de lacres (IIPR/Correios) por regional
// - Garante captura correta dos valores de lacre_iipr e lacre_correios no POST
// - Normaliza regional para formato consistente (remove zeros à esquerda)
// - Adiciona debug detalhado por lote para diagnosticar problemas
// - Valida que mapaLacresPorRegional é preenchido corretamente e usado no INSERT
// v8.11: Preserva inputs de lacres/etiquetas ao excluir postos e ao filtrar por data (compatível com PHP 5.3)
// - Implementa localStorage para persistir etiquetas/lacres por (id_despacho, regional, posto)
// - Funções JS: salvarEstadoEtiquetasCorreios(), restaurarEstadoEtiquetasCorreios()
// - Chamadas: antes de excluir posto, antes de aplicar filtro de data, ao carregar página
// - Garante que nenhum dado digitado seja perdido ao usar filtros ou remover linhas
// v8.11.1: Confirmação de gravação, modo sobrescrever/novo ofício, destaque visual de splits na CENTRAL IIPR
// - Adiciona confirmacao antes de gravar o ofício + escolha sobrescrever/novo
// - Campo hidden `modo_oficio` no form de Correios
// - Destaque leve das linhas abaixo do split (.split-central-grupo1/2/...)
// - Confirmação ao limpar sessão e reset parcial da CENTRAL IIPR ao limpar coluna X
// - Compatível com PHP 5.3 / ES5 (Yii 1.x)
// v8.12.3: Correção crítica - CENTRAL IIPR salva APENAS postos visíveis no grid (não todos os postos das datas)
// - JS prepararLacresCorreiosParaSubmit agora envia grupo_lacres[] para identificar CAPITAL/CENTRAL/REGIONAIS
// - Handler PHP separa postos por grupo real: $mapaCapital (CAPITAL), $mapaCentral (CENTRAL IIPR), $mapaLacresPorRegional (REGIONAIS)
// - Loop de gravação aplica lacres SOMENTE aos postos presentes nos mapas corretos:
//   * CAPITAL: salva apenas postos visíveis em $mapaCapital
//   * CENTRAL IIPR: salva apenas postos visíveis em $mapaCentral
//   * REGIONAIS: salva todos os postos das regionais visíveis em $mapaLacresPorRegional
// - Corrige comportamento onde CENTRAL salvava todos os postos das datas (016, 029, 042, etc.) mesmo quando apenas posto 086 estava visível
// v8.12.3-fix: Preservação de dados após salvar
// - Removido window.location.href após salvar com sucesso (impedia que inputs permanecessem preenchidos)
// - Inputs de Lacre IIPR, Lacre Correios e Etiqueta Correios agora permanecem na tela após "Gravar e Imprimir"
// - localStorage continua preservando etiquetas entre operações (salvar/filtrar/excluir)
// - Limpeza dos inputs ocorre APENAS via "Limpar Sessão" ou botão X específico de cada coluna
// - CENTRAL IIPR confirmado: grava SOMENTE postos que estão visíveis na grade (usa $mapaCentral filtrado por grupo)
// v8.12.3-fix2: Correção definitiva dos 3 problemas restantes
// - CENTRAL IIPR: Confirmado que JÁ grava apenas postos visíveis (lógica correta desde v8.12.3)
// - LACRES IIPR/CORREIOS: Salvamento em $_SESSION['lacres_personalizados'] após sucesso para preservar valores digitados
// - PRESERVAÇÃO: Valores restaurados via sessão ao recarregar, sem recalcular (exceto quando recalculo_por_lacre=1)
// - BUG CORRIGIDO: Regionais usavam mesmo valor para etiquetaiipr e etiquetacorreios (faltava incremento)
// v8.13: Refatoração estrutural - Snapshot da grade como fonte única de verdade
// - Frontend: prepararLacresCorreiosParaSubmit() agora cria snapshot_oficio (JSON) com estado exato da grade
// - Backend: salvar_oficio_correios usa EXCLUSIVAMENTE o snapshot para gravar (sem recálculos)
// - CAPITAL/CENTRAL: grava SOMENTE postos visíveis no snapshot (filtro rigoroso)
// - REGIONAIS: expande para todos os postos da regional, mas usa lacres/etiqueta do snapshot
// - Preservação total: após salvar, inputs permanecem com valores originais (sem recalcular nem zerar)
// - Compatibilidade: PHP 5.3.3 (Yii 1.x) + JavaScript ES5 (sem let/const/arrow functions)
// v8.13.1: Correções finais de consistência
// - CENTRAL IIPR: Confirmado uso exclusivo do snapshot (mesmo comportamento de CAPITAL - sem expansão automática)
// - Lacres IIPR/Correios: Garantido uso dos valores EXATOS dos inputs (sem sobrescrever com cálculos)
// - Preservação: Inputs permanecem preenchidos após salvar (valores salvos em $_SESSION['lacres_personalizados'])
// - Validação: etiquetaiipr ≠ etiquetacorreios quando digitados diferentes, CENTRAL grava apenas postos visíveis
// v8.13.2: Restauração da lógica ORIGINAL de atribuição automática de lacres
// - CAPITAL: Lacres em pares incrementais (+2) → lacre_iipr=N, lacre_correios=N+1, próximo=N+2
// - CENTRAL IIPR: Lacres IIPR sequenciais (+1), lacre Correios = ÚLTIMO lacre IIPR gerado (aplicado a todos)
// - REGIONAIS: Par de lacres por regional aplicado a todos os postos daquela regional
// - Snapshot mantido: Ao salvar, grava EXATAMENTE o que está na tela (sem recálculos no handler)
// - Compatibilidade: PHP 5.3.3 + ES5, sem quebrar funcionalidades existentes
// v8.13.3: Correções críticas de atribuição e gravação de lacres
// - CENTRAL IIPR: Lacre Correios = ÚLTIMO lacre IIPR + 1 (não o último puro) aplicado a TODAS as linhas do grupo
// - Lógica por grupo visual: respeita SPLITs, cada grupo tem lacreCorreios = max(lacreIIPR_grupo) + 1
// - REGIONAIS: Garante lacre_iipr ≠ lacre_correios (usa valores EXATOS dos inputs, não reutiliza)
// - Gravação no banco: etiquetacorreios sempre usa lacre_correios (nunca lacre_iipr duplicado)
// - Debug: Adiciona debug_lacres=1 para inspecionar mapas antes de gravar
// - Snapshot: Mantido como fonte única de verdade, corrigida apenas a lógica de cálculo inicial
// v8.13.4: Correções finais de usabilidade e fidelidade ao snapshot
// - Inputs zerados por padrão: não preenche com valor 1 automático, usuário digita lacres iniciais
// - Lacres NUNCA duplicados: validação rigorosa IIPR≠Correios em todos os grupos (exceto Correios da Central entre si)
// - Preservação total ao excluir: mantém TODOS os inputs (lacres + etiquetas) ao remover linha
// - Snapshot 100% fiel: CENTRAL IIPR salva APENAS postos visíveis na tela (não todos os postos das datas)
// - Impressão fiel: o que você vê na tela é EXATAMENTE o que será impresso e salvo no banco
// ==================================================================================
// v8.14.3: Confirmação 3 opções para Poupa Tempo + mantém funcionalidade Correios
// ==================================================================================
// - NOVO: Modal de confirmação no modelo_oficio_poupa_tempo.php (Sobrescrever/Criar Novo/Cancelar)
// - NOVO: Handler PT com modo_oficio para sobrescrever ou criar novo
// - MANTIDO: Toda funcionalidade v8.14.2 para Correios (redirect + auto-impressão)
// - MANTIDO: Impressão correta com valores do BD para Correios
// - Compatibilidade total entre PT e Correios

// Conexões com os bancos de dados
$pdo_controle = new PDO("mysql:host=10.15.61.169;dbname=controle;charset=utf8mb4", "controle_mat", "375256");
$pdo_controle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo_servico = new PDO("mysql:host=10.15.61.169;dbname=servico;charset=utf8mb4", "controle_mat", "375256");
$pdo_servico->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo_contrsos = new PDO("mysql:host=10.15.61.169;dbname=contrsos;charset=utf8mb4", "controle_mat", "375256");
$pdo_contrsos->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (!isset($_SESSION)) session_start();
if (!isset($_SESSION['etiquetas'])) $_SESSION['etiquetas'] = array();
if (!isset($_SESSION['linhas_removidas'])) $_SESSION['linhas_removidas'] = array();
if (!isset($_SESSION['lacres_personalizados'])) $_SESSION['lacres_personalizados'] = array();
if (!isset($_SESSION['postos_manuais'])) $_SESSION['postos_manuais'] = array();
if (!isset($_SESSION['postos_cadastrados'])) $_SESSION['postos_cadastrados'] = array();
if (!isset($_SESSION['datas_filtro'])) $_SESSION['datas_filtro'] = array();
if (!isset($_SESSION['debug_log'])) $_SESSION['debug_log'] = array();
if (!isset($_SESSION['excluir_regionais_manual'])) $_SESSION['excluir_regionais_manual'] = array();

if (!isset($_SESSION['id_despacho_poupa_tempo'])) $_SESSION['id_despacho_poupa_tempo'] = 0;

// === vX: SALVAR LACRES DO POUPA TEMPO ===================================
if (isset($_POST['acao']) && $_POST['acao'] === 'salvar_lacres_pt') {
    try {
        if (!isset($pdo_controle) || !($pdo_controle instanceof PDO)) {
            throw new Exception('PDO $pdo_controle não disponível.');
        }

        // 1) Descobrir o id do despacho do Poupa Tempo
        //    - primeiro tenta vir por POST
        //    - se não vier, pega o último despacho ativo do grupo 'POUPA TEMPO'
        //      para o usuário logado
        $id_despacho = isset($_POST['id_despacho']) ? (int)$_POST['id_despacho'] : 0;

        if ($id_despacho <= 0) {
            $usuario = isset($_SESSION['usuario']) ? $_SESSION['usuario'] : 'conferencia';

            $sqlBuscaDesp = "
                SELECT id
                  FROM ciDespachos
                 WHERE grupo   = 'POUPA TEMPO'
                   AND usuario = :usuario
                   AND ativo   = 's'
              ORDER BY id DESC
                 LIMIT 1
            ";
            $stmBuscaDesp = $pdo_controle->prepare($sqlBuscaDesp);
            $stmBuscaDesp->execute(array(':usuario' => $usuario));
            $idEncontrado = $stmBuscaDesp->fetchColumn();

            if ($idEncontrado) {
                $id_despacho = (int)$idEncontrado;
            }
        }

        if ($id_despacho <= 0) {
            throw new Exception('Não foi possível localizar o despacho do Poupa Tempo. Salve o ofício Poupa Tempo antes de lançar os lacres.');
        }

        // 2) Capturar apenas lacres IIPR (Poupa Tempo não tem lacre dos Correios)
        //    Espera algo do tipo: lacre_iipr[028] = '123456', lacre_iipr[029] = '123457', ...
        $lacres_iipr = (isset($_POST['lacre_iipr']) && is_array($_POST['lacre_iipr']))
            ? $_POST['lacre_iipr']
            : array();

        if (empty($lacres_iipr)) {
            throw new Exception('Nenhum lacre IIPR foi informado para o Poupa Tempo.');
        }

        $pdo_controle->beginTransaction();

        

        $sqlSel = "
            SELECT COUNT(*)
              FROM ciDespachoItens
             WHERE idDespacho = :id_despacho
               AND codigoPosto = :posto
        ";
        $stmSel = $pdo_controle->prepare($sqlSel);

        $sqlIns = "
            INSERT INTO ciDespachoItens (idDespacho, codigoPosto, lacre_iipr, dataRegistro)
            VALUES (:id_despacho, :posto, :lacre, NOW())
        ";
        $stmIns = $pdo_controle->prepare($sqlIns);

        $sqlUpd = "
            UPDATE ciDespachoItens
               SET lacre_iipr     = :lacre,
                   dataAtualizacao = NOW()
             WHERE idDespacho = :id_despacho
               AND codigoPosto = :posto
        ";
        $stmUpd = $pdo_controle->prepare($sqlUpd);

        $totalInseridos   = 0;
        $totalAtualizados = 0;

        foreach ($lacres_iipr as $posto => $valorLacre) {
            $valorLacre = trim($valorLacre);
            if ($valorLacre === '') {
                continue;
            }

            $stmSel->execute(array(
                ':id_despacho' => $id_despacho,
                ':posto'       => $posto
            ));
            $existe = (int)$stmSel->fetchColumn();

            if ($existe) {
                $stmUpd->execute(array(
                    ':lacre'       => $valorLacre,
                    ':id_despacho' => $id_despacho,
                    ':posto'       => $posto
                ));
                $totalAtualizados += $stmUpd->rowCount();
            } else {
                $stmIns->execute(array(
                    ':id_despacho' => $id_despacho,
                    ':posto'       => $posto,
                    ':lacre'       => $valorLacre
                ));
                $totalInseridos += $stmIns->rowCount();
            }
        }

        $pdo_controle->commit();

        echo "<script>alert('Lacres do Poupa Tempo salvos com sucesso. Inseridos: "
             . (int)$totalInseridos . ", atualizados: " . (int)$totalAtualizados . "');</script>";

    } catch (Exception $e) {
        if (isset($pdo_controle) && $pdo_controle instanceof PDO && $pdo_controle->inTransaction()) {
            $pdo_controle->rollBack();
        }
        echo "<script>alert('Erro ao salvar lacres do Poupa Tempo: "
             . addslashes($e->getMessage()) . "');</script>";
    }
}



// === v1: SALVAR OFÍCIO DO POUPA TEMPO (com detalhe de lotes, data e responsáveis) ===
if (isset($_POST['acao']) && $_POST['acao'] === 'salvar_oficio_pt') {
    try {
        if (!isset($pdo_controle) || !($pdo_controle instanceof PDO)) {
            throw new Exception('PDO $pdo_controle não disponível.');
        }

        $pdo_controle->beginTransaction();

        // 1) Coleta das datas - pode vir como pt_datas ou datas_str
        $datasStr = '';
        $datasRaw = array();

        if (isset($_POST['pt_datas']) && trim($_POST['pt_datas']) !== '') {
            $datasStr = trim($_POST['pt_datas']);
        } elseif (isset($_POST['datas_str']) && trim($_POST['datas_str']) !== '') {
            $datasStr = trim($_POST['datas_str']);
        }

        if ($datasStr !== '') {
            $tmp = explode(',', $datasStr);
            foreach ($tmp as $d) {
                $d = trim($d);
                if ($d !== '') {
                    $datasRaw[] = $d;
                }
            }
        }

        if (empty($datasRaw)) {
            throw new Exception('Nenhuma data válida informada para o Poupa Tempo.');
        }

        // Normaliza as datas para formato YYYY-MM-DD
        $datasSql = array();
        foreach ($datasRaw as $d) {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) {
                // já está em YYYY-MM-DD
                $datasSql[] = $d;
            } elseif (preg_match('/^(\d{2})[\/-](\d{2})[\/-](\d{4})$/', $d, $m)) {
                // vem como DD/MM/AAAA ou DD-MM-AAAA
                $datasSql[] = $m[3] . '-' . $m[2] . '-' . $m[1];
            } else {
                // fallback
                $datasSql[] = $d;
            }
        }

        // 2) Cabeçalho em ciDespachos (UPSERT pelo hash de grupo+datas)
        $grupo   = 'POUPA TEMPO';
        $usuario = isset($_SESSION['usuario']) ? $_SESSION['usuario'] : 'conferencia';
        $hash    = sha1($grupo . '|' . $datasStr);

        $stFind = $pdo_controle->prepare("SELECT id FROM ciDespachos WHERE hash_chave=? LIMIT 1");
        $stFind->execute(array($hash));
        $id_desp = $stFind->fetchColumn();

        if ($id_desp) {
            // Atualiza cabeçalho existente
            $stUpd = $pdo_controle->prepare("
                UPDATE ciDespachos
                   SET usuario   = ?,
                       grupo     = ?,
                       datas_str = ?,
                       ativo     = 1,
                       obs       = NULL
                 WHERE id = ?
            ");
            $stUpd->execute(array($usuario, $grupo, $datasStr, $id_desp));

            // Limpa itens antigos
            $stDel = $pdo_controle->prepare("DELETE FROM ciDespachoItens WHERE id_despacho=?");
            $stDel->execute(array($id_desp));

            // Limpa detalhe de lotes antigo
            $stDelL = $pdo_controle->prepare("DELETE FROM ciDespachoLotes WHERE id_despacho=?");
            $stDelL->execute(array($id_desp));
        } else {
            // Cria novo cabeçalho
            $st1 = $pdo_controle->prepare("
                INSERT INTO ciDespachos (usuario, grupo, datas_str, hash_chave, ativo, obs)
                VALUES (?,?,?,?,1,?)
            ");
            $st1->execute(array($usuario, $grupo, $datasStr, $hash, null));
            $id_desp = $pdo_controle->lastInsertId();
        }

        // 3) SELECT principal: SOMA por posto (igual ao modelo do ofício)
        $placeholders = implode(',', array_fill(0, count($datasSql), '?'));

        $sqlItens = "
            SELECT 
                LPAD(c.posto,3,'0') AS codigo,
                COALESCE(r.nome, CONCAT('POUPA TEMPO - ', LPAD(c.posto,3,'0'))) AS nome,
                SUM(COALESCE(c.quantidade,0)) AS quantidade,
                r.endereco AS endereco,
                r.regional AS regional
            FROM ciPostosCsv c
            INNER JOIN ciRegionais r 
                    ON LPAD(r.posto,3,'0') = LPAD(c.posto,3,'0')
            WHERE DATE(c.dataCarga) IN ($placeholders)
              AND REPLACE(LOWER(r.entrega),' ','') LIKE 'poupa%tempo'
            GROUP BY 
                LPAD(c.posto,3,'0'), r.nome, r.endereco, r.regional
            ORDER BY 
                LPAD(c.posto,3,'0')
        ";

        $stmtItens = $pdo_controle->prepare($sqlItens);
        $stmtItens->execute($datasSql);

        $rows = $stmtItens->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
            throw new Exception('Nenhum posto Poupa Tempo encontrado para as datas selecionadas.');
        }

        // 4) Insere os itens do despacho (1 linha por posto)
        $stItem = $pdo_controle->prepare("
            INSERT INTO ciDespachoItens
            (id_despacho, regional, posto, nome_posto, endereco, lote, quantidade,
             lacre_iipr, lacre_correios, etiqueta_correios, incluir)
            VALUES (?,?,?,?,?,?,?,?,?,?,1)
        ");

        foreach ($rows as $r) {
            $posto      = (string)$r['codigo'];       // 3 dígitos
            $nome_posto = (string)$r['nome'];
            $qtd_total  = (int)$r['quantidade'];
            $endereco   = trim((string)$r['endereco']);
            $regional   = $r['regional'];

            $lacre_iipr    = null;
            $lacre_corr    = null;
            $etiqueta_corr = null;

            $stItem->execute(array(
                $id_desp,
                $regional,
                $posto,
                $nome_posto,
                $endereco,
                null,          // lote (só no detalhe)
                $qtd_total,
                $lacre_iipr,
                $lacre_corr,
                $etiqueta_corr
            ));
        }

        // 5) Detalhe por LOTE: quantidade, data de carga e responsáveis
        // IMPORTANTE: troque c.usuario pelo nome REAL da coluna onde fica o responsável
        // (ex.: c.responsavel, c.emitente, c.usuarioCriacao, etc.)
        $sqlLotes = "
            SELECT 
                LPAD(c.posto,3,'0') AS posto,
                c.lote,
                SUM(COALESCE(c.quantidade,0)) AS quantidade,
                MIN(DATE(c.dataCarga)) AS data_carga,
                GROUP_CONCAT(DISTINCT c.usuario SEPARATOR ', ') AS responsaveis
            FROM ciPostosCsv c
            INNER JOIN ciRegionais r 
                    ON LPAD(r.posto,3,'0') = LPAD(c.posto,3,'0')
            WHERE DATE(c.dataCarga) IN ($placeholders)
              AND REPLACE(LOWER(r.entrega),' ','') LIKE 'poupa%tempo'
            GROUP BY 
                LPAD(c.posto,3,'0'), c.lote
            ORDER BY 
                LPAD(c.posto,3,'0'), c.lote
        ";

        $stmtLotes = $pdo_controle->prepare($sqlLotes);
        $stmtLotes->execute($datasSql);

        $stInsLote = $pdo_controle->prepare("
            INSERT INTO ciDespachoLotes (id_despacho, posto, lote, quantidade, data_carga, responsaveis)
            VALUES (?,?,?,?,?,?)
        ");

        while ($l = $stmtLotes->fetch(PDO::FETCH_ASSOC)) {
            $stInsLote->execute(array(
                $id_desp,
                (string)$l['posto'],
                (string)$l['lote'],
                (int)$l['quantidade'],
                $l['data_carga'],          // YYYY-MM-DD
                $l['responsaveis']         // nomes concatenados
            ));
        }

        // 6) Finaliza
        $pdo_controle->commit();
        echo "<script>
                alert('Ofício (Poupa Tempo) salvo. Nº " . (int)$id_desp . "');
                if (typeof marcarComoSalvo === 'function') { marcarComoSalvo(); }
                window.location.href='" . $_SERVER['PHP_SELF'] . "';
              </script>";
    } catch (Exception $e) {
        if ($pdo_controle->inTransaction()) {
            $pdo_controle->rollBack();
        }
        echo "<pre>Erro ao salvar ofício: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</pre>";
    }
    exit;
}


// === SALVAR OFÍCIO DOS CORREIOS (postos com entrega = 'correios') ===
// Salva todos os postos CAPITAL, CENTRAL IIPR e REGIONAIS com lacres e etiquetas
if (isset($_POST['acao']) && $_POST['acao'] === 'salvar_oficio_correios') {
    try {
        // v8.13.3: Debug detalhado opcional via debug_lacres=1
        $debug_lacres = (isset($_GET['debug_lacres']) && $_GET['debug_lacres'] === '1') || (isset($_POST['debug_lacres']) && $_POST['debug_lacres'] === '1');
        
        // Debug: registrar que o handler foi invocado e quais dados chegaram via POST
        try { add_debug('V8.11.X - salvar_oficio_correios chamado', $_POST); } catch (Exception $e) { /* ignore */ }
        if (!isset($pdo_controle) || !($pdo_controle instanceof PDO)) {
            throw new Exception('PDO $pdo_controle nao disponivel.');
        }

        $pdo_controle->beginTransaction();

        // v8.11.1: modo de ofício (sobrescrever / novo)
        $modoOficio = '';
        if (isset($_POST['modo_oficio'])) {
            $modoOficio = $_POST['modo_oficio'];
        }

        // Recuperar ultimo ofício Correios (se houver)
        $ultimoIdDespachoCorreios = null;
        $stUlt = $pdo_controle->prepare("SELECT id FROM ciDespachos WHERE grupo = 'CORREIOS' ORDER BY id DESC LIMIT 1");
        $stUlt->execute();
        $rowUlt = $stUlt->fetch(PDO::FETCH_ASSOC);
        if ($rowUlt && isset($rowUlt['id'])) {
            $ultimoIdDespachoCorreios = (int)$rowUlt['id'];
        }

        // Se modo sobrescrever, apagar lotes vinculados ao ultimo ofício antes de gravar
        if ($modoOficio === 'sobrescrever' && $ultimoIdDespachoCorreios !== null) {
            $stDelOld = $pdo_controle->prepare("DELETE FROM ciDespachoLotes WHERE id_despacho = ?");
            $stDelOld->execute(array($ultimoIdDespachoCorreios));
        }

        // 1) Coleta das datas
        $datasStr = '';
        $datasRaw = array();

        if (isset($_POST['correios_datas']) && trim($_POST['correios_datas']) !== '') {
            $datasStr = trim($_POST['correios_datas']);
        } elseif (isset($_POST['datas_str']) && trim($_POST['datas_str']) !== '') {
            $datasStr = trim($_POST['datas_str']);
        }

        if ($datasStr !== '') {
            $tmp = explode(',', $datasStr);
            foreach ($tmp as $d) {
                $d = trim($d);
                if ($d !== '') {
                    $datasRaw[] = $d;
                }
            }
        }

        if (empty($datasRaw)) {
            throw new Exception('Nenhuma data valida informada para o oficio Correios.');
        }

        // Normaliza as datas para formato YYYY-MM-DD
        $datasSql = array();
        foreach ($datasRaw as $d) {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) {
                $datasSql[] = $d;
            } elseif (preg_match('/^(\d{2})[\/-](\d{2})[\/-](\d{4})$/', $d, $m)) {
                $datasSql[] = $m[3] . '-' . $m[2] . '-' . $m[1];
            } else {
                $datasSql[] = $d;
            }
        }

        // 2) Cabeçalho em ciDespachos (UPSERT pelo hash de grupo+datas)
        $grupo   = 'CORREIOS';
        $usuario = isset($_SESSION['usuario']) ? $_SESSION['usuario'] : 'conferencia';
        $hash    = sha1($grupo . '|' . $datasStr);

        $stFind = $pdo_controle->prepare("SELECT id FROM ciDespachos WHERE hash_chave=? LIMIT 1");
        $stFind->execute(array($hash));
        $id_desp = $stFind->fetchColumn();

        if ($id_desp) {
            // Atualiza cabeçalho existente
            $stUpd = $pdo_controle->prepare("
                UPDATE ciDespachos
                   SET usuario   = ?,
                       grupo     = ?,
                       datas_str = ?,
                       ativo     = 1,
                       obs       = NULL
                 WHERE id = ?
            ");
            $stUpd->execute(array($usuario, $grupo, $datasStr, $id_desp));

            // Limpa itens antigos
            $stDel = $pdo_controle->prepare("DELETE FROM ciDespachoItens WHERE id_despacho=?");
            $stDel->execute(array($id_desp));

            // Limpa detalhe de lotes antigo
            $stDelL = $pdo_controle->prepare("DELETE FROM ciDespachoLotes WHERE id_despacho=?");
            $stDelL->execute(array($id_desp));
        } else {
            // Cria novo cabeçalho
            $st1 = $pdo_controle->prepare("
                INSERT INTO ciDespachos (usuario, grupo, datas_str, hash_chave, ativo, obs)
                VALUES (?,?,?,?,1,?)
            ");
            $st1->execute(array($usuario, $grupo, $datasStr, $hash, null));
            $id_desp = $pdo_controle->lastInsertId();
        }

        // 3) Captura os dados enviados pelo formulário
        // VERSAO 3: Normaliza TODAS as chaves para formato "041" (3 digitos com zeros)
        // Isso garante consistencia entre todos os arrays, independente do formato original
        
        // Funcao auxiliar para normalizar chaves de array
        function normalizarChavesPosto($array) {
            $resultado = array();
            if (!is_array($array)) return $resultado;
            foreach ($array as $chave => $valor) {
                // Remove prefixo "p_" se existir
                $chave_str = (string)$chave;
                if (strpos($chave_str, 'p_') === 0) {
                    $chave_str = substr($chave_str, 2);
                }
                // Remove caracteres nao-numericos e adiciona padding
                $chave_normalizada = str_pad(preg_replace('/\D+/', '', $chave_str), 3, '0', STR_PAD_LEFT);
                $resultado[$chave_normalizada] = $valor;
            }
            return $resultado;
        }
        
        $lacres_iipr_raw = isset($_POST['lacre_iipr']) && is_array($_POST['lacre_iipr']) ? $_POST['lacre_iipr'] : array();
        $lacres_correios_raw = isset($_POST['lacre_correios']) && is_array($_POST['lacre_correios']) ? $_POST['lacre_correios'] : array();
        $etiquetas_raw = isset($_POST['etiqueta_correios']) && is_array($_POST['etiqueta_correios']) ? $_POST['etiqueta_correios'] : array();
        $nomes_postos_raw = isset($_POST['nome_posto']) && is_array($_POST['nome_posto']) ? $_POST['nome_posto'] : array();
        $grupos_postos_raw = isset($_POST['grupo_posto']) && is_array($_POST['grupo_posto']) ? $_POST['grupo_posto'] : array();
        
        // Normalizar TODAS as chaves
        $lacres_iipr = normalizarChavesPosto($lacres_iipr_raw);
        $lacres_correios = normalizarChavesPosto($lacres_correios_raw);
        $etiquetas = normalizarChavesPosto($etiquetas_raw);
        $nomes_postos = normalizarChavesPosto($nomes_postos_raw);
        $grupos_postos = normalizarChavesPosto($grupos_postos_raw);

        // 4) Buscar dados complementares do banco (regional, endereco, quantidade)
        $placeholders = implode(',', array_fill(0, count($datasSql), '?'));

        // Query para buscar quantidade total por posto (apenas Correios)
        $sqlQtd = "
            SELECT 
                LPAD(c.posto,3,'0') AS codigo,
                COALESCE(r.nome, CONCAT('Posto ', LPAD(c.posto,3,'0'))) AS nome,
                SUM(COALESCE(c.quantidade,0)) AS quantidade,
                r.endereco AS endereco,
                r.regional AS regional
            FROM ciPostosCsv c
            INNER JOIN ciRegionais r 
                    ON LPAD(r.posto,3,'0') = LPAD(c.posto,3,'0')
            WHERE DATE(c.dataCarga) IN ($placeholders)
              AND LOWER(TRIM(r.entrega)) = 'correios'
            GROUP BY 
                LPAD(c.posto,3,'0'), r.nome, r.endereco, r.regional
            ORDER BY 
                LPAD(c.posto,3,'0')
        ";

        $stmtQtd = $pdo_controle->prepare($sqlQtd);
        $stmtQtd->execute($datasSql);

        $dadosBanco = array();
        while ($row = $stmtQtd->fetch(PDO::FETCH_ASSOC)) {
            $dadosBanco[$row['codigo']] = $row;
        }

        // v8.12: Para o fluxo CORREIOS, não gravamos em ciDespachoItens
        // ciDespachoItens será usado apenas para Poupa Tempo
        // $stItem foi removido (não necessário para Correios)
        // O trecho anterior que preparava INSERT em ciDespachoItens foi comentado/removido

        // 6) Salvar lotes por posto (todos os lotes das datas selecionadas, apenas Correios)
        $sqlLotes = "
            SELECT 
                LPAD(c.posto,3,'0') AS posto,
                c.lote,
                SUM(COALESCE(c.quantidade,0)) AS quantidade,
                MIN(DATE(c.dataCarga)) AS data_carga,
                GROUP_CONCAT(DISTINCT c.usuario SEPARATOR ', ') AS responsaveis,
                r.regional AS regional
            FROM ciPostosCsv c
            INNER JOIN ciRegionais r 
                    ON LPAD(r.posto,3,'0') = LPAD(c.posto,3,'0')
            WHERE DATE(c.dataCarga) IN ($placeholders)
              AND LOWER(TRIM(r.entrega)) = 'correios'
            GROUP BY 
                LPAD(c.posto,3,'0'), c.lote
            ORDER BY 
                LPAD(c.posto,3,'0'), c.lote
        ";

        $stmtLotes = $pdo_controle->prepare($sqlLotes);
        $stmtLotes->execute($datasSql);

        // v8.13: USAR SNAPSHOT JSON COMO FONTE ÚNICA DE VERDADE
        // O snapshot contém o estado exato da grade no momento do salvamento
        $snapshot = array();
        if (isset($_POST['snapshot_oficio']) && $_POST['snapshot_oficio'] !== '') {
            $tmp = json_decode($_POST['snapshot_oficio'], true);
            if (is_array($tmp)) {
                $snapshot = $tmp;
            }
        }

        // v8.13: Montar mapas a partir do SNAPSHOT (prioridade máxima)
        $mapaCapital = array();   // posto => lacres/etiqueta
        $mapaCentral = array();   // posto => lacres/etiqueta
        $mapaRegional = array();  // regional => lacres/etiqueta

        if (!empty($snapshot)) {
            // v8.13: Processar snapshot (fonte única de verdade)
            foreach ($snapshot as $linha) {
                $posto = isset($linha['posto']) ? str_pad(preg_replace('/\D+/', '', (string)$linha['posto']), 3, '0', STR_PAD_LEFT) : '';
                $grupo = isset($linha['grupo']) ? trim((string)$linha['grupo']) : '';
                $regional_raw = isset($linha['regional']) ? trim((string)$linha['regional']) : '0';
                $regional = ltrim($regional_raw, '0');
                if ($regional === '') $regional = '0';

                $lacreI = isset($linha['lacre_iipr']) ? trim((string)$linha['lacre_iipr']) : '';
                $lacreC = isset($linha['lacre_correios']) ? trim((string)$linha['lacre_correios']) : '';
                $etiq = isset($linha['etiqueta_correios']) ? trim((string)$linha['etiqueta_correios']) : '';

                $dados = array(
                    'lacre_iipr' => ($lacreI === '' ? 0 : (int)$lacreI),
                    'lacre_correios' => ($lacreC === '' ? 0 : (int)$lacreC),
                    'etiqueta_correios' => ($etiq === '' ? null : $etiq),
                );

                if ($grupo === 'CAPITAL') {
                    $mapaCapital[$posto] = $dados;
                } elseif ($grupo === 'CENTRAL IIPR') {
                    $mapaCentral[$posto] = $dados;
                } elseif ($grupo === 'REGIONAIS' && $regional !== '0') {
                    $mapaRegional[$regional] = $dados;
                }
            }

            add_debug('V8.13.1 - SNAPSHOT recebido e processado', array(
                'total_linhas' => count($snapshot),
                'capital' => array_keys($mapaCapital),
                'capital_lacres' => $mapaCapital,
                'central' => array_keys($mapaCentral),
                'central_lacres' => $mapaCentral,
                'regionais' => array_keys($mapaRegional),
                'regionais_lacres' => $mapaRegional,
            ));
            
            // v8.13.3: Debug detalhado de lacres quando debug_lacres=1
            if ($debug_lacres) {
                echo "<pre style='background:#f0f0f0;padding:20px;border:2px solid #333;margin:20px;'>";
                echo "<h3>DEBUG v8.13.3 - MAPAS DE LACRES ANTES DE GRAVAR</h3>\n\n";
                echo "<h4>CAPITAL (" . count($mapaCapital) . " postos):</h4>\n";
                foreach ($mapaCapital as $posto => $dados) {
                    echo "Posto $posto: IIPR=" . $dados['lacre_iipr'] . ", Correios=" . $dados['lacre_correios'] . ", Etiqueta=" . ($dados['etiqueta_correios'] ? substr($dados['etiqueta_correios'], 0, 10) . '...' : 'NULL') . "\n";
                }
                echo "\n<h4>CENTRAL IIPR (" . count($mapaCentral) . " postos):</h4>\n";
                foreach ($mapaCentral as $posto => $dados) {
                    echo "Posto $posto: IIPR=" . $dados['lacre_iipr'] . ", Correios=" . $dados['lacre_correios'] . ", Etiqueta=" . ($dados['etiqueta_correios'] ? substr($dados['etiqueta_correios'], 0, 10) . '...' : 'NULL') . "\n";
                }
                echo "\n<h4>REGIONAIS (" . count($mapaRegional) . " regionais):</h4>\n";
                foreach ($mapaRegional as $regional => $dados) {
                    echo "Regional $regional: IIPR=" . $dados['lacre_iipr'] . ", Correios=" . $dados['lacre_correios'] . ", Etiqueta=" . ($dados['etiqueta_correios'] ? substr($dados['etiqueta_correios'], 0, 10) . '...' : 'NULL') . "\n";
                }
                echo "\n<small>Para desativar este debug, remova ?debug_lacres=1 da URL</small>";
                echo "</pre>";
            }
        } else {
            // v8.13.1: FALLBACK para arrays antigos (se snapshot não existir)
            add_debug('V8.13.1 - FALLBACK: snapshot vazio, usando arrays antigos');
            $mapaLacresPorPosto = array();

        // Se o formulário forneceu arrays alinhados, usá-los (prioridade)
        $postosLacres_post = isset($_POST['posto_lacres']) && is_array($_POST['posto_lacres']) ? $_POST['posto_lacres'] : array();
        if (!empty($postosLacres_post)) {
            $lacresIIPR_post = isset($_POST['lacre_iipr']) && is_array($_POST['lacre_iipr']) ? $_POST['lacre_iipr'] : array();
            $lacresCorreios_post = isset($_POST['lacre_correios']) && is_array($_POST['lacre_correios']) ? $_POST['lacre_correios'] : array();
            $etiquetasCorreios_post = isset($_POST['etiqueta_correios']) && is_array($_POST['etiqueta_correios']) ? $_POST['etiqueta_correios'] : array();
            $gruposLacres_post = isset($_POST['grupo_lacres']) && is_array($_POST['grupo_lacres']) ? $_POST['grupo_lacres'] : array();

            foreach ($postosLacres_post as $idx => $postoRaw) {
                $postoCodigo = str_pad(preg_replace('/\D+/', '', (string)$postoRaw), 3, '0', STR_PAD_LEFT);
                if ($postoCodigo === '') continue;
                $lacreI = isset($lacresIIPR_post[$idx]) ? trim((string)$lacresIIPR_post[$idx]) : '';
                $lacreC = isset($lacresCorreios_post[$idx]) ? trim((string)$lacresCorreios_post[$idx]) : '';
                $eti = isset($etiquetasCorreios_post[$idx]) ? trim((string)$etiquetasCorreios_post[$idx]) : '';
                $grupo = isset($gruposLacres_post[$idx]) ? trim((string)$gruposLacres_post[$idx]) : '';

                $mapaLacresPorPosto[$postoCodigo] = array(
                    'lacre_iipr' => ($lacreI === '' ? 0 : (int)$lacreI),
                    'lacre_correios' => ($lacreC === '' ? 0 : (int)$lacreC),
                    'etiqueta_correios' => ($eti === '' ? null : $eti),
                    'grupo' => $grupo,
                );
            }
        } else {
            // Fallback: usar arrays nomeados (associativos) já normalizados anteriormente
            $todosOsPostos = array_unique(
                array_merge(
                    array_keys($lacres_iipr),
                    array_keys($lacres_correios),
                    array_keys($etiquetas)
                )
            );

            foreach ($todosOsPostos as $postoCodigo) {
                $postoCodigo = (string)$postoCodigo;
                $lacreIIPR = isset($lacres_iipr[$postoCodigo]) ? trim((string)$lacres_iipr[$postoCodigo]) : '';
                $lacreCorreios = isset($lacres_correios[$postoCodigo]) ? trim((string)$lacres_correios[$postoCodigo]) : '';
                $etiquetaCorr = isset($etiquetas[$postoCodigo]) ? trim((string)$etiquetas[$postoCodigo]) : '';

                $mapaLacresPorPosto[$postoCodigo] = array(
                    'lacre_iipr' => ($lacreIIPR === '' ? 0 : (int)$lacreIIPR),
                    'lacre_correios' => ($lacreCorreios === '' ? 0 : (int)$lacreCorreios),
                    'etiqueta_correios' => $etiquetaCorr !== '' ? $etiquetaCorr : null,
                    'grupo' => '', // grupo desconhecido no fallback
                );
            }
        }

        // v8.12: Criar MAPA DE LACRES POR REGIONAL (robusto)
        $mapaLacresPorRegional = array();
        $regionaisLacres_post = isset($_POST['regional_lacres']) && is_array($_POST['regional_lacres']) ? $_POST['regional_lacres'] : array();
        if (!empty($postosLacres_post) && !empty($regionaisLacres_post)) {
            foreach ($postosLacres_post as $idx => $postoRaw) {
                $regional_raw = isset($regionaisLacres_post[$idx]) ? trim((string)$regionaisLacres_post[$idx]) : '';
                // normalizar removendo zeros à esquerda
                $regional = ltrim($regional_raw, '0');
                if ($regional === '') {
                    // se não houver regional explícita, pular
                    continue;
                }

                $lacreI = isset($lacresIIPR_post[$idx]) ? trim((string)$lacresIIPR_post[$idx]) : '';
                $lacreC = isset($lacresCorreios_post[$idx]) ? trim((string)$lacresCorreios_post[$idx]) : '';
                $eti = isset($etiquetasCorreios_post[$idx]) ? trim((string)$etiquetasCorreios_post[$idx]) : '';

                if ($lacreI === '' && $lacreC === '' && $eti === '') {
                    continue;
                }

                if (!isset($mapaLacresPorRegional[$regional])) {
                    $mapaLacresPorRegional[$regional] = array(
                        'lacre_iipr' => 0,
                        'lacre_correios' => 0,
                        'etiqueta_correios' => null,
                    );
                }
                if ($lacreI !== '') {
                    $mapaLacresPorRegional[$regional]['lacre_iipr'] = (int)$lacreI;
                }
                if ($lacreC !== '') {
                    $mapaLacresPorRegional[$regional]['lacre_correios'] = (int)$lacreC;
                }
                if ($eti !== '') {
                    $mapaLacresPorRegional[$regional]['etiqueta_correios'] = $eti;
                }
            }
        }

            // v8.13.1 FALLBACK: Criar mapas separados por grupo (se snapshot não existiu)
            foreach ($mapaLacresPorPosto as $postoKey => $vals) {
                $grupoLinha = isset($vals['grupo']) ? trim((string)$vals['grupo']) : '';
                if ($grupoLinha === 'CAPITAL') {
                    $mapaCapital[$postoKey] = $vals;
                } elseif ($grupoLinha === 'CENTRAL IIPR') {
                    $mapaCentral[$postoKey] = $vals;
                }
            }
            
            // Criar mapaRegional a partir de mapaLacresPorRegional
            $mapaRegional = $mapaLacresPorRegional;

            add_debug('V8.13.1 FALLBACK - MAPA CAPITAL', array('postos' => array_keys($mapaCapital), 'dados' => $mapaCapital));
            add_debug('V8.13.1 FALLBACK - MAPA CENTRAL', array('postos' => array_keys($mapaCentral), 'dados' => $mapaCentral));
            add_debug('V8.13.1 FALLBACK - MAPA REGIONAL', array('regionais' => array_keys($mapaRegional), 'dados' => $mapaRegional));
        }

        // v8.6: Atualizar SQL do INSERT para incluir campos de lacres
        $stInsLote = $pdo_controle->prepare("
            INSERT INTO ciDespachoLotes (id_despacho, posto, lote, quantidade, data_carga, responsaveis, etiquetaiipr, etiquetacorreios, etiqueta_correios)
            VALUES (?,?,?,?,?,?,?,?,?)
        ");

        // VERSAO 6: Debug MELHORADO - registrar etiquetas recebidas
        // NOTA: A coluna etiqueta_correios no banco DEVE ser VARCHAR(35), nao INT
        // Se os valores estiverem zerados, execute:
        // ALTER TABLE ciDespachoLotes MODIFY etiqueta_correios VARCHAR(35);
        
        // DEBUG V6: Registrar todas as etiquetas recebidas do POST
        add_debug('V6 - Etiquetas RAW do POST', $etiquetas_raw);
        add_debug('V6 - Etiquetas NORMALIZADAS', $etiquetas);
        
        // v8.6: Debug do mapa de lacres por posto
        add_debug('V8.6 - MAPA DE LACRES POR POSTO', $mapaLacresPorPosto);
        
        // Usar as etiquetas ja normalizadas (capturadas e normalizadas no passo 3)
        // A variavel $etiquetas ja contem as chaves no formato "041" (3 digitos)
        $totalLotes = 0;
        $etiquetas_debug = array();
        $lotes_processados = array();
        
        while ($l = $stmtLotes->fetch(PDO::FETCH_ASSOC)) {
            // O posto do lote ja vem com LPAD do SQL (ex: "041")
            $posto_lote = (string)$l['posto'];
            // v8.10: Capturar a regional do lote (normalizar removendo zeros à esquerda)
            $regional_lote_raw = isset($l['regional']) ? trim((string)$l['regional']) : '';
            $regional_lote = ltrim($regional_lote_raw, '0');
            if ($regional_lote === '') { $regional_lote = '0'; }
            
            // VERSAO 6: Buscar etiqueta_correios correspondente ao posto
            // Tentar todas as variações de chave possíveis
            $etiqueta_do_posto = '';
            
            // Tentar com chave normalizada (3 dígitos)
            if (isset($etiquetas[$posto_lote])) {
                $etiqueta_do_posto = trim((string)$etiquetas[$posto_lote]);
            }
            // Tentar com chave sem zeros à esquerda
            if (empty($etiqueta_do_posto)) {
                $posto_sem_zeros = ltrim($posto_lote, '0');
                if (isset($etiquetas[$posto_sem_zeros])) {
                    $etiqueta_do_posto = trim((string)$etiquetas[$posto_sem_zeros]);
                }
            }
            // Tentar com prefixo p_
            if (empty($etiqueta_do_posto)) {
                $posto_com_p = 'p_' . $posto_lote;
                if (isset($etiquetas_raw[$posto_com_p])) {
                    $etiqueta_do_posto = trim((string)$etiquetas_raw[$posto_com_p]);
                }
            }
            
            // Debug: registrar etiquetas para log
            $lotes_processados[$posto_lote . '_' . $l['lote']] = array(
                'posto' => $posto_lote,
                'lote' => $l['lote'],
                'regional' => $regional_lote,
                'etiqueta_encontrada' => $etiqueta_do_posto,
                'chaves_tentadas' => array($posto_lote, ltrim($posto_lote, '0'), 'p_' . $posto_lote)
            );
            
            if (!empty($etiqueta_do_posto)) {
                $etiquetas_debug[$posto_lote] = $etiqueta_do_posto;
            }
            
            // v8.13.3: Recuperar lacres EXCLUSIVAMENTE do snapshot (valores EXATOS dos inputs)
            // 1º: CAPITAL (apenas postos visíveis em $mapaCapital - sem expansão)
            // 2º: CENTRAL IIPR (apenas postos visíveis em $mapaCentral - sem expansão)
            // 3º: REGIONAIS (expande todos os postos da regional, usa lacres do snapshot)
            // CRÍTICO: lacre_iipr e lacre_correios DEVEM ser valores distintos e corretos
            $lacreIIPR_lote = 0;
            $lacreCorreios_lote = 0;
            $etiquetaCorreios_lote = null;

            $aplicar_mapa = false;
            $origem_lacre = ''; // Para debug
            
            // Prioridade 1: posto visível em CAPITAL (valores EXATOS do input)
            if (isset($mapaCapital[$posto_lote])) {
                $lacreIIPR_lote       = (int)$mapaCapital[$posto_lote]['lacre_iipr'];
                $lacreCorreios_lote   = (int)$mapaCapital[$posto_lote]['lacre_correios'];
                $etiquetaCorreios_lote = $mapaCapital[$posto_lote]['etiqueta_correios'];
                $aplicar_mapa = true;
                $origem_lacre = 'CAPITAL';
            }
            // Prioridade 2: posto visível em CENTRAL IIPR (valores EXATOS do input)
            elseif (isset($mapaCentral[$posto_lote])) {
                $lacreIIPR_lote       = (int)$mapaCentral[$posto_lote]['lacre_iipr'];
                $lacreCorreios_lote   = (int)$mapaCentral[$posto_lote]['lacre_correios'];
                $etiquetaCorreios_lote = $mapaCentral[$posto_lote]['etiqueta_correios'];
                $aplicar_mapa = true;
                $origem_lacre = 'CENTRAL';
            }
            // Prioridade 3: REGIONAIS - expande postos da regional (valores EXATOS do input)
            elseif ($regional_lote !== '' && $regional_lote !== '0' && isset($mapaRegional[$regional_lote])) {
                $lacreIIPR_lote       = (int)$mapaRegional[$regional_lote]['lacre_iipr'];
                $lacreCorreios_lote   = (int)$mapaRegional[$regional_lote]['lacre_correios'];
                $etiquetaCorreios_lote = $mapaRegional[$regional_lote]['etiqueta_correios'];
                $aplicar_mapa = true;
                $origem_lacre = 'REGIONAL:' . $regional_lote;
            }
            
            // v8.14.0: Validação CRÍTICA - lacres NUNCA podem ser iguais (exceto CENTRAL entre si)
            // CAPITAL e REGIONAIS: IIPR ≠ Correios SEMPRE por posto
            // CENTRAL IIPR: Correios pode ser igual entre postos (todos usam último+1)
            if ($aplicar_mapa && $lacreIIPR_lote > 0 && $lacreCorreios_lote > 0) {
                if ($lacreIIPR_lote === $lacreCorreios_lote) {
                    // CRITICAL: lacres duplicados detectados - corrigir SEMPRE
                    if ($debug_lacres) {
                        echo "<div style='background:#ff6b6b;color:white;padding:10px;margin:10px;font-weight:bold;'>ERRO CORRIGIDO: Posto $posto_lote ($origem_lacre) tinha IIPR=$lacreIIPR_lote IGUAL Correios=$lacreCorreios_lote - AUTO-CORRIGIDO para Correios=" . ($lacreIIPR_lote + 1) . "</div>";
                    }
                    // SEMPRE corrigir: Correios = IIPR + 1 (regra universal)
                    $lacreCorreios_lote = $lacreIIPR_lote + 1;
                }
            }
            
            // Se nenhum mapa tiver dados, NÃO inserir este lote (postos não visíveis)
            if (!$aplicar_mapa) {
                continue;
            }

            // VERSAO 6: Garantir que etiqueta seja passada como STRING pura
            $etiqueta_para_banco = (string)$etiqueta_do_posto;
            
            // v8.13.3: Debug detalhado quando debug_lacres=1
            if ($debug_lacres && $totalLotes < 10) {
                echo "<div style='background:#e8f5e9;padding:5px;margin:2px;font-family:monospace;font-size:11px;'>";
                echo "Lote #" . ($totalLotes + 1) . ": Posto=$posto_lote, Regional=$regional_lote, ";
                echo "Origem=$origem_lacre, ";
                echo "<b>IIPR=$lacreIIPR_lote</b>, <b>Correios=$lacreCorreios_lote</b>, ";
                echo "Etiqueta=" . ($etiquetaCorreios_lote ? substr($etiquetaCorreios_lote, 0, 15) . '...' : 'NULL');
                echo "</div>";
            }
            
            // v8.10: Debug por lote antes de inserir
            // (registra apenas primeiras 5 linhas para não sobrecarregar o debug_log)
            if ($totalLotes < 5) {
            add_debug('V8.13.3 - LOTE A GRAVAR', array(
                'posto_lote'           => $posto_lote,
                'regional_lote_raw'    => $regional_lote_raw,
                'regional_lote_norm'   => $regional_lote,
                'origem_lacre'         => $origem_lacre,
                'lacreIIPR_lote'       => $lacreIIPR_lote,
                'lacreCorreios_lote'   => $lacreCorreios_lote,
                'etiquetaCorreios_lote' => $etiquetaCorreios_lote,
                'existe_em_mapaCapital'  => isset($mapaCapital[$posto_lote]),
                'existe_em_mapaCentral'  => isset($mapaCentral[$posto_lote]),
                'existe_em_mapaRegional' => isset($mapaRegional[$regional_lote]),
            ));
            }
            
            // v8.13.3: Passar os 3 campos de lacres ao INSERT com cast explícito
            // CRÍTICO: etiquetaiipr e etiquetacorreios devem ser INT distintos
            $stInsLote->execute(array(
                $id_desp,
                $posto_lote,
                (string)$l['lote'],
                (int)$l['quantidade'],
                $l['data_carga'],
                $l['responsaveis'],
                (int)$lacreIIPR_lote,          // etiquetaiipr (INT)
                (int)$lacreCorreios_lote,      // etiquetacorreios (INT) - NUNCA igual a lacre_iipr quando deveria ser diferente
                $etiquetaCorreios_lote         // etiqueta_correios (VARCHAR 35 dígitos)
            ));
            // v8.3: Registra dados do lote gravado para contar postos distintos
            if (!isset($lotes_processados_dados)) { $lotes_processados_dados = array(); }
            $lotes_processados_dados[] = array('posto' => $posto_lote, 'lote' => $l['lote']);
            $totalLotes++;
        }
        
        // Registrar debug das etiquetas
        add_debug('V6 - Lotes processados', $lotes_processados);
        if (!empty($etiquetas_debug)) {
            add_debug('V6 - Etiquetas salvas', $etiquetas_debug);
        } else {
            add_debug('V6 - AVISO: Nenhuma etiqueta foi associada aos lotes', array(
                'total_lotes' => $totalLotes,
                'etiquetas_disponiveis' => array_keys($etiquetas)
            ));
        }

        // 7) v8.3: Calcula total de postos distintos e total de lotes
        $totalPostosDistintos = 0;
        if (isset($lotes_processados_dados) && is_array($lotes_processados_dados)) {
            $postosUnicos = array_unique(array_column($lotes_processados_dados, 'posto'));
            $totalPostosDistintos = count($postosUnicos);
        }
        $totalLotesGravados = $totalLotes;
        
        $pdo_controle->commit();

        // v8.14.0: NÃO salvar lacres na sessão após salvar!
        // - Snapshot é usado APENAS para gravar no BD
        // - Sessão é atualizada APENAS quando usuário edita inputs manualmente
        // - Isso evita perpetuar duplicações ou erros do JavaScript
        // - localStorage já preserva os valores corretos que estavam na tela
        // (REMOVIDO: código que salvava mapaCapital/mapaCentral na sessão)

        // Verifica se deve imprimir após salvar
        $deve_imprimir = isset($_POST['imprimir_apos_salvar']) && $_POST['imprimir_apos_salvar'] === '1';

        // v8.12.3-fix: Mensagem de sucesso SEM recarregar página (preserva inputs na tela)
        if ($deve_imprimir) {
            echo "<script>
                    alert('Oficio Correios salvo com sucesso! No. " . (int)$id_desp . " - Postos: " . (int)$totalPostosDistintos . ", Lotes: " . (int)$totalLotesGravados . "');
                    if (typeof marcarComoSalvo === 'function') { marcarComoSalvo(); }
                    window.print();
                  </script>";
        } else {
            // v8.12.3-fix: NÃO recarrega a página para preservar inputs de lacres/etiquetas
            // Inputs permanecerão preenchidos até que usuário clique em "Limpar Sessão" ou "X" da coluna
            echo "<script>
                    alert('Oficio Correios salvo com sucesso! No. " . (int)$id_desp . " - Postos: " . (int)$totalPostosDistintos . ", Lotes: " . (int)$totalLotesGravados . "');
                    if (typeof marcarComoSalvo === 'function') { marcarComoSalvo(); }
                  </script>";
        }
    } catch (Exception $e) {
        if ($pdo_controle->inTransaction()) {
            $pdo_controle->rollBack();
        }
        echo "<script>alert('Erro ao salvar oficio Correios: " . addslashes($e->getMessage()) . "');</script>";
    }
}


// Manter um log para depuração
function add_debug($message, $data = null) {
    if (!isset($_SESSION['debug_log'])) {
        $_SESSION['debug_log'] = array();
    }
    $_SESSION['debug_log'][] = array(
        'time' => date('H:i:s'),
        'message' => $message,
        'data' => $data
    );
}

// Função para validar duplicatas de etiquetas
function validar_etiqueta_duplicada($nova_etiqueta, $indice_atual) {
    if (empty($nova_etiqueta) || strlen($nova_etiqueta) !== 35) {
        return array('valida' => true, 'mensagem' => '');
    }
    
    foreach ($_SESSION['etiquetas'] as $posto_codigo => $etiqueta_existente) {
        if ($posto_codigo !== $indice_atual && $etiqueta_existente === $nova_etiqueta) {
            return array(
                'valida' => false,
                'mensagem' => "Esta etiqueta já está sendo usada no posto {$posto_codigo}"
            );
        }
    }
    
    return array('valida' => true, 'mensagem' => '');
}

// V7.9: Função para analisar dados de expedição com nova lógica de data (+1 dia)
function analisar_expedicao($pdo_controle, $pdo_servico, $datas_filtro) {
    // V7.9: Converter datas do formato brasileiro para SQL e adicionar +1 dia
    $sql_dates_array = array();
    foreach ($datas_filtro as $data) {
        $partes = explode('-', $data);
        if (count($partes) === 3) {
            // V7.9: Adicionar 1 dia à data selecionada para buscar na madrugada seguinte
            $data_sql = "{$partes[2]}-{$partes[1]}-{$partes[0]}";
            $data_plus_one = date('Y-m-d', strtotime($data_sql . ' +1 day'));
            $sql_dates_array[] = $data_plus_one;
        }
    }
    
    if (empty($sql_dates_array)) {
        return array(
            'total_carteiras' => 0,
            'total_postos' => 0,
            'autores_faltantes' => array(),
            'diferenca' => 0,
            'postos_retirados' => array(),
            'detalhes_expedicao' => array()
        );
    }
    
    $sql_datas_in = "'" . implode("','", $sql_dates_array) . "'";
    
    // V7.9: Consultar carteiras expedidas na tbl_ci_filadeimpressao (data +1 dia às 02:00)
    $sql_expedidas = "SELECT DATE(datafila) as data, expedidas, TIME(datafila) as hora
                      FROM tbl_ci_filadeimpressao
                      WHERE DATE(datafila) IN ($sql_datas_in)
                      AND TIME(datafila) BETWEEN '01:30:00' AND '02:30:00'
                      ORDER BY DATE(datafila), ABS(TIMESTAMPDIFF(SECOND, TIME(datafila), '02:00:00'))";
    
    $stmt_expedidas = $pdo_servico->query($sql_expedidas);
    $dados_expedidas = array();
    $total_carteiras_geral = 0;
    $detalhes_expedicao = array();
    
    // Agrupar por data para pegar apenas um registro por data (o mais próximo das 02:00)
    $expedidas_por_data = array();
    $data_original_map = array_combine($sql_dates_array, $datas_filtro); // Mapeamento data+1 -> data original
    
    while ($row = $stmt_expedidas->fetch(PDO::FETCH_ASSOC)) {
        $data = $row['data'];
        if (!isset($expedidas_por_data[$data])) {
            $expedidas_por_data[$data] = array(
                'expedidas' => (int)$row['expedidas'],
                'hora' => $row['hora']
            );
            $total_carteiras_geral += (int)$row['expedidas'];
            
            // V7.9: Mostrar data original (não data+1)
            $data_original = isset($data_original_map[$data]) ? $data_original_map[$data] : date('d/m/Y', strtotime($data . ' -1 day'));
            $detalhes_expedicao[] = array(
                'data' => $data_original,
                'expedidas' => (int)$row['expedidas'],
                'hora' => $row['hora']
            );
        }
    }
    
    // V7.9: Para as demais consultas, usar as datas originais (sem +1)
    $sql_dates_original = array();
    foreach ($datas_filtro as $data) {
        $partes = explode('-', $data);
        if (count($partes) === 3) {
            $sql_dates_original[] = "{$partes[2]}-{$partes[1]}-{$partes[0]}";
        }
    }
    $sql_datas_original_in = "'" . implode("','", $sql_dates_original) . "'";
    
    // 2. Consultar total de postos em ciPostos (mantendo compatibilidade com ciPostosCsv)
    $sql_postos = "SELECT SUM(quantidade) as total_postos
                   FROM ciPostosCsv
                   WHERE DATE(dataCarga) IN ($sql_datas_original_in)";
    
    $stmt_postos = $pdo_controle->query($sql_postos);
    $row_postos = $stmt_postos->fetch(PDO::FETCH_ASSOC);
    $total_postos = (int)(isset($row_postos['total_postos']) ? $row_postos['total_postos'] : 0);
    
    // 3. Consultar autores presentes em ciPostosCsv para as datas
    $sql_autores_postos = "SELECT DISTINCT usuario as autor
                          FROM ciPostosCsv
                          WHERE DATE(dataCarga) IN ($sql_datas_original_in)
                          AND usuario IS NOT NULL";
    
    $stmt_autores_postos = $pdo_controle->query($sql_autores_postos);
    $autores_em_postos = array();
    while ($row = $stmt_autores_postos->fetch(PDO::FETCH_ASSOC)) {
        if (!empty($row['autor'])) {
            $autores_em_postos[] = $row['autor'];
        }
    }
    
    // 4. Para identificar autores faltantes, usamos uma consulta da ci-expedidas como fallback
    // já que a tbl_ci_filadeimpressao não tem campo autor individual
    $sql_expedidas_autores = "SELECT SUM(total) as total_carteiras, autor, total
                             FROM `ci-expedidas`
                             WHERE dia IN ($sql_datas_original_in)
                             GROUP BY autor";
    
    $stmt_expedidas_autores = $pdo_controle->query($sql_expedidas_autores);
    $dados_expedidas_por_autor = array();
    
    while ($row = $stmt_expedidas_autores->fetch(PDO::FETCH_ASSOC)) {
        $dados_expedidas_por_autor[$row['autor']] = (int)$row['total'];
    }
    
    // 5. Identificar autores faltantes e suas quantidades
    $autores_faltantes = array();
    $total_faltante = 0;
    
    foreach ($dados_expedidas_por_autor as $autor => $quantidade) {
        if (!in_array($autor, $autores_em_postos)) {
            $autores_faltantes[] = array(
                'autor' => $autor,
                'quantidade' => $quantidade
            );
            $total_faltante += $quantidade;
        }
    }
    
    // 6. Consultar retiradas na tabela ciRetirada
    $sql_retiradas = "SELECT protocolo
                      FROM ciRetirada
                      WHERE DATE(datasolicitacao) IN ($sql_datas_original_in)";
    
    $stmt_retiradas = $pdo_controle->query($sql_retiradas);
    $postos_retirados = array();
    
    while ($row = $stmt_retiradas->fetch(PDO::FETCH_ASSOC)) {
        $protocolo = $row['protocolo'];
        // Extrair os 3 primeiros dígitos do protocolo (formato: 850-23-00851)
        if (preg_match('/^(\d{3})/', $protocolo, $matches)) {
            $posto_numero = $matches[1];
            if (!in_array($posto_numero, $postos_retirados)) {
                $postos_retirados[] = $posto_numero;
            }
        }
    }
    
    // V7.9: Ordenar postos retirados em ordem crescente
    sort($postos_retirados, SORT_NUMERIC);
    
    // Calcular diferença
    $diferenca = $total_carteiras_geral - $total_postos;
    
    return array(
        'total_carteiras' => $total_carteiras_geral,
        'total_postos' => $total_postos,
        'autores_faltantes' => $autores_faltantes,
        'diferenca' => $diferenca,
        'total_faltante' => $total_faltante,
        'postos_retirados' => $postos_retirados,
        'dados_expedidas' => $dados_expedidas_por_autor,
        'autores_em_postos' => $autores_em_postos,
        'detalhes_expedicao' => $detalhes_expedicao
    );
}

// V8.1: Função para obter usuários válidos do banco contrsos
function obter_usuarios_validos($pdo_contrsos) {
    $sql = "SELECT nome, usuario FROM usuarios WHERE perfil != 'exf' ORDER BY nome";
    $stmt = $pdo_contrsos->query($sql);
    $usuarios = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $usuarios[] = array(
            'nome' => $row['nome'],
            'usuario' => $row['usuario']
        );
    }
    return $usuarios;
}

// V8.1: Função para obter nome do posto baseado no código
function obter_nome_posto($pdo_controle, $codigo_posto) {
    $sql = "SELECT DISTINCT posto FROM ciPostos WHERE posto LIKE ? ORDER BY posto LIMIT 1";
    $stmt = $pdo_controle->prepare($sql);
    $stmt->execute(array("{$codigo_posto}%"));
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        return $result['posto'];
    }
    
    // Se não encontrar, retorna o código com formatação padrão
    return sprintf("%03d - POSTO", intval($codigo_posto));
}

// V8.1: Função para inserir dados na tabela ciPostos com código de barras
function inserir_dados_cipostos_barcode($pdo_controle, $codigo_barras, $data, $turno, $autor) {
    try {
        // Validar código de barras (19 dígitos)
        if (strlen($codigo_barras) !== 19 || !ctype_digit($codigo_barras)) {
            throw new Exception("Código de barras deve ter exatamente 19 dígitos numéricos");
        }
        
        // Extrair informações do código de barras
        $lote = substr($codigo_barras, 0, 8);        // Primeiros 8 dígitos (não salvar)
        $regional = substr($codigo_barras, 8, 3);    // Próximos 3 dígitos (não salvar)
        $codigo_posto = substr($codigo_barras, 11, 3); // Próximos 3 dígitos
        $quantidade = (int)substr($codigo_barras, -5); // Últimos 5 dígitos
        
        // Obter nome completo do posto
        $nome_posto = obter_nome_posto($pdo_controle, $codigo_posto);
        
        // Converter data do formato brasileiro para SQL
        $partes = explode('/', $data);
        if (count($partes) === 3) {
            $data_sql = "{$partes[2]}-{$partes[1]}-{$partes[0]}";
        } else {
            throw new Exception("Formato de data inválido");
        }
        
        // Data e hora de criação (sempre 10:10:10)
        $criado = $data_sql . ' 10:10:10';
        
        // Inserir na tabela ciPostos
        $stmt = $pdo_controle->prepare("
            INSERT INTO ciPostos (posto, dia, quantidade, turno, regional, lote, autor, criado, situacao)
            VALUES (?, ?, ?, ?, NULL, ?, ?, ?, 0)
        ");
        
        $resultado = $stmt->execute(array(
            $nome_posto,
            $data_sql,
            $quantidade,
            $turno,
            (int)$lote,
            $autor,
            $criado
        ));
        
        if ($resultado) {
            add_debug("Dados inseridos em ciPostos via código de barras", array(
                'codigo_barras' => $codigo_barras,
                'posto' => $nome_posto,
                'quantidade' => $quantidade,
                'data' => $data_sql,
                'turno' => $turno,
                'autor' => $autor
            ));
            return array(
                'sucesso' => true,
                'mensagem' => "Dados inseridos com sucesso: {$nome_posto} - {$quantidade} carteiras"
            );
        } else {
            return array('sucesso' => false, 'mensagem' => "Erro ao inserir dados");
        }
    } catch (Exception $e) {
        add_debug("Erro ao inserir dados em ciPostos", array(
            'erro' => $e->getMessage(),
            'codigo_barras' => $codigo_barras,
            'data' => $data
        ));
        return array('sucesso' => false, 'mensagem' => "Erro: " . $e->getMessage());
    }
}

// Definições dos grupos de postos
$CAPITAL = array("001","002","014","015","030","031","032","033","034","035","036","037","039","040","044");
$CENTRAL = array("010","013","016","018","027","041","042","046","047","051","052","053","054",
            "055","056","057","058","059","060","061","062","063","064","065","066","067","068",
            "069","070","071","072","073","074","075","076","077","078","079","080","083","084",
            "085","086");

// CONFIGURAÇÃO DE SPLITS PARA CENTRAL IIPR
// Lista de códigos de posto (formato '046') que marcam o início de um novo malote
// Exemplo: $splitsCentral = array('046'); -> a partir do posto 046 começa o próximo malote
// Observe: os splits são aplicados sobre a ordem exibida da CENTRAL IIPR (ordenada numericamente)
$splitsCentral = array();

// Limpar a sessão completamente quando solicitado
if ((
    $_SERVER['REQUEST_METHOD'] === 'GET' && empty($_GET)) ||
    (isset($_POST['limpar_sessao']))
) {
    $_SESSION['etiquetas'] = array();
    $_SESSION['linhas_removidas'] = array();
    $_SESSION['lacres_personalizados'] = array();
    $_SESSION['postos_manuais'] = array();
    $_SESSION['postos_cadastrados'] = array();
    $_SESSION['datas_filtro'] = array();
    $_SESSION['debug_log'] = array();
    $_SESSION['excluir_regionais_manual'] = array();
}

// Salvar datas selecionadas
if (isset($_GET['datas']) && is_array($_GET['datas'])) {
    $_SESSION['datas_filtro'] = $_GET['datas'];
}

$mensagem_sucesso = '';
$mensagem_erro = '';

// Tratamento de POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // v1.6: liberar etiqueta quando campo é esvaziado (permite mover a etiqueta)
    if (isset($_POST['limpar_etiqueta'], $_POST['indice'])) {
        $indice = trim((string)$_POST['indice']);
        if (!isset($_SESSION['etiquetas'])) { $_SESSION['etiquetas'] = array(); }
        if (isset($CENTRAL) && is_array($CENTRAL) && in_array($indice, $CENTRAL)) {
            foreach ($CENTRAL as $posto_cod) { unset($_SESSION['etiquetas'][$posto_cod]); }
        } else {
            unset($_SESSION['etiquetas'][$indice]);
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array('status' => 'ok'));
        exit;
    }

    // Atualizar etiqueta com validação de duplicata
    if (isset($_POST['etiqueta'], $_POST['indice'])) {
        $indice = trim((string)$_POST['indice']);
        $nova_etiqueta = $_POST['etiqueta'];
        
        // Validar duplicata
        $validacao = validar_etiqueta_duplicada($nova_etiqueta, $indice);
        
        if (!$validacao['valida']) {
            echo json_encode(array('status' => 'erro', 'mensagem' => $validacao['mensagem']));
            exit;
        }
        
        // Propagar etiqueta: se o cliente enviou explicitamente a lista de postos do grupo
        // (parametro `group_postos[]`), aplicamos apenas a esses postos. Caso contrario,
        // manter comportamento legado: se for posto CENTRAL e nao houver group_postos,
        // aplicar a toda a CENTRAL; senao aplicar apenas ao indice informado.
        $_SESSION['etiquetas'][$indice] = $nova_etiqueta;

        if (isset($_POST['group_postos']) && is_array($_POST['group_postos']) && count($_POST['group_postos'])>0) {
            foreach ($_POST['group_postos'] as $posto_cod) {
                $posto_cod = trim((string)$posto_cod);
                if ($posto_cod !== '') {
                    $_SESSION['etiquetas'][$posto_cod] = $nova_etiqueta;
                }
            }
        } else {
            if (in_array($indice, $CENTRAL)) {
                // Sem group_postos informado: comportamento legado para CENTRAL
                foreach ($CENTRAL as $posto_cod) {
                    $_SESSION['etiquetas'][$posto_cod] = $nova_etiqueta;
                }
            }
        }
        
        echo json_encode(array('status' => 'ok'));
        exit;
    }
    
    // Atualizar lacre
    if (isset($_POST['update_lacre'], $_POST['indice'], $_POST['tipo'])) {
        $indice = trim((string)$_POST['indice']);
        $novo_lacre = $_POST['update_lacre'];
        // Se o cliente enviou group_postos[], atualizar todos os postos do grupo
        if (isset($_POST['group_postos']) && is_array($_POST['group_postos']) && count($_POST['group_postos'])>0) {
            foreach ($_POST['group_postos'] as $posto_cod) {
                $posto_cod = trim((string)$posto_cod);
                if ($posto_cod !== '') {
                    if (!isset($_SESSION['lacres_personalizados'][$posto_cod])) $_SESSION['lacres_personalizados'][$posto_cod] = array();
                    $_SESSION['lacres_personalizados'][$posto_cod][$_POST['tipo']] = $novo_lacre;
                }
            }
        } else {
            if (!isset($_SESSION['lacres_personalizados'][$indice])) $_SESSION['lacres_personalizados'][$indice] = array();
            $_SESSION['lacres_personalizados'][$indice][$_POST['tipo']] = $novo_lacre;
        }
        echo json_encode(array('status' => 'ok'));
        exit;
    }
    
    // Salvar etiquetas no banco de dados com confirmação
    if (isset($_POST['salvar_etiquetas_confirmado'])) {
        $login = isset($_POST['login_personalizado']) && !empty($_POST['login_personalizado'])
                 ? $_POST['login_personalizado']
                 : (isset($_POST['login']) ? $_POST['login'] : 'Sistema');
        $hoje = date('Y-m-d');
        $etiquetas_salvas = 0;
        
        // CORREÇÃO V8.1: Array para controlar etiquetas já salvas da central
        $etiquetas_central_salvas = array();
        $erros = 0;
        
        // Percorrer todas as etiquetas da sessão
        foreach ($_SESSION['etiquetas'] as $posto_codigo => $etiqueta) {
            // Verificar se a etiqueta é válida (não vazia e tem 35 caracteres)
            if (!empty($etiqueta) && strlen($etiqueta) === 35) {
                // CORREÇÃO V8.1: Para postos da central, verificar se a etiqueta já foi salva
                if (in_array($posto_codigo, $CENTRAL)) {
                    if (in_array($etiqueta, $etiquetas_central_salvas)) {
                        // Etiqueta da central já foi salva, pular para evitar duplicata
                        continue;
                    }
                    // Marcar etiqueta como salva para a central
                    $etiquetas_central_salvas[] = $etiqueta;
                }
                try {
                    // Extrair CEP e sequencial da etiqueta
                    $cep = substr($etiqueta, 0, 8);
                    $sequencial = substr($etiqueta, -5);
                    
                    // Preparar a observação
                    $observacao = "Etiqueta gerada por {$login} em " . date('d/m/Y');
                    
                    // Inserir na tabela ciMalotes
                    $stmt = $pdo_controle->prepare("INSERT INTO ciMalotes (leitura, data, observacao, login, tipo, cep, sequencial, posto)
                                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute(array(
                        $etiqueta,
                        $hoje,
                        $observacao,
                        $login,
                        1, // Tipo padrão
                        $cep,
                        $sequencial,
                        $posto_codigo
                    ));
                    
                    $etiquetas_salvas++;
                } catch (PDOException $e) {
                    add_debug("Erro ao salvar etiqueta", array(
                        'posto' => $posto_codigo,
                        'etiqueta' => $etiqueta,
                        'erro' => $e->getMessage()
                    ));
                    $erros++;
                }
            }
        }
        
        if ($etiquetas_salvas > 0) {
            $mensagem_sucesso = "Foram salvas {$etiquetas_salvas} etiquetas no banco de dados por {$login}.";
            if ($erros > 0) {
                $mensagem_sucesso .= " ({$erros} etiquetas não puderam ser salvas)";
            }
        } else {
            $mensagem_erro = "Nenhuma etiqueta foi salva no banco de dados. Verifique se há etiquetas preenchidas.";
        }
    }
    
    // V8.1: Inserir dados na tabela ciPostos com código de barras
    if (isset($_POST['inserir_dados_barcode'])) {
        $codigo_barras = trim($_POST['codigo_barras']);
        $data = trim($_POST['data_inserir']);
        $turno = (int)$_POST['turno_inserir'];
        $autor = isset($_POST['autor_inserir']) && $_POST['autor_inserir'] != '' ? trim($_POST['autor_inserir']) : 'conferencia';
        
        if (!empty($codigo_barras) && !empty($data) && $turno > 0) {
            $resultado = inserir_dados_cipostos_barcode($pdo_controle, $codigo_barras, $data, $turno, $autor);
            
            if ($resultado['sucesso']) {
                $mensagem_sucesso = $resultado['mensagem'];
            } else {
                $mensagem_erro = $resultado['mensagem'];
            }
        } else {
            $mensagem_erro = "Todos os campos são obrigatórios para inserir dados.";
        }
        
        // Redirecionamento preservando filtros
        $params = array();
        if (isset($_GET['lacre_capital'])) $params[] = "lacre_capital=" . urlencode($_GET['lacre_capital']);
        if (isset($_GET['lacre_central'])) $params[] = "lacre_central=" . urlencode($_GET['lacre_central']);
        if (isset($_GET['lacre_regionais'])) $params[] = "lacre_regionais=" . urlencode($_GET['lacre_regionais']);
        if (isset($_GET['responsavel'])) $params[] = "responsavel=" . urlencode($_GET['responsavel']);
        
        // Adicionar as datas selecionadas
        if (!empty($_SESSION['datas_filtro'])) {
            foreach ($_SESSION['datas_filtro'] as $data) {
                $params[] = "datas[]=" . urlencode($data);
            }
        }
        
        $redirect_url = $_SERVER['PHP_SELF'] . (isset($_GET['debug']) ? '?debug=1' : '');
        if (!empty($params)) {
            $redirect_url .= (strpos($redirect_url, '?') !== false ? '&' : '?') . implode('&', $params);
        }
        
        header("Location: $redirect_url");
        exit;
    }
    
    // Exclusão dedicada para REGIONAIS
    if (isset($_POST['excluir_posto_regional']) && $_POST['excluir_posto_regional'] === '1') {
        // Código do posto (número ou código manual com M)
        $codigo = trim((string)$_POST['codigo_posto']);
        
        // Informação para depuração
        add_debug("Solicitação de exclusão REGIONAL com nova abordagem", array(
            'codigo' => $codigo,
            'post_data' => $_POST,
            'regional_info' => isset($_POST['info_regional']) ? $_POST['info_regional'] : 'não informado'
        ));
        
        // Adicionar no array específico
        $_SESSION['excluir_regionais_manual'][] = $codigo;
        
        // Mensagem de sucesso
        $mensagem_sucesso = "Posto {$codigo} do grupo REGIONAIS foi excluído com sucesso!";
        
        // Redirecionamento preservando filtros
        $params = array();
        if (isset($_GET['lacre_capital'])) $params[] = "lacre_capital=" . urlencode($_GET['lacre_capital']);
        if (isset($_GET['lacre_central'])) $params[] = "lacre_central=" . urlencode($_GET['lacre_central']);
        if (isset($_GET['lacre_regionais'])) $params[] = "lacre_regionais=" . urlencode($_GET['lacre_regionais']);
        if (isset($_GET['responsavel'])) $params[] = "responsavel=" . urlencode($_GET['responsavel']);
        
        // Adicionar as datas selecionadas
        if (!empty($_SESSION['datas_filtro'])) {
            foreach ($_SESSION['datas_filtro'] as $data) {
                $params[] = "datas[]=" . urlencode($data);
            }
        }
        
        $redirect_url = $_SERVER['PHP_SELF'] . (isset($_GET['debug']) ? '?debug=1' : '');
        if (!empty($params)) {
            $redirect_url .= (strpos($redirect_url, '?') !== false ? '&' : '?') . implode('&', $params);
        }
        
        header("Location: $redirect_url");
        exit;
    }
    
    // Exclusão dos outros grupos (CAPITAL e CENTRAL)
    if (isset($_POST['excluir_posto']) && $_POST['excluir_posto'] === '1') {
        $codigo = trim((string)$_POST['codigo_posto']);
        $grupo = isset($_POST['grupo_posto']) ? $_POST['grupo_posto'] : 'Não especificado';
        
        // Para postos de CAPITAL e CENTRAL: usar o sistema tradicional
        $_SESSION['linhas_removidas'][$codigo] = true;
        
        if (isset($_SESSION['lacres_personalizados'][$codigo])) {
            unset($_SESSION['lacres_personalizados'][$codigo]);
        }
        if (isset($_SESSION['etiquetas'][$codigo])) {
            unset($_SESSION['etiquetas'][$codigo]);
        }
        
        // Se for um posto manual
        if (strpos($codigo, 'M') === 0 && isset($_SESSION['postos_manuais'][$codigo])) {
            unset($_SESSION['postos_manuais'][$codigo]);
        }
        
        $mensagem_sucesso = "Posto {$codigo} ({$grupo}) removido com sucesso!";
        
        // Redirecionamento preservando filtros
        $params = array();
        if (isset($_GET['lacre_capital'])) $params[] = "lacre_capital=" . urlencode($_GET['lacre_capital']);
        if (isset($_GET['lacre_central'])) $params[] = "lacre_central=" . urlencode($_GET['lacre_central']);
        if (isset($_GET['lacre_regionais'])) $params[] = "lacre_regionais=" . urlencode($_GET['lacre_regionais']);
        if (isset($_GET['responsavel'])) $params[] = "responsavel=" . urlencode($_GET['responsavel']);
        
        // Adicionar as datas selecionadas
        if (!empty($_SESSION['datas_filtro'])) {
            foreach ($_SESSION['datas_filtro'] as $data) {
                $params[] = "datas[]=" . urlencode($data);
            }
        }
        
        $redirect_url = $_SERVER['PHP_SELF'] . (isset($_GET['debug']) ? '?debug=1' : '');
        if (!empty($params)) {
            $redirect_url .= (strpos($redirect_url, '?') !== false ? '&' : '?') . implode('&', $params);
        }
        
        header("Location: $redirect_url");
        exit;
    }
    
    // Inserir linha acima/abaixo - Método via modal
    if (isset($_POST['inserir_linha'])) {
        $posicao = isset($_POST['posicao']) ? $_POST['posicao'] : '';
        $referencia_posto = isset($_POST['referencia_posto']) ? $_POST['referencia_posto'] : '';
        $novo_nome = isset($_POST['novo_nome']) ? $_POST['novo_nome'] : '';
        $grupo = isset($_POST['novo_grupo']) ? $_POST['novo_grupo'] : 'REGIONAIS';
        $lacre_iipr = isset($_POST['novo_lacre_iipr']) ? $_POST['novo_lacre_iipr'] : '';
        $lacre_correios = isset($_POST['novo_lacre_correios']) ? $_POST['novo_lacre_correios'] : '';
        
        if (empty($novo_nome)) {
            $mensagem_erro = "O nome do posto é obrigatório!";
        } else {
            // Gerar código único para o posto manual
            $codigo = 'M' . time() . rand(1000, 9999);
            
            // Registrar informações do posto
            $_SESSION['postos_manuais'][$codigo] = array(
                'posto_codigo' => $codigo,
                'posto_nome' => $novo_nome,
                'tipo' => $grupo,
                'quantidade' => 1,
                'referencia' => $referencia_posto,
                'posicao' => $posicao
            );
            
            // Registrar lacres personalizados
            $_SESSION['lacres_personalizados'][$codigo]['iipr'] = $lacre_iipr;
            $_SESSION['lacres_personalizados'][$codigo]['correios'] = $lacre_correios;
            
            // Mensagem de sucesso
            $mensagem_sucesso = "Posto '{$novo_nome}' adicionado com sucesso!";
            
            // Redirecionamento preservando todos os parâmetros
            $params = array();
            if (isset($_GET['lacre_capital'])) $params[] = "lacre_capital=" . urlencode($_GET['lacre_capital']);
            if (isset($_GET['lacre_central'])) $params[] = "lacre_central=" . urlencode($_GET['lacre_central']);
            if (isset($_GET['lacre_regionais'])) $params[] = "lacre_regionais=" . urlencode($_GET['lacre_regionais']);
            if (isset($_GET['responsavel'])) $params[] = "responsavel=" . urlencode($_GET['responsavel']);
            
            // Adicionar as datas selecionadas
            if (!empty($_SESSION['datas_filtro'])) {
                foreach ($_SESSION['datas_filtro'] as $data) {
                    $params[] = "datas[]=" . urlencode($data);
                }
            }
            
            $redirect_url = $_SERVER['PHP_SELF'] . (isset($_GET['debug']) ? '?debug=1' : '');
            if (!empty($params)) {
                $redirect_url .= (strpos($redirect_url, '?') !== false ? '&' : '?') . implode('&', $params);
            }
            
            header("Location: $redirect_url");
            exit;
        }
    }
    
    // Adicionar posto manual
    if (isset($_POST['adicionar_manual'])) {
        $tipo = $_POST['tipo_posto'];
        $posto_nome = $_POST['nome_posto'];
        $lacre_iipr = $_POST['lacre_iipr_manual'];
        $lacre_correios = $_POST['lacre_correios_manual'];
        
        if (empty($posto_nome)) {
            $mensagem_erro = "O nome do posto é obrigatório!";
        } else {
            $codigo = 'M' . time() . rand(1000, 9999);
            $_SESSION['postos_manuais'][$codigo] = array(
                'posto_codigo' => $codigo,
                'posto_nome' => $posto_nome,
                'tipo' => $tipo,
                'quantidade' => 1
            );
            $_SESSION['lacres_personalizados'][$codigo]['iipr'] = $lacre_iipr;
            $_SESSION['lacres_personalizados'][$codigo]['correios'] = $lacre_correios;
            
            $mensagem_sucesso = "Posto '{$posto_nome}' adicionado com sucesso!";
            
            // Redirecionamento para manter os parâmetros GET
            // Usar GET se presente, caso contrario usar o ultimo valor salvo em sessao
            $params = array();
            if (isset($_GET['lacre_capital'])) {
                $params[] = "lacre_capital=" . urlencode($_GET['lacre_capital']);
            } elseif (isset($_SESSION['ultimo_lacre_capital'])) {
                $params[] = "lacre_capital=" . urlencode($_SESSION['ultimo_lacre_capital']);
            }

            if (isset($_GET['lacre_central'])) {
                $params[] = "lacre_central=" . urlencode($_GET['lacre_central']);
            } elseif (isset($_SESSION['ultimo_lacre_central'])) {
                $params[] = "lacre_central=" . urlencode($_SESSION['ultimo_lacre_central']);
            }

            if (isset($_GET['lacre_regionais'])) {
                $params[] = "lacre_regionais=" . urlencode($_GET['lacre_regionais']);
            } elseif (isset($_SESSION['ultimo_lacre_regionais'])) {
                $params[] = "lacre_regionais=" . urlencode($_SESSION['ultimo_lacre_regionais']);
            }

            if (isset($_GET['responsavel'])) {
                $params[] = "responsavel=" . urlencode($_GET['responsavel']);
            } elseif (isset($_SESSION['ultimo_responsavel'])) {
                $params[] = "responsavel=" . urlencode($_SESSION['ultimo_responsavel']);
            }
            
            // Adicionar as datas selecionadas
            if (!empty($_SESSION['datas_filtro'])) {
                foreach ($_SESSION['datas_filtro'] as $data) {
                    $params[] = "datas[]=" . urlencode($data);
                }
            }
            
            $redirect_url = $_SERVER['PHP_SELF'] . (isset($_GET['debug']) ? '?debug=1' : '');
            if (!empty($params)) {
                $redirect_url .= (strpos($redirect_url, '?') !== false ? '&' : '?') . implode('&', $params);
            }
            
            header("Location: $redirect_url");
            exit;
        }
    }
    
    // Cadastrar novo posto
    if (isset($_POST['cadastrar_posto'])) {
        $posto = $_POST['posto'];
        $regional = $_POST['regional'];
        $nome = $_POST['nome'];
        
        try {
            // Verificar se o posto já existe
            $stmt_check = $pdo_controle->prepare("SELECT COUNT(*) FROM ciRegionais WHERE posto = ?");
            $stmt_check->execute(array($posto));
            $existe = $stmt_check->fetchColumn();
            
            if ($existe) {
                $mensagem_erro = "O posto {$posto} já existe na tabela ciRegionais.";
            } else {
                // Inserir o novo posto
                $stmt_insert = $pdo_controle->prepare("INSERT INTO ciRegionais (regional, posto, nome) VALUES (?, ?, ?)");
                $stmt_insert->execute(array($regional, $posto, $nome));
                
                // Registrar na sessão que o posto foi cadastrado
                $_SESSION['postos_cadastrados'][] = $posto;
                
                $mensagem_sucesso = "Posto {$posto} cadastrado com sucesso!";
            }
        } catch (PDOException $e) {
            $mensagem_erro = "Erro ao cadastrar posto: " . $e->getMessage();
        }
    }
}

// Obter datas disponíveis
$stmt_datas = $pdo_controle->query("SELECT DISTINCT DATE(dataCarga) as data FROM ciPostosCsv WHERE dataCarga IS NOT NULL ORDER BY data DESC LIMIT 5");
$datas_expedicao = array();
while ($row = $stmt_datas->fetch(PDO::FETCH_ASSOC)) {
    $datas_expedicao[] = date('d-m-Y', strtotime($row['data']));
}

// Usar datas da sessão se existirem, senão usar as do GET, ou default
if (!empty($_SESSION['datas_filtro'])) {
    $datas_filtro = $_SESSION['datas_filtro'];
} else {
    $datas_filtro = isset($_GET['datas']) ? $_GET['datas'] : $datas_expedicao;
    // Salvar na sessão para uso futuro
    $_SESSION['datas_filtro'] = $datas_filtro;
}

// V7.9: Realizar análise de expedição com nova lógica de data
$analise_expedicao = analisar_expedicao($pdo_controle, $pdo_servico, $datas_filtro);

// Parâmetros do formulário
$lacre_capital = isset($_GET['lacre_capital']) ? (int)$_GET['lacre_capital'] : 1;
$lacre_central = isset($_GET['lacre_central']) ? (int)$_GET['lacre_central'] : 0;
$lacre_regionais = isset($_GET['lacre_regionais']) ? (int)$_GET['lacre_regionais'] : 0;
// Persistir os ultimos lacres iniciais na sessao para preservar valores
// quando ações POST (ex: adicionar posto manual) redirecionarem sem os GETs
$_SESSION['ultimo_lacre_capital'] = $lacre_capital;
$_SESSION['ultimo_lacre_central'] = $lacre_central;
$_SESSION['ultimo_lacre_regionais'] = $lacre_regionais;
// Persistir responsavel selecionado
$_SESSION['ultimo_responsavel'] = $responsavel;
$responsavel = isset($_GET['responsavel']) ? $_GET['responsavel'] : 'Responsável Não Informado';
$cliente = isset($_GET['cliente']) ? $_GET['cliente'] : 'Cliente Não Informado';
$data_geracao = date('d/m/Y');

// Verificar posto 001
$tem_posto_001 = false;

// Obter informações dos postos e regionais
$postos_regionais = array();
$regionais_info = array();

// Dados da tabela ciRegionais
$stmt_regionais = $pdo_controle->query("SELECT id, regional, posto, nome, entrega FROM ciRegionais ORDER BY regional, posto");
while ($row = $stmt_regionais->fetch(PDO::FETCH_ASSOC)) {
    $posto_num = str_pad((int)$row['posto'], 3, '0', STR_PAD_LEFT);
    $regional_num = str_pad((int)$row['regional'], 3, '0', STR_PAD_LEFT);
    
    // Armazenar informações do posto
    $postos_regionais[$posto_num] = array(
        'posto_numero' => $posto_num,
        'posto_nome' => $row['nome'],
        'regional' => $regional_num
    );
    $postos_regionais[$posto_num]['entrega'] = isset($row['entrega']) ? $row['entrega'] : null;
    
    // Armazenar informações da regional
    if ($posto_num == $regional_num) {
        $regionais_info[$regional_num] = array(
            'nome' => $row['nome']
        );
    }
}

// Converter datas do formato brasileiro para SQL
$sql_dates_array = array();
foreach ($datas_filtro as $data) {
    $partes = explode('-', $data);
    if (count($partes) === 3) {
        $sql_dates_array[] = "{$partes[2]}-{$partes[1]}-{$partes[0]}";
    }
}
$sql_datas = implode("','", $sql_dates_array);

// Consulta SQL para postos
$sql = "SELECT posto, regional, quantidade, dataCarga FROM ciPostosCsv
        WHERE DATE(dataCarga) IN ('$sql_datas')
        ORDER BY regional, lote, posto";

$stmt = $pdo_controle->query($sql);
$postos_visiveis = array();
$postos_nao_cadastrados = array();
$postos_processados = array();

// Verificar posto 002
$tem_posto_002 = false;

// Processar resultado da consulta
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $data_original = $row['dataCarga'];
    if (empty($data_original)) continue;
    
    $posto_num = str_pad((int)$row['posto'], 3, '0', STR_PAD_LEFT);
    
    // Verificar se é posto 001 ou 002
    if ($posto_num === '001') {
        $tem_posto_001 = true;
    }
    if ($posto_num === '002') {
        $tem_posto_002 = true;
    }
    
    // Ignorar posto já processado
    if (in_array($posto_num, $postos_processados)) continue;
    
    // Verificar se o posto foi removido no sistema tradicional
    if (isset($_SESSION['linhas_removidas'][$posto_num]) && $_SESSION['linhas_removidas'][$posto_num] === true) {
        continue;
    }
    
    $postos_processados[] = $posto_num;
    
    // Replicate array_column for PHP 5.3.3
    $postos_nao_cadastrados_postos = array();
    foreach ($postos_nao_cadastrados as $val) {
        $postos_nao_cadastrados_postos[] = $val['posto'];
    }

    // Verificar se o posto existe em ciRegionais
    if (!isset($postos_regionais[$posto_num]) && !in_array($posto_num, $_SESSION['postos_cadastrados'])) {
        if (!in_array($posto_num, $postos_nao_cadastrados_postos)) {
            $postos_nao_cadastrados[] = array(
                'posto' => $posto_num,
                'regional' => $row['regional']
            );
        }
    }
    
    // Determinar o tipo do posto
    $tipo = '';
    if (in_array($posto_num, $CAPITAL)) {
        $tipo = 'CAPITAL';
    } elseif (in_array($posto_num, $CENTRAL)) {
        $tipo = 'CENTRAL IIPR';
    } else {
        $tipo = 'REGIONAIS';
    }
    
    // Se 001 ou 002, forçar tipo CAPITAL
    if ($posto_num === '001' || $posto_num === '002') {
        $tipo = 'CAPITAL';
    }
    
    // Construir nome do posto
    if (isset($postos_regionais[$posto_num])) {
        $posto_nome = $postos_regionais[$posto_num]['posto_nome'];
        $regional_num = $postos_regionais[$posto_num]['regional'];
        $ent = isset($postos_regionais[$posto_num]['entrega']) ? $postos_regionais[$posto_num]['entrega'] : null;
        if ($ent && preg_match('/poupa\s*-?\s*tempo/i', $ent)) { $tipo = 'POUPA TEMPO'; }
        if ($regional_num === '0' || $regional_num === '00' || $regional_num === '000' || (int)$regional_num === 0) { $tipo = 'CAPITAL'; }
        if ($regional_num === '999' || $regional_num === '0999' || (int)$regional_num === 999) { $tipo = 'CENTRAL IIPR'; }

        
        // Regras de agrupamento por regional
        if ($regional_num === '0' || $regional_num === '00' || $regional_num === '000' || (int)$regional_num === 0) { $tipo = 'CAPITAL'; }
        if ($regional_num === '999' || $regional_num === '0999' || (int)$regional_num === 999) { $tipo = 'CENTRAL IIPR'; }
// Regra solicitada: qualquer posto com regional == 0 deve ser tratado como CAPITAL
        if ($regional_num === '0' || $regional_num === '00' || $regional_num === '000' || (int)$regional_num === 0) {
            $tipo = 'CAPITAL';
        }

        
        $regional_nome = null;
        if (isset($regionais_info[$regional_num])) {
            $regional_nome = $regionais_info[$regional_num]['nome'];
        }
    } else {
        $posto_nome = "Posto {$posto_num}";
        $regional_num = str_pad((int)$row['regional'], 3, '0', STR_PAD_LEFT);
        $regional_nome = null;
    }
    
    // Formatar nome do posto
    if ($tipo === 'CAPITAL') {
        $posto_nome = "{$posto_num} - {$posto_nome}";
    } elseif ($tipo === 'CENTRAL IIPR') {
        $posto_nome = "{$posto_num} - {$posto_nome}";
    } else { // REGIONAIS
        if ($regional_nome) {
            $posto_nome = "Posto {$regional_num} - " . preg_replace('/^Posto \d+ - /', '', $regional_nome);
        } else {
            $posto_nome = "Posto {$posto_num} - " . preg_replace('/^Posto \d+ - /', '', $posto_nome);
        }
    }

    // Registrar o posto
    if (!isset($postos_visiveis[$posto_num])) {
        $postos_visiveis[$posto_num] = array(
            'posto_codigo' => $posto_num,
            'posto_nome' => $posto_nome,
            'quantidade' => 0,
            'tipo' => $tipo,
            'regional' => $regional_num
        );
    }
    $postos_visiveis[$posto_num]['quantidade'] += (int)$row['quantidade'];
}

// Se temos o posto 002 mas não temos o 001, criar posto 001
if ($tem_posto_002 && !$tem_posto_001 && !isset($_SESSION['linhas_removidas']['001'])) {
    $posto_num = '001';
    $tipo = 'CAPITAL';
    
    if (isset($postos_regionais[$posto_num])) {
        $posto_nome = $postos_regionais[$posto_num]['posto_nome'];
    } else {
        $posto_nome = "POSTO SEDE - CENTRAL IIPR";
    }
    
    $postos_visiveis[$posto_num] = array(
        'posto_codigo' => $posto_num,
        'posto_nome' => "{$posto_num} - {$posto_nome}",
        'quantidade' => 1,
        'tipo' => $tipo,
        'regional' => '001'
    );
}

// Garantir que posto 001 seja CAPITAL
if (isset($postos_visiveis['001']) && $postos_visiveis['001']['tipo'] !== 'CAPITAL') {
    $postos_visiveis['001']['tipo'] = 'CAPITAL';
}

// Adicionar postos manuais
if (!empty($_SESSION['postos_manuais'])) {
    foreach ($_SESSION['postos_manuais'] as $codigo => $posto) {
        // Verificar exclusão no sistema tradicional
        if (isset($_SESSION['linhas_removidas'][$codigo]) && $_SESSION['linhas_removidas'][$codigo] === true) {
            continue;
        }
        
        $postos_visiveis[$codigo] = array(
            'posto_codigo' => $codigo,
            'posto_nome' => $posto['posto_nome'],
            'tipo' => $posto['tipo'],
            'quantidade' => isset($posto['quantidade']) ? $posto['quantidade'] : 1
        );
    }
}

// Dividir postos por categoria
$dados = array('POUPA TEMPO' => array(), 'CAPITAL' => array(), 'CENTRAL IIPR' => array(), 'REGIONAIS' => array());
foreach ($postos_visiveis as $posto) {
    $dados[$posto['tipo']][] = $posto;
}

// V7.9: Remover posto 002 se estiver presente quando houver posto 001
if ($tem_posto_001 && $tem_posto_002) {
    $dados['CAPITAL'] = array_filter($dados['CAPITAL'], function($posto) {
        return $posto['posto_codigo'] !== '002';
    });
}

// Remover postos 001 e 002 de REGIONAIS
$dados['REGIONAIS'] = array_filter($dados['REGIONAIS'], function($posto) {
    return $posto['posto_codigo'] !== '001' && $posto['posto_codigo'] !== '002';
});

// Ordenar cada grupo de postos pelo número do posto
foreach ($dados as $grupo_nome => &$grupo) {
    usort($grupo, function($a, $b) {
        // Se ambos começam com 'M', são postos manuais - ordenar pelo nome
        if (strpos($a['posto_codigo'], 'M') === 0 && strpos($b['posto_codigo'], 'M') === 0) {
            return strcmp($a['posto_nome'], $b['posto_nome']);
        }
        // Se só um é manual, o numérico vem primeiro
        if (strpos($a['posto_codigo'], 'M') === 0) return 1;
        if (strpos($b['posto_codigo'], 'M') === 0) return -1;
        // Caso contrário, ordenar numericamente
        if ((int)$a['posto_codigo'] < (int)$b['posto_codigo']) return -1;
        if ((int)$a['posto_codigo'] > (int)$b['posto_codigo']) return 1;
        return 0;
    });
}
unset($grupo);

// Remover duplicatas e ordenar REGIONAIS
$nomes_vistos = array();
$dados['REGIONAIS'] = array_filter($dados['REGIONAIS'], function($posto) use (&$nomes_vistos) {
    if ($posto['posto_codigo'] === '001' || $posto['posto_codigo'] === '002') {
        return false;
    }
    
    if (in_array($posto['posto_nome'], $nomes_vistos)) {
        return false;
    }
    $nomes_vistos[] = $posto['posto_nome'];
    return true;
});

// Ordenar regionais por número exibido no nome
usort($dados['REGIONAIS'], function($a, $b) {
    // Se são postos manuais, ordenar pelo nome
    if (strpos($a['posto_codigo'], 'M') === 0 && strpos($b['posto_codigo'], 'M') === 0) {
        return strcmp($a['posto_nome'], $b['posto_nome']);
    }
    
    // Se apenas um é manual, o numérico vem primeiro
    if (strpos($a['posto_codigo'], 'M') === 0) return 1;
    if (strpos($b['posto_codigo'], 'M') === 0) return -1;
    
    // Extrair números do nome para ordenação
    preg_match('/Posto (\d+)/', $a['posto_nome'], $matchA);
    preg_match('/Posto (\d+)/', $b['posto_nome'], $matchB);
    $numA = isset($matchA[1]) ? (int)$matchA[1] : 0;
    $numB = isset($matchB[1]) ? (int)$matchB[1] : 0;
    
    if ($numA < $numB) return -1;
    if ($numA > $numB) return 1;
    return 0;
});

// Remoção manual dos postos REGIONAIS marcados para exclusão
if (!empty($_SESSION['excluir_regionais_manual'])) {
    foreach ($_SESSION['excluir_regionais_manual'] as $codigo_remover) {
        // Procurar o posto nos REGIONAIS e remover
        foreach ($dados['REGIONAIS'] as $idx => $posto) {
            if ($posto['posto_codigo'] === $codigo_remover) {
                unset($dados['REGIONAIS'][$idx]);
                add_debug("Posto REGIONAL removido manualmente", $posto);
                break;
            }
        }
    }
    // Reindexar o array após remoções
    $dados['REGIONAIS'] = array_values($dados['REGIONAIS']);
}

// Verificação final para postos 001/002
foreach ($dados['REGIONAIS'] as $key => $posto) {
    if ($posto['posto_codigo'] === '001' ||
        $posto['posto_codigo'] === '002' ||
        strpos($posto['posto_nome'], 'Posto 001') !== false ||
        strpos($posto['posto_nome'], 'Posto 002') !== false) {
        unset($dados['REGIONAIS'][$key]);
    }
}
$dados['REGIONAIS'] = array_values($dados['REGIONAIS']);

// Atribuição de lacres
// Detectar se houve recálculo por lacre (campo hidden no form de filtro)
$recalculo_por_lacre = false;
if ((isset($_GET['recalculo_por_lacre']) && $_GET['recalculo_por_lacre'] === '1') || (isset($_POST['recalculo_por_lacre']) && $_POST['recalculo_por_lacre'] === '1')) {
    $recalculo_por_lacre = true;
}

// v8.13.4: Inicializadores padrão ZERADOS (não preencher automaticamente)
// Usuário deve digitar lacres iniciais nos inputs do topo para cada grupo
$lacre_atual_capital = $lacre_capital;
$lacre_atual_central = $lacre_central;
$lacre_atual_regionais = $lacre_regionais;
$ultimo_central = null;

// Se houve recálculo por lacre, iremos sobrescrever totalmente os valores
// por grupo (CAPITAL, CENTRAL IIPR, REGIONAIS) abaixo, ignorando valores
// anteriores em sessão ou base.

// v8.13.4 CAPITAL: Só preenche se usuário digitou lacre inicial (>0)
// - Primeira linha: lacre_iipr=N, lacre_correios=N+1
// - Segunda linha: lacre_iipr=N+2, lacre_correios=N+3
// - Exemplo: lacre inicial=18 → linhas: 18/19, 20/21, 22/23...
// - Se lacre_capital=0 ou vazio: deixa TODOS os inputs em branco
if ($recalculo_por_lacre && (int)$lacre_capital > 0) {
    $lacre_iipr_cur = (int)$lacre_capital;
    $lacre_corr_cur = $lacre_iipr_cur + 1;
    foreach ($dados['CAPITAL'] as &$linha) {
        $indice = $linha['posto_codigo'];
        $linha['lacre_iipr'] = $lacre_iipr_cur;
        $linha['lacre_correios'] = $lacre_corr_cur;
        // v8.13.4: Garantir que IIPR e Correios são SEMPRE distintos
        if ($linha['lacre_iipr'] === $linha['lacre_correios']) {
            $linha['lacre_correios'] = $linha['lacre_iipr'] + 1;
        }
        $lacre_iipr_cur += 2;
        $lacre_corr_cur += 2;
    }
    unset($linha);
} else {
    // Sem recálculo: usar lacres personalizados da sessão ou manter existentes
    foreach ($dados['CAPITAL'] as &$linha) {
        $indice = $linha['posto_codigo'];
        // Garantir valores padrão para evitar Notice
        if (!isset($linha['lacre_iipr'])) {
            $linha['lacre_iipr'] = '';
        }
        if (!isset($linha['lacre_correios'])) {
            $linha['lacre_correios'] = '';
        }
        // Sobrescrever com valores personalizados se existirem
        if (isset($_SESSION['lacres_personalizados'][$indice]['iipr'])) {
            $linha['lacre_iipr'] = $_SESSION['lacres_personalizados'][$indice]['iipr'];
        }
        if (isset($_SESSION['lacres_personalizados'][$indice]['correios'])) {
            $linha['lacre_correios'] = $_SESSION['lacres_personalizados'][$indice]['correios'];
        }
    }
    unset($linha);
}

// v8.13.4 CENTRAL IIPR: Só preenche se usuário digitou lacre inicial (>0)
// - Lacres IIPR sequenciais (+1): 5, 6, 7, 8, 9, 10, 11...
// - Lacre Correios: ÚLTIMO lacre IIPR + 1 (aplicado a TODOS os postos do grupo)
// - Exemplo: 7 postos com lacre inicial=5 → IIPR: 5,6,7,8,9,10,11 | Correios (todos): 12
// - Com SPLITs: cada grupo visual tem seu próprio lacre Correios = max(IIPR_grupo) + 1
// - Se lacre_central=0 ou vazio: deixa TODOS os inputs em branco
if ($recalculo_por_lacre && (int)$lacre_central > 0) {
    $lacre_iipr_cur = (int)$lacre_central;
    foreach ($dados['CENTRAL IIPR'] as &$linha) {
        $indice = $linha['posto_codigo'];
        $linha['lacre_iipr'] = $lacre_iipr_cur;
        $ultimo_central = $lacre_iipr_cur;  // Atualiza o último IIPR gerado
        $lacre_iipr_cur += 1;  // Incremento sequencial +1
    }
    unset($linha);
} else {
    // Sem recálculo: usar lacres personalizados da sessão ou manter existentes
    foreach ($dados['CENTRAL IIPR'] as &$linha) {
        $indice = $linha['posto_codigo'];
        // Garantir valor padrão para evitar Notice
        if (!isset($linha['lacre_iipr'])) {
            $linha['lacre_iipr'] = '';
        }
        // Sobrescrever com valor personalizado se existir
        if (isset($_SESSION['lacres_personalizados'][$indice]['iipr'])) {
            $linha['lacre_iipr'] = $_SESSION['lacres_personalizados'][$indice]['iipr'];
        }
        // Atualizar $ultimo_central para ser usado no bloco seguinte
        if (isset($linha['lacre_iipr']) && $linha['lacre_iipr'] !== '') {
            $ultimo_central = $linha['lacre_iipr'];
        }
    }
    unset($linha);
}

// v8.13.4 CENTRAL IIPR: Atribuir lacre Correios (GARANTIDO: sempre último+1, nunca duplicado)
// - Com recalculo_por_lacre: TODOS os postos recebem o ÚLTIMO lacre IIPR + 1
// - Sem recalculo_por_lacre: Respeita splits/malotes por grupo (compatibilidade)
// - Exemplo: último IIPR=11 → lacre Correios de todos=12 (NUNCA 11)
// - Validação: lacre Correios NUNCA pode ser igual ao último IIPR
if (!empty($dados['CENTRAL IIPR']) && $ultimo_central !== null) {
    if ($recalculo_por_lacre && (int)$lacre_central > 0) {
        // v8.13.4: GARANTIDO - último IIPR + 1 vira lacre Correios de TODOS
        $lacreCorreiosCentral = $ultimo_central + 1;
        // v8.13.4: Validação extra - garantir que Correios ≠ último IIPR
        if ($lacreCorreiosCentral === $ultimo_central) {
            $lacreCorreiosCentral = $ultimo_central + 1;
        }
        foreach ($dados['CENTRAL IIPR'] as &$linha) {
            $linha['lacre_correios'] = $lacreCorreiosCentral;
        }
        unset($linha);
    } else {
        // Sem recálculo: manter lógica de SPLIT/malotes (para compatibilidade)
        // Construir mapeamento de grupos para central com base em $splitsCentral
        $central_groups = array(); // grupo => array(posicoes)
        $central_group_by_posto = array(); // posto_codigo => grupo
        $group_index = 0;
        foreach ($dados['CENTRAL IIPR'] as $idx => $linha) {
            $posto_code = $linha['posto_codigo'];
            // Se este posto está configurado como SPLIT, inicia novo grupo *antes* de atribuir
            if (!empty($splitsCentral) && in_array($posto_code, $splitsCentral)) {
                $group_index++;
            }
            if (!isset($central_groups[$group_index])) $central_groups[$group_index] = array();
            $central_groups[$group_index][] = $posto_code;
            $central_group_by_posto[$posto_code] = $group_index;
        }

        // v8.13.3: Gerar lacre Correios por grupo = max(lacre_iipr_grupo) + 1
        // Cada grupo visual (separado por SPLIT) tem seu próprio lacre Correios
        $group_lacres = array();
        foreach ($central_groups as $g => $postos_grupo) {
            $max_iipr_grupo = 0;
            // Encontrar maior lacre_iipr do grupo
            foreach ($dados['CENTRAL IIPR'] as $linha_central) {
                if (in_array($linha_central['posto_codigo'], $postos_grupo)) {
                    $iipr_val = isset($linha_central['lacre_iipr']) && $linha_central['lacre_iipr'] !== '' ? (int)$linha_central['lacre_iipr'] : 0;
                    if ($iipr_val > $max_iipr_grupo) {
                        $max_iipr_grupo = $iipr_val;
                    }
                }
            }
            // Lacre Correios do grupo = max(IIPR) + 1
            $group_lacres[$g] = $max_iipr_grupo + 1;
        }

        // Atribuir lacre_correios a cada linha de acordo com seu grupo ou sessão
        foreach ($dados['CENTRAL IIPR'] as &$linha) {
            $indice = $linha['posto_codigo'];
            // Garantir valor padrão
            if (!isset($linha['lacre_correios'])) {
                $linha['lacre_correios'] = '';
            }
            // Sobrescrever com valor personalizado ou calculado
            if (isset($_SESSION['lacres_personalizados'][$indice]['correios'])) {
                $linha['lacre_correios'] = $_SESSION['lacres_personalizados'][$indice]['correios'];
            } else {
                $gidx = isset($central_group_by_posto[$indice]) ? $central_group_by_posto[$indice] : 0;
                $linha['lacre_correios'] = isset($group_lacres[$gidx]) ? $group_lacres[$gidx] : $base_lacre;
            }
        }
        unset($linha);

        // Expor variáveis úteis para a renderização (template abaixo usa estas informações)
        $central_group_first = array();
        foreach ($central_groups as $g => $posts) {
            if (!empty($posts)) {
                $first = $posts[0];
                $central_group_first[$first] = true;
                foreach ($posts as $p) {
                    if ($p !== $first && !isset($central_group_first[$p])) {
                        $central_group_first[$p] = false;
                    }
                }
            }
        }
    }
}

// v8.13.4 REGIONAIS: Só preenche se usuário digitou lacre inicial (>0)
// - Cada linha representa uma regional com par de lacres SEMPRE diferentes
// - Esses lacres serão aplicados a TODOS os postos daquela regional ao salvar
// - Lacre IIPR e Lacre Correios DEVEM ser diferentes (ex: 5/6, não 5/5)
// - Se lacre_regionais=0 ou vazio: deixa TODOS os inputs em branco
if ($recalculo_por_lacre && (int)$lacre_regionais > 0) {
    $lacre_iipr_cur = (int)$lacre_regionais;
    $lacre_corr_cur = $lacre_iipr_cur + 1;
    foreach ($dados['REGIONAIS'] as &$linha) {
        $indice = $linha['posto_codigo'];
        $linha['lacre_iipr'] = $lacre_iipr_cur;
        $linha['lacre_correios'] = $lacre_corr_cur;
        // v8.13.4: Garantir que IIPR e Correios são SEMPRE distintos
        if ($linha['lacre_iipr'] === $linha['lacre_correios']) {
            $linha['lacre_correios'] = $linha['lacre_iipr'] + 1;
        }
        $lacre_iipr_cur += 2;
        $lacre_corr_cur += 2;
    }
    unset($linha);
} else {
    // Sem recálculo: usar lacres personalizados da sessão ou manter existentes
    foreach ($dados['REGIONAIS'] as &$linha) {
        $indice = $linha['posto_codigo'];
        // Garantir valores padrão para evitar Notice
        if (!isset($linha['lacre_iipr'])) {
            $linha['lacre_iipr'] = '';
        }
        if (!isset($linha['lacre_correios'])) {
            $linha['lacre_correios'] = '';
        }
        // Sobrescrever com valores personalizados se existirem
        if (isset($_SESSION['lacres_personalizados'][$indice]['iipr'])) {
            $linha['lacre_iipr'] = $_SESSION['lacres_personalizados'][$indice]['iipr'];
        }
        if (isset($_SESSION['lacres_personalizados'][$indice]['correios'])) {
            $linha['lacre_correios'] = $_SESSION['lacres_personalizados'][$indice]['correios'];
        }
    }
    unset($linha);
}

// Lista de regionais para dropdown
$todas_regionais = array();
foreach ($regionais_info as $num => $info) {
    $todas_regionais[$num] = $info['nome'];
}

// Debug - Informações sobre a sessão para ajudar na depuração
$mostrar_debug = isset($_GET['debug']) && $_GET['debug'] === '1';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Ofício Lacres V8.2 - <?php echo date('d/m/Y') ?></title>
    <style>
        :root {
            --font-size-base: 12px;
            --font-size-large: 14px;
            --font-size-xlarge: 16px;
        }
        
        .somente-impressao { display: none; }
        body {
            font-family: Arial, sans-serif;
            font-size: var(--font-size-base);
            margin: 15px;
            transition: font-size 0.3s ease;
        }
        body.zoom-level-1 { font-size: var(--font-size-large); }
        body.zoom-level-2 { font-size: var(--font-size-xlarge); }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid black; padding: 3px; text-align: center; }
        th:nth-child(1), td:nth-child(1) { width: 35%; text-align: left; }
        th:nth-child(2), td:nth-child(2) { width: 12%; }
        th:nth-child(3), td:nth-child(3) { width: 12%; }
        th:nth-child(4), td:nth-child(4) { width: 28%; }
        th:nth-child(5), td:nth-child(5) { width: 13%; }
        th { background-color: #f0f0f0; }
        input[type='text'] { width: 240px; font-family: monospace; text-align: center; font-size: inherit; }
        input.lacre { width: 80px; text-align: center; font-weight: bold }
        .quadro { border: 1px solid black; padding: 6px; margin-bottom: 8px; }
        .topo-formulario { display: flex; flex-wrap: wrap; gap: 12px; }
        .topo-formulario label { display: flex; flex-direction: column; }
        .alinhado { display: flex; align-items: center; gap: 8px; margin-top: 4px; }
        
        .assinaturas {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            page-break-inside: avoid;
            width: 100%;
        }
        .assinatura-esquerda, .assinatura-direita {
            width: 45%;
            text-align: center;
        }
        .assinatura-esquerda hr, .assinatura-direita hr {
            width: 80%;
            margin: 5px auto;
            border: 1px solid #000;
        }
        .assinatura-esquerda p, .assinatura-direita p {
            margin: 10px 0;
            font-size: 11px;
        }
        
        /* Botão de zoom */
        .zoom-control {
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 1000;
            display: flex;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .zoom-btn {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 3px;
            padding: 5px 10px;
            margin: 0 2px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .zoom-btn:hover {
            background: #e9ecef;
        }
        
        /* Alertas */
        .alerta {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
            border-radius: 4px;
            padding: 10px 15px;
            margin-bottom: 15px;
        }
        .alerta-titulo {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .alerta-lista {
            margin: 5px 0;
            padding-left: 20px;
        }
        
        /* Mensagens */
        .mensagem-sucesso {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
            padding: 10px 15px;
            margin-bottom: 15px;
        }
        .mensagem-erro {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            padding: 10px 15px;
            margin-bottom: 15px;
        }
        
        /* V7.9: Estilos para análise de expedição */
        .analise-expedicao {
            background-color: #e3f2fd;
            border: 2px solid #2196f3;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .analise-expedicao h3 {
            margin-top: 0;
            color: #1565c0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .analise-expedicao .icone {
            font-size: 24px;
        }
        .analise-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .analise-item {
            background-color: white;
            border: 1px solid #bbdefb;
            border-radius: 5px;
            padding: 12px;
        }
        .analise-item h4 {
            margin-top: 0;
            margin-bottom: 8px;
            color: #1565c0;
            font-size: 14px;
        }
        .analise-valor {
            font-size: 18px;
            font-weight: bold;
            color: #0d47a1;
        }
        .analise-diferenca {
            color: #d32f2f;
            font-weight: bold;
        }
        .analise-diferenca.positiva {
            color: #388e3c;
        }
        .autores-faltantes {
            background-color: #fff3e0;
            border: 1px solid #ffcc02;
            border-radius: 5px;
            padding: 10px;
            margin-top: 10px;
        }
        .autores-faltantes h5 {
            margin-top: 0;
            color: #e65100;
        }
        .autor-item {
            background-color: #ffe0b2;
            padding: 5px 8px;
            margin: 5px 0;
            border-radius: 3px;
            font-size: 12px;
        }
        .postos-retirados {
            background-color: #e8f5e8;
            border: 1px solid #4caf50;
            border-radius: 5px;
            padding: 10px;
            margin-top: 10px;
        }
        .postos-retirados h5 {
            margin-top: 0;
            color: #2e7d32;
        }
        .posto-retirado {
            background-color: #c8e6c9;
            padding: 5px 8px;
            margin: 5px 0;
            border-radius: 3px;
            font-size: 12px;
            display: inline-block;
            margin-right: 10px;
        }
        
        /* V7.9: Detalhes de expedição por data */
        .detalhes-expedicao {
            background-color: #f3e5f5;
            border: 1px solid #ce93d8;
            border-radius: 5px;
            padding: 10px;
            margin-top: 10px;
        }
        .detalhes-expedicao h5 {
            margin-top: 0;
            color: #4a148c;
        }
        .detalhe-item {
            background-color: #e1bee7;
            padding: 5px 8px;
            margin: 5px 0;
            border-radius: 3px;
            font-size: 12px;
        }
        
        /* Alerta de duplicata */
        .alerta-duplicata {
            background-color: #f8d7da;
            color: #721c24;
            border: 2px solid #dc3545;
            border-radius: 4px;
            padding: 8px;
            font-size: 12px;
            margin-top: 5px;
            display: none;
        }
        
        /* Formulário de cadastro */
        .cadastro-posto {
            background-color: #e9ecef;
            border: 1px solid #ced4da;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .cadastro-posto h3 {
            margin-top: 0;
            margin-bottom: 10px;
            color: #495057;
        }
        .form-cadastro {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: flex-end;
        }
        .form-cadastro label {
            display: flex;
            flex-direction: column;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .form-cadastro select,
        .form-cadastro input {
            margin-top: 3px;
            padding: 5px;
            border: 1px solid #ced4da;
            border-radius: 3px;
        }
        .btn-cadastrar {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 6px 12px;
            cursor: pointer;
            font-size: 12px;
            border-radius: 3px;
        }
        .btn-cadastrar:hover {
            background-color: #0069d9;
        }
                
        /* Adição manual */
        .quadro-adicionar {
            background-color: #f8f8f8;
            border: 1px solid #ddd;
            padding: 10px;
            margin-top: 20px;
            margin-bottom: 15px;
        }
        .quadro-adicionar h3 {
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .form-adicionar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: flex-end;
        }
        .form-adicionar label {
            display: flex;
            flex-direction: column;
            font-size: 12px;
            font-weight: bold;
        }
        .form-adicionar select,
        .form-adicionar input {
            margin-top: 3px;
            padding: 4px;
            border: 1px solid #ccc;
        }
        .btn-adicionar {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            font-size: 12px;
            border-radius: 3px;
        }
        .btn-adicionar:hover {
            background-color: #45a049;
        }
        
        /* Botões da tabela */
        .btn-add-above, .btn-add-below {
            background-color: #2196F3;
            color: white;
            border: none;
            padding: 3px 6px;
            margin: 2px;
            font-size: 10px;
            cursor: pointer;
            border-radius: 3px;
        }
        .btn-add-above:hover, .btn-add-below:hover {
            background-color: #0b7dda;
        }
        
        /* Botão excluir */
        .btn-excluir {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 3px 6px;
            margin: 2px;
            font-size: 10px;
            cursor: pointer;
            border-radius: 3px;
        }
        .btn-excluir:hover {
            background-color: #c82333;
        }
        
        /* Botao para limpar coluna */
        .btn-limpar-coluna {
            background-color: #ff6b6b;
            color: white;
            border: none;
            padding: 2px 6px;
            margin-left: 5px;
            font-size: 10px;
            font-weight: bold;
            cursor: pointer;
            border-radius: 3px;
            vertical-align: middle;
        }
        .btn-limpar-coluna:hover {
            background-color: #ee5a5a;
        }
        
        /* Animacao pulsante para botoes de salvamento */
        @keyframes pulsar {
            0%, 100% { 
                box-shadow: 0 0 5px rgba(255, 193, 7, 0.5);
                transform: scale(1);
            }
            50% { 
                box-shadow: 0 0 20px rgba(255, 193, 7, 0.8);
                transform: scale(1.02);
            }
        }
        
        .btn-pulsando {
            animation: pulsar 1.5s ease-in-out infinite;
        }
        
        .btn-salvo {
            animation: none !important;
            box-shadow: 0 0 10px rgba(40, 167, 69, 0.5) !important;
        }
        
        /* Botão excluir específico para REGIONAIS */
        .btn-excluir-regional {
            background-color: #9C27B0;
            color: white;
            border: none;
            padding: 3px 6px;
            margin: 2px;
            font-size: 10px;
            cursor: pointer;
            border-radius: 3px;
            font-weight: bold;
        }
        .btn-excluir-regional:hover {
            background-color: #7B1FA2;
        }
        
        /* Botão imprimir */
        .btn-imprimir {
            background-color: #17a2b8;
            color: white;
            border: none;
            padding: 8px 16px;
            margin: 10px 0;
            font-size: 14px;
            cursor: pointer;
            border-radius: 3px;
            display: inline-block;
        }
        .btn-imprimir i {
            margin-right: 5px;
        }
        .btn-imprimir:hover {
            background-color: #138496;
        }
        
        /* Botão salvar etiquetas */
        .btn-salvar-etiquetas {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 8px 16px;
            margin: 10px 0 10px 10px;
            font-size: 14px;
            cursor: pointer;
            border-radius: 3px;
            display: inline-block;
        }
        .btn-salvar-etiquetas i {
            margin-right: 5px;
        }
        .btn-salvar-etiquetas:hover {
            background-color: #218838;
        }
        
        /* Modal inserção */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .modal-overlay.active {
            display: flex;
        }
        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            width: 80%;
            max-width: 500px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .modal-title {
            margin-top: 0;
            color: #343a40;
        }
        .modal-form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .modal-form label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .modal-form input, .modal-form select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .modal-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 15px;
        }
        .modal-btn {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .modal-btn-cancel {
            background-color: #6c757d;
            color: white;
        }
        .modal-btn-save {
            background-color: #28a745;
            color: white;
        }
        
        /* Modal confirmação salvamento */
        .modal-confirmacao {
            background-color: white;
            padding: 25px;
            border-radius: 8px;
            width: 90%;
            max-width: 450px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        .modal-confirmacao h3 {
            margin-top: 0;
            color: #155724;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .modal-confirmacao .icone {
            font-size: 24px;
        }
        .modal-confirmacao p {
            margin: 15px 0;
            color: #495057;
        }
        .modal-confirmacao .form-group {
            margin: 15px 0;
        }
        .modal-confirmacao label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #495057;
        }
        .modal-confirmacao input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
        }
        .modal-confirmacao .btn-group {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }
        .modal-confirmacao .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
        }
        .modal-confirmacao .btn-cancelar {
            background-color: #6c757d;
            color: white;
        }
        .modal-confirmacao .btn-confirmar {
            background-color: #28a745;
            color: white;
        }
        
        /* Info reorganização */
        .reorg-info {
            margin-top: 10px;
            padding: 8px;
            background-color: #e3f2fd;
            border: 1px solid #bbdefb;
            border-radius: 4px;
            font-size: 12px;
            color: #0d47a1;
        }
        
        /* Novo aviso regionais */
        .regionais-info {
            margin-top: 10px;
            padding: 8px;
            background-color: #f3e5f5;
            border: 1px solid #ce93d8;
            border-radius: 4px;
            font-size: 12px;
            color: #4a148c;
        }
        
        /* Info validação duplicatas */
        .duplicata-info {
            margin-top: 10px;
            padding: 8px;
            background-color: #fff3e0;
            border: 1px solid #ffcc02;
            border-radius: 4px;
            font-size: 12px;
            color: #e65100;
        }
        
        /* V7.9: Info nova lógica de data */
        .data-info {
            margin-top: 10px;
            padding: 8px;
            background-color: #e8f5e8;
            border: 1px solid #4caf50;
            border-radius: 4px;
            font-size: 12px;
            color: #2e7d32;
        }
        
        /* Versão info */
        .version-info {
            position: fixed;
            left: 10px;
            bottom: 10px;
            font-size: 11px;
            color: #6c757d;
            background-color: rgba(255, 255, 255, 0.8);
            padding: 3px 6px;
            border-radius: 3px;
        }
        
        /* Debug info */
        .debug-info {
            margin-top: 30px;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }
        .debug-info h3 {
            margin-top: 0;
            color: #343a40;
        }
        .debug-info pre {
            font-size: 12px;
            background-color: #e9ecef;
            padding: 10px;
            border-radius: 3px;
            max-height: 300px;
            overflow: auto;
        }

        @media print {
            .somente-impressao { display: block !important; }
            .btn-limpar { display: none !important; }
            .btn-imprimir { display: none !important; }
            .btn-salvar-etiquetas { display: none !important; }
            .no-print { display: none !important; }
            .quadro-formulario, .quadro-formulario * { display: none !important; }
            .quadro-adicionar, .quadro-adicionar * { display: none !important; }
            .alerta, .alerta * { display: none !important; }
            .cadastro-posto, .cadastro-posto * { display: none !important; }
            .mensagem-sucesso, .mensagem-erro { display: none !important; }
            .zoom-control { display: none !important; }
            .reorg-info { display: none !important; }
            .regionais-info { display: none !important; }
            .duplicata-info { display: none !important; }
            .data-info { display: none !important; }
            .version-info { display: none !important; }
            .debug-info { display: none !important; }
            .modal-overlay { display: none !important; }
            .alerta-duplicata { display: none !important; }
            .analise-expedicao { display: none !important; }
            
            /* V7.9: MELHORIAS DEFINITIVAS para impressão sem sobreposição */
            input.etiqueta-barras, input.lacre {
                all: unset !important;
                font-family: 'Courier New', 'Monaco', 'Lucida Console', monospace !important;
                font-size: 12px !important; /* V7.9: Mesmo tamanho da fonte dos nomes dos postos */
                font-weight: bold !important;
                background: transparent !important;
                border: none !important;
                display: inline-block !important;
                box-shadow: none !important;
                appearance: none !important;
                -webkit-appearance: none !important;
                outline: none !important;
                white-space: nowrap !important;
                overflow: visible !important;
                word-break: keep-all !important;
                width: auto !important;
                letter-spacing: 0px !important; /* V7.9: Removido letter-spacing negativo */
                line-height: 1.2 !important;
                padding: 0 !important;
                margin: 0 !important;
                vertical-align: baseline !important;
                text-overflow: visible !important;
                -webkit-text-size-adjust: none !important;
                -moz-text-size-adjust: none !important;
                text-size-adjust: none !important;
            }
            
            /* V7.9: Lacres IIPR e Correios com tamanho aumentado e sem sobreposição */
            input.lacre {
                font-size: 12px !important; /* V7.9: Mesmo tamanho dos nomes dos postos */
                font-weight: bold !important; /* deixar lacres em negrito*/
                min-width: 85px !important; /* V7.9: Aumentado para evitar sobreposição */
                max-width: 85px !important;
                text-align: center !important;
                padding-right: 5px !important; /* V7.9: Espaçamento à direita */
            }
            
            /* V7.9: Etiquetas com tamanho aumentado */
            input.etiqueta-barras {
                font-size: 12px !important; /* V7.9: Mesmo tamanho dos nomes dos postos */
                min-width: 280px !important; /* V7.9: Aumentado para acomodar 35 caracteres */
                max-width: 280px !important;
                text-align: left !important;
                padding-left: 5px !important; /* V7.9: Espaçamento à esquerda */
            }
            
            /* V7.9: Ajustar colunas para evitar sobreposição */
            th:nth-child(1), td:nth-child(1) { width: 30% !important; font-size: 12px !important; }
            th:nth-child(2), td:nth-child(2) { width: 12% !important; padding: 2px 5px !important; }
            th:nth-child(3), td:nth-child(3) { width: 12% !important; padding: 2px 5px !important; }
            th:nth-child(4), td:nth-child(4) {
                width: 35% !important;
                min-width: 290px !important;
                padding: 2px 5px !important;
                word-wrap: break-word !important;
                overflow-wrap: break-word !important;
            }
            th:nth-child(5), td:nth-child(5) { width: 11% !important; }
            
            /* Esconder coluna de ações */
            .btn-excluir, .btn-excluir-regional, .btn-add-above, .btn-add-below, th:last-child, td:last-child { display: none !important; }
            
            /* Garantir que o logo fique bem formatado */
            .quadro-logo {line-height: 1.0; border: 1px solid black !important; padding: 12px !important; margin-bottom: 15px !important; box-sizing: border-box !important; }
            .info-cliente {line-height: 1.0; border: 1px solid black !important; padding: 10px !important; margin-bottom: 0px !important; box-sizing: border-box !important; }
            
            /* Reset de tamanho de fonte para impressão */
            body {
                margin-bottom: 5px !important;
                font-size: 12px !important; /* Reseta para o tamanho normal na impressão */
            }
            
            /* Margem da página impressa */
            @page {
                margin: 5mm 8mm 5mm 8mm;
                size: A4;
            }
            
            /* Garantir que as assinaturas fiquem lado a lado */
            .assinaturas {
                display: flex !important;
                justify-content: space-between !important;
                width: 100% !important;
            }
            .assinatura-esquerda, .assinatura-direita {
                width: 45% !important;
                display: block !important;
            }
            
            /* Ajustar tabela para caber na página */
            table {
                width: 100% !important;
                table-layout: fixed !important;
                border-collapse: collapse !important;
            }
        }
        
        /* V8.1: Novos estilos para interface melhorada */
        .painel-inserir-dados {
            background-color: #f8f9fa;
            border: 2px solid #007bff;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            display: none; /* V8.1: Escondido por padrão */
        }
        
        .painel-inserir-dados.ativo {
            display: block;
        }
        
        .btn-mostrar-painel {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 11px;
            margin-top: 5px;
        }
        
        .btn-mostrar-painel:hover {
            background-color: #218838;
        }
        
        .painel-inserir-dados h3 {
            color: #007bff;
            margin-top: 0;
            margin-bottom: 15px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }
        
        .form-group input, .form-group select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: inherit;
        }
        
        .btn-inserir {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: inherit;
            font-weight: bold;
        }
        
        .btn-inserir:hover {
            background-color: #218838;
        }
        
        .painel-analise {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            margin: 15px 0;
        }
        
        .painel-analise-header {
            background-color: #ffc107;
            padding: 10px 15px;
            margin: 0;
            cursor: pointer;
            user-select: none;
            border-radius: 6px 6px 0 0;
            font-weight: bold;
        }
        
        .painel-analise-header:hover {
            background-color: #e0a800;
        }
        
        .painel-analise-content {
            padding: 15px;
            display: block;
        }
        
        .painel-analise.collapsed .painel-analise-content {
            display: none;
        }
        
        .toggle-icon {
            float: right;
            transition: transform 0.3s ease;
        }
        
        .painel-analise.collapsed .toggle-icon {
            transform: rotate(-90deg);
        }
        
        /* Autocomplete */
        .autocomplete-container {
            position: relative;
        }
        
        .autocomplete-list {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-top: none;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }
        
        .autocomplete-item {
            padding: 8px 12px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }
        
        .autocomplete-item:hover,
        .autocomplete-item.selected {
            background-color: #007bff;
            color: white;
        }
        
        /* V8.1: Mensagens que desaparecem automaticamente */
        .mensagem-auto {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            padding: 15px 20px;
            border-radius: 5px;
            font-weight: bold;
            animation: slideIn 0.3s ease-out;
        }
        
        .mensagem-auto.sucesso {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .mensagem-auto.erro {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .mensagem-auto.fadeOut {
            animation: fadeOut 0.5s ease-out forwards;
        }
        
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; transform: translateX(100%); }
        }
        
        /* V8.1: Painel de análise mais compacto quando recolhido */
        .painel-analise.collapsed {
            margin: 5px 0;
        }
        
        .painel-analise.collapsed .painel-analise-header {
            font-size: 14px;
            padding: 8px 15px;
        }
        
        @media print {
            .painel-inserir-dados,
            .painel-analise.collapsed,
            .mensagem-auto,
            .btn-mostrar-painel,
            #tabela-poupa-tempo,
            table[data-grupo="POUPA TEMPO"],
            .btn-limpar-coluna,
            .btn-limpar-coluna-header,
            button.btn-limpar,
            th button {
                display: none !important;
            }
            
            .painel-analise:not(.collapsed) {
                page-break-before: always;
            }
        }
    </style>

<!-- PATCH v2.2.2 -->
<style>
.btn-oficio-pt{background:#6c63ff!important;border-color:#6c63ff!important;color:#fff!important;}
.btn-oficio-pt:hover{filter:brightness(.95);}
.btn-oficio-pt i{margin-right:6px;}
</style>
</head>
<body>

<div class="zoom-control">
    <button class="zoom-btn" id="zoom-in" title="Aumentar texto">A<sup>+</sup></button>
    <button class="zoom-btn" id="zoom-out" title="Diminuir texto">A<sup>−</sup></button>
</div>

<div class="version-info">Versão 0.8.2</div>

<?php if (!empty($mensagem_sucesso)): ?>
<div class="mensagem-auto sucesso" id="mensagem-auto">
    <?php echo htmlspecialchars($mensagem_sucesso) ?>
</div>
<?php endif; ?>

<?php if (!empty($mensagem_erro)): ?>
<div class="mensagem-auto erro" id="mensagem-auto">
    <?php echo htmlspecialchars($mensagem_erro) ?>
</div>
<?php endif; ?>

<div class="painel-analise" id="painel-analise">
    <div class="painel-analise-header" onclick="toggleAnalisePanel()">
        <span class="icone">📊</span> Análise de Expedição (V8.0)
        <span class="toggle-icon">▼</span>
    </div>
    <div class="painel-analise-content">
    <p><strong>Para a(s) data(s) escolhida(s):</strong> <?php echo implode(', ', $datas_filtro) ?></p>
    
    <div class="analise-grid">
        <div class="analise-item">
            <h4>Total de Carteiras Expedidas</h4>
            <div class="analise-valor"><?php echo number_format($analise_expedicao['total_carteiras']) ?></div>
            
        </div>
        
        <div class="analise-item">
            <h4>Total de Carteiras com Upload</h4>
            <div class="analise-valor"><?php echo number_format($analise_expedicao['total_postos']) ?></div>
            
        </div>
        
        <div class="analise-item">
            <h4>Diferença</h4>
            <div class="analise-valor <?php echo $analise_expedicao['diferenca'] >= 0 ? 'analise-diferenca positiva' : 'analise-diferenca' ?>">
                <?php echo $analise_expedicao['diferenca'] > 0 ? '+' : '' ?><?php echo number_format($analise_expedicao['diferenca']) ?>
            </div>
            <small><?php echo $analise_expedicao['diferenca'] >= 0 ? 'Expedição maior que uploads' : 'Uploads maior que expedição' ?></small>
            <?php if ($analise_expedicao['diferenca'] != 0): ?>
            <button class="btn-mostrar-painel" onclick="togglePainelInsercao()">
                Inserir Dados
            </button>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($analise_expedicao['postos_retirados'])): ?>
        <div class="analise-item">
            <h4>Postos com Retirada</h4>
            <div class="analise-valor"><?php echo count($analise_expedicao['postos_retirados']) ?></div>
            
        </div>
        <?php endif; ?>
    </div>
    
    <?php if (!empty($analise_expedicao['detalhes_expedicao'])): ?>
    <div class="detalhes-expedicao">
        <h5>📋 Detalhamento da produção:</h5>
        <?php foreach ($analise_expedicao['detalhes_expedicao'] as $detalhe): ?>
            <div class="detalhe-item">
                <strong><?php echo $detalhe['data'] ?></strong> -
                <?php echo number_format($detalhe['expedidas']) ?> carteiras expedidas às <?php echo $detalhe['hora'] ?>h
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($analise_expedicao['autores_faltantes'])): ?>
    <div class="autores-faltantes">
        <h5>⚠️ Autores em ci-expedidas que não aparecem em ciPostosCsv:</h5>
        <?php foreach ($analise_expedicao['autores_faltantes'] as $autor_info): ?>
            <div class="autor-item">
                <strong><?php echo htmlspecialchars($autor_info['autor']) ?></strong> -
                Quantidade faltante: <?php echo number_format($autor_info['quantidade']) ?>
            </div>
        <?php endforeach; ?>
        <p><strong>Total faltante:</strong> <?php echo number_format($analise_expedicao['total_faltante']) ?></p>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($analise_expedicao['postos_retirados'])): ?>
    <div class="postos-retirados">
        <h5>📦 Postos com retirada nas datas selecionadas:</h5>
        <?php foreach ($analise_expedicao['postos_retirados'] as $posto): ?>
            <span class="posto-retirado">Posto <?php echo htmlspecialchars($posto) ?></span>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    </div>
</div>

<div class="painel-inserir-dados" id="painel-insercao">
    <h3>🔧 Inserir Dados na Tabela ciPostos (V8.1)</h3>
    <p>Use este painel para inserir dados lendo o código de barras de 19 dígitos.</p>
    
    <form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
        <div class="form-grid">
            <div class="form-group">
                <label for="codigo_barras">Código de Barras (19 dígitos):</label>
                <input type="text"
                       id="codigo_barras"
                       name="codigo_barras"
                       placeholder="0071459800101600044"
                       maxlength="19"
                       pattern="\d{19}"
                       required
                       autocomplete="off">
                <small>Ex: 0071459800101600044 (lote+regional+posto+quantidade)</small>
            </div>
            
            <div class="form-group">
                <label for="data_inserir">Data (dd/mm/aaaa):</label>
                <input type="text"
                       id="data_inserir"
                       name="data_inserir"
                       placeholder="<?php echo date('d/m/Y') ?>"
                       pattern="\d{2}/\d{2}/\d{4}"
                       required>
            </div>
            
            <div class="form-group">
                <label for="turno_inserir">Turno:</label>
                <select id="turno_inserir" name="turno_inserir" required>
                    <option value="">Selecione o turno</option>
                    <option value="1">Manhã</option>
                    <option value="2">Tarde</option>
                    <option value="3">Noite</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="autor_inserir">Autor:</label>
                <select id="autor_inserir" name="autor_inserir">
                    <option value="conferencia">conferencia (padrão)</option>
                    <?php
                    $usuarios_validos = obter_usuarios_validos($pdo_contrsos);
                    foreach ($usuarios_validos as $usuario):
                    ?>
                        <option value="<?php echo htmlspecialchars($usuario['usuario']) ?>">
                            <?php echo htmlspecialchars($usuario['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <button type="submit" name="inserir_dados_barcode" class="btn-inserir">
            Inserir Dados na Tabela ciPostos
        </button>
        
        <?php if (isset($_GET['lacre_capital'])): ?>
            <input type="hidden" name="lacre_capital" value="<?php echo $_GET['lacre_capital'] ?>">
        <?php endif; ?>
        <?php if (isset($_GET['lacre_central'])): ?>
            <input type="hidden" name="lacre_central" value="<?php echo $_GET['lacre_central'] ?>">
        <?php endif; ?>
        <?php if (isset($_GET['lacre_regionais'])): ?>
            <input type="hidden" name="lacre_regionais" value="<?php echo $_GET['lacre_regionais'] ?>">
        <?php endif; ?>
        <?php if (isset($_GET['responsavel'])): ?>
            <input type="hidden" name="responsavel" value="<?php echo $_GET['responsavel'] ?>">
        <?php endif; ?>
        <?php foreach ($_SESSION['datas_filtro'] as $data): ?>
            <input type="hidden" name="datas[]" value="<?php echo $data ?>">
        <?php endforeach; ?>
    </form>
</div>
<div class="quadro quadro-formulario">
    <form method="post" style="margin-bottom: 10px;" onsubmit="return confirmarLimparSessao(this);">
        <button type="submit" name="limpar_sessao" class="btn-limpar">Limpar Sessão</button>
    </form>
    <form method="get" action="<?php echo $_SERVER['PHP_SELF'] ?>" id="formFiltroData" onsubmit="limparLacresPorRecalculo(); salvarEstadoEtiquetasCorreios();">
        <div class="topo-formulario">
            <label>Lacre Capital: <input type="number" name="lacre_capital" id="lacre_capital_input" value="<?php echo $lacre_capital ?>" required></label>
            <label>Lacre Central: <input type="number" name="lacre_central" id="lacre_central_input" value="<?php echo $lacre_central ?>" required></label>
            <label>Lacre Regionais: <input type="number" name="lacre_regionais" id="lacre_regionais_input" value="<?php echo $lacre_regionais ?>" required></label>
            <input type="hidden" name="recalculo_por_lacre" id="recalculo_por_lacre" value="<?php echo (isset($_GET['recalculo_por_lacre']) && $_GET['recalculo_por_lacre'] === '1') ? '1' : '0' ?>">
            <label>Responsável: <input type="text" name="responsavel" value="<?php echo htmlspecialchars($responsavel) ?>" required></label>
        </div>
        <div class="alinhado">
            <?php foreach ($datas_expedicao as $data): ?>
                <label><input type="checkbox" name="datas[]" value="<?php echo $data ?>" <?php echo in_array($data, $datas_filtro) ? 'checked' : '' ?>> <?php echo $data ?></label>
            <?php endforeach; ?>
        </div>
        <br>
        <button type="submit">Filtrar por data(s)</button>
    </form>
    
   
    
    <?php if (!empty($dados['REGIONAIS'])): ?>
    
    <?php endif; ?>
</div>

<?php if (!empty($mensagem_sucesso)): ?>
<div class="mensagem-sucesso">
    <?php echo htmlspecialchars($mensagem_sucesso) ?>
</div>
<?php endif; ?>

<?php if (!empty($mensagem_erro)): ?>
<div class="mensagem-erro">
    <?php echo htmlspecialchars($mensagem_erro) ?>
</div>
<?php endif; ?>

<?php if (!empty($postos_nao_cadastrados)): ?>
<div class="alerta">
    <div class="alerta-titulo">Atenção: Os seguintes postos não estão cadastrados na tabela ciRegionais:</div>
    <ul class="alerta-lista">
        <?php foreach ($postos_nao_cadastrados as $info): ?>
            <li>Posto <?php echo $info['posto'] ?></li>
        <?php endforeach; ?>
    </ul>
    <p>Utilize o formulário abaixo para cadastrar estes postos.</p>
</div>

<div class="cadastro-posto">
    <h3>Cadastrar Posto na Tabela ciRegionais</h3>
    <form method="post" class="form-cadastro">
        <label>
            Posto:
            <select name="posto" required>
                <option value="">Selecione o posto</option>
                <?php foreach ($postos_nao_cadastrados as $info): ?>
                    <option value="<?php echo $info['posto'] ?>" data-regional="<?php echo $info['regional'] ?>"><?php echo $info['posto'] ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Regional:
            <select name="regional" required>
                <option value="">Selecione a regional</option>
                <?php foreach ($todas_regionais as $num => $nome): ?>
                    <option value="<?php echo $num ?>"><?php echo $num ?> - <?php echo $nome ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Nome:
            <input type="text" name="nome" required placeholder="Ex: Posto 123 - Cidade">
        </label>
        <input type="hidden" name="cadastrar_posto" value="1">
        <button type="submit" class="btn-cadastrar">Cadastrar Posto</button>
    </form>
</div>
<?php endif; ?>

<!-- Formulário principal para salvar ofício Correios -->
<form method="post" id="formOficioCorreios" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="acao" id="acaoCorreios" value="salvar_oficio_correios">
    <input type="hidden" name="correios_datas" value="<?php echo htmlspecialchars(implode(',', $datas_filtro), ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="imprimir_apos_salvar" id="imprimirAposSalvar" value="0">
    <input type="hidden" name="modo_oficio" id="modo_oficio" value="" />

<div style="display: flex; gap: 10px; margin-bottom: 15px;">
    <button type="button" class="btn-imprimir" onclick="confirmarGravarEImprimir();" style="background:#28a745;"><i>💾🖨️</i> Gravar e Imprimir</button>
    <button type="button" class="btn-imprimir" onclick="prepararEImprimir();" style="background:#6c757d;"><i>🖨️</i> Apenas Imprimir</button>
    <button type="button" class="btn-salvar-etiquetas" onclick="abrirModalConfirmacao()"><i>💾</i> Salvar Etiquetas Correios</button>
</div>

<?php if (!empty($poupaTempoPayload)): ?>
        <?php
            // Garante JSON bem formado (com acentos)
            $poupaTempoPayloadJson = json_encode($poupaTempoPayload, JSON_UNESCAPED_UNICODE);
        ?>
        <!-- Botão Ofício Poupatempo (form gerado fora do form principal para evitar forms aninhados) -->
        <button type="button" class="btn btn-warning" onclick="abrirOficioPoupaTempo();">Ofício Poupatempo</button>
        <script type="text/javascript">
        function abrirOficioPoupaTempo() {
                var payload = <?php echo json_encode($poupaTempoPayload, JSON_UNESCAPED_UNICODE); ?>;
                var form = document.createElement('form');
                form.method = 'post';
                form.action = 'modelo_oficio_poupa_tempo.php';
                form.target = '_blank';
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'poupatempo_payload';
                input.value = JSON.stringify(payload);
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
        }
        </script>
<?php endif; ?>



<div class="quadro quadro-logo somente-impressao" style="margin-bottom: 15px;">
    <img src="logo_celepar.png" style="height: 60px; float: left; margin-right: 15px;">
    <div style="font-size: 14px;">
        <strong>CELEPAR – TECNOLOGIA DA INFORMAÇÃO E COMUNICAÇÃO DO PARANÁ</strong><br>
        COMPROVANTE DE ENTREGA DE SERVIÇOS
    </div>
</div>
<div style="clear: both;"></div>

<div class="info-cliente somente-impressao">
    <p><strong>CLIENTE:</strong> CORREIO - <strong>END.</strong>R: JOÃO NEGRÃO, 1251 - CENTRO - CURITIBA PARANÁ</p>
    </p>
    <p><strong>SISTEMA: </strong>SIV --<strong>SETOR: </strong>EXPEDIÇÃO</p>
</div>

<div class="quadro quadro-adicionar">
    <h3>Adicionar Posto Manualmente</h3>
    <form method="post" class="form-adicionar">
        <label>
            Grupo
            <select name="tipo_posto" required>
                <option value="CAPITAL">CAPITAL</option>
                <option value="CENTRAL IIPR">CENTRAL IIPR</option>
                <option value="REGIONAIS">REGIONAIS</option>
            </select>
        </label>
        <label>
            Nome do Posto
            <input type="text" name="nome_posto" required>
        </label>
        <label>
            Lacre IIPR
            <input type="number" name="lacre_iipr_manual" class="lacre" required>
        </label>
        <label>
            Lacre Correios
            <input type="number" name="lacre_correios_manual" class="lacre" required>
        </label>
        <input type="hidden" name="adicionar_manual" value="1">
        <button type="submit" class="btn-adicionar">Adicionar Posto</button>
    </form>
</div>

<?php foreach ($dados as $grupo => $itens): if (empty($itens)) continue; ?>
    <table id="tabela-<?php echo strtolower(str_replace(' ', '-', $grupo)) ?>" data-grupo="<?php echo htmlspecialchars($grupo, ENT_QUOTES, 'UTF-8') ?>">
        <thead>
            <tr>
                <th><?php echo $grupo ?></th>
                <th>
                    Lacre IIPR
                    <?php if ($grupo !== 'POUPA TEMPO'): ?>
                    <button type="button" class="btn-limpar-coluna" onclick="limparColuna('<?php echo htmlspecialchars($grupo, ENT_QUOTES, 'UTF-8') ?>', 'lacre_iipr')" title="Apagar todos os lacres IIPR deste grupo">X</button>
                    <?php endif; ?>
                </th>
                <th>
                    Lacre Correios
                    <?php if ($grupo !== 'POUPA TEMPO'): ?>
                    <button type="button" class="btn-limpar-coluna" onclick="limparColuna('<?php echo htmlspecialchars($grupo, ENT_QUOTES, 'UTF-8') ?>', 'lacre_correios')" title="Apagar todos os lacres Correios deste grupo">X</button>
                    <?php endif; ?>
                </th>
                <th>
                    Etiqueta Correios
                    <?php if ($grupo !== 'POUPA TEMPO' && $grupo !== 'CENTRAL IIPR'): ?>
                    <button type="button" class="btn-limpar-coluna" onclick="limparColuna('<?php echo htmlspecialchars($grupo, ENT_QUOTES, 'UTF-8') ?>', 'etiqueta_correios')" title="Apagar todas as etiquetas deste grupo">X</button>
                    <?php endif; ?>
                    <?php if ($grupo === 'CENTRAL IIPR'): ?>
                    <button type="button" class="btn-limpar-coluna" onclick="limparEtiquetasCentral()" title="Apagar todas as etiquetas da Central IIPR">X</button>
                    <?php endif; ?>
                </th>
                <th>Acoes</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($itens as $key => $dado): ?>
            <tr data-posto-codigo="<?php echo $dado['posto_codigo'] ?>" data-grupo="<?php echo $grupo ?>" data-regional="<?php echo isset($dado['regional']) ? htmlspecialchars($dado['regional'], ENT_QUOTES, 'UTF-8') : '0' ?>" data-regional-codigo="<?php echo isset($dado['regional']) ? htmlspecialchars($dado['regional'], ENT_QUOTES, 'UTF-8') : '0' ?>" <?php if ($grupo === 'CENTRAL IIPR'): ?>class="linha-central" data-central-index="<?php echo $key ?>"<?php endif; ?>>
                <td>
                    <!-- v8.6: Input oculto com código do posto para manter alinhamento de arrays -->
                    <?php if ($grupo !== 'POUPA TEMPO'): ?>
                    <input type="hidden" name="posto_codigo_correios[]" value="<?php echo htmlspecialchars($dado['posto_codigo'], ENT_QUOTES, 'UTF-8') ?>">
                    <?php endif; ?>
                    <?php echo $dado['posto_nome'] ?>
                    <?php if ($grupo === 'CENTRAL IIPR'): ?>
                    <br><button type="button" class="btn-split-aqui no-print" onclick="definirSplitAqui(this)" style="font-size:11px; padding:2px 6px; margin-top:4px;">Split aqui</button>
                    <?php endif; ?>
                    <?php if ($grupo !== 'POUPA TEMPO'): ?>
                    <input type="hidden" name="nome_posto[<?php echo htmlspecialchars($dado['posto_codigo'], ENT_QUOTES, 'UTF-8') ?>]" value="<?php echo htmlspecialchars($dado['posto_nome'], ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="grupo_posto[<?php echo htmlspecialchars($dado['posto_codigo'], ENT_QUOTES, 'UTF-8') ?>]" value="<?php echo htmlspecialchars($grupo, ENT_QUOTES, 'UTF-8') ?>">
                    <?php endif; ?>
                </td>
                <td><?php if ($grupo === 'POUPA TEMPO'): ?>—<?php else: ?><input class="lacre" type="text" name="lacre_iipr[<?php echo htmlspecialchars($dado['posto_codigo'], ENT_QUOTES, 'UTF-8') ?>]" value="<?php echo htmlspecialchars(isset($dado['lacre_iipr']) ? $dado['lacre_iipr'] : '', ENT_QUOTES, 'UTF-8') ?>" data-indice="<?php echo $dado['posto_codigo'] ?>" data-tipo="iipr"><?php endif; ?></td>
                <td><?php if ($grupo === 'POUPA TEMPO'): ?>—<?php else: ?><input class="lacre <?php if ($grupo === 'CENTRAL IIPR'): ?>central-correios<?php endif; ?>" type="text" name="lacre_correios[<?php echo htmlspecialchars($dado['posto_codigo'], ENT_QUOTES, 'UTF-8') ?>]" value="<?php echo htmlspecialchars(isset($dado['lacre_correios']) ? $dado['lacre_correios'] : '', ENT_QUOTES, 'UTF-8') ?>" data-indice="<?php echo $dado['posto_codigo'] ?>" data-tipo="correios"><?php endif; ?></td>
                <td>
    <?php if ($grupo === 'POUPA TEMPO'): ?>—
    <?php elseif ($grupo === 'CENTRAL IIPR'): ?>
        <input class="etiqueta-barras central-etiqueta" type="text" name="etiqueta_correios[p_<?php echo htmlspecialchars($dado['posto_codigo'], ENT_QUOTES, 'UTF-8') ?>]" maxlength="35" data-indice="<?php echo $dado['posto_codigo'] ?>" value="<?php echo htmlspecialchars(isset($_SESSION['etiquetas'][$dado['posto_codigo']]) ? $_SESSION['etiquetas'][$dado['posto_codigo']] : '', ENT_QUOTES, 'UTF-8') ?>">
    <?php else: ?>
        <input class="etiqueta-barras etiqueta-validavel" type="text" name="etiqueta_correios[p_<?php echo htmlspecialchars($dado['posto_codigo'], ENT_QUOTES, 'UTF-8') ?>]" maxlength="35" data-indice="<?php echo $dado['posto_codigo'] ?>" data-grupo="<?php echo htmlspecialchars($grupo, ENT_QUOTES, 'UTF-8') ?>" data-regional="<?php echo isset($dado['regional']) ? htmlspecialchars($dado['regional'], ENT_QUOTES, 'UTF-8') : '0' ?>" value="<?php echo htmlspecialchars(isset($_SESSION['etiquetas'][$dado['posto_codigo']]) ? $_SESSION['etiquetas'][$dado['posto_codigo']] : '', ENT_QUOTES, 'UTF-8') ?>">
        <div class="alerta-duplicata" id="alerta-<?php echo $dado['posto_codigo'] ?>"></div>
    <?php endif; ?>
</td>
                <td>
                    <?php if ($grupo === 'POUPA TEMPO'): ?>
                        —
                    <?php elseif ($grupo === 'REGIONAIS'): ?>
                    <button type="button" class="btn-excluir-regional"
                            onclick="excluirPostoRegional('<?php echo htmlspecialchars($dado['posto_codigo'], ENT_QUOTES, 'UTF-8') ?>', '<?php echo htmlspecialchars($dado['posto_nome'], ENT_QUOTES, 'UTF-8') ?>');">
                        Excluir Regional
                    </button>
                    <?php else: ?>
                    <button type="button" class="btn-excluir"
                            onclick="excluirPosto('<?php echo htmlspecialchars($dado['posto_codigo'], ENT_QUOTES, 'UTF-8') ?>', '<?php echo htmlspecialchars($grupo, ENT_QUOTES, 'UTF-8') ?>', '<?php echo htmlspecialchars($dado['posto_nome'], ENT_QUOTES, 'UTF-8') ?>');">
                        Excluir
                    </button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endforeach; ?>

</form>
<!-- Fim do formulário principal para salvar ofício Correios -->

<div class="assinaturas somente-impressao">
    <div class="assinatura-esquerda">
        <hr>
        <p>RESPONSÁVEL CELEPAR<br></p>
    </div>
    <div class="assinatura-direita">
        <hr>
        <p>RESPONSÁVEL CORREIOS<br></p>
    </div>
</div>

<div class="footer somente-impressao">
    <p>Documento gerado em <?php echo $data_geracao ?></p>
</div>

<div class="modal-overlay" id="modal-inserir">
    <div class="modal-content">
        <h3 class="modal-title">Inserir Novo Posto</h3>
        <form method="post" class="modal-form">
            <input type="hidden" name="inserir_linha" value="1">
            <input type="hidden" name="referencia_posto" id="referencia_posto" value="">
            <input type="hidden" name="posicao" id="posicao_insercao" value="">
            
            <label for="novo_grupo">Grupo:</label>
            <select name="novo_grupo" id="novo_grupo" required>
                <option value="CAPITAL">CAPITAL</option>
                <option value="CENTRAL IIPR">CENTRAL IIPR</option>
                <option value="REGIONAIS">REGIONAIS</option>
            </select>
            
            <label for="novo_nome">Nome do Posto:</label>
            <input type="text" name="novo_nome" id="novo_nome" required>
            
            <label for="novo_lacre_iipr">Lacre IIPR:</label>
            <input type="number" name="novo_lacre_iipr" id="novo_lacre_iipr" required>
            
            <label for="novo_lacre_correios">Lacre Correios:</label>
            <input type="number" name="novo_lacre_correios" id="novo_lacre_correios" required>
            
            <div class="modal-buttons">
                <button type="button" class="modal-btn modal-btn-cancel" onclick="fecharModal()">Cancelar</button>
                <button type="submit" class="modal-btn modal-btn-save">Salvar</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modal-confirmacao-salvamento">
    <div class="modal-confirmacao">
        <h3><span class="icone">💾</span> Confirmar Salvamento</h3>
        <p>Deseja realmente salvar todas as etiquetas dos Correios no banco de dados?</p>
        <p>Esta ação irá gravar <span id="contador-etiquetas">0</span> etiquetas válidas na tabela ciMalotes.</p>
        
        <form method="post" id="form-salvamento">
            <input type="hidden" name="salvar_etiquetas_confirmado" value="1">
            <input type="hidden" name="login" value="<?php echo htmlspecialchars($responsavel) ?>">
            
            <div class="form-group">
                <label for="login_personalizado">Nome do Responsável pelo Salvamento:</label>
                <input type="text" name="login_personalizado" id="login_personalizado"
                       value="<?php echo htmlspecialchars($responsavel) ?>"
                       placeholder="Digite o nome do responsável">
            </div>
            
            <div class="btn-group">
                <button type="button" class="btn btn-cancelar" onclick="fecharModalConfirmacao()">Cancelar</button>
                <button type="submit" class="btn btn-confirmar">Confirmar Salvamento</button>
            </div>
        </form>
    </div>
</div>

<?php if ($mostrar_debug): ?>
<div class="debug-info">
    <h3>Informações de Depuração (V7.9)</h3>
    <pre><?php
        echo "Linhas Removidas:\n";
        print_r($_SESSION['linhas_removidas']);
        
        echo "\n\nExclusões de Regionais Manual:\n";
        print_r($_SESSION['excluir_regionais_manual']);
        
        echo "\n\nAnálise de Expedição (V7.9):\n";
        print_r($analise_expedicao);
        
        echo "\n\nLog de Depuração:\n";
        print_r($_SESSION['debug_log']);
        
        echo "\n\nDados REGIONAIS:\n";
        print_r($dados['REGIONAIS']);
    ?></pre>
</div>
<?php endif; ?>

<!-- Formulario oculto para exclusao de postos -->
<form method="post" id="formExcluirPosto" style="display:none;">
    <input type="hidden" name="excluir_posto" id="excluir_posto_flag" value="">
    <input type="hidden" name="excluir_posto_regional" id="excluir_posto_regional_flag" value="">
    <input type="hidden" name="codigo_posto" id="excluir_codigo_posto" value="">
    <input type="hidden" name="grupo_posto" id="excluir_grupo_posto" value="">
    <input type="hidden" name="info_regional" id="excluir_info_regional" value="">
</form>

<script type="text/javascript">
// Funcoes para salvar oficio Correios (compativel com navegadores antigos)
// v8.9: Prepara arrays alinhados de lacres/etiquetas + regional antes do submit
function prepararLacresCorreiosParaSubmit(form) {
    if (!form) return;
    // v8.13: Remover inputs ocultos antigos + snapshot_oficio
    var nomes = ['posto_lacres[]','lacre_iipr[]','lacre_correios[]','etiqueta_correios[]','regional_lacres[]','grupo_lacres[]','snapshot_oficio'];
    for (var n=0;n<nomes.length;n++){
        var els = form.querySelectorAll('input[name="'+nomes[n]+'"]');
        for (var i=0;i<els.length;i++) { els[i].parentNode.removeChild(els[i]); }
    }

    // v8.13: Criar snapshot JSON da grade (fonte única de verdade)
    var snapshot = [];

    // v8.12: Coletar APENAS linhas visíveis (não excluídas) com atributo data-posto-codigo
    // Ignora linhas com display:none, com classe 'removido', ou que estejam ocultas
    var rows = form.querySelectorAll('tr[data-posto-codigo]');
    if (!rows || rows.length === 0) {
        rows = document.querySelectorAll('tr[data-posto-codigo]');
    }
    
    for (var r=0;r<rows.length;r++){
        var tr = rows[r];
        
        // v8.12: Pular linhas que estão ocultas (display:none ou classe removido)
        if (tr.style && tr.style.display === 'none') continue;
        if (tr.className && tr.className.indexOf('removido') !== -1) continue;
        
        var computedStyle = window.getComputedStyle ? window.getComputedStyle(tr) : null;
        if (computedStyle && computedStyle.display === 'none') continue;
        
        var posto = tr.getAttribute('data-posto-codigo');
        if (!posto) continue;

        // v8.9: Capturar regional da linha (usar data-regional-codigo ou data-regional)
        var regional = tr.getAttribute('data-regional-codigo') || tr.getAttribute('data-regional') || '0';

        // v8.12.3: Capturar grupo da linha (CAPITAL, CENTRAL IIPR, REGIONAIS)
        var grupo = tr.getAttribute('data-grupo') || '';

        // Encontrar inputs na linha
        var inpIIPR = tr.querySelector('input[name^="lacre_iipr"], input[data-tipo="iipr"], input.lacre');
        var inpCorr = tr.querySelector('input[name^="lacre_correios"], input[data-tipo="correios"], input.lacre');
        var inpEtiq = tr.querySelector('input[name^="etiqueta_correios"], input.etiqueta-barras');

        var valI = inpIIPR ? String(inpIIPR.value || '').trim() : '';
        var valC = inpCorr ? String(inpCorr.value || '').trim() : '';
        var valE = inpEtiq ? String(inpEtiq.value || '').trim() : '';

        // v8.13: Adicionar ao snapshot JSON
        snapshot.push({
            posto: posto,
            grupo: grupo,
            regional: regional,
            lacre_iipr: valI,
            lacre_correios: valC,
            etiqueta_correios: valE
        });

        // v8.13: Manter arrays antigos para compatibilidade
        var a = document.createElement('input'); a.type='hidden'; a.name='posto_lacres[]'; a.value=posto; form.appendChild(a);
        var b = document.createElement('input'); b.type='hidden'; b.name='lacre_iipr[]'; b.value=valI; form.appendChild(b);
        var c = document.createElement('input'); c.type='hidden'; c.name='lacre_correios[]'; c.value=valC; form.appendChild(c);
        var d = document.createElement('input'); d.type='hidden'; d.name='etiqueta_correios[]'; d.value=valE; form.appendChild(d);
        var e = document.createElement('input'); e.type='hidden'; e.name='regional_lacres[]'; e.value=regional; form.appendChild(e);
        var f = document.createElement('input'); f.type='hidden'; f.name='grupo_lacres[]'; f.value=grupo; form.appendChild(f);
    }

    // v8.13: Criar input hidden com snapshot JSON
    var snapshotInput = document.createElement('input');
    snapshotInput.type = 'hidden';
    snapshotInput.name = 'snapshot_oficio';
    snapshotInput.value = JSON.stringify(snapshot);
    form.appendChild(snapshotInput);
}

// v8.11: Persistencia de lacres/etiquetas em localStorage
// Salva estado dos inputs de lacre IIPR, lacre Correios e etiqueta Correios
function salvarEstadoEtiquetasCorreios() {
    if (typeof window.localStorage === 'undefined') {
        return;
    }

    // v8.11.2: Se estamos em recalculo por lacre, nao salvar em localStorage para
    // evitar sobrescrita de valores quando a restauracao rodar (mesmo que seja pulada
    // normalmente, eh mais seguro nao ter valores vazios gravados)
    try {
        var recalEl = document.getElementById('recalculo_por_lacre');
        if (recalEl && String(recalEl.value) === '1') {
            return;
        }
    } catch (e) {
        // ignore
    }

    var idDespachoInput = document.getElementById('id_despacho');
    var idDespacho = idDespachoInput ? idDespachoInput.value : '';

    var rows = document.querySelectorAll('tr[data-posto-codigo]');
    for (var r = 0; r < rows.length; r++) {
        var tr = rows[r];
        var postoCodigo = tr.getAttribute('data-posto-codigo');
        var regionalCodigo = tr.getAttribute('data-regional-codigo') || tr.getAttribute('data-regional') || '0';

        if (!postoCodigo) continue;

        // v8.13.4: Salvar TODOS os inputs (lacres IIPR, Correios e etiqueta)
        // para preservar ao excluir linha ou filtrar
        var inpIIPR = tr.querySelector('input[name^="lacre_iipr"], input[data-tipo="iipr"]');
        var inpCorr = tr.querySelector('input[name^="lacre_correios"], input[data-tipo="correios"]');
        var inpEtiq = tr.querySelector('input[name^="etiqueta_correios"], input.etiqueta-barras');
        
        var valI = inpIIPR ? String(inpIIPR.value || '').trim() : '';
        var valC = inpCorr ? String(inpCorr.value || '').trim() : '';
        var valE = inpEtiq ? String(inpEtiq.value || '').trim() : '';

        var chaveBase = 'oficioCorreios:' + idDespacho + ':' + regionalCodigo + ':' + postoCodigo;
        var valor = { 
            lacre_iipr: valI,
            lacre_correios: valC,
            etiqueta_correios: valE 
        };

        try {
            window.localStorage.setItem(chaveBase, JSON.stringify(valor));
        } catch (e) {
            // localStorage cheio ou desabilitado
        }
    }
}

// v8.13.4: Salvar TODOS os inputs (lacres + etiquetas) no localStorage
// Usado antes de excluir uma linha para preservar TUDO que foi digitado
function salvarSomenteEtiquetasCorreios() {
    if (typeof window.localStorage === 'undefined') {
        return;
    }

    var idDespachoInput = document.getElementById('id_despacho');
    var idDespacho = idDespachoInput ? idDespachoInput.value : '';

    var rows = document.querySelectorAll('tr[data-posto-codigo]');
    for (var r = 0; r < rows.length; r++) {
        var tr = rows[r];
        var postoCodigo = tr.getAttribute('data-posto-codigo');
        var regionalCodigo = tr.getAttribute('data-regional-codigo') || tr.getAttribute('data-regional') || '0';

        if (!postoCodigo) continue;

        // v8.13.4: Salvar TODOS os inputs (não apenas etiquetas)
        var inpIIPR = tr.querySelector('input[name^="lacre_iipr"], input[data-tipo="iipr"]');
        var inpCorr = tr.querySelector('input[name^="lacre_correios"], input[data-tipo="correios"]');
        var inpEtiq = tr.querySelector('input[name^="etiqueta_correios"], input.etiqueta-barras');
        
        var valI = inpIIPR ? String(inpIIPR.value || '').trim() : '';
        var valC = inpCorr ? String(inpCorr.value || '').trim() : '';
        var valE = inpEtiq ? String(inpEtiq.value || '').trim() : '';

        var chaveBase = 'oficioCorreios:' + idDespacho + ':' + regionalCodigo + ':' + postoCodigo;
        var valor = { 
            lacre_iipr: valI,
            lacre_correios: valC,
            etiqueta_correios: valE 
        };

        try {
            window.localStorage.setItem(chaveBase, JSON.stringify(valor));
        } catch (e) {
            // localStorage cheio ou desabilitado
        }
    }
}

// v8.11: Restaura estado dos inputs de lacre/etiqueta dos Correios
function restaurarEstadoEtiquetasCorreios() {
    if (typeof window.localStorage === 'undefined') {
        return;
    }

    // Restaura apenas as etiquetas de correios (códigos de barras).
    // Mantemos a restauração mesmo quando `recalculo_por_lacre` estiver setado,
    // pois lacres são recalculados no servidor e não devem ser substituídos
    // pelo conteúdo do localStorage; entretanto precisamos preservar as
    // etiquetas do usuário em qualquer fluxo (remoção, filtro, etc.).

    var idDespachoInput = document.getElementById('id_despacho');
    var idDespacho = idDespachoInput ? idDespachoInput.value : '';

    var rows = document.querySelectorAll('tr[data-posto-codigo]');
    for (var r = 0; r < rows.length; r++) {
        var tr = rows[r];
        var postoCodigo = tr.getAttribute('data-posto-codigo');
        var regionalCodigo = tr.getAttribute('data-regional-codigo') || tr.getAttribute('data-regional') || '0';

        if (!postoCodigo) continue;

        var chaveBase = 'oficioCorreios:' + idDespacho + ':' + regionalCodigo + ':' + postoCodigo;
        var json = window.localStorage.getItem(chaveBase);

        if (!json) continue;

        var valor;
        try {
            valor = JSON.parse(json);
        } catch (e) {
            continue;
        }

        // v8.13.4: Restaurar TODOS os inputs (lacres IIPR, Correios e etiqueta)
        var inpIIPR = tr.querySelector('input[name^="lacre_iipr"], input[data-tipo="iipr"]');
        var inpCorr = tr.querySelector('input[name^="lacre_correios"], input[data-tipo="correios"]');
        var inpEtiq = tr.querySelector('input[name^="etiqueta_correios"], input.etiqueta-barras');
        
        if (valor) {
            if (inpIIPR && valor.lacre_iipr) {
                inpIIPR.value = valor.lacre_iipr;
            }
            if (inpCorr && valor.lacre_correios) {
                inpCorr.value = valor.lacre_correios;
            }
            if (inpEtiq && valor.etiqueta_correios) {
                inpEtiq.value = valor.etiqueta_correios;
            }
        }
    }
}

// v8.14.1: Função para preencher inputs visualmente antes de imprimir
// Garante que lacres/etiquetas aparecem no PDF gerado
function preencherInputsParaImpressao() {
    var rows = document.querySelectorAll('tr[data-posto-codigo]');
    for (var r = 0; r < rows.length; r++) {
        var tr = rows[r];
        if (tr.style && tr.style.display === 'none') continue;
        
        var inpIIPR = tr.querySelector('input[name^="lacre_iipr"], input[data-tipo="iipr"]');
        var inpCorr = tr.querySelector('input[name^="lacre_correios"], input[data-tipo="correios"]');
        var inpEtiq = tr.querySelector('input[name^="etiqueta_correios"], input.etiqueta-barras');
        
        // Garantir que values estão visíveis (renderizados no DOM)
        if (inpIIPR && inpIIPR.value) { inpIIPR.setAttribute('value', inpIIPR.value); }
        if (inpCorr && inpCorr.value) { inpCorr.setAttribute('value', inpCorr.value); }
        if (inpEtiq && inpEtiq.value) { inpEtiq.setAttribute('value', inpEtiq.value); }
    }
}

// v8.14.1: Confirmação customizada com 3 opções: Sobrescrever | Criar Novo | Cancelar
function confirmarGravarEImprimir() {
    // Criar modal customizado com 3 botões
    var overlay = document.createElement('div');
    overlay.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;display:flex;align-items:center;justify-content:center;';
    
    var modal = document.createElement('div');
    modal.style.cssText = 'background:white;padding:30px;border-radius:8px;box-shadow:0 4px 20px rgba(0,0,0,0.3);max-width:500px;text-align:center;';
    
    var titulo = document.createElement('h3');
    titulo.textContent = 'Como deseja gravar o Ofício dos Correios?';
    titulo.style.cssText = 'margin-top:0;color:#333;';
    
    var texto = document.createElement('p');
    texto.innerHTML = '<b>Sobrescrever:</b> Apaga lotes do último ofício e grava este no lugar.<br><br>' +
                      '<b>Criar Novo:</b> Mantém ofício anterior e cria outro com novo número.<br><br>' +
                      '<b>Cancelar:</b> Aborta a operação.';
    texto.style.cssText = 'margin:20px 0;line-height:1.6;color:#555;';
    
    var botoes = document.createElement('div');
    botoes.style.cssText = 'display:flex;gap:10px;justify-content:center;margin-top:25px;';
    
    var btnSobrescrever = document.createElement('button');
    btnSobrescrever.textContent = 'Sobrescrever';
    btnSobrescrever.style.cssText = 'background:#ff9800;color:white;border:none;padding:12px 24px;border-radius:4px;cursor:pointer;font-size:14px;font-weight:bold;';
    btnSobrescrever.onclick = function() {
        document.body.removeChild(overlay);
        var campoModo = document.getElementById('modo_oficio');
        if (campoModo) { campoModo.value = 'sobrescrever'; }
        gravarEImprimirCorreios();
    };
    
    var btnCriarNovo = document.createElement('button');
    btnCriarNovo.textContent = 'Criar Novo';
    btnCriarNovo.style.cssText = 'background:#28a745;color:white;border:none;padding:12px 24px;border-radius:4px;cursor:pointer;font-size:14px;font-weight:bold;';
    btnCriarNovo.onclick = function() {
        document.body.removeChild(overlay);
        var campoModo = document.getElementById('modo_oficio');
        if (campoModo) { campoModo.value = 'novo'; }
        gravarEImprimirCorreios();
    };
    
    var btnCancelar = document.createElement('button');
    btnCancelar.textContent = 'Cancelar';
    btnCancelar.style.cssText = 'background:#dc3545;color:white;border:none;padding:12px 24px;border-radius:4px;cursor:pointer;font-size:14px;font-weight:bold;';
    btnCancelar.onclick = function() {
        document.body.removeChild(overlay);
    };
    
    botoes.appendChild(btnSobrescrever);
    botoes.appendChild(btnCriarNovo);
    botoes.appendChild(btnCancelar);
    
    modal.appendChild(titulo);
    modal.appendChild(texto);
    modal.appendChild(botoes);
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
}

// v8.14.0: Limpar Sessão zera TODOS inputs (incluindo topo: lacre_capital/central/regionais)
function confirmarLimparSessao(form) {
    var msg = "Você está prestes a LIMPAR TODA A SESSÃO deste Ofício.\n\n" +
              "Isso irá zerar TODOS os inputs de lacre e de etiqueta dos Correios.\n\n" +
              "Deseja continuar?";
    if (!window.confirm(msg)) {
        return false;
    }

    try {
        // v8.14.0: Zerar inputs DO TOPO (lacre_capital, lacre_central, lacre_regionais)
        var inputTopo = document.getElementById('lacre_capital_input');
        if (inputTopo) { inputTopo.value = ''; }
        inputTopo = document.getElementById('lacre_central_input');
        if (inputTopo) { inputTopo.value = ''; }
        inputTopo = document.getElementById('lacre_regionais_input');
        if (inputTopo) { inputTopo.value = ''; }
        
        // Zerar inputs visuais de lacres e etiquetas (linhas da tabela)
        var inputs = document.querySelectorAll('input.lacre, input.etiqueta-barras, input.central-correios, input.central-etiqueta');
        for (var i = 0; i < inputs.length; i++) {
            inputs[i].value = '';
            try { inputs[i].removeAttribute('readonly'); } catch (e) {}
        }

        // Limpar localStorage relacionado ao despacho atual
        var idDespInput = document.getElementById('id_despacho');
        var idDespacho = idDespInput ? idDespInput.value : '';
        for (var k = localStorage.length - 1; k >= 0; k--) {
            var key = localStorage.key(k);
            if (!key) continue;
            if (key.indexOf('oficioCorreios:' + idDespacho + ':') === 0) {
                localStorage.removeItem(key);
            }
        }
    } catch (e) {
        // ignore
    }

    // permitir que o form submeta para a action que limpa a sessao
    return true;
}

function gravarEImprimirCorreios() {
    var form = document.getElementById('formOficioCorreios');
    if (form) {
        // v8.14.1: Preencher inputs visualmente antes de submeter (garante impressão correta)
        if (typeof preencherInputsParaImpressao === 'function') {
            try { preencherInputsParaImpressao(); } catch (e) { /* ignore */ }
        }
        // v8.12: Salvar etiquetas antes de submeter para garantir restauração após reload
        if (typeof salvarEstadoEtiquetasCorreios === 'function') {
            try { salvarEstadoEtiquetasCorreios(); } catch (e) { /* ignore */ }
        }
        document.getElementById('acaoCorreios').value = 'salvar_oficio_correios';
        document.getElementById('imprimirAposSalvar').value = '1';
        prepararLacresCorreiosParaSubmit(form);
        form.submit();
    } else {
        alert('Erro: Formulario nao encontrado.');
    }
}

function apenasGravarCorreios() {
    var form = document.getElementById('formOficioCorreios');
    if (form) {
        document.getElementById('acaoCorreios').value = 'salvar_oficio_correios';
        document.getElementById('imprimirAposSalvar').value = '0';
        prepararLacresCorreiosParaSubmit(form);
        form.submit();
    } else {
        alert('Erro: Formulario nao encontrado.');
    }
}

// Funcoes para excluir postos (compativel com navegadores antigos)
function excluirPosto(codigo, grupo, nome) {
    if (confirm('Confirma a exclusao do posto ' + nome + '?')) {
        try { if (typeof salvarSomenteEtiquetasCorreios === 'function') salvarSomenteEtiquetasCorreios(); } catch (e) { /* ignore */ }
        document.getElementById('excluir_posto_flag').value = '1';
        document.getElementById('excluir_posto_regional_flag').value = '';
        document.getElementById('excluir_codigo_posto').value = codigo;
        document.getElementById('excluir_grupo_posto').value = grupo;
        document.getElementById('formExcluirPosto').submit();
    }
}

function excluirPostoRegional(codigo, nome) {
    if (confirm('Confirma a exclusao do posto REGIONAL ' + nome + '?')) {
        try { if (typeof salvarSomenteEtiquetasCorreios === 'function') salvarSomenteEtiquetasCorreios(); } catch (e) { /* ignore */ }
        document.getElementById('excluir_posto_flag').value = '';
        document.getElementById('excluir_posto_regional_flag').value = '1';
        document.getElementById('excluir_codigo_posto').value = codigo;
        document.getElementById('excluir_info_regional').value = nome;
        document.getElementById('formExcluirPosto').submit();
    }
}

// Funcao para limpar todos os inputs de uma coluna em um grupo especifico
function limparColuna(grupo, tipoColuna) {
    var nomeColuna = '';
    if (tipoColuna === 'lacre_iipr') {
        nomeColuna = 'Lacre IIPR';
    } else if (tipoColuna === 'lacre_correios') {
        nomeColuna = 'Lacre Correios';
    } else if (tipoColuna === 'etiqueta_correios') {
        nomeColuna = 'Etiqueta Correios';
    }
    
    if (!confirm('Deseja realmente apagar todos os valores da coluna "' + nomeColuna + '" do grupo "' + grupo + '"?\n\nEsta acao nao pode ser desfeita.')) {
        return;
    }
    
    // Encontrar a tabela do grupo (verificar ambos os ids possiveis)
    var tabelaId = 'tabela-' + grupo.toLowerCase().replace(/ /g, '-');
    var tabela = document.getElementById(tabelaId);
    
    // Se for CENTRAL IIPR, verificar id alternativo
    if (!tabela && grupo.toUpperCase() === 'CENTRAL IIPR') {
        tabela = document.getElementById('tblCentralIIPR');
    }
    
    if (!tabela) {
        alert('Tabela nao encontrada: ' + tabelaId);
        return;
    }
    
    // Limpar todos os inputs da coluna especificada
    var inputs = tabela.querySelectorAll('input[name^="' + tipoColuna + '["]');
    for (var i = 0; i < inputs.length; i++) {
        inputs[i].value = '';
    }
    
    // Marcar como nao salvo
    marcarComoNaoSalvo();
    
    alert('Coluna "' + nomeColuna + '" do grupo "' + grupo + '" foi limpa com sucesso!');
}

// v8.11.2: Limpar lacres de forma silenciosa (sem confirmacao) quando ha recalculo por lacre
// Esta funcao eh chamada automaticamente antes de submeter o filtro quando o usuario altera lacres iniciais
function limparLacresPorRecalculo() {
    var recalEl = document.getElementById('recalculo_por_lacre');
    var lacreCap = document.getElementById('lacre_capital_input');
    var lacreCentral = document.getElementById('lacre_central_input');
    var lacreRegionais = document.getElementById('lacre_regionais_input');
    
    // So limpar se recalculo_por_lacre == '1'
    if (!recalEl || String(recalEl.value) !== '1') {
        return;
    }
    
    // Determinar quais grupos tiveram alteracao no lacre inicial
    // Se algum foi alterado, limpamos esse grupo
    
    // CAPITAL
    if (lacreCap && lacreCap.value !== '') {
        var tabelaCap = document.getElementById('tabela-capital');
        if (tabelaCap) {
            var inputsCap = tabelaCap.querySelectorAll('input[name^="lacre_iipr["], input[name^="lacre_correios["]');
            for (var i = 0; i < inputsCap.length; i++) {
                inputsCap[i].value = '';
            }
        }
    }
    
    // CENTRAL IIPR
    if (lacreCentral && lacreCentral.value !== '') {
        var tabelaCentral = document.getElementById('tabela-central-iipr');
        if (!tabelaCentral) {
            tabelaCentral = document.getElementById('tblCentralIIPR');
        }
        if (tabelaCentral) {
            var inputsCentral = tabelaCentral.querySelectorAll('input[name^="lacre_iipr["], input[name^="lacre_correios["]');
            for (var i = 0; i < inputsCentral.length; i++) {
                inputsCentral[i].value = '';
            }
        }
    }
    
    // REGIONAIS
    if (lacreRegionais && lacreRegionais.value !== '') {
        var tabelaRegionais = document.getElementById('tabela-regionais');
        if (tabelaRegionais) {
            var inputsRegionais = tabelaRegionais.querySelectorAll('input[name^="lacre_iipr["], input[name^="lacre_correios["]');
            for (var i = 0; i < inputsRegionais.length; i++) {
                inputsRegionais[i].value = '';
            }
        }
    }
}

// Funcao especial para limpar etiquetas da Central IIPR (incluindo campos readonly do split)
function limparEtiquetasCentral() {
    if (!confirm('Deseja realmente apagar todas as etiquetas da Central IIPR?\n\nEsta acao nao pode ser desfeita.')) {
        return;
    }
    
    // Tentar encontrar a tabela por ambos os ids possiveis
    var tabela = document.getElementById('tabela-central-iipr');
    if (!tabela) {
        tabela = document.getElementById('tblCentralIIPR');
    }
    if (!tabela) {
        alert('Tabela da Central IIPR nao encontrada');
        return;
    }
    
    // Limpar todos os campos de etiqueta (inclusive readonly)
    var inputs = tabela.querySelectorAll('input[name^="etiqueta_correios["]');
    for (var i = 0; i < inputs.length; i++) {
        inputs[i].value = '';
        inputs[i].removeAttribute('readonly');
        inputs[i].className = 'etiqueta-barras etiqueta-central';
    }

    // Resetar estado do split (variaveis JS e classes visuais)
    try {
        window.splitCentralAtivo = false;
        splitIndexCentral = null;
        window.splitVisualIndices = [];

        // Remover classes visuais das linhas da central
        var linhasCentral = document.querySelectorAll('tr.linha-central');
        for (var r = 0; r < linhasCentral.length; r++) {
            for (var g = 1; g <= 5; g++) {
                removerClasse(linhasCentral[r], 'split-central-grupo' + g);
            }
        }

        // Resetar estilo dos botões de split
        var allSplitBtns = document.querySelectorAll('button[onclick*="definirSplitAqui"]');
        for (var b = 0; b < allSplitBtns.length; b++) {
            allSplitBtns[b].style.background = '';
            allSplitBtns[b].textContent = 'Split aqui';
            allSplitBtns[b].style.border = '';
        }

        // Limpar localStorage relacionado a CENTRAL IIPR (se houver id_despacho)
        var idDespInput = document.getElementById('id_despacho');
        var idDespacho = idDespInput ? idDespInput.value : '';
        try {
            for (var k = localStorage.length - 1; k >= 0; k--) {
                var key = localStorage.key(k);
                if (!key) continue;
                if (key.indexOf('oficioCorreios:' + idDespacho + ':') === 0) {
                    // Opcionalmente, filtrar por regional da central se necessario
                    localStorage.removeItem(key);
                }
            }
        } catch (e) { /* ignore */ }
    } catch (e) {
        // ignore erros de limpeza visual
    }

    // Marcar como nao salvo
    marcarComoNaoSalvo();

    alert('Etiquetas da Central IIPR foram limpas com sucesso!');
}

// VERSAO 3: Variavel global para controlar estado de salvamento
var botoesGravar = [];

// Funcao auxiliar para adicionar classe (compativel com navegadores antigos)
function adicionarClasse(el, classe) {
    if (!el) return;
    if (el.className.indexOf(classe) < 0) {
        el.className = el.className + ' ' + classe;
    }
}

// Funcao auxiliar para remover classe (compativel com navegadores antigos)
function removerClasse(el, classe) {
    if (!el) return;
    var regex = new RegExp('\\s*' + classe, 'g');
    el.className = el.className.replace(regex, '');
}

// Funcao para marcar formulario como nao salvo (ativar pulsacao)
function marcarComoNaoSalvo() {
    for (var i = 0; i < botoesGravar.length; i++) {
        removerClasse(botoesGravar[i], 'btn-salvo');
        adicionarClasse(botoesGravar[i], 'btn-pulsando');
    }
}

// Funcao para marcar formulario como salvo (parar pulsacao)
// Chamada apos salvamento bem-sucedido antes de print ou redirect
function marcarComoSalvo() {
    for (var i = 0; i < botoesGravar.length; i++) {
        removerClasse(botoesGravar[i], 'btn-pulsando');
        adicionarClasse(botoesGravar[i], 'btn-salvo');
    }
}

// v8.11.2: Preencher lacres automaticamente ao adicionar posto manualmente
// Essa funcao calcula o lacre da nova linha com base no ultimo lacre do grupo
function preencherLacresParaPostoManual(event) {
    try {
        var form = document.querySelector('.form-adicionar');
        if (!form) return;

        // Obter valores do formulario
        var tipoEl = form.querySelector('select[name="tipo_posto"]');
        var lacreManualIIPR = form.querySelector('input[name="lacre_iipr_manual"]');
        var lacreManualCorr = form.querySelector('input[name="lacre_correios_manual"]');
        if (!tipoEl || !lacreManualIIPR || !lacreManualCorr) return;

        var grupo = String(tipoEl.value || '').trim();
        var tabelaId = 'tabela-' + grupo.toLowerCase().replace(/ /g, '-');
        var tabela = document.getElementById(tabelaId);

        // Função util: encontra ultimo valor numerico não vazio em uma lista de inputs
        var encontrarUltimo = function(inputs) {
            var ultimo = null;
            for (var i = 0; i < inputs.length; i++) {
                var v = String(inputs[i].value || '').trim();
                if (v !== '') {
                    var n = parseInt(v, 10);
                    if (!isNaN(n)) ultimo = n;
                }
            }
            return ultimo;
        };

        var novoI = null;
        var novoC = null;

        if (tabela) {
            // procurar todos os inputs lacre IIPR e Correios dentro da tabela
            var inputsI = tabela.querySelectorAll('input[name^="lacre_iipr"], input[data-tipo="iipr"], input.lacre');
            var inputsC = tabela.querySelectorAll('input[name^="lacre_correios"], input[data-tipo="correios"], input.lacre');

            var ultimoI = encontrarUltimo(inputsI);
            var ultimoC = encontrarUltimo(inputsC);

            if (ultimoI !== null) {
                // Grupo CENTRAL IIPR: lacre IIPR incrementa +1
                if (grupo === 'CENTRAL IIPR') {
                    novoI = ultimoI + 1;
                } else {
                    // CAPITAL e REGIONAIS: incremento +2
                    novoI = ultimoI + 2;
                }
            }

            if (ultimoC !== null) {
                // Para lacre Correios, manter incremento de 2 para CAPITAL/REGIONAIS
                if (grupo === 'CENTRAL IIPR') {
                    // CENTRAL: manter o mesmo comportamento de correios (usar ultimo se existir)
                    novoC = ultimoC;
                } else {
                    novoC = ultimoC + 2;
                }
            }
        }

        // Se nao encontrou ultimo, usar lacre inicial do formulario (caso exista)
        if (novoI === null) {
            try {
                if (grupo === 'CAPITAL') {
                    var base = document.getElementById('lacre_capital_input');
                    if (base && String(base.value || '').trim() !== '') {
                        var b = parseInt(String(base.value), 10);
                        if (!isNaN(b)) {
                            novoI = b;
                        }
                    }
                } else if (grupo === 'CENTRAL IIPR') {
                    var base = document.getElementById('lacre_central_input');
                    if (base && String(base.value || '').trim() !== '') {
                        var b = parseInt(String(base.value), 10);
                        if (!isNaN(b)) {
                            novoI = b;
                        }
                    }
                } else {
                    var base = document.getElementById('lacre_regionais_input');
                    if (base && String(base.value || '').trim() !== '') {
                        var b = parseInt(String(base.value), 10);
                        if (!isNaN(b)) {
                            novoI = b;
                        }
                    }
                }
            } catch (e) { /* ignore */ }
        }

        if (novoC === null) {
            try {
                if (grupo === 'CAPITAL') {
                    var baseC = document.getElementById('lacre_capital_input');
                    if (baseC && String(baseC.value || '').trim() !== '') {
                        var bc = parseInt(String(baseC.value), 10);
                        if (!isNaN(bc)) novoC = bc + 1;
                    }
                } else if (grupo === 'CENTRAL IIPR') {
                    // CENTRAL: usar lacre correios calculado pelo servidor por split; se nao houver, usar lacre_central+1
                    var baseC = document.getElementById('lacre_central_input');
                    if (baseC && String(baseC.value || '').trim() !== '') {
                        var bc = parseInt(String(baseC.value), 10);
                        if (!isNaN(bc)) novoC = bc + 1;
                    }
                } else {
                    var baseC = document.getElementById('lacre_regionais_input');
                    if (baseC && String(baseC.value || '').trim() !== '') {
                        var bc = parseInt(String(baseC.value), 10);
                        if (!isNaN(bc)) novoC = bc + 1;
                    }
                }
            } catch (e) { /* ignore */ }
        }

        // Finalmente, atribuir os valores calculados ao form (sem alterar outras linhas)
        if (novoI !== null) {
            lacreManualIIPR.value = String(novoI);
        }
        if (novoC !== null) {
            lacreManualCorr.value = String(novoC);
        }
    } catch (e) {
        // em caso de erro, nao bloquear envio
    }
}

// Inicializar monitoramento de alteracoes nos inputs
function inicializarMonitoramentoAlteracoes() {
    // v8.11.1: Injetar estilos de destaque para splits (se ainda nao existe)
    if (typeof document !== 'undefined' && document.getElementById && !document.getElementById('split-central-styles')) {
        try {
            var styleEl = document.createElement('style');
            styleEl.id = 'split-central-styles';
            styleEl.type = 'text/css';
            var css = '.split-central-grupo1 { background-color: #fffbe6; } ' +
                      '.split-central-grupo2 { background-color: #e8f7ff; } ' +
                      '.split-central-grupo3 { background-color: #fff3e0; } ' +
                      '.split-central-grupo4 { background-color: #f3ffe8; } ' +
                      '.split-central-grupo5 { background-color: #f0f4ff; }';
            if (styleEl.styleSheet) { styleEl.styleSheet.cssText = css; } else { styleEl.appendChild(document.createTextNode(css)); }
            var heads = document.getElementsByTagName('head');
            if (heads && heads.length) { heads[0].appendChild(styleEl); }
        } catch (e) { /* ignore */ }
    }

    // Inicializar array de indices visuais (v8.11.1)
    window.splitVisualIndices = window.splitVisualIndices || [];

    // v8.11: Restaurar estado dos inputs de lacres/etiquetas (localStorage)
    restaurarEstadoEtiquetasCorreios();
    
    // Encontrar todos os botoes de gravar
    var btns = document.querySelectorAll('button[onclick*="gravar"], button[onclick*="Gravar"]');
    for (var i = 0; i < btns.length; i++) {
        botoesGravar.push(btns[i]);
    }
    
    // Monitorar todos os inputs de lacre e etiqueta
    var inputs = document.querySelectorAll('input.lacre, input.etiqueta-barras, input[name^="lacre_"], input[name^="etiqueta_"]');
    for (var i = 0; i < inputs.length; i++) {
        inputs[i].addEventListener('input', function() {
            marcarComoNaoSalvo();
        });
        inputs[i].addEventListener('change', function() {
            marcarComoNaoSalvo();
        });
    }

    // Se o usuário alterar os lacres iniciais no formulário de filtro, marcar que
    // estamos fazendo um recálculo por lacre para que a restauração do localStorage
    // seja pulada (apenas neste caso). Isso evita que a restauração destrua os
    // valores calculados quando o usuário quer recalcular.
    try {
        var lacreCap = document.getElementById('lacre_capital_input');
        var lacreCentral = document.getElementById('lacre_central_input');
        var lacreRegionais = document.getElementById('lacre_regionais_input');
        var recalEl = document.getElementById('recalculo_por_lacre');
        var setRecal = function() {
            try { if (recalEl) recalEl.value = '1'; } catch (e) {}
        };
        if (lacreCap) { lacreCap.addEventListener('input', setRecal); lacreCap.addEventListener('change', setRecal); }
        if (lacreCentral) { lacreCentral.addEventListener('input', setRecal); lacreCentral.addEventListener('change', setRecal); }
        if (lacreRegionais) { lacreRegionais.addEventListener('input', setRecal); lacreRegionais.addEventListener('change', setRecal); }
    } catch (e) { /* ignore */ }

    // Interceptar envio do formulario 'Adicionar Posto Manualmente' para
    // preencher automaticamente os lacres da nova linha sem tocar nas existentes
    try {
        var formAdicionar = document.querySelector('.form-adicionar');
        if (formAdicionar) {
            formAdicionar.addEventListener('submit', function(e) {
                preencherLacresParaPostoManual(e);
                // nao prevenir submit; apenas garantir que os campos estao preenchidos
            });
        }
    } catch (er) { /* ignore */ }
    
    // VERSAO 3: Iniciar SEM pulsacao (so pulsa quando ha mudanca)
    // Pagina recarrega apos salvar, entao comeca sempre sem pulsacao
}

// Chamar inicializacao quando DOM estiver pronto
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', inicializarMonitoramentoAlteracoes);
} else {
    inicializarMonitoramentoAlteracoes();
}

// Funcao para preparar e imprimir, garantindo que valores do split sejam preservados
function prepararEImprimir() {
    // Sincronizar valores dos inputs antes de imprimir
    sincronizarValoresSplit();
    
    // Aguardar um momento para garantir que o DOM foi atualizado
    setTimeout(function() {
        window.print();
    }, 100);
}

// Funcao para sincronizar valores do split da Central IIPR
function sincronizarValoresSplit() {
    var tabela = document.getElementById('tabela-central-iipr');
    if (!tabela) {
        tabela = document.getElementById('tblCentralIIPR');
    }
    if (!tabela) return;
    
    var splitIndex = parseInt((document.getElementById('central_split_index') || {value: -1}).value, 10);
    if (isNaN(splitIndex) || splitIndex < 0) return;
    
    var rows = tabela.querySelectorAll('tbody tr');
    if (!rows.length) rows = tabela.querySelectorAll('tr:not(:first-child)');
    if (!rows.length) return;
    
    // Encontrar indices das colunas
    var ths = tabela.querySelectorAll('thead th, tr:first-child th');
    var idxLacre = -1, idxEtiqueta = -1;
    for (var i = 0; i < ths.length; i++) {
        var texto = (ths[i].textContent || '').toLowerCase();
        if (texto.indexOf('lacre correios') >= 0) idxLacre = i;
        if (texto.indexOf('etiqueta correios') >= 0) idxEtiqueta = i;
    }
    
    // Obter valores do primeiro grupo (antes do split)
    var valorLacreGrupo1 = '', valorEtiquetaGrupo1 = '';
    if (rows[0]) {
        if (idxLacre >= 0 && rows[0].children[idxLacre]) {
            var inp = rows[0].children[idxLacre].querySelector('input');
            if (inp) valorLacreGrupo1 = inp.value || '';
        }
        if (idxEtiqueta >= 0 && rows[0].children[idxEtiqueta]) {
            var inp = rows[0].children[idxEtiqueta].querySelector('input');
            if (inp) valorEtiquetaGrupo1 = inp.value || '';
        }
    }
    
    // Obter valores do segundo grupo (depois do split)
    var valorLacreGrupo2 = '', valorEtiquetaGrupo2 = '';
    if (splitIndex + 1 < rows.length) {
        var primeiraLinhaGrupo2 = rows[splitIndex + 1];
        if (idxLacre >= 0 && primeiraLinhaGrupo2.children[idxLacre]) {
            var inp = primeiraLinhaGrupo2.children[idxLacre].querySelector('input');
            if (inp) valorLacreGrupo2 = inp.value || '';
        }
        if (idxEtiqueta >= 0 && primeiraLinhaGrupo2.children[idxEtiqueta]) {
            var inp = primeiraLinhaGrupo2.children[idxEtiqueta].querySelector('input');
            if (inp) valorEtiquetaGrupo2 = inp.value || '';
        }
    }
    
    // Aplicar valores para todas as linhas de cada grupo
    for (var i = 0; i < rows.length; i++) {
        var row = rows[i];
        
        if (i <= splitIndex) {
            // Grupo 1: antes do split
            if (idxLacre >= 0 && row.children[idxLacre]) {
                var inp = row.children[idxLacre].querySelector('input');
                if (inp) {
                    inp.value = valorLacreGrupo1;
                    inp.setAttribute('value', valorLacreGrupo1);
                }
            }
            if (idxEtiqueta >= 0 && row.children[idxEtiqueta]) {
                var inp = row.children[idxEtiqueta].querySelector('input');
                if (inp) {
                    inp.value = valorEtiquetaGrupo1;
                    inp.setAttribute('value', valorEtiquetaGrupo1);
                }
            }
        } else {
            // Grupo 2: depois do split
            if (idxLacre >= 0 && row.children[idxLacre]) {
                var inp = row.children[idxLacre].querySelector('input');
                if (inp) {
                    inp.value = valorLacreGrupo2;
                    inp.setAttribute('value', valorLacreGrupo2);
                }
            }
            if (idxEtiqueta >= 0 && row.children[idxEtiqueta]) {
                var inp = row.children[idxEtiqueta].querySelector('input');
                if (inp) {
                    inp.value = valorEtiquetaGrupo2;
                    inp.setAttribute('value', valorEtiquetaGrupo2);
                }
            }
        }
    }
}
</script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Configuracao do zoom
    var body = document.body;
    var zoomInBtn = document.getElementById('zoom-in');
    var zoomOutBtn = document.getElementById('zoom-out');
    var currentZoomLevel = 0; // 0 = normal, 1 = grande, 2 = muito grande
    
    // Verificar se ha um nivel de zoom salvo no localStorage
    if (localStorage.getItem('zoomLevel')) {
        currentZoomLevel = parseInt(localStorage.getItem('zoomLevel'));
        applyZoom();
    }
    
    if (zoomInBtn) {
        zoomInBtn.addEventListener('click', function() {
            if (currentZoomLevel < 2) {
                currentZoomLevel++;
                applyZoom();
                localStorage.setItem('zoomLevel', currentZoomLevel);
            }
        });
    }
    
    if (zoomOutBtn) {
        zoomOutBtn.addEventListener('click', function() {
            if (currentZoomLevel > 0) {
                currentZoomLevel--;
                applyZoom();
                localStorage.setItem('zoomLevel', currentZoomLevel);
            }
        });
    }
    
    function applyZoom() {
        body.className = body.className.replace(/zoom-level-[0-2]/g, '').trim();
        if (currentZoomLevel > 0) {
            body.className = body.className + ' zoom-level-' + currentZoomLevel;
        }
    }
    
    // Preencher automaticamente a regional com base no posto selecionado
    var selectPosto = document.querySelector('select[name="posto"]');
    var selectRegional = document.querySelector('select[name="regional"]');
    
    if (selectPosto && selectRegional) {
        selectPosto.addEventListener('change', function() {
            var option = this.options[this.selectedIndex];
            var regionalSugerida = option.getAttribute('data-regional');
            
            if (regionalSugerida) {
                // Encontrar a opcao correspondente no select de regionais
                var opts = selectRegional.options;
                for (var j = 0; j < opts.length; j++) {
                    if (opts[j].value === regionalSugerida || opts[j].value.indexOf(regionalSugerida + ' -') === 0) {
                        selectRegional.value = opts[j].value;
                        break;
                    }
                }
            }
            
            // Preencher o campo nome com um valor padrao
            var nomePosto = document.querySelector('input[name="nome"]');
            if (nomePosto && this.value) {
                nomePosto.value = 'Posto ' + this.value;
            }
        });
    }

    // V8.0: Pure JavaScript implementation for SPLIT and field replication (no AJAX)
    var splitIndexCentral = null;
    // v8.11.1: indices visuais para destacar multiplos splits
    window.splitVisualIndices = window.splitVisualIndices || [];

    // Aplica destaque visual nas linhas da CENTRAL de acordo com splitVisualIndices
    function aplicarDestaqueSplits() {
        var linhasCentral = document.querySelectorAll('tr.linha-central');
        if (!linhasCentral) return;

        // Remover classes antigas
        for (var i = 0; i < linhasCentral.length; i++) {
            for (var g = 1; g <= 5; g++) {
                removerClasse(linhasCentral[i], 'split-central-grupo' + g);
            }
        }

        if (!window.splitVisualIndices || window.splitVisualIndices.length === 0) return;

        // Ordenar indices e aplicar classes aos ranges abaixo de cada split
        var indices = window.splitVisualIndices.slice(0).sort(function(a,b){return a-b;});
        var total = linhasCentral.length;
        for (var gi = 0; gi < indices.length; gi++) {
            var start = indices[gi] + 1; // linhas abaixo do split
            var end = (gi + 1 < indices.length) ? (indices[gi+1]) : (total - 1);
            var classe = 'split-central-grupo' + (gi + 1);
            for (var r = start; r <= end; r++) {
                if (r >= 0 && r < total) {
                    adicionarClasse(linhasCentral[r], classe);
                }
            }
        }
    }

    // Function to define split position (called by button onclick)
    window.definirSplitAqui = function(btn) {
        var tr = btn;
        while (tr && tr.tagName !== 'TR') tr = tr.parentNode;
        if (!tr) return;

        var linhasCentral = document.querySelectorAll('tr.linha-central');
        var idx = -1;
        for (var i = 0; i < linhasCentral.length; i++) {
            if (linhasCentral[i] === tr) { idx = i; break; }
        }
        if (idx < 0) return;

        // Toggle visual split: se ja existe no array, remover; senao adicionar
        var foundPos = -1;
        for (var z = 0; z < window.splitVisualIndices.length; z++) {
            if (window.splitVisualIndices[z] === idx) { foundPos = z; break; }
        }
        if (foundPos >= 0) {
            // remover
            window.splitVisualIndices.splice(foundPos, 1);
            btn.style.background = '';
            btn.textContent = 'Split aqui';
        } else {
            // adicionar
            window.splitVisualIndices.push(idx);
            btn.style.background = '#ff9800';
            btn.textContent = '← Split AQUI';
        }

        // Manter compatibilidade com comportamento antigo: alternar splitIndexCentral para logica de replicacao
        if (splitIndexCentral === idx) {
            splitIndexCentral = null;
        } else {
            // Limpar estilo de botoes de split antigos para que apenas o "ativo" (logica) fique destacado
            var allSplitBtns = document.querySelectorAll('button[onclick*="definirSplitAqui"]');
            for (var j = 0; j < allSplitBtns.length; j++) {
                allSplitBtns[j].style.border = '';
            }
            splitIndexCentral = idx;
            btn.style.border = '2px solid #ff9800';
        }

        // Aplicar destaques visuais
        aplicarDestaqueSplits();
    };
    
    // Function to replicate value within appropriate group (called by input listeners)
    window.replicarValor = function(campo, tipo) {
        var linhasCentral = document.querySelectorAll('tr.linha-central');
        var tr = campo;
        while (tr && tr.tagName !== 'TR') tr = tr.parentNode;
        if (!tr) return;
        
        var rowIndex = -1;
        for (var i = 0; i < linhasCentral.length; i++) {
            if (linhasCentral[i] === tr) { rowIndex = i; break; }
        }
        if (rowIndex < 0) return;
        
        var valor = campo.value;
        var selector = (tipo === 'correios') ? 'input.central-correios' : 'input.central-etiqueta';
        
        if (splitIndexCentral === null) {
            // No split: replicate to all CENTRAL fields of this type
            var campos = document.querySelectorAll(selector);
            for (var j = 0; j < campos.length; j++) {
                campos[j].value = valor;
            }
        } else {
            // Split active: replicate only within the group
            var groupStart, groupEnd;
            if (rowIndex <= splitIndexCentral) {
                // Editing in group 1 (before/at split): replicate to group 1 only
                groupStart = 0;
                groupEnd = splitIndexCentral;
            } else {
                // Editing in group 2 (after split): replicate to group 2 only
                groupStart = splitIndexCentral + 1;
                groupEnd = linhasCentral.length - 1;
            }
            
            // Apply to fields in the appropriate group
            var campos = document.querySelectorAll(selector);
            for (var k = 0; k < campos.length; k++) {
                var fieldTr = campos[k];
                while (fieldTr && fieldTr.tagName !== 'TR') fieldTr = fieldTr.parentNode;
                if (!fieldTr) continue;
                
                var fieldRowIndex = -1;
                for (var n = 0; n < linhasCentral.length; n++) {
                    if (linhasCentral[n] === fieldTr) { fieldRowIndex = n; break; }
                }
                
                // Replicate if field is within the current group
                if (fieldRowIndex >= groupStart && fieldRowIndex <= groupEnd) {
                    campos[k].value = valor;
                }
            }
        }
    };
    
    // Add event listeners to central-correios inputs
    var centralCorreioInputs = document.querySelectorAll('input.central-correios');
    for (var c = 0; c < centralCorreioInputs.length; c++) {
        (function(campo) {
            campo.addEventListener('change', function() {
                replicarValor(campo, 'correios');
            });
        })(centralCorreioInputs[c]);
    }
    
    // Add event listeners to central-etiqueta inputs
    var centralEtiquetaInputs = document.querySelectorAll('input.central-etiqueta');
    for (var e = 0; e < centralEtiquetaInputs.length; e++) {
        (function(campo) {
            campo.addEventListener('change', function() {
                replicarValor(campo, 'etiqueta');
            });
        })(centralEtiquetaInputs[e]);
    }
    
    // v8.4: Função auxiliar para focar no próximo input de etiqueta_correios
    window.focarProximaEtiqueta = function(inputAtual) {
        // Buscar todos os inputs de etiqueta_correios (class etiqueta-barras que estão em inputs válidos)
        var todosEtiquetas = document.querySelectorAll('input.etiqueta-barras');
        var indices = [];
        for (var i = 0; i < todosEtiquetas.length; i++) {
            indices.push(todosEtiquetas[i]);
        }
        
        // Encontrar índice do input atual
        var indiceAtual = -1;
        for (var j = 0; j < indices.length; j++) {
            if (indices[j] === inputAtual) {
                indiceAtual = j;
                break;
            }
        }
        
        // Se houver próximo input, focar nele
        if (indiceAtual >= 0 && indiceAtual + 1 < indices.length) {
            var proximoInput = indices[indiceAtual + 1];
            // Aguardar um pouco para garantir que o DOM foi atualizado
            setTimeout(function() {
                proximoInput.focus();
                // Selecionar texto se houver para facilitar sobrescrita
                if (proximoInput.select) {
                    proximoInput.select();
                }
            }, 50);
        }
    };
    
    // v8.3 CORRIGIDA: Validação de etiquetas_correios duplicadas para CAPITAL + REGIONAIS (não CENTRAL)
    // CORREÇÃO: Usar blur em vez de change, limpar campo sem travar, sem guardas globais
    var etiquetasValidaveis = document.querySelectorAll('input.etiqueta-validavel');
    for (var v = 0; v < etiquetasValidaveis.length; v++) {
        (function(inputEtiqueta) {
            // v8.4: Listener de input para disparar blur quando atingir 35 dígitos (para scanner/leitura automática)
            inputEtiqueta.addEventListener('input', function() {
                var valor = (this.value || '').replace(/\D/g, ''); // Remove tudo que não é dígito
                if (valor.length >= 35) {
                    // Disparar blur para que a validação execute
                    this.blur();
                }
            });
            
            inputEtiqueta.addEventListener('blur', function() {
                var valorAtual = (this.value || '').trim();
                var indice = this.getAttribute('data-indice');
                var grupoAtual = this.getAttribute('data-grupo') || '';
                
                // Se campo vazio, apenas limpar aviso
                if (valorAtual === '') {
                    this.style.background = '';
                    var alertaDiv = document.getElementById('alerta-' + indice);
                    if (alertaDiv) { alertaDiv.style.display = 'none'; alertaDiv.textContent = ''; }
                    return;
                }
                
                // v8.3 CORRIGIDA: Contar ocorrências deste valor em CAPITAL (regional=0) + REGIONAIS, excluindo CENTRAL IIPR
                var totalOcorrencias = 0;
                for (var i = 0; i < etiquetasValidaveis.length; i++) {
                    var outroInput = etiquetasValidaveis[i];
                    var outroGrupo = outroInput.getAttribute('data-grupo') || '';
                    
                    // Saltar se for CENTRAL IIPR (central pode compartilhar)
                    if (outroGrupo === 'CENTRAL IIPR') continue;
                    
                    var outroValor = (outroInput.value || '').trim();
                    if (outroValor === valorAtual) {
                        totalOcorrencias++;
                    }
                }
                
                // Se tem duplicata (mais de 1 ocorrência), limpar campo e alertar
                if (totalOcorrencias > 1) {
                    alert('Já existe outro posto com esta mesma etiqueta dos Correios. Cada etiqueta deve ser única para capital e regionais.');
                    // Limpar apenas o campo atual, sem reverter a anteriores
                    this.value = '';
                    this.style.background = '';
                    // Mostrar aviso no div de alerta associado
                    var alertaDiv = document.getElementById('alerta-' + indice);
                    if (alertaDiv) {
                        alertaDiv.textContent = 'Campo limpo. Digite novamente sem duplicar.';
                        alertaDiv.style.display = 'block';
                        alertaDiv.style.color = '#d00';
                        alertaDiv.style.fontSize = '11px';
                        alertaDiv.style.fontWeight = 'bold';
                    }
                    // Recolocar foco no campo para permitir nova digitação (NÃO avança)
                    this.focus();
                } else {
                    // Aceita o valor - limpar aviso
                    this.style.background = '';
                    var alertaDiv = document.getElementById('alerta-' + indice);
                    if (alertaDiv) { alertaDiv.style.display = 'none'; alertaDiv.textContent = ''; }
                    // v8.4: Se aceito, avançar para o próximo input de etiqueta
                    focarProximaEtiqueta(this);
                }
            });
        })(etiquetasValidaveis[v]);
    }
});

// Funcoes para o modal
function abrirModalInserir(botao) {
    // Obter dados do posto e operacao
    var posto = botao.getAttribute('data-posto');
    var grupo = botao.getAttribute('data-grupo');
    var posicao = botao.getAttribute('data-posicao');
    
    // Preencher campos do modal
    document.getElementById('referencia_posto').value = posto;
    document.getElementById('posicao_insercao').value = posicao;
    document.getElementById('novo_grupo').value = grupo;
    
    // Sugerir valores para os lacres baseados no posto de referencia
    var tr = botao;
    while (tr && tr.tagName !== 'TR') {
        tr = tr.parentNode;
    }
    if (tr) {
        var lacreIiprEl = tr.querySelector('input.lacre[data-tipo="iipr"]');
        var lacreCorreiosEl = tr.querySelector('input.lacre[data-tipo="correios"]');
        var lacreIipr = lacreIiprEl ? lacreIiprEl.value : '0';
        var lacreCorreios = lacreCorreiosEl ? lacreCorreiosEl.value : '0';
        
        document.getElementById('novo_lacre_iipr').value = parseInt(lacreIipr) + 1;
        document.getElementById('novo_lacre_correios').value = parseInt(lacreCorreios) + 1;
    }
    
    // Focar no campo de nome
    document.getElementById('novo_nome').value = '';
    document.getElementById('novo_nome').focus();
    
    // Mostrar o modal
    var modal = document.getElementById('modal-inserir');
    if (modal) {
        modal.className = modal.className + ' active';
    }
}

function fecharModal() {
    var modal = document.getElementById('modal-inserir');
    if (modal) {
        modal.className = modal.className.replace(/\s*active/g, '');
    }
}

// V7.6: Funcoes para modal de confirmacao de salvamento
function abrirModalConfirmacao() {
    // Contar etiquetas validas
    var etiquetasValidas = contarEtiquetasValidas();
    document.getElementById('contador-etiquetas').textContent = etiquetasValidas;
    
    // Mostrar o modal
    var modal = document.getElementById('modal-confirmacao-salvamento');
    if (modal) {
        modal.className = modal.className + ' active';
    }
    
    // Focar no campo de nome
    document.getElementById('login_personalizado').focus();
}

function fecharModalConfirmacao() {
    var modal = document.getElementById('modal-confirmacao-salvamento');
    if (modal) {
        modal.className = modal.className.replace(/\s*active/g, '');
    }
}

function contarEtiquetasValidas() {
    var contador = 0;
    var campos = document.querySelectorAll('input.etiqueta-barras');
    for (var n = 0; n < campos.length; n++) {
        if (campos[n].value && campos[n].value.length === 35) {
            contador++;
        }
    }
    return contador;
}

// Fechar modais ao clicar fora
document.addEventListener('click', function(e) {
    if (e.target.className && e.target.className.indexOf('modal-overlay') >= 0) {
        fecharModal();
        fecharModalConfirmacao();
    }
});

// V8.1: Funcao para toggle do painel de analise
function toggleAnalisePanel() {
    var painel = document.getElementById('painel-analise');
    if (painel) {
        if (painel.className.indexOf('collapsed') >= 0) {
            painel.className = painel.className.replace(/\s*collapsed/g, '');
            localStorage.setItem('painelAnaliseCollapsed', 'false');
        } else {
            painel.className = painel.className + ' collapsed';
            localStorage.setItem('painelAnaliseCollapsed', 'true');
        }
    }
}

// V8.1: Funcao para mostrar/esconder painel de insercao
function togglePainelInsercao() {
    var painel = document.getElementById('painel-insercao');
    if (painel) {
        if (painel.className.indexOf('ativo') >= 0) {
            painel.className = painel.className.replace(/\s*ativo/g, '');
        } else {
            painel.className = painel.className + ' ativo';
            // Focar no campo de codigo de barras quando abrir
            setTimeout(function() {
                var codigoBarras = document.getElementById('codigo_barras');
                if (codigoBarras) codigoBarras.focus();
            }, 100);
        }
    }
}

// V8.1: Inicializacao da interface
document.addEventListener('DOMContentLoaded', function() {
    // V8.1: Mensagens automaticas que desaparecem
    var mensagemAuto = document.getElementById('mensagem-auto');
    if (mensagemAuto) {
        setTimeout(function() {
            mensagemAuto.className = mensagemAuto.className + ' fadeOut';
            setTimeout(function() {
                if (mensagemAuto.parentNode) {
                    mensagemAuto.parentNode.removeChild(mensagemAuto);
                }
            }, 500);
        }, 3000); // Desaparece apos 3 segundos
    }
    
    // V8.1: Validacao do codigo de barras
    var codigoBarrasInput = document.getElementById('codigo_barras');
    if (codigoBarrasInput) {
        codigoBarrasInput.addEventListener('input', function(e) {
            var value = e.target.value.replace(/\D/g, ''); // Apenas numeros
            
            if (value.length > 19) {
                value = value.substring(0, 19);
            }
            
            e.target.value = value;
            
            // Validar se tem 19 dígitos
            if (value.length === 19) {
                e.target.style.borderColor = '#28a745'; // Verde se válido
            } else {
                e.target.style.borderColor = '#dc3545'; // Vermelho se inválido
            }
        });
        
        // Permitir colagem e limpeza automatica
        codigoBarrasInput.addEventListener('paste', function(e) {
            var target = e.target;
            setTimeout(function() {
                var value = target.value.replace(/\D/g, '');
                if (value.length > 19) {
                    value = value.substring(0, 19);
                }
                target.value = value;
            }, 10);
        });
    }
    
    // V8.0: Restaurar estado do painel de analise
    var painelAnalise = document.getElementById('painel-analise');
    if (painelAnalise && localStorage.getItem('painelAnaliseCollapsed') === 'true') {
        painelAnalise.className = painelAnalise.className + ' collapsed';
    }
    
    // V8.0: Mascara para data no formato dd/mm/aaaa
    var dataInput = document.getElementById('data_inserir');
    if (dataInput) {
        dataInput.addEventListener('input', function(e) {
            var value = e.target.value.replace(/\D/g, '');
            
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2);
            }
            if (value.length >= 5) {
                value = value.substring(0, 5) + '/' + value.substring(5, 9);
            }
            
            e.target.value = value;
        });
        
        // Preencher com data atual se estiver vazio
        if (!dataInput.value) {
            var hoje = new Date();
            var dia = hoje.getDate();
            var mes = hoje.getMonth() + 1;
            var ano = hoje.getFullYear();
            dia = dia < 10 ? '0' + dia : dia;
            mes = mes < 10 ? '0' + mes : mes;
            dataInput.value = dia + '/' + mes + '/' + ano;
        }
    }
});
</script>


<?php
if (!function_exists('__pt_session_v222')) {
  function __pt_session_v222(){ if(function_exists('session_status')){ if(session_status()!==PHP_SESSION_ACTIVE) @session_start(); } else { if(!session_id()) @session_start(); } }
}
__pt_session_v222();

$__datas_filtro = isset($datas_filtro)?$datas_filtro:(isset($_POST['datas_filtro'])?$_POST['datas_filtro']:(isset($_GET['datas_filtro'])?$_GET['datas_filtro']:array()));
if (!is_array($__datas_filtro)) $__datas_filtro = array_filter(array_map('trim', explode(',', (string)$__datas_filtro)));

if (!function_exists('__pt_norm_dates_v222')){
  function __pt_norm_dates_v222($arr){
    $out=array();
    foreach((array)$arr as $d){
      $d=trim((string)$d); if(!$d) continue;
      if (preg_match('/^\d{2}[-\/]\d{2}[-\/]\d{4}$/',$d)){ $sep=strpos($d,'/')!==false?'/':'-'; $p=explode($sep,$d); $out[]=sprintf('%04d-%02d-%02d',(int)$p[2],(int)$p[1],(int)$p[0]); }
      elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/',$d)){ $out[]=$d; }
    }
    return array_values(array_unique($out));
  }
}
$__datas_norm = __pt_norm_dates_v222($__datas_filtro);


// ===================================================================
// BLOCO POUPA TEMPO – Gera payload com endereço para o ofício
// ===================================================================

$poupaTempoPayload = array();

try {
    if (isset($pdo_controle) && $pdo_controle instanceof PDO && !empty($__datas_norm)) {

        // Datas normalizadas (as mesmas usadas no restante da tela)
        $in = "'" . implode("','", array_map('strval', $__datas_norm)) . "'";

        $sql = "
            SELECT 
                LPAD(c.posto,3,'0') AS codigo,
                COALESCE(r.nome, CONCAT('POUPA TEMPO - ', LPAD(c.posto,3,'0'))) AS nome,
                SUM(COALESCE(c.quantidade,0)) AS quantidade,
                r.endereco AS endereco
            FROM ciPostosCsv c
            INNER JOIN ciRegionais r 
                    ON LPAD(r.posto,3,'0') = LPAD(c.posto,3,'0')
            WHERE DATE(c.dataCarga) IN ($in)
              AND REPLACE(LOWER(r.entrega),' ','') LIKE 'poupa%tempo'
            GROUP BY 
                LPAD(c.posto,3,'0'), r.nome, r.endereco
            ORDER BY 
                LPAD(c.posto,3,'0')
        ";

        $stmt = $pdo_controle->query($sql, PDO::FETCH_ASSOC);

        foreach ($stmt as $r) {
            $poupaTempoPayload[] = array(
                'codigo'     => (string)$r['codigo'],          // ex: "006"
                'nome'       => (string)$r['nome'],            // ex: "POUPA TEMPO - PINHEIRINHO"
                'quantidade' => (int)$r['quantidade'],         // soma das CINs
                'lacre'      => '',
                'endereco'   => (string)$r['endereco']         // ENDEREÇO vindo da ciRegionais
            );
        }
    }
} catch (Exception $e) {
    // Se quiser depurar:
    // echo "<pre>ERRO POUPATEMPO SQL: ".$e->getMessage()."</pre>";
}

// Fallback antigo (apenas se o SELECT não trouxe nada)
// Usa o array $dados, caso ainda exista a estrutura antiga
if (!$poupaTempoPayload && isset($dados) && is_array($dados)) {
    $cands = array('POUPA TEMPO','Poupa Tempo','POUPATEMPO','POUPA-TEMPO');
    $src   = array();

    foreach ($cands as $k) {
        if (isset($dados[$k]) && is_array($dados[$k])) {
            $src = $dados[$k];
            break;
        }
    }

    if ($src) {
        $agr = array();
        foreach ($src as $r) {
            $codigo = sprintf(
                '%03s',
                preg_replace('/\D+/', '', (string)(
                    isset($r['posto_codigo']) ? $r['posto_codigo'] :
                    (isset($r['codigo']) ? $r['codigo'] : '')
                ))
            );
            $nome = trim((string)(
                isset($r['posto_nome']) ? $r['posto_nome'] :
                (isset($r['nome']) ? $r['nome'] : '')
            ));
            $qtd  = (int)(
                isset($r['quantidade']) ? $r['quantidade'] :
                (isset($r['qtd']) ? $r['qtd'] : 0)
            );
            if (!$codigo) continue;

            $key = $codigo.'|'.$nome;
            if (!isset($agr[$key])) {
                $agr[$key] = array(
                    'codigo'   => $codigo,
                    'nome'     => $nome,
                    'quantidade' => 0,
                    'lacre'    => '',
                    'endereco' => '' // aqui não temos endereço nesse fallback
                );
            }
            $agr[$key]['quantidade'] += $qtd;
        }
        $poupaTempoPayload = array_values($agr);
        usort($poupaTempoPayload, function($a,$b){
            return (int)$a['codigo'] - (int)$b['codigo'];
        });
    }
}

// JSON que vai para o modelo_oficio_poupa_tempo.php
$poupaTempoPayloadJson = json_encode($poupaTempoPayload ?: array());

// Datas em string (as mesmas usadas na tela)
$__pt_datas_join = htmlspecialchars(
    implode(',', isset($__datas_norm) ? $__datas_norm : array()),
    ENT_QUOTES,
    'UTF-8'
);
?>

<!-- ==================================================================
     FORMULÁRIO OCULTO – Gera o ofício Poupatempo em nova aba
     ================================================================== -->
<form id="oficioPTForm" method="post" action="modelo_oficio_poupa_tempo.php" target="_blank" style="display:none;">
  <input type="hidden" name="acao" value="oficio_poupatempo" />
  <input type="hidden" name="pt_datas" value="<?php echo $__pt_datas_join; ?>" />
  <input type="hidden" name="<?php echo htmlspecialchars(session_name(),ENT_QUOTES,'UTF-8'); ?>"
         value="<?php echo htmlspecialchars(session_id(),ENT_QUOTES,'UTF-8'); ?>" />
  <textarea name="poupatempo_payload" style="display:none;"><?php
      echo htmlspecialchars($poupaTempoPayloadJson ?: '[]', ENT_QUOTES, 'UTF-8');
  ?></textarea>
</form>


<script>
(function(){
  function norm(t){ return (t||'').toLowerCase().replace(/\s+/g,' ').trim(); }
  function allBtns(){ return document.querySelectorAll('button, a, input[type="button"], input[type="submit"]'); }
  function findByText(list){
    var n=allBtns();
    for(var i=0;i<n.length;i++){
      var t=norm(n[i].innerText||n[i].value);
      for(var k=0;k<list.length;k++){ if(t.indexOf(list[k])>=0) return n[i]; }
    }
    return null;
  }
  function addBtn(){
    if (document.getElementById('btnOficioPT')) return;
    var salvar=findByText(['salvar etiquetas','salvar etiqueta']);
    var imprimir=findByText(['imprimir']);
    var ref=salvar||imprimir;
    var cont=(ref&&ref.parentElement)?ref.parentElement:document.body;
    var btn=document.createElement('button');
    btn.type='button'; btn.id='btnOficioPT';
    btn.className=(ref?(ref.className+' btn-oficio-pt'):'btn-oficio-pt');
    btn.innerHTML='<i class="icon-doc"></i> Gerar Ofício Poupa Tempo';
    cont.appendChild(btn);
    btn.addEventListener('click', function(){ var f=document.getElementById('oficioPTForm'); if(f) f.submit(); });
  }
  if (document.readyState==='loading') document.addEventListener('DOMContentLoaded', addBtn); else addBtn();
  new MutationObserver(function(){ if(!document.getElementById('btnOficioPT')) addBtn(); }).observe(document.documentElement,{childList:true,subtree:true});
})();
</script>

<!-- SPLIT CENTRAL IIPR - helper rename -->
<script>(function(){var t=document.getElementById('tabela-central-iipr');if(t){t.id='tblCentralIIPR';}})();</script>


<!-- SPLIT CENTRAL IIPR START -->
<style>
  #btnSplitCentral{margin-left:12px}
  tr.split-below{background:#fff9cc}
  #splitCentralModal{position:fixed; inset:0; background:rgba(0,0,0,.35); display:none;
    align-items:center; justify-content:center; z-index:9999;}
  #splitCentralModal .box{background:#fff; border-radius:10px; min-width:360px; max-width:640px;
    padding:16px; box-shadow:0 8px 24px rgba(0,0,0,.25)}
  #splitCentralModal h3{margin:0 0 8px 0}
  #splitCentralModal .list{max-height:320px; overflow:auto; border:1px solid #ddd; border-radius:8px; padding:8px}
  #splitCentralModal .row{display:flex; gap:8px; align-items:center; margin:4px 0}
  #splitCentralModal .actions{display:flex; gap:10px; justify-content:flex-end; margin-top:12px}
  #splitCentralModal button{padding:8px 12px; border-radius:6px; border:1px solid #bbb; cursor:pointer}
  #splitCentralModal .ok{background:#4caf50; color:#fff; border-color:#4caf50}
  #splitCentralModal .cancel{background:#ccc}
</style>

<div id="splitCentralModal">
  <div class="box">
    <h3>Escolha a partir de qual posto será feito o split (somente CENTRAL IIPR)</h3>
    <div class="list" id="splitCentralList"></div>
    <div class="actions">
      <button class="cancel" id="splitCentralCancel">Cancelar</button>
      <button class="ok" id="splitCentralApply">Aplicar</button>
    </div>
  </div>
</div>

<script>
(function(){
  function ensureCentralId(){
    var t = document.getElementById('tblCentralIIPR');
    if (t) return t;
    
    // Tentar encontrar pelo id da tabela gerada
    var tblCentral = document.getElementById('tabela-central-iipr');
    if (tblCentral) {
      tblCentral.id = 'tblCentralIIPR';
      return tblCentral;
    }
    
    // Fallback: buscar por cabecalho
    var headers = document.querySelectorAll('h1,h2,h3,h4,legend,div,section,span');
    var hdr = null;
    for (var h = 0; h < headers.length; h++) {
      if (/(^|\s)central\s+iipr(\s|$)/i.test((headers[h].textContent||'').trim())) {
        hdr = headers[h];
        break;
      }
    }
    if (hdr){
      var sib = hdr.nextElementSibling;
      while (sib && sib.tagName && sib.tagName.toLowerCase()!=='table'){ sib = sib.nextElementSibling; }
      if (sib){ sib.id='tblCentralIIPR'; return sib; }
    }
    var best=null, bestScore=0;
    var tables = document.querySelectorAll('table');
    for (var ti = 0; ti < tables.length; ti++) {
      var tb = tables[ti];
      var rows = tb.querySelectorAll('tbody tr'); if(!rows.length) rows = tb.querySelectorAll('tr:not(:first-child)');
      var ok=0, tot=0;
      for (var ri = 0; ri < rows.length; ri++) {
        var tr = rows[ri];
        var el = tr.querySelector("[name='regional'],[name='regional[]'],[data-regional]");
        var v = el ? (el.value || el.getAttribute('data-regional') || '') : '';
        if (String(v).trim()==='999') ok++;
        tot++;
      }
      var score = tot? ok/tot : 0;
      if (score>bestScore){ bestScore=score; best=tb; }
    }
    if (best && bestScore>=0.5){ best.id='tblCentralIIPR'; return best; }
    return null;
  }
  function norm(t){return (t||'').toLowerCase().replace(/\s+/g,' ').trim();}
  function ensureSplitButton(){
    var btn = document.getElementById('btnSplitCentral');
    if (!btn) {
      var anchorAfter = ['Gerar Oficio Poupa Tempo','Salvar Etiquetas','Imprimir'];
      var ref = null;
      var btnsAndLinks = document.querySelectorAll('a,button');
      for (var i=0;i<anchorAfter.length;i++){
        for (var j = 0; j < btnsAndLinks.length; j++) {
          if (norm(btnsAndLinks[j].textContent) === norm(anchorAfter[i])) {
            ref = btnsAndLinks[j];
            break;
          }
        }
        if (ref) break;
      }
      if (ref && ref.parentElement){
        btn = document.createElement('a');
        btn.id = 'btnSplitCentral';
        btn.href = 'javascript:void(0)';
        //btn.className = 'btn btn-primary';
        btn.className = ref ? ref.className : '';
        btn.innerHTML = '<span>Split da Central</span>';
        // só cor roxa aqui; resto do layout vem do ref
        btn.style.background  = '#7c63ff';
        btn.style.borderColor = '#7c63ff';

        ref.parentElement.insertBefore(btn, ref.nextSibling);
      }
    }
    return document.getElementById('btnSplitCentral');
  }
  function rowsOf(tbl){
    var rows = tbl.querySelectorAll('tbody tr');
    if (!rows.length) rows = tbl.querySelectorAll('tr:not(:first-child)');
    return rows;
  }
  function indexByHeader(tbl, headerText){
    var ths = tbl.querySelectorAll('thead th, tr:first-child th');
    var target = (headerText||'').toLowerCase();
    for (var i=0;i<ths.length;i++){
      if ((ths[i].textContent||'').toLowerCase().indexOf(target)>=0) return i;
    }
    return -1;
  }
  function cell(td){
    var inp = td ? td.querySelector('input,textarea') : null;
    return {
      get: function(){ return inp ? (inp.value||'') : (td ? (td.textContent||'') : ''); },
      set: function(v){ if (inp){ inp.value=v; inp.setAttribute('value', v); } else if (td){ td.textContent=v; } },
      lock: function(on){ 
        if (inp){ 
          inp.readOnly = !!on; 
          inp.disabled = false;
          // VERSAO 4: Remover atributo readonly do HTML se estiver desbloqueando
          if (!on) {
            inp.removeAttribute('readonly');
            // Remover classe readonly se existir
            if (inp.className.indexOf('etiqueta-central-readonly') >= 0) {
              inp.className = inp.className.replace(/\s*etiqueta-central-readonly/g, '');
              if (inp.className.indexOf('etiqueta-central') < 0) {
                inp.className = inp.className + ' etiqueta-central';
              }
            }
          } else {
            inp.setAttribute('readonly', 'readonly');
          }
        } 
      }
    };
  }
  function defaults(tbl, idxL, idxE){
    var rs = rowsOf(tbl); if (!rs.length) return {lacre:'', etiqueta:''};
    var cL = (idxL>=0) ? rs[0].children[idxL] : null;
    var cE = (idxE>=0) ? rs[0].children[idxE] : null;
    return {lacre: cell(cL).get().trim(), etiqueta: cell(cE).get().trim()};
  }
  // Funcao auxiliar para adicionar classe (compativel com navegadores antigos)
  function addClass(el, classe) {
    if (!el) return;
    if (el.className.indexOf(classe) < 0) {
      el.className = el.className + ' ' + classe;
    }
  }
  // Funcao auxiliar para remover classe (compativel com navegadores antigos)
  function removeClass(el, classe) {
    if (!el) return;
    var regex = new RegExp('\\s*' + classe, 'g');
    el.className = el.className.replace(regex, '');
  }
  function applyAt(tbl, splitIndex){
    var rs = rowsOf(tbl); if (!rs.length) return;
    var idxL = indexByHeader(tbl,'lacre correios');
    var idxE = indexByHeader(tbl,'etiqueta correios');
    var def = defaults(tbl, idxL, idxE);
    
    // Primeiro, marcar visualmente o split e configurar editabilidade
    for (var i=0;i<rs.length;i++){
      var r = rs[i];
      var cL = (idxL>=0) ? r.children[idxL] : null;
      var cE = (idxE>=0) ? r.children[idxE] : null;
      
      if (i<=splitIndex){
        // GRUPO 1 (acima do split)
        removeClass(r, 'split-below');
        if (i === 0) {
          // Primeira linha do grupo 1: editavel (define o valor do grupo)
          if (cL) cell(cL).set(def.lacre), cell(cL).lock(false);
          if (cE) cell(cE).set(def.etiqueta), cell(cE).lock(false);
        } else {
          // Demais linhas do grupo 1: readonly, recebem o valor da primeira linha
          if (cL) cell(cL).set(def.lacre), cell(cL).lock(true);
          if (cE) cell(cE).set(def.etiqueta), cell(cE).lock(true);
        }
      }else{
        // GRUPO 2 (abaixo do split)
        addClass(r, 'split-below');
        if (i === splitIndex + 1) {
          // Primeira linha do grupo 2: editavel (define o valor do grupo)
          if (cL) cell(cL).set(''), cell(cL).lock(false);
          if (cE) cell(cE).set(''), cell(cE).lock(false);
        } else {
          // Demais linhas do grupo 2: readonly, receberao o valor da primeira linha do grupo 2
          if (cL) cell(cL).set(''), cell(cL).lock(true);
          if (cE) cell(cE).set(''), cell(cE).lock(true);
        }
      }
    }
    var hid = document.getElementById('central_split_index');
    if (!hid){
      hid = document.createElement('input');
      hid.type='hidden'; hid.name='central_split_index'; hid.id='central_split_index';
      document.body.appendChild(hid);
    }
    hid.value = String(splitIndex);
    
    // Configurar propagacao automatica para ambos os grupos
    configurarPropagacaoGrupos(tbl, splitIndex, idxL, idxE);
    
    // VERSAO 3 CORRIGIDA: Aplicar valores imediatamente apos ativar split
    // Propagar valores existentes da primeira linha de cada grupo para as demais
    propagarValoresIniciais(tbl, splitIndex, idxL, idxE);
  }
  
  // Funcao para propagar valores iniciais imediatamente apos ativar o split
  function propagarValoresIniciais(tbl, splitIndex, idxL, idxE) {
    var rs = rowsOf(tbl);
    if (!rs.length) return;
    
    // GRUPO 1: Propagar valores da linha 0 para linhas 1 ate splitIndex
    if (rs[0]) {
      var valorL1 = '', valorE1 = '';
      if (idxL >= 0 && rs[0].children[idxL]) {
        var inp = rs[0].children[idxL].querySelector('input');
        if (inp) valorL1 = inp.value || '';
      }
      if (idxE >= 0 && rs[0].children[idxE]) {
        var inp = rs[0].children[idxE].querySelector('input');
        if (inp) valorE1 = inp.value || '';
      }
      
      for (var i = 1; i <= splitIndex && i < rs.length; i++) {
        if (idxL >= 0 && rs[i].children[idxL]) {
          var c = rs[i].children[idxL];
          cell(c).set(valorL1);
        }
        if (idxE >= 0 && rs[i].children[idxE]) {
          var c = rs[i].children[idxE];
          cell(c).set(valorE1);
        }
      }
    }
    
    // GRUPO 2: Propagar valores da linha splitIndex+1 para linhas splitIndex+2 ate o final
    var g2Start = splitIndex + 1;
    if (g2Start < rs.length && rs[g2Start]) {
      var valorL2 = '', valorE2 = '';
      if (idxL >= 0 && rs[g2Start].children[idxL]) {
        var inp = rs[g2Start].children[idxL].querySelector('input');
        if (inp) valorL2 = inp.value || '';
      }
      if (idxE >= 0 && rs[g2Start].children[idxE]) {
        var inp = rs[g2Start].children[idxE].querySelector('input');
        if (inp) valorE2 = inp.value || '';
      }
      
      for (var i = g2Start + 1; i < rs.length; i++) {
        if (idxL >= 0 && rs[i].children[idxL]) {
          var c = rs[i].children[idxL];
          cell(c).set(valorL2);
        }
        if (idxE >= 0 && rs[i].children[idxE]) {
          var c = rs[i].children[idxE];
          cell(c).set(valorE2);
        }
      }
    }
  }
  
  // Funcao para propagar valores dentro de cada grupo do split
  // VERSAO 3 CORRIGIDA: Propaga cada coluna INDEPENDENTEMENTE
  // Re-query DOM toda vez, sem usar variavel de estado
  
  function configurarPropagacaoGrupos(tbl, splitIndex, idxL, idxE) {
    // Re-query linhas da tabela toda vez
    var rs = rowsOf(tbl);
    if (!rs.length) return;
    
    // Funcao para propagar valor de UMA coluna para um grupo
    function propagarColunaParaGrupo(colIdx, valor, startIdx, endIdx) {
      if (colIdx < 0) return;
      var linhas = rowsOf(tbl); // Re-query para pegar estado atual
      for (var i = startIdx; i <= endIdx && i < linhas.length; i++) {
        var r = linhas[i];
        var c = r.children[colIdx];
        if (c) cell(c).set(valor);
      }
    }
    
    // Funcao para criar listener com closure correta
    function criarListener(colIdx, startIdx, endIdx) {
      return function() {
        propagarColunaParaGrupo(colIdx, this.value, startIdx, endIdx);
      };
    }
    
    // GRUPO 1: primeira linha (0) propaga para linhas 1 ate splitIndex
    // Remover listeners antigos e adicionar novos
    if (rs[0]) {
      if (idxL >= 0 && rs[0].children[idxL]) {
        var inpL1 = rs[0].children[idxL].querySelector('input');
        if (inpL1) {
          // Marcar com ID unico do split para evitar multiplos handlers
          var splitId = 'split_' + splitIndex + '_g1_l';
          if (inpL1.getAttribute('data-split-id') !== splitId) {
            inpL1.setAttribute('data-split-id', splitId);
            inpL1.addEventListener('input', criarListener(idxL, 1, splitIndex));
          }
        }
      }
      if (idxE >= 0 && rs[0].children[idxE]) {
        var inpE1 = rs[0].children[idxE].querySelector('input');
        if (inpE1) {
          var splitId = 'split_' + splitIndex + '_g1_e';
          if (inpE1.getAttribute('data-split-id') !== splitId) {
            inpE1.setAttribute('data-split-id', splitId);
            inpE1.addEventListener('input', criarListener(idxE, 1, splitIndex));
          }
        }
      }
    }
    
    // GRUPO 2: primeira linha (splitIndex+1) propaga para linhas (splitIndex+2) ate o final
    var g2Start = splitIndex + 1;
    if (g2Start < rs.length && rs[g2Start]) {
      var g2End = rs.length - 1;
      if (idxL >= 0 && rs[g2Start].children[idxL]) {
        var inpL2 = rs[g2Start].children[idxL].querySelector('input');
        if (inpL2) {
          var splitId = 'split_' + splitIndex + '_g2_l';
          if (inpL2.getAttribute('data-split-id') !== splitId) {
            inpL2.setAttribute('data-split-id', splitId);
            inpL2.addEventListener('input', criarListener(idxL, g2Start + 1, g2End));
          }
        }
      }
      if (idxE >= 0 && rs[g2Start].children[idxE]) {
        var inpE2 = rs[g2Start].children[idxE].querySelector('input');
        if (inpE2) {
          var splitId = 'split_' + splitIndex + '_g2_e';
          if (inpE2.getAttribute('data-split-id') !== splitId) {
            inpE2.setAttribute('data-split-id', splitId);
            inpE2.addEventListener('input', criarListener(idxE, g2Start + 1, g2End));
          }
        }
      }
    }
  }
  function removeSplit(tbl){
    var rs = rowsOf(tbl); if (!rs.length) return;
    var idxL = indexByHeader(tbl,'lacre correios');
    var idxE = indexByHeader(tbl,'etiqueta correios');
    var def = defaults(tbl, idxL, idxE);
    for (var i=0;i<rs.length;i++){
      var r = rs[i];
      var cL = (idxL>=0) ? r.children[idxL] : null;
      var cE = (idxE>=0) ? r.children[idxE] : null;
      if (cL) cell(cL).set(def.lacre), cell(cL).lock(true);
      if (cE) cell(cE).set(def.etiqueta), cell(cE).lock(true);
      removeClass(r, 'split-below');
    }
    var hid = document.getElementById('central_split_index');
    if (hid) hid.value = '';
  }
  function mount(){
    var btn = ensureSplitButton();
    var tbl = ensureCentralId();
    if (!tbl){
      if (btn) btn.addEventListener('click', function(){ alert('Tabela da CENTRAL IIPR não encontrada (#tblCentralIIPR).'); });
      return;
    }
    // Autofill abaixo do split (copia exatamente o valor digitado)
(function enableAutoFillBelowSplit(){
  var idxL = indexByHeader(tbl,'lacre correios');
  var idxE = indexByHeader(tbl,'etiqueta correios');
  if (idxL<0 && idxE<0) return;

  // Funcao auxiliar para encontrar elemento pai pelo nome de tag (substitui closest)
  function findParent(el, tagName) {
    var current = el;
    while (current && current.parentNode) {
      current = current.parentNode;
      if (current.tagName && current.tagName.toUpperCase() === tagName.toUpperCase()) {
        return current;
      }
    }
    return null;
  }

  // Funcao auxiliar para encontrar indice de elemento em lista (substitui indexOf)
  function findIndex(list, element) {
    for (var i = 0; i < list.length; i++) {
      if (list[i] === element) return i;
    }
    return -1;
  }

  // Normaliza inputs das colunas-alvo (evita truncar)
  var rs = rowsOf(tbl);
  var colsToCheck = [idxL, idxE];
  for (var trIdx = 0; trIdx < rs.length; trIdx++) {
    var tr = rs[trIdx];
    for (var colIdx = 0; colIdx < colsToCheck.length; colIdx++) {
      var col = colsToCheck[colIdx];
      if (col < 0) continue;
      var td = tr.children[col]; if (!td) continue;
      var inp = td.querySelector('input,textarea'); if (!inp) continue;
      inp.type = 'text';
      inp.inputMode = 'numeric';
      inp.maxLength = (col === idxE ? 35 : 10);
    }
  }

  // VERSAO 3: Handler global - so ativo quando NAO ha split
  // Quando split ativo, a propagacao e feita pelos listeners especificos de configurarPropagacaoGrupos
  tbl.addEventListener('input', function(ev){
    var target = ev.target;
    if (target.tagName!=='INPUT' && target.tagName!=='TEXTAREA') return;

    var splitIndex = parseInt((document.getElementById('central_split_index')||{value:-1}).value,10);
    
    // Se ha split ativo, ignora - propagacao gerenciada por configurarPropagacaoGrupos
    if (!isNaN(splitIndex) && splitIndex >= 0) return;

    var td = findParent(target, 'td'); if (!td) return;
    var tr = findParent(target, 'tr'); if (!tr) return;

    var rowIndex = findIndex(rs, tr);
    // Sem split: so a primeira linha propaga
    if (rowIndex !== 0) return;

    var colIndex = findIndex(tr.children, td);
    var isCorr   = (colIndex===idxL || colIndex===idxE);
    if (!isCorr) return;

    var val = String(target.value || target.textContent || '');

    for (var i=1;i<rs.length;i++){
      var cellTarget = rs[i].children[colIndex];
      if (!cellTarget) continue;
      var inp = cellTarget.querySelector('input,textarea');

      if (inp){
        inp.type = 'text';
        inp.inputMode = 'numeric';
        inp.maxLength = (colIndex===idxE ? 35 : 10);
        inp.value = val;
        inp.setAttribute('value', val);
      } else {
        cellTarget.textContent = val;
      }
    }
  });
})();


    if (btn){
      btn.addEventListener('click', function(){
        var rows = rowsOf(tbl);
        var labels = [];
        for (var i=0;i<rows.length;i++){
          var td = rows[i].children[0];
          var txt = (td ? (td.textContent||'').trim() : '') || ('Linha '+(i+1));
          labels.push(txt);
        }
        var list = document.getElementById('splitCentralList');
        list.innerHTML = '';
        for (var li = 0; li < labels.length; li++) {
          var d = document.createElement('div');
          d.className = 'row';
          d.innerHTML = '<input type="radio" name="split_row" value="'+li+'"> <span>'+labels[li]+'</span>';
          list.appendChild(d);
        }
        var modal = document.getElementById('splitCentralModal');
        document.getElementById('splitCentralCancel').onclick = function(){ modal.style.display='none'; };
        document.getElementById('splitCentralApply').onclick = function(){
          var sel = modal.querySelector('input[name="split_row"]:checked');
          if (!sel){ alert('Selecione o posto onde começa o segundo malote.'); return; }
          applyAt(tbl, parseInt(sel.value,10));
          modal.style.display='none';
        };
        modal.style.display = 'flex';
      });
      btn.addEventListener('contextmenu', function(e){ e.preventDefault(); removeSplit(tbl); });
    }
  }
  if (document.readyState==='loading') document.addEventListener('DOMContentLoaded', mount);
  else mount();
})();
</script>
<!-- SPLIT CENTRAL IIPR END -->

<!-- COSEP: begin endereco payload enrichment -->
<?php
$__COSEP_enderecosPoupa = array();
try {
    $___pdo_addr = isset($pdo_controle) ? $pdo_controle : (isset($pdo) ? $pdo : null);
    if ($___pdo_addr) {
        $___sql = "SELECT LPAD(CAST(posto AS UNSIGNED),3,'0') AS p3, endereco
                   FROM ciRegionais
                   WHERE REPLACE(LOWER(entrega),' ','') IN ('poupa-tempo','poupatempo')";
        $___st = $___pdo_addr->query($___sql);
        if ($___st) {
            foreach ($___st as $___row) {
                $__COSEP_enderecosPoupa[$___row['p3']] = $___row['endereco'];
            }
        }
    }
} catch (\Throwable $e) {
}
?>
<script>
(function(){
  try {
    if (!window.ENDERECOS_PTP) {
      window.ENDERECOS_PTP = <?php echo json_encode($__COSEP_enderecosPoupa, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); ?>;
    }
  } catch (e) {}

  // Funcao auxiliar para preencher string com zeros a esquerda (compativel com navegadores antigos)
  function padStart(str, targetLen, padChar) {
    str = String(str);
    while (str.length < targetLen) {
      str = padChar + str;
    }
    return str;
  }

  document.addEventListener('submit', function(ev){
    try{
      var f = ev.target;
      if (!f || !f.action) return;
      if (!/modelo_oficio_poupa_tempo\.php(\?|$)/.test(f.action)) return;
      var inp = f.querySelector("input[name='poupatempo_payload']");
      if (!inp || !inp.value) return;
      var data = JSON.parse(inp.value);
      if (Array.isArray(data)) {
        for (var di = 0; di < data.length; di++) {
          var it = data[di];
          var code = (it.codigo || it.posto || "").toString();
          var m = code.match(/\d{1,3}/);
          var p3 = m ? padStart(m[0], 3, "0") : "";
          if (!it.endereco && p3 && window.ENDERECOS_PTP && window.ENDERECOS_PTP[p3]) {
            it.endereco = window.ENDERECOS_PTP[p3];
          }
        }
        inp.value = JSON.stringify(data);
      }
    }catch(e){}
  }, true);
})();
</script>
<script type="text/javascript">
(function(){
  if (window.__btnSalvarPT_injected) return; // evita duplicar
  window.__btnSalvarPT_injected = true;

  function norm(t){return (t||'').toLowerCase().replace(/\s+/g,' ').trim();}

  // 1) Tenta achar o botão "âncora" (o Gerar Ofício Poupa Tempo)
  function acharReferencia(){
    // tente por id (se você colocou id no botão manualmente)
    var byId = document.getElementById('btnGerarOficioPT');
    if (byId) return byId;

    // fallback por texto visível
    var labels = ['Gerar Ofício Poupa Tempo','Gerar Oficio Poupa Tempo'];
    var nodes = document.querySelectorAll('a,button');
    for (var i=0;i<nodes.length;i++){
      if (labels.indexOf(nodes[i].textContent.trim()) >= 0) return nodes[i];
      if (norm(nodes[i].textContent) === norm('Gerar Ofício Poupa Tempo')) return nodes[i];
    }
    return null;
  }

  var ref = acharReferencia();
  if (!ref || !ref.parentNode){
    // Se não achou, mostra um log e não quebra nada
    console.log('[Salvar PT] Botão de referência não encontrado.');
    return;
  }

  // 2) Cria o novo botão com o MESMO estilo do "Gerar Ofício Poupa Tempo"
  var btn = document.createElement('button');
  btn.type = 'button';
  btn.id = 'btnSalvarOficioPT';
  btn.textContent = 'Salvar Ofício (PT)';
  btn.className = ref.className || '';        // copia estilo
  btn.style.marginLeft = '8px';               // pequeno espaçamento
  btn.style.textDecoration = 'none';          // tira sublinhado, se herdar <a>
  ref.parentNode.insertBefore(btn, ref.nextSibling);

  // 3) Função para coletar as linhas do Poupa Tempo
  function coletarPT(){
    // Se você já tem uma função que o "Gerar Ofício" usa, reaproveite:
    if (typeof window.coletarPoupaTempoPayload === 'function'){
      try { return window.coletarPoupaTempoPayload(); } catch(e){}
    }
    // Fallback: varre uma tabela com id típico
    var itens = [];
    var tbl = document.querySelector("#tabela-poupa-tempo") ||
              document.querySelector("table[id*='poupa']") ||
              document.querySelector("table[id*='tempo']");
    if (!tbl) return itens;

    var trs = tbl.querySelectorAll('tbody tr');
    for (var i=0;i<trs.length;i++){
      var tds = trs[i].children;
      if (!tds || tds.length < 3) continue;

      var codigo = (tds[0].textContent||'').trim(); // ex: "028 – Curitiba"
      var qtd = 0;
      var elQ = tds[1].querySelector('input,textarea');
      if (elQ){
        var raw = (elQ.value||elQ.textContent||'');
        raw = raw.replace(/[^\d]/g,'');
        qtd = parseInt(raw,10)||0;
      } else {
        var raw2 = (tds[1].textContent||'').replace(/[^\d]/g,'');
        qtd = parseInt(raw2,10)||0;
      }

      var lacre = '';
      var elL = tds[2].querySelector('input,textarea');
      if (elL) { lacre = elL.value||elL.textContent||''; }
      else { lacre = (tds[2].textContent||'').trim(); }

      // etiqueta pode estar na 4ª/5ª col., se existir na sua grade
      var etiqueta = null;
      var tdEtiq = tds[4] || null;
      if (tdEtiq){
        var inpE = tdEtiq.querySelector('input,textarea');
        if (inpE) etiqueta = inpE.value||'';
      }

      itens.push({codigo: codigo, nome: codigo, quantidade: qtd, lacre: lacre, etiqueta: etiqueta});
    }
    return itens;
  }

  // 4) Datas: pega os inputs name="datas[]"
  function coletarDatas(){
    var ds = document.querySelectorAll("input[name='datas[]']");
    var v = [];
    for (var i=0;i<ds.length;i++){
      var x = (ds[i].value||'').trim();
      if (x) v.push(x);
    }
    // fallback tenta um input de data padrão da sua tela
    if (!v.length){
      var d1 = document.getElementById('data_inserir');
      if (d1 && d1.value) v.push(d1.value);
    }
    return v;
  }

  // 5) Ao clicar, posta em form oculto para o handler PHP (acao=salvar_oficio_pt)
  btn.onclick = function(){
    var itens = coletarPT();
    if (!itens || !itens.length){
      alert('Nada do Poupa Tempo para salvar.');
      return;
    }
    var datas = coletarDatas();

    var f = document.createElement('form');
    f.method = 'post';
    f.action = ''; // mesma página
    var a = document.createElement('input'); a.type='hidden'; a.name='acao';       a.value='salvar_oficio_pt'; f.appendChild(a);
    var b = document.createElement('input'); b.type='hidden'; b.name='datas_str';  b.value=datas.join(',');    f.appendChild(b);
    var c = document.createElement('input'); c.type='hidden'; c.name='payload_json'; c.value=JSON.stringify(itens); f.appendChild(c);
    document.body.appendChild(f);
    f.submit();
  };

})();
</script>

<!-- COSEP: end endereco payload enrichment -->
</body>
</html>