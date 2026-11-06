<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/autenticacao.php';
require_once __DIR__ . '/../includes/repositories/PerguntaRepository.php';

$usuarioId = (int) ($_SESSION['usuario']['id'] ?? 0);
$perguntaId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($usuarioId <= 0 || $perguntaId === false || $perguntaId === null || $perguntaId <= 0) {
    header('Location: /github/desafiodomilhao/public/admin/perguntas.php', true, 303);
    exit;
}

$repository = new PerguntaRepository();
$pergunta = $repository->buscarPorIdAutorizado((int) $perguntaId, $usuarioId);

if ($pergunta === null) {
    $_SESSION['erro_pergunta'] = 'Questão não encontrada ou sem acesso autorizado.';
    header('Location: /github/desafiodomilhao/public/admin/perguntas.php', true, 303);
    exit;
}

$siteSubtitulo = 'Detalhes da Questão';
$paginaTitulo = 'Detalhes da Questão - Desafio do Milhão';
$paginaDescricao = 'Visualize os detalhes de uma questão.';
$paginaCss = '/github/desafiodomilhao/public/assets/css/perguntas.css';

require_once __DIR__ . '/../includes/header.php';
?>

<main class="pagina-banco-questoes">
  <section class="cabecalho-banco">
    <span class="rotulo-banco"><i class="fa-solid fa-eye"></i> BANCO DE QUESTÕES</span>
    <h1>DETALHES DA QUESTÃO</h1>
    <p>Confira o enunciado, as alternativas e a explicação cadastrada.</p>
  </section>

  <section class="grupo-nivel-questoes">
    <div class="cabecalho-grupo-nivel">
      <div>
        <span><?php echo escapar((string) $pergunta['area_nome']); ?></span>
        <h3>QUESTÃO #<?php echo (int) $pergunta['id']; ?></h3>
      </div>
      <strong><?php echo escapar(strtoupper((string) $pergunta['dificuldade'])); ?></strong>
    </div>

    <div class="detalhes-questao">
      <h2><?php echo escapar((string) $pergunta['pergunta']); ?></h2>

      <div class="alternativas-detalhes">
        <?php foreach ($pergunta['alternativas'] as $alternativa): ?>
        <article class="alternativa-detalhe <?php echo (int) $alternativa['correta'] === 1 ? 'correta' : ''; ?>">
          <strong><?php echo escapar((string) $alternativa['letra']); ?></strong>
          <span><?php echo escapar((string) $alternativa['texto']); ?></span>
          <?php if ((int) $alternativa['correta'] === 1): ?><i class="fa-solid fa-circle-check"></i><?php endif; ?>
        </article>
        <?php endforeach; ?>
      </div>

      <section class="explicacao-detalhe">
        <h3><i class="fa-solid fa-lightbulb"></i> EXPLICAÇÃO</h3>
        <p><?php echo nl2br(escapar((string) $pergunta['explicacao'])); ?></p>
      </section>

      <div class="acoes-card-questao">
        <a href="/github/desafiodomilhao/public/admin/pergunta-cadastrar.php?id=<?php echo (int) $pergunta['id']; ?>" class="acao-editar-questao"><i class="fa-solid fa-pen"></i> EDITAR</a>
        <a href="/github/desafiodomilhao/public/admin/perguntas.php" class="acao-detalhes-questao"><i class="fa-solid fa-arrow-left"></i> VOLTAR</a>
      </div>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
