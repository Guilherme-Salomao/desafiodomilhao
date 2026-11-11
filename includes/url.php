<?php

declare(strict_types=1);

/* ============================================================
   URL BASE DO PROJETO

   O sistema pode estar em /desafiodomilhao/public no XAMPP ou
   em /github/desafiodomilhao/public na produção.
============================================================ */

function baseUrlProjeto(): string
{
    static $baseUrl = null;

    if ($baseUrl !== null) {
        return $baseUrl;
    }

    $candidatos = [
        (string) ($_SERVER['SCRIPT_NAME'] ?? ''),
        (string) ($_SERVER['REQUEST_URI'] ?? ''),
    ];

    foreach ($candidatos as $candidato) {
        if (preg_match('#^(.*/public)(?:/|$)#', $candidato, $resultado)) {
            $baseUrl = rtrim($resultado[1], '/');
            return $baseUrl;
        }
    }

    return $baseUrl = '/github/desafiodomilhao/public';
}

function urlProjeto(string $caminho = ''): string
{
    return baseUrlProjeto() .
        ($caminho === '' ? '/' : '/' . ltrim($caminho, '/'));
}
