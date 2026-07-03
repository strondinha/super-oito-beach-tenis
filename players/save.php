<?php
require_once '../lib/storage.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$nomes   = $_POST['nome']   ?? [];
$apelidos = $_POST['apelido'] ?? [];
$total   = (int)($_POST['total'] ?? 0);

$totaisValidos = [8, 16, 24, 32];
if (!in_array($total, $totaisValidos, true)) {
    echo json_encode(['status' => 'erro', 'msg' => 'Quantidade inválida. Escolha 8, 16, 24 ou 32 atletas.']);
    exit;
}

if (count($nomes) !== $total) {
    echo json_encode(['status' => 'erro', 'msg' => "Preencha todos os {$total} atletas."]);
    exit;
}

$participantes    = [];
$nomesCadastrados = [];

for ($i = 0; $i < $total; $i++) {
    $nome    = trim($nomes[$i]    ?? '');
    $apelido = trim($apelidos[$i] ?? '');
    $nome    = preg_replace('/\s+/', ' ', $nome);

    if ($nome === '') {
        echo json_encode(['status' => 'erro', 'msg' => 'Preencha o nome de todos os atletas.']);
        exit;
    }

    $chave = function_exists('mb_strtolower')
        ? mb_strtolower($nome, 'UTF-8')
        : strtolower($nome);

    if (isset($nomesCadastrados[$chave])) {
        echo json_encode(['status' => 'erro', 'msg' => "Nome duplicado: \"{$nome}\". Todos os nomes devem ser únicos."]);
        exit;
    }

    $nomesCadastrados[$chave] = true;
    $participantes[] = [
        'id'      => $i + 1,
        'nome'    => $nome,
        'apelido' => $apelido,
    ];
}

gravar_json('../data/participantes.json', $participantes);
// Limpa rodadas ao recadastrar atletas
$arquivosLimpar = ['../data/rodadas.json', '../data/classificacoes.json'];
foreach ($arquivosLimpar as $f) {
    if (file_exists($f)) unlink($f);
}

echo json_encode(['status' => 'ok', 'redirect' => '../setup/setup.php']);
