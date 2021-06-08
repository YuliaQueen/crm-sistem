<?php

use common\models\domains\Worklog;
use yii\helpers\Html;
use yii\web\YiiAsset;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\domains\Worklog */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Worklogs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
YiiAsset::register($this);
?>
<div class="worklog-view">
    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'worklog_id',
            'issue_id',
            'date',
            'author_id',
            [
                'attribute' => 'timespent',
                'value' =>
                    function (Worklog $model) {
                        return $model->timespent / 60 / 60 . ' час.';
                    },
            ],
            'worklog_comment:ntext',
        ],
    ]) ?>

</div>
