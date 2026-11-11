<?php

declare(strict_types=1);


/* ============================================================
   SESSÃO
============================================================ */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}


/* ============================================================
   DEPENDÊNCIAS
============================================================ */

require_once __DIR__ . '/includes/url.php';

require_once __DIR__ . '/includes/conexao.php';


/* ============================================================
   CONEXÃO
============================================================ */

$pdo = conectarBanco();


/* ============================================================
   ID DA ÁREA
============================================================ */

$areaId =
    filter_input(
        INPUT_GET,
        'id',
        FILTER_VALIDATE_INT
    );


/* ============================================================
   RETORNO
============================================================ */

$retorno =
    trim(
        (string) (
            $_GET['retorno']
            ?? ''
        )
    );


/* ============================================================
   ID INVÁLIDO
============================================================ */

if (
    $areaId === false ||
    $areaId === null ||
    $areaId <= 0
) {

    header(
        'Location: ' .
        urlProjeto(
            'areas.php'
        ),
        true,
        303
    );

    exit;
}


/* ============================================================
   BUSCA A ÁREA
============================================================ */

$sql = '
    SELECT
        id,
        nome,
        slug,
        descricao,
        icone,
        ativo,
        ordem
    FROM areas
    WHERE
        id = :id
        AND ativo = 1
    LIMIT 1
';


$stmt =
    $pdo->prepare(
        $sql
    );


$stmt->execute(
    [
        ':id' =>
            $areaId,
    ]
);


$area =
    $stmt->fetch(
        PDO::FETCH_ASSOC
    );


/* ============================================================
   ÁREA NÃO EXISTE OU ESTÁ INATIVA
============================================================ */

if (!$area) {

    unset(
        $_SESSION['area']
    );


    header(
        'Location: ' .
        urlProjeto(
            'areas.php'
        ),
        true,
        303
    );

    exit;
}


/* ============================================================
   SALVA ÁREA NA SESSÃO
============================================================ */

$_SESSION['area'] = [

    'id' =>
        (int) $area['id'],

    'nome' =>
        (string) $area['nome'],

    'slug' =>
        (string) $area['slug'],

    'descricao' =>
        (string) (
            $area['descricao']
            ?? ''
        ),

    'icone' =>
        (string) (
            $area['icone']
            ?? 'fa-solid fa-layer-group'
        ),
];


/* ============================================================
   REMOVE PARTIDA ANTERIOR

   Ao selecionar uma nova área, uma partida antiga não deve
   continuar utilizando perguntas da área anterior.
============================================================ */

unset(
    $_SESSION['partida'],
    $_SESSION['pergunta_atual']
);


/* ============================================================
   DESTINO

   Se areas.php foi aberta para retornar diretamente ao jogo,
   respeitamos o retorno.

   No fluxo normal, voltamos para a página inicial.
============================================================ */

if ($retorno === 'jogo') {

    header(
        'Location: ' .
        urlProjeto(
            'configurar-partida.php'
        ),
        true,
        303
    );

    exit;
}


/* ============================================================
   FLUXO NORMAL
============================================================ */

header(
    'Location: ' .
    urlProjeto(),
    true,
    303
);

exit;