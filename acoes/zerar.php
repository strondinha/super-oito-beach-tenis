<?php
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');
$arquivos = [
    __DIR__ . '/../dados/participantes.json',
    __DIR__ . '/../dados/rodadas.json',
    __DIR__ . '/../dados/classificacoes.json',
];
foreach ($arquivos as $f) {
    if (file_exists($f)) {
        unlink($f);
    }
}
echo json_encode(['status' => 'ok']);
