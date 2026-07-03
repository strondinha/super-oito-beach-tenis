<?php

$basePath  = $basePath  ?? '';
$activeNav = $activeNav ?? '';

function navClass(string $key, string $active): string {
    return $key === $active ? ' class="active"' : '';
}
?>
<header class="site-header">
    <div class="header-inner">
        <a href="<?= $basePath ?>index.php" class="header-brand">
            <span class="brand-icon">🎾</span>
            <div class="brand-text">
                <span class="brand-name">LetzPlay</span>
                <span class="brand-sub">Beach Tennis</span>
            </div>
        </a>
        <nav class="header-nav">
            <a href="<?= $basePath ?>players/register.php"<?= navClass('atletas', $activeNav) ?>>Atletas</a>
            <a href="<?= $basePath ?>setup/setup.php"<?= navClass('grupos', $activeNav) ?>>Grupos</a>
            <a href="<?= $basePath ?>matches/rounds.php"<?= navClass('rodadas', $activeNav) ?>>Rodadas</a>
            <a href="<?= $basePath ?>standings/standings.php"<?= navClass('classificacao', $activeNav) ?>>Classificação</a>
        </nav>
    </div>
</header>
