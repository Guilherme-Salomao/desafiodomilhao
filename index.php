<?php

    declare (strict_types = 1);

    /* =========================================================
   SESSÃO
========================================================= */

    if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
    }

    /* =========================================================
   ÁREA SELECIONADA
========================================================= */

    $areaSelecionada =
    $_SESSION['area'] ?? null;

    /* =========================================================
   DESTINOS DOS MODOS
========================================================= */

    /*
 * 1 PLAYER
 *
 * Se já existe uma área selecionada:
 * vai direto para a configuração da partida individual.
 *
 * Se não existe:
 * primeiro escolhe a área.
 */

    $urlUmPlayer = $areaSelecionada
    ? '/desafiodomilhao/configurar-partida.php?modo=individual'
    : '/desafiodomilhao/areas.php?retorno=jogo';

    /*
 * 2 PLAYERS
 *
 * Internamente continuamos utilizando o modo "competicao".
 *
 * O nome visual foi alterado para 2 PLAYERS.
 */

    $urlDoisPlayers = $areaSelecionada
    ? '/desafiodomilhao/configurar-partida.php?modo=competicao'
    : '/desafiodomilhao/areas.php?retorno=competicao';

    /* =========================================================
   CONFIGURAÇÕES DA PÁGINA
========================================================= */

    $siteSubtitulo = $areaSelecionada['nome'] ?? 'Escolha seu desafio';

    $paginaTitulo =
    'Desafio do Milhão';

    $paginaDescricao =
    'Escolha uma área de conhecimento e desafie seus conhecimentos no Desafio do Milhão.';

    $paginaUrl =
    'http://localhost/desafiodomilhao/';

    $paginaTipo =
    'website';

    /* =========================================================
   HEADER
========================================================= */

    require_once __DIR__ . '/includes/header.php';

?>


<!-- =========================================================
     TELA INICIAL / ÁUDIO
========================================================= -->

<div id="telaInicialAudio" class="tela-inicial-audio">

  <div class="conteudo-inicial-audio">

    <img src="/desafiodomilhao/assets/img/logo/favicon.png" alt="Desafio do Milhão" class="logo-inicial-audio">

    <button id="btnIniciarExperiencia" type="button">

      <i class="fa-solid fa-play"></i>

      INICIAR DESAFIO

    </button>

  </div>

</div>


<!-- =========================================================
     CONTEÚDO PRINCIPAL
========================================================= -->

<main class="pagina-inicial">


  <!-- =====================================================
         ÁREA SUPERIOR
    ====================================================== -->

  <section class="area-superior">


    <!-- =================================================
             COLUNA ESQUERDA
        ================================================== -->

    <div class="coluna-esquerda">

      <div class="logo-area">


        <!-- =================================================
                     LOGO
                ================================================== -->

        <img src="/desafiodomilhao/assets/img/logo/favicon.png" alt="Desafio do Milhão" class="logo-principal">


        <!-- =================================================
                     ÁREA SELECIONADA / ESCOLHER ÁREA
                ================================================== -->

        <a href="/desafiodomilhao/areas.php?retorno=inicio"
          class="subtitulo link-desafio <?php echo $areaSelecionada ? 'area-definida' : ''; ?>">

          <?php if ($areaSelecionada): ?>

          <i class="<?php echo escapar(
                            (string) (
                                $areaSelecionada['icone'] ?? 'fa-solid fa-question'
                        )
                    ); ?>" aria-hidden="true"></i>


          <span>

            <?php echo escapar(
                    (string) (
                        $areaSelecionada['nome'] ?? ''
                    )
            ); ?>

          </span>


          <i class="fa-solid fa-check check-area" aria-hidden="true"></i>


          <?php else: ?>

          ESCOLHA SEU DESAFIO

          <?php endif; ?>

        </a>


        <!-- =================================================
                     ÁREA SELECIONADA
                ================================================== -->

        <?php if ($areaSelecionada): ?>

        <div class="area-selecionada-info">


          <span class="rotulo-area-selecionada">

            ÁREA SELECIONADA

          </span>


          <!-- =========================================
                             TROCAR ÁREA
                        ========================================== -->

          <a href="/desafiodomilhao/areas.php?retorno=inicio" class="trocar-area">

            <i class="fa-solid fa-arrows-rotate" aria-hidden="true"></i>

            TROCAR ÁREA

          </a>


          <!-- =========================================
                             LIMPAR ÁREA
                        ========================================== -->

          <a href="/desafiodomilhao/limpar-area.php?retorno=inicio" class="trocar-area">

            <i class="fa-solid fa-xmark" aria-hidden="true"></i>

            LIMPAR ÁREA

          </a>


        </div>


        <?php else: ?>

        <p class="descricao">

          Escolha uma área de conhecimento e depois
          selecione se deseja jogar sozinho ou com
          outro jogador.

        </p>

        <?php endif; ?>


      </div>

    </div>


    <!-- =================================================
             COLUNA DIREITA
        ================================================== -->

    <div class="coluna-direita">


      <nav class="menu-principal" aria-label="Menu principal">


        <!-- =================================================
                     1 PLAYER
                ================================================== -->

        <a href="<?php echo escapar($urlUmPlayer); ?>" class="botao-menu botao-jogar">

          <div class="icone-botao">

            <i class="fa-solid fa-user" aria-hidden="true"></i>

          </div>


          <div class="texto-botao">

            <strong>

              1 PLAYER

            </strong>

            <span>

              Jogue sozinho e chegue ao milhão

            </span>

          </div>


        </a>


        <!-- =================================================
                     2 PLAYERS
                ================================================== -->

        <a href="<?php echo escapar($urlDoisPlayers); ?>" class="botao-menu">

          <div class="icone-botao">

            <i class="fa-solid fa-user-group" aria-hidden="true"></i>

          </div>


          <div class="texto-botao">

            <strong>

              2 PLAYERS

            </strong>

            <span>

              Disputa entre dois jogadores

            </span>

          </div>


        </a>


        <!-- =================================================
                     MODO PROFESSOR
                ================================================== -->

        <a href="/desafiodomilhao/professor.php" class="botao-menu">

          <div class="icone-botao">

            <i class="fa-solid fa-graduation-cap" aria-hidden="true"></i>

          </div>


          <div class="texto-botao">

            <strong>

              MODO PROFESSOR

            </strong>

            <span>

              Configure partidas e conteúdos

            </span>

          </div>


        </a>


        <!-- =================================================
                     BANCO DE QUESTÕES
                ================================================== -->

        <a href="/desafiodomilhao/login.php" class="botao-menu">

          <div class="icone-botao">

            <i class="fa-solid fa-database" aria-hidden="true"></i>

          </div>


          <div class="texto-botao">

            <strong>

              BANCO DE QUESTÕES

            </strong>

            <span>

              Cadastre e gerencie perguntas

            </span>

          </div>


        </a>


        <!-- =================================================
                     RANKING
                ================================================== -->

        <a href="/desafiodomilhao/ranking.php" class="botao-menu">

          <div class="icone-botao">

            <i class="fa-solid fa-trophy" aria-hidden="true"></i>

          </div>


          <div class="texto-botao">

            <strong>

              RANKING

            </strong>

            <span>

              Veja os melhores jogadores

            </span>

          </div>


        </a>


        <!-- =================================================
                     CONFIGURAÇÕES
                ================================================== -->

        <a href="/desafiodomilhao/configuracoes.php" class="botao-menu">

          <div class="icone-botao">

            <i class="fa-solid fa-gear" aria-hidden="true"></i>

          </div>


          <div class="texto-botao">

            <strong>

              CONFIGURAÇÕES

            </strong>

            <span>

              Som e preferências do jogo

            </span>

          </div>


        </a>


      </nav>

    </div>

  </section>


  <!-- =====================================================
         CARDS INFERIORES
    ====================================================== -->

  <section class="area-cards">


    <!-- =================================================
             PRÊMIO
        ================================================== -->

    <div class="card-destaque">

      <span class="rotulo">

        PRÊMIO MÁXIMO

      </span>

      <h2>

        R$ 1.000.000

      </h2>

      <p>

        10 perguntas separam você do grande desafio.

      </p>

    </div>


    <!-- =================================================
             DESAFIO
        ================================================== -->

    <div class="card-desafio">

      <i class="fa-solid fa-bolt" aria-hidden="true"></i>


      <div>

        <span>

          DESAFIO FINAL

        </span>

        <strong>

          Você chegaria ao milhão?

        </strong>

      </div>


    </div>


  </section>


</main>


<!-- =========================================================
     ÁUDIO
========================================================= -->

<audio id="musicaAbertura" preload="auto" loop>

  <source src="/desafiodomilhao/assets/audio/trilhas/abertura.mp3" type="audio/mpeg">

</audio>


<!-- =========================================================
     FOOTER
========================================================= -->

<?php

    require_once __DIR__ . '/includes/footer.php';

?>


<!-- =========================================================
     JAVASCRIPT
========================================================= -->

<script src="/desafiodomilhao/assets/js/main.js"></script>


</body>

</html>