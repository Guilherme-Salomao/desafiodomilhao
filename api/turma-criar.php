<?php

declare(strict_types=1);


/* ============================================================
   SESSÃO
============================================================ */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}


/* ============================================================
   CABEÇALHO JSON
============================================================ */

header(
    'Content-Type: application/json; charset=utf-8'
);


/* ============================================================
   DEPENDÊNCIAS
============================================================ */

require_once dirname(__DIR__) .
    '/includes/conexao.php';

require_once dirname(__DIR__) .
    '/includes/url.php';


/* ============================================================
   FUNÇÃO DE RESPOSTA
============================================================ */

function responderTurmaCriar(
    bool $sucesso,
    array $dados = [],
    int $status = 200
): never {

    http_response_code(
        $status
    );

    echo json_encode(
        array_merge(
            [
                'sucesso' =>
                    $sucesso,
            ],
            $dados
        ),
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );

    exit;
}


/* ============================================================
   VALIDA PARTIDA
============================================================ */

$partida =
    $_SESSION['partida']
    ?? null;


if (
    !is_array($partida) ||
    empty($partida['area_id'])
) {

    responderTurmaCriar(
        false,
        [
            'mensagem' =>
                'Nenhuma partida está em andamento.',
        ],
        400
    );
}


/* ============================================================
   PERGUNTA ATUAL
============================================================ */

$perguntaId =
    (int) (
        $partida['pergunta_atual_id']
        ?? 0
    );


if ($perguntaId <= 0) {

    responderTurmaCriar(
        false,
        [
            'mensagem' =>
                'A pergunta atual não foi identificada.',
        ],
        400
    );
}


/* ============================================================
   TOKEN DA PARTIDA
============================================================ */

$partidaToken =
    trim(
        (string) (
            $partida['partida_token']
            ?? $partida['token']
            ?? ''
        )
    );


/*
 * Caso a partida ainda não possua token,
 * criamos um e mantemos na sessão.
 */

if ($partidaToken === '') {

    try {

        $partidaToken =
            bin2hex(
                random_bytes(16)
            );

    } catch (Throwable $erro) {

        responderTurmaCriar(
            false,
            [
                'mensagem' =>
                    'Não foi possível criar o identificador da partida.',
            ],
            500
        );
    }


    $_SESSION['partida']['partida_token'] =
        $partidaToken;
}


/* ============================================================
   CONEXÃO
============================================================ */

try {

    $pdo =
        conectarBanco();

} catch (Throwable $erro) {

    responderTurmaCriar(
        false,
        [
            'mensagem' =>
                'Não foi possível conectar ao banco de dados.',
        ],
        500
    );
}


/* ============================================================
   PROCURA CONSULTA JÁ EXISTENTE

   Se o navegador recarregar, não queremos criar vários QR Codes
   para a mesma pergunta.
============================================================ */

$sqlConsulta = '
    SELECT
        id,
        token
    FROM consultas_turma
    WHERE
        partida_token = :partida_token
        AND pergunta_id = :pergunta_id
        AND ativa = 1
    ORDER BY id DESC
    LIMIT 1
';


$stmtConsulta =
    $pdo->prepare(
        $sqlConsulta
    );


$stmtConsulta->execute(
    [
        ':partida_token' =>
            $partidaToken,

        ':pergunta_id' =>
            $perguntaId,
    ]
);


$consulta =
    $stmtConsulta->fetch(
        PDO::FETCH_ASSOC
    );


/* ============================================================
   CRIA NOVA CONSULTA
============================================================ */

if (!$consulta) {

    try {

        $token =
            bin2hex(
                random_bytes(24)
            );

    } catch (Throwable $erro) {

        responderTurmaCriar(
            false,
            [
                'mensagem' =>
                    'Não foi possível criar o QR Code da consulta.',
            ],
            500
        );
    }


    $sqlInserir = '
        INSERT INTO consultas_turma (
            token,
            partida_token,
            pergunta_id,
            ativa
        )
        VALUES (
            :token,
            :partida_token,
            :pergunta_id,
            1
        )
    ';


    $stmtInserir =
        $pdo->prepare(
            $sqlInserir
        );


    $stmtInserir->execute(
        [
            ':token' =>
                $token,

            ':partida_token' =>
                $partidaToken,

            ':pergunta_id' =>
                $perguntaId,
        ]
    );


    $consultaId =
        (int) $pdo->lastInsertId();


} else {

    $consultaId =
        (int) (
            $consulta['id']
            ?? 0
        );


    $token =
        (string) (
            $consulta['token']
            ?? ''
        );
}


/* ============================================================
   MONTA URL ABSOLUTA

   Precisamos de URL absoluta porque o celular vai ler o QR Code.
============================================================ */

$host =
    (string) (
        $_SERVER['HTTP_HOST']
        ?? ''
    );


$httpsAtivo =
    !empty(
        $_SERVER['HTTPS']
    ) &&
    strtolower(
        (string) $_SERVER['HTTPS']
    ) !== 'off';


$protocolo =
    $httpsAtivo
        ? 'https'
        : 'http';


$urlVotacao =
    $protocolo .
    '://' .
    $host .
    urlProjeto(
        'turma.php?t=' .
        rawurlencode(
            $token
        )
    );


/* ============================================================
   SALVA CONSULTA ATUAL NA SESSÃO DO PROFESSOR
============================================================ */

$_SESSION['partida']['consulta_turma'] = [

    'id' =>
        $consultaId,

    'token' =>
        $token,

    'pergunta_id' =>
        $perguntaId,

];


/* ============================================================
   RESPOSTA
============================================================ */

responderTurmaCriar(
    true,
    [
        'consulta_id' =>
            $consultaId,

        'token' =>
            $token,

        'pergunta_id' =>
            $perguntaId,

        'url' =>
            $urlVotacao,

        'votos' => [
            'A' => 0,
            'B' => 0,
            'C' => 0,
            'D' => 0,
        ],

        'total_respostas' =>
            0,
    ]
);