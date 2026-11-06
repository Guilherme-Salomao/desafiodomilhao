<?php

declare(strict_types=1);

/*
 * Copie este arquivo para config/database.php e preencha os dados.
 * O ambiente é escolhido automaticamente pelo host: localhost usa
 * "local" e o servidor usa "production". APP_ENV pode sobrescrever
 * a escolha com local, development, production ou prod.
 * Nunca versione config/database.php.
 */

return [
    'environments' => [
        'local' => [
            'host' => '127.0.0.1',
            'port' => 3306,
            'database' => 'desafiodomilhao',
            'username' => 'USUARIO_DO_BANCO_LOCAL',
            'password' => 'SENHA_DO_BANCO_LOCAL',
            'charset' => 'utf8mb4',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        ],
        'production' => [
            'host' => 'localhost',
            'port' => 3306,
            'database' => 'NOME_DO_BANCO_EM_PRODUCAO',
            'username' => 'USUARIO_DO_BANCO_EM_PRODUCAO',
            'password' => 'SENHA_DO_BANCO_EM_PRODUCAO',
            'charset' => 'utf8mb4',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        ],
    ],
];
