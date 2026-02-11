<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\RegistrationWorkshop $model */

$this->title = 'Create Registration Workshop';
$this->params['breadcrumbs'][] = ['label' => 'Registration Workshops', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="registration-workshop-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
