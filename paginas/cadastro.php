<?php
$basePath  = '../';
$activeNav = 'atletas';
require_once '../nucleo/bd.php';
$existentes    = ler_json('../dados/participantes.json');
$totalExist    = count($existentes);
$modoExistente = (!empty($existentes) && ($existentes[0]['modo_dupla'] ?? false)) ? 'duplas' : 'individual';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Atletas &mdash; LetzPlay Beach Tennis</title>
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body>
    <?php include '../layout/cabecalho.php'; ?>

    <main class="page-layout">
        <div class="container">
            <div class="page-header">
                <a href="../index.php" class="page-back">Voltar ao início</a>
                <h1 class="page-title" id="page-h1">Cadastro de Atletas</h1>
                <p class="page-subtitle" id="page-subtitle">Escolha a quantidade de participantes e preencha os dados de cada atleta.</p>
            </div>

            <?php if ($totalExist > 0): ?>
            <div class="alert alert-info" style="margin-bottom:24px;">
                <strong>Atenção:</strong> Já existem <?= $totalExist ?> <?= $modoExistente === 'duplas' ? 'duplas' : 'atletas' ?> cadastrados.
                Salvar novamente irá substituir todos os dados do torneio atual.
            </div>
            <?php endif; ?>

            <!-- MODO DO TORNEIO -->
            <div class="card" style="margin-bottom:24px;">
                <div class="card-header">
                    <h3>Modo do Torneio</h3>
                </div>
                <div class="card-body">
                    <div class="count-selector" style="grid-template-columns:repeat(2,1fr);max-width:420px;">
                        <label class="count-option">
                            <input type="radio" name="modo_choice" id="modo-individual" value="individual"
                                   <?= $modoExistente !== 'duplas' ? 'checked' : '' ?>>
                            <span class="count-option-label">
                                <span class="count-number" style="font-size:1.6rem;">👤</span>
                                <span class="count-label">Individual</span>
                            </span>
                        </label>
                        <label class="count-option">
                            <input type="radio" name="modo_choice" id="modo-duplas" value="duplas"
                                   <?= $modoExistente === 'duplas' ? 'checked' : '' ?>>
                            <span class="count-option-label">
                                <span class="count-number" style="font-size:1.6rem;">👥</span>
                                <span class="count-label">Duplas Fixas</span>
                            </span>
                        </label>
                    </div>
                    <p style="margin-top:10px;font-size:.82rem;color:var(--muted);">
                        <strong>Individual:</strong> cada atleta compete por conta própria &bull;
                        <strong>Duplas Fixas:</strong> pares fixos competem juntos durante todo o torneio, gerando uma dupla campeã
                    </p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 id="card-qty-title">Quantidade de Atletas</h3>
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

                    <form id="form-cadastro" onsubmit="enviarFormulario(event, '../acoes/salvar_atletas.php')">
                        <input type="hidden" name="total" id="total-hidden" value="8">
                        <input type="hidden" name="modo_torneio" id="modo-hidden" value="<?= $modoExistente ?>">
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

    <?php include '../layout/rodape.php'; ?>
    <script>window.BASEPATH = '../';</script>
    <script src="../js/app.js"></script>
    <script>
    (function () {
        const radios      = document.querySelectorAll('input[name="num_atletas"]');
        const modoRadios  = document.querySelectorAll('input[name="modo_choice"]');
        const playerList  = document.getElementById('player-list');
        const totalHidden = document.getElementById('total-hidden');
        const modoHidden  = document.getElementById('modo-hidden');
        const cardTitle   = document.getElementById('card-qty-title');
        const pageH1      = document.getElementById('page-h1');
        const pageSubtitle = document.getElementById('page-subtitle');
        const submitBtn   = document.querySelector('#form-cadastro [type="submit"]');

        function getModo() {
            return document.querySelector('input[name="modo_choice"]:checked')?.value || 'individual';
        }

        function updateCountLabels(isDuplas) {
            document.querySelectorAll('.count-option').forEach(function(opt) {
                const radio = opt.querySelector('input[name="num_atletas"]');
                if (!radio) return;
                const n  = +radio.value;
                const lb = opt.querySelector('.count-label');
                if (!lb) return;
                if (isDuplas) {
                    lb.textContent = n + ' dupla' + (n !== 1 ? 's' : '');
                } else {
                    lb.textContent = (n / 8) + ' grupo' + (n > 8 ? 's' : '');
                }
            });
        }

        function renderPlayers(count) {
            const isDuplas = getModo() === 'duplas';
            modoHidden.value = isDuplas ? 'duplas' : 'individual';

            if (cardTitle)    cardTitle.textContent   = isDuplas ? 'Quantidade de Duplas' : 'Quantidade de Atletas';
            if (pageH1)       pageH1.textContent       = isDuplas ? 'Cadastro de Duplas' : 'Cadastro de Atletas';
            if (pageSubtitle) pageSubtitle.textContent = isDuplas
                ? 'Escolha a quantidade de duplas e preencha os nomes dos dois jogadores de cada par.'
                : 'Escolha a quantidade de participantes e preencha os dados de cada atleta.';
            if (submitBtn)    submitBtn.textContent     = isDuplas ? 'Salvar Duplas e Continuar' : 'Salvar Atletas e Continuar';
            updateCountLabels(isDuplas);

            totalHidden.value = count;
            playerList.innerHTML = '';

            for (var i = 1; i <= count; i++) {
                var grupo = Math.ceil(i / 8);
                if ((i - 1) % 8 === 0) {
                    var div = document.createElement('div');
                    div.className = 'group-divider';
                    div.textContent = count > 8
                        ? 'Grupo ' + grupo
                        : (isDuplas ? 'Duplas' : 'Atletas');
                    playerList.appendChild(div);
                }
                var row = document.createElement('div');
                row.className = 'player-row';
                if (isDuplas) {
                    row.innerHTML =
                        '<div class="player-number">' + i + '</div>' +
                        '<div class="dupla-inputs">' +
                            '<input type="text" name="dupla_j1[]" placeholder="Jogador 1" required maxlength="80">' +
                            '<span class="dupla-sep">/</span>' +
                            '<input type="text" name="dupla_j2[]" placeholder="Jogador 2" required maxlength="80">' +
                        '</div>';
                } else {
                    row.innerHTML =
                        '<div class="player-number">' + i + '</div>' +
                        '<input type="text" name="nome[]" placeholder="Nome completo" required maxlength="80">' +
                        '<input type="text" name="apelido[]" placeholder="Apelido (opcional)" maxlength="40">';
                }
                playerList.appendChild(row);
            }
        }

        modoRadios.forEach(function(r) {
            r.addEventListener('change', function() {
                var count = +(document.querySelector('input[name="num_atletas"]:checked')?.value || 8);
                renderPlayers(count);
            });
        });
        radios.forEach(function(r) {
            r.addEventListener('change', function() { renderPlayers(+r.value); });
        });

        var checked = document.querySelector('input[name="num_atletas"]:checked');
        renderPlayers(checked ? +checked.value : 8);
    })();
    </script>
</body>
</html>
