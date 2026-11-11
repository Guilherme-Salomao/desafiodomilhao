<?php

declare(strict_types=1);

session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

require_once __DIR__ . '/../includes/conexao.php';
require_once __DIR__ . '/../includes/url.php';


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


$partida = $_SESSION['partida'] ?? null;

if (!is_array($partida)) {
    responderJson(
        [
            'sucesso' => false,
            'mensagem' => 'Nenhuma partida ativa.'
        ],
        400
    );
}


$perguntaId = (int) (
    $partida['pergunta_atual_id']
    ?? 0
);

if ($perguntaId <= 0) {
    responderJson(
        [
            'sucesso' => false,
            'mensagem' => 'Pergunta atual não encontrada.'
        ],
        400
    );
}


$partidaToken = trim(
    (string) (
        $partida['partida_token']
        ?? $partida['token']
        ?? ''
    )
);


if ($partidaToken === '') {

    try {

        $partidaToken = bin2hex(
            random_bytes(24)
        );

    } catch (Throwable $erro) {

        responderJson(
            [
                'sucesso' => false,
                'mensagem' => 'Não foi possível gerar o identificador da partida.'
            ],
            500
        );
    }

    $_SESSION['partida']['partida_token'] =
        $partidaToken;
}


try {

    $pdo = conectarBanco();


    /*
     * Verifica se já existe uma consulta ativa
     * para esta mesma pergunta.
     */

    $consulta = $pdo->prepare(
        '
        SELECT
            id,
            token,
            pergunta_id,
            ativa
        FROM consultas_tecnico
        WHERE partida_token = :partida_token
          AND pergunta_id = :pergunta_id
          AND ativa = 1
        ORDER BY id DESC
        LIMIT 1
        '
    );

    $consulta->execute(
        [
            ':partida_token' => $partidaToken,
            ':pergunta_id' => $perguntaId
        ]
    );

    $existente = $consulta->fetch(
        PDO::FETCH_ASSOC
    );


    if ($existente) {

        $token = (string) $existente['token'];

        $protocolo =
            (!empty($_SERVER['HTTPS']) &&
             $_SERVER['HTTPS'] !== 'off')
                ? 'https'
                : 'http';

        $host =
            (string) ($_SERVER['HTTP_HOST'] ?? '');

        $url =
            $protocolo .
            '://' .
            $host .
            urlProjeto(
                'tecnico.php?t=' .
                rawurlencode($token)
            );

        responderJson(
            [
                'sucesso' => true,
                'consulta_id' => (int) $existente['id'],
                'token' => $token,
                'pergunta_id' => $perguntaId,
                'url' => $url,
                'respondida' => false,
                'alternativa' => null
            ]
        );
    }


    /*
     * Cria uma nova consulta.
     */

    $token = bin2hex(
        random_bytes(24)
    );


    $inserir = $pdo->prepare(
        '
        INSERT INTO consultas_tecnico (
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
        '
    );

    $inserir->execute(
        [
            ':token' => $token,
            ':partida_token' => $partidaToken,
            ':pergunta_id' => $perguntaId
        ]
    );


    $consultaId =
        (int) $pdo->lastInsertId();


    $protocolo =
        (!empty($_SERVER['HTTPS']) &&
         $_SERVER['HTTPS'] !== 'off')
            ? 'https'
            : 'http';

    $host =
        (string) ($_SERVER['HTTP_HOST'] ?? '');

    $url =
        $protocolo .
        '://' .
        $host .
        urlProjeto(
            'tecnico.php?t=' .
            rawurlencode($token)
        );


    $_SESSION['partida']['consulta_tecnico'] = [
        'id' => $consultaId,
        'token' => $token,
        'pergunta_id' => $perguntaId
    ];


    responderJson(
        [
            'sucesso' => true,
            'consulta_id' => $consultaId,
            'token' => $token,
            'pergunta_id' => $perguntaId,
            'url' => $url,
            'respondida' => false,
            'alternativa' => null
        ]
    );


} catch (Throwable $erro) {

    responderJson(
        [
            'sucesso' => false,
            'mensagem' => 'Não foi possível criar a consulta ao técnico.'
        ],
        500
    );
}