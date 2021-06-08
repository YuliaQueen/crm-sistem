<?php

namespace common\fixtures;

use yii\test\ActiveFixture;
use common\models\domains\AuthItem;

class AuthItemFixture extends ActiveFixture
{
    public $modelClass = AuthItem::class;
    public $dataFile = '@common/fixtures/data/auth_item.php';
}
