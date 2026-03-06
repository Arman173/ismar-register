<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Visita $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="visita-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'nombre')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'descripcion')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'fecha')->input('date', [
        'class' => 'form-control',
        'min' => date('Y-m-d'), // Opcional: evita seleccionar fechas pasadas
        'value' => $model->fecha ? date('Y-m-d', strtotime($model->fecha)) : null,
    ]) ?>

    <?= $form->field($model, 'hr_inicio')->input('time', [
        'class' => 'form-control'
    ]) ?>

    <?= $form->field($model, 'hr_fin')->input('time', [
        'class' => 'form-control'
    ]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
