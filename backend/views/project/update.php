<?php

use common\models\domains\Project;
use yii\web\YiiAsset;

/* @var $this yii\web\View */
/* @var $project Project */

$this->title = 'Изменить проект ' . $project->project_key;
$this->params['breadcrumbs'][] = $this->title;

YiiAsset::register($this);
?>

<div class="container-fluid">
    <?= $this->render('_form', [
            'project' => $project,
    ]) ?>
</div>