<?php
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');
require_once '../nucleo/bd.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$nomes   = $_POST['nome']   ?? [];
$apelidos = $_POST['apelido'] ?? [];
$total   = (int)($_POST['total'] ?? 0);
$modo    = $_POST['modo_torneio'] ?? 'individual';
$isDuplas = $modo === 'duplas';

$totaisValidos = [8, 16, 24, 32];
if (!in_array($total, $totaisValidos, true)) {
    echo json_encode(['status' => 'erro', 'msg' => 'Quantidade inválida. Escolha 8, 16, 24 ou 32 ' . ($isDuplas ? 'duplas' : 'atletas') . '.']);
    exit;
}

$participantes    = [];
$nomesCadastrados = [];

if ($isDuplas) {
    // ── Modo Duplas Fixas ──────────────────────────────────────────
    $duplas_j1 = $_POST['dupla_j1'] ?? [];
    $duplas_j2 = $_POST['dupla_j2'] ?? [];

    if (count($duplas_j1) !== $total || count($duplas_j2) !== $total) {
        echo json_encode(['status' => 'erro', 'msg' => "Preencha todos os jogadores das {$total} duplas."]);
        exit;
    }

    for ($i = 0; $i < $total; $i++) {
        $j1 = trim(preg_replace('/\s+/', ' ', $duplas_j1[$i] ?? ''));
        $j2 = trim(preg_replace('/\s+/', ' ', $duplas_j2[$i] ?? ''));

        if ($j1 === '' || $j2 === '') {
            echo json_encode(['status' => 'erro', 'msg' => 'Preencha os dois jogadores de cada dupla (dupla ' . ($i + 1) . ').']);
            exit;
        }
        if (mb_strtolower($j1, 'UTF-8') === mb_strtolower($j2, 'UTF-8')) {
            echo json_encode(['status' => 'erro', 'msg' => 'Os dois jogadores de uma dupla não podem ter o mesmo nome (dupla ' . ($i + 1) . ').']);
            exit;
        }

        $chave = mb_strtolower($j1 . '|' . $j2, 'UTF-8');
        if (isset($nomesCadastrados[$chave])) {
            echo json_encode(['status' => 'erro', 'msg' => "Dupla duplicada: \"{$j1} / {$j2}\". Todas as duplas devem ser únicas."]);
            exit;
        }
        $nomesCadastrados[$chave] = true;

        $participantes[] = [
            'id'             => $i + 1,
            'nome'           => $j1 . ' / ' . $j2,
            'apelido'        => '',
            'modo_dupla'     => true,
            'dupla_jogador1' => $j1,
            'dupla_jogador2' => $j2,
        ];
    }
} else {
    // ── Modo Individual (existente) ────────────────────────────────
    if (count($nomes) !== $total) {
        echo json_encode(['status' => 'erro', 'msg' => "Preencha todos os {$total} atletas."]);
        exit;
    }

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
}

gravar_json('../dados/participantes.json', $participantes);
// Limpa rodadas ao recadastrar atletas
$arquivosLimpar = ['../dados/rodadas.json', '../dados/classificacoes.json'];
foreach ($arquivosLimpar as $f) {
    if (file_exists($f)) unlink($f);
}

echo json_encode(['status' => 'ok', 'redirect' => '../paginas/grupos.php']);
