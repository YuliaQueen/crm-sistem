<?php


namespace common\models\enums;


use yii2mod\enum\helpers\BaseEnum;


/**
 * Перечисление отделов
 */
class Department extends BaseEnum
{
    const ANALYTIC = 1;
    const FRONTEND = 2;
    const DESIGN = 3;
    const DEVELOPMENT = 4;
    const MANAGEMENT = 5;
    const TESTING = 6;

    /**
     * @var array
     */
    protected static $list = [
        self::ANALYTIC => 'Аналитика',
        self::FRONTEND => 'Верстка',
        self::DESIGN => 'Дизайн',
        self::DEVELOPMENT => 'Разработка',
        self::MANAGEMENT => 'Управление проектами',
        self::TESTING => 'Тестирование'
    ];
}