<?php
/* @var $this yii\web\View */

use backend\models\forms\PerformanceForm;
use yii\helpers\Html;
use yii\jui\DatePicker;
use yii\widgets\ActiveForm;

/* @var $model PerformanceForm */
/* @var $worklogsData string */

$this->title = 'Jira worklogs report';
$this->params['breadcrumbs'][] = $this->title;
?>

    <div class="site-order-form">

        <?php $form = ActiveForm::begin(); ?>

        <div class="row">
            <div class="col-lg-8">
                <?= $form->field($model, 'project')->textInput(['maxlength' => true]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <?= $form->field($model, 'startDate')->widget(DatePicker::class, [
                    'language' => 'ru',
                    'dateFormat' => 'yyyy-MM-dd',
                    'options' => ['class' => 'form-control',],
                ]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <?= $form->field($model, 'endDate')->widget(DatePicker::class, [
                    'language' => 'ru',
                    'dateFormat' => 'yyyy-MM-dd',
                    'options' => ['class' => 'form-control',],
                ]) ?>
            </div>
        </div>

        <div class="form-group">
            <?= Html::submitButton('Сформировать', ['class' => 'btn btn-primary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
        <button class="copy btn btn-primary" <?= empty($worklogsData) ? 'style="display: none;"' : '' ?>>
            Копировать
        </button>
        <div class="content-data">
            <?= "<pre>" ?>
            <?= $worklogsData ?>
        </div>
    </div>

<?php

$js = <<<JS
$('.copy').click(function(){
    const el = document.createElement('textarea');
    el.value = $('.content-data').text();
    document.body.appendChild(el);
    el.select();
    document.execCommand('copy');
    document.body.removeChild(el);
});
JS;
$this->registerJS($js);
