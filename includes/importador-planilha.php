<?php

declare(strict_types=1);

function lerPlanilhaImportacao(string $arquivo, string $extensao): array
{
    if ($extensao === 'csv') {
        $linhas = [];
        $manipulador = fopen($arquivo, 'rb');

        if ($manipulador === false) {
            throw new RuntimeException('Não foi possível ler o arquivo CSV.');
        }

        while (($linha = fgetcsv($manipulador, 0, ';')) !== false) {
            if (count($linha) === 1) {
                $linha = str_getcsv((string) $linha[0], ',');
            }
            $linhas[] = $linha;
        }

        fclose($manipulador);
        return $linhas;
    }

    if ($extensao !== 'xlsx' || !class_exists('ZipArchive')) {
        throw new RuntimeException('Envie uma planilha .xlsx ou um arquivo .csv válido.');
    }

    $zip = new ZipArchive();
    if ($zip->open($arquivo) !== true) {
        throw new RuntimeException('Não foi possível abrir a planilha enviada.');
    }

    $compartilhadas = [];
    $xmlCompartilhadas = $zip->getFromName('xl/sharedStrings.xml');
    if ($xmlCompartilhadas !== false) {
        $xml = simplexml_load_string($xmlCompartilhadas);
        $namespaces = $xml?->getNamespaces(true) ?? [];
        $namespace = $namespaces[''] ?? $namespaces['x'] ?? '';
        if ($xml && $namespace !== '') {
            $xml->registerXPathNamespace('x', $namespace);
            foreach ($xml->xpath('//x:si') ?: [] as $item) {
                $compartilhadas[] = implode('', array_map(
                    static fn (SimpleXMLElement $texto): string => (string) $texto,
                    $item->xpath('.//x:t') ?: []
                ));
            }
        }
    }

    $xmlPlanilha = $zip->getFromName('xl/worksheets/sheet1.xml');
    $zip->close();

    if ($xmlPlanilha === false) {
        throw new RuntimeException('A primeira aba da planilha não foi encontrada.');
    }

    $xml = simplexml_load_string($xmlPlanilha);
    $namespaces = $xml?->getNamespaces(true) ?? [];
    $namespace = $namespaces[''] ?? $namespaces['x'] ?? '';
    if (!$xml instanceof SimpleXMLElement || $namespace === '') {
        throw new RuntimeException('A planilha possui uma estrutura inválida.');
    }

    $xml->registerXPathNamespace('x', $namespace);
    $linhas = [];

    foreach ($xml->xpath('//x:sheetData/x:row') ?: [] as $linhaXml) {
        $linha = [];
        foreach ($linhaXml->xpath('x:c') ?: [] as $celula) {
            $celula->registerXPathNamespace('x', $namespace);
            $atributos = $celula->attributes();
            $referencia = (string) ($atributos['r'] ?? 'A1');
            preg_match('/[A-Z]+/', $referencia, $encontrado);
            $indice = colunaPlanilhaParaIndice($encontrado[0] ?? 'A');
            $tipo = (string) ($atributos['t'] ?? '');
            $valor = '';

            if ($tipo === 's') {
                $valores = $celula->xpath('x:v') ?: [];
                $valor = $compartilhadas[(int) ($valores[0] ?? 0)] ?? '';
            } elseif ($tipo === 'inlineStr') {
                $valor = implode('', array_map(
                    static fn (SimpleXMLElement $texto): string => (string) $texto,
                    $celula->xpath('.//x:t') ?: []
                ));
            } else {
                $valores = $celula->xpath('x:v') ?: [];
                $valor = (string) ($valores[0] ?? '');
            }

            $linha[$indice] = trim($valor);
        }

        if (!empty($linha)) {
            ksort($linha);
            $linhas[] = array_values($linha);
        }
    }

    return $linhas;
}

function colunaPlanilhaParaIndice(string $coluna): int
{
    $indice = 0;
    foreach (str_split($coluna) as $letra) {
        $indice = ($indice * 26) + (ord($letra) - 64);
    }
    return $indice - 1;
}

function normalizarCabecalhoPlanilha(string $texto): string
{
    $texto = preg_replace('/^\xEF\xBB\xBF/', '', $texto) ?? $texto;
    $convertido = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
    $texto = strtolower($convertido === false ? $texto : $convertido);
    return preg_replace('/[^a-z0-9]+/', '_', trim($texto)) ?? '';
}
