<?php
// Test script para validar a construção de $mapaLacresPorPosto
function build_mapa_from_aligned($postos, $lacresI, $lacresC, $etiquetas) {
    $map = array();
    foreach ($postos as $idx => $postoRaw) {
        $postoCodigo = str_pad(preg_replace('/\D+/', '', (string)$postoRaw), 3, '0', STR_PAD_LEFT);
        if ($postoCodigo === '') continue;
        $lacreI = isset($lacresI[$idx]) ? trim((string)$lacresI[$idx]) : '';
        $lacreC = isset($lacresC[$idx]) ? trim((string)$lacresC[$idx]) : '';
        $eti = isset($etiquetas[$idx]) ? trim((string)$etiquetas[$idx]) : '';
        $map[$postoCodigo] = array(
            'lacre_iipr' => ($lacreI === '' ? 0 : (int)$lacreI),
            'lacre_correios' => ($lacreC === '' ? 0 : (int)$lacreC),
            'etiqueta_correios' => ($eti === '' ? null : $eti),
        );
    }
    return $map;
}

function build_mapa_from_assoc($lacres_iipr, $lacres_correios, $etiquetas) {
    $map = array();
    $todos = array_unique(array_merge(array_keys($lacres_iipr), array_keys($lacres_correios), array_keys($etiquetas)));
    foreach ($todos as $postoCodigo) {
        $postoCodigo = (string)$postoCodigo;
        $lacreI = isset($lacres_iipr[$postoCodigo]) ? trim((string)$lacres_iipr[$postoCodigo]) : '';
        $lacreC = isset($lacres_correios[$postoCodigo]) ? trim((string)$lacres_correios[$postoCodigo]) : '';
        $eti = isset($etiquetas[$postoCodigo]) ? trim((string)$etiquetas[$postoCodigo]) : '';
        $map[$postoCodigo] = array(
            'lacre_iipr' => ($lacreI === '' ? 0 : (int)$lacreI),
            'lacre_correios' => ($lacreC === '' ? 0 : (int)$lacreC),
            'etiqueta_correios' => $eti !== '' ? $eti : null,
        );
    }
    return $map;
}

// Cenário 1: arrays alinhados enviados pelo JS
$postos = array('41', '042', '050');
$lacre_iipr_arr = array('111', '', '333');
$lacre_correios_arr = array('444', '555', '');
etiqueta_arr = array('ETQ041','', 'ETQ050');

$map1 = build_mapa_from_aligned($postos, $lacre_iipr_arr, $lacre_correios_arr, $etiqueta_arr);

// Cenário 2: arrays associativos já normalizados
$lacres_iipr_assoc = array('041' => '111', '042' => '', '050' => '333');
$lacres_correios_assoc = array('041' => '444', '042' => '555', '050' => '');
etiquetas_assoc = array('041' => 'ETQ041', '050' => 'ETQ050');

$map2 = build_mapa_from_assoc($lacres_iipr_assoc, $lacres_correios_assoc, $etiquetas_assoc);

echo "--- Mapa (aligned arrays) ---\n";
echo json_encode($map1, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

echo "--- Mapa (assoc arrays) ---\n";
echo json_encode($map2, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

?>