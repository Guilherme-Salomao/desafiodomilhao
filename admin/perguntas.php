<?php

    declare (strict_types = 1);

    require_once __DIR__ .
    '/../includes/autenticacao.php';

    require_once __DIR__ .
    '/../includes/repositories/PerguntaRepository.php';

    require_once __DIR__ .
    '/../includes/repositories/UsuarioRepository.php';

    $usuario =
    $_SESSION['usuario'] ?? [];

    $usuarioId =
    (int) (
    $usuario['id'] ?? 0
    );

    $nomeUsuario =
    (string) (
    $usuario['nome'] ?? 'Usuário'
    );

    $usuarioRepository =
    new UsuarioRepository();

    $perguntaRepository =
    new PerguntaRepository();

    if (
    $usuarioId <= 0 ||
    ! $usuarioRepository
    ->estaAtivo(
        $usuarioId
    )
    ) {

    unset(
        $_SESSION['usuario']
    );

    $_SESSION['erro_login'] =
        'Seu acesso foi desativado ou sua sessão não é mais válida.';

    header(
        'Location: /desafiodomilhao/login.php',
        true,
        303
    );

    exit;
    }

    $areasUsuario =
    $usuarioRepository
    ->listarAreasDoUsuario(
        $usuarioId
    );

    if (empty($areasUsuario)) {

    unset(
        $_SESSION['usuario']
    );

    $_SESSION['erro_login'] =
        'Sua conta não possui nenhuma área autorizada. Entre em contato com o administrador.';

    header(
        'Location: /desafiodomilhao/login.php',
        true,
        303
    );

    exit;
    }

    $areaRecebida =
    filter_input(
    INPUT_GET,
    'area',
    FILTER_VALIDATE_INT
    );

    $areaFiltro = null;

    if (
    $areaRecebida !== false &&
    $areaRecebida !== null &&
    $areaRecebida > 0
    ) {

    $areaFiltro =
    (int) $areaRecebida;
    }

    $dificuldadeRecebida =
    trim(
    (string) (
        $_GET['dificuldade'] ?? ''
    )
    );

    $dificuldadeFiltro = null;

    if (
    in_array(
        $dificuldadeRecebida,
        [
            'facil',
            'medio',
            'dificil',
        ],
        true
    )
    ) {

    $dificuldadeFiltro =
        $dificuldadeRecebida;
    }

    $statusRecebido =
    trim(
    (string) (
        $_GET['status'] ?? ''
    )
    );

    $statusFiltro = null;

    if ($statusRecebido === 'ativo') {

    $statusFiltro = 1;

    } elseif (
    $statusRecebido === 'inativo'
    ) {

    $statusFiltro = 0;
    }

    $buscaRecebida =
    trim(
    (string) (
        $_GET['busca'] ?? ''
    )
    );

    $busca =
    $buscaRecebida !== ''
    ? $buscaRecebida
    : null;

    if ($areaFiltro !== null) {

    if (
        ! $usuarioRepository
        ->usuarioPossuiArea(
            $usuarioId,
            $areaFiltro
        )
    ) {

        $areaFiltro =
            null;
    }
    }

    $itensPorPagina = 20;

    $paginaRecebida =
    filter_input(
        INPUT_GET,
        'pagina',
        FILTER_VALIDATE_INT
    );

    $paginaAtual =
    $paginaRecebida !== false &&
    $paginaRecebida !== null &&
    $paginaRecebida > 0
        ? (int) $paginaRecebida
        : 1;

    $totalPerguntasFiltradas =
    $perguntaRepository
    ->contarFiltradasPorUsuario(
        $usuarioId,
        $areaFiltro,
        $dificuldadeFiltro,
        $statusFiltro,
        $busca
    );

    $totalPaginas =
    max(
        1,
        (int) ceil(
            $totalPerguntasFiltradas /
            $itensPorPagina
        )
    );

    if ($paginaAtual > $totalPaginas) {
        $paginaAtual = $totalPaginas;
    }

    $offset =
    ($paginaAtual - 1) * $itensPorPagina;

    $perguntas =
    $perguntaRepository
    ->listarPorUsuario(
        $usuarioId,
        $areaFiltro,
        $dificuldadeFiltro,
        $statusFiltro,
        $busca,
        $itensPorPagina,
        $offset
    );

    /* =========================================================
       ORGANIZA QUESTÕES POR NÍVEL DE DIFICULDADE
    ========================================================= */

    $perguntasPorNivel = [
        'facil' => [],
        'medio' => [],
        'dificil' => [],
    ];

    foreach ($perguntas as $pergunta) {

    $dificuldadePergunta =
    (string) ($pergunta['dificuldade'] ?? '');

    if (! isset($perguntasPorNivel[$dificuldadePergunta])) {
        continue;
    }

    $perguntasPorNivel[$dificuldadePergunta][] =
        $pergunta;
    }

    $parametrosPaginacao = [];

    if ($buscaRecebida !== '') {
    $parametrosPaginacao['busca'] = $buscaRecebida;
    }

    if ($areaFiltro !== null) {
    $parametrosPaginacao['area'] = $areaFiltro;
    }

    if ($dificuldadeFiltro !== null) {
    $parametrosPaginacao['dificuldade'] = $dificuldadeFiltro;
    }

    if ($statusFiltro !== null) {
    $parametrosPaginacao['status'] =
        $statusFiltro === 1 ? 'ativo' : 'inativo';
    }

    $paginaInicial =
    max(1, $paginaAtual - 2);

    $paginaFinal =
    min($totalPaginas, $paginaAtual + 2);

    $totalPerguntas =
    $perguntaRepository
    ->contarPorUsuario(
        $usuarioId
    );

    $totalAtivas =
    $perguntaRepository
    ->contarAtivasPorUsuario(
        $usuarioId
    );

    $totalInativas =
    $perguntaRepository
    ->contarInativasPorUsuario(
        $usuarioId
    );

    $totalAreas =
    count(
    $areasUsuario
    );

    $mensagemSucesso =
    $_SESSION['sucesso_pergunta'] ?? null;

    $mensagemErro =
    $_SESSION['erro_pergunta'] ?? null;

    unset(
    $_SESSION['sucesso_pergunta'],
    $_SESSION['erro_pergunta']
    );

    $siteSubtitulo =
    'Banco de Questões';

    $paginaTitulo =
    'Banco de Questões - Desafio do Milhão';

    $paginaDescricao =
    'Gerencie as perguntas do Desafio do Milhão.';

    $paginaUrl =
    'http://localhost/desafiodomilhao/admin/perguntas.php';

    $paginaTipo =
    'website';

    $paginaCss =
    '/desafiodomilhao/assets/css/perguntas.css';

    require_once __DIR__ .
    '/../includes/header.php';

?>

<main class="pagina-banco-questoes">

  <section class="cabecalho-banco">

    <img src="/desafiodomilhao/assets/img/logo/favicon.png" alt="Desafio do Milhão" class="logo-banco">

    <span class="rotulo-banco">
      <i class="fa-solid fa-database"></i>
      ÁREA ADMINISTRATIVA
    </span>

    <h1>
      BANCO DE QUESTÕES
    </h1>

    <p>
      Cadastre e gerencie as perguntas disponíveis no jogo.
    </p>

  </section>

  <section class="painel-usuario-banco">

    <div class="usuario-logado-banco">

      <div class="icone-usuario-banco">
        <i class="fa-solid fa-user-shield"></i>
      </div>

      <div>
        <span>USUÁRIO LOGADO</span>

        <strong>
          <?php echo escapar($nomeUsuario); ?>
        </strong>
      </div>

    </div>

    <div class="areas-usuario-banco">

      <span class="titulo-areas-banco">
        ÁREAS AUTORIZADAS
      </span>

      <div class="tags-areas-banco">

        <?php foreach ($areasUsuario as $area): ?>

        <span class="tag-area-banco">

          <i class="<?php echo escapar(
                            (string) (
                                $area['icone'] ?? 'fa-solid fa-layer-group'
                        )
                    ); ?>"></i>

          <?php echo escapar(
                  (string) (
                      $area['nome'] ?? 'Área'
                  )
          ); ?>

        </span>

        <?php endforeach; ?>

      </div>

    </div>

    <a href="/desafiodomilhao/" class="botao-sair-banco">
      <i class="fa-solid fa-right-from-bracket"></i>
      SAIR
    </a>

  </section>

  <?php if ($mensagemSucesso): ?>

  <section class="mensagem-banco sucesso">

    <div class="icone-mensagem-banco">
      <i class="fa-solid fa-circle-check"></i>
    </div>

    <div class="conteudo-mensagem-banco">

      <strong>
        OPERAÇÃO REALIZADA
      </strong>

      <span>
        <?php echo escapar(
                (string) $mensagemSucesso
        ); ?>
      </span>

    </div>

    <button type="button" class="fechar-mensagem-banco" data-fechar-mensagem aria-label="Fechar mensagem">
      <i class="fa-solid fa-xmark"></i>
    </button>

  </section>

  <?php endif; ?>

  <?php if ($mensagemErro): ?>

  <section class="mensagem-banco erro">

    <div class="icone-mensagem-banco">
      <i class="fa-solid fa-circle-exclamation"></i>
    </div>

    <div class="conteudo-mensagem-banco">

      <strong>
        NÃO FOI POSSÍVEL CONCLUIR
      </strong>

      <span>
        <?php echo escapar(
                (string) $mensagemErro
        ); ?>
      </span>

    </div>

    <button type="button" class="fechar-mensagem-banco" data-fechar-mensagem aria-label="Fechar mensagem">
      <i class="fa-solid fa-xmark"></i>
    </button>

  </section>

  <?php endif; ?>

  <section class="acoes-superiores-banco">

    <div class="titulo-secao-banco">

      <span>
        GERENCIAMENTO
      </span>

      <h2>
        Suas questões
      </h2>

    </div>

    <a href="/desafiodomilhao/admin/pergunta-cadastrar.php" class="botao-nova-pergunta">
      <i class="fa-solid fa-plus"></i>
      CADASTRAR NOVA QUESTÃO
    </a>

  </section>

  <section class="grade-estatisticas-banco">

    <article class="card-estatistica">

      <div class="icone-estatistica">
        <i class="fa-solid fa-layer-group"></i>
      </div>

      <div>
        <span>TOTAL</span>
        <strong><?php echo $totalPerguntas; ?></strong>
      </div>

    </article>

    <article class="card-estatistica">

      <div class="icone-estatistica ativo">
        <i class="fa-solid fa-circle-check"></i>
      </div>

      <div>
        <span>ATIVAS</span>
        <strong><?php echo $totalAtivas; ?></strong>
      </div>

    </article>

    <article class="card-estatistica">

      <div class="icone-estatistica inativo">
        <i class="fa-solid fa-circle-pause"></i>
      </div>

      <div>
        <span>INATIVAS</span>
        <strong><?php echo $totalInativas; ?></strong>
      </div>

    </article>

    <article class="card-estatistica">

      <div class="icone-estatistica">
        <i class="fa-solid fa-shield-halved"></i>
      </div>

      <div>
        <span>ÁREAS</span>
        <strong><?php echo $totalAreas; ?></strong>
      </div>

    </article>

  </section>

  <section class="painel-filtros-banco">

    <div class="cabecalho-filtros">

      <div>
        <i class="fa-solid fa-filter"></i>
        <strong>FILTRAR QUESTÕES</strong>
      </div>

    </div>

    <form method="GET" action="/desafiodomilhao/admin/perguntas.php" class="formulario-filtros">

      <div class="campo-filtro campo-busca">

        <label for="busca">
          Buscar pergunta
        </label>

        <div>

          <i class="fa-solid fa-magnifying-glass"></i>

          <input type="text" name="busca" id="busca" maxlength="150" value="<?php echo escapar(
                                                                                $buscaRecebida
                                                                            ); ?>" placeholder="Digite parte da pergunta...">

        </div>

      </div>

      <div class="campo-filtro">

        <label for="area">
          Área
        </label>

        <select name="area" id="area">

          <option value="">
            Todas
          </option>

          <?php foreach ($areasUsuario as $area): ?>

          <?php
              $idAreaAtual =
              (int) $area['id'];
          ?>

          <option value="<?php echo $idAreaAtual; ?>" <?php echo
      $areaFiltro === $idAreaAtual
          ? 'selected'
      : '';
  ?>>
            <?php echo escapar(
                    (string) $area['nome']
            ); ?>
          </option>

          <?php endforeach; ?>

        </select>

      </div>

      <div class="campo-filtro">

        <label for="dificuldade">
          Dificuldade
        </label>

        <select name="dificuldade" id="dificuldade">

          <option value="">
            Todas
          </option>

          <option value="facil" <?php echo
                                    $dificuldadeFiltro === 'facil'
                                        ? 'selected'
                                    : '';
                                ?>>
            Fácil
          </option>

          <option value="medio" <?php echo
                                    $dificuldadeFiltro === 'medio'
                                        ? 'selected'
                                    : '';
                                ?>>
            Média
          </option>

          <option value="dificil" <?php echo
                                      $dificuldadeFiltro === 'dificil'
                                          ? 'selected'
                                      : '';
                                  ?>>
            Difícil
          </option>

        </select>

      </div>

      <div class="campo-filtro">

        <label for="status">
          Status
        </label>

        <select name="status" id="status">

          <option value="">
            Todos
          </option>

          <option value="ativo" <?php echo
                                    $statusRecebido === 'ativo'
                                        ? 'selected'
                                    : '';
                                ?>>
            Ativa
          </option>

          <option value="inativo" <?php echo
                                      $statusRecebido === 'inativo'
                                          ? 'selected'
                                      : '';
                                  ?>>
            Inativa
          </option>

        </select>

      </div>

      <a href="/desafiodomilhao/admin/perguntas.php" class="botao-limpar-filtros">
        <i class="fa-solid fa-eraser"></i>
        LIMPAR FILTROS
      </a>

    </form>

  </section>

  <section class="lista-questoes-banco">

    <?php if (empty($perguntas)): ?>

    <div class="estado-vazio-banco">

      <div class="icone-vazio-banco">
        <i class="fa-solid fa-circle-question"></i>
      </div>

      <h3>
        NENHUMA QUESTÃO ENCONTRADA
      </h3>

      <p>
        Ainda não existem questões para os filtros selecionados.
      </p>

      <a href="/desafiodomilhao/admin/pergunta-cadastrar.php">
        <i class="fa-solid fa-plus"></i>
        CADASTRAR PRIMEIRA QUESTÃO
      </a>

    </div>

    <?php else: ?>

    <?php
        $titulosNiveis = [
            'facil' => 'QUESTÕES FÁCEIS',
            'medio' => 'QUESTÕES MÉDIAS',
            'dificil' => 'QUESTÕES DIFÍCEIS',
        ];
    ?>

    <?php foreach ($perguntasPorNivel as $nivel => $questoesNivel): ?>

    <?php if (empty($questoesNivel)): ?>
    <?php continue; ?>
    <?php endif; ?>

    <section class="grupo-nivel-questoes grupo-nivel-<?php echo escapar($nivel); ?>">

      <div class="cabecalho-grupo-nivel">
        <div>
          <span>NÍVEL DE DIFICULDADE</span>
          <h3><?php echo escapar($titulosNiveis[$nivel]); ?></h3>
        </div>

        <strong><?php echo count($questoesNivel); ?> <?php echo count($questoesNivel) === 1 ? 'QUESTÃO' : 'QUESTÕES'; ?></strong>
      </div>

      <div class="lista-questoes-nivel">

    <?php foreach ($questoesNivel as $pergunta): ?>

    <?php

        $perguntaId =
        (int) $pergunta['id'];

        $dificuldade =
        (string) (
            $pergunta['dificuldade'] ?? ''
        );

        $dificuldadeTexto =
        match ($dificuldade) {

            'facil'   =>
            'FÁCIL',

            'medio'   =>
            'MÉDIA',

            'dificil' =>
            'DIFÍCIL',

            default   =>
            'N/D'
        };

        $ativo =
        (int) (
            $pergunta['ativo'] ?? 0
        ) === 1;

        $dataCriacao =
        (string) (
            $pergunta['criado_em'] ?? ''
        );

        $dataFormatada = '';

        if ($dataCriacao !== '') {

            $timestamp =
                strtotime(
                $dataCriacao
            );

            if ($timestamp !== false) {

                $dataFormatada =
                    date(
                    'd/m/Y H:i',
                    $timestamp
                );
            }
        }

    ?>

    <article class="card-questao-banco">

      <div class="topo-card-questao">

        <div class="identificacao-questao">

          <span class="numero-questao">
            #<?php echo $perguntaId; ?>
          </span>

          <span class="tag-area-questao">

            <i class="<?php echo escapar(
                              (string) (
                                  $pergunta['area_icone'] ?? 'fa-solid fa-layer-group'
                          )
                      ); ?>"></i>

            <?php echo escapar(
                    (string) (
                        $pergunta['area_nome'] ?? 'Área'
                    )
            ); ?>

          </span>

        </div>

        <div class="tags-status-questao">

          <span class="
                                    tag-dificuldade
                                    dificuldade-<?php echo escapar(
                                                        $dificuldade
                                                ); ?>
                                ">
            <?php echo $dificuldadeTexto; ?>
          </span>

          <?php if ($ativo): ?>

          <span class="tag-status ativo">
            ATIVA
          </span>

          <?php else: ?>

          <span class="tag-status inativo">
            INATIVA
          </span>

          <?php endif; ?>

        </div>

      </div>

      <div class="conteudo-card-questao">

        <h3>
          <?php echo escapar(
                  (string) (
                      $pergunta['pergunta'] ?? ''
                  )
          ); ?>
        </h3>

        <div class="metadados-questao">

          <span>
            <i class="fa-solid fa-user"></i>

            <?php echo escapar(
                    (string) (
                        $pergunta['usuario_nome'] ?? 'Usuário'
                    )
            ); ?>
          </span>

          <?php if ($dataFormatada !== ''): ?>

          <span>
            <i class="fa-solid fa-calendar"></i>
            <?php echo escapar(
                    $dataFormatada
            ); ?>
          </span>

          <?php endif; ?>

        </div>

      </div>

      <div class="acoes-card-questao">

        <a href="/desafiodomilhao/admin/pergunta-cadastrar.php?id=<?php echo $perguntaId; ?>" class="acao-editar-questao">
          <i class="fa-solid fa-pen"></i>
          EDITAR
        </a>

        <a href="/desafiodomilhao/admin/detalhes-pergunta.php?id=<?php echo $perguntaId; ?>" class="acao-detalhes-questao">
          <i class="fa-solid fa-eye"></i>
          DETALHES
        </a>

        <form action="/desafiodomilhao/admin/excluir-pergunta.php" method="POST" class="formulario-excluir-questao" data-excluir-questao>
          <input type="hidden" name="pergunta_id" value="<?php echo $perguntaId; ?>">
          <button type="submit" class="acao-excluir-questao">
            <i class="fa-solid fa-trash"></i>
            EXCLUIR
          </button>
        </form>

      </div>

    </article>

    <?php endforeach; ?>

      </div>

    </section>

    <?php endforeach; ?>

    <?php endif; ?>

  </section>

  <?php if ($totalPaginas > 1): ?>

  <nav class="paginacao-questoes" aria-label="Paginação das questões">

    <?php if ($paginaAtual > 1): ?>
    <?php $parametrosPaginacao['pagina'] = $paginaAtual - 1; ?>
    <a href="/desafiodomilhao/admin/perguntas.php?<?php echo escapar(http_build_query($parametrosPaginacao)); ?>">
      <i class="fa-solid fa-arrow-left"></i>
      ANTERIOR
    </a>
    <?php endif; ?>

    <div class="numeros-paginacao">
      <?php for ($pagina = $paginaInicial; $pagina <= $paginaFinal; $pagina++): ?>
      <?php $parametrosPaginacao['pagina'] = $pagina; ?>
      <a href="/desafiodomilhao/admin/perguntas.php?<?php echo escapar(http_build_query($parametrosPaginacao)); ?>" <?php echo $pagina === $paginaAtual ? 'aria-current="page"' : ''; ?>>
        <?php echo $pagina; ?>
      </a>
      <?php endfor; ?>
    </div>

    <?php if ($paginaAtual < $totalPaginas): ?>
    <?php $parametrosPaginacao['pagina'] = $paginaAtual + 1; ?>
    <a href="/desafiodomilhao/admin/perguntas.php?<?php echo escapar(http_build_query($parametrosPaginacao)); ?>">
      PRÓXIMA
      <i class="fa-solid fa-arrow-right"></i>
    </a>
    <?php endif; ?>

  </nav>

  <?php endif; ?>

  <section class="rodape-banco">

    <a href="/desafiodomilhao/" class="botao-voltar-banco">
      <i class="fa-solid fa-arrow-left"></i>
      VOLTAR AO JOGO
    </a>

  </section>

</main>

<?php

    require_once __DIR__ .
    '/../includes/footer.php';

?>

<script src="/desafiodomilhao/assets/js/perguntas.js?v=<?php echo (int) filemtime(__DIR__ . '/../assets/js/perguntas.js'); ?>"></script>

</body>

</html>
