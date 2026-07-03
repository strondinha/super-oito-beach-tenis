<?php
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');
require_once '../nucleo/bd.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$dados = ler_json('../dados/rodadas.json');
if (empty($dados['grupos'])) {
    echo json_encode(['status' => 'erro', 'msg' => 'Nenhum grupo encontrado.']);
    exit;
}

$rodadaNumero = (int)($_POST['rodada_numero'] ?? 0);
$placares     = $_POST['placar'] ?? [];
$gps          = (int)($dados['config']['games_por_set'] ?? 4);

if ($rodadaNumero < 1) {
    echo json_encode(['status' => 'erro', 'msg' => 'Número de rodada inválido.']);
    exit;
}

foreach ($dados['grupos'] as $gi => &$grupo) {
    $rodadaIndex = null;
    foreach ($grupo['rodadas'] as $ri => $r) {
        if ($r['numero'] === $rodadaNumero) { $rodadaIndex = $ri; break; }
    }

    if ($rodadaIndex === null) {
        echo json_encode(['status' => 'erro', 'msg' => "Rodada {$rodadaNumero} não encontrada no Grupo " . ($gi + 1) . '.']);
        exit;
    }

    $numPartidas   = count($grupo['rodadas'][$rodadaIndex]['partidas']);
    $grupoPlacares = $placares[$gi] ?? [];

    for ($pi = 0; $pi < $numPartidas; $pi++) {
        $raw1 = trim($grupoPlacares[$pi]['j1'] ?? '');
        $raw2 = trim($grupoPlacares[$pi]['j2'] ?? '');
        $quad = 'Quadra ' . ($pi + 1);
        $grp  = 'Grupo '  . ($gi + 1);

        if ($raw1 === '' || $raw2 === '') {
            echo json_encode(['status' => 'erro', 'msg' => "Preencha o placar da {$quad} do {$grp}."]);
            exit;
        }

        if (filter_var($raw1, FILTER_VALIDATE_INT) === false ||
            filter_var($raw2, FILTER_VALIDATE_INT) === false) {
            echo json_encode(['status' => 'erro', 'msg' => "Placar inválido na {$quad} do {$grp}."]);
            exit;
        }

        $p1 = (int)$raw1;
        $p2 = (int)$raw2;

        if ($p1 < 0 || $p2 < 0) {
            echo json_encode(['status' => 'erro', 'msg' => "Placar negativo na {$quad} do {$grp}."]);
            exit;
        }

        if ($p1 === $p2) {
            echo json_encode(['status' => 'erro', 'msg' => "Empate não é permitido na {$quad} do {$grp}. Um dos atletas deve ter mais sets."]);
            exit;
        }

        // Vencedor tem que ter pelo menos games_por_set sets
        $vencedor_pts = max($p1, $p2);
        if ($vencedor_pts < $gps) {
            echo json_encode(['status' => 'erro', 'msg' => "Na {$quad} do {$grp}: o vencedor deve ter pelo menos {$gps} games."]);
            exit;
        }

        // Determina vencedor
        $vencedor = $p1 > $p2 ? 'j1' : 'j2';

        $dados['grupos'][$gi]['rodadas'][$rodadaIndex]['partidas'][$pi]['placar_1']       = $p1;
        $dados['grupos'][$gi]['rodadas'][$rodadaIndex]['partidas'][$pi]['placar_2']       = $p2;
        $dados['grupos'][$gi]['rodadas'][$rodadaIndex]['partidas'][$pi]['vencedor']       = $vencedor;
        $dados['grupos'][$gi]['rodadas'][$rodadaIndex]['partidas'][$pi]['status_partida'] = 'concluida';
    }

    $dados['grupos'][$gi]['rodadas'][$rodadaIndex]['status'] = 'concluida';
}
unset($grupo);

gravar_json('../dados/rodadas.json', $dados);

$totalRodadas  = count($dados['grupos'][0]['rodadas']);
$proximaRodada = $rodadaNumero + 1;

if ($proximaRodada <= $totalRodadas) {
    echo json_encode(['status' => 'ok', 'redirect' => 'rodadas.php?rodada=' . $proximaRodada]);
} else {
    echo json_encode(['status' => 'ok', 'redirect' => '../paginas/classificacao.php']);
}
