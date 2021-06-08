<?php

use common\models\domains\GoogleSheets;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Google Sheets';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="google-sheets-index">
    <p>
        <?= Html::a('Add Google Sheets', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'project_id',
                'value' => function (GoogleSheets $model) {
                    return $model->project->project_key ?? '';
                },
            ],
            'spreadsheet',
            'sheet',
            'range',
            'reload_days',
            [
                'attribute' => 'last_load_date',
                'format' => ['date', 'php:d-m-Y H:i:s'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {reload} {update} {delete}',
                'header' => 'Действия',
                'headerOptions' => ['style' => 'color: #3c8dbc', 'width' => '100'],
                'buttons' => [
                    'reload' => function ($url, $data) {
                        return Html::a('<span class="fa fa-refresh"></span>', Url::to(['/google-sheets/reload', 'id' => $data['id']]));
                    },
                ],
            ],
        ],
    ]); ?>
</div>
