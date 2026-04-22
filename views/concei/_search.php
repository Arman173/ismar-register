<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\ConceiSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="concei-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'titulo') ?>

    <?= $form->field($model, 'costo_preventa_taller') ?>

    <?= $form->field($model, 'costo_preventa_visita') ?>

    <?= $form->field($model, 'costo_taller') ?>

    <?php // echo $form->field($model, 'costo_visita') ?>

    <?php // echo $form->field($model, 'fin_preventa') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
