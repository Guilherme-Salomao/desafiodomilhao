<?php

    declare (strict_types = 1);

    /* =========================================================
   SESSÃO
========================================================= */

    if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
    }

    /* =========================================================
   ÁREA ATUAL
========================================================= */

    $areaSelecionada = $_SESSION['area'] ?? null;

    /* =========================================================
   CONFIGURAÇÕES DA PÁGINA
========================================================= */

    $siteSubtitulo = 'Escolha uma área';

    $paginaTitulo =
    'Escolha uma Área - Desafio do Milhão';

    $paginaDescricao =
    'Escolha uma área de conhecimento para iniciar o Desafio do Milhão.';

    $paginaUrl =
    'https://www.guilhermeshorane.com.br/github/desafiodomilhao/public/areas.php';

    $paginaTipo =
    'website';

    $paginaCss =
    '/github/desafiodomilhao/public/assets/css/areas.css';

    /* =========================================================
   DESTINO APÓS A ESCOLHA
========================================================= */

    $retornosPermitidos = [
    'inicio',
    'jogo',
    'competicao',
    ];

    $retorno = trim(
    (string) ($_GET['retorno'] ?? 'inicio')
    );

    if (
    ! in_array(
        $retorno,
        $retornosPermitidos,
        true
    )
    ) {
    $retorno = 'inicio';
    }

    /* =========================================================
   REPOSITORY
========================================================= */

    require_once __DIR__ .
    '/includes/repositories/AreaRepository.php';

    /* =========================================================
   CARREGA AS ÁREAS
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
        '[Desafio do Milhão - Áreas] ' .
        $erro->getMessage()
    );

    $erroBanco = true;
    }

    /* =========================================================
   HEADER
========================================================= */

    require_once __DIR__ . '/includes/header.php';

?>


<!-- =========================================================
     CONTEÚDO PRINCIPAL
========================================================= -->

<main class="pagina-areas">


  <!-- =====================================================
         CABEÇALHO
    ====================================================== -->

  <section class="cabecalho-areas">

    <img src="/github/desafiodomilhao/public/assets/img/logo/favicon.png" alt="Desafio do Milhão" class="logo-areas">

    <h1>
      ESCOLHA SUA ÁREA
    </h1>

    <p>
      Selecione uma área de conhecimento para iniciar o desafio.
    </p>


    <!-- =================================================
             ÁREA ATUAL
        ================================================== -->

    <?php if ($areaSelecionada): ?>

    <div class="area-selecionada-info">

      <span class="rotulo-area-selecionada">
        ÁREA ATUAL
      </span>

      <strong>
        <?php echo escapar(
                (string) ($areaSelecionada['nome'] ?? '')
        ); ?>
      </strong>

      <a href="/github/desafiodomilhao/public/limpar-area.php?retorno=areas" class="trocar-area">

        <i class="fa-solid fa-xmark"></i>

        LIMPAR ÁREA SELECIONADA

      </a>

    </div>

    <?php endif; ?>


  </section>


  <!-- =====================================================
         ÁREAS DISPONÍVEIS
    ====================================================== -->

  <section class="grade-areas">


    <?php if ($erroBanco): ?>


    <div class="mensagem-areas">

      <i class="fa-solid fa-triangle-exclamation"></i>

      <strong>
        Não foi possível carregar as áreas.
      </strong>

      <span>
        Tente novamente em alguns instantes.
      </span>

    </div>


    <?php elseif (empty($areas)): ?>


    <div class="mensagem-areas">

      <i class="fa-solid fa-circle-info"></i>

      <strong>
        Nenhuma área disponível.
      </strong>

      <span>
        Ainda não existem áreas ativas cadastradas.
      </span>

    </div>


    <?php else: ?>


    <?php foreach ($areas as $area): ?>


    <?php

        $estaSelecionada =
        $areaSelecionada &&
        isset($areaSelecionada['id']) &&
        (int) $areaSelecionada['id'] ===
        (int) $area['id'];

    ?>


    <a href="/github/desafiodomilhao/public/selecionar-area.php?area=<?php
                                                           echo urlencode(
                                                               (string) $area['slug']
                                                       );
                                                       ?>&retorno=<?php
         echo urlencode($retorno);
         ?>" class="card-area">


      <!-- ÍCONE -->

      <div class="icone-area">

        <i class="<?php echo escapar(
                      (string) $area['icone']
                  ); ?>" aria-hidden="true"></i>

      </div>


      <!-- CONTEÚDO -->

      <div class="conteudo-area">

        <h2>

          <?php echo escapar(
                  (string) $area['nome']
          ); ?>


          <?php if ($estaSelecionada): ?>

          <i class="fa-solid fa-check" aria-hidden="true"></i>

          <?php endif; ?>

        </h2>

        <p>
          <?php echo escapar(
                  (string) ($area['descricao'] ?? '')
          ); ?>
        </p>

      </div>


      <!-- SETA -->

      <div class="seta-area">

        <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>

      </div>


    </a>


    <?php endforeach; ?>


    <?php endif; ?>


  </section>


  <!-- =====================================================
         VOLTAR
    ====================================================== -->

  <section class="area-voltar">

    <a href="/github/desafiodomilhao/public/" class="botao-voltar" onclick="history.back(); return false;">

      <i class="fa-solid fa-arrow-left"></i>

      VOLTAR

    </a>

  </section>


</main>


<!-- =========================================================
     FOOTER
========================================================= -->

<?php

    require_once __DIR__ . '/includes/footer.php';

?>


</body>

</html>