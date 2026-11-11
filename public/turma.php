<?php

declare(strict_types=1);

/* ============================================================
   DEPENDÊNCIAS
============================================================ */

require_once dirname(__DIR__) . '/includes/conexao.php';
require_once dirname(__DIR__) . '/includes/url.php';


/* ============================================================
   ESCAPE
============================================================ */

function escaparTurma(string $valor): string
{
    return htmlspecialchars(
        $valor,
        ENT_QUOTES,
        'UTF-8'
    );
}


/* ============================================================
   TOKEN
============================================================ */

$token =
    trim(
        (string) (
            $_GET['t']
            ?? ''
        )
    );


$consulta = null;
$alternativas = [];
$erro = '';


/* ============================================================
   CONSULTA
============================================================ */

if ($token === '') {

    $erro =
        'Esta votação não é válida.';

} else {

    try {

        $pdo =
            conectarBanco();


        $stmt =
            $pdo->prepare(
                '
                    SELECT
                        ct.id,
                        ct.token,
                        ct.pergunta_id,
                        ct.ativa,
                        p.pergunta
                    FROM consultas_turma ct

                    INNER JOIN perguntas p
                        ON p.id = ct.pergunta_id

                    WHERE ct.token = :token

                    LIMIT 1
                '
            );


        $stmt->execute(
            [
                ':token' =>
                    $token,
            ]
        );


        $consulta =
            $stmt->fetch(
                PDO::FETCH_ASSOC
            );


        if (!$consulta) {

            $erro =
                'Esta votação não foi encontrada.';

        } elseif (
            (int) (
                $consulta['ativa']
                ?? 0
            ) !== 1
        ) {

            $erro =
                'Esta votação já foi encerrada.';

        } else {

            /* =================================================
               ALTERNATIVAS

               Não selecionamos "correta".
               O celular do aluno não recebe essa informação.
            ================================================= */

            $stmtAlternativas =
                $pdo->prepare(
                    '
                        SELECT
                            letra,
                            texto
                        FROM alternativas
                        WHERE pergunta_id = :pergunta_id
                        ORDER BY letra ASC
                    '
                );


            $stmtAlternativas->execute(
                [
                    ':pergunta_id' =>
                        (int) $consulta['pergunta_id'],
                ]
            );


            $alternativas =
                $stmtAlternativas->fetchAll(
                    PDO::FETCH_ASSOC
                );


            if (empty($alternativas)) {

                $erro =
                    'As alternativas desta pergunta não foram encontradas.';
            }
        }

    } catch (Throwable $erroBanco) {

        $erro =
            'Não foi possível carregar esta votação.';
    }
}


/* ============================================================
   URLS
============================================================ */

$urlVotar =
    urlProjeto(
        'api/turma-votar.php'
    );


$cssTurma =
    urlProjeto(
        'assets/css/turma.css'
    );


$logo =
    urlProjeto(
        'assets/img/logo/favicon.png'
    );

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        Consultar a Turma - Desafio do Milhão
    </title>

    <link
        rel="stylesheet"
        href="<?php echo escaparTurma($cssTurma); ?>"
    >

</head>

<body>


<main class="pagina-votacao-turma">


    <section class="painel-votacao-turma">


        <img
            src="<?php echo escaparTurma($logo); ?>"
            alt="Desafio do Milhão"
            class="logo-votacao-turma"
        >


        <?php if ($erro !== ''): ?>


            <div class="estado-votacao erro">


                <strong>
                    VOTAÇÃO INDISPONÍVEL
                </strong>


                <p>
                    <?php echo escaparTurma($erro); ?>
                </p>


            </div>


        <?php else: ?>


            <span class="rotulo-votacao">
                CONSULTAR A TURMA
            </span>


            <h1>
                Qual alternativa você escolheria?
            </h1>


            <p class="pergunta-votacao">

                <?php
                echo escaparTurma(
                    (string) (
                        $consulta['pergunta']
                        ?? ''
                    )
                );
                ?>

            </p>


            <form
                id="formVotacaoTurma"
                action="<?php echo escaparTurma($urlVotar); ?>"
                method="POST"
            >


                <input
                    type="hidden"
                    name="token"
                    value="<?php echo escaparTurma($token); ?>"
                >


                <div class="alternativas-votacao">


                    <?php foreach ($alternativas as $alternativa): ?>


                        <?php

                        $letra =
                            strtoupper(
                                trim(
                                    (string) (
                                        $alternativa['letra']
                                        ?? ''
                                    )
                                )
                            );

                        $texto =
                            (string) (
                                $alternativa['texto']
                                ?? ''
                            );

                        ?>


                        <label class="alternativa-votacao">


                            <input
                                type="radio"
                                name="alternativa"
                                value="<?php echo escaparTurma($letra); ?>"
                                required
                            >


                            <span class="letra-votacao">

                                <?php
                                echo escaparTurma(
                                    $letra
                                );
                                ?>

                            </span>


                            <span class="texto-votacao">

                                <?php
                                echo escaparTurma(
                                    $texto
                                );
                                ?>

                            </span>


                        </label>


                    <?php endforeach; ?>


                </div>


                <button
                    type="submit"
                    class="botao-enviar-voto"
                >

                    ENVIAR RESPOSTA

                </button>


            </form>


            <div
                id="mensagemVotacaoTurma"
                class="mensagem-votacao"
                hidden
            ></div>


        <?php endif; ?>


    </section>


</main>


<?php if ($erro === ''): ?>

<script>

const formulario =
    document.getElementById(
        'formVotacaoTurma'
    );

const mensagem =
    document.getElementById(
        'mensagemVotacaoTurma'
    );


formulario.addEventListener(
    'submit',
    async function (evento) {

        evento.preventDefault();


        const botao =
            formulario.querySelector(
                'button[type="submit"]'
            );


        botao.disabled =
            true;


        try {

            const resposta =
                await fetch(
                    formulario.action,
                    {
                        method:
                            'POST',

                        body:
                            new FormData(
                                formulario
                            )
                    }
                );


            const retorno =
                await resposta.json();


            mensagem.hidden =
                false;


            if (!retorno.sucesso) {

                mensagem.className =
                    'mensagem-votacao erro';

                mensagem.textContent =
                    retorno.mensagem ||
                    'Não foi possível registrar seu voto.';

                botao.disabled =
                    false;

                return;
            }


            mensagem.className =
                'mensagem-votacao sucesso';

            mensagem.innerHTML =
                '<strong>RESPOSTA REGISTRADA!</strong>' +
                '<span>Obrigado por participar da ajuda à turma.</span>';


            formulario.hidden =
                true;


        } catch (erro) {

            mensagem.hidden =
                false;

            mensagem.className =
                'mensagem-votacao erro';

            mensagem.textContent =
                'Não foi possível enviar sua resposta.';

            botao.disabled =
                false;
        }
    }
);

</script>

<?php endif; ?>


</body>
</html>