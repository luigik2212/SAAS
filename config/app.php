<?php

return [
    'name' => env('APP_NAME', 'NOME_DO_SAAS'),
    'env' => env('APP_ENV', 'local'),
    'debug' => (bool) env('APP_DEBUG', true),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => env('APP_TIMEZONE', 'America/Sao_Paulo'),
    'locale' => env('APP_LOCALE', 'pt_BR'),
    'fallback_locale' => 'pt_BR',
    'faker_locale' => 'pt_BR',
];
