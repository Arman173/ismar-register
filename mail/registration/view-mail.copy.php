<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use yii\bootstrap\ActiveForm;
// Agregamos los modelos necesarios para buscar los talleres y visitas
use app\models\Taller;
use app\models\Visita;
use app\models\RegistroTaller;
use app\models\RegistroVisita;

/* @var $this yii\web\View */
/* @var $model app\models\Registration */

?>

<style>
    table{
        border: solid 2px black;
        width: 100%; /* Para que la tabla se vea bien proporcionada */
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
        <h2>Registro confirmado - ConCEI 3</h2>
        <p>Estimado/a <?= Html::encode($model->fullName) ?>,</p> 
        <p>Gracias por registrarse al tercer Congreso de Ciencias Exactas e Ingenierías 3, que se llevará a cabo en Mérida, México, del 7 al 9 de octubre de 2026 en el Campus de Ciencias Exactas e Ingenierías (CCEI) de la Universidad Autónoma de Yucatán. </p>
        <p>Le informamos que hemos recibido su comprobante de pago y su estatus actual es <strong>En revisión</strong>. Nuestro equipo administrativo ya se encuentra validando su transferencia bancaria.</p>
        <p>Tan pronto como el pago sea validado exitosamente, le notificaremos por este mismo medio para que tenga todo listo para el día del congreso.</p>

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
            <th style="text-align: center;">#</th>
            <th style="text-align: right;">Cuota</th>
            <th style="text-align: right;">Subtotal</th>
        </tr>
        <tr>
            <td><?= Html::encode($model->registrationType->name) ?></td>
            <td style="text-align: center;">1</td>
            <td style="text-align: right;"><?= Html::encode($model->registrationType->cost) ?> MXN</td>
            <td style="text-align: right;"><?= Html::encode($model->registrationType->cost) ?> MXN</td>
        </tr>


        <?php
            // Lógica para buscar talleres y visitas
            $talleresBD = RegistroTaller::find()->where(['registration_id' => $model->id])->all();
            $visitasBD = RegistroVisita::find()->where(['registration_id' => $model->id])->all();

            $typeIdStr = (string)$model->registration_type_id;
            // 1 = General, 12 = Estudiante (Ajusta estos IDs si son distintos en tu BD)
            $gratisPermitidos = ($typeIdStr === '1' || $typeIdStr === '12') ? 1 : 0;
            $gratisUsados = 0;
            $costoExtra = 100.00;

            // Inicializamos el total con la cuota base
            $total = (float)($model->registrationType->cost);
        ?>

        <?php foreach ($talleresBD as $rt): ?>
            <?php 
                $taller = Taller::findOne($rt->taller_id);
                $nombreTaller = $taller ? $taller->nombre : 'Taller Especializado';
                $subtotalTaller = $costoExtra;

                if ($gratisUsados < $gratisPermitidos) {
                    $subtotalTaller = 0;
                    $gratisUsados++;
                    $nombreTaller .= ' (Incluido)';
                }
                $total += $subtotalTaller;
            ?>
            <tr>
                <td>Taller: <?= Html::encode($nombreTaller) ?></td>
                <td style="text-align: center;">1</td>
                <td style="text-align: right;"><?= number_format($costoExtra, 2) ?> MXN</td>
                <td style="text-align: right;"><?= number_format($subtotalTaller, 2) ?> MXN</td>
            </tr>
        <?php endforeach; ?>

        <?php foreach ($visitasBD as $rv): ?>
            <?php 
                $visita = Visita::findOne($rv->visita_id);
                $nombreVisita = $visita ? $visita->nombre : 'Visita Industrial';
                $subtotalVisita = $costoExtra;

                if ($gratisUsados < $gratisPermitidos) {
                    $subtotalVisita = 0;
                    $gratisUsados++;
                    $nombreVisita .= ' (Incluido)';
                }
                $total += $subtotalVisita;
            ?>
            <tr>
                <td>Visita: <?= Html::encode($nombreVisita) ?></td>
                <td style="text-align: center;">1</td>
                <td style="text-align: right;"><?= number_format($costoExtra, 2) ?> MXN</td>
                <td style="text-align: right;"><?= number_format($subtotalVisita, 2) ?> MXN</td>
            </tr>
        <?php endforeach; ?>

        <tr>
            <td></td>
            <td></td>
            <td style="border-top: solid 2px black; text-align: right; font-weight: bold;"> Total :</td>
            <td style="border-top: solid 2px black; text-align: right; font-weight: bold;"> <?= number_format($total, 2) ?> MXN </td>
        </tr>

    </table>

    <p style="margin-top: 20px; font-size: 0.95em; color: #555;">
        <strong>Tu folio de transferencia asignado es:</strong> <?= Html::encode($model->getConceptoPago()) ?>
    </p>

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