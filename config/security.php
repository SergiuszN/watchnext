<?php

return [
    'roles' => [
        // ---------------- users -----------------
        'ROLE_ADMIN' => [
            'ROLE_USER',
            'ROLE_ADMIN_GROUP',
        ],
        'ROLE_USER' => [
            'ROLE_USER_GROUP',
        ],

        // ---------------- groups ----------------

        'ROLE_ADMIN_GROUP' => [

        ],
        'ROLE_USER_GROUP' => [
            'ROLE_ACCESS_TEST',
        ],

        // ---------------- actions ----------------

        'ROLE_ACCESS_TEST' => [],
    ],
    'access_control' => [
        '/admin' => ['ROLE_ADMIN']
    ],
];