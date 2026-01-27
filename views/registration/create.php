<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Registration */

$this->title = Yii::t('app', 'Registro ConCEI-2026');
?>
<div class="registration-create">

    <h2><?= Html::encode($this->title) ?></h2>

    <?= $this->render('_form', [
        'registration' => $registration,
		'invoice' => $invoice,
    ]) ?>


</div>
