<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\RegistroTaller $model */

$this->title = 'Create Registro Taller';
$this->params['breadcrumbs'][] = ['label' => 'Registro Tallers', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="registro-taller-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
