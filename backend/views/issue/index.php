<?php

use common\models\domains\Issue;
use common\models\enums\IssueLevel;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\queries\IssueQuery */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Issues';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="issue-index">

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'issue_key',
            [
                'attribute' => 'parent_id',
                'label' => 'Parent Key',
                'value' => function (Issue $model) {
                    return $model->parent->issue_key ?? 'no parent';
                },
            ],
            [
                'attribute' => 'level',
                'label' => 'Level',
                'value' => function (Issue $model) {
                    return IssueLevel::getLabel($model->level);
                },
            ],
            [
                'attribute' => 'project_id',
                'label' => 'Project',
                'value' => function (Issue $model) {
                    return $model->project->project_key;
                },
            ],
            [
                'attribute' => 'issue_type',
                'label' => 'Issue Type',
            ],
            [
                'attribute' => 'updated_at',
                'label' => 'Updated',
                'format' => ['date', 'php:d-m-Y H:i:s'],
            ],
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
