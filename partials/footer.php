<?php

$basePath = $basePath ?? '';
?>
<footer class="site-footer">
    <div class="footer-inner">
        <div>
            <span class="footer-brand-name">🎾 LetzPlay Beach Tennis</span>
            <p class="footer-brand-desc">
                Sistema profissional de gerenciamento de torneios de beach tennis.
                Fase de grupos com round-robin e fase eliminatória, seguindo as normas
                da ITF e CBT.
            </p>
        </div>
        <div class="footer-col">
            <h4>Acesso Rápido</h4>
            <ul>
                <li><a href="<?= $basePath ?>players/register.php">Cadastrar Atletas</a></li>
                <li><a href="<?= $basePath ?>setup/setup.php">Sortear Grupos</a></li>
                <li><a href="<?= $basePath ?>matches/rounds.php">Rodadas</a></li>
                <li><a href="<?= $basePath ?>standings/standings.php">Classificação</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Regulamento</h4>
            <ul>
                <li>Regras ITF e CBT</li>
                <li>Rede a 1,70m do solo</li>
                <li>1 set de 4 games, sem tiebreak</li>
                <li>8 a 32 atletas por torneio</li>
                <li>Grupos de 8 por sorteio</li>
                <li>Top 2 de cada grupo avança</li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> LetzPlay Beach Tennis &mdash; Todos os direitos reservados.</p>
    </div>
</footer>
