<?php

use common\models\domains\Calendar;
use common\models\domains\User;
use common\models\enums\Department;
use common\models\enums\Employment;
use common\models\enums\EventType;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\YiiAsset;
use yii\widgets\ActiveForm;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\domains\User */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Пользователи', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
YiiAsset::register($this);
?>
<div class="container-fluid user-view">
    <p>
        <?= Html::a('Изменить', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::a('Добавить событие календаря', ['add-event', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
                'attribute' => 'name',
                'label' => 'ФИО',
            ],
            'email:email',
            [
                'attribute' => 'department_id',
                'label' => 'Отдел',
                'format' => 'raw',
                'value' => $model->department_id ? Department::getLabel($model->department_id) : 'не указано',
            ],
            [
                'attribute' => 'jira_user',
                'label' => 'Логин в Jira',
                'value' => $model->jira_user ? $model->jira_user : 'не указано',
            ],
            [
                'attribute' => 'slack_email',
                'label' => 'Email в Slack',
                'format' => $model->slack_email ? 'email' : 'raw',
                'value' => $model->slack_email ? $model->slack_email : 'не указано',

            ],
            [
                'attribute' => 'employee',
                'label' => 'Вид сотрудничества',
                'value' => $model->employee ? Employment::getLabel($model->employee) : 'не указано',
            ],
            [
                'attribute' => 'weekly_load',
                'label' => 'Недельная нагрузка',
                'value' => $model->weekly_load ? $model->weekly_load . ' часов' : 'не указано',
            ],
            [
                'attribute' => 'leadership_id',
                'label' => 'Руководитель',
                'format' => 'raw',
                'value' => $model->leadership_id ? User::find()->where(['id' => $model->leadership_id])->one()->name : 'не указано',
            ],
            [
                'attribute' => 'date_of_employment',
                'label' => 'Дата устройства на работу',
                'format' => 'raw',
                'value' => $model->date_of_employment ? Yii::$app->formatter->asDate($model->date_of_employment, 'long') : 'не указано',
            ],
            [
                'attribute' => 'date_of_dismissal',
                'label' => 'Дата увольнения',
                'format' => 'raw',
                'value' => $model->date_of_dismissal ? Yii::$app->formatter->asDate($model->date_of_dismissal, 'long') : 'не указано',
            ],
        ],
    ]) ?>

    <h4 class="text-danger text-bold">События календаря</h4>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'header' => '№',
                'headerOptions' => ['style' => 'color: #3c8dbc', 'width' => '5%'],
            ],
            [
                'label' => 'Период',
                'value' => function (Calendar $model) {
                    if ($model->date_start === $model->date_end) {
                        return Yii::$app->formatter->asDate($model->date_start, 'medium');
                    }
                    return 'C ' . Yii::$app->formatter->asDate($model->date_start, 'medium') . ' по ' .
                        Yii::$app->formatter->asDate($model->date_end, 'medium');
                },
                'headerOptions' => ['style' => 'color: #3c8dbc', 'width' => '30%'],
            ],
            [
                'label' => 'Кол-во дней',
                'value' => function (Calendar $model) {
                    return (strtotime($model->date_end) - strtotime($model->date_start)) / (60 * 60 * 24) + 1;
                },
                'headerOptions' => ['style' => 'color: #3c8dbc', 'width' => '20%'],
            ],
            [
                'attribute' => 'type',
                'label' => 'Тип события',
                'value' => function (Calendar $model) {
                    return EventType::getLabel($model->type) ?? '';
                },
                'headerOptions' => ['style' => 'color: #3c8dbc', 'width:' => '20%'],
            ],
            [
                'class' => ActionColumn::class,
                'template' => '{update}{delete}',
                'header' => 'Действия',
                'headerOptions' => ['style' => 'color: #3c8dbc', 'width' => '100'],
                'contentOptions' => ['style' => 'text-align: right;'],
                'buttons' => [
                    'update' => function ($url, $data) {
                        return Html::a(' <span class="glyphicon glyphicon-edit"></span>', ['update-event', 'id' => $data->id]);
                    },
                    'delete' => function ($url, $data) {
                        return Html::a(' <span class="glyphicon glyphicon-trash"></span>', ['delete-event', 'id' => $data->id],
                            [
                                'data' => [
                                    'confirm' => 'Вы действительно хотите удалить событие?',
                                    'method' => 'post',
                                ],
                            ]);
                    },
                ],
            ],

        ],
    ]); ?>

    <h4 class="text-danger text-bold">Изменить текущий пароль</h4>
    <?php $form = ActiveForm::begin(); ?>
    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'password')
                ->input('password', ['class' => 'form-control input-sm'])
                ->label('Введите новый пароль')
                ->hint('Введите пароль не менее 6 символов') ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'passwordRepeat')
                ->input('password', ['class' => 'form-control input-sm'])
                ->label('Введите новый пароль еще раз') ?>
        </div>
    </div>
    <div class="form-group">
        <?= Html::submitButton('Сохранить новый пароль', ['class' => 'btn btn-success']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
