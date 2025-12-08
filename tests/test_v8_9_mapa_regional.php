<?php
// Test da lógica de v8.9: construção de mapas regional e prioridade

echo "=== Teste da v8.9: Mapa Regional ===\n\n";

// Simular POST do formulário v8.9
$_POST['posto_lacres']      = array('041', '042', '050');
$_POST['regional_lacres']   = array('950', '950', '950');
$_POST['lacre_iipr']        = array('111', '222', '');
$_POST['lacre_correios']    = array('333', '444', '555');
$_POST['etiqueta_correios'] = array('ETQ041', 'ETQ042', 'ETQ050');

// Construir mapa por regional (v8.9)
$mapaLacresPorRegional = array();
$postosLacres_post = $_POST['posto_lacres'] ?? array();
$regionaisLacres_post = $_POST['regional_lacres'] ?? array();
$lacresIIPR_post = $_POST['lacre_iipr'] ?? array();
$lacresCorreios_post = $_POST['lacre_correios'] ?? array();
$etiquetasCorreios_post = $_POST['etiqueta_correios'] ?? array();

if (!empty($postosLacres_post) && !empty($regionaisLacres_post)) {
    foreach ($postosLacres_post as $idx => $postoRaw) {
        $regional = isset($regionaisLacres_post[$idx]) ? trim((string)$regionaisLacres_post[$idx]) : '';
        if ($regional === '' || $regional === '0') continue;
        
        $lacreI = isset($lacresIIPR_post[$idx]) ? trim((string)$lacresIIPR_post[$idx]) : '';
        $lacreC = isset($lacresCorreios_post[$idx]) ? trim((string)$lacresCorreios_post[$idx]) : '';
        $eti = isset($etiquetasCorreios_post[$idx]) ? trim((string)$etiquetasCorreios_post[$idx]) : '';
        
        // Só sobrescreve se houver valor preenchido
        if ($lacreI !== '' || $lacreC !== '' || $eti !== '') {
            if (!isset($mapaLacresPorRegional[$regional])) {
                $mapaLacresPorRegional[$regional] = array(
                    'lacre_iipr'        => 0,
                    'lacre_correios'    => 0,
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
}

echo "Mapa Regional Resultante:\n";
echo json_encode($mapaLacresPorRegional, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// Teste de prioridade
echo "=== Teste de Prioridade ===\n\n";

$mapaLacresPorPosto = array(
    '050' => array('lacre_iipr' => 999, 'lacre_correios' => 888, 'etiqueta_correios' => 'ESPECIAL')
);

// Simular lotes de diferentes postos/regionais
$lotes_teste = array(
    array('posto' => '041', 'regional' => '950', 'esperado' => 'Do mapa regional (111, 333, ETQ041)'),
    array('posto' => '042', 'regional' => '950', 'esperado' => 'Do mapa regional, mas POST foi sobrescrito (222, 444, ETQ042)'),
    array('posto' => '050', 'regional' => '950', 'esperado' => 'Do mapa por posto (999, 888, ESPECIAL)'),
);

foreach ($lotes_teste as $lote) {
    $posto = $lote['posto'];
    $regional = $lote['regional'];
    
    $lacreIIPR = 0;
    $lacreCorreios = 0;
    $etiqueta = null;
    
    if (isset($mapaLacresPorPosto[$posto])) {
        $lacreIIPR = $mapaLacresPorPosto[$posto]['lacre_iipr'];
        $lacreCorreios = $mapaLacresPorPosto[$posto]['lacre_correios'];
        $etiqueta = $mapaLacresPorPosto[$posto]['etiqueta_correios'];
    } elseif ($regional !== '' && isset($mapaLacresPorRegional[$regional])) {
        $lacreIIPR = $mapaLacresPorRegional[$regional]['lacre_iipr'];
        $lacreCorreios = $mapaLacresPorRegional[$regional]['lacre_correios'];
        $etiqueta = $mapaLacresPorRegional[$regional]['etiqueta_correios'];
    }
    
    echo "Posto $posto (Regional $regional):\n";
    echo "  IIPR: $lacreIIPR, Correios: $lacreCorreios, Etiqueta: " . ($etiqueta ?? 'NULL') . "\n";
    echo "  Esperado: " . $lote['esperado'] . "\n\n";
}

echo "=== Teste concluído ===\n";
?>
