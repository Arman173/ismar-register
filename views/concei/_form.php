<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Concei $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="concei-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'titulo')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'costo_preventa_taller')->textInput() ?>

    <?= $form->field($model, 'costo_preventa_visita')->textInput() ?>

    <?= $form->field($model, 'costo_taller')->textInput() ?>

    <?= $form->field($model, 'costo_visita')->textInput() ?>

    <?php// Armando: para que el input sea una fecha valida ?>
    <?= $form->field($model, 'fin_preventa')->input('date') ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
