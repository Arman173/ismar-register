<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\RegistrationCode */

$this->title = 'Generate Registration Codes';
$this->params['breadcrumbs'][] = ['label' => 'Registration Codes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="registration-code-create">

	<h1><?= Html::encode($this->title) ?></h1>
	
	<div class="registration-code-form">

		<?= Html::beginForm() ?>
		
		<div class="form-group">
			<?= Html::label('¿How many registration codes would you like to generate?') ?>
			<?= Html::textInput('number') ?>
		</div>

		<div class="form-group">
			<?= Html::submitButton('Generate', ['class' => 'btn btn-success']) ?>
		</div>

		<?= Html::endForm() ?>

	</div>
</div>