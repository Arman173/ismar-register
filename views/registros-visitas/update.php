<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\RegistroVisita $model */

$this->title = 'Update Registro Visita: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Registro Visitas', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="registro-visita-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
