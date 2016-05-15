<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $registration app\models\Registration */

$this->title = Yii::t('app', 'Upload Payment Receipt');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Registrations'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $registration->id, 'url' => ['view', 'id' => $registration->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="registration-upload-payment-receipt">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="registration-upload-payment-receipt-form">
		
		<?php $form = ActiveForm::begin([
			'layout' => 'horizontal',
			'options' => ['enctype' => 'multipart/form-data'],
		]); ?>
		
		<?= $form->field($registration, 'file_payment_receipt')->fileInput() ?>
		
		<div class="form-group">
			<?= Html::submitButton(Yii::t('app', 'Upload Receipt'), ['class' => 'btn btn-success']) ?>
		</div>

		<?php ActiveForm::end(); ?>
		
	</div>

</div>
