<?php

use backend\modules\rbac\models\domains\AuthItemModel;
use yii\web\View;

/* @var $this View */
/* @var $model AuthItemModel */

$this->title = 'Редактирование роли: ' . $model->description;

$this->params['breadcrumbs'][] = ['label' => 'Пользователи', 'url' => ['/user']];
$this->params['breadcrumbs'][] = ['label' => 'Управление правами пользователей', 'url' => ['/rbac/assignment']];
$this->params['breadcrumbs'][] = ['label' => 'Управление ролями', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->description, 'url' => ['view', 'id' => $model->name]];
$this->params['breadcrumbs'][] = 'Редактирование роли';
?>
<div class="auth-item-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
