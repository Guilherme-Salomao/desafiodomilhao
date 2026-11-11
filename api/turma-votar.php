<?php

declare(strict_types=1);


/* ============================================================
   JSON
============================================================ */

header(
    'Content-Type: application/json; charset=utf-8'
);


/* ============================================================
   DEPENDÊNCIAS
============================================================ */

require_once dirname(__DIR__) .
    '/includes/conexao.php';


/* ============================================================
   RESPOSTA
============================================================ */

function responderVotoTurma(
    bool $sucesso,
    string $mensagem,
    int $status = 200
): never {

    http_response_code(
        $status
    );


    echo json_encode(
        [
            'sucesso' =>
                $sucesso,

            'mensagem' =>
                $mensagem,
        ],
        JSON_UNESCAPED_UNICODE
    );


    exit;
}


/* ============================================================
   MÉTODO
============================================================ */

if (
    ($_SERVER['REQUEST_METHOD'] ?? '')
    !== 'POST'
) {

    responderVotoTurma(
        false,
        'Método inválido.',
        405
    );
}


/* ============================================================
   DADOS
============================================================ */

$token =
    trim(
        (string) (
            $_POST['token']
            ?? ''
        )
    );


$alternativa =
    strtoupper(
        trim(
            (string) (
                $_POST['alternativa']
                ?? ''
            )
        )
    );


if ($token === '') {

    responderVotoTurma(
        false,
        'Consulta não identificada.',
        400
    );
}


if (
    !in_array(
        $alternativa,
        [
            'A',
            'B',
            'C',
            'D',
        ],
        true
    )
) {

    responderVotoTurma(
        false,
        'Alternativa inválida.',
        400
    );
}


/* ============================================================
   IDENTIFICADOR DO DISPOSITIVO
============================================================ */

$cookieNome =
    'desafio_turma_id';


$identificador =
    trim(
        (string) (
            $_COOKIE[$cookieNome]
            ?? ''
        )
    );


if ($identificador === '') {

    try {

        $identificador =
            bin2hex(
                random_bytes(24)
            );

    } catch (Throwable $erro) {

        responderVotoTurma(
            false,
            'Não foi possível identificar este dispositivo.',
            500
        );
    }


    setcookie(
        $cookieNome,
        $identificador,
        [
            'expires' =>
                time() +
                (60 * 60 * 24 * 365),

            'path' =>
                '/',

            'secure' =>
                !empty(
                    $_SERVER['HTTPS']
                ),

            'httponly' =>
                true,

            'samesite' =>
                'Lax',
        ]
    );
}


/* ============================================================
   BANCO
============================================================ */

try {

    $pdo =
        conectarBanco();


    /* ========================================================
       LOCALIZA CONSULTA
    ======================================================== */

    $stmtConsulta =
        $pdo->prepare(
            '
                SELECT
                    id,
                    pergunta_id,
                    ativa
                FROM consultas_turma
                WHERE token = :token
                LIMIT 1
            '
        );


    $stmtConsulta->execute(
        [
            ':token' =>
                $token,
        ]
    );


    $consulta =
        $stmtConsulta->fetch(
            PDO::FETCH_ASSOC
        );


    if (!$consulta) {

        responderVotoTurma(
            false,
            'Esta votação não existe.',
            404
        );
    }


    if (
        (int) (
            $consulta['ativa']
            ?? 0
        ) !== 1
    ) {

        responderVotoTurma(
            false,
            'Esta votação já foi encerrada.',
            409
        );
    }


    $consultaId =
        (int) $consulta['id'];


    /* ========================================================
       CONFIRMA SE A ALTERNATIVA EXISTE NA PERGUNTA

       Não verificamos se é correta.
    ======================================================== */

    $stmtAlternativa =
        $pdo->prepare(
            '
                SELECT id
                FROM alternativas
                WHERE
                    pergunta_id = :pergunta_id
                    AND letra = :letra
                LIMIT 1
            '
        );


    $stmtAlternativa->execute(
        [
            ':pergunta_id' =>
                (int) $consulta['pergunta_id'],

            ':letra' =>
                $alternativa,
        ]
    );


    if (!$stmtAlternativa->fetchColumn()) {

        responderVotoTurma(
            false,
            'Esta alternativa não pertence à pergunta.',
            400
        );
    }


    /* ========================================================
       GRAVA VOTO
    ======================================================== */

    $stmtVoto =
        $pdo->prepare(
            '
                INSERT INTO consulta_turma_votos (
                    consulta_id,
                    alternativa,
                    identificador
                )
                VALUES (
                    :consulta_id,
                    :alternativa,
                    :identificador
                )
            '
        );


    try {

        $stmtVoto->execute(
            [
                ':consulta_id' =>
                    $consultaId,

                ':alternativa' =>
                    $alternativa,

                ':identificador' =>
                    $identificador,
            ]
        );

    } catch (PDOException $erro) {


        /*
         * 1062 = chave duplicada.
         *
         * O mesmo dispositivo já votou
         * nesta consulta.
         */

        if (
            (string) $erro->getCode()
            === '23000'
        ) {

            responderVotoTurma(
                false,
                'Este aparelho já respondeu esta votação.',
                409
            );
        }


        throw $erro;
    }


    responderVotoTurma(
        true,
        'Resposta registrada com sucesso.'
    );


} catch (Throwable $erro) {

    responderVotoTurma(
        false,
        'Não foi possível registrar a resposta.',
        500
    );
}