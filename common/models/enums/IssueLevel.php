<?php

namespace common\models\enums;

use yii2mod\enum\helpers\BaseEnum;

/**
 * Перечисление уровней задач в Jira
 */
class IssueLevel extends BaseEnum
{
    public const EPIC = 1;
    public const TASK = 2;
    public const SUB_TASK = 3;

    /**
     * @var array
     */
    protected static $list = [
        self::EPIC => 'Epic',
        self::TASK => 'Task',
        self::SUB_TASK => 'Sub-task',
    ];
}
