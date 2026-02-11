<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Workshops $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="workshops-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'date')->input('date', [
        'class' => 'form-control',
        'min' => date('Y-m-d'), // Opcional: evita seleccionar fechas pasadas
        'value' => $model->date ? date('Y-m-d', strtotime($model->date)) : null,
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
