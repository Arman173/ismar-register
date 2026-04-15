<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\RegistroTaller $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="registro-taller-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'registration_id')->textInput() ?>

    <?= $form->field($model, 'taller_id')->textInput() ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
