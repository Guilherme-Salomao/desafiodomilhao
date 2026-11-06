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
        'Location: /desafiodomilhao/configuracoes.php',
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

$nome = trim(
    (string) ($_POST['nome'] ?? '')
);

$login = trim(
    mb_strtolower(
        (string) ($_POST['login'] ?? '')
    )
);

$senha =
(string) ($_POST['senha'] ?? '');

$confirmarSenha =
(string) ($_POST['confirmar_senha'] ?? '');

$areasRecebidas =
$_POST['areas'] ?? [];

/* =========================================================
   FUNÇÃO DE ERRO

   Qualquer erro retorna para Configurações.
========================================================= */

function retornarCadastroComErro(
    string $mensagem
): never {

    $_SESSION['erro_cadastro_configuracoes'] =
        $mensagem;

    unset(
        $_SESSION['sucesso_cadastro']
    );

    header(
        'Location: /desafiodomilhao/configuracoes.php',
        true,
        303
    );

    exit;
}

/* =========================================================
   VALIDAÇÃO DO NOME
========================================================= */

if (
    mb_strlen($nome) < 3 ||
    mb_strlen($nome) > 120
) {

    retornarCadastroComErro(
        'Informe um nome válido.'
    );
}

/* =========================================================
   VALIDAÇÃO DO LOGIN
========================================================= */

if (
    mb_strlen($login) < 3 ||
    mb_strlen($login) > 80
) {

    retornarCadastroComErro(
        'O login deve possuir entre 3 e 80 caracteres.'
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

    retornarCadastroComErro(
        'O login possui caracteres inválidos.'
    );
}

/* =========================================================
   TAMANHO DA SENHA
========================================================= */

if (
    strlen($senha) < 8 ||
    strlen($senha) > 72
) {

    retornarCadastroComErro(
        'A senha deve possuir entre 8 e 72 caracteres.'
    );
}

/* =========================================================
   LETRA MAIÚSCULA
========================================================= */

if (
    ! preg_match(
        '/[A-Z]/',
        $senha
    )
) {

    retornarCadastroComErro(
        'A senha deve possuir pelo menos uma letra maiúscula.'
    );
}

/* =========================================================
   LETRA MINÚSCULA
========================================================= */

if (
    ! preg_match(
        '/[a-z]/',
        $senha
    )
) {

    retornarCadastroComErro(
        'A senha deve possuir pelo menos uma letra minúscula.'
    );
}

/* =========================================================
   NÚMERO
========================================================= */

if (
    ! preg_match(
        '/[0-9]/',
        $senha
    )
) {

    retornarCadastroComErro(
        'A senha deve possuir pelo menos um número.'
    );
}

/* =========================================================
   CONFIRMAÇÃO DA SENHA
========================================================= */

if (
    ! hash_equals(
        $senha,
        $confirmarSenha
    )
) {

    retornarCadastroComErro(
        'As senhas informadas não são iguais.'
    );
}

/* =========================================================
   ÁREAS
========================================================= */

if (
    ! is_array($areasRecebidas) ||
    empty($areasRecebidas)
) {

    retornarCadastroComErro(
        'Selecione pelo menos uma área de atuação.'
    );
}

/* =========================================================
   NORMALIZA IDS
========================================================= */

$areasIds = [];

foreach ($areasRecebidas as $areaId) {

    $id = filter_var(
        $areaId,
        FILTER_VALIDATE_INT
    );

    if (
        $id !== false &&
        $id > 0
    ) {

        $areasIds[] =
        (int) $id;

    }

}

$areasIds = array_values(
    array_unique(
        $areasIds
    )
);

if (empty($areasIds)) {

    retornarCadastroComErro(
        'Selecione pelo menos uma área válida.'
    );
}

/* =========================================================
   PROCESSAMENTO
========================================================= */

try {

    $usuarioRepository =
    new UsuarioRepository();

    /* =====================================================
       LOGIN JÁ EXISTE
    ===================================================== */

    if (
        $usuarioRepository->loginExiste(
            $login
        )
    ) {

        retornarCadastroComErro(
            'Este login já está sendo utilizado. Escolha outro login.'
        );
    }

    /* =====================================================
       ÁREAS VÁLIDAS
    ===================================================== */

    $areasValidas =
    $usuarioRepository
        ->buscarAreasAtivasPorIds(
            $areasIds
        );

    $areasValidasIds = array_map(

        static function (
            array $area
        ): int {

            return (int) $area['id'];

        },

        $areasValidas

    );

    sort($areasIds);

    sort($areasValidasIds);

    if (
        $areasIds !==
        $areasValidasIds
    ) {

        retornarCadastroComErro(
            'Uma ou mais áreas selecionadas são inválidas ou estão inativas.'
        );
    }

    /* =====================================================
       HASH DA SENHA
    ===================================================== */

    $senhaHash =
        password_hash(
        $senha,
        PASSWORD_DEFAULT
    );

    if ($senhaHash === false) {

        throw new RuntimeException(
            'Não foi possível gerar o hash da senha.'
        );
    }

    /* =====================================================
       CRIA USUÁRIO

       O usuário sempre nasce:
       ativo = 0
    ===================================================== */

    $usuarioRepository
        ->criarSolicitacao(

            $nome,

            $login,

            $senhaHash,

            $areasValidasIds

        );

    /* =====================================================
       LIMPA POSSÍVEL ERRO
    ===================================================== */

    unset(
        $_SESSION['erro_cadastro_configuracoes']
    );

    /* =====================================================
       MENSAGEM DE SUCESSO
    ===================================================== */

    $_SESSION['sucesso_cadastro'] =
        'Seu cadastro foi realizado com sucesso. Aguarde a autorização do administrador para acessar o Banco de Questões.';

    /* =====================================================
       VOLTA PARA CONFIGURAÇÕES
    ===================================================== */

    header(
        'Location: /desafiodomilhao/configuracoes.php',
        true,
        303
    );

    exit;

} catch (Throwable $erro) {

    error_log(
        '[Desafio do Milhão - Cadastro de usuário] ' .
        $erro->getMessage()
    );

    retornarCadastroComErro(
        'Não foi possível realizar o cadastro. Tente novamente.'
    );

}
