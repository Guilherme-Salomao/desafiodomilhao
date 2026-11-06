<?php

    declare (strict_types = 1);

    /* =========================================================
   PROTEÇÃO DE ACESSO
========================================================= */

    require_once __DIR__ . '/../includes/autenticacao.php';

    require_once __DIR__ . '/../includes/repositories/PerguntaRepository.php';

    /* =========================================================
   DADOS DO USUÁRIO LOGADO
========================================================= */

    $usuario = $_SESSION['usuario'] ?? null;

    $nomeUsuario = (string) ($usuario['nome'] ?? 'Usuário');

    $areasUsuario = $usuario['areas'] ?? [];

    $perguntaIdEdicao = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    $modoEdicao =
    $perguntaIdEdicao !== false &&
    $perguntaIdEdicao !== null &&
    $perguntaIdEdicao > 0;

    $perguntaEdicao = null;

    if ($modoEdicao) {
    $perguntaRepository = new PerguntaRepository();
    $perguntaEdicao = $perguntaRepository->buscarPorIdAutorizado(
        (int) $perguntaIdEdicao,
        (int) ($usuario['id'] ?? 0)
    );

    if ($perguntaEdicao === null) {
        $_SESSION['erro_pergunta'] = 'Questão não encontrada ou sem acesso autorizado.';
        header('Location: /github/desafiodomilhao/public/admin/perguntas.php', true, 303);
        exit;
    }
    }

    $alternativasEdicao = ['A' => '', 'B' => '', 'C' => '', 'D' => ''];
    $respostaCorretaEdicao = '';

    foreach (($perguntaEdicao['alternativas'] ?? []) as $alternativa) {
    $letra = (string) ($alternativa['letra'] ?? '');

    if (isset($alternativasEdicao[$letra])) {
        $alternativasEdicao[$letra] = (string) ($alternativa['texto'] ?? '');

        if ((int) ($alternativa['correta'] ?? 0) === 1) {
            $respostaCorretaEdicao = $letra;
        }
    }
    }

    /* =========================================================
   MENSAGENS FLASH
========================================================= */

    if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
    }

    $mensagemSucesso =
    $_SESSION['sucesso_pergunta'] ?? null;

    $mensagemErro =
    $_SESSION['erro_pergunta'] ?? null;

    unset(
    $_SESSION['sucesso_pergunta'],
    $_SESSION['erro_pergunta']
    );

    /* =========================================================
   CONFIGURAÇÕES DA PÁGINA
========================================================= */

    $siteSubtitulo =
    $modoEdicao ? 'Editar Questão' : 'Cadastrar Questão';

    $paginaTitulo =
    ($modoEdicao ? 'Editar Questão' : 'Cadastrar Questão') . ' - Desafio do Milhão';

    $paginaDescricao =
    'Cadastre uma nova questão no Banco de Questões do Desafio do Milhão.';

    $paginaUrl =
    'https://www.guilhermeshorane.com.br/github/desafiodomilhao/public/admin/pergunta-cadastrar.php';

    $paginaTipo =
    'website';

    $paginaCss =
    '/github/desafiodomilhao/public/assets/css/pergunta-cadastrar.css';

    /* =========================================================
   HEADER
========================================================= */

    require_once __DIR__ . '/../includes/header.php';
?>

<main class="pagina-pergunta-cadastrar">

  <!-- =====================================================
       CABEÇALHO
  ====================================================== -->

  <section class="cabecalho-pergunta">

    <img src="/github/desafiodomilhao/public/assets/img/logo/favicon.png" alt="Desafio do Milhão" class="logo-pergunta">

    <span class="rotulo-pergunta">
      <i class="fa-solid fa-circle-question"></i>
      BANCO DE QUESTÕES
    </span>

    <h1><?php echo $modoEdicao ? 'EDITAR QUESTÃO' : 'CADASTRAR NOVA QUESTÃO'; ?></h1>

    <p>
      Cadastre perguntas para o jogo de acordo com as áreas
      autorizadas para o seu usuário.
    </p>

  </section>

  <!-- =====================================================
       INFORMAÇÕES DO USUÁRIO
  ====================================================== -->

  <section class="painel-usuario-pergunta">

    <div class="bloco-usuario-info">
      <div class="icone-usuario-info">
        <i class="fa-solid fa-user-shield"></i>
      </div>

      <div class="conteudo-usuario-info">
        <span>USUÁRIO LOGADO</span>
        <strong><?php echo escapar($nomeUsuario); ?></strong>
      </div>
    </div>

    <div class="bloco-usuario-areas">
      <span class="titulo-areas-autorizadas">
        ÁREAS AUTORIZADAS
      </span>

      <div class="lista-areas-autorizadas">
        <?php foreach ($areasUsuario as $area): ?>
        <span class="tag-area-autorizada">
          <i class="<?php echo escapar((string) ($area['icone'] ?? 'fa-solid fa-layer-group')); ?>"></i>
          <?php echo escapar((string) ($area['nome'] ?? 'Área')); ?>
        </span>
        <?php endforeach; ?>
      </div>
    </div>

  </section>

  <!-- =====================================================
       MENSAGEM DE VALIDAÇÃO DO FORMULÁRIO
  ====================================================== -->

  <section id="mensagemValidacaoPergunta" class="mensagem-status-pergunta erro" role="alert" hidden>
    <div class="icone-mensagem-status">
      <i class="fa-solid fa-circle-exclamation"></i>
    </div>

    <div class="conteudo-mensagem-status">
      <strong>ERRO AO CADASTRAR</strong>
      <span id="textoMensagemValidacaoPergunta"></span>
    </div>

    <button type="button" class="fechar-mensagem-status" data-fechar-mensagem aria-label="Fechar mensagem">
      <i class="fa-solid fa-xmark"></i>
    </button>
  </section>

  <!-- =====================================================
       MENSAGEM DE SUCESSO
  ====================================================== -->

  <?php if ($mensagemSucesso): ?>
  <section class="mensagem-status-pergunta sucesso">
    <div class="icone-mensagem-status">
      <i class="fa-solid fa-circle-check"></i>
    </div>

    <div class="conteudo-mensagem-status">
      <strong>QUESTÃO CADASTRADA</strong>
      <span><?php echo escapar((string) $mensagemSucesso); ?></span>
    </div>

    <button type="button" class="fechar-mensagem-status" data-fechar-mensagem aria-label="Fechar mensagem">
      <i class="fa-solid fa-xmark"></i>
    </button>
  </section>
  <?php endif; ?>

  <!-- =====================================================
       MENSAGEM DE ERRO
  ====================================================== -->

  <?php if ($mensagemErro): ?>
  <section class="mensagem-status-pergunta erro">
    <div class="icone-mensagem-status">
      <i class="fa-solid fa-circle-exclamation"></i>
    </div>

    <div class="conteudo-mensagem-status">
      <strong>ERRO AO CADASTRAR</strong>
      <span><?php echo escapar((string) $mensagemErro); ?></span>
    </div>

    <button type="button" class="fechar-mensagem-status" data-fechar-mensagem aria-label="Fechar mensagem">
      <i class="fa-solid fa-xmark"></i>
    </button>
  </section>
  <?php endif; ?>

  <!-- =====================================================
       FORMULÁRIO
  ====================================================== -->

  <section class="painel-formulario-pergunta">

    <div class="topo-formulario-pergunta">
      <div class="icone-topo-formulario">
        <i class="fa-solid fa-file-pen"></i>
      </div>

      <div class="texto-topo-formulario">
        <strong>NOVA QUESTÃO</strong>
        <span>
          <?php echo $modoEdicao ? 'Atualize os dados da questão selecionada.' : 'Preencha os campos abaixo para cadastrar uma nova pergunta.'; ?>
        </span>
      </div>
    </div>

    <form action="/github/desafiodomilhao/public/admin/processar-pergunta.php" method="POST" class="formulario-pergunta"
      id="formularioPergunta">

      <?php if ($modoEdicao): ?>
      <input type="hidden" name="pergunta_id" value="<?php echo (int) $perguntaIdEdicao; ?>">
      <?php endif; ?>

      <!-- =================================================
           LINHA 1
      ================================================== -->

      <div class="linha-formulario linha-dupla">

        <!-- ÁREA -->
        <div class="grupo-campo">
          <label for="area_id">
            <i class="fa-solid fa-layer-group"></i>
            Área
          </label>

          <select name="area_id" id="area_id" required>
            <option value="">
              Selecione uma área
            </option>

            <?php foreach ($areasUsuario as $area): ?>
            <option value="<?php echo (int) $area['id']; ?>" <?php echo $modoEdicao && (int) $perguntaEdicao['area_id'] === (int) $area['id'] ? 'selected' : ''; ?>>
              <?php echo escapar((string) $area['nome']); ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- DIFICULDADE -->
        <div class="grupo-campo">
          <label for="dificuldade">
            <i class="fa-solid fa-signal"></i>
            Dificuldade
          </label>

          <select name="dificuldade" id="dificuldade" required>
            <option value="">
              Selecione a dificuldade
            </option>
            <option value="facil" <?php echo $modoEdicao && $perguntaEdicao['dificuldade'] === 'facil' ? 'selected' : ''; ?>>Fácil</option>
            <option value="medio" <?php echo $modoEdicao && $perguntaEdicao['dificuldade'] === 'medio' ? 'selected' : ''; ?>>Média</option>
            <option value="dificil" <?php echo $modoEdicao && $perguntaEdicao['dificuldade'] === 'dificil' ? 'selected' : ''; ?>>Difícil</option>
          </select>
        </div>

      </div>

      <!-- =================================================
           ENUNCIADO
      ================================================== -->

      <div class="grupo-campo">
        <label for="enunciado">
          <i class="fa-solid fa-circle-question"></i>
          Pergunta / Enunciado
        </label>

        <textarea name="enunciado" id="enunciado" rows="5" maxlength="1000" placeholder="Digite o enunciado da questão"
          required><?php echo escapar((string) ($perguntaEdicao['pergunta'] ?? '')); ?></textarea>
      </div>

      <!-- =================================================
           ALTERNATIVAS
      ================================================== -->

      <div class="bloco-alternativas">

        <div class="titulo-bloco-formulario">
          <i class="fa-solid fa-list-check"></i>
          <strong>ALTERNATIVAS</strong>
        </div>

        <div class="grupo-campo alternativa-campo">
          <label for="alternativa_a">
            Alternativa A
          </label>
          <input type="text" name="alternativa_a" id="alternativa_a" maxlength="255" value="<?php echo escapar($alternativasEdicao['A']); ?>"
            placeholder="Digite a alternativa A" required>
        </div>

        <div class="grupo-campo alternativa-campo">
          <label for="alternativa_b">
            Alternativa B
          </label>
          <input type="text" name="alternativa_b" id="alternativa_b" maxlength="255" value="<?php echo escapar($alternativasEdicao['B']); ?>"
            placeholder="Digite a alternativa B" required>
        </div>

        <div class="grupo-campo alternativa-campo">
          <label for="alternativa_c">
            Alternativa C
          </label>
          <input type="text" name="alternativa_c" id="alternativa_c" maxlength="255" value="<?php echo escapar($alternativasEdicao['C']); ?>"
            placeholder="Digite a alternativa C" required>
        </div>

        <div class="grupo-campo alternativa-campo">
          <label for="alternativa_d">
            Alternativa D
          </label>
          <input type="text" name="alternativa_d" id="alternativa_d" maxlength="255" value="<?php echo escapar($alternativasEdicao['D']); ?>"
            placeholder="Digite a alternativa D" required>
        </div>

      </div>

      <!-- =================================================
           RESPOSTA CORRETA
      ================================================== -->

      <div class="bloco-resposta-correta" id="blocoRespostaCorreta">

        <div class="titulo-bloco-formulario">
          <i class="fa-solid fa-bullseye"></i>
          <strong>RESPOSTA CORRETA</strong>
        </div>

        <div class="lista-respostas-corretas">

          <label class="opcao-resposta-correta">
            <input type="radio" name="resposta_correta" value="A" <?php echo $respostaCorretaEdicao === 'A' ? 'checked' : ''; ?>>
            <span>A</span>
          </label>

          <label class="opcao-resposta-correta">
            <input type="radio" name="resposta_correta" value="B" <?php echo $respostaCorretaEdicao === 'B' ? 'checked' : ''; ?>>
            <span>B</span>
          </label>

          <label class="opcao-resposta-correta">
            <input type="radio" name="resposta_correta" value="C" <?php echo $respostaCorretaEdicao === 'C' ? 'checked' : ''; ?>>
            <span>C</span>
          </label>

          <label class="opcao-resposta-correta">
            <input type="radio" name="resposta_correta" value="D" <?php echo $respostaCorretaEdicao === 'D' ? 'checked' : ''; ?>>
            <span>D</span>
          </label>

        </div>

      </div>

      <!-- =================================================
           EXPLICAÇÃO
      ================================================== -->

      <div class="grupo-campo">
        <label for="explicacao">
          <i class="fa-solid fa-lightbulb"></i>
          Explicação da resposta
        </label>

        <textarea name="explicacao" id="explicacao" rows="5" maxlength="1500"
          placeholder="Explique por que a resposta correta está certa" required><?php echo escapar((string) ($perguntaEdicao['explicacao'] ?? '')); ?></textarea>
      </div>

      <!-- =================================================
           STATUS
      ================================================== -->

      <div class="bloco-status-pergunta">

        <label class="switch-status-pergunta">
          <input type="checkbox" name="ativo" value="1" <?php echo ! $modoEdicao || (int) $perguntaEdicao['ativo'] === 1 ? 'checked' : ''; ?>>
          <span class="slider-status"></span>
        </label>

        <div class="texto-status-pergunta">
          <strong>Questão ativa</strong>
          <span>
            Se marcada, a pergunta poderá ser utilizada nas partidas.
          </span>
        </div>

      </div>

      <!-- =================================================
           AÇÕES
      ================================================== -->

      <div class="acoes-formulario-pergunta">

        <a href="/github/desafiodomilhao/public/admin/perguntas.php" class="botao-voltar-pergunta">
          <i class="fa-solid fa-arrow-left"></i>
          VOLTAR
        </a>

        <button type="reset" class="botao-limpar-pergunta">
          <i class="fa-solid fa-eraser"></i>
          LIMPAR
        </button>

        <button type="submit" class="botao-salvar-pergunta">
          <i class="fa-solid fa-floppy-disk"></i>
          <?php echo $modoEdicao ? 'SALVAR ALTERAÇÕES' : 'SALVAR QUESTÃO'; ?>
        </button>

      </div>

    </form>

  </section>

</main>

<?php
    require_once __DIR__ . '/../includes/footer.php';
?>

<script src="/github/desafiodomilhao/public/assets/js/pergunta-cadastrar.js"></script>

</body>

</html>
