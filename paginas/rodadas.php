<?php
require_once '../nucleo/bd.php';
require_once '../nucleo/pontuacao.php';

$dados         = ler_json('../dados/rodadas.json');
$participantes = ler_json('../dados/participantes.json');
$config        = $dados['config'] ?? ['games_por_set' => 4, 'criterio_1' => 'vitorias', 'criterio_2' => 'saldo_games'];
$gps           = (int)($config['games_por_set'] ?? 4);
$modoTorneio   = $config['modo_torneio'] ?? 'individual';
$isDuplas      = $modoTorneio === 'duplas';

$basePath  = '../';
$activeNav = 'rodadas';

// Mapa id → apelido/nome
$nomes = [];
foreach ($participantes as $p) {
    $nomes[$p['id']] = $p['apelido'] ?: $p['nome'];
}

$totalRodadas = 0;
if (!empty($dados['grupos'][0]['rodadas'])) {
    $totalRodadas = count($dados['grupos'][0]['rodadas']);
}

// Rodada atual = primeira com alguma partida pendente
$rodadaAtual        = null;
$todosGruposConcluidos = !empty($dados['grupos']);

foreach ($dados['grupos'] ?? [] as $grupo) {
    foreach ($grupo['rodadas'] as $r) {
        if ($r['status'] !== 'concluida') {
            $todosGruposConcluidos = false;
            if ($rodadaAtual === null || $r['numero'] < $rodadaAtual) {
                $rodadaAtual = $r['numero'];
            }
            break;
        }
    }
}

$rodadaExibida = isset($_GET['rodada']) ? (int)$_GET['rodada'] : $rodadaAtual;
$emEdicao      = ($rodadaExibida !== null && $rodadaExibida !== $rodadaAtual);

$ultimaConcluida = null;
foreach ($dados['grupos'][0]['rodadas'] ?? [] as $r) {
    if ($r['status'] === 'concluida') $ultimaConcluida = $r['numero'];
}

$rodadaDisponivel = false;
if ($rodadaExibida !== null && !empty($dados['grupos'])) {
    foreach ($dados['grupos'][0]['rodadas'] as $r) {
        if ($r['numero'] === $rodadaExibida) { $rodadaDisponivel = true; break; }
    }
}

// Helper: exibe pontos no formato 0/15/30/40
function pts_label(int $p): string {
    return (string)([0, 15, 30, 40][$p] ?? 40);
}

// Helper: placar visual do jogo atual
function jogo_display(array $partida): array {
    $ej  = $partida['estado_jogo'] ?? 'normal';
    $pj1 = (int)($partida['pontos_j1'] ?? 0);
    $pj2 = (int)($partida['pontos_j2'] ?? 0);
    $es  = $partida['estado_set'] ?? 'normal';

    if ($es === 'mini_tiebreak') {
        return ['tipo' => 'mini', 'j1' => (int)($partida['mini_tb_j1'] ?? 0), 'j2' => (int)($partida['mini_tb_j2'] ?? 0)];
    }
    if ($ej === 'deuce')           return ['tipo' => 'deuce'];
    if ($ej === 'vantagem_j1')     return ['tipo' => 'adv', 'quem' => 'j1'];
    if ($ej === 'vantagem_j2')     return ['tipo' => 'adv', 'quem' => 'j2'];
    return ['tipo' => 'normal', 'j1' => pts_label($pj1), 'j2' => pts_label($pj2)];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rodadas &mdash; LetzPlay Beach Tennis</title>
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body>
    <?php include '../layout/cabecalho.php'; ?>

    <main class="page-layout">
        <div class="container">
            <div class="page-header">
                <a href="../index.php" class="page-back">Voltar ao início</a>
                <h1 class="page-title">
                    <?php if ($todosGruposConcluidos && !empty($dados['grupos'])): ?>
                        Fase de Grupos Concluída 🏆
                    <?php elseif ($rodadaExibida): ?>
                        <?= $emEdicao ? 'Rodada ' : 'Rodada ' ?><?= $rodadaExibida ?> de <?= $totalRodadas ?>
                    <?php else: ?>
                        Rodadas
                    <?php endif; ?>
                </h1>
                <?php if (!empty($dados['grupos'])): ?>
                <p class="page-subtitle">
                    <?= $dados['total_jogadores'] ?> <?= $isDuplas ? 'duplas' : 'atletas' ?> &bull;
                    <?= $dados['total_grupos'] ?> grupo<?= $dados['total_grupos'] > 1 ? 's' : '' ?> &bull;
                    Set a <?= $gps ?> games &bull;
                    <?= $totalRodadas ?> rodadas por grupo
                    <?php if ($isDuplas): ?>&bull; <span class="modo-duplas-badge">👥 Duplas Fixas</span><?php endif; ?>
                </p>
                <?php endif; ?>
            </div>

            <?php if (empty($dados['grupos'])): ?>
                <div class="alert alert-warning">
                    Nenhum grupo sorteado.
                    <a href="../paginas/grupos.php" style="color:inherit;font-weight:700;">Sortear grupos &rarr;</a>
                </div>

            <?php elseif ($todosGruposConcluidos): ?>
                <div class="alert alert-success" style="margin-bottom:24px;">
                    Todas as <?= $totalRodadas ?> rodadas foram concluídas!
                </div>
                <div style="display:flex;gap:12px;flex-wrap:wrap;">
                    <a href="../paginas/classificacao.php" class="btn btn-primary">Ver Classificação Final</a>
                    <?php if ($ultimaConcluida): ?>
                        <a href="rodadas.php?rodada=<?= $ultimaConcluida ?>" class="btn btn-outline">Ver Última Rodada</a>
                    <?php endif; ?>
                </div>

            <?php else: ?>

                <!-- Barra de ações -->
                <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:24px;">
                    <?php if ($ultimaConcluida && !$emEdicao): ?>
                        <a href="rodadas.php?rodada=<?= $ultimaConcluida ?>" class="btn btn-outline btn-sm">&#8592; Rodada <?= $ultimaConcluida ?></a>
                    <?php endif; ?>
                    <a href="../paginas/classificacao.php" class="btn btn-outline btn-sm">Classificação parcial</a>
                    <?php if ($emEdicao): ?>
                        <a href="rodadas.php" class="btn btn-outline btn-sm">Rodada atual</a>
                    <?php endif; ?>
                </div>

                <!-- Indicadores de progresso -->
                <div class="rounds-progress">
                    <?php for ($i = 1; $i <= $totalRodadas; $i++):
                        $concluida = true;
                        foreach ($dados['grupos'] as $g) {
                            foreach ($g['rodadas'] as $r) {
                                if ($r['numero'] === $i && $r['status'] !== 'concluida') {
                                    $concluida = false; break;
                                }
                            }
                        }
                        $ativa = ($i === $rodadaExibida);
                    ?>
                    <a href="rodadas.php?rodada=<?= $i ?>"
                       class="round-dot <?= $concluida ? 'done' : ($ativa ? 'current' : '') ?>"
                       title="Rodada <?= $i ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </div>

                <?php if (!$rodadaDisponivel): ?>
                    <div class="alert alert-warning">Rodada <?= $rodadaExibida ?> não encontrada.</div>
                <?php else: ?>

                <form onsubmit="enviarFormulario(event, '../acoes/salvar_resultado.php')">
                    <input type="hidden" name="rodada_numero" value="<?= $rodadaExibida ?>">

                    <div class="groups-list">
                    <?php foreach ($dados['grupos'] as $gi => $grupo):
                        $rodadaGrupo = null;
                        foreach ($grupo['rodadas'] as $r) {
                            if ($r['numero'] === $rodadaExibida) { $rodadaGrupo = $r; break; }
                        }
                        if (!$rodadaGrupo) continue;
                        $rodadaConcluida = ($rodadaGrupo['status'] === 'concluida');
                    ?>
                    <div class="group-card">
                        <div class="group-header">
                            <h3>Grupo <?= $grupo['numero'] ?></h3>
                            <?php if ($rodadaConcluida): ?>
                                <span class="group-badge done">&#10003; Concluída</span>
                            <?php else: ?>
                                <span class="group-badge"><?= count($rodadaGrupo['partidas']) ?> partidas</span>
                            <?php endif; ?>
                        </div>
                        <div class="group-body">
                            <div class="matches-grid">
                            <?php foreach ($rodadaGrupo['partidas'] as $pi => $partida):
                                $nomej1 = htmlspecialchars($nomes[$partida['j1']] ?? "#{$partida['j1']}", ENT_QUOTES, 'UTF-8');
                                $nomej2 = htmlspecialchars($nomes[$partida['j2']] ?? "#{$partida['j2']}", ENT_QUOTES, 'UTF-8');
                                $v1     = ($partida['placar_1'] !== '' && $partida['placar_1'] !== null) ? (int)$partida['placar_1'] : '';
                                $v2     = ($partida['placar_2'] !== '' && $partida['placar_2'] !== null) ? (int)$partida['placar_2'] : '';
                                $done   = (($partida['status_partida'] ?? '') === 'concluida');
                            ?>
                            <div class="match-card">
                                <div class="match-label">Jogo <?= $pi + 1 ?></div>
                                <div class="match-row">
                                    <span class="match-player"><?= $nomej1 ?></span>
                                    <div class="match-score">
                                        <input type="number"
                                            class="score-input"
                                            name="placar[<?= $gi ?>][<?= $pi ?>][j1]"
                                            min="0" max="20" step="1"
                                            value="<?= htmlspecialchars((string)$v1, ENT_QUOTES, 'UTF-8') ?>"
                                            <?= $rodadaConcluida ? 'readonly' : 'required' ?>
                                            placeholder="0">
                                        <span class="score-x">&times;</span>
                                        <input type="number"
                                            class="score-input"
                                            name="placar[<?= $gi ?>][<?= $pi ?>][j2]"
                                            min="0" max="20" step="1"
                                            value="<?= htmlspecialchars((string)$v2, ENT_QUOTES, 'UTF-8') ?>"
                                            <?= $rodadaConcluida ? 'readonly' : 'required' ?>
                                            placeholder="0">
                                    </div>
                                    <span class="match-player right"><?= $nomej2 ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    </div>

                    <?php if ($rodadaGrupo && !$rodadaConcluida): ?>
                    <div style="display:flex;justify-content:flex-end;margin-top:24px;">
                        <button type="submit" class="btn btn-primary" style="padding:14px 36px;font-size:1.02rem;">
                            <?= $emEdicao ? '&#9998; Salvar Correção' : '&#10003; Salvar Rodada e Avançar' ?>
                        </button>
                    </div>
                    <?php endif; ?>
                </form>

                <?php endif; /* rodadaDisponivel */ ?>
            <?php endif; ?>
        </div>
    </main>

    <?php include '../layout/rodape.php'; ?>
    <script>window.BASEPATH = '../';</script>
    <script>window.RODADA_ATUAL = <?= json_encode($rodadaAtual) ?>;</script>
    <script>window.TOTAL_RODADAS = <?= json_encode($totalRodadas) ?>;</script>
    <script src="../js/app.js"></script>
</body>
</html>
