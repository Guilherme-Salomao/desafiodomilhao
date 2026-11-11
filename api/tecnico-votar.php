<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

require_once __DIR__ . '/../includes/conexao.php';


function responderJson(array $dados, int $status = 200): never
{
    http_response_code($status);

    echo json_encode(
        $dados,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );

    exit;
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    responderJson(
        [
            'sucesso' => false,
            'mensagem' => 'Método não permitido.'
        ],
        405
    );
}


$token = trim(
    (string) ($_POST['token'] ?? '')
);

$alternativa = strtoupper(
    trim(
        (string) ($_POST['alternativa'] ?? '')
    )
);


if ($token === '') {

    responderJson(
        [
            'sucesso' => false,
            'mensagem' => 'Consulta inválida.'
        ],
        400
    );
}


if (
    !in_array(
        $alternativa,
        ['A', 'B', 'C', 'D'],
        true
    )
) {

    responderJson(
        [
            'sucesso' => false,
            'mensagem' => 'Alternativa inválida.'
        ],
        400
    );
}


/*
 * Identificador do aparelho.
 */

$identificador =
    trim(
        (string) (
            $_COOKIE['desafio_tecnico_id']
            ?? ''
        )
    );


if ($identificador === '') {

    $identificador =
        bin2hex(
            random_bytes(24)
        );

    setcookie(
        'desafio_tecnico_id',
        $identificador,
        [
            'expires' => time() + 31536000,
            'path' => '/',
            'secure' =>
                !empty($_SERVER['HTTPS']) &&
                $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax'
        ]
    );
}


try {

    $pdo = conectarBanco();

    $pdo->beginTransaction();


    /*
     * FOR UPDATE impede duas pessoas de
     * responderem simultaneamente.
     */

    $consulta = $pdo->prepare(
        '
        SELECT
            id,
            pergunta_id,
            ativa
        FROM consultas_tecnico
        WHERE token = :token
        LIMIT 1
        FOR UPDATE
        '
    );

    $consulta->execute(
        [
            ':token' => $token
        ]
    );

    $dadosConsulta =
        $consulta->fetch(
            PDO::FETCH_ASSOC
        );


    if (!$dadosConsulta) {

        $pdo->rollBack();

        responderJson(
            [
                'sucesso' => false,
                'mensagem' => 'Consulta não encontrada.'
            ],
            404
        );
    }


    if ((int) $dadosConsulta['ativa'] !== 1) {

        $pdo->rollBack();

        responderJson(
            [
                'sucesso' => false,
                'encerrada' => true,
                'mensagem' => 'Esta consulta já foi respondida.'
            ],
            409
        );
    }


    $consultaId =
        (int) $dadosConsulta['id'];

    $perguntaId =
        (int) $dadosConsulta['pergunta_id'];


    /*
     * Confirma que a alternativa pertence
     * à pergunta atual.
     */

    $validarAlternativa =
        $pdo->prepare(
            '
            SELECT id
            FROM alternativas
            WHERE pergunta_id = :pergunta_id
              AND letra = :letra
            LIMIT 1
            '
        );

    $validarAlternativa->execute(
        [
            ':pergunta_id' => $perguntaId,
            ':letra' => $alternativa
        ]
    );


    if (
        !$validarAlternativa->fetchColumn()
    ) {

        $pdo->rollBack();

        responderJson(
            [
                'sucesso' => false,
                'mensagem' => 'Alternativa não encontrada.'
            ],
            400
        );
    }


    /*
     * Registra a PRIMEIRA resposta.
     */

    $inserir =
        $pdo->prepare(
            '
            INSERT INTO consulta_tecnico_respostas (
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

    $inserir->execute(
        [
            ':consulta_id' => $consultaId,
            ':alternativa' => $alternativa,
            ':identificador' => $identificador
        ]
    );


    /*
     * Encerra imediatamente a votação.
     */

    $encerrar =
        $pdo->prepare(
            '
            UPDATE consultas_tecnico
            SET
                ativa = 0,
                respondida_em = NOW()
            WHERE id = :id
            '
        );

    $encerrar->execute(
        [
            ':id' => $consultaId
        ]
    );


    $pdo->commit();


    responderJson(
        [
            'sucesso' => true,
            'respondida' => true,
            'alternativa' => $alternativa,
            'mensagem' => 'Resposta registrada com sucesso.'
        ]
    );


} catch (PDOException $erro) {

    if (
        isset($pdo) &&
        $pdo->inTransaction()
    ) {
        $pdo->rollBack();
    }


    /*
     * Se duas respostas entrarem exatamente
     * juntas, o UNIQUE também protege.
     */

    if (
        (string) $erro->getCode() === '23000'
    ) {

        responderJson(
            [
                'sucesso' => false,
                'encerrada' => true,
                'mensagem' => 'Esta consulta já foi respondida.'
            ],
            409
        );
    }


    responderJson(
        [
            'sucesso' => false,
            'mensagem' => 'Não foi possível registrar a resposta.'
        ],
        500
    );


} catch (Throwable $erro) {

    if (
        isset($pdo) &&
        $pdo->inTransaction()
    ) {
        $pdo->rollBack();
    }

    responderJson(
        [
            'sucesso' => false,
            'mensagem' => 'Não foi possível registrar a resposta.'
        ],
        500
    );
}