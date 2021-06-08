<?php

namespace common\fixtures;

use yii\test\ActiveFixture;
use common\models\domains\AuthRule;

class AuthRuleFixture extends ActiveFixture
{
    public $modelClass = AuthRule::class;
    public $dataFile = '@common/fixtures/data/auth_rule.php';
}
