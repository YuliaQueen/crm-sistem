<?php

use common\models\enums\DayType;
use common\models\enums\EventType;

$time = time();

return [
    [
//      'id' => 1,
        'date_start' => '2021-06-10',
        'date_end' => '2021-06-10',
        'type' => DayType::WEEKDAY,
        'created_at' => time(),
        'updated_at' => time(),
        'deleted_at' => 0,
        'created_by_id' => 2,
        'updated_by_id' => 2,
    ],
    [
//      'id' => 2,
        'date_start' => '2021-06-11',
        'date_end' => '2021-06-15',
        'user_id' => 2,
        'type' => EventType::SICK_LIST,
        'created_at' => time(),
        'updated_at' => time(),
        'deleted_at' => 0,
        'created_by_id' => 2,
        'updated_by_id' => 2,
    ],
];
