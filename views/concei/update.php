<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Concei $model */

$this->title = 'Update Concei: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Conceis', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="concei-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
