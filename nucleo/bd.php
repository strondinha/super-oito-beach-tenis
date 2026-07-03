<?php
function ler_json($caminho) {
    if (!file_exists($caminho)) return [];
    $dados = json_decode(file_get_contents($caminho), true);
    return is_array($dados) ? $dados : [];
}

function gravar_json($caminho, $dados) {
    file_put_contents($caminho, json_encode($dados, JSON_PRETTY_PRINT));
}