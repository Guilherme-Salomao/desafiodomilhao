<?php

declare(strict_types=1);

session_start();

header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido.']);
    exit;
}

$ajuda = trim((string) ($_POST['ajuda'] ?? ''));
$permitidas = ['cinquenta', 'tecnico', 'turma', 'trocar'];

if (! in_array($ajuda, $permitidas, true) || ! isset($_SESSION['partida'])) {
    http_response_code(400);
    echo json_encode(['erro' => 'Ajuda inválida.']);
    exit;
}

$_SESSION['partida']['ajudas'] ??= [];

if (! empty($_SESSION['partida']['ajudas'][$ajuda])) {
    http_response_code(409);
    echo json_encode(['erro' => 'Esta ajuda já foi utilizada.']);
    exit;
}

$_SESSION['partida']['ajudas'][$ajuda] = true;

$resposta = ['sucesso' => true];

if ($ajuda === 'cinquenta') {
    $respostaCorreta = (string) (
        $_SESSION['partida']['resposta_correta_atual'] ?? ''
    );

    if (! in_array($respostaCorreta, ['A', 'B', 'C', 'D'], true)) {
        http_response_code(400);
        echo json_encode(['erro' => 'Não foi possível preparar o sorteio desta questão.']);
        exit;
    }

    $combinacoes = [
        ['A', 'B'], ['A', 'C'], ['A', 'D'],
        ['B', 'C'], ['B', 'D'], ['C', 'D'],
    ];

    $ultimoSorteio = (string) ($_SESSION['ultimo_sorteio_5050'] ?? '');

    $combinacoesDisponiveis = array_values(array_filter(
        $combinacoes,
        static function (array $combinacao) use ($ultimoSorteio, $respostaCorreta): bool {
            return
                ! in_array($respostaCorreta, $combinacao, true) &&
                implode('', $combinacao) !== $ultimoSorteio;
        }
    ));

    if (empty($combinacoesDisponiveis)) {
        $combinacoesDisponiveis = array_values(array_filter(
            $combinacoes,
            static function (array $combinacao) use ($respostaCorreta): bool {
                return ! in_array($respostaCorreta, $combinacao, true);
            }
        ));
    }

    $sorteio = $combinacoesDisponiveis[
        array_rand($combinacoesDisponiveis)
    ];

    $_SESSION['ultimo_sorteio_5050'] = implode('', $sorteio);
    $_SESSION['partida']['ajudas']['cinquenta_sorteio'] = $sorteio;
    $resposta['removidas'] = $sorteio;
}

echo json_encode($resposta);
