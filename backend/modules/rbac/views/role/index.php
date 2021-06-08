<?php

use backend\modules\rbac\models\enums\Permission;
use yii\data\ArrayDataProvider;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\rbac\Role;
use yii\web\View;
use yii\widgets\Pjax;
use backend\modules\rbac\models\search\AuthItemSearch;

/* @var $this View */
/* @var $dataProvider ArrayDataProvider */
/* @var $searchModel AuthItemSearch */

$this->params['breadcrumbs'][] = ['label' => 'Пользователи', 'url' => ['/user']];
$this->params['breadcrumbs'][] = ['label' => 'Управление правами пользователей', 'url' => ['/rbac/assignment']];
$this->title = 'Управление ролями';
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="card card-primary card-outline">

    <div class="card-header">
        <?= Html::a('Создать роль', ['create'], ['class' => 'btn btn-success']) ?>
    </div>

    <?php Pjax::begin(['timeout' => 5000, 'enablePushState' => false]); ?>
    <div class="card-body">
        <?= GridView::widget([
            'filterModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'columns' => [
                [
                    'attribute' => 'description',
                    'format' => 'ntext',
                    'label' => 'Название',
                ],
                [
                    'class' => ActionColumn::class,
                    'visibleButtons' => [
                        // Нельзя редактировать роль "админ".
                        'update' => static function (Role $model) {
                            return $model->name !== Permission::ADMIN;
                        },
                        'delete' => static function (Role $model) {
                            return $model->name !== Permission::ADMIN;
                        },
                    ],
                    'header' => 'Действия',
                ],
            ],
        ]) ?>
    </div>
    <?php Pjax::end(); ?>
</div>
