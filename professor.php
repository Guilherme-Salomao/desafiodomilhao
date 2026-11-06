<?php

    declare (strict_types = 1);

    /* =========================================================
   SESSÃO
========================================================= */

    if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
    }

    /* =========================================================
   REPOSITORY
========================================================= */

    require_once __DIR__ . '/includes/repositories/AreaRepository.php';

    /* =========================================================
   CARREGA ÁREAS
========================================================= */

    $areas     = [];
    $erroBanco = false;

    try {

    $areaRepository = new AreaRepository();

    $areas = $areaRepository->listarAtivas();

    } catch (Throwable $erro) {

    error_log(
        '[Desafio do Milhão - Modo Professor] ' .
        $erro->getMessage()
    );

    $erroBanco = true;
    }

    /* =========================================================
   CONFIGURAÇÕES DA PÁGINA
========================================================= */

    $siteSubtitulo =
    'Modo Professor';

    $paginaTitulo =
    'Modo Professor - Desafio do Milhão';

    $paginaDescricao =
    'Crie um quiz coletivo para utilizar com toda a turma.';

    $paginaUrl =
    'http://localhost/desafiodomilhao/professor.php';

    $paginaTipo =
    'website';

    $paginaCss =
    '/desafiodomilhao/assets/css/professor.css';

    /* =========================================================
   HEADER
========================================================= */

    require_once __DIR__ . '/includes/header.php';

?>


<!-- =========================================================
     CONTEÚDO PRINCIPAL
========================================================= -->

<main class="pagina-professor">


  <!-- =====================================================
         CABEÇALHO
    ====================================================== -->

  <section class="cabecalho-professor">


    <img src="/desafiodomilhao/assets/img/logo/favicon.png" alt="Desafio do Milhão" class="logo-professor">


    <div class="identificacao-professor">

      <i class="fa-solid fa-chalkboard-user"></i>

      <span>
        MODO PROFESSOR
      </span>

    </div>


    <h1>
      QUIZ PARA SALA DE AULA
    </h1>


    <p>
      Configure as perguntas e conduza o desafio
      no ritmo da sua turma.
    </p>


  </section>


  <!-- =====================================================
         FORMULÁRIO
    ====================================================== -->

  <section class="painel-professor">


    <form id="formProfessor" action="/desafiodomilhao/iniciar-quiz-professor.php" method="POST" autocomplete="off">


      <!-- =================================================
                 ÁREA
            ================================================== -->

      <section class="secao-configuracao">


        <div class="titulo-secao">

          <div class="numero-etapa">
            1
          </div>

          <div>

            <strong>
              Escolha a área
            </strong>

            <span>
              Selecione o conteúdo que será utilizado no quiz.
            </span>

          </div>

        </div>


        <?php if ($erroBanco): ?>


        <div class="mensagem-erro">

          <i class="fa-solid fa-triangle-exclamation"></i>

          <span>
            Não foi possível carregar as áreas.
          </span>

        </div>


        <?php elseif (empty($areas)): ?>


        <div class="mensagem-erro">

          <i class="fa-solid fa-circle-info"></i>

          <span>
            Nenhuma área está disponível no momento.
          </span>

        </div>


        <?php else: ?>


        <div class="grade-areas-professor">


          <?php foreach ($areas as $indice => $area): ?>


          <label class="opcao-area-professor">


            <input type="radio" name="area_id" value="<?php echo (int) $area['id']; ?>" data-nome="<?php echo escapar(
                 (string) $area['nome']
             ); ?>" <?php echo $indice === 0 ? 'checked' : ''; ?> required>


            <span class="conteudo-area-professor">


              <span class="icone-area-professor">

                <i class="<?php echo escapar(
                              (string) $area['icone']
                          ); ?>"></i>

              </span>


              <span class="dados-area-professor">

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


              <i class="fa-solid fa-circle-check check-area-professor"></i>


            </span>


          </label>


          <?php endforeach; ?>


        </div>


        <?php endif; ?>


      </section>


      <!-- =================================================
                 QUANTIDADE
            ================================================== -->

      <section class="secao-configuracao">


        <div class="titulo-secao">

          <div class="numero-etapa">
            2
          </div>

          <div>

            <strong>
              Quantidade de perguntas
            </strong>

            <span>
              Defina o tamanho do quiz.
            </span>

          </div>

        </div>


        <div class="opcoes-quantidade">


          <label class="opcao-quantidade">

            <input type="radio" name="quantidade_perguntas" value="10" checked>

            <span>

              <strong>
                10
              </strong>

              <small>
                perguntas
              </small>

            </span>

          </label>


          <label class="opcao-quantidade">

            <input type="radio" name="quantidade_perguntas" value="15">

            <span>

              <strong>
                15
              </strong>

              <small>
                perguntas
              </small>

            </span>

          </label>


          <label class="opcao-quantidade">

            <input type="radio" name="quantidade_perguntas" value="20">

            <span>

              <strong>
                20
              </strong>

              <small>
                perguntas
              </small>

            </span>

          </label>


        </div>


      </section>


      <!-- =================================================
                 DIFICULDADE
            ================================================== -->

      <section class="secao-configuracao">


        <div class="titulo-secao">

          <div class="numero-etapa">
            3
          </div>

          <div>

            <strong>
              Dificuldade
            </strong>

            <span>
              Determine o nível das perguntas utilizadas.
            </span>

          </div>

        </div>


        <div class="opcoes-dificuldade-professor">


          <!-- FÁCIL -->

          <label class="opcao-dificuldade-professor">


            <input type="radio" name="dificuldade" value="facil">


            <span>

              <i class="fa-solid fa-seedling"></i>

              <strong>
                FÁCIL
              </strong>

              <small>
                Somente perguntas fáceis
              </small>

            </span>


          </label>


          <!-- MISTA -->

          <label class="opcao-dificuldade-professor">


            <input type="radio" name="dificuldade" value="mista" checked>


            <span>

              <i class="fa-solid fa-layer-group"></i>

              <strong>
                MISTA
              </strong>

              <small>
                Perguntas fáceis, médias e difíceis
              </small>

            </span>


          </label>


          <!-- DIFÍCIL -->

          <label class="opcao-dificuldade-professor">


            <input type="radio" name="dificuldade" value="dificil">


            <span>

              <i class="fa-solid fa-fire"></i>

              <strong>
                DIFÍCIL
              </strong>

              <small>
                Somente perguntas difíceis
              </small>

            </span>


          </label>


        </div>


      </section>


      <!-- =================================================
                 INFORMAÇÃO
            ================================================== -->

      <div class="aviso-sem-tempo">


        <i class="fa-solid fa-infinity"></i>


        <div>

          <strong>
            SEM CRONÔMETRO
          </strong>

          <span>
            No Modo Professor não existe limite de tempo.
            Você decide quando revelar a resposta e quando
            avançar para a próxima pergunta.
          </span>

        </div>


      </div>


      <!-- =================================================
                 RESUMO
            ================================================== -->

      <section class="resumo-professor">


        <div>

          <i class="fa-solid fa-book-open"></i>

          <span>
            ÁREA
          </span>

          <strong id="resumoArea">
            -
          </strong>

        </div>


        <div>

          <i class="fa-solid fa-list-ol"></i>

          <span>
            PERGUNTAS
          </span>

          <strong id="resumoQuantidade">
            10
          </strong>

        </div>


        <div>

          <i class="fa-solid fa-chart-simple"></i>

          <span>
            DIFICULDADE
          </span>

          <strong id="resumoDificuldade">
            Mista
          </strong>

        </div>


        <div>

          <i class="fa-solid fa-clock"></i>

          <span>
            TEMPO
          </span>

          <strong>
            Livre
          </strong>

        </div>


      </section>


      <!-- =================================================
                 AÇÕES
            ================================================== -->

      <div class="acoes-professor">


        <a href="/desafiodomilhao/" class="botao-voltar-professor">

          <i class="fa-solid fa-arrow-left"></i>

          VOLTAR

        </a>


        <button type="submit" class="botao-iniciar-quiz" <?php echo empty($areas) ? 'disabled' : ''; ?>>

          <i class="fa-solid fa-play"></i>

          INICIAR QUIZ

        </button>


      </div>


    </form>


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

<script src="/desafiodomilhao/assets/js/professor.js"></script>


</body>

</html>