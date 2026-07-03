<?php

function gerar_round_robin_grupo(array $ids): array
{
    $n = count($ids);
    if ($n < 2) {
        return [];
    }

    $lista = $ids;
    $fixed = array_pop($lista);   // Fixa o último
    $size  = $n - 1;              // = count($lista)
    $rodadas = [];

    for ($r = 0; $r < $size; $r++) {
        $partidas = [];

        // Partida do elemento fixo com lista[$r]
        $partidas[] = [
            'j1'       => $fixed,
            'j2'       => $lista[$r % $size],
            'placar_1' => '',
            'placar_2' => '',
        ];

        // Demais n/2 - 1 pares
        for ($i = 1; $i < (int)($n / 2); $i++) {
            $a = $lista[($r + $i) % $size];
            $b = $lista[($r - $i + $size) % $size];
            $partidas[] = [
                'j1'       => $a,
                'j2'       => $b,
                'placar_1' => '',
                'placar_2' => '',
            ];
        }

        $rodadas[] = [
            'numero'   => $r + 1,
            'status'   => 'pendente',
            'partidas' => $partidas,
        ];
    }

    return $rodadas;
}

/**
 * Divide os participantes em grupos de 8 (sorteio aleatório)
 * e gera o schedule round-robin de cada grupo.
 */
function gerar_grupos(array $participantes): array
{
    $ids = array_column($participantes, 'id');
    shuffle($ids);

    $grupos = [];
    foreach (array_chunk($ids, 8) as $i => $grupoIds) {
        $grupos[] = [
            'numero'   => $i + 1,
            'jogadores' => $grupoIds,
            'rodadas'  => gerar_round_robin_grupo($grupoIds),
        ];
    }

    return $grupos;
}
