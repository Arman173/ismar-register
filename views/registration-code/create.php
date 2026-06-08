<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\RegistrationCode */

$this->title = 'Crear código de registro';
$this->params['breadcrumbs'][] = ['label' => 'Registration Codes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="registration-code-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
