<?php

use common\models\domains\Project;
use common\models\domains\Worklog;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Проекты';
$this->params['breadcrumbs'][] = $this->title;
?>
<p>
    <?= Html::a('Добавить проект', ['create-project'], ['class' => 'btn btn-success']) ?>
</p>
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'tableOptions' => ['class' => 'table table-striped table-bordered table-hover dataTable'],
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        [
            'attribute' => 'project_key',
            'label' => 'Ключ проекта',
            'format' => 'raw',
        ],
        [
            'attribute' => 'reload_days',
            'label' => 'Автозагрузка',
            'value' => function (Project $project) {
                if ($project->reload_days !== null) {
                    return $project->autoload === true
                        ? $project->reload_days . ' дней'
                        : $project->reload_days . ' дней (выкл)';
                } else {
                    return 'выключено';
                }
            },
            'format' => 'raw',
        ],
        [
            'attribute' => 'load_start_date',
            'label' => 'Грузить с',
            'format' => 'date',
        ],
        [
            'label' => 'Первый лог',
            'format' => 'date',
            'headerOptions' => ['style' => 'color: #3c8dbc'],
            'value' => function (Project $project) {
                return Project::find()->getFirstLog($project->id);
            },
        ],
        [
            'label' => 'Последний лог',
            'format' => 'date',
            'headerOptions' => ['style' => 'color: #3c8dbc'],
            'value' => function (Project $project) {
                return Project::find()->getLastLog($project->id);
            },
        ],
        [
            'attribute' => 'last_load_date',
            'label' => 'Обновлено',
            'format' => ['date','php:d-m-Y H:i:s'],
            'value' => function (Project $project) {
                return $project->last_load_date === null
                    ? $project->created_at
                    : $project->last_load_date;
            },
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{reload} {update} {delete}',
            'header' => 'Действия',
            'headerOptions' => ['style' => 'color: #3c8dbc', 'width' => '100'],
            'buttons' => [
                'reload' => function ($url, $data) {
                    return Html::a('<span class="fa fa-refresh"></span>', Url::to(['/project/reload', 'id' => $data['id']]));
                },
            ],
        ],
    ],
]);
?>
