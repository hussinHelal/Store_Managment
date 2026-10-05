<?php

return [
    'superadmin' => [
        'username' => env('SUPERADMIN_USERNAME'),
        'password' => env('SUPERADMIN_PASSWORD'),
        'name' => env('SUPERADMIN_NAME', 'Superadmin'),
    ],
    'admin' => [
        'username' => env('ADMIN_USERNAME'),
        'password' => env('ADMIN_PASSWORD'),
        'name' => env('ADMIN_NAME', 'Administrator'),
    ],
];