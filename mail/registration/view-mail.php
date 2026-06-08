<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Taller;
use app\models\Visita;
use app\models\RegistroTaller;
use app\models\RegistroVisita;
use app\models\Concei;
use app\models\Pago;

/* @var $this yii\web\View */
/* @var $model app\models\Registration */
/* @var $pago app\models\Pago */ // Esta la envía tu controlador al rechazar

$estadoPago = $model->estadoPagos(); 

// Validamos si este correo se está enviando desde el botón de Rechazar
$esRechazo = isset($pago) && $pago->estado === 'rechazado';
?>

<div class="registration-view">
    <div style="text-align: center; margin-bottom: 20px;">
        <img src="https://i.postimg.cc/tJs083Gk/logo-concei.jpg" alt="Logo ConCEI" style="max-width: 250px; height: auto;">
    </div>

    <?php if ($esRechazo): ?>
        
        <div style="padding: 15px; margin-bottom: 20px; border: 1px solid #cccccc; border-radius: 4px; background-color: #f9f9f9; color: #333333;">
            <h2 style="margin-top: 0; color: #222222;">Problema con su comprobante - ConCEI 3</h2>
            <p>Estimado/a <?= Html::encode($model->fullName) ?>,</p>
            <p>Le informamos que hemos rechazado su comprobante de pago por irregularidades encontradas. Por favor, ingrese al sistema a su panel de usuario para subir un comprobante <strong> válido </strong> para poder continuar con su registro.</p>
        </div>

    <?php else: ?>
        
        <div style="padding: 15px; margin-bottom: 20px; border: 1px solid #cccccc; border-radius: 4px; background-color: #f9f9f9; color: #333333;">
            <h2 style="margin-top: 0; color: #222222;">Registro confirmado - ConCEI 3</h2>
            <p>Estimado/a <?= Html::encode($model->fullName) ?>,</p>
            <p>Gracias por registrarse al tercer Congreso de Ciencias Exactas e Ingenierías 3, que se llevará a cabo en Mérida, México, del 7 al 9 de octubre de 2026 en el Campus de Ciencias Exactas e Ingenierías (CCEI) de la Universidad Autónoma de Yucatán.</p>
        </div>

    <?php endif; ?>

    <div style="text-align: center; margin: 20px 0;">
        <p>Para acceder a sus datos registrados o si quiere agregar talleres o visitas puede acceder a su panel de usuario en el siguiente enlace:</p>
        <a href="<?= Url::to(['registration/view', 'id' => $model->id], true) ?>" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">
            Ver panel de usuario: 
        </a>
    </div>

    <p style="color: #555; font-size: 0.9em;">
        <?php

        date_default_timezone_set('America/Merida');

        $dias = ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];
        $meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
        $fecha = $dias[date('w')] . ", " . date('d') . " de " . $meses[date('n')-1] . " de " . date('Y');
        $hora = date('H:i');
        echo $fecha . ", " . $hora;

        ?>
    </p>

    <h3><?= $esRechazo ? 'Detalle de lo que se le rechazó' : 'Información registrada' ?></h3>

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
                $concei = Concei::find()->one();
                $esPreventa = $concei ? $concei->es_preventa() : false;
                
                $precioTaller = $concei ? $concei->getCostoTaller() : 100;
                $precioVisita = $concei ? $concei->getCostoVisita() : 100;
                
                $tipoRegistroStr = (string)$model->registration_type_id;
                $tieneGratis = ($tipoRegistroStr === '1' || $tipoRegistroStr === '12');
                $itemsCobrados = 0; 
                
                $costoGafete = $esPreventa ? $model->registrationType->cost_early_bird : $model->registrationType->cost_late;

                if ($esRechazo) {
                    // Si se rechazó, buscamos SOLO lo que pertenece a ese comprobante
                    $talleres = RegistroTaller::find()->where(['pago_id' => $pago->id])->all();
                    $visitas  = RegistroVisita::find()->where(['pago_id' => $pago->id])->all();
                    
                    // Verificamos si en este comprobante rechazado venía incluido el Gafete
                    $primerPago = Pago::find()->where(['registration_id' => $model->id])->orderBy(['id' => SORT_ASC])->one();
                    $mostrarGafete = ($primerPago && $primerPago->id == $pago->id);
                    
                    $montoTotal = $pago->mount;
                    $conceptoFolio = $pago->concepto;
                } else {
                    // Si es un correo normal, mostramos todo lo del usuario
                    $talleres = RegistroTaller::find()->where(['registration_id' => $model->id])->all();
                    $visitas  = RegistroVisita::find()->where(['registration_id' => $model->id])->all();
                    $mostrarGafete = true;
                    
                    $montoTotal = $model->total_amount;
                    $conceptoFolio = $model->getConceptoPago();
                }
            ?>

            <?php if ($mostrarGafete): ?>
            <tr>
                <td style="padding: 8px; border: solid 1px #ccc;"><?= Html::encode($model->registrationType->name) ?></td>
                <td style="padding: 8px; border: solid 1px #ccc; text-align: center;">1</td>
                <td style="padding: 8px; border: solid 1px #ccc; text-align: right;"><?= number_format($costoGafete, 2) ?> MXN</td>
            </tr>
            <?php endif; ?>

            <?php foreach ($talleres as $rt): ?>
                <?php 
                    $taller = Taller::findOne($rt->taller_id); 
                    $precioMostrar = $precioTaller;
                    if ($tieneGratis && $itemsCobrados === 0 && $mostrarGafete) {
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

            <?php foreach ($visitas as $rv): ?>
                <?php 
                    $visita = Visita::findOne($rv->visita_id); 
                    $precioMostrar = $precioVisita;
                    if ($tieneGratis && $itemsCobrados === 0 && $mostrarGafete) {
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
                <td colspan="2" style="text-align: right; font-weight: bold; border-top: solid 2px black; padding: 8px;">TOTAL DEL COMPROBANTE:</td>
                <td style="text-align: right; font-weight: bold; border-top: solid 2px black; padding: 8px;">
                    <?= number_format($montoTotal, 2) ?> MXN
                </td>
            </tr>
        </tbody>
    </table>

    <p style="margin-top: 20px; font-size: 0.95em; color: #555;">
        <strong>Tu folio de transferencia asignado fue:</strong> <?= Html::encode($conceptoFolio) ?>
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