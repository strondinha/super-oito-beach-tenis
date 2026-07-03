<?php
/**
 * Motor de pontuação — LetzPlay Beach Tennis
 * Suporta: sets de 4 ou 6 games, deuce de jogo, deuce de set e mini-tiebreak.
 */

/* ================================================================
   SCORING ENGINE
   ================================================================ */

/**
 * Processa um ponto de $jogador ('j1' | 'j2').
 * Retorna a $partida com estado atualizado.
 */
function processar_ponto(array $partida, string $jogador, array $config): array
{
    if (($partida['status_partida'] ?? 'pendente') === 'concluida') {
        return $partida;
    }

    $partida['status_partida']          = 'em_andamento';
    $partida['total_pontos_' . $jogador] =
        ($partida['total_pontos_' . $jogador] ?? 0) + 1;

    $gps = (int)($config['games_por_set'] ?? 4);

    if (($partida['estado_set'] ?? 'normal') === 'mini_tiebreak') {
        return _mini_tiebreak($partida, $jogador);
    }

    return _ponto_no_jogo($partida, $jogador, $gps);
}

/* ----------------------------------------------------------------
   Internos do engine
   ---------------------------------------------------------------- */

function _ponto_no_jogo(array $p, string $j, int $gps): array
{
    $o  = $j === 'j1' ? 'j2' : 'j1';
    $ej = $p['estado_jogo'] ?? 'normal';

    if ($ej === 'deuce') {
        $p['estado_jogo'] = 'vantagem_' . $j;
        return $p;
    }
    if ($ej === 'vantagem_' . $j) {
        return _ganhar_jogo($p, $j, $gps);
    }
    if ($ej === 'vantagem_' . $o) {
        $p['estado_jogo'] = 'deuce';
        return $p;
    }

    // Progresso normal (0→1→2→3 → vitória; ambos em 3 = deuce)
    $pk  = 'pontos_' . $j;
    $pko = 'pontos_' . $o;
    $pv  = ($p[$pk] ?? 0) + 1;
    $po  = (int)($p[$pko] ?? 0);
    $p[$pk] = $pv;

    if ($pv === 3 && $po === 3) {
        $p['estado_jogo'] = 'deuce';
        $p['pontos_j1']   = 3;
        $p['pontos_j2']   = 3;
        return $p;
    }
    if ($pv >= 4) {
        return _ganhar_jogo($p, $j, $gps);
    }
    return $p;
}

function _ganhar_jogo(array $p, string $j, int $gps): array
{
    $o = $j === 'j1' ? 'j2' : 'j1';

    // Zera estado do jogo
    $p['pontos_j1']   = 0;
    $p['pontos_j2']   = 0;
    $p['estado_jogo'] = 'normal';

    // Incrementa games
    $gk  = $j === 'j1' ? 'placar_1' : 'placar_2';
    $gko = $o === 'j1' ? 'placar_1' : 'placar_2';
    $p[$gk] = ($p[$gk] ?? 0) + 1;

    $gj         = (int)$p[$gk];
    $go         = (int)($p[$gko] ?? 0);
    $deuce_trig = $gps - 1;              // 3 para gps=4 ; 5 para gps=6
    $es         = $p['estado_set'] ?? 'normal';

    if ($es === 'deuce') {
        $diff = $gj - $go;
        if ($diff >= 2) {
            $p['status_partida'] = 'concluida';
            $p['vencedor']       = $j;
        } elseif ($gj === $go) {
            // Empatou novamente → mini-tiebreak
            $p['estado_set'] = 'mini_tiebreak';
            $p['mini_tb_j1'] = 0;
            $p['mini_tb_j2'] = 0;
        }
        // diff == 1: continua no deuce de set
    } else {
        if ($gj >= $gps) {
            $p['status_partida'] = 'concluida';
            $p['vencedor']       = $j;
        } elseif ($gj === $deuce_trig && $go === $deuce_trig) {
            $p['estado_set'] = 'deuce';
        }
    }

    return $p;
}

function _mini_tiebreak(array $p, string $j): array
{
    $o    = $j === 'j1' ? 'j2' : 'j1';
    $mtk  = 'mini_tb_' . $j;
    $mtko = 'mini_tb_' . $o;

    $p[$mtk] = ($p[$mtk] ?? 0) + 1;
    $mt      = (int)$p[$mtk];
    $mto     = (int)($p[$mtko] ?? 0);

    // Primeiro a 7, precisando de 2 de diferença
    if ($mt >= 7 && ($mt - $mto) >= 2) {
        $gk             = $j === 'j1' ? 'placar_1' : 'placar_2';
        $p[$gk]         = ($p[$gk] ?? 0) + 1;
        $p['estado_set']     = 'normal';
        $p['status_partida'] = 'concluida';
        $p['vencedor']       = $j;
    }
    return $p;
}

/* ================================================================
   CLASSIFICAÇÃO
   ================================================================ */

/**
 * Calcula o ranking de um grupo com critérios configuráveis de desempate.
 * $config: ['criterio_1' => '...', 'criterio_2' => '...']
 * Critérios: 'vitorias' | 'derrotas' | 'saldo_games' | 'confronto_direto'
 */
function calcular_classificacao_grupo(array $grupo, array $participantesPorId, array $config = []): array
{
    $ranking = [];

    foreach ($grupo['jogadores'] as $id) {
        $p    = $participantesPorId[$id] ?? null;
        $nome = $p ? ($p['apelido'] ?: $p['nome']) : "Atleta #{$id}";
        $ranking[$id] = [
            'id'           => $id,
            'nome'         => $nome,
            'jogos'        => 0,
            'vitorias'     => 0,
            'derrotas'     => 0,
            'games_pro'    => 0,
            'games_contra' => 0,
            'pontos'       => 0,
            'pts_pro'      => 0,
            'pts_contra'   => 0,
        ];
    }

    foreach ($grupo['rodadas'] as $rodada) {
        foreach ($rodada['partidas'] as $partida) {
            // Compatibilidade: formato antigo (placar='') e novo (status_partida)
            $st = $partida['status_partida'] ?? null;
            if ($st !== null) {
                if ($st !== 'concluida') continue;
            } else {
                if ($partida['placar_1'] === '' || $partida['placar_2'] === '') continue;
            }

            $g1 = (int)$partida['placar_1'];
            $g2 = (int)$partida['placar_2'];
            $j1 = $partida['j1'];
            $j2 = $partida['j2'];
            if (!isset($ranking[$j1]) || !isset($ranking[$j2])) continue;

            $vencedor = $partida['vencedor'] ?? null;
            if ($vencedor === null) {
                if ($g1 > $g2) $vencedor = 'j1';
                elseif ($g2 > $g1) $vencedor = 'j2';
            }

            $ranking[$j1]['jogos']++;
            $ranking[$j1]['games_pro']    += $g1;
            $ranking[$j1]['games_contra'] += $g2;
            $ranking[$j1]['pontos']       += $g1;
            $ranking[$j1]['pts_pro']      += (int)($partida['total_pontos_j1'] ?? 0);
            $ranking[$j1]['pts_contra']   += (int)($partida['total_pontos_j2'] ?? 0);

            $ranking[$j2]['jogos']++;
            $ranking[$j2]['games_pro']    += $g2;
            $ranking[$j2]['games_contra'] += $g1;
            $ranking[$j2]['pontos']       += $g2;
            $ranking[$j2]['pts_pro']      += (int)($partida['total_pontos_j2'] ?? 0);
            $ranking[$j2]['pts_contra']   += (int)($partida['total_pontos_j1'] ?? 0);

            if ($vencedor === 'j1')      { $ranking[$j1]['vitorias']++; $ranking[$j2]['derrotas']++; }
            elseif ($vencedor === 'j2')  { $ranking[$j2]['vitorias']++; $ranking[$j1]['derrotas']++; }
        }
    }

    $ranking = array_values($ranking);
    $crit1   = $config['criterio_1'] ?? 'vitorias';
    $crit2   = $config['criterio_2'] ?? 'saldo_games';

    usort($ranking, static function (array $a, array $b) use ($crit1, $crit2, $grupo): int {
        // Primário: saldo de games
        $sg = ($b['games_pro'] - $b['games_contra']) - ($a['games_pro'] - $a['games_contra']);
        if ($sg !== 0) return $sg;

        $c1 = _cmp_crit($a, $b, $crit1, $grupo);
        if ($c1 !== 0) return $c1;

        $c2 = _cmp_crit($a, $b, $crit2, $grupo);
        if ($c2 !== 0) return $c2;

        return strcmp($a['nome'], $b['nome']);
    });

    return $ranking;
}

function _cmp_crit(array $a, array $b, string $crit, array $grupo): int
{
    switch ($crit) {
        case 'vitorias':       return $b['vitorias'] - $a['vitorias'];
        case 'derrotas':       return $a['derrotas'] - $b['derrotas'];
        case 'saldo_games':    return ($b['games_pro'] - $b['games_contra']) - ($a['games_pro'] - $a['games_contra']);
        case 'confronto_direto': return _cmp_direto($a, $b, $grupo);
        default:               return 0;
    }
}

function _cmp_direto(array $a, array $b, array $grupo): int
{
    foreach ($grupo['rodadas'] as $rodada) {
        foreach ($rodada['partidas'] as $p) {
            $st = $p['status_partida'] ?? null;
            if ($st !== null && $st !== 'concluida') continue;
            if ($st === null && ($p['placar_1'] === '' || $p['placar_2'] === '')) continue;

            $j1 = $p['j1']; $j2 = $p['j2'];
            $v  = $p['vencedor'] ?? ((int)$p['placar_1'] > (int)$p['placar_2'] ? 'j1'
                                   : ((int)$p['placar_2'] > (int)$p['placar_1'] ? 'j2' : null));

            if ($j1 === $a['id'] && $j2 === $b['id']) {
                if ($v === 'j1') return -1;
                if ($v === 'j2') return  1;
                return 0;
            }
            if ($j1 === $b['id'] && $j2 === $a['id']) {
                if ($v === 'j1') return  1;
                if ($v === 'j2') return -1;
                return 0;
            }
        }
    }
    return 0;
}

/**
 * Verifica se todas as rodadas de um grupo foram concluídas.
 */
function grupo_concluido(array $grupo): bool
{
    foreach ($grupo['rodadas'] as $r) {
        if ($r['status'] !== 'concluida') return false;
    }
    return true;
}
