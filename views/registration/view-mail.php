<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Registration */

?>
<div class="registration-view">
	
	<?php if( empty($model->payment_receipt) ): ?>
	<div class="alert alert-warning">
		<p>Para completar su registro deberá subir su comprobante de transferencia bancaria en el siguiente link.</p>
		<p><?= Html::a(Yii::t('app', 'Complete Registration'), ['view', 'id' => $model->id], ['class' => 'btn btn-primary']) ?></p>
	</div>
	<?php endif; ?>
	<
	<?php if( !empty($model->payment_receipt) ): ?>
	<div class="alert alert-success">
		<h1>Registro Completo!</h1>
		<p><?= Html::encode($model->fullName) ?>, su registro fue guardado correctamente, se le notificará cuando su comprobante de pago haya sido revisado y aceptado para que su registro esté completo.</p>
	</div>
	<?php endif; ?>
	
	<div class="alert alert-info">
		<p>Puede ver sus datos registrados en el siguiente enlace!</p>
		<p><?= Html::a(Yii::t('app', 'Registration Data'), ['view', 'id' => $model->id], ['class' => 'btn btn-primary']) ?></p>
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
			// [
			// 	'attribute' => 'modification_date',
			// 	'visible' => !empty($model->modification_date),
			// ],
			// [
			// 	'attribute' => 'paid_by_credit_card',
			// 	'visible' => $model->paid_by_credit_card == true,
			// 	'value' => ($model->paid_by_credit_card)? 'Yes': 'No',
			// ],
			// [
			// 	'attribute' => 'credit_card_import',
			// 	'visible' => $model->paid_by_credit_card == true,
			// ],
			// [
			// 	'attribute' => 'credit_card_autorization',
			// 	'visible' => $model->paid_by_credit_card == true,
			// ],
			// [
			// 	'attribute' => 'credit_card_date_paid',
			// 	'visible' => $model->paid_by_credit_card == true,
			// ],
        ],
    ]) ?>
	
	<?php if(!empty($model->invoice)): ?>
	
<h3>Datos de Facturación</h3>

    <?php 
        $razonSocial = $model->invoice->business_name;
        if (preg_match('/uady|universidad aut[oó]noma de yucat[aá]n/i', $razonSocial)): 
    ?>
        <div style="background-color: #f2dede; color: #a94442; padding: 15px; margin-bottom: 20px; border: 1px solid #ebccd1; border-radius: 4px;">
            <strong>Atención:</strong> El ConCEI NO emite facturas a nombre de la Universidad Autónoma de Yucatán. Su factura no podrá ser procesada con estos datos.
        </div>
    <?php endif; ?>
	
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
