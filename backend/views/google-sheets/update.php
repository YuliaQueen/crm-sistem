<?php

/* @var $this yii\web\View */
/* @var $model common\models\domains\GoogleSheets */
/* @var $projects Project */

use common\models\domains\Project;

$this->title = 'Update autoload task for: ' . $model->project->project_key;
$this->params['breadcrumbs'][] = ['label' => 'Google Sheets', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="google-sheets-update">

    <?= $this->render('_form', [
        'model' => $model,
        'projects' => $projects
    ]) ?>

</div>
