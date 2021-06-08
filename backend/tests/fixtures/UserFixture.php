<?php

namespace tests\fixtures;

use yii\test\ActiveFixture;

class UserFixture extends ActiveFixture
{
    public $modelClass = 'common\models\domains\User';
    public $dataFile = '@tests/fixtures/data/user.php';
}