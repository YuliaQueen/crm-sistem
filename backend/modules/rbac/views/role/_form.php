<?php

use backend\modules\rbac\models\domains\AuthItemModel;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model AuthItemModel */
?>
<div class="auth-item-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="card card-primary card-outline">
        <div class="card-body">

            <?= $form->field($model, 'description')->textInput(['maxlength' => 64]) ?>

        </div>
        <div class="card-footer">
            <?= Html::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить', [
                'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary',
            ]) ?>
        </div>

    </div>

    <?php ActiveForm::end(); ?>
</div>
