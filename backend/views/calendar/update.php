<?php

use common\models\domains\Calendar;

/* @var $this yii\web\View */
/* @var $model Calendar */

$this->title = 'Изменить дату: ' . Yii::$app->formatter->asDate($model->date_start, 'long');
$this->params['breadcrumbs'][] = ['label' => 'Календарь', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Изменить дату: ' . Yii::$app->formatter->asDate($model->date_start, 'long');
?>
<div class="calendar-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
