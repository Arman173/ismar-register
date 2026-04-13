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

    <div style="text-align: center; margin-bottom: 20px;">
        <img src="<?= Url::to('@web/img/logo_concei.jpeg', true) ?>" alt="Logo ConCEI" style="max-width: 250px; height: auto;">
    </div>
    
    <?php if(Yii::$app->session->hasFlash('registration-submitted-successfully-mail')): ?>
    <div class="alert alert-success">
    </div>
    <?php endif; ?>
    
    <?php if( !$model->confirmado ): ?>
    <div class="alert alert-warning">
        <h2>Registro Pendiente - ConCEI 3</h2>
        <p>Estimado/a <?= Html::encode($model->fullName) ?>,</p> 
        <p>Gracias por registrarse al tercer Congreso de Ciencias Exactas e Ingenierías 3, que se llevará a cabo en Mérida, México, del 7 al 9 de octubre de 2026 en el Campus de Ciencias Exactas e Ingenierías (CCEI) de la Universidad Autónoma de Yucatán. </p>
        <p>Le informamos que en este momento su estatus es Pendiente de verificación. Nuestro equipo administrativo se encuentra revisando la transacción y validando su transferencia bancaria.</p>
        <p>Una vez que su pago sea verificado, su estatus se actualizará a Confirmado y recibirá un nuevo correo electrónico notificándole que su registro al evento es oficial.  </p>
    </div>
    <?php endif; ?>
    
    <?php if( $model->confirmado ): ?>
    <div class="alert alert-success">
        <h2>Confirmación de Registro - ConCEI 3</h2>
        <p>Estimado/a <?= Html::encode($model->fullName) ?>,</p> 
        <p>Nos complace informarle que su comprobante de pago ha sido <strong>verificado y aceptado exitosamente</strong>.</p>
        <p>Su registro para el tercer Congreso de Ciencias Exactas e Ingenierías (ConCEI 3) está ahora <strong>completo y confirmado</strong>.</p>
    </div> <?php endif; ?>

    <div class="alert alert-info">
        <p>Puede actualizar sus datos utilizando el siguiente enlace.
        <br /><?= Html::a(Yii::t('app', 'Actualizar registro'), Url::to(['submitted', 'id' => $model->id, 'token' => $model->token],true), ['class' => 'btn btn-primary']) ?></p>
    </div>

    <p style="color: #555; font-size: 0.9em;"> 
    <?php 
    $dias = ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];
    $meses = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
    echo $dias[date('w')] . ", " . date('d') . " de " . $meses[date('n')-1] . " de " . date('Y');
    ?> 
    </p>

	<p><?= Html::encode($model->fullName) ?>, 
    <br />
    <?= Html::encode($model->organization_name) ?>
    <br />
    <?= Html::encode($model->city) ?>, <?= Html::encode($model->country) ?>
    <br />
    <?= Html::encode($model->email) ?>
    </p>

	<h3> Información registrada </h3>

	<table>
		<tr>
			<th>Concepto</th>
			<th>#</th>
			<th>Cuota</th>
			<th>Subtotal</th>
		</tr>
		<tr>
			<td><?= Html::encode($model->registrationType->name) ?></td>
			<td>1</td>
			<td><?= Html::encode($model->registrationType->cost) ?> MXN</td>
			<td><?= Html::encode($model->registrationType->cost) ?> MXN</td>
		</tr>


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

    <?php 
        $razonSocial = $model->invoice->business_name;
        if (preg_match('/uady|universidad aut[oó]noma de yucat[aá]n/i', $razonSocial)): 
    ?>
        <div style="background-color: #f2dede; color: #a94442; padding: 15px; margin-bottom: 20px; border: 1px solid #ebccd1; border-radius: 4px;">
            <strong></strong> El ConCEI NO emite facturas a nombre de la Universidad Autónoma de Yucatán. Su factura no podrá ser procesada con estos datos.
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
