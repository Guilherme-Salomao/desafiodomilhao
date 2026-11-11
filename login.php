<?php

declare(strict_types=1);

/* =========================================================
   SESSÃO
========================================================= */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}


/* =========================================================
   BASE URL
========================================================= */

$scriptName =
    str_replace(
        '\\',
        '/',
        (string) (
            $_SERVER['SCRIPT_NAME']
            ?? ''
        )
    );

$baseUrl =
    rtrim(
        dirname($scriptName),
        '/'
    );

if (
    $baseUrl === '.' ||
    $baseUrl === '/'
) {
    $baseUrl = '';
}


/* =========================================================
   DESTINO
========================================================= */

$destino =
    trim(
        (string) (
            $_GET['destino']
            ?? $_SESSION['destino_login']
            ?? 'perguntas'
        )
    );


$destinosPermitidos = [
    'perguntas',
    'areas',
];


if (
    !in_array(
        $destino,
        $destinosPermitidos,
        true
    )
) {
    $destino =
        'perguntas';
}


/* =========================================================
   GUARDA DESTINO
========================================================= */

$_SESSION['destino_login'] =
    $destino;


/* =========================================================
   AO ENTRAR PELO GERENCIAMENTO DE ÁREAS,
   REMOVE AUTORIZAÇÃO ANTERIOR.

   ISSO FAZ A SENHA SER SOLICITADA NOVAMENTE.
========================================================= */

if ($destino === 'areas') {

    unset(
        $_SESSION['areas_autorizado']
    );
}


/* =========================================================
   MENSAGENS
========================================================= */

$erroLogin =
    $_SESSION['erro_login']
    ?? null;


$avisoLogin =
    $_SESSION['aviso_login']
    ?? null;


$loginAnterior =
    $_SESSION['login_anterior']
    ?? '';


unset(
    $_SESSION['erro_login'],
    $_SESSION['aviso_login']
);


/* =========================================================
   TEXTOS DINÂMICOS
========================================================= */

if ($destino === 'areas') {

    $tituloAcesso =
        'ACESSO AO GERENCIAMENTO DE ÁREAS';

    $descricaoAcesso =
        'Informe seu login e sua senha para gerenciar as áreas do jogo.';

    $textoAviso =
        'Somente usuários autorizados podem cadastrar e editar áreas.';

} else {

    $tituloAcesso =
        'ACESSO AO BANCO DE QUESTÕES';

    $descricaoAcesso =
        'Informe seu login e sua senha.';

    $textoAviso =
        'Somente usuários autorizados pelo administrador podem acessar o Banco de Questões.';
}


/* =========================================================
   CONFIGURAÇÕES DA PÁGINA
========================================================= */

$siteSubtitulo =
    'Acesso restrito';

$paginaTitulo =
    'Entrar - Desafio do Milhão';

$paginaDescricao =
    'Acesso restrito à área administrativa.';

$paginaCss =
    $baseUrl .
    '/assets/css/login.css';


/* =========================================================
   HEADER
========================================================= */

require_once __DIR__ .
    '/includes/header.php';

?>


<main class="pagina-login">


    <!-- =====================================================
         CABEÇALHO
    ====================================================== -->

    <section class="cabecalho-login">


        <img
            src="<?php echo htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8'); ?>/assets/img/logo/favicon.png"
            alt="Desafio do Milhão"
            class="logo-login"
        >


        <span class="rotulo-login">

            <i class="fa-solid fa-user-lock"></i>

            ACESSO RESTRITO

        </span>


        <h1>
            ENTRAR
        </h1>


        <p>

            <?php if ($destino === 'areas'): ?>

                Acesse sua conta para gerenciar as áreas do jogo.

            <?php else: ?>

                Acesse sua conta para gerenciar o Banco de Questões.

            <?php endif; ?>

        </p>


    </section>


    <!-- =====================================================
         CONTAINER
    ====================================================== -->

    <section class="container-login">


        <!-- =================================================
             TÍTULO
        ================================================== -->

        <div class="titulo-painel-login">


            <div class="icone-painel-login">

                <i class="fa-solid fa-right-to-bracket"></i>

            </div>


            <div>

                <strong>

                    <?php
                    echo htmlspecialchars(
                        $tituloAcesso,
                        ENT_QUOTES,
                        'UTF-8'
                    );
                    ?>

                </strong>


                <span>

                    <?php
                    echo htmlspecialchars(
                        $descricaoAcesso,
                        ENT_QUOTES,
                        'UTF-8'
                    );
                    ?>

                </span>

            </div>


        </div>


        <!-- =================================================
             ERRO
        ================================================== -->

        <?php if ($erroLogin): ?>


            <div class="mensagem-login erro">


                <i class="fa-solid fa-circle-exclamation"></i>


                <span>

                    <?php
                    echo htmlspecialchars(
                        (string) $erroLogin,
                        ENT_QUOTES,
                        'UTF-8'
                    );
                    ?>

                </span>


            </div>


        <?php endif; ?>


        <!-- =================================================
             AVISO
        ================================================== -->

        <?php if ($avisoLogin): ?>


            <div class="mensagem-login aviso">


                <i class="fa-solid fa-circle-info"></i>


                <span>

                    <?php
                    echo htmlspecialchars(
                        (string) $avisoLogin,
                        ENT_QUOTES,
                        'UTF-8'
                    );
                    ?>

                </span>


            </div>


        <?php endif; ?>


        <!-- =================================================
             FORMULÁRIO
        ================================================== -->

        <form
            action="<?php echo htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8'); ?>/processar-login.php"
            method="POST"
            autocomplete="off"
            class="formulario-login"
        >


            <input
                type="hidden"
                name="destino"
                value="<?php
                echo htmlspecialchars(
                    $destino,
                    ENT_QUOTES,
                    'UTF-8'
                );
                ?>"
            >


            <!-- LOGIN -->

            <div class="grupo-login">


                <label for="login">

                    <i class="fa-solid fa-user"></i>

                    Login

                </label>


                <div class="campo-login">


                    <i class="fa-solid fa-at"></i>


                    <input
                        type="text"
                        name="login"
                        id="login"
                        maxlength="80"
                        value="<?php
                        echo htmlspecialchars(
                            (string) $loginAnterior,
                            ENT_QUOTES,
                            'UTF-8'
                        );
                        ?>"
                        placeholder="Digite seu login"
                        autocomplete="username"
                        required
                        autofocus
                    >


                </div>


            </div>


            <!-- SENHA -->

            <div class="grupo-login">


                <label for="senha">

                    <i class="fa-solid fa-lock"></i>

                    Senha

                </label>


                <div class="campo-login campo-senha-login">


                    <i class="fa-solid fa-key"></i>


                    <input
                        type="password"
                        name="senha"
                        id="senha"
                        placeholder="Digite sua senha"
                        autocomplete="current-password"
                        required
                    >


                    <button
                        type="button"
                        class="mostrar-senha-login"
                        id="mostrarSenhaLogin"
                        aria-label="Mostrar senha"
                    >

                        <i class="fa-solid fa-eye"></i>

                    </button>


                </div>


            </div>


            <!-- AVISO -->

            <div class="aviso-acesso-login">


                <i class="fa-solid fa-circle-info"></i>


                <span>

                    <?php
                    echo htmlspecialchars(
                        $textoAviso,
                        ENT_QUOTES,
                        'UTF-8'
                    );
                    ?>

                </span>


            </div>


            <!-- AÇÕES -->

            <div class="acoes-login">


                <a
                    href="<?php echo htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8'); ?>/"
                    class="botao-voltar-login"
                >

                    <i class="fa-solid fa-arrow-left"></i>

                    VOLTAR

                </a>


                <button
                    type="submit"
                    class="botao-entrar-login"
                >

                    <i class="fa-solid fa-right-to-bracket"></i>

                    ENTRAR

                </button>


            </div>


        </form>


        <!-- =================================================
             CADASTRO
        ================================================== -->

        <div class="rodape-login">


            <span>
                Ainda não possui acesso?
            </span>


            <a
                href="<?php echo htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8'); ?>/cadastro.php"
            >

                <i class="fa-solid fa-user-plus"></i>

                SOLICITAR CADASTRO

            </a>


        </div>


    </section>


</main>


<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {

        const botao =
            document.getElementById(
                "mostrarSenhaLogin"
            );

        const senha =
            document.getElementById(
                "senha"
            );

        if (
            !botao ||
            !senha
        ) {
            return;
        }

        botao.addEventListener(
            "click",
            function () {

                const exibindo =
                    senha.type ===
                    "text";

                senha.type =
                    exibindo
                        ? "password"
                        : "text";

                const icone =
                    botao.querySelector(
                        "i"
                    );

                if (icone) {

                    icone.className =
                        exibindo
                            ? "fa-solid fa-eye"
                            : "fa-solid fa-eye-slash";
                }
            }
        );
    }
);

</script>


<?php

require_once __DIR__ .
    '/includes/footer.php';

?>


</body>
</html>