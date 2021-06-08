<?php

use common\models\enums\DayType;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\domains\Calendar */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="calendar-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->errorSummary($model)?>

    <?= $form->field($model, 'date_start')->textInput(['type' => 'date']) ?>

    <?= $form->field($model, 'type')->dropDownList(DayType::listData(), ['prompt' => 'Выберите тип дня']) ?>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
