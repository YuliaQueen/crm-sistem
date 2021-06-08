<?php

use backend\modules\rbac\models\enums\Permission;
use yii\rbac\Item;

$time = time();

return [
    [
        'name' => Permission::ADMIN,
        'type' => Item::TYPE_ROLE,
        'description' => 'Админ',
        'rule_name' => null,
        'data' => null,
        'created_at' => $time,
        'updated_at' => $time,
    ],
    [
        'name' => Permission::USER,
        'type' => Item::TYPE_PERMISSION,
        'description' => 'Управление пользователями',
        'rule_name' => null,
        'data' => null,
        'created_at' => $time,
        'updated_at' => $time,
    ],
    [
        'name' => Permission::RBAC,
        'type' => Item::TYPE_PERMISSION,
        'description' => 'Управление разрешениями',
        'rule_name' => null,
        'data' => null,
        'created_at' => $time,
        'updated_at' => $time,
    ],
    [
        'name' => Permission::SETTINGS,
        'type' => Item::TYPE_PERMISSION,
        'description' => 'Управление настройками',
        'rule_name' => null,
        'data' => null,
        'created_at' => $time,
        'updated_at' => $time,
    ],
    [
        'name' => Permission::CALENDAR,
        'type' => Item::TYPE_PERMISSION,
        'description' => 'Управление календарем',
        'rule_name' => null,
        'data' => null,
        'created_at' => $time,
        'updated_at' => $time,
    ],
    [
        'name' => Permission::RECEIVING_WORKLOGS,
        'type' => Item::TYPE_PERMISSION,
        'description' => 'Получение jira worklogs',
        'rule_name' => null,
        'data' => null,
        'created_at' => $time,
        'updated_at' => $time,
    ],
];
