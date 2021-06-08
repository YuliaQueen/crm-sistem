<?php

use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = ['label' => 'Пользователи', 'url' => ['/user']];
$this->title = 'Управление правами пользователей';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="card-header">
    <?= Html::a('Управление ролями', ['role/index'], ['class' => 'btn btn-success']) ?>
</div>

<div class="auth-assignment-index">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'name',
                'label' => 'Пользователь',
            ],
            [
                'attribute' => 'description',
                'label' => 'Разрешения',
                'format' => 'html',
                'headerOptions' =>
                    [
                        'style' => 'color: #3c8dbc',
                    ],
                'value' => function ($model) {
                    $authAssignments = $model->authAssignments;
                    $row = [];
                    foreach ($authAssignments as $authAssignment) {
                        $description = $authAssignment->itemName->description;
                        array_push($row, $description);
                    }
                    return implode(', ', $row);
                },
            ],
            [
                'class' => ActionColumn::class,
                'header' => 'Действия',
                'headerOptions' =>
                    [
                        'style' => 'color: #3c8dbc',
                    ],
                'template' => '{view}',
                'buttons' =>
                    [
                        'view' => function ($url, $model, $key) {
                            return Html::a('', ['assignment/view', 'id' => $model->id], ['class' => 'glyphicon glyphicon-pencil']);
                        },
                    ],
            ],
        ],
    ]); ?>
</div>
