<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/autenticacao.php';
require_once __DIR__ . '/../includes/repositories/PerguntaRepository.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: /desafiodomilhao/admin/perguntas.php', true, 303);
    exit;
}

$usuarioId = (int) ($_SESSION['usuario']['id'] ?? 0);
$perguntaId = filter_var($_POST['pergunta_id'] ?? null, FILTER_VALIDATE_INT);

if ($usuarioId <= 0 || $perguntaId === false || $perguntaId === null || $perguntaId <= 0) {
    $_SESSION['erro_pergunta'] = 'Não foi possível identificar a questão para exclusão.';
    header('Location: /desafiodomilhao/admin/perguntas.php', true, 303);
    exit;
}

try {
    $repository = new PerguntaRepository();
    $pergunta = $repository->buscarPorIdAutorizado((int) $perguntaId, $usuarioId);

    if ($pergunta === null) {
        $_SESSION['erro_pergunta'] = 'Você não possui acesso a esta questão.';
    } else {
        $repository->excluir((int) $perguntaId);
        $_SESSION['sucesso_pergunta'] = 'A questão foi excluída com sucesso.';
    }
} catch (Throwable $erro) {
    error_log('[Desafio do Milhão - Exclusão de pergunta] ' . $erro->getMessage());
    $_SESSION['erro_pergunta'] = 'Não foi possível excluir a questão. Tente novamente.';
}

header('Location: /desafiodomilhao/admin/perguntas.php', true, 303);
exit;
