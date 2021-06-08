<?php

use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $assignUrl array */
/* @var $removeUrl array */
/* @var $opts string */
/* @var $disableEdit bool */

$this->registerJs("var _opts = {$opts};", View::POS_BEGIN);
?>
<div class="row">
    <div class="col-lg-5">
        <h3>Доступные</h3>
        <input class="form-control search" data-target="available"
               placeholder="Поиск по доступным">
        <br/>
        <select multiple size="20" class="form-control list"
                data-target="available"<?= $disableEdit ? ' disabled' : '' ?>></select>
    </div>
    <div class="col-lg-2">
        <div class="move-buttons">
            <br><br>
            <?= Html::a('&gt;&gt;', $assignUrl, [
                'class' => 'btn btn-success btn-assign',
                'data-target' => 'available',
                'title' => 'Назначить',
            ]) ?>
            <br/><br/>
            <?= Html::a('&lt;&lt;', $removeUrl, [
                'class' => 'btn btn-danger btn-assign',
                'data-target' => 'assigned',
                'title' => 'Удалить',
            ]) ?>
        </div>
    </div>
    <div class="col-lg-5">
        <h3>Назначенные</h3>
        <input class="form-control search" data-target="assigned"
               placeholder="Поиск по назначенным">
        <br/>
        <select multiple size="20" class="form-control list" data-target="assigned"
                data-target="available"<?= $disableEdit ? ' disabled' : '' ?>></select>
    </div>
</div>
