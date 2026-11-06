<?php

    declare (strict_types = 1);

    /* =========================================================
   SESSÃO
========================================================= */

    if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
    }

    /* =========================================================
   VERIFICA ÁREA SELECIONADA
========================================================= */

    $areaSelecionada = $_SESSION['area'] ?? null;

    if (
    ! is_array($areaSelecionada) ||
    empty($areaSelecionada['id']) ||
    empty($areaSelecionada['nome']) ||
    empty($areaSelecionada['slug'])
    ) {

    header(
        'Location: /desafiodomilhao/areas.php?retorno=jogo',
        true,
        302
    );

    exit;
    }

    /* =========================================================
   MODO DE JOGO
========================================================= */

    $modosPermitidos = [
    'individual',
    'competicao',
    ];

    $modo = trim(
    (string) ($_GET['modo'] ?? 'individual')
    );

    if (! in_array($modo, $modosPermitidos, true)) {
    $modo = 'individual';
    }

    /* =========================================================
   CONFIGURAÇÕES DA PÁGINA
========================================================= */

    $siteSubtitulo =
    'Configurar partida';

    $paginaTitulo =
    'Configurar Partida - Desafio do Milhão';

    $paginaDescricao =
    'Configure sua partida no Desafio do Milhão.';

    $paginaUrl =
    'http://localhost/desafiodomilhao/configurar-partida.php';

    $paginaTipo =
    'website';

    $paginaCss =
    '/desafiodomilhao/assets/css/configurar-partida.css';

    /* =========================================================
   HEADER
========================================================= */

    require_once __DIR__ . '/includes/header.php';

?>


<!-- =========================================================
     CONTEÚDO PRINCIPAL
========================================================= -->

<main class="pagina-configuracao">


  <!-- =====================================================
     CABEÇALHO
====================================================== -->

  <section class="cabecalho-configuracao">

    <!-- LOGO -->

    <img src="/desafiodomilhao/assets/img/logo/favicon.png" alt="Desafio do Milhão" class="logo-configuracao">


    <!-- =================================================
       ÁREA SELECIONADA
  ================================================== -->

    <div class="subtitulo link-desafio area-definida">

      <i class="<?php echo escapar(
                        (string) (
                            $areaSelecionada['icone'] ?? 'fa-solid fa-question'
                    )
                ); ?>" aria-hidden="true"></i>

      <span>
        <?php echo escapar(
                (string) $areaSelecionada['nome']
        ); ?>
      </span>

      <i class="fa-solid fa-check check-area" aria-hidden="true"></i>

    </div>


    <!-- =================================================
       TÍTULO
  ================================================== -->

    <div class="titulo-configuracao">

      <span class="etapa-configuracao">
        CONFIGURAÇÃO DA PARTIDA
      </span>

      <h1>
        PREPARE SEU DESAFIO
      </h1>

      <p>
        Defina as opções antes de iniciar a partida.
      </p>

    </div>

  </section>

  <!-- =====================================================
         CONTAINER
    ====================================================== -->

  <section class="container-configuracao">


    <!-- =================================================
             FORMULÁRIO
        ================================================== -->

    <section class="painel-formulario">

      <form id="formConfigurarPartida" action="/desafiodomilhao/iniciar-partida.php" method="POST" autocomplete="off">


        <!-- =================================================
                     MODO
                ================================================== -->

        <input type="hidden" name="modo" value="<?php echo escapar($modo); ?>">


        <!-- =================================================
                     JOGADOR
                ================================================== -->

        <?php if ($modo === 'individual'): ?>

        <div class="grupo-campo">

          <label for="nomeJogador">

            <i class="fa-solid fa-user"></i>

            Nome do jogador

          </label>

          <input type="text" id="nomeJogador" name="nome_jogador" maxlength="60" placeholder="Digite seu nome" required>

          <small>
            Esse nome poderá ser utilizado no ranking.
          </small>

        </div>

        <?php endif; ?>


        <!-- =================================================
                     COMPETIÇÃO
                ================================================== -->

        <?php if ($modo === 'competicao'): ?>

        <div class="bloco-equipes">

          <div class="titulo-secao-form">

            <i class="fa-solid fa-users"></i>

            <div>

              <span>
                Informe os nomes dos jogadores.
              </span>

            </div>

          </div>


          <div class="linha-campos">


            <!-- EQUIPE 1 -->

            <div class="grupo-campo">

              <label for="equipe1">
                Jogador 1
              </label>

              <input type="text" id="equipe1" name="equipe_1" maxlength="60" placeholder="Nome do jogador 1" required>

            </div>


            <!-- EQUIPE 2 -->

            <div class="grupo-campo">

              <label for="equipe2">
                Jogador 2
              </label>

              <input type="text" id="equipe2" name="equipe_2" maxlength="60" placeholder="Nome do jogador 2" required>

            </div>


          </div>

        </div>

        <?php endif; ?>


        <!-- =================================================
                     QUANTIDADE DE PERGUNTAS
                ================================================== -->

        <div class="grupo-campo">

          <label for="quantidadePerguntas">

            <i class="fa-solid fa-list-ol"></i>

            Quantidade de perguntas

          </label>

          <select id="quantidadePerguntas" name="quantidade_perguntas" required>

            <option value="10" selected>
              10 perguntas
            </option>

            <option value="15">
              15 perguntas
            </option>

            <option value="20">
              20 perguntas
            </option>

          </select>

          <small>
            O Desafio do Milhão utiliza no mínimo 10 perguntas.
          </small>

        </div>


        <!-- =================================================
                     DIFICULDADE
                ================================================== -->

        <div class="grupo-campo">

          <label>

            <i class="fa-solid fa-chart-simple"></i>

            Dificuldade

          </label>


          <div class="opcoes-dificuldade">


            <!-- =========================================
                             FÁCIL
                        ========================================== -->

            <label class="opcao-dificuldade">

              <input type="radio" name="dificuldade" value="facil">

              <span class="conteudo-dificuldade">

                <i class="fa-solid fa-seedling"></i>

                <strong>
                  FÁCIL
                </strong>

                <small>
                  Somente perguntas fáceis
                </small>

                <span class="tempo-dificuldade">
                  <i class="fa-solid fa-clock"></i>
                  20 segundos
                </span>

              </span>

            </label>


            <!-- =========================================
                             MISTA
                        ========================================== -->

            <label class="opcao-dificuldade">

              <input type="radio" name="dificuldade" value="mista" checked>

              <span class="conteudo-dificuldade">

                <i class="fa-solid fa-layer-group"></i>

                <strong>
                  MISTA
                </strong>

                <small>
                  Fácil, média e difícil
                </small>

                <span class="tempo-dificuldade">
                  <i class="fa-solid fa-clock"></i>
                  30 segundos
                </span>

              </span>

            </label>


            <!-- =========================================
                             DIFÍCIL
                        ========================================== -->

            <label class="opcao-dificuldade">

              <input type="radio" name="dificuldade" value="dificil">

              <span class="conteudo-dificuldade">

                <i class="fa-solid fa-fire"></i>

                <strong>
                  DIFÍCIL
                </strong>

                <small>
                  Somente perguntas difíceis
                </small>

                <span class="tempo-dificuldade">
                  <i class="fa-solid fa-clock"></i>
                  45 segundos
                </span>

              </span>

            </label>


          </div>

        </div>


        <!-- =================================================
                     EXPLICAÇÃO DA DIFICULDADE
                ================================================== -->

        <div id="explicacaoDificuldade" class="explicacao-dificuldade">

          <i class="fa-solid fa-circle-info"></i>

          <div>

            <strong id="tituloExplicacaoDificuldade">
              Modo Misto
            </strong>

            <span id="textoExplicacaoDificuldade">
              A partida utilizará perguntas fáceis, médias
              e difíceis, com 30 segundos para responder
              cada questão.
            </span>

          </div>

        </div>


        <!-- =================================================
                     RESUMO
                ================================================== -->

        <div class="resumo-partida-form">


          <!-- PERGUNTAS -->

          <div>

            <span>
              PERGUNTAS
            </span>

            <strong id="resumoPerguntas">
              10
            </strong>

          </div>


          <!-- TEMPO -->

          <div>

            <span>
              TEMPO POR PERGUNTA
            </span>

            <strong id="resumoTempo">
              30s
            </strong>

          </div>


          <!-- DIFICULDADE -->

          <div>

            <span>
              DIFICULDADE
            </span>

            <strong id="resumoDificuldade">
              Mista
            </strong>

          </div>


        </div>


        <!-- =================================================
                     AÇÕES
                ================================================== -->

        <div class="acoes-configuracao">


          <!-- VOLTAR -->

          <button type="button" class="botao-voltar-config" id="btnVoltarConfiguracao">

            <i class="fa-solid fa-arrow-left"></i>

            VOLTAR

          </button>


          <!-- INICIAR -->

          <button type="submit" class="botao-iniciar-partida">

            <i class="fa-solid fa-play"></i>

            INICIAR PARTIDA

          </button>


        </div>


      </form>

    </section>

  </section>


</main>


<!-- =========================================================
     FOOTER
========================================================= -->

<?php

    require_once __DIR__ . '/includes/footer.php';

?>


<!-- =========================================================
     JAVASCRIPT
========================================================= -->

<script src="/desafiodomilhao/assets/js/configurar-partida.js"></script>


</body>

</html>