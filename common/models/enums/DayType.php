<?php

namespace common\models\enums;

use yii\base\Exception;
use yii2mod\enum\helpers\BaseEnum;

/**
 * Тип дня недели
 */
class DayType extends BaseEnum
{
    public const WEEKDAY = 1;
    public const WEEKEND = 2;

    /**
     * @var array
     */
    protected static $list = [
        self::WEEKDAY => 'Рабочий день',
        self::WEEKEND => 'Выходной',
    ];

    /**
     * Возвращает тип события календаря - рабочий или нерабочий день
     *
     * @param $type
     * @return int
     * @throws Exception
     */
    public static function getDayType($type): int
    {
        if (in_array($type, EventType::$workDay)) {
            return DayType::WEEKDAY;
        } elseif (in_array($type, EventType::$weekend)) {
            return DayType::WEEKEND;
        } else {
            throw new  Exception('Неизвестный тип события: ' . $type);
        }
    }
}
