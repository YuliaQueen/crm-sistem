<?php

namespace common\fixtures;

use common\models\domains\Calendar;
use yii\test\ActiveFixture;

class CalendarFixture extends ActiveFixture
{
    public $modelClass = Calendar::class;
    public $dataFile = '@common/fixtures/data/calendar.php';
}