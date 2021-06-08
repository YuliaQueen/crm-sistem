<?php

use backend\modules\rbac\models\domains\AuthItemModel;
use yii\web\View;

/* @var $this View */
/* @var $model AuthItemModel */

$this->title = 'Создать роль';

$this->params['breadcrumbs'][] = ['label' => 'Пользователи', 'url' => ['/user']];
$this->params['breadcrumbs'][] = ['label' => 'Управление правами пользователей', 'url' => ['/rbac/assignment']];
$this->params['breadcrumbs'][] = ['label' => 'Управление ролями', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="auth-item-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
