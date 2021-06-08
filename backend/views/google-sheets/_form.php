<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\domains\GoogleSheets */
/* @var $form yii\widgets\ActiveForm */
/* @var $projects array */

$items = ArrayHelper::map($projects, 'id', 'project_key');

?>

<div style="width: 60%">

    <?php $form = ActiveForm::begin(); ?>
    <div class="row">
        <div class="col-lg-6">
            <?= $form->field($model, 'project_id')->dropDownList($items, ['prompt' => 'Выбрать проект']) ?>
        </div>
        <div class="col-lg-6">
            <?= $form->field($model, 'reload_days')->textInput() ?>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <?= $form->field($model, 'spreadsheet')->textInput(['maxlength' => true, 'placeholder' => 'Table ID']) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6">
            <?= $form->field($model, 'sheet')->textInput(['maxlength' => true, 'placeholder' => 'Sheet ID']) ?>
        </div>
        <div class="col-lg-6">
            <?= $form->field($model, 'range')->textInput(['maxlength' => true, 'placeholder' => 'Лист1!A1']) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="form-group">
                <?= Html::submitButton('Save', ['class' => 'btn btn-success btn btn-success btn-block']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
