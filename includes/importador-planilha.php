<?php

declare(strict_types=1);


/* ============================================================
   LEITURA DA PLANILHA
============================================================ */

function lerPlanilhaImportacao(
    string $arquivo,
    string $extensao
): array {

    $extensao =
        strtolower(
            trim($extensao)
        );


    /* ========================================================
       CSV
    ======================================================== */

    if ($extensao === 'csv') {

        return lerArquivoCsvImportacao(
            $arquivo
        );
    }


    /* ========================================================
       XLSX
    ======================================================== */

    if (
        $extensao !== 'xlsx' ||
        !class_exists('ZipArchive')
    ) {

        throw new RuntimeException(
            'Envie uma planilha .xlsx ou um arquivo .csv válido.'
        );
    }


    return lerArquivoXlsxImportacao(
        $arquivo
    );
}


/* ============================================================
   CSV
============================================================ */

function lerArquivoCsvImportacao(
    string $arquivo
): array {

    $linhas = [];


    $manipulador =
        fopen(
            $arquivo,
            'rb'
        );


    if ($manipulador === false) {

        throw new RuntimeException(
            'Não foi possível ler o arquivo CSV.'
        );
    }


    while (
        (
            $linha =
                fgetcsv(
                    $manipulador,
                    0,
                    ';'
                )
        ) !== false
    ) {


        /*
         * Algumas planilhas CSV utilizam vírgula.
         *
         * Se a leitura usando ponto e vírgula retornar
         * somente uma coluna, tentamos novamente com vírgula.
         */

        if (count($linha) === 1) {

            $linha =
                str_getcsv(
                    (string) $linha[0],
                    ','
                );
        }


        $linha =
            array_map(
                static function ($valor): string {

                    return trim(
                        (string) $valor
                    );
                },
                $linha
            );


        $linhas[] =
            $linha;
    }


    fclose(
        $manipulador
    );


    return $linhas;
}


/* ============================================================
   XLSX
============================================================ */

function lerArquivoXlsxImportacao(
    string $arquivo
): array {

    $zip =
        new ZipArchive();


    if (
        $zip->open(
            $arquivo
        ) !== true
    ) {

        throw new RuntimeException(
            'Não foi possível abrir a planilha enviada.'
        );
    }


    try {


        /* ====================================================
           STRINGS COMPARTILHADAS
        ==================================================== */

        $compartilhadas =
            lerStringsCompartilhadasXlsx(
                $zip
            );


        /* ====================================================
           LOCALIZA PRIMEIRA PLANILHA
        ==================================================== */

        $caminhoPlanilha =
            localizarPrimeiraPlanilhaXlsx(
                $zip
            );


        if ($caminhoPlanilha === null) {

            throw new RuntimeException(
                'A primeira aba da planilha não foi encontrada.'
            );
        }


        $xmlPlanilha =
            $zip->getFromName(
                $caminhoPlanilha
            );


        if ($xmlPlanilha === false) {

            throw new RuntimeException(
                'Não foi possível ler a primeira aba da planilha.'
            );
        }


        /* ====================================================
           CARREGA XML
        ==================================================== */

        libxml_use_internal_errors(
            true
        );


        $xml =
            simplexml_load_string(
                $xmlPlanilha
            );


        if (
            !$xml instanceof SimpleXMLElement
        ) {

            libxml_clear_errors();

            throw new RuntimeException(
                'A planilha possui uma estrutura XML inválida.'
            );
        }


        libxml_clear_errors();


        /* ====================================================
           BUSCA LINHAS INDEPENDENTEMENTE DO NAMESPACE

           O uso de local-name() permite ler arquivos gerados
           pelo Excel, LibreOffice e outras bibliotecas XLSX.
        ==================================================== */

        $linhasXml =
            $xml->xpath(
                '//*[local-name()="sheetData"]/*[local-name()="row"]'
            )
            ?: [];


        $linhas =
            [];


        foreach (
            $linhasXml as $linhaXml
        ) {


            $linha =
                [];


            $celulas =
                $linhaXml->xpath(
                    './*[local-name()="c"]'
                )
                ?: [];


            foreach (
                $celulas as $celula
            ) {


                /* ============================================
                   REFERÊNCIA DA CÉLULA
                   Ex.: A1, B1, C2...
                ============================================ */

                $atributos =
                    $celula->attributes();


                $referencia =
                    (string) (
                        $atributos['r']
                        ?? ''
                    );


                if (
                    !preg_match(
                        '/^([A-Z]+)([0-9]+)$/i',
                        $referencia,
                        $partesReferencia
                    )
                ) {

                    continue;
                }


                $coluna =
                    strtoupper(
                        $partesReferencia[1]
                    );


                $indice =
                    colunaPlanilhaParaIndice(
                        $coluna
                    );


                /* ============================================
                   TIPO
                ============================================ */

                $tipo =
                    (string) (
                        $atributos['t']
                        ?? ''
                    );


                /* ============================================
                   VALOR
                ============================================ */

                $valor =
                    lerValorCelulaXlsx(
                        $celula,
                        $tipo,
                        $compartilhadas
                    );


                $linha[$indice] =
                    trim(
                        $valor
                    );
            }


            /* =================================================
               IGNORA LINHA TOTALMENTE VAZIA
            ================================================= */

            if (empty($linha)) {

                continue;
            }


            $temConteudo =
                false;


            foreach (
                $linha as $valorLinha
            ) {

                if (
                    trim(
                        (string) $valorLinha
                    ) !== ''
                ) {

                    $temConteudo =
                        true;

                    break;
                }
            }


            if (!$temConteudo) {

                continue;
            }


            /* =================================================
               IMPORTANTE:

               Não usamos simplesmente array_values().

               Em XLSX, células vazias podem nem existir no XML.
               Isso faria as colunas seguintes "andarem para
               esquerda".

               Exemplo:

               A = área
               B = dificuldade
               C = pergunta
               D vazia
               E = alternativa B

               Sem preencher os espaços, E viraria D.
            ================================================= */

            $maiorIndice =
                max(
                    array_keys(
                        $linha
                    )
                );


            $linhaCompleta =
                [];


            for (
                $i = 0;
                $i <= $maiorIndice;
                $i++
            ) {

                $linhaCompleta[$i] =
                    isset(
                        $linha[$i]
                    )
                        ? (string) $linha[$i]
                        : '';
            }


            $linhas[] =
                $linhaCompleta;
        }


        return $linhas;


    } finally {


        $zip->close();
    }
}


/* ============================================================
   VALOR DA CÉLULA XLSX
============================================================ */

function lerValorCelulaXlsx(
    SimpleXMLElement $celula,
    string $tipo,
    array $compartilhadas
): string {


    /* ========================================================
       STRING COMPARTILHADA
    ======================================================== */

    if ($tipo === 's') {


        $valores =
            $celula->xpath(
                './*[local-name()="v"]'
            )
            ?: [];


        if (empty($valores)) {

            return '';
        }


        $indiceCompartilhado =
            (int) (
                (string) $valores[0]
            );


        return (string) (
            $compartilhadas[
                $indiceCompartilhado
            ]
            ?? ''
        );
    }


    /* ========================================================
       STRING INLINE

       É comum em arquivos gerados por bibliotecas PHP,
       Python, JavaScript etc.
    ======================================================== */

    if ($tipo === 'inlineStr') {


        $textos =
            $celula->xpath(
                './/*[local-name()="t"]'
            )
            ?: [];


        $valor =
            '';


        foreach (
            $textos as $texto
        ) {

            $valor .=
                (string) $texto;
        }


        return $valor;
    }


    /* ========================================================
       STRING XML DIRETA
    ======================================================== */

    if ($tipo === 'str') {


        $valores =
            $celula->xpath(
                './*[local-name()="v"]'
            )
            ?: [];


        return empty($valores)
            ? ''
            : (string) $valores[0];
    }


    /* ========================================================
       BOOLEAN
    ======================================================== */

    if ($tipo === 'b') {


        $valores =
            $celula->xpath(
                './*[local-name()="v"]'
            )
            ?: [];


        $valor =
            empty($valores)
                ? ''
                : (string) $valores[0];


        return $valor === '1'
            ? '1'
            : '0';
    }


    /* ========================================================
       NÚMERO / VALOR PADRÃO
    ======================================================== */

    $valores =
        $celula->xpath(
            './*[local-name()="v"]'
        )
        ?: [];


    if (!empty($valores)) {

        return (string) $valores[0];
    }


    /* ========================================================
       FALLBACK

       Alguns geradores colocam texto dentro da célula sem
       utilizar exatamente a estrutura tradicional do Excel.
    ======================================================== */

    $textos =
        $celula->xpath(
            './/*[local-name()="t"]'
        )
        ?: [];


    if (!empty($textos)) {


        $valor =
            '';


        foreach (
            $textos as $texto
        ) {

            $valor .=
                (string) $texto;
        }


        return $valor;
    }


    return '';
}


/* ============================================================
   STRINGS COMPARTILHADAS XLSX
============================================================ */

function lerStringsCompartilhadasXlsx(
    ZipArchive $zip
): array {

    $compartilhadas =
        [];


    $xmlCompartilhadas =
        $zip->getFromName(
            'xl/sharedStrings.xml'
        );


    /*
     * sharedStrings.xml não é obrigatório.
     *
     * Arquivos que utilizam inlineStr normalmente
     * não possuem esse arquivo.
     */

    if (
        $xmlCompartilhadas === false
    ) {

        return [];
    }


    libxml_use_internal_errors(
        true
    );


    $xml =
        simplexml_load_string(
            $xmlCompartilhadas
        );


    if (
        !$xml instanceof SimpleXMLElement
    ) {

        libxml_clear_errors();

        return [];
    }


    libxml_clear_errors();


    $itens =
        $xml->xpath(
            '//*[local-name()="si"]'
        )
        ?: [];


    foreach (
        $itens as $item
    ) {


        $textos =
            $item->xpath(
                './/*[local-name()="t"]'
            )
            ?: [];


        $valor =
            '';


        foreach (
            $textos as $texto
        ) {

            $valor .=
                (string) $texto;
        }


        $compartilhadas[] =
            $valor;
    }


    return $compartilhadas;
}


/* ============================================================
   LOCALIZA A PRIMEIRA ABA DO XLSX
============================================================ */

function localizarPrimeiraPlanilhaXlsx(
    ZipArchive $zip
): ?string {


    /*
     * Na grande maioria das planilhas:
     *
     * xl/worksheets/sheet1.xml
     *
     * Primeiro tentamos diretamente para manter o processo
     * rápido.
     */

    if (
        $zip->locateName(
            'xl/worksheets/sheet1.xml'
        ) !== false
    ) {

        return 'xl/worksheets/sheet1.xml';
    }


    /*
     * Fallback:
     *
     * procura a primeira worksheet disponível.
     */

    $planilhas =
        [];


    for (
        $i = 0;
        $i < $zip->numFiles;
        $i++
    ) {


        $nome =
            $zip->getNameIndex(
                $i
            );


        if (
            !is_string($nome)
        ) {

            continue;
        }


        if (
            preg_match(
                '#^xl/worksheets/sheet([0-9]+)\.xml$#i',
                $nome,
                $encontrado
            )
        ) {


            $numero =
                (int) $encontrado[1];


            $planilhas[$numero] =
                $nome;
        }
    }


    if (empty($planilhas)) {

        return null;
    }


    ksort(
        $planilhas,
        SORT_NUMERIC
    );


    return reset(
        $planilhas
    ) ?: null;
}


/* ============================================================
   CONVERTE COLUNA EXCEL PARA ÍNDICE
============================================================ */

function colunaPlanilhaParaIndice(
    string $coluna
): int {

    $coluna =
        strtoupper(
            trim($coluna)
        );


    $indice =
        0;


    foreach (
        str_split(
            $coluna
        ) as $letra
    ) {


        $codigo =
            ord($letra);


        if (
            $codigo < 65 ||
            $codigo > 90
        ) {

            continue;
        }


        $indice =
            ($indice * 26) +
            ($codigo - 64);
    }


    return max(
        0,
        $indice - 1
    );
}


/* ============================================================
   NORMALIZA CABEÇALHO
============================================================ */

function normalizarCabecalhoPlanilha(
    string $texto
): string {


    /* ========================================================
       REMOVE BOM UTF-8
    ======================================================== */

    $texto =
        preg_replace(
            '/^\xEF\xBB\xBF/',
            '',
            $texto
        )
        ?? $texto;


    /* ========================================================
       REMOVE ESPAÇOS INVISÍVEIS
    ======================================================== */

    $texto =
        str_replace(
            [
                "\xC2\xA0",
                "\r",
                "\n",
                "\t",
            ],
            [
                ' ',
                ' ',
                ' ',
                ' ',
            ],
            $texto
        );


    $texto =
        trim(
            $texto
        );


    /* ========================================================
       REMOVE ACENTOS
    ======================================================== */

    $convertido =
        iconv(
            'UTF-8',
            'ASCII//TRANSLIT//IGNORE',
            $texto
        );


    if (
        $convertido !== false
    ) {

        $texto =
            $convertido;
    }


    /* ========================================================
       MINÚSCULAS
    ======================================================== */

    $texto =
        strtolower(
            $texto
        );


    /* ========================================================
       NORMALIZA CARACTERES
    ======================================================== */

    $texto =
        preg_replace(
            '/[^a-z0-9]+/',
            '_',
            $texto
        )
        ?? '';


    return trim(
        $texto,
        '_'
    );
}