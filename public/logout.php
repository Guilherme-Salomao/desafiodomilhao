<?php

declare(strict_types=1);

/* ============================================================
   SESSÃO
============================================================ */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}


/* ============================================================
   URL DO PROJETO
============================================================ */

require_once dirname(__DIR__) .
    '/includes/url.php';


/* ============================================================
   REMOVE AUTENTICAÇÃO ADMINISTRATIVA

   Mantemos os demais dados da sessão, como área selecionada
   e informações do jogo.
============================================================ */

unset(
    $_SESSION['usuario'],
    $_SESSION['areas_autorizado'],
    $_SESSION['destino_login'],
    $_SESSION['login_anterior'],
    $_SESSION['erro_login'],
    $_SESSION['aviso_login']
);


/* ============================================================
   REGENERA O ID DA SESSÃO
============================================================ */

session_regenerate_id(true);


/* ============================================================
   RETORNA PARA A PÁGINA INICIAL
============================================================ */

header(
    'Location: ' . urlProjeto(),
    true,
    303
);

exit;