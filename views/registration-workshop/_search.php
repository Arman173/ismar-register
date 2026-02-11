<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\RegistrationWorkshopSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="registration-workshop-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'registration_id') ?>

    <?= $form->field($model, 'workshop_id') ?>

    <?= $form->field($model, 'created_at') ?>

    <?= $form->field($model, 'cost') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
