<?php

declare(strict_types=1);

/* =========================================================
   SESSÃO
========================================================= */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}


/* =========================================================
   VALIDA PARTIDA
========================================================= */

$partida =
    $_SESSION['partida'] ?? null;

if (
    ! is_array($partida) ||
    empty($partida['area_id'])
) {

    header(
        'Location: /github/desafiodomilhao/public/',
        true,
        303
    );

    exit;
}


/* =========================================================
   QUANTIDADE TOTAL DE PERGUNTAS
========================================================= */

$quantidadePerguntas =
    (int) (
        $partida['quantidade_perguntas']
        ?? 10
    );

if ($quantidadePerguntas < 1) {
    $quantidadePerguntas = 10;
}


/* =========================================================
   PERGUNTA ATUAL
========================================================= */

$perguntaAtual =
    (int) (
        $partida['pergunta_atual']
        ?? 1
    );

if ($perguntaAtual < 1) {
    $perguntaAtual = 1;
}


/* =========================================================
   REGISTRA PERGUNTA UTILIZADA
========================================================= */

$perguntaAtualId =
    (int) (
        $partida['pergunta_atual_id']
        ?? 0
    );

if (
    ! isset(
        $_SESSION['partida']['perguntas_usadas']
    ) ||
    ! is_array(
        $_SESSION['partida']['perguntas_usadas']
    )
) {

    $_SESSION['partida']['perguntas_usadas'] =
        [];
}

if ($perguntaAtualId > 0) {

    $_SESSION['partida']['perguntas_usadas'][] =
        $perguntaAtualId;

    $_SESSION['partida']['perguntas_usadas'] =
        array_values(
            array_unique(
                array_map(
                    'intval',
                    $_SESSION['partida']['perguntas_usadas']
                )
            )
        );
}


/* =========================================================
   VERIFICA SE JÁ CHEGOU AO FINAL
========================================================= */

if ($perguntaAtual >= $quantidadePerguntas) {

    header(
        'Location: /github/desafiodomilhao/public/jogo.php',
        true,
        303
    );

    exit;
}


/* =========================================================
   AVANÇA PERGUNTA
========================================================= */

$_SESSION['partida']['pergunta_atual'] =
    $perguntaAtual + 1;


/* =========================================================
   LIMPA DADOS DA QUESTÃO ANTERIOR
========================================================= */

unset(
    $_SESSION['partida']['pergunta_atual_id'],
    $_SESSION['partida']['resposta_correta_atual']
);


/* =========================================================
   REDIRECIONA PARA O JOGO
========================================================= */

header(
    'Location: /github/desafiodomilhao/public/jogo.php',
    true,
    303
);

exit;