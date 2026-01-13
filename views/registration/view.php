<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Registration */


	$s_transm  = $model->create_s_transm();
	
	$c_referencia  = $model->create_c_referencia();
	
	$t_servicio = '99'; // Servicios
	
	$val_6 = '825'; // Clave Cuenta Bancaria
	
	$t_importe = $model->registrationType->cost; // Total de Importe
	
	$val_7 = '58'; // Servicio a utilizar
	
	$s_desc = $model->folio.': '.$model->fullName.', '.$model->registrationType->nameCost; // Descripción
	
	$s_idioma = '02'; // Idioma
	
	$s_concepto = 'Registration Fee – ISMAR 2016'; // Concepto del servicio
	
	$s_nom = $model->first_name.'/'.$model->last_name.'/ /'; // Nombre completo
	
	$s_email = $model->email; // Correo electrónico
	
	$val_8 = '111'; // Medio de pago
	
	$s_verificacion = '0p78fYu54i98utn88vya5oi2n%fg2z65%8a47e!s!!09mG4spi&%hgs';

?>
<div class="registration-view">
   
		<?php if( empty( $model->paid_by_credit_card ) && empty($model->payment_receipt) ): ?>
		<div class="alert alert-warning">
        	<h2>Data Submission</h2>
			<p><?= Html::encode($model->prefix) ?> <?= Html::encode($model->fullName) ?>, your data was submitted sucessfully.</p>
			<h2>Attention!</h2>
			<p>To complete your registration you need to pay online with credit or debit card or upload a payment receipt using the buttons below.</p>
		</div>
		<?php endif; ?>
		
		<?php if( !empty( $model->paid_by_credit_card ) || !empty($model->payment_receipt) ): ?>
		<div class="alert alert-success">
			<h2>Registration Confirmation - IEEE ISMAR 2016</h2>
			<p><?= Html::encode($model->prefix) ?> <?= Html::encode($model->fullName) ?>, <br /> Thank you for registering for the IEEE ISMAR 2016 taking place at Merida, Mexico from September 19-23, 2016.</p>
		</div>
		<?php endif; ?>

	
	<?= Html::beginForm('http://www.pagos.uady.mx/sim/RecibePago/uady/registropago.php') ?>
	
    <p>
        <?php if( Yii::$app->user->isGuest ): ?>
		
		
		<?= Html::a(Yii::t('app', 'Upload Payment Receipt'), ['upload-payment-receipt', 'id' => $model->id, 'token' => $model->token ], ['class' => 'btn btn-primary']) ?>
		
		
		<?php else: ?>
		
		<?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
		
		<?php endif; ?>
		
		
		
		<?= Html::hiddenInput('s_transm', $s_transm) ?>
		<?= Html::hiddenInput('c_referencia', $c_referencia) ?>
		<?= Html::hiddenInput('t_servicio', $t_servicio) ?>
		<?= Html::hiddenInput('val_6', $val_6) ?>
		<?= Html::hiddenInput('val_7', $val_7) ?>
		<?= Html::hiddenInput('t_importe', $t_importe) ?>
		<?= Html::hiddenInput('s_desc', $s_desc) ?>
		<?= Html::hiddenInput('s_idioma', $s_idioma) ?>
		<?= Html::hiddenInput('s_concepto', $s_concepto) ?>
		<?= Html::hiddenInput('s_nom', $s_nom) ?>
		<?= Html::hiddenInput('s_email', $s_email) ?>
		<?= Html::hiddenInput('val_8', $val_8) ?>
		<?= Html::hiddenInput('s_verificacion', $s_verificacion) ?>
		
		<?= Html::submitButton('Pay by Credit Card', ['class' => 'btn btn-primary']) ?>
		
    </p>
	
	<?= Html::endForm() ?>
	

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
			'folio',
			[
				'label' => 'Registration Type',
				'value' => $model->registrationType->nameCost,
			],
            'organization_name',
			'prefix',
            'first_name',
            'last_name',
            'display_name',
            'address',
            'city',
            'state',
            'zip',
            'country',
            'business_phone',
            'fax',
            'email:email',
            'emergency_name',
            'emergency_phone',
			'diet',
			[
				'label' => 'Student Proof',
				'value' => Html::a($model->student_id, ['registration/view-student-id', 'id'=>$model->id, 'token'=>$model->token]),
				'format' => 'html',
			],
			[
				'label' => 'Payment Receipt',
				'value' => Html::a($model->payment_receipt, ['registration/view-payment-receipt', 'id'=>$model->id, 'token'=>$model->token]),
				'format' => 'html',
			],
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







