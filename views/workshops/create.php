<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Workshops $model */

$this->title = 'Create Workshops';
$this->params['breadcrumbs'][] = ['label' => 'Workshops', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="workshops-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
