<?php

use common\models\domains\Calendar;
use common\models\domains\User;
use yii\web\YiiAsset;

/* @var $this yii\web\View */
/* @var $calendar Calendar */
/* @var $user User */


$this->title = 'Добавить событие';
$this->params['breadcrumbs'][] = $this->title . ' для ' . $user->name;
YiiAsset::register($this);

?>

<div class="container-fluid">

    <?= $this->render('_event-form', [
        'calendar' => $calendar,
    ]) ?>
</div>
