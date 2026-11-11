<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/autenticacao.php';
require_once __DIR__ . '/../includes/repositories/UsuarioRepository.php';

$usuarioId = (int) ($_SESSION['usuario']['id'] ?? 0);

try {
    $usuarioRepository = new UsuarioRepository();
    if ($usuarioId <= 0 || !$usuarioRepository->estaAtivo($usuarioId)) {
        throw new RuntimeException('Sessão inválida.');
    }
    $areasUsuario = $usuarioRepository->listarAreasDoUsuario($usuarioId);
} catch (Throwable $erro) {
    unset($_SESSION['usuario']);
    $_SESSION['erro_login'] = 'Faça login para acessar o Banco de Questões.';
    header('Location: /github/desafiodomilhao/public/login.php', true, 303);
    exit;
}

$erros = $_SESSION['erros_importacao'] ?? [];
$arquivoAnterior = $_SESSION['arquivo_importacao'] ?? null;
unset($_SESSION['erros_importacao'], $_SESSION['arquivo_importacao']);

$siteSubtitulo = 'Importar perguntas';
$paginaTitulo = 'Importar perguntas - Desafio do Milhão';
$paginaDescricao = 'Importe perguntas a partir de uma planilha preenchida pelos professores.';
$paginaCss = '/github/desafiodomilhao/public/assets/css/perguntas.css';
require_once __DIR__ . '/../includes/header.php';
?>
<main class="pagina-banco-questoes pagina-importacao">
  <a class="perfil-voltar" href="/github/desafiodomilhao/public/admin/perguntas.php"><i class="fa-solid fa-arrow-left"></i> VOLTAR AO BANCO DE QUESTÕES</a>
  <section class="cartao-importacao">
    <span class="rotulo-banco"><i class="fa-solid fa-file-arrow-up"></i> IMPORTAÇÃO EM LOTE</span>
    <h1>Importar perguntas por planilha</h1>
    <p>Baixe o modelo, mantenha os títulos das colunas e preencha uma pergunta por linha.</p>
    <div class="acoes-importacao">
      <a href="/github/desafiodomilhao/public/assets/modelos/modelo-importacao-perguntas.xlsx" download><i class="fa-solid fa-file-excel"></i> BAIXAR PLANILHA MODELO</a>
      <span>Formatos aceitos: XLSX ou CSV · sem limite de perguntas</span>
    </div>
    <div class="areas-importacao"><strong>Áreas que você pode importar:</strong><?php foreach ($areasUsuario as $area): ?><span><?php echo escapar((string) $area['slug']); ?></span><?php endforeach; ?></div>
    <?php if (!empty($erros)): ?>
      <div class="erros-importacao"><strong>Corrija a planilha antes de importar:</strong><ul><?php foreach ($erros as $erro): ?><li><?php echo escapar((string) $erro); ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <form action="/github/desafiodomilhao/public/admin/processar-importacao.php" method="post" enctype="multipart/form-data" class="form-importacao">
      <label for="arquivo_planilha">Selecione a planilha preenchida</label>
      <input id="arquivo_planilha" type="file" name="arquivo_planilha" accept=".xlsx,.csv" required>
      <?php if ($arquivoAnterior): ?><small>Último arquivo enviado: <?php echo escapar((string) $arquivoAnterior); ?></small><?php endif; ?>
      <button type="submit"><i class="fa-solid fa-cloud-arrow-up"></i> VALIDAR E IMPORTAR</button>
    </form>
  </section>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
