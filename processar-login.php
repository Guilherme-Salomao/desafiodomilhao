<?php

declare(strict_types=1);

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
    ($_SERVER['REQUEST_METHOD'] ?? '')
    !== 'POST'
) {

    header(
        'Location: login.php',
        true,
        303
    );

    exit;
}


/* =========================================================
   CONEXÃO
========================================================= */

require_once __DIR__ .
    '/includes/conexao.php';

$pdo =
    conectarBanco();


/* =========================================================
   DESTINO
========================================================= */

$destino =
    trim(
        (string) (
            $_POST['destino']
            ?? $_SESSION['destino_login']
            ?? 'perguntas'
        )
    );


$destinosPermitidos = [
    'perguntas',
    'areas',
];


if (
    !in_array(
        $destino,
        $destinosPermitidos,
        true
    )
) {

    $destino =
        'perguntas';
}


/* =========================================================
   DADOS RECEBIDOS
========================================================= */

$login =
    trim(
        (string) (
            $_POST['login']
            ?? ''
        )
    );


$senhaDigitada =
    (string) (
        $_POST['senha']
        ?? ''
    );


/* =========================================================
   SALVA LOGIN PARA REEXIBIÇÃO
========================================================= */

$_SESSION['login_anterior'] =
    $login;


/* =========================================================
   VALIDA CAMPOS
========================================================= */

if (
    $login === '' ||
    $senhaDigitada === ''
) {

    $_SESSION['erro_login'] =
        'Informe o login e a senha.';


    header(
        'Location: login.php?destino=' .
        rawurlencode($destino),
        true,
        303
    );

    exit;
}


/* =========================================================
   DESCOBRE A COLUNA DE SENHA DA TABELA USUARIOS

   Compatível com:
   - senha_hash
   - password_hash
   - senha
   - password
========================================================= */

try {

    $stmtEstrutura =
        $pdo->query(
            'SHOW COLUMNS FROM usuarios'
        );


    $colunasUsuarios =
        $stmtEstrutura->fetchAll(
            PDO::FETCH_COLUMN
        );


} catch (Throwable $erro) {

    error_log(
        '[Login - estrutura usuários] ' .
        $erro->getMessage()
    );


    $_SESSION['erro_login'] =
        'Não foi possível validar os dados de acesso.';


    header(
        'Location: login.php?destino=' .
        rawurlencode($destino),
        true,
        303
    );

    exit;
}


/* =========================================================
   LOCALIZA COLUNA DE SENHA
========================================================= */

$colunaSenha =
    null;


$possiveisColunasSenha = [
    'senha_hash',
    'password_hash',
    'senha',
    'password',
];


foreach (
    $possiveisColunasSenha
    as $possivelColuna
) {

    if (
        in_array(
            $possivelColuna,
            $colunasUsuarios,
            true
        )
    ) {

        $colunaSenha =
            $possivelColuna;

        break;
    }
}


/* =========================================================
   COLUNA NÃO LOCALIZADA
========================================================= */

if ($colunaSenha === null) {

    error_log(
        '[Login] Nenhuma coluna de senha reconhecida foi encontrada na tabela usuarios.'
    );


    $_SESSION['erro_login'] =
        'A estrutura de autenticação do sistema está inválida.';


    header(
        'Location: login.php?destino=' .
        rawurlencode($destino),
        true,
        303
    );

    exit;
}


/* =========================================================
   VALIDA COLUNAS NECESSÁRIAS
========================================================= */

$colunasObrigatorias = [
    'id',
    'login',
    'nome',
    'ativo',
];


foreach (
    $colunasObrigatorias
    as $colunaObrigatoria
) {

    if (
        !in_array(
            $colunaObrigatoria,
            $colunasUsuarios,
            true
        )
    ) {

        error_log(
            '[Login] Coluna obrigatória ausente em usuarios: ' .
            $colunaObrigatoria
        );


        $_SESSION['erro_login'] =
            'A estrutura de usuários do sistema está incompleta.';


        header(
            'Location: login.php?destino=' .
            rawurlencode($destino),
            true,
            303
        );

        exit;
    }
}


/* =========================================================
   BUSCA USUÁRIO

   O nome da coluna foi validado acima antes de entrar
   no SQL.
========================================================= */

$sqlUsuario =
    '
        SELECT
            id,
            login,
            nome,
            ativo,
            `' . $colunaSenha . '` AS senha_armazenada

        FROM usuarios

        WHERE login = :login

        LIMIT 1
    ';


try {

    $stmtUsuario =
        $pdo->prepare(
            $sqlUsuario
        );


    $stmtUsuario->execute(
        [
            ':login' =>
                $login,
        ]
    );


    $usuario =
        $stmtUsuario->fetch(
            PDO::FETCH_ASSOC
        );


} catch (Throwable $erro) {

    error_log(
        '[Login - consulta usuário] ' .
        $erro->getMessage()
    );


    $_SESSION['erro_login'] =
        'Não foi possível validar o acesso.';


    header(
        'Location: login.php?destino=' .
        rawurlencode($destino),
        true,
        303
    );

    exit;
}


/* =========================================================
   USUÁRIO NÃO ENCONTRADO
========================================================= */

if (!$usuario) {

    $_SESSION['erro_login'] =
        'Login ou senha inválidos.';


    header(
        'Location: login.php?destino=' .
        rawurlencode($destino),
        true,
        303
    );

    exit;
}


/* =========================================================
   STATUS
========================================================= */

if (
    (int) (
        $usuario['ativo']
        ?? 0
    ) !== 1
) {

    $_SESSION['erro_login'] =
        'Seu cadastro ainda não está autorizado para acessar o sistema.';


    header(
        'Location: login.php?destino=' .
        rawurlencode($destino),
        true,
        303
    );

    exit;
}


/* =========================================================
   VALIDA SENHA
========================================================= */

$senhaArmazenada =
    (string) (
        $usuario['senha_armazenada']
        ?? ''
    );


if (
    $senhaArmazenada === '' ||
    !password_verify(
        $senhaDigitada,
        $senhaArmazenada
    )
) {

    $_SESSION['erro_login'] =
        'Login ou senha inválidos.';


    header(
        'Location: login.php?destino=' .
        rawurlencode($destino),
        true,
        303
    );

    exit;
}


/* =========================================================
   ÁREAS AUTORIZADAS
========================================================= */

$sqlAreas =
    '
        SELECT
            a.id,
            a.nome,
            a.slug,
            a.descricao,
            a.icone

        FROM usuario_areas ua

        INNER JOIN areas a
            ON a.id = ua.area_id

        WHERE
            ua.usuario_id = :usuario_id
            AND a.ativo = 1

        ORDER BY
            a.ordem ASC,
            a.nome ASC
    ';


try {

    $stmtAreas =
        $pdo->prepare(
            $sqlAreas
        );


    $stmtAreas->execute(
        [
            ':usuario_id' =>
                (int) $usuario['id'],
        ]
    );


    $areasUsuario =
        $stmtAreas->fetchAll(
            PDO::FETCH_ASSOC
        );


} catch (Throwable $erro) {

    error_log(
        '[Login - áreas usuário] ' .
        $erro->getMessage()
    );


    $_SESSION['erro_login'] =
        'Não foi possível verificar suas áreas autorizadas.';


    header(
        'Location: login.php?destino=' .
        rawurlencode($destino),
        true,
        303
    );

    exit;
}


/* =========================================================
   SEM ÁREAS
========================================================= */

if (empty($areasUsuario)) {

    $_SESSION['erro_login'] =
        'Sua conta não possui nenhuma área autorizada. Entre em contato com o administrador.';


    header(
        'Location: login.php?destino=' .
        rawurlencode($destino),
        true,
        303
    );

    exit;
}


/* =========================================================
   REGENERA SESSÃO
========================================================= */

session_regenerate_id(
    true
);


/* =========================================================
   SESSÃO DO USUÁRIO
========================================================= */

$_SESSION['usuario'] = [

    'id' =>
        (int) $usuario['id'],

    'login' =>
        (string) $usuario['login'],

    'nome' =>
        (string) $usuario['nome'],

    'areas' =>
        $areasUsuario,
];


/* =========================================================
   LIMPA TEMPORÁRIOS
========================================================= */

unset(
    $_SESSION['erro_login'],
    $_SESSION['aviso_login'],
    $_SESSION['login_anterior'],
    $_SESSION['destino_login']
);


/* =========================================================
   GERENCIAMENTO DE ÁREAS
========================================================= */

if ($destino === 'areas') {

    /*
     * Confirma que a senha foi digitada especificamente
     * para entrar no gerenciamento de áreas.
     */

    $_SESSION['areas_autorizado'] = [

        'usuario_id' =>
            (int) $usuario['id'],

        'autorizado_em' =>
            time(),
    ];


    header(
        'Location: admin/areas.php',
        true,
        303
    );

    exit;
}


/* =========================================================
   BANCO DE QUESTÕES
========================================================= */

/*
 * O acesso normal ao Banco de Questões não concede
 * automaticamente permissão para gerenciamento de áreas.
 */

unset(
    $_SESSION['areas_autorizado']
);


header(
    'Location: admin/perguntas.php',
    true,
    303
);

exit;