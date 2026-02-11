<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\RegistrationWorkshop $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="registration-workshop-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'registration_id')->textInput() ?>

    <?= $form->field($model, 'workshop_id')->textInput() ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'cost')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
