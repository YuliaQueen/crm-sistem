<?php

use common\models\domains\Worklog;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $projects array */
$items = ArrayHelper::map($projects, 'id', 'project_key');
$this->title = 'Worklogs';
$this->params['breadcrumbs'][] = $this->title;
?>
<div style="width: 350px">
    <?php $form = ActiveForm::begin(['method' => 'GET']); ?>
    <div class="form-group">
        <?= Html::dropDownList('project_key', 'id', $items, ['class' => 'form-control', 'prompt' => 'Все']); ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
<div class="worklog-index">
    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'project',
                'value' => function (Worklog $model) {
                    return $model->issue->project->project_key ?? '';
                },
            ],
            [
                'attribute' => 'issue key',
                'value' => function (Worklog $model) {
                    return $model->issue->issue_key ?? '';
                },
            ],
            'worklog_id',
            'date',
            [
                'attribute' => 'author',
                'value' => function (Worklog $model) {
                    return $model->author->name ?? '';
                },
            ],
            [
                'attribute' => 'timespent',
                'value' =>
                    function (Worklog $model) {
                        return $model->timespent / 60 / 60 . ' час.';
                    },
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view}',
                'header' => 'Действия',
                'headerOptions' => ['style' => 'color: #3c8dbc', 'width' => '100'],
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
