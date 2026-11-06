<?php

declare (strict_types = 1);

/* ============================================================
   DESAFIO DO MILHÃO
   CONEXÃO SEGURA COM MYSQL UTILIZANDO PDO
============================================================ */

/* ============================================================
   CAMINHO DO ARQUIVO DE CONFIGURAÇÃO
============================================================ */

$configPath = 'D:/xampp/config/desafiodomilhao-db.php';

/* ============================================================
   VERIFICA SE O ARQUIVO EXISTE
============================================================ */

if (! file_exists($configPath)) {

    error_log(
        '[Desafio do Milhão] Arquivo de configuração do banco não encontrado: ' .
        $configPath
    );

    throw new RuntimeException(
        'Configuração do banco de dados não encontrada.'
    );
}

/* ============================================================
   CARREGA CONFIGURAÇÕES
============================================================ */

$config = require $configPath;

/* ============================================================
   VALIDA ESTRUTURA DA CONFIGURAÇÃO
============================================================ */

if (! is_array($config)) {

    error_log(
        '[Desafio do Milhão] O arquivo de configuração do banco não retornou um array válido.'
    );

    throw new RuntimeException(
        'Configuração do banco de dados inválida.'
    );
}

/* ============================================================
   CAMPOS OBRIGATÓRIOS
============================================================ */

$camposObrigatorios = [
    'host',
    'port',
    'database',
    'username',
    'password',
    'charset',
    'options',
];

foreach ($camposObrigatorios as $campo) {

    if (! array_key_exists($campo, $config)) {

        error_log(
            '[Desafio do Milhão] Campo ausente na configuração do banco: ' .
            $campo
        );

        throw new RuntimeException(
            'Configuração do banco de dados incompleta.'
        );
    }
}

/* ============================================================
   FUNÇÃO PRINCIPAL DE CONEXÃO
============================================================ */

function conectarBanco(): PDO
{
    static $pdo = null;

    /*
     * Se já existe uma conexão nesta requisição,
     * reutiliza a mesma.
     */
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    /* ========================================================
       CARREGA A CONFIGURAÇÃO EXTERNA
    ======================================================== */

    $configPath = 'D:/xampp/config/desafiodomilhao-db.php';

    if (! file_exists($configPath)) {

        error_log(
            '[Desafio do Milhão] Arquivo de configuração do banco não encontrado.'
        );

        throw new RuntimeException(
            'Não foi possível acessar as configurações do banco.'
        );
    }

    $config = require $configPath;

    /* ========================================================
       MONTA DSN
    ======================================================== */

    $dsn =
        'mysql:' .
        'host=' . $config['host'] . ';' .
        'port=' . $config['port'] . ';' .
        'dbname=' . $config['database'] . ';' .
        'charset=' . $config['charset'];

    /* ========================================================
       CRIA CONEXÃO PDO
    ======================================================== */

    try {

        $pdo = new PDO(
            $dsn,
            $config['username'],
            $config['password'],
            $config['options']
        );

        return $pdo;

    } catch (PDOException $erro) {

        /*
         * Detalhes ficam somente no log do servidor.
         */

        error_log(
            '[Desafio do Milhão - Erro PDO] ' .
            $erro->getMessage()
        );

        /*
         * Não expomos detalhes técnicos ao usuário.
         */

        throw new RuntimeException(
            'Não foi possível conectar ao banco de dados.'
        );
    }
}
