<?php

use backend\modules\rbac\models\domains\AssignmentModel;
use backend\modules\rbac\RbacAsset;
use common\models\domains\User;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\View;

/* @var $this View */
/* @var $user User */
/* @var $assignment AssignmentModel */

RbacAsset::register($this);

$this->title = 'Разрешения для ' . $user->name;

$this->params['breadcrumbs'][] = ['label' => 'Пользователи', 'url' => ['/user']];
$this->params['breadcrumbs'][] = ['label' => 'Управление правами пользователей', 'url' => ['/rbac/assignment']];
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="card card-primary card-outline">
    <div class="card-header">
        <?= Html::a('Перейти в профиль', ['/user/view', 'id' => $user->id], ['class' => 'btn btn-primary']) ?>
    </div>
</div>

<div class="card card-primary card-outline">
    <div class="card-body">

        <?= $this->render('../_dualListBox', [
            'opts' => Json::htmlEncode([
                'items' => $assignment->items,
            ]),
            'assignUrl' => ['assign', 'id' => $assignment->userId],
            'removeUrl' => ['remove', 'id' => $assignment->userId],
            'disableEdit' => false,
        ]) ?>

    </div>
</div>
