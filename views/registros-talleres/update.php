<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\RegistroTaller $model */

$this->title = 'Update Registro Taller: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Registro Tallers', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="registro-taller-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
