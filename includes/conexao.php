<?php

declare (strict_types = 1);

/* ============================================================
   DESAFIO DO MILHÃO
   CONEXÃO SEGURA COM MYSQL UTILIZANDO PDO
============================================================ */

/* ============================================================
   CAMINHO DO ARQUIVO DE CONFIGURAÇÃO
============================================================ */

$configPath = getenv('APP_DB_CONFIG') ?: dirname(__DIR__) . '/config/database.php';

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
   SELECIONA O AMBIENTE DE EXECUÇÃO
============================================================ */

function resolverConfiguracaoBanco(array $config): array
{
    /*
     * Mantém compatibilidade com o formato antigo, que continha
     * diretamente host, database, username e password.
     */
    if (!isset($config['environments'])) {
        return $config;
    }

    $ambiente = strtolower(trim((string) getenv('APP_ENV')));

    if ($ambiente === '') {
        $host = strtolower((string) (
            $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? ''
        ));

        $host = preg_replace('/:\\d+$/', '', $host) ?? $host;
        $ambiente = in_array(
            $host,
            ['localhost', '127.0.0.1', '::1'],
            true
        )
            ? 'local'
            : 'production';
    }

    $aliases = [
        'dev' => 'local',
        'development' => 'local',
        'prod' => 'production',
        'production' => 'production',
        'local' => 'local',
    ];

    $ambiente = $aliases[$ambiente] ?? $ambiente;

    if (!isset($config['environments'][$ambiente])) {
        throw new RuntimeException(
            'Ambiente de banco de dados não configurado: ' . $ambiente
        );
    }

    $ambienteConfig = $config['environments'][$ambiente];

    if (!is_array($ambienteConfig)) {
        throw new RuntimeException(
            'Configuração do ambiente de banco inválida.'
        );
    }

    return $ambienteConfig;
}

$config = resolverConfiguracaoBanco($config);

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

    $configPath = getenv('APP_DB_CONFIG') ?: dirname(__DIR__) . '/config/database.php';

    if (! file_exists($configPath)) {

        error_log(
            '[Desafio do Milhão] Arquivo de configuração do banco não encontrado.'
        );

        throw new RuntimeException(
            'Não foi possível acessar as configurações do banco.'
        );
    }

    $config = resolverConfiguracaoBanco(require $configPath);

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
