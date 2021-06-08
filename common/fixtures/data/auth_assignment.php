<?php

use backend\modules\rbac\models\enums\Permission;

$time = time();

return [
    [
        'item_name' => Permission::ADMIN,
        'user_id' => 2,
        'created_at' => $time,
    ],
];
