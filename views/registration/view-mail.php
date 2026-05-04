<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use app\models\RegistroTaller;
use app\models\RegistroVisita;
use app\models\Taller;
use app\models\Visita;

/* @var $this yii\web\View */
/* @var $model app\models\Registration */

// Nos aseguramos de que el total esté calculado usando la función del modelo
if (empty($model->total_amount)) {
    $model->calculateTotalCost();
}

// Buscamos qué talleres y visitas tiene registrados este usuario en la BD
$registrosTalleres = RegistroTaller::find()->where(['registration_id' => $model->id])->all();
$registrosVisitas  = RegistroVisita::find()->where(['registration_id' => $model->id])->all();

?>
<div class="registration-view">
    
    <?php if( !$model->confirmado ): ?>
    <div class="alert alert-warning">
        <p>¡Registro confirmado!</p>
        <p><?= Html::a(Yii::t('app', 'Complete Registration'), ['view', 'id' => $model->id], ['class' => 'btn btn-primary']) ?></p>
    </div>
    <?php endif; ?>
    
    <?php if( $model->confirmado ): ?>
    <div class="alert alert-success">
        <h1>¡Registro Completo!</h1>
        <p><?= Html::encode($model->fullName) ?>, su registro fue guardado correctamente, se le notificará cuando su comprobante de pago haya sido revisado y aceptado para que su registro esté completo.</p>
    </div>
    <?php endif; ?>

    <h3 style="color: #2c3e50; border-bottom: 2px solid #eee; padding-bottom: 5px;">Resumen de su Registro</h3>
    <table border="1" cellpadding="10" cellspacing="0" style="border-collapse: collapse; width: 100%; text-align: left; margin-bottom: 25px; font-family: Arial, sans-serif;">
        <thead style="background-color: #f8f9fa;">
            <tr>
                <th style="color: #333;">Concepto Seleccionado</th>
            </tr>
        </thead>
        <tbody>
            <!-- Tipo de Registro -->
            <tr>
                <td><strong>Tipo de Cuota:</strong> <?= Html::encode($model->registrationType->name) ?></td>
            </tr>
            
            <!-- Talleres -->
            <?php if (!empty($registrosTalleres)): ?>
                <?php foreach ($registrosTalleres as $rt): ?>
                    <?php $taller = Taller::findOne($rt->taller_id); ?>
                    <?php if ($taller): ?>
                    <tr>
                        <td><strong>Taller:</strong> <?= Html::encode($taller->nombre) ?></td>
                    </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Visitas -->
            <?php if (!empty($registrosVisitas)): ?>
                <?php foreach ($registrosVisitas as $rv): ?>
                    <?php $visita = Visita::findOne($rv->visita_id); ?>
                    <?php if ($visita): ?>
                    <tr>
                        <td><strong>Visita Industrial:</strong> <?= Html::encode($visita->nombre) ?></td>
                    </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Total a Pagar -->
            <tr style="background-color: #e8f5e9;">
                <td style="text-align: right; font-size: 1.1em;">
                    <strong>Total a Pagar: <span style="color: #2e7d32;">$<?= number_format($model->total_amount, 2) ?> MXN</span></strong>
                </td>
            </tr>
        </tbody>
    </table>
    <!-- ============================================== -->
    
    <div class="alert alert-info">
        <p>¡Puede ver sus datos registrados y subir su comprobante en el siguiente enlace!</p>
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