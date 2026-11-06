<?php

    declare (strict_types = 1);

    /* =========================================================
   SESSÃO
========================================================= */

    if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
    }

    /* =========================================================
   MENSAGENS / DADOS ANTERIORES
========================================================= */

    $erroCadastro =
    $_SESSION['erro_cadastro'] ?? null;

    $cadastroAnterior =
    $_SESSION['cadastro_anterior'] ?? [];

    /*
 * A mensagem de erro é utilizada apenas uma vez.
 */

    unset(
    $_SESSION['erro_cadastro']
    );

    /* =========================================================
   DADOS ANTERIORES
========================================================= */

    $nomeAnterior =
    (string) (
    $cadastroAnterior['nome'] ?? ''
    );

    $loginAnterior =
    (string) (
    $cadastroAnterior['login'] ?? ''
    );

    $areasAnteriores =
    $cadastroAnterior['areas'] ?? [];

    if (! is_array($areasAnteriores)) {
    $areasAnteriores = [];
    }

    /*
 * Normaliza os IDs como string para facilitar
 * a comparação com os IDs vindos do banco.
 */

    $areasAnteriores = array_map(
    'strval',
    $areasAnteriores
    );

    /* =========================================================
   REPOSITORY
========================================================= */

    require_once __DIR__ .
    '/includes/repositories/AreaRepository.php';

    /* =========================================================
   CARREGA ÁREAS
========================================================= */

    $areas = [];

    $erroBanco = false;

    try {

    $areaRepository =
    new AreaRepository();

    $areas =
    $areaRepository->listarAtivas();

    } catch (Throwable $erro) {

    error_log(
        '[Desafio do Milhão - Cadastro] ' .
        $erro->getMessage()
    );

    $erroBanco = true;

    }

    /* =========================================================
   CONFIGURAÇÕES DA PÁGINA
========================================================= */

    $siteSubtitulo =
    'Solicitar cadastro';

    $paginaTitulo =
    'Solicitar Cadastro - Desafio do Milhão';

    $paginaDescricao =
    'Solicite acesso ao Banco de Questões do Desafio do Milhão.';

    $paginaUrl =
    'https://www.guilhermeshorane.com.br/github/desafiodomilhao/public/cadastro.php';

    $paginaTipo =
    'website';

    $paginaCss =
    '/github/desafiodomilhao/public/assets/css/cadastro.css';

    /* =========================================================
   HEADER
========================================================= */

    require_once __DIR__ .
    '/includes/header.php';

?>


<!-- =========================================================
     CONTEÚDO PRINCIPAL
========================================================= -->

<main class="pagina-cadastro">


  <!-- =====================================================
         CABEÇALHO
    ====================================================== -->

  <section class="cabecalho-cadastro">


    <!-- LOGO -->

    <img src="/github/desafiodomilhao/public/assets/img/logo/favicon.png" alt="Desafio do Milhão" class="logo-cadastro">


    <!-- IDENTIFICAÇÃO -->

    <span class="rotulo-cadastro">

      <i class="fa-solid fa-user-plus"></i>

      ACESSO AO BANCO DE QUESTÕES

    </span>


    <!-- TÍTULO -->

    <h1>
      SOLICITAR CADASTRO
    </h1>


    <!-- DESCRIÇÃO -->

    <p>

      Crie sua conta e informe as áreas de conhecimento
      em que deseja cadastrar perguntas.

    </p>


  </section>


  <!-- =====================================================
         PAINEL
    ====================================================== -->

  <section class="painel-cadastro">


    <!-- =================================================
             ERRO DO CADASTRO
        ================================================== -->

    <?php if ($erroCadastro): ?>


    <div class="mensagem-cadastro erro">


      <i class="fa-solid fa-triangle-exclamation"></i>


      <div>

        <strong>
          Não foi possível concluir o cadastro.
        </strong>

        <span>

          <?php echo escapar(
                  (string) $erroCadastro
          ); ?>

        </span>

      </div>


    </div>


    <?php endif; ?>


    <!-- =================================================
             FORMULÁRIO
        ================================================== -->

    <form id="formCadastroUsuario" action="/github/desafiodomilhao/public/processar-cadastro.php" method="POST" autocomplete="off">


      <!-- =================================================
                 DADOS DE ACESSO
            ================================================== -->

      <section class="secao-cadastro">


        <!-- CABEÇALHO -->

        <div class="titulo-secao-cadastro">


          <div class="numero-etapa-cadastro">
            1
          </div>


          <div>

            <strong>
              Dados de acesso
            </strong>

            <span>
              Informe seus dados para criar a solicitação.
            </span>

          </div>


        </div>


        <!-- =================================================
                     NOME
                ================================================== -->

        <div class="grupo-campo-cadastro">


          <label for="nomeUsuario">

            <i class="fa-solid fa-user"></i>

            Nome completo

          </label>


          <input type="text" id="nomeUsuario" name="nome" maxlength="120" value="<?php echo escapar(
                                                                                     $nomeAnterior
                                                                                 ); ?>" placeholder="Digite seu nome completo" autocomplete="name" required>


          <small>

            Nome que será utilizado para identificar
            sua conta.

          </small>


        </div>


        <!-- =================================================
                     LOGIN
                ================================================== -->

        <div class="grupo-campo-cadastro">


          <label for="loginUsuario">

            <i class="fa-solid fa-at"></i>

            Login

          </label>


          <input type="text" id="loginUsuario" name="login" maxlength="80" value="<?php echo escapar(
                                                                                      $loginAnterior
                                                                                  ); ?>" placeholder="Escolha um login" autocomplete="username" autocapitalize="none"
            spellcheck="false" required>


          <small>

            Utilize letras, números, ponto,
            hífen ou underline.

          </small>


        </div>


        <!-- =================================================
                     SENHAS
                ================================================== -->

        <div class="linha-senhas">


          <!-- =============================================
                         SENHA
                    ============================================== -->

          <div class="grupo-campo-cadastro">


            <label for="senhaUsuario">

              <i class="fa-solid fa-lock"></i>

              Senha

            </label>


            <div class="campo-senha">


              <input type="password" id="senhaUsuario" name="senha" maxlength="72" placeholder="Crie uma senha"
                autocomplete="new-password" required>


              <button type="button" class="botao-mostrar-senha" data-alvo="senhaUsuario" aria-label="Mostrar senha"
                title="Mostrar senha">

                <i class="fa-solid fa-eye"></i>

              </button>


            </div>


          </div>


          <!-- =============================================
                         CONFIRMAR SENHA
                    ============================================== -->

          <div class="grupo-campo-cadastro">


            <label for="confirmarSenhaUsuario">

              <i class="fa-solid fa-shield-halved"></i>

              Confirmar senha

            </label>


            <div class="campo-senha">


              <input type="password" id="confirmarSenhaUsuario" name="confirmar_senha" maxlength="72"
                placeholder="Digite novamente" autocomplete="new-password" required>


              <button type="button" class="botao-mostrar-senha" data-alvo="confirmarSenhaUsuario"
                aria-label="Mostrar senha" title="Mostrar senha">

                <i class="fa-solid fa-eye"></i>

              </button>


            </div>


          </div>


        </div>


        <!-- =================================================
                     REQUISITOS DA SENHA
                ================================================== -->

        <div class="requisitos-senha">


          <!-- TAMANHO -->

          <span id="requisitoTamanho" class="requisito-senha">

            <i class="fa-solid fa-circle"></i>

            Pelo menos 8 caracteres

          </span>


          <!-- MAIÚSCULA -->

          <span id="requisitoMaiuscula" class="requisito-senha">

            <i class="fa-solid fa-circle"></i>

            Uma letra maiúscula

          </span>


          <!-- MINÚSCULA -->

          <span id="requisitoMinuscula" class="requisito-senha">

            <i class="fa-solid fa-circle"></i>

            Uma letra minúscula

          </span>


          <!-- NÚMERO -->

          <span id="requisitoNumero" class="requisito-senha">

            <i class="fa-solid fa-circle"></i>

            Um número

          </span>


        </div>


      </section>


      <!-- =================================================
                 ÁREAS DE ATUAÇÃO
            ================================================== -->

      <section class="secao-cadastro">


        <!-- CABEÇALHO -->

        <div class="titulo-secao-cadastro">


          <div class="numero-etapa-cadastro">
            2
          </div>


          <div>

            <strong>
              Áreas de atuação
            </strong>

            <span>

              Você pode solicitar acesso a uma
              ou várias áreas.

            </span>

          </div>


        </div>


        <!-- =================================================
                     ERRO AO CARREGAR ÁREAS
                ================================================== -->

        <?php if ($erroBanco): ?>


        <div class="mensagem-cadastro erro">


          <i class="fa-solid fa-triangle-exclamation"></i>


          <div>

            <strong>
              Não foi possível carregar as áreas.
            </strong>

            <span>
              Tente novamente em alguns instantes.
            </span>

          </div>


        </div>


        <!-- =================================================
                     SEM ÁREAS
                ================================================== -->

        <?php elseif (empty($areas)): ?>


        <div class="mensagem-cadastro erro">


          <i class="fa-solid fa-circle-info"></i>


          <div>

            <strong>
              Nenhuma área disponível.
            </strong>

            <span>

              Ainda não existem áreas ativas
              cadastradas.

            </span>

          </div>


        </div>


        <!-- =================================================
                     LISTA DE ÁREAS
                ================================================== -->

        <?php else: ?>


        <div class="grade-areas-cadastro">


          <?php foreach ($areas as $area): ?>


          <?php

              $areaId =
              (string) $area['id'];

              $areaMarcada =
                  in_array(
                  $areaId,
                  $areasAnteriores,
                  true
              );

          ?>


          <label class="opcao-area-cadastro">


            <!-- CHECKBOX -->

            <input type="checkbox" name="areas[]" value="<?php echo (int) $area['id']; ?>" <?php echo $areaMarcada
      ? 'checked'
      : ''; ?>>


            <!-- CARD -->

            <span class="conteudo-area-cadastro">


              <!-- ÍCONE -->

              <span class="icone-area-cadastro">

                <i class="<?php echo escapar(
                              (string) $area['icone']
                          ); ?>" aria-hidden="true"></i>

              </span>


              <!-- CONTEÚDO -->

              <span class="dados-area-cadastro">


                <strong>

                  <?php echo escapar(
                          (string) $area['nome']
                  ); ?>

                </strong>


                <small>

                  <?php echo escapar(
                          (string) (
                              $area['descricao'] ?? ''
                          )
                  ); ?>

                </small>


              </span>


              <!-- CHECK -->

              <span class="check-area-cadastro">

                <i class="fa-solid fa-check" aria-hidden="true"></i>

              </span>


            </span>


          </label>


          <?php endforeach; ?>


        </div>


        <!-- =================================================
                         CONTADOR
                    ================================================== -->

        <div class="contador-areas-selecionadas">


          <i class="fa-solid fa-layer-group"></i>


          <span id="textoAreasSelecionadas">

            Nenhuma área selecionada

          </span>


        </div>


        <?php endif; ?>


      </section>


      <!-- =================================================
                 AVISO DE APROVAÇÃO
            ================================================== -->

      <div class="aviso-aprovacao">


        <div class="icone-aviso-aprovacao">

          <i class="fa-solid fa-hourglass-half"></i>

        </div>


        <div>

          <strong>
            CADASTRO SUJEITO À APROVAÇÃO
          </strong>

          <span>

            Sua conta será criada como inativa.
            O acesso ao Banco de Questões somente
            será liberado após aprovação do
            administrador.

          </span>

        </div>


      </div>


      <!-- =================================================
                 AÇÕES
            ================================================== -->

      <div class="acoes-cadastro">


        <!-- VOLTAR -->

        <a href="/github/desafiodomilhao/public/configuracoes.php" class="botao-voltar-cadastro">

          <i class="fa-solid fa-arrow-left"></i>

          VOLTAR

        </a>


        <!-- SOLICITAR -->

        <button type="submit" id="btnSolicitarCadastro" class="botao-solicitar-cadastro" <?php echo(
                                                                                                 $erroBanco ||
                                                                                             empty($areas)
                                                                                         ) ? 'disabled' : ''; ?>>

          <i class="fa-solid fa-paper-plane"></i>

          SOLICITAR ACESSO

        </button>


      </div>


    </form>


    <!-- =================================================
             JÁ POSSUI CONTA
        ================================================== -->

    <div class="ja-possui-cadastro">


      <span>
        Já possui uma conta?
      </span>


      <a href="/github/desafiodomilhao/public/login.php">

        <i class="fa-solid fa-right-to-bracket"></i>

        ENTRAR

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

<script src="/github/desafiodomilhao/public/assets/js/cadastro.js"></script>


</body>

</html>