<?php

declare(strict_types=1);

/*
 * Roteador público: mantém controladores e configurações fora de public/.
 */

$raizProjeto = dirname(__DIR__);
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
