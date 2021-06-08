<?php

use backend\modules\rbac\models\domains\AuthItemModel;
use backend\modules\rbac\models\enums\Permission;
use backend\modules\rbac\RbacAsset;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\View;

/* @var $this View */
/* @var $model AuthItemModel */

RbacAsset::register($this);

$this->title = 'Разрешения для ' . $model->description;

$this->params['breadcrumbs'][] = ['label' => 'Пользователи', 'url' => ['/user']];
$this->params['breadcrumbs'][] = ['label' => 'Управление правами пользователей', 'url' => ['/rbac/assignment']];
$this->params['breadcrumbs'][] = ['label' => 'Управление ролями', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->description;
?>
<div class="card card-primary card-outline">
    <?php if ($model->name !== Permission::ADMIN) : ?>
        <div class="card-header">
            <?= Html::a('Редактировать', ['update', 'id' => $model->name], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Удалить', ['delete', 'id' => $model->name], [
                'class' => 'btn btn-danger',
                'data-confirm' => 'Вы уверены, что хотите удалить роль?',
                'data-method' => 'post',
            ]) ?>
            <?= Html::a('Создать', ['create'], ['class' => 'btn btn-success']) ?>
        </div>
    <?php endif; ?>
</div>

<div class="card card-primary card-outline">
    <div class="card-header">
    </div>
    <div class="card-body">
        <?= $this->render('../_dualListBox', [
            'opts' => Json::htmlEncode([
                'items' => $model->getItems(),
            ]),
            'assignUrl' => ['assign', 'id' => $model->name],
            'removeUrl' => ['remove', 'id' => $model->name],
            'disableEdit' => $model->name === Permission::ADMIN,
        ]) ?>
    </div>
</div>
