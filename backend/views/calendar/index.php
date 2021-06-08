<?php

use common\models\domains\Calendar;
use common\models\enums\DayType;
use yii\grid\ActionColumn;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Календарь';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="calendar-index">

    <p>
        <?= Html::a('Добавить переопределение', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'date_start:date',
            [
                'attribute' => 'type',
                'value' => function (Calendar $model) {
                    return DayType::getLabel($model->type);
                },
            ],
            [
                'class' => ActionColumn::class,
                'template' => '{update} {delete}',
                'header' => 'Действия',
                'headerOptions' => ['style' => 'color: #3c8dbc', 'width' => '100'],
                'contentOptions' => ['style' => 'text-align: right;'],
            ],

        ],
    ]); ?>
</div>
