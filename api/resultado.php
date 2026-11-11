<?php

declare(strict_types=1);

/* ============================================================
   SESSÃO
============================================================ */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}


/* ============================================================
   RESPOSTA JSON
============================================================ */

header('Content-Type: application/json; charset=utf-8');


/* ============================================================
   FUNÇÃO DE RESPOSTA
============================================================ */

function responderResultado(
    bool $sucesso,
    string $mensagem,
    array $dados = [],
    int $status = 200
): never {

    http_response_code($status);

    echo json_encode(
        array_merge(
            [
                'sucesso' => $sucesso,
                'mensagem' => $mensagem,
            ],
            $dados
        ),
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );

    exit;
}


/* ============================================================
   SOMENTE POST
============================================================ */

if (
    ($_SERVER['REQUEST_METHOD'] ?? '')
    !== 'POST'
) {

    responderResultado(
        false,
        'Método não permitido.',
        [],
        405
    );
}


/* ============================================================
   CONEXÃO
============================================================ */

require_once dirname(__DIR__) .
    '/includes/conexao.php';

try {

    $pdo = conectarBanco();

} catch (Throwable $erro) {

    error_log(
        '[Resultado - conexão] ' .
        $erro->getMessage()
    );

    responderResultado(
        false,
        'Não foi possível conectar ao banco de dados.',
        [],
        500
    );
}


/* ============================================================
   PARTIDA
============================================================ */

$partida =
    $_SESSION['partida']
    ?? null;


if (
    !is_array($partida) ||
    empty($partida['area_id'])
) {

    responderResultado(
        false,
        'Partida não encontrada.',
        [],
        400
    );
}


/* ============================================================
   RECEBE JSON
============================================================ */

$conteudo =
    file_get_contents(
        'php://input'
    );


$dados =
    json_decode(
        $conteudo ?: '',
        true
    );


if (!is_array($dados)) {

    responderResultado(
        false,
        'Dados da partida inválidos.',
        [],
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
            ?? ''
        )
    );


/*
 * Compatibilidade com partidas iniciadas
 * antes da criação do ranking.
 */

if ($partidaToken === '') {

    try {

        $partidaToken =
            bin2hex(
                random_bytes(32)
            );

    } catch (Throwable $erro) {

        $partidaToken =
            hash(
                'sha256',
                session_id() .
                '|' .
                microtime(true) .
                '|' .
                random_int(
                    1,
                    PHP_INT_MAX
                )
            );
    }

    $_SESSION['partida']['partida_token'] =
        $partidaToken;
}


/* ============================================================
   ÁREA
============================================================ */

$areaId =
    (int) (
        $partida['area_id']
        ?? 0
    );


if ($areaId <= 0) {

    responderResultado(
        false,
        'Área da partida inválida.',
        [],
        400
    );
}


/* ============================================================
   USUÁRIO
============================================================ */

$usuarioId =
    null;


if (
    isset($_SESSION['usuario']) &&
    is_array($_SESSION['usuario']) &&
    !empty($_SESSION['usuario']['id'])
) {

    $id =
        (int) $_SESSION['usuario']['id'];

    if ($id > 0) {

        $usuarioId =
            $id;
    }
}


/* ============================================================
   NOME DO JOGADOR
============================================================ */

$nomeJogador =
    trim(
        (string) (
            $partida['nome_jogador']
            ?? ''
        )
    );


/*
 * Caso alguma versão anterior tenha
 * armazenado o nome com outra chave.
 */

if ($nomeJogador === '') {

    $nomeJogador =
        trim(
            (string) (
                $partida['jogador']
                ?? ''
            )
        );
}


if ($nomeJogador === '') {

    $nomeJogador =
        'Jogador';
}


$nomeJogador =
    mb_substr(
        $nomeJogador,
        0,
        120
    );


/* ============================================================
   MODO
============================================================ */

$modo =
    trim(
        (string) (
            $partida['modo']
            ?? 'individual'
        )
    );


$modosPermitidos = [
    'individual',
    'competicao',
    'professor',
];


if (
    !in_array(
        $modo,
        $modosPermitidos,
        true
    )
) {

    $modo =
        'individual';
}


/* ============================================================
   DIFICULDADE
============================================================ */

$dificuldade =
    trim(
        (string) (
            $partida['dificuldade']
            ?? 'mista'
        )
    );


if ($dificuldade === '') {

    $dificuldade =
        'mista';
}


$dificuldade =
    mb_substr(
        $dificuldade,
        0,
        20
    );


/* ============================================================
   QUANTIDADE DE PERGUNTAS
============================================================ */

$quantidadePerguntas =
    (int) (
        $partida['quantidade_perguntas']
        ?? 10
    );


if ($quantidadePerguntas < 1) {

    $quantidadePerguntas =
        10;
}


/* ============================================================
   RESULTADO
============================================================ */

$resultado =
    trim(
        (string) (
            $dados['resultado']
            ?? 'game_over'
        )
    );


if (
    !in_array(
        $resultado,
        [
            'game_over',
            'vencedor',
        ],
        true
    )
) {

    $resultado =
        'game_over';
}


/* ============================================================
   PERGUNTA FINAL
============================================================ */

$perguntaFinal =
    max(
        0,
        (int) (
            $dados['pergunta_final']
            ?? $partida['pergunta_atual']
            ?? 0
        )
    );


/* ============================================================
   PERGUNTAS RESPONDIDAS
============================================================ */

$perguntasRespondidas =
    max(
        0,
        (int) (
            $dados['perguntas_respondidas']
            ?? $perguntaFinal
        )
    );


/* ============================================================
   ACERTOS
============================================================ */

$acertos =
    max(
        0,
        (int) (
            $dados['acertos']
            ?? 0
        )
    );


/* ============================================================
   ERROS
============================================================ */

$erros =
    max(
        0,
        (int) (
            $dados['erros']
            ?? 0
        )
    );


/* ============================================================
   TEMPO
============================================================ */

$tempoTotalMs =
    max(
        0,
        (int) (
            $dados['tempo_total_ms']
            ?? 0
        )
    );


/* ============================================================
   AJUDAS
============================================================ */

$ajudaCinquenta =
    !empty(
        $dados['ajuda_cinquenta']
    )
        ? 1
        : 0;


$ajudaTecnico =
    !empty(
        $dados['ajuda_tecnico']
    )
        ? 1
        : 0;


$ajudaTurma =
    !empty(
        $dados['ajuda_turma']
    )
        ? 1
        : 0;


$ajudaTrocar =
    !empty(
        $dados['ajuda_trocar']
    )
        ? 1
        : 0;


$ajudasUsadas =
    $ajudaCinquenta +
    $ajudaTecnico +
    $ajudaTurma +
    $ajudaTrocar;


/* ============================================================
   PRÊMIO
============================================================ */

$premio =
    (float) (
        $dados['premio']
        ?? 0
    );


if ($premio < 0) {

    $premio =
        0;
}


/* ============================================================
   CONCLUÍDA
============================================================ */

$concluida =
    $resultado === 'vencedor'
        ? 1
        : 0;


/* ============================================================
   VERIFICA SE JÁ FOI GRAVADA
============================================================ */

try {

    $sqlVerifica = '
        SELECT
            id
        FROM resultados
        WHERE partida_token = :partida_token
        LIMIT 1
    ';


    $stmtVerifica =
        $pdo->prepare(
            $sqlVerifica
        );


    $stmtVerifica->execute(
        [
            ':partida_token' =>
                $partidaToken,
        ]
    );


    $resultadoExistente =
        $stmtVerifica->fetch(
            PDO::FETCH_ASSOC
        );


    if ($resultadoExistente) {

        $_SESSION['partida']['resultado_gravado'] =
            true;


        responderResultado(
            true,
            'Resultado já registrado.',
            [
                'id' =>
                    (int) $resultadoExistente['id'],

                'duplicado' =>
                    true,
            ]
        );
    }

} catch (Throwable $erro) {

    error_log(
        '[Resultado - verificação] ' .
        $erro->getMessage()
    );


    responderResultado(
        false,
        'Não foi possível verificar o resultado da partida.',
        [],
        500
    );
}


/* ============================================================
   GRAVA RESULTADO
============================================================ */

$sqlInsert = '
    INSERT INTO resultados
    (
        partida_token,
        area_id,
        usuario_id,
        nome_jogador,
        modo,
        dificuldade,
        quantidade_perguntas,
        pergunta_final,
        perguntas_respondidas,
        acertos,
        erros,
        tempo_total_ms,
        ajudas_usadas,
        ajuda_cinquenta,
        ajuda_tecnico,
        ajuda_turma,
        ajuda_trocar,
        premio,
        resultado,
        concluida
    )
    VALUES
    (
        :partida_token,
        :area_id,
        :usuario_id,
        :nome_jogador,
        :modo,
        :dificuldade,
        :quantidade_perguntas,
        :pergunta_final,
        :perguntas_respondidas,
        :acertos,
        :erros,
        :tempo_total_ms,
        :ajudas_usadas,
        :ajuda_cinquenta,
        :ajuda_tecnico,
        :ajuda_turma,
        :ajuda_trocar,
        :premio,
        :resultado,
        :concluida
    )
';


try {

    $stmtInsert =
        $pdo->prepare(
            $sqlInsert
        );


    $stmtInsert->execute(
        [
            ':partida_token' =>
                $partidaToken,

            ':area_id' =>
                $areaId,

            ':usuario_id' =>
                $usuarioId,

            ':nome_jogador' =>
                $nomeJogador,

            ':modo' =>
                $modo,

            ':dificuldade' =>
                $dificuldade,

            ':quantidade_perguntas' =>
                $quantidadePerguntas,

            ':pergunta_final' =>
                $perguntaFinal,

            ':perguntas_respondidas' =>
                $perguntasRespondidas,

            ':acertos' =>
                $acertos,

            ':erros' =>
                $erros,

            ':tempo_total_ms' =>
                $tempoTotalMs,

            ':ajudas_usadas' =>
                $ajudasUsadas,

            ':ajuda_cinquenta' =>
                $ajudaCinquenta,

            ':ajuda_tecnico' =>
                $ajudaTecnico,

            ':ajuda_turma' =>
                $ajudaTurma,

            ':ajuda_trocar' =>
                $ajudaTrocar,

            ':premio' =>
                $premio,

            ':resultado' =>
                $resultado,

            ':concluida' =>
                $concluida,
        ]
    );


    $idResultado =
        (int) $pdo->lastInsertId();


    $_SESSION['partida']['resultado_gravado'] =
        true;


    $_SESSION['partida']['resultado'] =
        $resultado;


    $_SESSION['partida']['premio'] =
        $premio;


    $_SESSION['partida']['tempo_total_ms'] =
        $tempoTotalMs;


    $_SESSION['partida']['ajudas_usadas'] =
        $ajudasUsadas;


    responderResultado(
        true,
        'Resultado registrado com sucesso.',
        [
            'id' =>
                $idResultado,

            'duplicado' =>
                false,

            'nome_jogador' =>
                $nomeJogador,

            'premio' =>
                $premio,

            'ajudas_usadas' =>
                $ajudasUsadas,

            'tempo_total_ms' =>
                $tempoTotalMs,

            'resultado' =>
                $resultado,
        ]
    );


} catch (PDOException $erro) {

    /*
     * 23000 = violação de chave única.
     *
     * Se o navegador tentou novamente o mesmo
     * resultado, consideramos a operação concluída.
     */

    if (
        $erro->getCode() ===
        '23000'
    ) {

        $_SESSION['partida']['resultado_gravado'] =
            true;


        responderResultado(
            true,
            'Resultado já registrado.',
            [
                'duplicado' =>
                    true,
            ]
        );
    }


    error_log(
        '[Resultado - INSERT] ' .
        $erro->getMessage()
    );


    responderResultado(
        false,
        'Não foi possível registrar o resultado.',
        [],
        500
    );


} catch (Throwable $erro) {

    error_log(
        '[Resultado - erro] ' .
        $erro->getMessage()
    );


    responderResultado(
        false,
        'Não foi possível registrar o resultado.',
        [],
        500
    );
}