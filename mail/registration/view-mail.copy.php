<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Taller;
use app\models\Visita;
use app\models\RegistroTaller;
use app\models\RegistroVisita;
use app\models\Concei;

/* @var $this yii\web\View */
/* @var $model app\models\Registration */
?>

<div class="registration-view">
    <div style="text-align: center; margin-bottom: 20px;">
        <img src="<?= Url::to('@web/img/logo_concei.jpeg', true) ?>" alt="Logo ConCEI" style="max-width: 250px; height: auto;">
    </div>

    <div class="alert <?= $model->confirmado ? 'alert-success' : 'alert-warning' ?>">
        <h2><?= $model->confirmado ? 'Confirmación de Registro' : 'Registro confirmado' ?> - ConCEI 3</h2>
        <p>Estimado/a <?= Html::encode($model->fullName) ?>,</p>
        <?php if (!$model->confirmado): ?>
            <p>Gracias por registrarse al tercer Congreso de Ciencias Exactas e Ingenierías 3, que se llevará a cabo en Mérida, México, del 7 al 9 de octubre de 2026 en el Campus de Ciencias Exactas e Ingenierías (CCEI) de la Universidad Autónoma de Yucatán. </p>
            <p>Le informamos que hemos recibido su comprobante de pago, el cual se encuentra <strong>En revisión</strong>. Nuestro equipo administrativo ya se encuentra validando su transferencia bancaria.</p>
            <p>Podrá visualizar el estatus de su comprobante de pago en el siguiente enlace</p>

        <?php else: ?>
            <p>Su comprobante de pago ha sido <strong>verificado y aceptado exitosamente</strong>. Su registro está completo.</p>
        <?php endif; ?>
    </div>

    <p style="color: #555; font-size: 0.9em;">
        <?php
        $dias = ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];
        $meses = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
        echo $dias[date('w')] . ", " . date('d') . " de " . $meses[date('n')-1] . " de " . date('Y');
        ?>
    </p>

    <p>
        <strong><?= Html::encode($model->fullName) ?></strong><br />
        <?= Html::encode($model->organization_name) ?><br />
        <?= Html::encode($model->city) ?>, <?= Html::encode($model->country) ?><br />
        <?= Html::encode($model->email) ?>
    </p>

    <h3>Información registrada</h3>

    <table style="border: solid 2px black; width: 100%; border-collapse: collapse; font-family: Arial, sans-serif;">
        <thead>
            <tr>
                <th style="background-color: #DDDDDD; border: solid 2px black; padding: 8px; text-align: left;">Concepto</th>
                <th style="background-color: #DDDDDD; border: solid 2px black; padding: 8px; text-align: center;">Cantidad</th>
                <th style="background-color: #DDDDDD; border: solid 2px black; padding: 8px; text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                // Sacamos los precios reales (Preventa o Normal)
                $concei = Concei::find()->one();
                $esPreventa = $concei ? $concei->es_preventa() : false;
                
                $precioTaller = $concei ? $concei->getCostoTaller() : 100;
                $precioVisita = $concei ? $concei->getCostoVisita() : 100;
                
                // Revisamos si el Gafete incluye 1 gratis
                $tipoRegistroStr = (string)$model->registration_type_id;
                $tieneGratis = ($tipoRegistroStr === '1' || $tipoRegistroStr === '12');
                $itemsCobrados = 0; 
                
                // Calculamos el gafete (1000 o 1200)
                $costoGafete = $esPreventa ? $model->registrationType->cost_early_bird : $model->registrationType->cost_late;
            ?>

            <tr>
                <td style="padding: 8px; border: solid 1px #ccc;"><?= Html::encode($model->registrationType->name) ?></td>
                <td style="padding: 8px; border: solid 1px #ccc; text-align: center;">1</td>
                <td style="padding: 8px; border: solid 1px #ccc; text-align: right;"><?= number_format($costoGafete, 2) ?> MXN</td>
            </tr>

            <?php foreach (RegistroTaller::find()->where(['registration_id' => $model->id])->all() as $rt): ?>
                <?php 
                    $taller = Taller::findOne($rt->taller_id); 
                    $precioMostrar = $precioTaller;
                    if ($tieneGratis && $itemsCobrados === 0) {
                        $precioMostrar = 0; 
                    }
                    $itemsCobrados++;
                ?>
                <tr>
                    <td style="padding: 8px; border: solid 1px #ccc;">Taller: <?= Html::encode($taller ? $taller->nombre : 'Taller Especializado') ?></td>
                    <td style="padding: 8px; border: solid 1px #ccc; text-align: center;">1</td>
                    <td style="padding: 8px; border: solid 1px #ccc; text-align: right;"><?= $precioMostrar == 0 ? 'incluido' : number_format($precioMostrar, 2) . ' MXN' ?></td>
                </tr>
            <?php endforeach; ?>

            <?php foreach (RegistroVisita::find()->where(['registration_id' => $model->id])->all() as $rv): ?>
                <?php 
                    $visita = Visita::findOne($rv->visita_id); 
                    $precioMostrar = $precioVisita;
                    if ($tieneGratis && $itemsCobrados === 0) {
                        $precioMostrar = 0; 
                    }
                    $itemsCobrados++;
                ?>
                <tr>
                    <td style="padding: 8px; border: solid 1px #ccc;">Visita: <?= Html::encode($visita ? $visita->nombre : 'Visita Industrial') ?></td>
                    <td style="padding: 8px; border: solid 1px #ccc; text-align: center;">1</td>
                    <td style="padding: 8px; border: solid 1px #ccc; text-align: right;"><?= $precioMostrar == 0 ? 'incluido' : number_format($precioMostrar, 2) . ' MXN' ?></td>
                </tr>
            <?php endforeach; ?>

            <tr>
                <td colspan="2" style="text-align: right; font-weight: bold; border-top: solid 2px black; padding: 8px;">TOTAL PAGADO:</td>
                <td style="text-align: right; font-weight: bold; border-top: solid 2px black; padding: 8px;">
                    <?= number_format($model->total_amount, 2) ?> MXN
                </td>
            </tr>
        </tbody>
    </table>

    <p style="margin-top: 20px; font-size: 0.95em; color: #555;">
        <strong>Tu folio de transferencia asignado fue:</strong> <?= Html::encode($model->getConceptoPago()) ?>
    </p>

    <?php if (!empty($model->invoice)): ?>
        <h3>Datos de Facturación</h3>
        
        <?php 
            $razonSocial = $model->invoice->business_name;
            if (preg_match('/uady|universidad aut[oó]noma de yucat[aá]n/i', $razonSocial)): 
        ?>
            <div style="background-color: #f2dede; color: #a94442; padding: 15px; margin-bottom: 20px; border: 1px solid #ebccd1; border-radius: 4px;">
                <strong>Atención:</strong> El ConCEI NO emite facturas a nombre de la Universidad Autónoma de Yucatán. Su factura no podrá ser procesada con estos datos.
            </div>
        <?php endif; ?>
        
        <table style="border: solid 1px #ccc; width: 100%; border-collapse: collapse; font-family: Arial, sans-serif;">
            <tr><th style="padding: 8px; border: solid 1px #ccc; text-align: left; background: #eee;">Razón Social</th><td style="padding: 8px; border: solid 1px #ccc;"><?= Html::encode($model->invoice->business_name) ?></td></tr>
            <tr><th style="padding: 8px; border: solid 1px #ccc; text-align: left; background: #eee;">RFC</th><td style="padding: 8px; border: solid 1px #ccc;"><?= Html::encode($model->invoice->rfc) ?></td></tr>
            <tr><th style="padding: 8px; border: solid 1px #ccc; text-align: left; background: #eee;">Código Postal</th><td style="padding: 8px; border: solid 1px #ccc;"><?= Html::encode($model->invoice->zip_code) ?></td></tr>
            <tr><th style="padding: 8px; border: solid 1px #ccc; text-align: left; background: #eee;">Email</th><td style="padding: 8px; border: solid 1px #ccc;"><?= Html::encode($model->invoice->email) ?></td></tr>
        </table>
    <?php endif; ?>
</div>