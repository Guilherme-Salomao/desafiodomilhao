<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/autenticacao.php';
require_once __DIR__ . '/../includes/repositories/UsuarioRepository.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /github/desafiodomilhao/public/admin/perfil.php', true, 303);
    exit;
}

$usuarioId = (int) ($_SESSION['usuario']['id'] ?? 0);
$nome = trim((string) ($_POST['nome'] ?? ''));
$novaSenha = (string) ($_POST['nova_senha'] ?? '');
$confirmarSenha = (string) ($_POST['confirmar_senha'] ?? '');
$areasIds = is_array($_POST['areas'] ?? null) ? $_POST['areas'] : [];

if ($usuarioId <= 0 || $nome === '' || strlen($nome) > 150) {
    $_SESSION['erro_perfil'] = 'Informe um nome válido.';
    header('Location: /github/desafiodomilhao/public/admin/perfil.php', true, 303);
    exit;
}

if (empty($areasIds)) {
    $_SESSION['erro_perfil'] = 'Selecione pelo menos uma área autorizada.';
    header('Location: /github/desafiodomilhao/public/admin/perfil.php', true, 303);
    exit;
}

$senhaHash = null;

if ($novaSenha !== '' || $confirmarSenha !== '') {
    if ($novaSenha !== $confirmarSenha) {
        $_SESSION['erro_perfil'] = 'A confirmação da nova senha não confere.';
        header('Location: /github/desafiodomilhao/public/admin/perfil.php', true, 303);
        exit;
    }

    if (
        strlen($novaSenha) < 8 ||
        strlen($novaSenha) > 72 ||
        !preg_match('/[A-Z]/', $novaSenha) ||
        !preg_match('/[a-z]/', $novaSenha) ||
        !preg_match('/\\d/', $novaSenha)
    ) {
        $_SESSION['erro_perfil'] = 'A senha deve ter de 8 a 72 caracteres, com letras maiúscula, minúscula e número.';
        header('Location: /github/desafiodomilhao/public/admin/perfil.php', true, 303);
        exit;
    }

    $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);

    if ($senhaHash === false) {
        $_SESSION['erro_perfil'] = 'Não foi possível proteger a nova senha.';
        header('Location: /github/desafiodomilhao/public/admin/perfil.php', true, 303);
        exit;
    }
}

try {
    $usuarioRepository = new UsuarioRepository();
    $areas = $usuarioRepository->atualizarPerfil(
        $usuarioId,
        $nome,
        $senhaHash,
        $areasIds
    );

    $_SESSION['usuario']['nome'] = $nome;
    $_SESSION['usuario']['areas'] = array_map(
        static fn (array $area): array => [
            'id' => (int) $area['id'],
            'nome' => (string) $area['nome'],
            'slug' => (string) $area['slug'],
            'icone' => (string) $area['icone'],
        ],
        $areas
    );
    $_SESSION['sucesso_perfil'] = 'Perfil atualizado com sucesso.';
} catch (Throwable $erro) {
    error_log('[Desafio do Milhão - Perfil] ' . $erro->getMessage());
    $_SESSION['erro_perfil'] = 'Não foi possível atualizar o perfil. Verifique as áreas selecionadas.';
}

header('Location: /github/desafiodomilhao/public/admin/perfil.php', true, 303);
exit;
