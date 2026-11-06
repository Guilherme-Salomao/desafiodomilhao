<?php

declare (strict_types = 1);

/* =========================================================
   SESSÃO
========================================================= */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/* =========================================================
   SOMENTE POST
========================================================= */

if (
    ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST'
) {

    header(
        'Location: /desafiodomilhao/login.php',
        true,
        302
    );

    exit;
}

/* =========================================================
   REPOSITORY
========================================================= */

require_once __DIR__ .
    '/includes/repositories/UsuarioRepository.php';

/* =========================================================
   DADOS RECEBIDOS
========================================================= */

$login = trim(
    mb_strtolower(
        (string) ($_POST['login'] ?? '')
    )
);

$senha =
(string) ($_POST['senha'] ?? '');

/* =========================================================
   PRESERVA LOGIN

   A senha nunca é armazenada na sessão.
========================================================= */

$_SESSION['login_anterior'] =
    $login;

/* =========================================================
   FUNÇÃO - ERRO
========================================================= */

function retornarLoginComErro(
    string $mensagem
): never {

    $_SESSION['erro_login'] =
        $mensagem;

    unset(
        $_SESSION['aviso_login']
    );

    header(
        'Location: /desafiodomilhao/login.php',
        true,
        303
    );

    exit;
}

/* =========================================================
   FUNÇÃO - AVISO
========================================================= */

function retornarLoginComAviso(
    string $mensagem
): never {

    $_SESSION['aviso_login'] =
        $mensagem;

    unset(
        $_SESSION['erro_login']
    );

    header(
        'Location: /desafiodomilhao/login.php',
        true,
        303
    );

    exit;
}

/* =========================================================
   VALIDA LOGIN
========================================================= */

if (
    mb_strlen($login) < 3 ||
    mb_strlen($login) > 80
) {

    retornarLoginComErro(
        'Informe um login válido.'
    );
}

/* =========================================================
   FORMATO DO LOGIN
========================================================= */

if (
    ! preg_match(
        '/^[a-z0-9._-]+$/',
        $login
    )
) {

    retornarLoginComErro(
        'Informe um login válido.'
    );
}

/* =========================================================
   VALIDA SENHA
========================================================= */

if (
    $senha === '' ||
    strlen($senha) > 72
) {

    retornarLoginComErro(
        'Informe sua senha.'
    );
}

/* =========================================================
   PROCESSAMENTO
========================================================= */

try {

    $usuarioRepository =
    new UsuarioRepository();

    /* =====================================================
       BUSCA USUÁRIO PELO LOGIN
    ===================================================== */

    $usuario =
    $usuarioRepository
        ->buscarPorLogin(
            $login
        );

    /* =====================================================
       USUÁRIO NÃO ENCONTRADO

       Mantemos mensagem genérica para não revelar
       se determinada conta existe.
    ===================================================== */

    if (! $usuario) {

        retornarLoginComErro(
            'Login ou senha inválidos.'
        );
    }

    /* =====================================================
       VERIFICA SENHA
    ===================================================== */

    $senhaHash =
    (string) (
        $usuario['senha_hash'] ?? ''
    );

    if (
        $senhaHash === '' ||
        ! password_verify(
            $senha,
            $senhaHash
        )
    ) {

        retornarLoginComErro(
            'Login ou senha inválidos.'
        );
    }

    /* =====================================================
       VERIFICA SE USUÁRIO ESTÁ ATIVO
    ===================================================== */

    if (
        (int) ($usuario['ativo'] ?? 0) !== 1
    ) {

        retornarLoginComAviso(
            'Seu cadastro foi realizado, mas o acesso ainda não foi liberado pelo administrador.'
        );
    }

    /* =====================================================
       CARREGA ÁREAS AUTORIZADAS
    ===================================================== */

    $areas =
    $usuarioRepository
        ->listarAreasDoUsuario(
            (int) $usuario['id']
        );

    /* =====================================================
       USUÁRIO ATIVO SEM ÁREAS
    ===================================================== */

    if (empty($areas)) {

        retornarLoginComErro(
            'Sua conta está ativa, mas não possui nenhuma área autorizada. Entre em contato com o administrador.'
        );
    }

    /* =====================================================
       REGENERA ID DA SESSÃO

       Proteção contra session fixation.
    ===================================================== */

    session_regenerate_id(
        true
    );

    /* =====================================================
       MONTA ÁREAS DA SESSÃO
    ===================================================== */

    $areasSessao = [];

    foreach ($areas as $area) {

        $areasSessao[] = [

            'id'    =>
            (int) $area['id'],

            'nome'  =>
            (string) $area['nome'],

            'slug'  =>
            (string) $area['slug'],

            'icone' =>
            (string) $area['icone'],

        ];

    }

    /* =====================================================
       CRIA SESSÃO DO USUÁRIO
    ===================================================== */

    $_SESSION['usuario'] = [

        'id'    =>
        (int) $usuario['id'],

        'nome'  =>
        (string) $usuario['nome'],

        'login' =>
        (string) $usuario['login'],

        'areas' =>
        $areasSessao,

    ];

    /* =====================================================
       LIMPA MENSAGENS ANTIGAS
    ===================================================== */

    unset(
        $_SESSION['erro_login'],
        $_SESSION['aviso_login'],
        $_SESSION['login_anterior']
    );

    /* =====================================================
       ATUALIZA ÚLTIMO LOGIN
    ===================================================== */

    $usuarioRepository
        ->atualizarUltimoLogin(
            (int) $usuario['id']
        );

    /* =====================================================
       REDIRECIONA PARA O BANCO DE QUESTÕES
    ===================================================== */

    header(
        'Location: /desafiodomilhao/admin/perguntas.php',
        true,
        303
    );

    exit;

} catch (Throwable $erro) {

    /* =====================================================
       LOG INTERNO
    ===================================================== */

    error_log(
        '[Desafio do Milhão - Login] ' .
        $erro->getMessage()
    );

    /* =====================================================
       ERRO GENÉRICO
    ===================================================== */

    retornarLoginComErro(
        'Não foi possível realizar o login. Tente novamente.'
    );

}
