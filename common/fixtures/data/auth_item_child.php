<?php

use backend\modules\rbac\models\enums\Permission;

return [
    [
        'parent' => Permission::ADMIN,
        'child' => Permission::USER,
    ],
    [
        'parent' => Permission::ADMIN,
        'child' => Permission::RBAC,
    ],
    [
        'parent' => Permission::ADMIN,
        'child' => Permission::SETTINGS,
    ],
    [
        'parent' => Permission::ADMIN,
        'child' => Permission::CALENDAR,
    ],
    [
        'parent' => Permission::ADMIN,
        'child' => Permission::RECEIVING_WORKLOGS,
    ],
];
