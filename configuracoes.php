<?php

    declare (strict_types = 1);

    /* =========================================================
   SESSÃO
========================================================= */

    if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
    }

    /* =========================================================
   MENSAGENS DO CADASTRO

   São mensagens temporárias.
   Após serem lidas, são removidas da sessão.
========================================================= */

    $sucessoCadastro =
    $_SESSION['sucesso_cadastro'] ?? null;

    $erroCadastro =
    $_SESSION['erro_cadastro_configuracoes'] ?? null;

    /* =========================================================
   REMOVE AS FLASH MESSAGES DA SESSÃO
========================================================= */

    unset(
    $_SESSION['sucesso_cadastro'],
    $_SESSION['erro_cadastro_configuracoes']
    );

    /* =========================================================
   CONFIGURAÇÕES DA PÁGINA
========================================================= */

    $siteSubtitulo =
    'Configurações';

    $paginaTitulo =
    'Configurações - Desafio do Milhão';

    $paginaDescricao =
    'Configure áudio, experiência visual, partida, ranking e acesso ao Banco de Questões.';

    $paginaUrl =
    'https://www.guilhermeshorane.com.br/github/desafiodomilhao/public/configuracoes.php';

    $paginaTipo =
    'website';

    $paginaCss =
    '/github/desafiodomilhao/public/assets/css/configuracoes.css';

    /* =========================================================
   HEADER
========================================================= */

    require_once __DIR__ .
    '/includes/header.php';

?>


<!-- =========================================================
     CONTEÚDO PRINCIPAL
========================================================= -->

<main class="pagina-configuracoes">


  <!-- =====================================================
         CABEÇALHO
    ====================================================== -->

  <section class="cabecalho-configuracoes">


    <!-- LOGO -->

    <img src="/github/desafiodomilhao/public/assets/img/logo/favicon.png" alt="Desafio do Milhão" class="logo-configuracoes">


    <!-- IDENTIFICAÇÃO -->

    <span class="rotulo-configuracoes">

      <i class="fa-solid fa-gear"></i>

      CONFIGURAÇÕES

    </span>


    <!-- TÍTULO -->

    <h1>
      PERSONALIZE SUA EXPERIÊNCIA
    </h1>


    <!-- DESCRIÇÃO -->

    <p>

      Ajuste o funcionamento do jogo e gerencie
      seu acesso ao Banco de Questões.

    </p>


  </section>


  <!-- =====================================================
         MENSAGEM DE SUCESSO DO CADASTRO
    ====================================================== -->

  <?php if ($sucessoCadastro): ?>


  <section class="mensagem-cadastro-status sucesso">


    <!-- ÍCONE -->

    <div class="icone-cadastro-status">

      <i class="fa-solid fa-circle-check"></i>

    </div>


    <!-- CONTEÚDO -->

    <div class="conteudo-cadastro-status">


      <strong>
        CADASTRO REALIZADO
      </strong>


      <span>

        <?php echo escapar(
                (string) $sucessoCadastro
        ); ?>

      </span>


    </div>


    <!-- FECHAR -->

    <button type="button" class="fechar-cadastro-status" data-fechar-mensagem aria-label="Fechar mensagem"
      title="Fechar">

      <i class="fa-solid fa-xmark"></i>

    </button>


  </section>


  <?php endif; ?>


  <!-- =====================================================
         MENSAGEM DE ERRO DO CADASTRO
    ====================================================== -->

  <?php if ($erroCadastro): ?>


  <section class="mensagem-cadastro-status erro">


    <!-- ÍCONE -->

    <div class="icone-cadastro-status">

      <i class="fa-solid fa-circle-exclamation"></i>

    </div>


    <!-- CONTEÚDO -->

    <div class="conteudo-cadastro-status">


      <strong>
        ERRO NO CADASTRO
      </strong>


      <span>

        <?php echo escapar(
                (string) $erroCadastro
        ); ?>

      </span>


    </div>


    <!-- FECHAR -->

    <button type="button" class="fechar-cadastro-status" data-fechar-mensagem aria-label="Fechar mensagem"
      title="Fechar">

      <i class="fa-solid fa-xmark"></i>

    </button>


  </section>


  <?php endif; ?>


  <!-- =====================================================
         GRADE PRINCIPAL DE CONFIGURAÇÕES
    ====================================================== -->

  <section class="grade-configuracoes">


    <!-- =================================================
             ÁUDIO
        ================================================== -->

    <button type="button" class="card-configuracao" data-painel="painelAudio">


      <span class="icone-configuracao">

        <i class="fa-solid fa-volume-high"></i>

      </span>


      <span class="conteudo-configuracao">

        <strong>
          ÁUDIO
        </strong>


        <small>
          Música, efeitos sonoros e volumes do jogo.
        </small>

      </span>


      <span class="seta-configuracao">

        <i class="fa-solid fa-chevron-right"></i>

      </span>


    </button>


    <!-- =================================================
             EXPERIÊNCIA VISUAL
        ================================================== -->

    <button type="button" class="card-configuracao" data-painel="painelVisual">


      <span class="icone-configuracao">

        <i class="fa-solid fa-wand-magic-sparkles"></i>

      </span>


      <span class="conteudo-configuracao">

        <strong>
          EXPERIÊNCIA VISUAL
        </strong>


        <small>
          Animações, tela cheia e efeitos visuais.
        </small>

      </span>


      <span class="seta-configuracao">

        <i class="fa-solid fa-chevron-right"></i>

      </span>


    </button>


    <!-- =================================================
             PARTIDA
        ================================================== -->

    <button type="button" class="card-configuracao" data-painel="painelPartida">


      <span class="icone-configuracao">

        <i class="fa-solid fa-gamepad"></i>

      </span>


      <span class="conteudo-configuracao">

        <strong>
          PARTIDA
        </strong>


        <small>
          Perguntas, alternativas e comportamento da partida.
        </small>

      </span>


      <span class="seta-configuracao">

        <i class="fa-solid fa-chevron-right"></i>

      </span>


    </button>


    <!-- =================================================
             RANKING
        ================================================== -->

    <button type="button" class="card-configuracao" data-painel="painelRanking">


      <span class="icone-configuracao">

        <i class="fa-solid fa-trophy"></i>

      </span>


      <span class="conteudo-configuracao">

        <strong>
          RANKING
        </strong>


        <small>
          Defina como a classificação será apresentada.
        </small>

      </span>


      <span class="seta-configuracao">

        <i class="fa-solid fa-chevron-right"></i>

      </span>


    </button>


  </section>


  <!-- =====================================================
         ACESSO AO BANCO DE QUESTÕES
    ====================================================== -->

  <section class="acesso-banco-questoes">


    <!-- ÍCONE -->

    <div class="icone-acesso-banco">

      <i class="fa-solid fa-user-shield"></i>

    </div>


    <!-- CONTEÚDO -->

    <div class="conteudo-acesso-banco">


      <span class="rotulo-acesso">
        ACESSO RESTRITO
      </span>


      <h2>
        BANCO DE QUESTÕES
      </h2>


      <p>

        Entre com sua conta para cadastrar e gerenciar
        perguntas. Caso ainda não possua acesso,
        solicite seu cadastro ao administrador.

      </p>


    </div>


    <!-- BOTÕES -->

    <div class="acoes-acesso-banco">


      <a href="/github/desafiodomilhao/public/login.php" class="botao-acesso entrar">

        <i class="fa-solid fa-right-to-bracket"></i>

        ENTRAR

      </a>


      <a href="/github/desafiodomilhao/public/cadastro.php" class="botao-acesso cadastrar">

        <i class="fa-solid fa-user-plus"></i>

        SOLICITAR CADASTRO

      </a>


    </div>


  </section>


  <!-- =====================================================
         PAINEL - ÁUDIO
    ====================================================== -->

  <section id="painelAudio" class="painel-configuracao" hidden>


    <!-- CABEÇALHO -->

    <div class="cabecalho-painel-configuracao">


      <div>


        <i class="fa-solid fa-volume-high"></i>


        <div>

          <strong>
            ÁUDIO
          </strong>

          <span>
            Configure música e efeitos sonoros.
          </span>

        </div>


      </div>


      <button type="button" class="botao-fechar-painel" aria-label="Fechar painel">

        <i class="fa-solid fa-xmark"></i>

      </button>


    </div>


    <!-- OPÇÕES -->

    <div class="lista-opcoes-configuracao">


      <!-- MÚSICA DE FUNDO -->

      <div class="item-configuracao">


        <div>

          <strong>
            Música de fundo
          </strong>

          <span>
            Ativa ou desativa a trilha sonora do jogo.
          </span>

        </div>


        <label class="switch-configuracao">


          <input type="checkbox" id="configMusica" checked>


          <span></span>


        </label>


      </div>


      <!-- VOLUME DA MÚSICA -->

      <div class="item-configuracao item-volume">


        <div>

          <strong>
            Volume da música
          </strong>

          <span>
            Ajuste o volume da trilha sonora.
          </span>

        </div>


        <div class="controle-volume">


          <input type="range" id="configVolumeMusica" min="0" max="100" value="35">


          <span id="valorVolumeMusica">
            35%
          </span>


        </div>


      </div>


      <!-- EFEITOS SONOROS -->

      <div class="item-configuracao">


        <div>

          <strong>
            Efeitos sonoros
          </strong>

          <span>
            Sons de acerto, erro e transições.
          </span>

        </div>


        <label class="switch-configuracao">


          <input type="checkbox" id="configEfeitos" checked>


          <span></span>


        </label>


      </div>


      <!-- VOLUME DOS EFEITOS -->

      <div class="item-configuracao item-volume">


        <div>

          <strong>
            Volume dos efeitos
          </strong>

          <span>
            Ajuste o volume dos efeitos sonoros.
          </span>

        </div>


        <div class="controle-volume">


          <input type="range" id="configVolumeEfeitos" min="0" max="100" value="70">


          <span id="valorVolumeEfeitos">
            70%
          </span>


        </div>


      </div>


    </div>


  </section>


  <!-- =====================================================
         PAINEL - EXPERIÊNCIA VISUAL
    ====================================================== -->

  <section id="painelVisual" class="painel-configuracao" hidden>


    <!-- CABEÇALHO -->

    <div class="cabecalho-painel-configuracao">


      <div>


        <i class="fa-solid fa-wand-magic-sparkles"></i>


        <div>

          <strong>
            EXPERIÊNCIA VISUAL
          </strong>

          <span>
            Ajuste os efeitos visuais da plataforma.
          </span>

        </div>


      </div>


      <button type="button" class="botao-fechar-painel" aria-label="Fechar painel">

        <i class="fa-solid fa-xmark"></i>

      </button>


    </div>


    <!-- OPÇÕES -->

    <div class="lista-opcoes-configuracao">


      <!-- ANIMAÇÕES -->

      <div class="item-configuracao">


        <div>

          <strong>
            Animações
          </strong>

          <span>
            Exibe animações durante a navegação e partidas.
          </span>

        </div>


        <label class="switch-configuracao">


          <input type="checkbox" id="configAnimacoes" checked>


          <span></span>


        </label>


      </div>


      <!-- TELA CHEIA -->

      <div class="item-configuracao">


        <div>

          <strong>
            Tela cheia ao iniciar
          </strong>

          <span>
            Solicita tela cheia quando a partida começar.
          </span>

        </div>


        <label class="switch-configuracao">


          <input type="checkbox" id="configTelaCheia">


          <span></span>


        </label>


      </div>


      <!-- EFEITOS VISUAIS -->

      <div class="item-configuracao">


        <div>

          <strong>
            Efeitos de acerto e erro
          </strong>

          <span>
            Mostra efeitos visuais após responder.
          </span>

        </div>


        <label class="switch-configuracao">


          <input type="checkbox" id="configEfeitosVisuais" checked>


          <span></span>


        </label>


      </div>


    </div>


  </section>


  <!-- =====================================================
         PAINEL - PARTIDA
    ====================================================== -->

  <section id="painelPartida" class="painel-configuracao" hidden>


    <!-- CABEÇALHO -->

    <div class="cabecalho-painel-configuracao">


      <div>


        <i class="fa-solid fa-gamepad"></i>


        <div>

          <strong>
            PARTIDA
          </strong>

          <span>
            Defina como as questões serão apresentadas.
          </span>

        </div>


      </div>


      <button type="button" class="botao-fechar-painel" aria-label="Fechar painel">

        <i class="fa-solid fa-xmark"></i>

      </button>


    </div>


    <!-- OPÇÕES -->

    <div class="lista-opcoes-configuracao">


      <!-- EMBARALHAR PERGUNTAS -->

      <div class="item-configuracao">


        <div>

          <strong>
            Embaralhar perguntas
          </strong>

          <span>
            Altera a ordem das questões em cada partida.
          </span>

        </div>


        <label class="switch-configuracao">


          <input type="checkbox" id="configEmbaralharPerguntas" checked>


          <span></span>


        </label>


      </div>


      <!-- EMBARALHAR ALTERNATIVAS -->

      <div class="item-configuracao">


        <div>

          <strong>
            Embaralhar alternativas
          </strong>

          <span>
            Altera a posição das alternativas A, B, C e D.
          </span>

        </div>


        <label class="switch-configuracao">


          <input type="checkbox" id="configEmbaralharAlternativas" checked>


          <span></span>


        </label>


      </div>


      <!-- EXPLICAÇÃO -->

      <div class="item-configuracao">


        <div>

          <strong>
            Mostrar explicação
          </strong>

          <span>
            Exibe a explicação da resposta após a questão.
          </span>

        </div>


        <label class="switch-configuracao">


          <input type="checkbox" id="configMostrarExplicacao" checked>


          <span></span>


        </label>


      </div>


      <!-- DIFICULDADE -->

      <div class="item-configuracao">


        <div>

          <strong>
            Mostrar dificuldade
          </strong>

          <span>
            Exibe o nível da pergunta durante a partida.
          </span>

        </div>


        <label class="switch-configuracao">


          <input type="checkbox" id="configMostrarDificuldade" checked>


          <span></span>


        </label>


      </div>


      <!-- CONFIRMAR SAÍDA -->

      <div class="item-configuracao">


        <div>

          <strong>
            Confirmar ao sair
          </strong>

          <span>
            Solicita confirmação antes de abandonar uma partida.
          </span>

        </div>


        <label class="switch-configuracao">


          <input type="checkbox" id="configConfirmarSaida" checked>


          <span></span>


        </label>


      </div>


    </div>


  </section>


  <!-- =====================================================
         PAINEL - RANKING
    ====================================================== -->

  <section id="painelRanking" class="painel-configuracao" hidden>


    <!-- CABEÇALHO -->

    <div class="cabecalho-painel-configuracao">


      <div>


        <i class="fa-solid fa-trophy"></i>


        <div>

          <strong>
            RANKING
          </strong>

          <span>
            Configure a apresentação dos resultados.
          </span>

        </div>


      </div>


      <button type="button" class="botao-fechar-painel" aria-label="Fechar painel">

        <i class="fa-solid fa-xmark"></i>

      </button>


    </div>


    <!-- OPÇÕES -->

    <div class="lista-opcoes-configuracao">


      <!-- PARTICIPAR -->

      <div class="item-configuracao">


        <div>

          <strong>
            Participar do ranking
          </strong>

          <span>
            Permite registrar resultados no ranking.
          </span>

        </div>


        <label class="switch-configuracao">


          <input type="checkbox" id="configParticiparRanking" checked>


          <span></span>


        </label>


      </div>


      <!-- POSIÇÃO -->

      <div class="item-configuracao">


        <div>

          <strong>
            Mostrar posição final
          </strong>

          <span>
            Exibe a posição do jogador ao terminar a partida.
          </span>

        </div>


        <label class="switch-configuracao">


          <input type="checkbox" id="configMostrarPosicao" checked>


          <span></span>


        </label>


      </div>


    </div>


  </section>


  <!-- =====================================================
         AÇÕES GERAIS
    ====================================================== -->

  <section class="acoes-configuracoes">


    <!-- VOLTAR -->

    <a href="/github/desafiodomilhao/public/" class="botao-voltar-configuracoes">

      <i class="fa-solid fa-arrow-left"></i>

      VOLTAR

    </a>


    <!-- RESTAURAR -->

    <button type="button" id="btnRestaurarConfiguracoes" class="botao-restaurar-configuracoes">

      <i class="fa-solid fa-arrow-rotate-left"></i>

      RESTAURAR PADRÃO

    </button>


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

<script src="/github/desafiodomilhao/public/assets/js/configuracoes.js"></script>


</body>

</html>