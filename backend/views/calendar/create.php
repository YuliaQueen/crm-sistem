<?php

/* @var $this yii\web\View */
/* @var $model common\models\domains\Calendar */

$this->title = 'Добавить переопределение';

$this->params['breadcrumbs'][] = ['label' => 'Календарь', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="calendar-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
