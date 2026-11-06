<?php

declare(strict_types=1);

/*
 * Copie este arquivo para config/database.php e preencha os dados
 * locais ou de produção. Nunca versione config/database.php.
 */

return [
    'host' => '127.0.0.1',
    'port' => 3306,
    'database' => 'desafiodomilhao',
    'username' => 'USUARIO_DO_BANCO',
    'password' => 'SENHA_DO_BANCO',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
];
