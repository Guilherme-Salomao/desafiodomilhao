<?php

declare (strict_types = 1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/* =========================================================
   REMOVE SOMENTE A ÁREA SELECIONADA
========================================================= */

unset($_SESSION['area']);

/* =========================================================
   REMOVE TAMBÉM A PARTIDA ATUAL
========================================================= */

unset($_SESSION['partida']);

/* =========================================================
   REDIRECIONA PARA A HOME
========================================================= */

header(
    'Location: /desafiodomilhao/',
    true,
    302
);

exit;
