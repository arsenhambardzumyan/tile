<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

// Auto-create schema for SQLite test DB
if (($_SERVER['APP_ENV'] ?? null) === 'test') {
    try {
        passthru('php '.escapeshellarg(dirname(__DIR__).'/bin/console').' doctrine:schema:create --no-interaction --env=test >/dev/null 2>&1');
    } catch (\Throwable) {
        // ignore
    }
}
