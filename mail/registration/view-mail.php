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
		<h2>Data submitted successfully!</h2>
		<p><?= Html::encode($model->fullName) ?>, your data was submitted sucessfully.</p>
	</div>
	<?php endif; ?>
	
    
	<?php if( empty( $model->paid_by_credit_card ) && empty($model->payment_receipt) ): ?>
	<div class="alert alert-warning">
		<h2>Pending Registration - IEEE ISMAR 2016</h2>
		<p>Dear <?= Html::encode($model->prefix) ?> <?= Html::encode($model->fullName) ?>, 
        <br />
        Thank you for registering for the IEEE ISMAR 2016 taking place at Merida, Mexico from September 19-23, 2016.
        <br />
        To complete your registration you need to pay online with credit or debit card, or upload your payment receipt using the link below.
        </p>
		<p><?= Html::a(Yii::t('app', 'Complete Registration'), Url::to(['submitted', 'id' => $model->id, 'token' => $model->token],true), ['class' => 'btn btn-primary']) ?></p>
	</div>
	<?php endif; ?>
	
    
	<?php if( !empty( $model->paid_by_credit_card ) || !empty($model->payment_receipt) ): ?>
	<div class="alert alert-success">
		<h2>Registration Confirmation - IEEE ISMAR 2016</h2>
		<p>Dear <?= Html::encode($model->prefix) ?> <?= Html::encode($model->fullName) ?>, 
        <br />
        Thank you for registering for the IEEE ISMAR 2016 taking place at Merida, Mexico from September 19-23, 2016.</p>
	</div>
	<?php endif; ?>

		<div class="alert alert-info">
		<p>You can update your data using the link below.
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
    <br />
    Dietary restrictions: <?= Html::encode($model->diet) ?>
    <br />
    </p>

	<h3> Registered Information </h3>

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

		<?php
		$t1 = ($model->banquet_ticket);
		?>

		<?php if( ($model->banquet_ticket) > 0 ): ?>
			<tr>
				<td> Additional Ticket to Attend the Banquet (Tuesday, 20 Sep 2016) </td>
				<td>  <?= Html::encode($model->banquet_ticket) ?>  </td>
				<td>70 MXN</td>
				<td> <?= ($t1*70) ?> MXN</td>
			</tr>
		<?php endif; ?>

		<?php
		$t2 = ($model->proceedings_copies);
		?>

		<?php if( ($model->proceedings_copies) > 0 ): ?>
			<tr>
				<td>Additional Copy of Conference Proceedings  </td>
				<td><?= Html::encode($model->proceedings_copies) ?> </td>
				<td>30 MXN</td>
				<td> <?= ($t2*30) ?> MXN</td>
			</tr>
		<?php endif; ?>


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
			$total = (float)($model->registrationType->cost) + $s1*70 + $s2*30;
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
