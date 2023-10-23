<?php

return [
    // Define roles tree structure but remember use only one level of nesting
    'roles' => [
        // ---------------- users -----------------
        'ROLE_ADMIN' => [
            'ROLE_USER',
            'ROLE_ADMIN_GR',
        ],
        'ROLE_USER' => [
            'ROLE_USER_GR',
        ],

        // ---------------- groups ----------------

        'ROLE_ADMIN_GR' => [
        ],

        'ROLE_USER_GR' => [
            'ROLE_HOMEPAGE_GR',
            'ROLE_ITEM_GR',
        ],

        // ---------------- actions ----------------
        'ROLE_HOMEPAGE_GR' => [
            'ROLE_HOMEPAGE_APP',
        ],

        'ROLE_ITEM_GR' => [
            'ROLE_ITEM_ADD',
        ]
    ],
    'access_control' => [
        '/admin' => ['ROLE_ADMIN'],
        '/app' => ['ROLE_USER']
    ],
];