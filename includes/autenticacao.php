<?php

declare (strict_types = 1);

/* =========================================================
   SESSÃO
========================================================= */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/* =========================================================
   RECUPERA USUÁRIO DA SESSÃO
========================================================= */

$usuarioAutenticado =
$_SESSION['usuario'] ?? null;

/* =========================================================
   VALIDA AUTENTICAÇÃO
========================================================= */

if (
    ! is_array($usuarioAutenticado) ||
    empty($usuarioAutenticado['id']) ||
    empty($usuarioAutenticado['nome']) ||
    empty($usuarioAutenticado['login'])
) {

    /* =====================================================
       LIMPA POSSÍVEL SESSÃO INVÁLIDA
    ===================================================== */

    unset(
        $_SESSION['usuario']
    );

    /* =====================================================
       MENSAGEM PARA LOGIN
    ===================================================== */

    $_SESSION['erro_login'] =
        'Faça login para acessar o Banco de Questões.';

    /* =====================================================
       REDIRECIONAMENTO
    ===================================================== */

    header(
        'Location: /desafiodomilhao/login.php',
        true,
        303
    );

    exit;
}

/* =========================================================
   DISPONIBILIZA USUÁRIO LOGADO
========================================================= */

$usuarioLogado =
    $usuarioAutenticado;
