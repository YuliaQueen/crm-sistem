<?php

namespace common\fixtures;

use yii\test\ActiveFixture;
use common\models\domains\AuthItemChild;

class AuthItemChildFixture extends ActiveFixture
{
    public $modelClass = AuthItemChild::class;
    public $dataFile = '@common/fixtures/data/auth_item_child.php';
}
