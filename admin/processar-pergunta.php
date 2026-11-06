<?php

declare (strict_types = 1);

require_once __DIR__ .
    '/../includes/autenticacao.php';

require_once __DIR__ .
    '/../includes/conexao.php';

require_once __DIR__ .
    '/../includes/repositories/UsuarioRepository.php';

require_once __DIR__ .
    '/../includes/repositories/PerguntaRepository.php';

if (
    ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST'
) {

    header(
        'Location: /github/desafiodomilhao/public/admin/pergunta-cadastrar.php',
        true,
        302
    );

    exit;
}

$usuario =
$_SESSION['usuario'] ?? null;

if (
    ! is_array($usuario) ||
    empty($usuario['id'])
) {

    $_SESSION['erro_login'] =
        'Faça login para acessar o Banco de Questões.';

    header(
        'Location: /github/desafiodomilhao/public/login.php',
        true,
        303
    );

    exit;
}

$usuarioId =
(int) $usuario['id'];

$perguntaIdEdicao =
filter_var(
    $_POST['pergunta_id'] ?? null,
    FILTER_VALIDATE_INT
);

$urlRetornoPergunta =
$perguntaIdEdicao !== false &&
$perguntaIdEdicao !== null &&
$perguntaIdEdicao > 0
    ? '/github/desafiodomilhao/public/admin/pergunta-cadastrar.php?id=' . (int) $perguntaIdEdicao
    : '/github/desafiodomilhao/public/admin/pergunta-cadastrar.php';

function retornarPerguntaComErro(
    string $mensagem
): never {

    global $urlRetornoPergunta;

    $_SESSION['erro_pergunta'] =
        $mensagem;

    unset(
        $_SESSION['sucesso_pergunta']
    );

    header(
        'Location: ' . $urlRetornoPergunta,
        true,
        303
    );

    exit;
}

$areaId =
    filter_var(
    $_POST['area_id'] ?? null,
    FILTER_VALIDATE_INT
);

$dificuldade =
    trim(
    (string) (
        $_POST['dificuldade'] ?? ''
    )
);

$pergunta =
    trim(
    (string) (
        $_POST['enunciado'] ?? ''
    )
);

$alternativaA =
    trim(
    (string) (
        $_POST['alternativa_a'] ?? ''
    )
);

$alternativaB =
    trim(
    (string) (
        $_POST['alternativa_b'] ?? ''
    )
);

$alternativaC =
    trim(
    (string) (
        $_POST['alternativa_c'] ?? ''
    )
);

$alternativaD =
    trim(
    (string) (
        $_POST['alternativa_d'] ?? ''
    )
);

$respostaCorreta =
    strtoupper(
    trim(
        (string) (
            $_POST['resposta_correta'] ?? ''
        )
    )
);

$explicacao =
    trim(
    (string) (
        $_POST['explicacao'] ?? ''
    )
);

$ativo =
isset($_POST['ativo'])
    ? 1
    : 0;

if (
    $areaId === false ||
    $areaId === null ||
    $areaId <= 0
) {

    retornarPerguntaComErro(
        'Selecione uma área válida.'
    );
}

$dificuldadesPermitidas = [
    'facil',
    'medio',
    'dificil',
];

if (
    ! in_array(
        $dificuldade,
        $dificuldadesPermitidas,
        true
    )
) {

    retornarPerguntaComErro(
        'Selecione uma dificuldade válida.'
    );
}

$tamanhoPergunta =
    mb_strlen(
    $pergunta
);

if ($tamanhoPergunta < 10) {

    retornarPerguntaComErro(
        'A pergunta deve possuir pelo menos 10 caracteres.'
    );
}

if ($tamanhoPergunta > 1000) {

    retornarPerguntaComErro(
        'A pergunta não pode ultrapassar 1000 caracteres.'
    );
}

$alternativas = [

    'A' => $alternativaA,

    'B' => $alternativaB,

    'C' => $alternativaC,

    'D' => $alternativaD,

];

foreach (
    $alternativas as $letra => $texto
) {

    if (mb_strlen($texto) < 1) {

        retornarPerguntaComErro(
            "Informe o conteúdo da alternativa {$letra}."
        );
    }

    if (mb_strlen($texto) > 255) {

        retornarPerguntaComErro(
            "A alternativa {$letra} não pode ultrapassar 255 caracteres."
        );
    }
}

$alternativasNormalizadas =
    array_map(
    static function (
        string $texto
    ): string {

        return mb_strtolower(
            trim($texto)
        );

    },
    array_values(
        $alternativas
    )
);

if (
    count(
        array_unique(
            $alternativasNormalizadas
        )
    ) !== 4
) {

    retornarPerguntaComErro(
        'As alternativas devem possuir textos diferentes.'
    );
}

$respostasPermitidas = [
    'A',
    'B',
    'C',
    'D',
];

if (
    ! in_array(
        $respostaCorreta,
        $respostasPermitidas,
        true
    )
) {

    retornarPerguntaComErro(
        'Selecione qual alternativa é a resposta correta.'
    );
}

$tamanhoExplicacao =
    mb_strlen(
    $explicacao
);

if ($tamanhoExplicacao < 5) {

    retornarPerguntaComErro(
        'Informe uma explicação para a resposta.'
    );
}

if ($tamanhoExplicacao > 1500) {

    retornarPerguntaComErro(
        'A explicação não pode ultrapassar 1500 caracteres.'
    );
}

try {

    $usuarioRepository =
    new UsuarioRepository();

    if (
        ! $usuarioRepository
        ->estaAtivo(
            $usuarioId
        )
    ) {

        unset(
            $_SESSION['usuario']
        );

        $_SESSION['erro_login'] =
            'Seu acesso foi desativado pelo administrador.';

        header(
            'Location: /github/desafiodomilhao/public/login.php',
            true,
            303
        );

        exit;
    }

    if (
        ! $usuarioRepository
        ->usuarioPossuiArea(
            $usuarioId,
            (int) $areaId
        )
    ) {

        retornarPerguntaComErro(
            'Você não possui autorização para cadastrar questões nesta área.'
        );
    }

    if (
        $perguntaIdEdicao !== false &&
        $perguntaIdEdicao !== null &&
        $perguntaIdEdicao > 0
    ) {
        $perguntaRepository = new PerguntaRepository();

        if (
            $perguntaRepository->buscarPorIdAutorizado(
                (int) $perguntaIdEdicao,
                $usuarioId
            ) === null
        ) {
            retornarPerguntaComErro(
                'Você não possui acesso a esta questão.'
            );
        }

        $perguntaRepository->atualizar(
            (int) $perguntaIdEdicao,
            (int) $areaId,
            $pergunta,
            $dificuldade,
            $explicacao,
            $ativo,
            $alternativas,
            $respostaCorreta
        );

        $_SESSION['sucesso_pergunta'] =
            'A questão foi atualizada com sucesso.';

        header('Location: /github/desafiodomilhao/public/admin/perguntas.php', true, 303);
        exit;
    }

    $pdo =
        conectarBanco();

    $pdo->beginTransaction();

    $sqlPergunta = "
        INSERT INTO perguntas (
            area_id,
            usuario_id,
            pergunta,
            dificuldade,
            explicacao,
            imagem,
            ativo
        )
        VALUES (
            :area_id,
            :usuario_id,
            :pergunta,
            :dificuldade,
            :explicacao,
            NULL,
            :ativo
        )
    ";

    $stmtPergunta =
    $pdo->prepare(
        $sqlPergunta
    );

    $stmtPergunta->bindValue(
        ':area_id',
        (int) $areaId,
        PDO::PARAM_INT
    );

    $stmtPergunta->bindValue(
        ':usuario_id',
        $usuarioId,
        PDO::PARAM_INT
    );

    $stmtPergunta->bindValue(
        ':pergunta',
        $pergunta,
        PDO::PARAM_STR
    );

    $stmtPergunta->bindValue(
        ':dificuldade',
        $dificuldade,
        PDO::PARAM_STR
    );

    $stmtPergunta->bindValue(
        ':explicacao',
        $explicacao,
        PDO::PARAM_STR
    );

    $stmtPergunta->bindValue(
        ':ativo',
        $ativo,
        PDO::PARAM_INT
    );

    $stmtPergunta->execute();

    $perguntaId =
    (int) $pdo->lastInsertId();

    if ($perguntaId <= 0) {

        throw new RuntimeException(
            'Não foi possível identificar a pergunta cadastrada.'
        );
    }

    $sqlAlternativa = "
        INSERT INTO alternativas (
            pergunta_id,
            letra,
            texto,
            correta,
            ordem
        )
        VALUES (
            :pergunta_id,
            :letra,
            :texto,
            :correta,
            :ordem
        )
    ";

    $stmtAlternativa =
    $pdo->prepare(
        $sqlAlternativa
    );

    $ordens = [

        'A' => 1,

        'B' => 2,

        'C' => 3,

        'D' => 4,

    ];

    foreach (
        $alternativas as $letra => $texto
    ) {

        $correta =
        $letra === $respostaCorreta
            ? 1
            : 0;

        $stmtAlternativa->bindValue(
            ':pergunta_id',
            $perguntaId,
            PDO::PARAM_INT
        );

        $stmtAlternativa->bindValue(
            ':letra',
            $letra,
            PDO::PARAM_STR
        );

        $stmtAlternativa->bindValue(
            ':texto',
            $texto,
            PDO::PARAM_STR
        );

        $stmtAlternativa->bindValue(
            ':correta',
            $correta,
            PDO::PARAM_INT
        );

        $stmtAlternativa->bindValue(
            ':ordem',
            $ordens[$letra],
            PDO::PARAM_INT
        );

        $stmtAlternativa->execute();
    }

    $pdo->commit();

    unset(
        $_SESSION['erro_pergunta']
    );

    $_SESSION['sucesso_pergunta'] =
        'A questão foi cadastrada com sucesso.';

    header(
        'Location: /github/desafiodomilhao/public/admin/perguntas.php',
        true,
        303
    );

    exit;

} catch (Throwable $erro) {

    if (
        isset($pdo) &&
        $pdo instanceof PDO &&
        $pdo->inTransaction()
    ) {

        $pdo->rollBack();
    }

    error_log(
        '[Desafio do Milhão - Cadastro de pergunta] ' .
        $erro->getMessage()
    );

    retornarPerguntaComErro(
        'Não foi possível cadastrar a questão. Verifique os dados e tente novamente.'
    );
}
