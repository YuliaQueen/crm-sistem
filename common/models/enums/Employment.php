<?php

namespace common\models\enums;


use yii2mod\enum\helpers\BaseEnum;


/**
 * Перечисление вида сотрудничества
 */
class Employment extends BaseEnum
{
    public const  STAFF = 1;
    public const FREELANCE = 2;

    /**
     * @var array
     */
    protected static $list = [
        self::STAFF => 'Штатный',
        self::FREELANCE => 'Внештатный',
    ];
}