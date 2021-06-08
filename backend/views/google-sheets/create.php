<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\domains\GoogleSheets */
/* @var $projects array */

$this->title = 'Add Autoload Task';
$this->params['breadcrumbs'][] = ['label' => 'Google Sheets', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="google-sheets-create">

    <?= $this->render('_form', [
        'model' => $model,
        'projects' => $projects,
    ]) ?>

</div>
