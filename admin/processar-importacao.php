<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/autenticacao.php';
require_once __DIR__ . '/../includes/importador-planilha.php';
require_once __DIR__ . '/../includes/repositories/UsuarioRepository.php';
require_once __DIR__ . '/../includes/repositories/PerguntaRepository.php';

function retornarImportacaoComErro(array $erros, ?string $arquivo = null): never
{
    $_SESSION['erros_importacao'] = array_slice($erros, 0, 12);
    $_SESSION['arquivo_importacao'] = $arquivo;
    header('Location: /github/desafiodomilhao/public/admin/importar-perguntas.php', true, 303);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: /github/desafiodomilhao/public/admin/importar-perguntas.php', true, 303);
    exit;
}

$usuarioId = (int) ($_SESSION['usuario']['id'] ?? 0);
$upload = $_FILES['arquivo_planilha'] ?? null;

if ($usuarioId <= 0 || !is_array($upload) || ($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    retornarImportacaoComErro(['Selecione uma planilha válida para importar.']);
}

$nomeArquivo = basename((string) ($upload['name'] ?? 'planilha'));
$extensao = strtolower(pathinfo($nomeArquivo, PATHINFO_EXTENSION));

if (!in_array($extensao, ['xlsx', 'csv'], true)) {
    retornarImportacaoComErro(['Envie um arquivo no formato XLSX ou CSV.'], $nomeArquivo);
}

try {
    $linhas = lerPlanilhaImportacao((string) $upload['tmp_name'], $extensao);
    if (count($linhas) < 2) {
        throw new RuntimeException('A planilha deve conter ao menos uma pergunta, além do cabeçalho.');
    }

    $cabecalhos = array_map('normalizarCabecalhoPlanilha', array_shift($linhas));
    $esperados = ['area', 'dificuldade', 'pergunta', 'alternativa_a', 'alternativa_b', 'alternativa_c', 'alternativa_d', 'resposta_correta', 'explicacao', 'ativa'];
    $indices = array_flip($cabecalhos);
    foreach ($esperados as $cabecalho) {
        if (!array_key_exists($cabecalho, $indices)) {
            throw new RuntimeException('Coluna obrigatória ausente: ' . $cabecalho . '. Baixe novamente o modelo.');
        }
    }

    $usuarioRepository = new UsuarioRepository();
    $areasPermitidas = [];
    foreach ($usuarioRepository->listarAreasDoUsuario($usuarioId) as $area) {
        $areasPermitidas[normalizarCabecalhoPlanilha((string) $area['slug'])] = (int) $area['id'];
    }

    $questoes = [];
    $erros = [];
    foreach ($linhas as $numero => $linha) {
        $pegar = static fn (string $coluna): string => trim((string) ($linha[$indices[$coluna]] ?? ''));
        if (implode('', $linha) === '') { continue; }

        $areaSlug = normalizarCabecalhoPlanilha($pegar('area'));
        $dificuldade = normalizarCabecalhoPlanilha($pegar('dificuldade'));
        $dificuldade = $dificuldade === 'media' ? 'medio' : $dificuldade;
        $pergunta = $pegar('pergunta');
        $alternativas = ['A' => $pegar('alternativa_a'), 'B' => $pegar('alternativa_b'), 'C' => $pegar('alternativa_c'), 'D' => $pegar('alternativa_d')];
        $resposta = strtoupper($pegar('resposta_correta'));
        $explicacao = $pegar('explicacao');
        $ativa = normalizarCabecalhoPlanilha($pegar('ativa'));
        $linhaNumero = $numero + 2;

        if (!isset($areasPermitidas[$areaSlug])) { $erros[] = "Linha {$linhaNumero}: área não autorizada ou inexistente."; continue; }
        if (!in_array($dificuldade, ['facil', 'medio', 'dificil'], true)) { $erros[] = "Linha {$linhaNumero}: dificuldade deve ser facil, medio ou dificil."; continue; }
        if (mb_strlen($pergunta) < 10 || mb_strlen($pergunta) > 1000) { $erros[] = "Linha {$linhaNumero}: pergunta deve ter de 10 a 1000 caracteres."; continue; }
        if (mb_strlen($explicacao) < 5 || mb_strlen($explicacao) > 1500) { $erros[] = "Linha {$linhaNumero}: explicação deve ter de 5 a 1500 caracteres."; continue; }
        if (!in_array($resposta, ['A', 'B', 'C', 'D'], true)) { $erros[] = "Linha {$linhaNumero}: resposta correta deve ser A, B, C ou D."; continue; }
        if (count(array_unique(array_map(static fn (string $valor): string => mb_strtolower($valor), $alternativas))) !== 4 || in_array('', $alternativas, true) || max(array_map('mb_strlen', $alternativas)) > 255) { $erros[] = "Linha {$linhaNumero}: preencha quatro alternativas diferentes de até 255 caracteres."; continue; }

        $questoes[] = [
            'area_id' => $areasPermitidas[$areaSlug], 'dificuldade' => $dificuldade,
            'pergunta' => $pergunta, 'alternativas' => $alternativas,
            'resposta_correta' => $resposta, 'explicacao' => $explicacao,
            'ativo' => in_array($ativa, ['nao', 'n', '0', 'inativa'], true) ? 0 : 1,
        ];
    }

    if (!empty($erros) || empty($questoes)) {
        retornarImportacaoComErro(empty($erros) ? ['Nenhuma pergunta válida foi encontrada.'] : $erros, $nomeArquivo);
    }

    $quantidade = (new PerguntaRepository())->importarEmLote($usuarioId, $questoes);
    $_SESSION['sucesso_pergunta'] = "{$quantidade} questão(ões) importada(s) com sucesso.";
    header('Location: /github/desafiodomilhao/public/admin/perguntas.php', true, 303);
    exit;
} catch (Throwable $erro) {
    error_log('[Desafio do Milhão - Importação] ' . $erro->getMessage());
    retornarImportacaoComErro([$erro->getMessage()], $nomeArquivo);
}
