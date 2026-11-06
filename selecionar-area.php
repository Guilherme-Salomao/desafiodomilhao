<?php

declare (strict_types = 1);

/* ============================================================
   SESSÃO
============================================================ */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/* ============================================================
   REPOSITORY
============================================================ */

require_once __DIR__ .
    '/includes/repositories/AreaRepository.php';

/* ============================================================
   RECEBE PARÂMETROS
============================================================ */

$slug = trim(
    (string) ($_GET['area'] ?? '')
);

$retorno = trim(
    (string) ($_GET['retorno'] ?? 'inicio')
);

/* ============================================================
   VALIDA SLUG
============================================================ */

if ($slug === '') {

    header(
        'Location: /desafiodomilhao/areas.php',
        true,
        302
    );

    exit;
}

/* ============================================================
   RETORNOS PERMITIDOS
============================================================ */

$retornosPermitidos = [
    'inicio',
    'jogo',
    'competicao',
];

if (
    ! in_array(
        $retorno,
        $retornosPermitidos,
        true
    )
) {

    $retorno = 'inicio';
}

/* ============================================================
   BUSCA ÁREA
============================================================ */

try {

    $areaRepository =
    new AreaRepository();

    $area =
    $areaRepository->buscarAtivaPorSlug(
        $slug
    );

    /* ========================================================
       ÁREA INVÁLIDA
    ======================================================== */

    if ($area === null) {

        header(
            'Location: /desafiodomilhao/areas.php?retorno=' .
            urlencode($retorno),
            true,
            302
        );

        exit;
    }

    /* ========================================================
       SALVA ÁREA
    ======================================================== */

    $_SESSION['area'] = [

        'id'    =>
        (int) $area['id'],

        'nome'  =>
        (string) $area['nome'],

        'slug'  =>
        (string) $area['slug'],

        'icone' =>
        (string) $area['icone'],

    ];

    /* ========================================================
       DESTINO
    ======================================================== */

    switch ($retorno) {

        /* HOME */

        case 'inicio':

            header(
                'Location: /desafiodomilhao/',
                true,
                302
            );

            break;

        /* COMPETIÇÃO */

        case 'competicao':

            header(
                'Location: /desafiodomilhao/configurar-partida.php?modo=competicao',
                true,
                302
            );

            break;

        /* JOGO INDIVIDUAL */

        case 'jogo':

        default:

            header(
                'Location: /desafiodomilhao/configurar-partida.php?modo=individual',
                true,
                302
            );

            break;

    }

    exit;

} catch (Throwable $erro) {

    error_log(
        '[Desafio do Milhão - Seleção de Área] ' .
        $erro->getMessage()
    );

    header(
        'Location: /desafiodomilhao/areas.php?retorno=' .
        urlencode($retorno),
        true,
        302
    );

    exit;
}
