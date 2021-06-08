<?php

use common\models\domains\User;
use common\models\enums\Department;
use common\models\enums\Employment;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\domains\User */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="container-fluid">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true])->label('ФИО') ?>

    <?= $form->field($model, 'email')->input('email')->label('Email') ?>

    <?php if (Yii::$app->controller->action->id === 'create'): ?>
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'password')->input('password')->label('Введите пароль') ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'passwordRepeat')->input('password')->label('Введите пароль еще раз') ?>
            </div>
        </div>
    <?php endif; ?>


    <?= $form->field($model, 'employee')->dropDownList(Employment::listData(), ['prompt' => 'Выберите вид сотрудничества'])->label('Вид сотрудничества') ?>

    <?= $form->field($model, 'department_id')->dropDownList(Department::listData(), ['prompt' => 'Выберите направление'])->label('Направление') ?>

    <?= $form->field($model, 'jira_user')->textInput(['maxlength' => true])->label('Логин в Jira') ?>

    <?= $form->field($model, 'slack_email')->input('email')->label('Email в Slack') ?>

    <?= $form->field($model, 'weekly_load')->textInput()->label('Недельная нагрузка (часов)') ?>

    <?= $form->field($model, 'leadership_id')->dropDownList(ArrayHelper::map(User::find()->notSystem()->notDeleted()->all(), 'id', 'name'),
        ['prompt' => 'Выберите руководителя'])->label('Руководитель') ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'date_of_employment')->textInput(['type' => 'date'])->label('Дата устройства на работу') ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'date_of_dismissal')->textInput(['type' => 'date'])->label('Дата увольнения') ?>
        </div>
    </div>
    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
