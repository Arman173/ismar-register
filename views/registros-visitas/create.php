<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\RegistroVisita $model */

$this->title = 'Create Registro Visita';
$this->params['breadcrumbs'][] = ['label' => 'Registro Visitas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="registro-visita-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
