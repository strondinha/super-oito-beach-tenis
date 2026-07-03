<?php
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');
require_once '../nucleo/bd.php';
require_once '../nucleo/sorteio.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$participantes = ler_json('../dados/participantes.json');
$total         = count($participantes);
$totaisValidos = [8, 16, 24, 32];

if (!in_array($total, $totaisValidos, true)) {
    echo json_encode([
        'status' => 'erro',
        'msg'    => "Número inválido de atletas ({$total}). Necessário: 8, 16, 24 ou 32.",
    ]);
    exit;
}

// Lê e valida configuração
$criterios_validos = ['vitorias', 'derrotas', 'saldo_games', 'confronto_direto'];
$gps = (int)($_POST['games_por_set'] ?? 4);
if (!in_array($gps, [4, 6], true)) $gps = 4;

$crit1 = $_POST['criterio_1'] ?? 'vitorias';
$crit2 = $_POST['criterio_2'] ?? 'saldo_games';
if (!in_array($crit1, $criterios_validos, true)) $crit1 = 'vitorias';
if (!in_array($crit2, $criterios_validos, true)) $crit2 = 'saldo_games';
if ($crit1 === $crit2) $crit2 = 'saldo_games';

$config = ['games_por_set' => $gps, 'criterio_1' => $crit1, 'criterio_2' => $crit2];

// Detecta modo duplas fixas a partir dos participantes
$modoTorneio = (!empty($participantes) && ($participantes[0]['modo_dupla'] ?? false)) ? 'duplas' : 'individual';
$config['modo_torneio'] = $modoTorneio;

$grupos = gerar_grupos($participantes);

// Inicializa cada partida com os novos campos de pontuação ao vivo
foreach ($grupos as &$grupo) {
    foreach ($grupo['rodadas'] as &$rodada) {
        foreach ($rodada['partidas'] as &$partida) {
            $partida = array_merge($partida, [
                'placar_1'        => 0,
                'placar_2'        => 0,
                'pontos_j1'       => 0,
                'pontos_j2'       => 0,
                'estado_jogo'     => 'normal',
                'estado_set'      => 'normal',
                'mini_tb_j1'      => 0,
                'mini_tb_j2'      => 0,
                'total_pontos_j1' => 0,
                'total_pontos_j2' => 0,
                'status_partida'  => 'pendente',
                'vencedor'        => null,
            ]);
        }
        unset($partida);
    }
    unset($rodada);
}
unset($grupo);

$dados = [
    'formato'         => 'grupos',
    'total_jogadores' => $total,
    'total_grupos'    => count($grupos),
    'config'          => $config,
    'grupos'          => $grupos,
];

gravar_json('../dados/rodadas.json', $dados);
gravar_json('../dados/classificacoes.json', []);

echo json_encode(['status' => 'ok', 'redirect' => '../paginas/rodadas.php']);
