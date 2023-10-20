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
            'ROLE_USER_EDIT_GR',
            'ROLE_POST_EDIT_GR'
        ],

        'ROLE_USER_GR' => [
            'ROLE_USER_VIEW_GR',
            'ROLE_POST_EDIT_GR'
        ],

        // ---------------- actions ----------------
        'ROLE_USER_EDIT_GR' => [
            'ROLE_USER_ADD',
            'ROLE_USER_EDIT',
            'ROLE_USER_DELETE',
            'ROLE_USER_VIEW_GR'
        ],

        'ROLE_USER_VIEW_GR' => [
            'ROLE_USER_VIEW',
        ],

        'ROLE_POST_EDIT_GR' => [
            'ROLE_POST_ADD',
            'ROLE_POST_EDIT',
            'ROLE_POST_DELETE',
            'ROLE_POST_VIEW_GR'
        ],

        'ROLE_POST_VIEW_GR' => [
            'ROLE_POST_VIEW',
        ],
    ],
    'access_control' => [
        '/admin' => ['ROLE_ADMIN'],
        '/app' => ['ROLE_ADMIN']
    ],
];