<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Visita $model */

$this->title = 'Create Visita';
$this->params['breadcrumbs'][] = ['label' => 'Visitas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="visita-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
