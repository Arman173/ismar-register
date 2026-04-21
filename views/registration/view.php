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
   
		<?php if( $model->confirmado ): ?>
		<div class="alert alert-warning">
        	<h2>Registro pendiente</h2>
			<p><?= Html::encode($model->fullName) ?>, sus datos han sido guardados correctamente.</p>
			<h2></h2>
			<p>Para completar su registro, necesitará subir su comprobante de transferencia bancaria en el botón de abajo.</p>
		</div>
		<?php endif; ?>
		
		<?php if( !$model->confirmado ): ?>
		<div class="alert alert-success">
			<h2>Confirmación de registro</h2>
			<p><?= Html::encode($model->fullName) ?>, <br />Gracias por registrarse al ConCEI-3, que se llevará a cabo en Mérida, México del 7 al 9 de octubre de 2026 en el Campus de Ciencias Exactas e Ingenierías de la Universidad Autónoma de Yucatán (UADY).</p>
		</div>
		<?php endif; ?>

	
	<?= Html::beginForm('http://www.pagos.uady.mx/sim/RecibePago/uady/registropago.php') ?>
	
    <p>
        <?php if( Yii::$app->user->isGuest ): ?>
		
		
		<!-- <?= Html::a(Yii::t('app', 'Upload Payment Receipt'), ['upload-payment-receipt', 'id' => $model->id, 'token' => $model->token ], ['class' => 'btn btn-primary']) ?> -->
		 <?php if( Yii::$app->user->isGuest ): ?>
			<?= Html::a(Yii::t('app', 'Actualizar'), ['update-submit', 'id' => $model->id, 'token'=>$model->token], ['class' => 'btn btn-primary']) ?>
		<?php else: ?>
			<?= Html::a(Yii::t('app', 'Actualizar'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
		<?php endif; ?>
		
		
		<?php else: ?>
		
		<?= Html::a(Yii::t('app', 'Actualizar'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Eliminar'), ['delete', 'id' => $model->id], [
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
		
    </p>
	
	<?= Html::endForm() ?>
	

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
			'folio',
			[
				'label' => 'Tipo de Registro',
				'value' => $model->registrationType->name,
			],
            'organization_name',
            'first_name',
            'last_name',
			[
				'attribute' => 'display_name',
				'label' => 'Nombre Completo'
			],
            // [
            //     'attribute' => 'display_name',
            //     'format' => 'raw',
            //     'value' => function ($model) {
            //         // Creamos un pequeño formulario en línea que apunta a una nueva acción en el controlador
            //         return Html::beginForm(['update-display-name', 'id' => $model->id], 'post', ['class' => 'form-inline'])
            //             . Html::textInput('display_name', $model->display_name, [
            //                 'class' => 'form-control input-sm', 
            //                 'style' => 'display: inline-block; width: auto; margin-right: 5px;'
            //             ])
            //             . Html::submitButton('Actualizar', ['class' => 'btn btn-sm btn-success'])
            //             . Html::endForm();
            //     },
            // ],
            'city',
            'state',
            'country',
            'business_phone',
            'email:email',
			[
				'label' => 'Recibo de pago',
				'value' => Html::a($model->payment_receipt, ['registration/view-payment-receipt', 'id'=>$model->id, 'token'=>$model->token]),
				'format' => 'html',
			],
			[
				'attribute' => 'creation_date',
				'label' => 'Fecha de Creación'
			],
			[
				'attribute' => 'modification_date',
				'visible' => !empty($model->modification_date),
			],
        ],
    ]) ?>
	
	<?php if(!empty($model->invoice)): ?>
	
		<h2>Datos de Facturación</h2>
	
    <?php 
        $razonSocial = $model->invoice->business_name;
        if (preg_match('/uady|universidad aut[oó]noma de yucat[aá]n/i', $razonSocial)): 
    ?>
        <div class="alert alert-danger">
            <strong>Atención:</strong> El ConCEI NO emite facturas a nombre de la Universidad Autónoma de Yucatán. Por favor actualice su información de facturación.
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
