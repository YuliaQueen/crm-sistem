<?php

use common\models\domains\Calendar;
use common\models\enums\EventType;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $calendar Calendar */

?>
<div class="event-form">
    <?php $form = ActiveForm::begin(
        [
            'fieldConfig' => [
                'template' => "{label}{input}",
            ],
        ]
    ); ?>
    <?= $form->errorSummary($calendar) ?>
    <?= $form->field($calendar, 'date_start')->textInput(['type' => 'date'])->label('Дата начала') ?>
    <?= $form->field($calendar, 'date_end')->textInput(['type' => 'date']) ?>
    <?= $form->field($calendar, 'type')->dropDownList(EventType::listData(), ['prompt' => 'Выберите тип события']) ?>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
