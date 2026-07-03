<?php
$basePath  = '../';
$activeNav = 'atletas';
require_once '../lib/storage.php';
$existentes = ler_json('../data/participantes.json');
$totalExist  = count($existentes);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Atletas &mdash; LetzPlay Beach Tennis</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../partials/header.php'; ?>

    <main class="page-layout">
        <div class="container">
            <div class="page-header">
                <a href="../index.php" class="page-back">Voltar ao início</a>
                <h1 class="page-title">Cadastro de Atletas</h1>
                <p class="page-subtitle">Escolha a quantidade de participantes e preencha os dados de cada atleta.</p>
            </div>

            <?php if ($totalExist > 0): ?>
            <div class="alert alert-info" style="margin-bottom:24px;">
                <strong>Atenção:</strong> Já existem <?= $totalExist ?> atletas cadastrados.
                Salvar novamente irá substituir todos os dados do torneio atual.
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3>Quantidade de Atletas</h3>
                </div>
                <div class="card-body">
                    <div class="count-selector" id="count-selector">
                        <?php foreach ([8, 16, 24, 32] as $n): ?>
                        <label class="count-option">
                            <input type="radio" name="num_atletas" value="<?= $n ?>" <?= $n === 8 ? 'checked' : '' ?>>
                            <span class="count-option-label">
                                <span class="count-number"><?= $n ?></span>
                                <span class="count-label"><?= $n / 8 ?> grupo<?= $n > 8 ? 's' : '' ?></span>
                            </span>
                        </label>
                        <?php endforeach; ?>
                    </div>

                    <form id="form-cadastro" onsubmit="enviarFormulario(event, 'save.php')">
                        <input type="hidden" name="total" id="total-hidden" value="8">
                        <div class="player-list" id="player-list"></div>
                        <div style="margin-top:28px;">
                            <button type="submit" class="btn btn-primary btn-block" style="font-size:1rem;padding:14px;">
                                Salvar Atletas e Continuar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <?php include '../partials/footer.php'; ?>
    <script>window.BASEPATH = '../';</script>
    <script src="../js/ui.js"></script>
    <script>
    (function () {
        const radios     = document.querySelectorAll('input[name="num_atletas"]');
        const playerList = document.getElementById('player-list');
        const totalHidden = document.getElementById('total-hidden');

        function renderPlayers(count) {
            totalHidden.value = count;
            playerList.innerHTML = '';
            for (let i = 1; i <= count; i++) {
                const grupo = Math.ceil(i / 8);
                if ((i - 1) % 8 === 0) {
                    const div = document.createElement('div');
                    div.className = 'group-divider';
                    div.textContent = count > 8 ? 'Grupo ' + grupo : 'Atletas';
                    playerList.appendChild(div);
                }
                const row = document.createElement('div');
                row.className = 'player-row';
                row.innerHTML = '<div class="player-number">' + i + '</div>'
                    + '<input type="text" name="nome[]" placeholder="Nome completo" required maxlength="80">'
                    + '<input type="text" name="apelido[]" placeholder="Apelido (opcional)" maxlength="40">';
                playerList.appendChild(row);
            }
        }

        radios.forEach(r => r.addEventListener('change', () => renderPlayers(+r.value)));
        const checked = document.querySelector('input[name="num_atletas"]:checked');
        renderPlayers(checked ? +checked.value : 8);
    })();
    </script>
</body>
</html>
