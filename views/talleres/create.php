<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Taller $model */

$this->title = 'Create Taller';
$this->params['breadcrumbs'][] = ['label' => 'Tallers', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="taller-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
