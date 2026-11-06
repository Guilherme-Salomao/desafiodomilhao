<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/autenticacao.php';
require_once __DIR__ . '/../includes/repositories/UsuarioRepository.php';

$usuarioSessao = $_SESSION['usuario'] ?? [];
$usuarioId = (int) ($usuarioSessao['id'] ?? 0);

if ($usuarioId <= 0) {
    header('Location: /github/desafiodomilhao/public/login.php', true, 303);
    exit;
}

try {
    $usuarioRepository = new UsuarioRepository();
    $usuario = $usuarioRepository->buscarPorId($usuarioId);

    if (!$usuario || !$usuarioRepository->estaAtivo($usuarioId)) {
        unset($_SESSION['usuario']);
        $_SESSION['erro_login'] = 'Sua sessão não é mais válida.';
        header('Location: /github/desafiodomilhao/public/login.php', true, 303);
        exit;
    }

    $areasAtivas = $usuarioRepository->listarTodasAreasAtivas();
    $areasUsuario = $usuarioRepository->listarAreasDoUsuario($usuarioId);
    $areasSelecionadas = array_map(
        static fn (array $area): int => (int) $area['id'],
        $areasUsuario
    );
} catch (Throwable $erro) {
    error_log('[Desafio do Milhão - Perfil] ' . $erro->getMessage());
    http_response_code(500);
    exit('Não foi possível carregar o perfil.');
}

$mensagemSucesso = $_SESSION['sucesso_perfil'] ?? null;
$mensagemErro = $_SESSION['erro_perfil'] ?? null;
unset($_SESSION['sucesso_perfil'], $_SESSION['erro_perfil']);

$siteSubtitulo = 'Meu perfil';
$paginaTitulo = 'Meu perfil - Desafio do Milhão';
$paginaDescricao = 'Atualize seus dados, senha e áreas autorizadas.';
$paginaUrl = 'https://www.guilhermeshorane.com.br/github/desafiodomilhao/public/admin/perfil.php';
$paginaCss = '/github/desafiodomilhao/public/assets/css/perfil.css';

require_once __DIR__ . '/../includes/header.php';
?>

<main class="pagina-perfil">
  <a class="perfil-voltar" href="/github/desafiodomilhao/public/admin/perguntas.php">
    <i class="fa-solid fa-arrow-left"></i> VOLTAR AO BANCO DE QUESTÕES
  </a>

  <header class="cabecalho-perfil">
    <span><i class="fa-solid fa-user-gear"></i> CONTA DO USUÁRIO</span>
    <h1>Meu perfil</h1>
    <p>Atualize seus dados de acesso e escolha as áreas em que poderá cadastrar perguntas.</p>
  </header>

  <?php if ($mensagemSucesso): ?>
    <div class="alerta-perfil sucesso"><i class="fa-solid fa-circle-check"></i><?php echo escapar((string) $mensagemSucesso); ?></div>
  <?php endif; ?>

  <?php if ($mensagemErro): ?>
    <div class="alerta-perfil erro"><i class="fa-solid fa-circle-exclamation"></i><?php echo escapar((string) $mensagemErro); ?></div>
  <?php endif; ?>

  <form class="formulario-perfil" action="/github/desafiodomilhao/public/admin/processar-perfil.php" method="post">
    <section class="cartao-perfil">
      <h2><i class="fa-solid fa-id-card"></i> Dados pessoais</h2>
      <div class="campos-perfil">
        <label>Nome
          <input type="text" name="nome" value="<?php echo escapar((string) $usuario['nome']); ?>" maxlength="150" required>
        </label>
        <label>Login
          <input type="text" value="<?php echo escapar((string) $usuario['login']); ?>" disabled>
          <small>O login não pode ser alterado nesta tela.</small>
        </label>
      </div>
    </section>

    <section class="cartao-perfil">
      <h2><i class="fa-solid fa-lock"></i> Alterar senha</h2>
      <p class="ajuda-perfil">Deixe os campos em branco para manter sua senha atual.</p>
      <div class="campos-perfil">
        <label>Nova senha
          <input type="password" name="nova_senha" minlength="8" maxlength="72" autocomplete="new-password">
        </label>
        <label>Confirmar nova senha
          <input type="password" name="confirmar_senha" minlength="8" maxlength="72" autocomplete="new-password">
        </label>
      </div>
    </section>

    <section class="cartao-perfil">
      <h2><i class="fa-solid fa-layer-group"></i> Áreas autorizadas</h2>
      <p class="ajuda-perfil">Selecione pelo menos uma área. Ela define as perguntas que você poderá administrar.</p>
      <div class="grade-areas-perfil">
        <?php foreach ($areasAtivas as $area): ?>
          <label class="opcao-area-perfil">
            <input type="checkbox" name="areas[]" value="<?php echo (int) $area['id']; ?>"<?php echo in_array((int) $area['id'], $areasSelecionadas, true) ? ' checked' : ''; ?>>
            <span><i class="<?php echo escapar((string) ($area['icone'] ?? 'fa-solid fa-layer-group')); ?>"></i><?php echo escapar((string) $area['nome']); ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </section>

    <div class="acoes-perfil">
      <a href="/github/desafiodomilhao/public/admin/perguntas.php">CANCELAR</a>
      <button type="submit"><i class="fa-solid fa-floppy-disk"></i> SALVAR ALTERAÇÕES</button>
    </div>
  </form>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
