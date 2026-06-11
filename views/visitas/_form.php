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

    <?= $form->field($model, 'fecha')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'horario')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'modalidad')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'cupos')->textInput() ?>

    <?= $form->field($model, 'reservados')->textInput()->hint('El valor en "cupos" ya es el total. Los "reservados" se descuentan de esa cantidad') ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
