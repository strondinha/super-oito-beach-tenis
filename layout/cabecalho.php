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
            <a href="<?= $basePath ?>paginas/cadastro.php"<?= navClass('atletas', $activeNav) ?>>Atletas</a>
            <a href="<?= $basePath ?>paginas/grupos.php"<?= navClass('grupos', $activeNav) ?>>Grupos</a>
            <a href="<?= $basePath ?>paginas/rodadas.php"<?= navClass('rodadas', $activeNav) ?>>Rodadas</a>
            <a href="<?= $basePath ?>paginas/classificacao.php"<?= navClass('classificacao', $activeNav) ?>>Classificação</a>
        </nav>
    </div>
</header>
