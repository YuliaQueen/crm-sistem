<?php

namespace common\fixtures;

use yii\test\ActiveFixture;
use common\models\domains\AuthAssignment;

class AuthAssignmentFixture extends ActiveFixture
{
    public $modelClass = AuthAssignment::class;
    public $dataFile = '@common/fixtures/data/auth_assignment.php';
}
