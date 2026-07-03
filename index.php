<?php
$dataDir = __DIR__ . '/dados';
if (!is_dir($dataDir)) mkdir($dataDir, 0777, true);
$basePath  = '';
$activeNav = 'home';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LetzPlay Beach Tennis &mdash; Super Oito</title>
    <meta name="description" content="Sistema profissional de gerenciamento de torneios de beach tennis. Fase de grupos e eliminatórias seguindo as regras ITF e CBT.">
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
    <?php include 'layout/cabecalho.php'; ?>

    <main>
        <!-- HERO -->
        <section class="hero">
            <div class="hero-inner">
                <div class="hero-badge">TORNEIO OFICIAL &bull; ITF &amp; CBT</div>
                <h1 class="hero-title">Super Oito<br><span>Beach Tennis</span></h1>
                <p class="hero-desc">
                    Sistema de gerenciamento profissional para torneios de beach tennis com fase
                    de grupos e eliminatórias. Rápido, organizado e fiel às normas oficiais.
                </p>
                <a href="paginas/cadastro.php" class="btn btn-hero">Iniciar Torneio</a>
            </div>
        </section>

        <!-- PAINEL -->
        <section class="panel-section">
            <div class="container">
                <h2 class="section-title">Painel do Torneio</h2>
                <div class="nav-grid">
                    <a href="paginas/cadastro.php" class="nav-card">
                        <div class="nav-card-icon">👥</div>
                        <div class="nav-card-content">
                            <h3>Cadastrar Atletas</h3>
                            <p>Registre de 8 a 32 participantes para o torneio</p>
                        </div>
                        <span class="nav-card-arrow">→</span>
                    </a>
                    <a href="paginas/grupos.php" class="nav-card">
                        <div class="nav-card-icon">🏆</div>
                        <div class="nav-card-content">
                            <h3>Sortear Grupos</h3>
                            <p>Divida os atletas em grupos e gere a tabela de confrontos</p>
                        </div>
                        <span class="nav-card-arrow">→</span>
                    </a>
                    <a href="paginas/rodadas.php" class="nav-card">
                        <div class="nav-card-icon">🎾</div>
                        <div class="nav-card-content">
                            <h3>Registrar Resultados</h3>
                            <p>Lance os placares das partidas rodada a rodada</p>
                        </div>
                        <span class="nav-card-arrow">→</span>
                    </a>
                    <a href="paginas/classificacao.php" class="nav-card">
                        <div class="nav-card-icon">📊</div>
                        <div class="nav-card-content">
                            <h3>Classificação</h3>
                            <p>Acompanhe a tabela de pontos por grupo e classificados</p>
                        </div>
                        <span class="nav-card-arrow">→</span>
                    </a>
                </div>
                <div class="danger-zone">
                    <button onclick="zerarSistema()" class="btn btn-danger-outline">&#9888; Zerar Torneio</button>
                </div>
            </div>
        </section>

        <!-- REGULAMENTO -->
        <section class="rules-section">
            <div class="container">
                <h2 class="section-title">Regulamento Oficial</h2>
                <div class="rules-grid">
                    <div class="rule-card">
                        <div class="rule-icon">📋</div>
                        <h3>Regras Gerais</h3>
                        <p>Os jogos obedecem às regras vigentes da <strong>ITF</strong> e <strong>CBT</strong>.
                        A rede estará a <strong>1,70m</strong> do solo em todos os confrontos.</p>
                    </div>
                    <div class="rule-card">
                        <div class="rule-icon">�</div>
                        <h3>Modos de Jogo</h3>
                        <p>Suporta <strong>Individual</strong> (8&ndash;32 atletas) e <strong>Duplas Fixas</strong>
                        (8&ndash;32 pares). No modo duplas, os pares competem juntos em todo o torneio,
                        gerando uma <strong>dupla campeã</strong>.</p>
                    </div>
                    <div class="rule-card">
                        <div class="rule-icon">🏅</div>
                        <h3>Sistema de Grupos</h3>
                        <p>Atletas divididos em <strong>grupos de 8</strong> por sorteio.
                        <strong>7 jogos</strong> por atleta em 1 set de 4 games, sem tiebreak.</p>
                    </div>
                    <div class="rule-card">
                        <div class="rule-icon">⭐</div>
                        <h3>Pontuação Individual</h3>
                        <p>Cada atleta pontua pelo <strong>saldo de games</strong>.
                        Cada game conquistado equivale a <strong>1 ponto</strong> na tabela do grupo.</p>
                    </div>
                    <div class="rule-card">
                        <div class="rule-icon">🚀</div>
                        <h3>Classificação</h3>
                        <p>Os <strong>2 melhores atletas</strong> de cada grupo avançam para a fase
                        eliminatória em duplas sorteadas automaticamente pelo sistema.</p>
                    </div>
                    <div class="rule-card">
                        <div class="rule-icon">⚖️</div>
                        <h3>Critérios de Desempate</h3>
                        <p><strong>1º</strong> Maior número de vitórias &nbsp;
                           <strong>2º</strong> Menor número de derrotas &nbsp;
                           <strong>3º</strong> Sorteio entre os empatados</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'layout/rodape.php'; ?>
    <script>window.BASEPATH = '';</script>
    <script src="js/app.js"></script>
</body>
</html>
