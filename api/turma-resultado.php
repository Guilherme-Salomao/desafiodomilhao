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

function responderResultadoTurma(
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
   TOKEN
============================================================ */

$token =
    trim(
        (string) (
            $_GET['t']
            ?? $_POST['t']
            ?? ''
        )
    );


if ($token === '') {

    responderResultadoTurma(
        false,
        [
            'mensagem' =>
                'Consulta não identificada.',
        ],
        400
    );
}


/* ============================================================
   CONEXÃO
============================================================ */

try {

    $pdo =
        conectarBanco();

} catch (Throwable $erro) {

    responderResultadoTurma(
        false,
        [
            'mensagem' =>
                'Não foi possível conectar ao banco de dados.',
        ],
        500
    );
}


/* ============================================================
   LOCALIZA CONSULTA
============================================================ */

try {

    $stmtConsulta =
        $pdo->prepare(
            '
                SELECT
                    id,
                    token,
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

        responderResultadoTurma(
            false,
            [
                'mensagem' =>
                    'Esta consulta não foi encontrada.',
            ],
            404
        );
    }


    $consultaId =
        (int) (
            $consulta['id']
            ?? 0
        );


    /* ========================================================
       CONTAGEM DOS VOTOS
    ======================================================== */

    $stmtVotos =
        $pdo->prepare(
            '
                SELECT
                    alternativa,
                    COUNT(*) AS quantidade
                FROM consulta_turma_votos
                WHERE consulta_id = :consulta_id
                GROUP BY alternativa
            '
        );


    $stmtVotos->execute(
        [
            ':consulta_id' =>
                $consultaId,
        ]
    );


    $linhas =
        $stmtVotos->fetchAll(
            PDO::FETCH_ASSOC
        );


    /* ========================================================
       INICIALIZA CONTAGEM
    ======================================================== */

    $quantidades = [
        'A' => 0,
        'B' => 0,
        'C' => 0,
        'D' => 0,
    ];


    foreach ($linhas as $linha) {

        $letra =
            strtoupper(
                trim(
                    (string) (
                        $linha['alternativa']
                        ?? ''
                    )
                )
            );


        if (
            array_key_exists(
                $letra,
                $quantidades
            )
        ) {

            $quantidades[$letra] =
                (int) (
                    $linha['quantidade']
                    ?? 0
                );
        }
    }


    /* ========================================================
       TOTAL
    ======================================================== */

    $total =
        array_sum(
            $quantidades
        );


    /* ========================================================
       PERCENTUAIS
    ======================================================== */

    $percentuais = [
        'A' => 0,
        'B' => 0,
        'C' => 0,
        'D' => 0,
    ];


    if ($total > 0) {

        foreach (
            $quantidades as $letra => $quantidade
        ) {

            $percentuais[$letra] =
                (int) round(
                    (
                        $quantidade /
                        $total
                    ) * 100
                );
        }
    }


    /* ========================================================
       AJUSTE DE ARREDONDAMENTO

       Garante que a soma final fique em 100 quando houver votos.
    ======================================================== */

    if ($total > 0) {

        $soma =
            array_sum(
                $percentuais
            );


        if ($soma !== 100) {

            $diferenca =
                100 - $soma;


            $maiorLetra =
                'A';


            foreach (
                $quantidades as $letra => $quantidade
            ) {

                if (
                    $quantidade >
                    $quantidades[$maiorLetra]
                ) {

                    $maiorLetra =
                        $letra;
                }
            }


            $percentuais[$maiorLetra] +=
                $diferenca;
        }
    }


    /* ========================================================
       RESPOSTA
    ======================================================== */

    responderResultadoTurma(
        true,
        [
            'consulta_id' =>
                $consultaId,

            'token' =>
                $token,

            'ativa' =>
                (int) (
                    $consulta['ativa']
                    ?? 0
                ) === 1,

            'total_respostas' =>
                $total,

            'quantidades' =>
                $quantidades,

            'votos' =>
                $percentuais,
        ]
    );


} catch (Throwable $erro) {

    responderResultadoTurma(
        false,
        [
            'mensagem' =>
                'Não foi possível consultar o resultado da turma.',
        ],
        500
    );
}