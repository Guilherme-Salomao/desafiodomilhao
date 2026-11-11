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


$token = trim(
    (string) (
        $_GET['t']
        ?? $_POST['t']
        ?? ''
    )
);


if ($token === '') {

    responderJson(
        [
            'sucesso' => false,
            'mensagem' => 'Token não informado.'
        ],
        400
    );
}


try {

    $pdo = conectarBanco();


    $consulta =
        $pdo->prepare(
            '
            SELECT
                id,
                token,
                ativa,
                respondida_em
            FROM consultas_tecnico
            WHERE token = :token
            LIMIT 1
            '
        );

    $consulta->execute(
        [
            ':token' => $token
        ]
    );

    $dados =
        $consulta->fetch(
            PDO::FETCH_ASSOC
        );


    if (!$dados) {

        responderJson(
            [
                'sucesso' => false,
                'mensagem' => 'Consulta não encontrada.'
            ],
            404
        );
    }


    $resposta =
        $pdo->prepare(
            '
            SELECT alternativa
            FROM consulta_tecnico_respostas
            WHERE consulta_id = :consulta_id
            LIMIT 1
            '
        );

    $resposta->execute(
        [
            ':consulta_id' =>
                (int) $dados['id']
        ]
    );

    $alternativa =
        $resposta->fetchColumn();


    responderJson(
        [
            'sucesso' => true,

            'consulta_id' =>
                (int) $dados['id'],

            'token' =>
                (string) $dados['token'],

            'ativa' =>
                (int) $dados['ativa'] === 1,

            'respondida' =>
                $alternativa !== false,

            'alternativa' =>
                $alternativa !== false
                    ? (string) $alternativa
                    : null
        ]
    );


} catch (Throwable $erro) {

    responderJson(
        [
            'sucesso' => false,
            'mensagem' => 'Não foi possível consultar a resposta do técnico.'
        ],
        500
    );
}