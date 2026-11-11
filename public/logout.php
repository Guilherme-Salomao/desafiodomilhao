<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/* =========================================================
   REMOVE SOMENTE A AUTENTICAÇÃO ADMINISTRATIVA
========================================================= */

unset(
    $_SESSION['usuario'],
    $_SESSION['areas_autorizado'],
    $_SESSION['destino_login'],
    $_SESSION['login_anterior'],
    $_SESSION['erro_login'],
    $_SESSION['aviso_login']
);

/* =========================================================
   RENOVA A SESSÃO
========================================================= */

session_regenerate_id(true);

/* =========================================================
   VOLTA PARA O JOGO
========================================================= */

header(
    'Location: /desafiodomilhao/public/',
    true,
    303
);

exit;