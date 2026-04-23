<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Pago $model */

$this->title = 'Create Pago';
$this->params['breadcrumbs'][] = ['label' => 'Pagos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pago-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
