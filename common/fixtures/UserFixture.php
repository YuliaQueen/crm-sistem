<?php
namespace common\fixtures;

use yii\test\ActiveFixture;
use common\models\domains\User;

class UserFixture extends ActiveFixture
{
    public $modelClass = User::class;
    public $dataFile = '@common/fixtures/data/user.php';
}