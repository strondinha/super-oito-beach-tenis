<?php
require_once '../nucleo/bd.php';
$basePath  = '../';
$activeNav = 'grupos';

$participantes = ler_json('../dados/participantes.json');
$total         = count($participantes);
$numGrupos     = ($total > 0 && $total % 8 === 0) ? intdiv($total, 8) : 0;

$dadosRodadas  = ler_json('../dados/rodadas.json');
$temRodadas    = !empty($dadosRodadas);
$totaisValidos = [8, 16, 24, 32];

// Detecta modo duplas
$modoTorneio = ($dadosRodadas['config']['modo_torneio'] ?? null)
    ?? ((!empty($participantes) && ($participantes[0]['modo_dupla'] ?? false)) ? 'duplas' : 'individual');
$isDuplas = $modoTorneio === 'duplas';
$unidade  = $isDuplas ? 'dupla' : 'atleta';
$unidades = $isDuplas ? 'duplas' : 'atletas';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sortear Grupos &mdash; LetzPlay Beach Tennis</title>
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body>
    <?php include '../layout/cabecalho.php'; ?>

    <main class="page-layout">
        <div class="container">
            <div class="page-header">
                <a href="../index.php" class="page-back">Voltar ao início</a>
                <h1 class="page-title">Sortear Grupos</h1>
                <p class="page-subtitle">Gere os grupos por sorteio e crie a tabela de confrontos do torneio.
                    <?php if ($isDuplas): ?>
                    <span class="modo-duplas-badge" style="margin-left:8px;">👥 Duplas Fixas</span>
                    <?php endif; ?>
                </p>
            </div>

            <?php if ($total === 0): ?>
                <div class="alert alert-warning">
                    Nenhum <?= $unidade ?> cadastrado.
                    <a href="../paginas/cadastro.php" style="color:inherit;font-weight:700;">Cadastrar <?= $unidades ?> &rarr;</a>
                </div>

            <?php elseif (!in_array($total, $totaisValidos, true)): ?>
                <div class="alert alert-danger">
                    Número inválido de <?= $unidades ?> (<strong><?= $total ?></strong>).
                    O torneio requer exatamente <strong>8, 16, 24 ou 32</strong> <?= $unidades ?>.
                    <br><a href="../paginas/cadastro.php" style="color:inherit;font-weight:700;">Recadastrar <?= $unidades ?> &rarr;</a>
                </div>

            <?php else: ?>

                <?php if ($temRodadas): ?>
                <div class="alert alert-warning" style="margin-bottom:24px;">
                    <strong>Atenção:</strong> Já existe uma tabela gerada.
                    Sortear novamente <strong>apagará todos os resultados</strong> registrados.
                </div>
                <?php endif; ?>

                <div class="card" style="margin-bottom:24px;">
                    <div class="card-header">
                        <h3>Resumo do Torneio</h3>
                        <span class="chip chip-primary"><?= $total ?> <?= $unidades ?> cadastrad<?= $isDuplas ? 'as' : 'os' ?></span>
                    </div>
                    <div class="card-body">
                        <div class="stats-grid" style="margin-bottom:24px;">
                            <div class="stat-box">
                                <div class="stat-number"><?= $total ?></div>
                                <div class="stat-label"><?= ucfirst($unidades) ?></div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-number"><?= $numGrupos ?></div>
                                <div class="stat-label">Grupos</div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-number">7</div>
                                <div class="stat-label">Rodadas</div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-number"><?= $numGrupos * 2 ?></div>
                                <div class="stat-label">Classificados</div>
                            </div>
                        </div>

                        <p style="font-size:.9rem;color:var(--text-muted);line-height:1.75;margin-bottom:28px;">
                            <?php if ($isDuplas): ?>
                            As <strong><?= $total ?></strong> duplas serão divididas em
                            <strong><?= $numGrupos ?></strong> grupo<?= $numGrupos > 1 ? 's' : '' ?> de 8 por sorteio.
                            Cada dupla disputará <strong>7 partidas</strong>
                            contra todas as adversárias do seu grupo.
                            <?= $numGrupos > 1 ? 'As <strong>2 melhores duplas de cada grupo</strong> avançam para a fase eliminatória.' : 'A <strong>1ª dupla colocada</strong> é a dupla campeã.' ?>
                            <?php else: ?>
                            Os <strong><?= $total ?></strong> atletas serão divididos em
                            <strong><?= $numGrupos ?></strong> grupo<?= $numGrupos > 1 ? 's' : '' ?> de 8 por sorteio.
                            Cada atleta disputará <strong>7 partidas individuais</strong>
                            contra todos os adversários do seu grupo.
                            <?= $numGrupos > 1 ? 'Os <strong>2 melhores de cada grupo</strong> avançam para a fase eliminatória.' : 'O <strong>1º colocado</strong> da tabela é o campeão.' ?>
                            <?php endif; ?>
                        </p>

                        <?php $cfgAtual = ler_json('../dados/rodadas.json')['config'] ?? []; ?>

                        <form onsubmit="enviarFormulario(event, '../acoes/sortear_grupos.php')">

                            <!-- GAMES POR SET -->
                            <div style="margin-bottom:22px;">
                                <p style="font-size:.82rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text);margin-bottom:10px;">
                                    Games para fechar o set
                                </p>
                                <div class="count-selector" style="grid-template-columns:repeat(2,1fr);max-width:280px;">
                                    <?php foreach ([4,6] as $gps): ?>
                                    <label class="count-option">
                                        <input type="radio" name="games_por_set" value="<?= $gps ?>"
                                               <?= ($cfgAtual['games_por_set'] ?? 4) == $gps ? 'checked' : '' ?>>
                                        <span class="count-option-label">
                                            <span class="count-number"><?= $gps ?></span>
                                            <span class="count-label">games</span>
                                        </span>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                                <p style="font-size:.78rem;color:var(--muted);margin-top:7px;">
                                    Empate em <?= '3×3 ou 5×5' ?> → vai a 2; se empatar novamente → mini-tiebreak a 7 pontos.
                                </p>
                            </div>

                            <!-- CRITÉRIOS DE DESEMPATE -->
                            <?php
                            $opcoesDesempate = [
                                'vitorias'        => 'Maior nº de vitórias',
                                'derrotas'        => 'Menor nº de derrotas',
                                'saldo_games'     => 'Saldo de games',
                                'confronto_direto'=> 'Confronto direto',
                            ];
                            ?>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:24px;">
                                <div>
                                    <label style="display:block;font-size:.82rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text);margin-bottom:6px;">
                                        1º critério de desempate
                                    </label>
                                    <select name="criterio_1" id="criterio1" onchange="syncCriterios()" class="form-select">
                                        <?php foreach ($opcoesDesempate as $val => $label): ?>
                                        <option value="<?= $val ?>" <?= ($cfgAtual['criterio_1'] ?? 'vitorias') === $val ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label style="display:block;font-size:.82rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text);margin-bottom:6px;">
                                        2º critério de desempate
                                    </label>
                                    <select name="criterio_2" id="criterio2" onchange="syncCriterios()" class="form-select">
                                        <?php foreach ($opcoesDesempate as $val => $label): ?>
                                        <option value="<?= $val ?>" <?= ($cfgAtual['criterio_2'] ?? 'saldo_games') === $val ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block" style="font-size:1.05rem;padding:16px;">
                                🎲 Sortear Grupos e Gerar Tabela de Confrontos
                            </button>
                        </form>

                        <script>
                        function syncCriterios() {
                            const s1 = document.getElementById('criterio1');
                            const s2 = document.getElementById('criterio2');
                            Array.from(s2.options).forEach(o => o.disabled = (o.value === s1.value));
                            if (s1.value === s2.value) {
                                const alt = Array.from(s2.options).find(o => !o.disabled);
                                if (alt) s2.value = alt.value;
                            }
                        }
                        syncCriterios();
                        </script>
                    </div>
                </div>

                <!-- Lista de atletas cadastrados -->
                <div class="card">
                    <div class="card-header">
                        <h3><?= $isDuplas ? 'Duplas Cadastradas' : 'Atletas Cadastrados' ?></h3>
                    </div>
                    <div class="card-body" style="padding-top:12px;">
                        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:8px;">
                            <?php foreach ($participantes as $p): ?>
                            <div style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:var(--surface-2);border:1px solid var(--border);border-radius:var(--r-sm);">
                                <span style="font-size:.8rem;font-weight:700;color:var(--text-muted);min-width:22px;"><?= $p['id'] ?></span>
                                <div>
                                    <?php if ($isDuplas && ($p['modo_dupla'] ?? false)): ?>
                                    <div style="font-weight:600;font-size:.9rem;color:var(--text);"><?= htmlspecialchars($p['dupla_jogador1'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="dupla-players-display">&amp; <?= htmlspecialchars($p['dupla_jogador2'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                                    <?php else: ?>
                                    <div style="font-weight:600;font-size:.9rem;color:var(--text);"><?= htmlspecialchars($p['nome'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <?php if ($p['apelido']): ?>
                                    <div style="font-size:.78rem;color:var(--text-muted);"><?= htmlspecialchars($p['apelido'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

            <?php endif; ?>
        </div>
    </main>

    <?php include '../layout/rodape.php'; ?>
    <script>window.BASEPATH = '../';</script>
    <script src="../js/app.js"></script>
</body>
</html>
