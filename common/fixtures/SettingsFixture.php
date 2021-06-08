<?php

namespace common\fixtures;

use common\models\domains\Settings;
use yii\test\ActiveFixture;

class SettingsFixture extends ActiveFixture
{
    public $modelClass = Settings::class;
    public $dataFile = '@common/fixtures/data/settings.php';
}