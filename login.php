<?php

    declare (strict_types = 1);

    /* =========================================================
   SESSÃO
========================================================= */

    if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
    }

    /* =========================================================
   MENSAGENS
========================================================= */

    $erroLogin =
    $_SESSION['erro_login'] ?? null;

    $avisoLogin =
    $_SESSION['aviso_login'] ?? null;

    /* =========================================================
   LOGIN ANTERIOR
========================================================= */

    $loginAnterior =
    (string) (
    $_SESSION['login_anterior'] ?? ''
    );

    /* =========================================================
   FLASH MESSAGES
========================================================= */

    unset(
    $_SESSION['erro_login'],
    $_SESSION['aviso_login']
    );

    /* =========================================================
   CONFIGURAÇÕES DA PÁGINA
========================================================= */

    $siteSubtitulo =
    'Entrar';

    $paginaTitulo =
    'Entrar - Desafio do Milhão';

    $paginaDescricao =
    'Acesse o Banco de Questões do Desafio do Milhão.';

    $paginaUrl =
    'http://localhost/desafiodomilhao/login.php';

    $paginaTipo =
    'website';

    $paginaCss =
    '/desafiodomilhao/assets/css/login.css';

    /* =========================================================
   HEADER
========================================================= */

    require_once __DIR__ .
    '/includes/header.php';

?>


<!-- =========================================================
     CONTEÚDO PRINCIPAL
========================================================= -->

<main class="pagina-login">


  <!-- =====================================================
         CABEÇALHO
    ====================================================== -->

  <section class="cabecalho-login">


    <img src="/desafiodomilhao/assets/img/logo/favicon.png" alt="Desafio do Milhão" class="logo-login">


    <span class="rotulo-login">

      <i class="fa-solid fa-user-shield"></i>

      ACESSO RESTRITO

    </span>


    <h1>
      ENTRAR
    </h1>


    <p>

      Acesse sua conta para gerenciar
      o Banco de Questões.

    </p>


  </section>


  <!-- =====================================================
         PAINEL
    ====================================================== -->

  <section class="painel-login">


    <!-- =================================================
             ERRO
        ================================================== -->

    <?php if ($erroLogin): ?>


    <div class="mensagem-login erro">


      <div class="icone-mensagem-login">

        <i class="fa-solid fa-circle-exclamation"></i>

      </div>


      <div class="conteudo-mensagem-login">

        <strong>
          NÃO FOI POSSÍVEL ENTRAR
        </strong>

        <span>

          <?php echo escapar(
                  (string) $erroLogin
          ); ?>

        </span>

      </div>


      <button type="button" class="fechar-mensagem-login" data-fechar-mensagem aria-label="Fechar mensagem">

        <i class="fa-solid fa-xmark"></i>

      </button>


    </div>


    <?php endif; ?>


    <!-- =================================================
             AGUARDANDO AUTORIZAÇÃO
        ================================================== -->

    <?php if ($avisoLogin): ?>


    <div class="mensagem-login aviso">


      <div class="icone-mensagem-login">

        <i class="fa-solid fa-hourglass-half"></i>

      </div>


      <div class="conteudo-mensagem-login">

        <strong>
          ACESSO AGUARDANDO LIBERAÇÃO
        </strong>

        <span>

          <?php echo escapar(
                  (string) $avisoLogin
          ); ?>

        </span>

      </div>


      <button type="button" class="fechar-mensagem-login" data-fechar-mensagem aria-label="Fechar mensagem">

        <i class="fa-solid fa-xmark"></i>

      </button>


    </div>


    <?php endif; ?>


    <!-- =================================================
             IDENTIFICAÇÃO
        ================================================== -->

    <div class="titulo-painel-login">


      <div class="icone-painel-login">

        <i class="fa-solid fa-right-to-bracket"></i>

      </div>


      <div>

        <strong>
          ACESSO AO BANCO DE QUESTÕES
        </strong>

        <span>
          Informe seu login e sua senha.
        </span>

      </div>


    </div>


    <!-- =================================================
             FORMULÁRIO
        ================================================== -->

    <form id="formLogin" action="/desafiodomilhao/processar-login.php" method="POST" autocomplete="on">


      <!-- =================================================
                 LOGIN
            ================================================== -->

      <div class="grupo-campo-login">


        <label for="loginUsuario">

          <i class="fa-solid fa-user"></i>

          Login

        </label>


        <div class="campo-login-com-icone">


          <i class="fa-solid fa-at"></i>


          <input type="text" id="loginUsuario" name="login" maxlength="80" value="<?php echo escapar(
                                                                                      $loginAnterior
                                                                                  ); ?>" placeholder="Digite seu login" autocomplete="username" autocapitalize="none"
            spellcheck="false" required autofocus>


        </div>


      </div>


      <!-- =================================================
                 SENHA
            ================================================== -->

      <div class="grupo-campo-login">


        <label for="senhaUsuario">

          <i class="fa-solid fa-lock"></i>

          Senha

        </label>


        <div class="campo-senha-login">


          <i class="fa-solid fa-key icone-campo-senha"></i>


          <input type="password" id="senhaUsuario" name="senha" maxlength="72" placeholder="Digite sua senha"
            autocomplete="current-password" required>


          <button type="button" class="botao-mostrar-senha-login" id="btnMostrarSenha" aria-label="Mostrar senha"
            title="Mostrar senha">

            <i class="fa-solid fa-eye"></i>

          </button>


        </div>


      </div>


      <!-- =================================================
                 INFORMAÇÃO
            ================================================== -->

      <div class="informacao-login">


        <i class="fa-solid fa-circle-info"></i>


        <span>

          Somente usuários autorizados pelo administrador
          podem acessar o Banco de Questões.

        </span>


      </div>


      <!-- =================================================
                 AÇÕES
            ================================================== -->

      <div class="acoes-login">


        <a href="/desafiodomilhao/" class="botao-voltar-login">

          <i class="fa-solid fa-arrow-left"></i>

          VOLTAR

        </a>


        <button type="submit" class="botao-entrar-login" id="btnEntrarLogin">

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


      <a href="/desafiodomilhao/cadastro.php">

        <i class="fa-solid fa-user-plus"></i>

        SOLICITAR CADASTRO

      </a>


    </div>


  </section>


</main>


<!-- =========================================================
     FOOTER
========================================================= -->

<?php

    require_once __DIR__ .
    '/includes/footer.php';

?>


<!-- =========================================================
     JAVASCRIPT
========================================================= -->

<script src="/desafiodomilhao/assets/js/login.js"></script>


</body>

</html>
