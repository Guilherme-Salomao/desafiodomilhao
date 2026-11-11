<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/conexao.php';
require_once dirname(__DIR__) . '/includes/url.php';


$token = trim(
    (string) ($_GET['t'] ?? '')
);

$consulta = null;
$alternativas = [];
$erro = '';


if ($token === '') {

    $erro =
        'Consulta inválida.';

} else {

    try {

        $pdo = conectarBanco();


        $buscar =
            $pdo->prepare(
                '
                SELECT
                    ct.id,
                    ct.token,
                    ct.pergunta_id,
                    ct.ativa,
                    p.pergunta
                FROM consultas_tecnico ct
                INNER JOIN perguntas p
                    ON p.id = ct.pergunta_id
                WHERE ct.token = :token
                LIMIT 1
                '
            );

        $buscar->execute(
            [
                ':token' => $token
            ]
        );

        $consulta =
            $buscar->fetch(
                PDO::FETCH_ASSOC
            );


        if (!$consulta) {

            $erro =
                'Consulta não encontrada.';

        } elseif ((int) $consulta['ativa'] !== 1) {

            $erro =
                'Esta consulta já foi respondida.';

        } else {

            $buscarAlternativas =
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

            $buscarAlternativas->execute(
                [
                    ':pergunta_id' =>
                        (int) $consulta['pergunta_id']
                ]
            );

            $alternativas =
                $buscarAlternativas->fetchAll(
                    PDO::FETCH_ASSOC
                );
        }


    } catch (Throwable $e) {

        $erro =
            'Não foi possível carregar a consulta.';
    }
}


function e(string $texto): string
{
    return htmlspecialchars(
        $texto,
        ENT_QUOTES,
        'UTF-8'
    );
}
?>
<!DOCTYPE html>

<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="theme-color"
        content="#00101e"
    >

    <title>
        Consultar o Técnico
    </title>

    <link
        rel="stylesheet"
        href="<?= e(urlProjeto('assets/css/tecnico.css')) ?>"
    >

</head>

<body>

<main class="pagina-tecnico">

    <section class="cartao-tecnico">


        <!-- =================================================
             CABEÇALHO
        ================================================== -->

        <header class="cabecalho-tecnico">

            <div class="icone-cabecalho-tecnico">

                <i class="icone-tecnico">
                    👨‍💻
                </i>

            </div>

            <div class="texto-cabecalho-tecnico">

                <strong>
                    CONSULTAR O TÉCNICO
                </strong>

                <span>
                    Apenas uma resposta será aceita
                </span>

            </div>

        </header>


        <?php if ($erro !== ''): ?>


            <!-- =================================================
                 CONSULTA ENCERRADA
            ================================================== -->

            <div class="mensagem-tecnico erro">

                <span class="icone-mensagem-tecnico">
                    ✓
                </span>

                <strong>
                    CONSULTA ENCERRADA
                </strong>

                <p>
                    <?= e($erro) ?>
                </p>

            </div>


        <?php elseif ($consulta): ?>


            <!-- =================================================
                 PERGUNTA
            ================================================== -->

            <section class="pergunta-tecnico">

                <span class="rotulo-pergunta-tecnico">
                    PERGUNTA
                </span>

                <h1>
                    <?= e(
                        (string) $consulta['pergunta']
                    ) ?>
                </h1>

            </section>


            <!-- =================================================
                 FORMULÁRIO
            ================================================== -->

            <form
                id="formTecnico"
                class="formulario-tecnico"
            >

                <input
                    type="hidden"
                    name="token"
                    value="<?= e($token) ?>"
                >


                <div class="alternativas-tecnico">

                    <?php foreach ($alternativas as $alternativa): ?>

                        <?php
                        $letra =
                            strtoupper(
                                (string) $alternativa['letra']
                            );
                        ?>

                        <label class="alternativa-tecnico">

                            <input
                                type="radio"
                                name="alternativa"
                                value="<?= e($letra) ?>"
                                required
                            >

                            <span class="letra-alternativa-tecnico">

                                <?= e($letra) ?>

                            </span>

                            <span class="texto-alternativa-tecnico">

                                <?= e(
                                    (string) $alternativa['texto']
                                ) ?>

                            </span>

                        </label>

                    <?php endforeach; ?>

                </div>


                <button
                    type="submit"
                    class="botao-responder-tecnico"
                    id="btnResponderTecnico"
                >

                    ENVIAR RESPOSTA

                </button>

            </form>


            <!-- =================================================
                 SUCESSO
            ================================================== -->

            <div
                class="mensagem-tecnico sucesso"
                id="mensagemSucessoTecnico"
                hidden
            >

                <span class="icone-mensagem-tecnico">
                    ✓
                </span>

                <strong>
                    RESPOSTA REGISTRADA!
                </strong>

                <p>
                    A consulta foi encerrada.
                </p>

                <small>
                    Você foi a única pessoa a responder.
                </small>

            </div>


            <!-- =================================================
                 ERRO
            ================================================== -->

            <div
                class="mensagem-tecnico erro"
                id="mensagemErroTecnico"
                hidden
            >

                <span class="icone-mensagem-tecnico">
                    !
                </span>

                <strong>
                    CONSULTA ENCERRADA
                </strong>

                <p id="textoErroTecnico"></p>

            </div>


        <?php endif; ?>

    </section>

</main>


<?php if ($consulta && $erro === ''): ?>

<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {

        const formulario =
            document.getElementById(
                "formTecnico"
            );

        const botao =
            document.getElementById(
                "btnResponderTecnico"
            );

        const sucesso =
            document.getElementById(
                "mensagemSucessoTecnico"
            );

        const erro =
            document.getElementById(
                "mensagemErroTecnico"
            );

        const textoErro =
            document.getElementById(
                "textoErroTecnico"
            );


        if (!formulario) {
            return;
        }


        formulario.addEventListener(
            "submit",
            async function (evento) {

                evento.preventDefault();


                const dados =
                    new FormData(
                        formulario
                    );


                const alternativa =
                    dados.get(
                        "alternativa"
                    );


                if (!alternativa) {
                    return;
                }


                botao.disabled =
                    true;

                botao.textContent =
                    "ENVIANDO...";


                try {

                    const resposta =
                        await fetch(
                            <?= json_encode(
                                urlProjeto(
                                    'api/tecnico-votar.php'
                                ),
                                JSON_UNESCAPED_SLASHES
                            ) ?>,
                            {
                                method:
                                    "POST",

                                body:
                                    dados
                            }
                        );


                    const retorno =
                        await resposta.json();


                    if (retorno.sucesso) {

                        formulario.hidden =
                            true;

                        sucesso.hidden =
                            false;

                        return;
                    }


                    formulario.hidden =
                        true;

                    erro.hidden =
                        false;


                    textoErro.textContent =
                        retorno.mensagem ||
                        "Esta consulta já foi respondida.";


                } catch (falha) {

                    botao.disabled =
                        false;

                    botao.textContent =
                        "ENVIAR RESPOSTA";


                    erro.hidden =
                        false;


                    textoErro.textContent =
                        "Não foi possível enviar a resposta.";
                }
            }
        );

    }
);

</script>

<?php endif; ?>

</body>

</html>