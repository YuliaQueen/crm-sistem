<?php

namespace common\models\enums;

use yii2mod\enum\helpers\BaseEnum;

/**
 * Тип пользовательского события
 */
class EventType extends BaseEnum
{
    public const ADDITIONAL_WORKING_DAY = 1;
    public const ADDITIONAL_WEEKEND = 2;
    public const VACATION = 3;
    public const SICK_LIST = 5;

    /**
     * @var array
     */
    protected static $list = [
        self::VACATION => 'Отпуск',
        self::SICK_LIST => 'Больничный',
        self::ADDITIONAL_WORKING_DAY => 'Дополнительный рабочий день',
        self::ADDITIONAL_WEEKEND => 'Дополнительный выходной',
    ];

    /**
     * Нерабочие типы дней
     *
     * @var array|string[]
     */
    public static array $weekend = [
        self::VACATION,
        self::SICK_LIST,
        self::ADDITIONAL_WEEKEND
    ];

    /**
     * Рабочие типы дней
     *
     * @var array|string[]
     */
    public static array $workDay = [
        self::ADDITIONAL_WORKING_DAY,
    ];
}
