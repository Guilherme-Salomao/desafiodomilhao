<?php

declare(strict_types=1);

/* ============================================================
   SESSÃO
============================================================ */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}


/* ============================================================
   SOMENTE POST
============================================================ */

if (
    ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST'
) {

    header(
        'Location: /github/desafiodomilhao/public/',
        true,
        302
    );

    exit;
}


/* ============================================================
   ÁREA
============================================================ */

$area =
    $_SESSION['area'] ?? null;

if (
    ! is_array($area) ||
    empty($area['id']) ||
    empty($area['slug']) ||
    empty($area['nome'])
) {

    header(
        'Location: /github/desafiodomilhao/public/areas.php?retorno=jogo',
        true,
        302
    );

    exit;
}


/* ============================================================
   MODO
============================================================ */

$modo =
    trim(
        (string) (
            $_POST['modo']
            ?? 'individual'
        )
    );

$modosPermitidos = [
    'individual',
    'competicao',
];

if (
    ! in_array(
        $modo,
        $modosPermitidos,
        true
    )
) {

    $modo =
        'individual';
}


/* ============================================================
   QUANTIDADE DE PERGUNTAS
============================================================ */

$quantidadePerguntas =
    filter_input(
        INPUT_POST,
        'quantidade_perguntas',
        FILTER_VALIDATE_INT
    );

$quantidadesPermitidas = [
    10,
    15,
    20,
];

if (
    $quantidadePerguntas === false ||
    $quantidadePerguntas === null ||
    ! in_array(
        $quantidadePerguntas,
        $quantidadesPermitidas,
        true
    )
) {

    $quantidadePerguntas =
        10;
}


/* ============================================================
   DIFICULDADE
============================================================ */

$dificuldade =
    trim(
        (string) (
            $_POST['dificuldade']
            ?? 'mista'
        )
    );

$dificuldadesPermitidas = [
    'facil',
    'mista',
    'dificil',
];

if (
    ! in_array(
        $dificuldade,
        $dificuldadesPermitidas,
        true
    )
) {

    $dificuldade =
        'mista';
}


/* ============================================================
   TEMPO AUTOMÁTICO
============================================================ */

/*
 * IMPORTANTE:
 *
 * O tempo NÃO vem do formulário.
 *
 * Ele é definido exclusivamente pelo servidor.
 *
 * Isso impede que o jogador altere o HTML ou POST
 * para obter um tempo diferente.
 */

$temposPorDificuldade = [

    'facil' =>
        20,

    'mista' =>
        30,

    'dificil' =>
        45,

];

$tempoPergunta =
    $temposPorDificuldade[
        $dificuldade
    ];


/* ============================================================
   ESTRATÉGIA DE PERGUNTAS
============================================================ */

/*
 * Define como o jogo deverá buscar as perguntas.
 *
 * Fácil:
 * somente perguntas classificadas como "facil".
 *
 * Difícil:
 * somente perguntas classificadas como "dificil".
 *
 * Mista:
 * utiliza "facil", "medio" e "dificil".
 */

$estrategiaPerguntas =
    match ($dificuldade) {

        'facil' =>
            'somente_facil',

        'dificil' =>
            'somente_dificil',

        default =>
            'progressiva'

    };


/* ============================================================
   TOKEN ÚNICO DA PARTIDA
============================================================ */

/*
 * Cada partida precisa possuir um identificador único.
 *
 * Esse token será salvo na tabela resultados.
 *
 * A coluna partida_token possui UNIQUE no banco,
 * impedindo que F5 ou múltiplas requisições gravem
 * o mesmo resultado várias vezes.
 */

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
            microtime(true) .
            random_int(
                100000,
                999999
            )
        );
}


/* ============================================================
   CONFIGURAÇÃO DA PARTIDA
============================================================ */

$configuracao = [

    /* ========================================================
       IDENTIFICAÇÃO DA PARTIDA
    ======================================================== */

    'partida_token' =>
        $partidaToken,


    /* ========================================================
       ÁREA
    ======================================================== */

    'area_id' =>
        (int) $area['id'],

    'area_nome' =>
        (string) $area['nome'],

    'area_slug' =>
        (string) $area['slug'],


    /* ========================================================
       MODO
    ======================================================== */

    'modo' =>
        $modo,


    /* ========================================================
       PERGUNTAS
    ======================================================== */

    'quantidade_perguntas' =>
        (int) $quantidadePerguntas,

    'pergunta_atual' =>
        1,

    /*
     * IDs das perguntas já utilizadas
     * durante esta partida.
     */

    'perguntas_usadas' =>
        [],

    /*
     * Quantidade de questões efetivamente
     * respondidas.
     */

    'perguntas_respondidas' =>
        0,


    /* ========================================================
       DIFICULDADE
    ======================================================== */

    'dificuldade' =>
        $dificuldade,


    /* ========================================================
       ESTRATÉGIA
    ======================================================== */

    'estrategia_perguntas' =>
        $estrategiaPerguntas,


    /* ========================================================
       TEMPO
    ======================================================== */

    'tempo_pergunta' =>
        $tempoPergunta,

    /*
     * Tempo total utilizado na partida.
     *
     * O JavaScript calculará em milissegundos.
     */

    'tempo_total_ms' =>
        0,


    /* ========================================================
       RESULTADO / RANKING
    ======================================================== */

    'acertos' =>
        0,

    'erros' =>
        0,

    'pontuacao' =>
        0,

    /*
     * Prêmio acumulado em valor numérico.
     *
     * Exemplo:
     *
     * 0
     * 1000
     * 2000
     * 200000
     * 1000000
     */

    'premio' =>
        0,

    /*
     * Total de tipos de ajuda utilizados.
     */

    'ajudas_usadas' =>
        0,

    /*
     * Impede registro duplicado do ranking.
     */

    'resultado_gravado' =>
        false,

    /*
     * Resultado final.
     *
     * null enquanto a partida está acontecendo.
     *
     * Depois poderá ser:
     *
     * game_over
     * vencedor
     */

    'resultado' =>
        null,


    /* ========================================================
       AJUDAS
    ======================================================== */

    'ajudas' => [

        'cinquenta' =>
            false,

        'tecnico' =>
            false,

        'turma' =>
            false,

        'trocar' =>
            false,

    ],


    /* ========================================================
       DATA / HORÁRIO
    ======================================================== */

    'criada_em' =>
        time(),

    /*
     * Momento inicial com maior precisão.
     *
     * Útil para auditoria e futuras estatísticas.
     */

    'iniciada_em_ms' =>
        (int) round(
            microtime(true) * 1000
        ),

];


/* ============================================================
   JOGADOR INDIVIDUAL
============================================================ */

if (
    $modo ===
    'individual'
) {

    $nomeJogador =
        trim(
            (string) (
                $_POST['nome_jogador']
                ?? ''
            )
        );

    $nomeJogador =
        mb_substr(
            $nomeJogador,
            0,
            60
        );

    if (
        mb_strlen(
            $nomeJogador
        ) < 2
    ) {

        header(
            'Location: /github/desafiodomilhao/public/configurar-partida.php?modo=individual',
            true,
            303
        );

        exit;
    }

    $configuracao[
        'nome_jogador'
    ] =
        $nomeJogador;

}


/* ============================================================
   COMPETIÇÃO
============================================================ */

if (
    $modo ===
    'competicao'
) {

    $equipe1 =
        trim(
            (string) (
                $_POST['equipe_1']
                ?? ''
            )
        );

    $equipe2 =
        trim(
            (string) (
                $_POST['equipe_2']
                ?? ''
            )
        );


    $equipe1 =
        mb_substr(
            $equipe1,
            0,
            60
        );


    $equipe2 =
        mb_substr(
            $equipe2,
            0,
            60
        );


    /* ========================================================
       VALIDA NOMES
    ======================================================== */

    if (
        mb_strlen(
            $equipe1
        ) < 2 ||
        mb_strlen(
            $equipe2
        ) < 2
    ) {

        header(
            'Location: /github/desafiodomilhao/public/configurar-partida.php?modo=competicao',
            true,
            303
        );

        exit;
    }


    /* ========================================================
       NOMES NÃO PODEM SER IGUAIS
    ======================================================== */

    if (
        mb_strtolower(
            $equipe1
        ) ===
        mb_strtolower(
            $equipe2
        )
    ) {

        header(
            'Location: /github/desafiodomilhao/public/configurar-partida.php?modo=competicao',
            true,
            303
        );

        exit;
    }


    /* ========================================================
       EQUIPES
    ======================================================== */

    $configuracao[
        'equipes'
    ] = [

        [

            'nome' =>
                $equipe1,

            'pontos' =>
                0,

            'acertos' =>
                0,

        ],

        [

            'nome' =>
                $equipe2,

            'pontos' =>
                0,

            'acertos' =>
                0,

        ],

    ];


    /* ========================================================
       PRIMEIRA EQUIPE
    ======================================================== */

    $configuracao[
        'equipe_atual'
    ] =
        0;

}


/* ============================================================
   DISTRIBUIÇÃO DO MODO MISTO
============================================================ */

if (
    $dificuldade ===
    'mista'
) {

    switch (
        $quantidadePerguntas
    ) {

        /* ====================================================
           10 PERGUNTAS

           3 fáceis
           4 médias
           3 difíceis
        ==================================================== */

        case 10:

            $configuracao[
                'distribuicao'
            ] = [

                'facil' =>
                    3,

                'medio' =>
                    4,

                'dificil' =>
                    3,

            ];

            break;


        /* ====================================================
           15 PERGUNTAS

           5 fáceis
           5 médias
           5 difíceis
        ==================================================== */

        case 15:

            $configuracao[
                'distribuicao'
            ] = [

                'facil' =>
                    5,

                'medio' =>
                    5,

                'dificil' =>
                    5,

            ];

            break;


        /* ====================================================
           20 PERGUNTAS

           7 fáceis
           7 médias
           6 difíceis
        ==================================================== */

        case 20:

            $configuracao[
                'distribuicao'
            ] = [

                'facil' =>
                    7,

                'medio' =>
                    7,

                'dificil' =>
                    6,

            ];

            break;

    }

}


/* ============================================================
   MODO FÁCIL
============================================================ */

if (
    $dificuldade ===
    'facil'
) {

    $configuracao[
        'distribuicao'
    ] = [

        'facil' =>
            $quantidadePerguntas,

        'medio' =>
            0,

        'dificil' =>
            0,

    ];

}


/* ============================================================
   MODO DIFÍCIL
============================================================ */

if (
    $dificuldade ===
    'dificil'
) {

    $configuracao[
        'distribuicao'
    ] = [

        'facil' =>
            0,

        'medio' =>
            0,

        'dificil' =>
            $quantidadePerguntas,

    ];

}


/* ============================================================
   SALVA HISTÓRICO DA PARTIDA ANTERIOR
============================================================ */

/*
 * Mantém a lógica atual do projeto:
 *
 * antes de iniciar uma nova partida, as perguntas
 * utilizadas anteriormente são armazenadas para evitar
 * repetições excessivas entre partidas.
 */

$partidaAnterior =
    $_SESSION['partida']
    ?? null;

if (
    is_array(
        $partidaAnterior
    )
) {

    $perguntasAnteriores =
        array_map(

            'intval',

            is_array(
                $partidaAnterior[
                    'perguntas_usadas'
                ] ?? null
            )

                ? $partidaAnterior[
                    'perguntas_usadas'
                ]

                : []

        );


    $perguntaAtualAnterior =
        (int) (
            $partidaAnterior[
                'pergunta_atual_id'
            ]
            ?? 0
        );


    if (
        $perguntaAtualAnterior > 0
    ) {

        $perguntasAnteriores[] =
            $perguntaAtualAnterior;
    }


    $perguntasAnteriores =
        array_values(

            array_unique(

                array_filter(

                    $perguntasAnteriores,

                    static fn (
                        int $id
                    ): bool =>
                        $id > 0

                )

            )

        );


    if (
        $perguntasAnteriores !== []
    ) {

        $historicoPartidas =
            $_SESSION[
                'historico_partidas_perguntas'
            ]
            ?? [];


        $historicoPartidas =
            is_array(
                $historicoPartidas
            )

                ? $historicoPartidas

                : [];


        $historicoPartidas[] =
            $perguntasAnteriores;


        /*
         * Mantém somente as últimas
         * dez partidas desta sessão.
         */

        $_SESSION[
            'historico_partidas_perguntas'
        ] =
            array_slice(
                $historicoPartidas,
                -10
            );
    }
}


/* ============================================================
   SALVA NOVA PARTIDA
============================================================ */

$_SESSION[
    'partida'
] =
    $configuracao;


/* ============================================================
   REGENERA ID DA SESSÃO
============================================================ */

session_regenerate_id(
    true
);


/* ============================================================
   REDIRECIONA PARA O JOGO
============================================================ */

header(
    'Location: /github/desafiodomilhao/public/jogo.php',
    true,
    303
);

exit;