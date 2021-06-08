<?php

use common\models\enums\SystemSettingsName;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\domains\Settings */
/* @var string $passReplace */
/* @var string $tokenReplace */

$this->title = 'Системные настройки';
$this->params['breadcrumbs'][] = ['label' => 'Настройки', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="settings-index">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, SystemSettingsName::JIRA_URL)->textInput(['maxlength' => true, 'id' => 'jiraUrl']) ?>

    <?= $form->field($model, SystemSettingsName::JIRA_LOGIN)->textInput(['maxlength' => true, 'id' => 'jiraLogin']) ?>

    <?= $form->field($model, SystemSettingsName::JIRA_PASSWORD)->passwordInput(['value' => $passReplace, 'id' => 'jiraPassword']) ?>

    <?= $form->field($model, SystemSettingsName::SLACK_TOKEN)->passwordInput(['value' => $tokenReplace, 'id' => 'slackToken']) ?>

    <?= Html::input('hidden', 'hasChangePassword', 0, ['id' => 'passwordChange']) ?>

    <?= Html::input('hidden', 'hasChangeToken', 0, ['id' => 'tokenChange']) ?>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$script = <<<JS
$('#jiraPassword').on('change', function () {
    $('#passwordChange').val('1');
});

$('#slackToken').on('change', function () {
    $('#tokenChange').val('1');
});

$('#jiraPassword').on('click focusin', function() {
    this.value = '';
});

$('#slackToken').on('click focusin', function() {
    this.value = '';
});

JS;

$this->registerJs($script, View::POS_READY);
?>
