<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use yii\bootstrap\ActiveForm;


/* @var $this yii\web\View */
/* @var $model app\models\Registration */

?>

<style>
	table{
		border: solid 2px black;
	}
	th {
		background-color: #DDDDDD;
		border: solid 2px black;
		padding-right: 3px;
		padding-left: 3px;
	}
	td{
		padding-right: 3px;
		padding-left: 3px;

	}
</style>

<div class="registration-view">
	
	<?php if(Yii::$app->session->hasFlash('registration-submitted-successfully-mail')): ?>
    <div class="alert alert-success">
		<h2>Datos enviados con éxito!</h2>

		<p><?= Html::encode($model->fullName) ?>, sus datos han sido enviando correctamente.</p>
	</div>
	<?php endif; ?>
	
    
	<?php if( empty( $model->paid_by_credit_card ) && empty($model->payment_receipt) ): ?>
	<div class="alert alert-warning">
		<h2>Registro pendiente - ConCEI 3</h2>
		<p>Estimado/a <?= Html::encode($model->prefix) ?> <?= Html::encode($model->fullName) ?>, 
        <br /> 
		Gracias por registrarse al tercer congreso 2026, que se llevará a cabo en Mérida, México, del 7 al 9 de octubre de 2026 en el campus de Ciencias Exactas e ingenierías (CCEI) UADY.
        <br />
		Para completar su registro, es necesario que realice su pago en línea mediante transferencia bancaria, deberá subir su comprobante de pago utilizando el enlace a continuación.        </p>
		<p><?= Html::a(Yii::t('app', 'Complete Registration'), Url::to(['submitted', 'id' => $model->id, 'token' => $model->token],true), ['class' => 'btn btn-primary']) ?></p>
	</div>
	<?php endif; ?>
	
    
	<?php if( !empty( $model->paid_by_credit_card ) || !empty($model->payment_receipt) ): ?>
	<div class="alert alert-success">
		<h2>Confirmación de registro - CONCEI-3</h2>
		<p>Estimado/a <?= Html::encode($model->fullName) ?>, 
        <br />
			Gracias por registrarse al ConCEI 3, que se llevará a cabo en Mérida, México, del 7 al 9 de octubre de 2026 en el campus de Ciencias Exactas e ingenierías (CCEI) UADY.</p>
	</div>
	<?php endif; ?>

		<div class="alert alert-info">
		<p>Puede actualizar sus datos utilizando el siguiente link.
		<br /><?= Html::a(Yii::t('app', 'Update Registration'), Url::to(['submitted', 'id' => $model->id, 'token' => $model->token],true), ['class' => 'btn btn-primary']) ?></p>
	</div>

	<p> <?= date("l"), ", ", date("F"), " ", date("d"), ", ", date("Y")  ?> </p>

	<p><?= Html::encode($model->prefix) ?> <?= Html::encode($model->fullName) ?>, 
    <br />
    <?= Html::encode($model->organization_name) ?>
    <br />
    <?= Html::encode($model->city) ?>, <?= Html::encode($model->country) ?>
    <br />
    <?= Html::encode($model->zip) ?>
    <br />
    <?= Html::encode($model->email) ?>
    </p>

	<h3> Informacipon registrada </h3>

	<table>
		<tr>
			<th>Details</th>
			<th>#</th>
			<th>Fee</th>
			<th>Total</th>
		</tr>
		<tr>
			<td><?= Html::encode($model->registrationType->name) ?></td>
			<td>1</td>
			<td><?= Html::encode($model->registrationType->cost) ?> MXN</td>
			<td><?= Html::encode($model->registrationType->cost) ?> MXN</td>
		</tr>

		<?php if( ($model->W1) == 1 ): ?>
			<tr>
				<td>2nd International Workshop on Diminished Reality as Challenging Issue in Mixed and Augmented Reality </td>
				<td></td>
				<td></td>
				<td></td>
 			</tr>
		<?php endif; ?>

		<?php if( ($model->W2) == 1 ): ?>
			<tr>
				<td>Collaborative Mixed Reality Environments (CoMiRE)</td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
		<?php endif; ?>

		<?php if( ($model->W3) == 1 ): ?>
			<tr>
				<td> Human factors in Augmented Reality </td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
		<?php endif; ?>

		<?php if( ($model->W4) == 1 ): ?>
			<tr>
				<td> Standards for Mixed and Augmented Reality </td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
		<?php endif; ?>

		<?php if( ($model->W5) == 1 ): ?>
			<tr>
				<td>Interaction Design Principles of Augmented Reality focusing on the Ageing Population </td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
		<?php endif; ?>

		<?php if( ($model->W6) == 1 ): ?>
			<tr>
				<td> Workshop on Human Behavior Analysis and Visualization for Collective Visual Sensing </td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
		<?php endif; ?>

		<?php if( ($model->W7) == 1 ): ?>
			<tr>
				<td> MASH'D </td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
		<?php endif; ?>

		<?php if( ($model->T1) == 1 ): ?>
			<tr>
				<td> Daqri Tutorial </td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
		<?php endif; ?>

		<?php
			// UPDATE: implementacion de cast numerico.
			$s1 = (int)($model->banquet_ticket);
			$s2 = (int)($model->proceedings_copies);
			$total = (float)($model->registrationType->cost)
		?>
			<tr>
				<td></td>
				<td></td>
				<td  style = "border-top: solid 2px black; "> Total :</td>
				<td  style = "border-top: solid 2px black; "> <?= $total ?> MXN </td>
			</tr>

	</table>

	<?php if(!empty($model->invoice)): ?>
	
	<h3>Datos de Facturación</h3>
	
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
