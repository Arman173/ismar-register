<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use app\models\Taller;
use app\models\Visita;
use app\models\RegistroTaller;
use app\models\RegistroVisita;

/* @var $this yii\web\View */
/* @var $model app\models\Registration */
?>

<style>
    table {
        border: solid 2px black;
        width: 100%;
    }
    th {
        background-color: #DDDDDD;
        border: solid 2px black;
        padding: 5px;
    }
    td {
        padding: 5px;
        border: solid 1px #ccc;
    }
</style>

<div class="registration-view">
    <div style="text-align: center; margin-bottom: 20px;">
        <img src="<?= Url::to('@web/img/logo_concei.jpeg', true) ?>" alt="Logo ConCEI" style="max-width: 250px; height: auto;">
    </div>

    <div class="alert <?= $model->confirmado ? 'alert-success' : 'alert-warning' ?>">
        <h2><?= $model->confirmado ? 'Confirmación de Registro' : 'Registro recibido' ?> - ConCEI 3</h2>
        <p>Estimado/a <?= Html::encode($model->fullName) ?>,</p>
        <?php if (!$model->confirmado): ?>
            <p>Hemos recibido su comprobante de pago y su estatus es <strong>En revisión</strong>. Validaremos su transferencia a la brevedad.</p>
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

    <table>
        <thead>
            <tr>
                <th>Concepto</th>
                <th style="text-align: center;">Cantidad</th>
                <th style="text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?= Html::encode($model->registrationType->name) ?></td>
                <td style="text-align: center;">1</td>
                <td style="text-align: right;"><?= number_format($model->registrationType->cost, 2) ?> MXN</td>
            </tr>

            <?php foreach (RegistroTaller::find()->where(['registration_id' => $model->id])->all() as $rt): ?>
                <?php $taller = Taller::findOne($rt->taller_id); ?>
                <tr>
                    <td>Taller: <?= Html::encode($taller ? $taller->nombre : 'Taller Especializado') ?></td>
                    <td style="text-align: center;">1</td>
                    <td style="text-align: right;">--</td>
                </tr>
            <?php endforeach; ?>

            <?php foreach (RegistroVisita::find()->where(['registration_id' => $model->id])->all() as $rv): ?>
                <?php $visita = Visita::findOne($rv->visita_id); ?>
                <tr>
                    <td>Visita: <?= Html::encode($visita ? $visita->nombre : 'Visita Industrial') ?></td>
                    <td style="text-align: center;">1</td>
                    <td style="text-align: right;">--</td>
                </tr>
            <?php endforeach; ?>

            <tr>
                <td colspan="2" style="text-align: right; font-weight: bold; border-top: solid 2px black;">TOTAL PAGADO:</td>
                <td style="text-align: right; font-weight: bold; border-top: solid 2px black;">
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
        <?= DetailView::widget([
            'model' => $model->invoice,
            'attributes' => [
                'business_name',
                'rfc',
                'zip_code',
                'email',
            ],
        ]) ?>
    <?php endif; ?>
</div>