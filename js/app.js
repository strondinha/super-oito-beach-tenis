
function garantirModal() {
    let overlay = document.getElementById('modal-overlay');
    if (overlay) return overlay;

    overlay = document.createElement('div');
    overlay.id = 'modal-overlay';
    overlay.className = 'janela-sistema-overlay is-hidden';
    overlay.innerHTML = `
        <div class="janela-sistema" role="dialog" aria-modal="true" aria-labelledby="modal-titulo">
            <h3 id="modal-titulo"></h3>
            <p  id="modal-msg"></p>
            <div class="janela-sistema-acoes">
                <button type="button" class="btn btn-outline" id="modal-cancelar">Cancelar</button>
                <button type="button" class="btn btn-primary" id="modal-confirmar">OK</button>
            </div>
        </div>
    `;
    document.body.appendChild(overlay);

    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            fecharModal();
            overlay._resolve && overlay._resolve(false);
        }
    });

    return overlay;
}

function abrirModal({ titulo, mensagem, confirmarTexto = 'OK', cancelarTexto = null, classeConfirmar = 'btn-primary' }) {
    const overlay    = garantirModal();
    const btnConfirm = overlay.querySelector('#modal-confirmar');
    const btnCancel  = overlay.querySelector('#modal-cancelar');

    overlay.querySelector('#modal-titulo').textContent = titulo;
    overlay.querySelector('#modal-msg').textContent    = mensagem;
    btnConfirm.textContent = confirmarTexto;
    btnConfirm.className   = `btn ${classeConfirmar}`;
    btnCancel.textContent  = cancelarTexto || 'Cancelar';
    btnCancel.style.display = cancelarTexto ? '' : 'none';
    overlay.classList.remove('is-hidden');

    return new Promise((resolve) => {
        overlay._resolve = resolve;

        const onOK  = () => { cleanup(); fecharModal(); resolve(true); };
        const onNot = () => { cleanup(); fecharModal(); resolve(false); };

        function cleanup() {
            btnConfirm.removeEventListener('click', onOK);
            btnCancel.removeEventListener('click', onNot);
            overlay._resolve = null;
        }

        btnConfirm.addEventListener('click', onOK,  { once: true });
        btnCancel.addEventListener('click',  onNot, { once: true });
    });
}

function fecharModal() {
    const overlay = document.getElementById('modal-overlay');
    if (overlay) overlay.classList.add('is-hidden');
}

async function msg(titulo, mensagem) {
    return abrirModal({ titulo, mensagem, cancelarTexto: null });
}

/* ----------------------- FORMULÁRIO ------------------------- */

async function enviarFormulario(event, url) {
    event.preventDefault();
    const form = event.target;
    const btn  = form.querySelector('[type="submit"]');
    if (!btn) return;

    const originalHTML = btn.innerHTML;
    btn.disabled  = true;
    btn.innerHTML = '&#8987; Aguarde...';

    try {
        const res  = await fetch(url, { method: 'POST', body: new FormData(form) });
        const json = await res.json();

        if (json.status === 'ok') {
            if (json.redirect) {
                window.location.href = json.redirect;
            }
        } else {
            await msg('Atenção', json.msg || 'Ocorreu um erro inesperado.');
        }
    } catch {
        await msg('Erro de conexão', 'Não foi possível comunicar com o servidor. Verifique sua conexão e tente novamente.');
    } finally {
        btn.disabled  = false;
        btn.innerHTML = originalHTML;
    }
}

/* ----------------------- ZERAR TORNEIO --------------------- */

async function zerarSistema() {
    const confirmado = await abrirModal({
        titulo:          'Zerar Torneio',
        mensagem:        'Esta ação apagará TODOS os dados: atletas, grupos, rodadas e resultados. Essa operação não pode ser desfeita. Confirma?',
        confirmarTexto:  'Sim, zerar tudo',
        cancelarTexto:   'Cancelar',
        classeConfirmar: 'btn-danger',
    });

    if (!confirmado) return;

    try {
        const basePath = (typeof window.BASEPATH !== 'undefined') ? window.BASEPATH : '';
        const res  = await fetch(basePath + 'acoes/zerar.php', { method: 'POST' });
        const json = await res.json();

        if (json.status === 'ok') {
            window.location.href = basePath + 'index.php';
        }
    } catch {
        await msg('Erro', 'Não foi possível zerar o sistema.');
    }
}

/* =================== PLACAR AO VIVO =================== */

const PTS = [0, 15, 30, 40];

async function adicionarPonto(gi, rodadaNum, pi, jogador, btn) {
    if (btn) btn.disabled = true;

    try {
        const basePath = (typeof window.BASEPATH !== 'undefined') ? window.BASEPATH : '';
        const res = await fetch(basePath + 'acoes/salvar_resultado.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ grupo_idx: gi, rodada_num: rodadaNum, partida_idx: pi, jogador }),
        });

        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();
        if (data.status !== 'ok') { await msg('Erro', data.msg || 'Erro ao registrar ponto.'); return; }

        atualizarMatchUI(gi, pi, data.partida);

        if (data.rodada_concluida) {
            mostrarAvanco(data.rodada_num, data.total_rodadas, data.tudo_concluido);
        }
    } catch (e) {
        await msg('Erro', 'Não foi possível registrar o ponto. Tente novamente.');
    } finally {
        if (btn) btn.disabled = false;
    }
}

function atualizarMatchUI(gi, pi, p) {
    const card = document.getElementById(`match-${gi}-${pi}`);
    if (!card) return;

    const g1 = document.getElementById(`games-${gi}-${pi}-j1`);
    const g2 = document.getElementById(`games-${gi}-${pi}-j2`);
    if (g1) g1.textContent = p.placar_1 ?? 0;
    if (g2) g2.textContent = p.placar_2 ?? 0;

    const center = document.getElementById(`center-${gi}-${pi}`);
    if (!center) return;

    const ej = p.estado_jogo  ?? 'normal';
    const es = p.estado_set   ?? 'normal';

    if (p.status_partida === 'concluida') {
        const nome = p.vencedor === 'j1'
            ? document.getElementById(`name-${gi}-${pi}-j1`)?.textContent
            : document.getElementById(`name-${gi}-${pi}-j2`)?.textContent;
        center.innerHTML = `<div class="live-status-done">&#10003;</div>`;
        const ctrl = document.getElementById(`ctrl-${gi}-${pi}`);
        if (ctrl) {
            ctrl.className = 'live-result';
            ctrl.innerHTML = `&#10003; ${nome ?? ''}`;
        }
        card.classList.add('match-done');
        const sides = card.querySelectorAll('.live-side');
        if (p.vencedor === 'j1' && sides[0]) sides[0].classList.add('winner');
        if (p.vencedor === 'j2' && sides[1]) sides[1].classList.add('winner');
        return;
    }

    if (es === 'mini_tiebreak') {
        center.innerHTML = `<div class="live-tb-label">TB</div>
            <div class="live-tb-score">${p.mini_tb_j1 ?? 0} &mdash; ${p.mini_tb_j2 ?? 0}</div>`;
        return;
    }
    if (ej === 'deuce') {
        center.innerHTML = `<div class="live-deuce">DEUCE</div>`;
        return;
    }
    if (ej === 'vantagem_j1' || ej === 'vantagem_j2') {
        const nome = ej === 'vantagem_j1'
            ? document.getElementById(`name-${gi}-${pi}-j1`)?.textContent
            : document.getElementById(`name-${gi}-${pi}-j2`)?.textContent;
        center.innerHTML = `<div class="live-adv-label">ADV<br><small>${nome ?? ''}</small></div>`;
        return;
    }
    // Normal: só o traço
    center.innerHTML = `<div class="live-vs">&#x2014;</div>`;
}

function mostrarAvanco(rodadaNum, totalRodadas, tudoConcluido) {
    const bar = document.getElementById('avanco-bar');
    const btn = document.getElementById('avanco-btn');
    if (!bar || !btn) return;

    const base = (typeof window.BASEPATH !== 'undefined') ? window.BASEPATH : '';
    if (tudoConcluido) {
        btn.href        = base + 'paginas/classificacao.php';
        btn.textContent = '🏆 Ver Classificação Final';
    } else {
        btn.href        = 'rodadas.php?rodada=' + (rodadaNum + 1);
        btn.textContent = 'Próxima Rodada →';
    }
    bar.style.display = 'flex';
}
