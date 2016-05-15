<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Registration */

?>
<div class="registration-view">
	
	<?php if(Yii::$app->session->hasFlash('registration-submitted-successfully')): ?>
	<div class="alert alert-success">
		<h1>Data submitted successfully!</h1>
		<p><?= Html::encode($model->fullName) ?>, your data was submitted sucessfully.</p>
	</div>
	<?php endif; ?>
	
	<?php if( empty( $model->paid_by_credit_card ) && empty($model->payment_receipt) ): ?>
	<div class="alert alert-warning">
		<h1>Attention!</h1>
		<p>To complet your registration you need to pay online with credit or debit card or upload a payment receipt using the link below.</p>
		<p><?= Html::a(Yii::t('app', 'Complete Registration'), ['view', 'id' => $model->id], ['class' => 'btn btn-primary']) ?></p>
	</div>
	<?php endif; ?>
	
	<?php if( !empty( $model->paid_by_credit_card ) || !empty($model->payment_receipt) ): ?>
	<div class="alert alert-success">
		<h1>Registration completed!</h1>
		<p><?= Html::encode($model->fullName) ?>, your registration was completed sucessfully.</p>
	</div>
	<?php endif; ?>
	
	<div class="alert alert-info">
		<p>You can update your data if you require using the link below!</p>
		<p><?= Html::a(Yii::t('app', 'Update Registration'), ['view', 'id' => $model->id], ['class' => 'btn btn-primary']) ?></p>
	</div>
	
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
			[
				'label' => 'Registration Type',
				'value' => $model->registrationType->nameCost,
			],
            'organization_name',
            'first_name',
            'last_name',
            'display_name',
            'degree',
            'business_phone',
            'fax',
            'email:email',
            'email2:email',
            'address',
            'city',
            'state',
            'province',
            'zip',
            'country',
			[
				'label' => 'Student Id',
				'value' => Html::a($model->student_id,'@web/files/studentid/'.$model->student_id),
				'format' => 'html',
			],
			[
				'label' => 'Payment Receipt',
				'value' => Html::a($model->payment_receipt,'@web/files/payment/'.$model->payment_receipt),
				'format' => 'html',
			],
            'emergency_name',
            'emergency_phone',
			'creation_date',
			[
				'attribute' => 'modification_date',
				'visible' => !empty($model->modification_date),
			],
			[
				'attribute' => 'paid_by_credit_card',
				'visible' => $model->paid_by_credit_card == true,
				'value' => ($model->paid_by_credit_card)? 'Yes': 'No',
			],
			[
				'attribute' => 'credit_card_import',
				'visible' => $model->paid_by_credit_card == true,
			],
			[
				'attribute' => 'credit_card_autorization',
				'visible' => $model->paid_by_credit_card == true,
			],
			[
				'attribute' => 'credit_card_date_paid',
				'visible' => $model->paid_by_credit_card == true,
			],
        ],
    ]) ?>
	
	<?php if(!empty($model->invoice)): ?>
	
	<h2>Datos de Facturación</h2>
	
	<?= DetailView::widget([
        'model' => $model->invoice,
        'attributes' => [
			'business_name',
			'rfc',
			'address',
			'zip_code',
			'city',
			'state',
			'email',
        ],
    ]) ?>
	
	<?php endif; ?>

</div>
