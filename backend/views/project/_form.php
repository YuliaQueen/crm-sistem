<?php

use common\models\domains\Project;
use yii\helpers\Html;
use yii\jui\DatePicker;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $project Project */
/* @var $form yii\widgets\ActiveForm */
?>

    <div class="container-fluid">
        <?php $form = ActiveForm::begin(); ?>
        <div class="row">
            <div class="col-lg-4">
                <?= $form->field($project, 'project_key')
                    ->textInput(['maxlength' => true])->label('Ключ проекта') ?>
            </div>
            <div class="col-lg-4">
                <?= $form->field($project, 'load_start_date')->widget(DatePicker::class, [
                    'language' => 'ru',
                    'dateFormat' => 'yyyy-MM-dd',
                    'options' => ['class' => 'form-control',],
                ])->label('Начальная дата загрузки') ?>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4 text-right">
                <?= $form->field($project, 'autoload')
                    ->checkbox(['class' => 'form-check-input', 'id' => 'checkbox'])->label(false) ?>
            </div>
            <div class="col-lg-4">
                <?= $form->field($project, 'reload_days')
                    ->textInput(['disabled' => true, 'id' => 'days'])->label('дней') ?>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success btn-block']) ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
<?php
$js = <<<JS
$('#checkbox').click(function(){
    if ($(this).is(':checked')){
        $('#days').prop('disabled', false);
    } else {
        $('#days').prop('disabled', true);
    }
});
JS;
$this->registerJS($js);
