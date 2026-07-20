<?php

declare(strict_types=1);

return [
    'app_name' => 'SICERDAS Mukomuko',
    'app_full_name' => 'Sistem Informasi Creative Financing Daerah dan Tanggung Jawab Sosial Perusahaan Kabupaten Mukomuko',
    'db' => [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => getenv('DB_PORT') ?: '3306',
        'name' => getenv('DB_NAME') ?: 'sicerdas',
        'user' => getenv('DB_USER') ?: 'sicerdas',
        'pass' => getenv('DB_PASS') ?: 'sicerdas123',
    ],
];
