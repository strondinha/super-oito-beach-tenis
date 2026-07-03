<?php
require_once '../nucleo/bd.php';
require_once '../nucleo/pontuacao.php';

$basePath  = '../';
$activeNav = 'classificacao';

$dados        = ler_json('../dados/rodadas.json');
$participantes = ler_json('../dados/participantes.json');
$config        = $dados['config'] ?? [];
$modoTorneio   = $config['modo_torneio'] ?? 'individual';
$isDuplas      = $modoTorneio === 'duplas';

$participantesPorId = [];
foreach ($participantes as $p) {
    $participantesPorId[$p['id']] = $p;
}

$classificacoesPorGrupo = [];
$todosConcluidos        = !empty($dados['grupos']);
$algumConcluido         = false;

foreach ($dados['grupos'] ?? [] as $grupo) {
    $concluido = grupo_concluido($grupo);
    if (!$concluido) {
        $todosConcluidos = false;
    } else {
        $algumConcluido = true;
    }

    $classificacoesPorGrupo[] = [
        'grupo'     => $grupo,
        'ranking'   => calcular_classificacao_grupo($grupo, $participantesPorId, $config),
        'concluido' => $concluido,
    ];
}

// Gera duplas eliminatórias ao final: 1º Grupo A + 2º Grupo B (rotativo)
$duplasEliminatorias = [];
if ($todosConcluidos && count($classificacoesPorGrupo) > 0) {
    $n = count($classificacoesPorGrupo);
    for ($i = 0; $i < $n; $i++) {
        $rank  = $classificacoesPorGrupo[$i]['ranking'];
        $rankNext = $classificacoesPorGrupo[($i + 1) % $n]['ranking'];
        if (isset($rank[0], $rankNext[1])) {
            $duplasEliminatorias[] = [
                'a1'     => $rank[0],
                'g1'     => $i + 1,
                'pos1'   => '1º',
                'a2'     => $rankNext[1],
                'g2'     => (($i + 1) % $n) + 1,
                'pos2'   => '2º',
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classificação &mdash; LetzPlay Beach Tennis</title>
    <link rel="stylesheet" href="../css/estilo.css">
    <style>@media print { .site-header,.site-footer,.no-print { display:none !important; } }</style>
</head>
<body>
    <?php include '../layout/cabecalho.php'; ?>

    <main class="page-layout">
        <div class="container">
            <div class="page-header">
                <a href="../index.php" class="page-back no-print">Voltar ao início</a>
                <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:14px;">
                    <div>
                        <h1 class="page-title">
                            <?php if ($todosConcluidos && !empty($dados['grupos'])): ?>
                                Classificação Final
                            <?php else: ?>
                                Classificação Parcial
                            <?php endif; ?>
                        </h1>
                        <?php if (!empty($dados['total_grupos'])): ?>
                        <p class="page-subtitle">
                            <?= $dados['total_jogadores'] ?> <?= $isDuplas ? 'duplas' : 'atletas' ?> &bull;
                            <?= $dados['total_grupos'] ?> grupo<?= $dados['total_grupos'] > 1 ? 's' : '' ?> &bull;
                            <?= $todosConcluidos ? ($isDuplas ? 'Fase de grupos concluída' : 'Fase de grupos concluída') : 'Em andamento' ?>
                            <?php if ($isDuplas): ?>&bull; <span class="modo-duplas-badge">👥 Duplas Fixas</span><?php endif; ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    <button onclick="window.print()" class="btn btn-outline btn-sm no-print">🖨 Imprimir</button>
                </div>
            </div>

            <?php if (empty($dados['grupos'])): ?>
                <div class="alert alert-warning">
                    Nenhum grupo gerado.
                    <a href="../paginas/grupos.php" style="color:inherit;font-weight:700;">Gerar grupos &rarr;</a>
                </div>
            <?php else: ?>

            <div class="groups-list">
                <?php foreach ($classificacoesPorGrupo as $cg): ?>
                <div class="group-card">
                    <div class="group-header">
                        <h3>Grupo <?= $cg['grupo']['numero'] ?></h3>
                        <span class="group-badge <?= $cg['concluido'] ? 'done' : '' ?>">
                            <?= $cg['concluido'] ? '&#10003; Concluído' : 'Em andamento' ?>
                        </span>
                    </div>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Pos</th>
                                    <th><?= $isDuplas ? 'Dupla' : 'Atleta' ?></th>
                                    <th title="Games ganhos">G</th>
                                    <th title="Jogos disputados">J</th>
                                    <th title="Vitórias">V</th>
                                    <th title="Derrotas">D</th>
                                    <th title="Games a favor">GF</th>
                                    <th title="Games contra">GC</th>
                                    <th title="Saldo de games">SG</th>
                                    <th title="Pontos marcados (0-15-30-40)">PT</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cg['ranking'] as $pos => $r): ?>
                                <tr class="<?= $pos < 2 ? 'qualifies' : ($cg['concluido'] ? 'eliminated' : '') ?>">
                                    <td class="rank-pos"><?= $pos + 1 ?>º</td>
                                    <td class="rank-name"><?= htmlspecialchars($r['nome'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="rank-pts"><?= $r['pontos'] ?></td>
                                    <td><?= $r['jogos'] ?></td>
                                    <td><?= $r['vitorias'] ?></td>
                                    <td><?= $r['derrotas'] ?></td>
                                    <td><?= $r['games_pro'] ?></td>
                                    <td><?= $r['games_contra'] ?></td>
                                    <td><strong><?= ($r['games_pro'] - $r['games_contra'] >= 0 ? '+' : '') . ($r['games_pro'] - $r['games_contra']) ?></strong></td>
                                    <td style="font-size:.82rem;color:var(--muted);"><?= $r['pts_pro'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if ($cg['concluido'] && count($cg['ranking']) >= 2): ?>
                    <div style="padding:12px 22px;background:var(--success-light);border-top:1px solid rgba(16,185,129,.18);">
                        <span style="font-size:.85rem;color:#065f46;font-weight:600;">
                            &#10003; <?= $isDuplas ? 'Duplas classificadas' : 'Classificados' ?>:
                            <?= htmlspecialchars($cg['ranking'][0]['nome'], ENT_QUOTES, 'UTF-8') ?>
                            &amp; <?= htmlspecialchars($cg['ranking'][1]['nome'], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>

                <!-- CAMPEÃO: grupo único concluído -->
                <?php if ($todosConcluidos && count($classificacoesPorGrupo) === 1 && !empty($classificacoesPorGrupo[0]['ranking'])): ?>
                <div class="card" style="border:2px solid var(--primary);">
                    <div class="card-header" style="background:var(--primary-soft);">
                        <h3 style="color:var(--primary-dark);">🏆 <?= $isDuplas ? 'Dupla Campeã do Torneio' : 'Campeão do Torneio' ?></h3>
                    </div>
                    <div class="card-body">
                        <?php $podio = $classificacoesPorGrupo[0]['ranking']; ?>
                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;">
                        <?php foreach (array_slice($podio, 0, min(3, count($podio))) as $pos => $r):
                            $pInfo = $participantesPorId[$r['id']] ?? [];
                            $isDuplaEntry = $isDuplas && ($pInfo['modo_dupla'] ?? false);
                        ?>
                            <div style="text-align:center;padding:20px 12px;background:#fff;border:1px solid var(--border);border-radius:var(--r-lg);">
                                <div style="font-size:2rem;margin-bottom:6px;"><?= ['🥇','🥈','🥉'][$pos] ?></div>
                                <?php if ($isDuplaEntry): ?>
                                <div style="font-size:1rem;font-weight:800;color:var(--text);"><?= htmlspecialchars($pInfo['dupla_jogador1'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                                <div style="font-size:.9rem;font-weight:700;color:var(--primary-dark);margin-top:2px;">&amp; <?= htmlspecialchars($pInfo['dupla_jogador2'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                                <?php else: ?>
                                <div style="font-size:1.05rem;font-weight:800;color:var(--text);"><?= htmlspecialchars($r['nome'], ENT_QUOTES, 'UTF-8') ?></div>
                                <?php endif; ?>
                                <div style="font-size:.8rem;color:var(--muted);margin-top:4px;"><?= $r['vitorias'] ?>V / <?= $r['games_pro'] ?>G</div>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- FASE ELIMINATÓRIA -->
                <?php if ($todosConcluidos && !empty($duplasEliminatorias) && count($classificacoesPorGrupo) > 1): ?>
                <div class="card">
                    <div class="card-header">
                        <h3>🏆 Fase Eliminatória &mdash; <?= $isDuplas ? 'Confrontos entre Duplas Fixas' : 'Duplas Geradas pelo Sistema' ?></h3>
                        <span class="chip chip-gold">Geração Automática</span>
                    </div>
                    <div class="card-body">
                        <p style="margin-bottom:20px;font-size:.9rem;line-height:1.7;">
                            <?php if ($isDuplas): ?>
                            Os confrontos eliminatórios abaixo foram gerados automaticamente pelo sistema
                            seguindo o critério <strong>1ª dupla de um grupo &times; 2ª dupla do grupo seguinte</strong>,
                            garantindo que duplas de grupos diferentes se enfrentem.
                            <?php else: ?>
                            As duplas abaixo foram formadas automaticamente pelo sistema seguindo o critério
                            <strong>1º de um grupo + 2º do grupo seguinte</strong>, garantindo que atletas
                            de grupos diferentes formem parceria.
                            <?php endif; ?>
                        </p>
                        <div class="bracket-grid">
                            <?php foreach ($duplasEliminatorias as $di => $dupla):
                                $pInfo1 = $participantesPorId[$dupla['a1']['id']] ?? [];
                                $pInfo2 = $participantesPorId[$dupla['a2']['id']] ?? [];
                            ?>
                            <div class="bracket-card">
                                <div class="bracket-card-title"><?= $isDuplas ? 'Confronto' : 'Dupla' ?> <?= $di + 1 ?></div>
                                <div class="bracket-player">
                                    <span style="font-size:1.1rem;">🎾</span>
                                    <div>
                                        <div class="bracket-player-name"><?= htmlspecialchars($dupla['a1']['nome'], ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="bracket-player-info"><?= $dupla['pos1'] ?> do Grupo <?= $dupla['g1'] ?></div>
                                        <?php if ($isDuplas && ($pInfo1['modo_dupla'] ?? false)): ?>
                                        <div class="bracket-dupla-players"><?= htmlspecialchars($pInfo1['dupla_jogador1'] ?? '', ENT_QUOTES, 'UTF-8') ?> &amp; <?= htmlspecialchars($pInfo1['dupla_jogador2'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="bracket-player">
                                    <span style="font-size:1.1rem;">🎾</span>
                                    <div>
                                        <div class="bracket-player-name"><?= htmlspecialchars($dupla['a2']['nome'], ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="bracket-player-info"><?= $dupla['pos2'] ?> do Grupo <?= $dupla['g2'] ?></div>
                                        <?php if ($isDuplas && ($pInfo2['modo_dupla'] ?? false)): ?>
                                        <div class="bracket-dupla-players"><?= htmlspecialchars($pInfo2['dupla_jogador1'] ?? '', ENT_QUOTES, 'UTF-8') ?> &amp; <?= htmlspecialchars($pInfo2['dupla_jogador2'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="action-bar no-print">
                <?php if (!$todosConcluidos): ?>
                    <a href="../paginas/rodadas.php" class="btn btn-primary">Continuar Rodadas &rarr;</a>
                <?php else: ?>
                    <span style="font-size:.875rem;color:var(--text-muted);">Fase de grupos encerrada.</span>
                    <a href="../index.php" class="btn btn-outline">Voltar ao Início</a>
                <?php endif; ?>
            </div>

            <?php endif; ?>
        </div>
    </main>

    <?php include '../layout/rodape.php'; ?>
    <script>window.BASEPATH = '../';</script>
    <script src="../js/app.js"></script>
</body>
</html>
