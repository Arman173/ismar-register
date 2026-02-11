<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\RegistrationWorkshop $model */

$this->title = 'Update Registration Workshop: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Registration Workshops', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="registration-workshop-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
