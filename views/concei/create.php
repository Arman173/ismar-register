<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Concei $model */

$this->title = 'Create Concei';
$this->params['breadcrumbs'][] = ['label' => 'Conceis', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="concei-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
