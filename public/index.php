<?php

declare(strict_types=1);

/*
 * Roteador público: mantém controladores e configurações fora de public/.
 */

$raizProjeto = dirname(__DIR__);
$arquivoUrl = $raizProjeto . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'url.php';

require_once $arquivoUrl;

$baseUrlProjeto = baseUrlProjeto();

/*
 * Mantém os templates legados compatíveis enquanto suas URLs são
 * gradualmente centralizadas. Também ajusta Location em fluxos POST.
 */
header_register_callback(static function () use ($baseUrlProjeto): void {
    foreach (headers_list() as $cabecalho) {
        $prefixo = 'Location: /github/desafiodomilhao/public';

        if (str_starts_with($cabecalho, $prefixo)) {
            header_remove('Location');
            header(
                'Location: ' .
                $baseUrlProjeto .
                substr($cabecalho, strlen($prefixo))
            );
            break;
        }
    }
});

ob_start(static function (string $conteudo) use ($baseUrlProjeto): string {
    if ($baseUrlProjeto === '/github/desafiodomilhao/public') {
        return $conteudo;
    }

    return preg_replace(
        '#(?<![A-Za-z0-9.:-])/github/desafiodomilhao/public#',
        $baseUrlProjeto,
        $conteudo
    ) ?? $conteudo;
});

$rota = trim((string) ($_GET['route'] ?? ''), '/');
$rota = $rota === '' ? 'index.php' : $rota;

if (
    ! preg_match('/^[a-zA-Z0-9_\/-]+\.php$/', $rota) ||
    preg_match('#^(?:includes|config|database|dados)/#', $rota)
) {
    http_response_code(404);
    exit('Página não encontrada.');
}

$arquivo = realpath($raizProjeto . DIRECTORY_SEPARATOR . $rota);
$prefixoRaiz = realpath($raizProjeto) . DIRECTORY_SEPARATOR;

if (
    $arquivo === false ||
    ! str_starts_with($arquivo, $prefixoRaiz) ||
    ! is_file($arquivo)
) {
    http_response_code(404);
    exit('Página não encontrada.');
}

require $arquivo;
