<?php

use common\models\enums\SystemSettingsName;

$time = time();

return [
    [
//        'id' => '1',
        'name' => SystemSettingsName::JIRA_URL,
        'value' => 'http://jira.com',
        'created_at' => $time,
        'updated_at' => $time,
        'is_deleted' => 0,
        'created_by_id' => 2,
        'updated_by_id' => 2,
    ],
    [
//        'id' => '2',
        'name' => SystemSettingsName::JIRA_LOGIN,
        'value' => 'myLogin',
        'created_at' => $time,
        'updated_at' => $time,
        'is_deleted' => 0,
        'created_by_id' => 2,
        'updated_by_id' => 2,
    ],
    [
//        'id' => '3',
        'name' => SystemSettingsName::JIRA_PASSWORD,
        'value' => 'myPassword',
        'created_at' => $time,
        'updated_at' => $time,
        'is_deleted' => 0,
        'created_by_id' => 2,
        'updated_by_id' => 2,
    ],
    [
//        'id' => '4',
        'name' => SystemSettingsName::SLACK_TOKEN,
        'value' => Yii::$app->security->generateRandomString(),
        'created_at' => $time,
        'updated_at' => $time,
        'is_deleted' => 0,
        'created_by_id' => 2,
        'updated_by_id' => 2,
    ],
];
