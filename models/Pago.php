<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pagos".
 *
 * @property int $id
 * @property int $registration_id
 * @property float $mount
 * @property string $concepto
 * @property string $created_at
 * @property string|null $comprobante_pago
 * @property string $estado
 */
class Pago extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pagos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['comprobante_pago'], 'default', 'value' => null],
            [['registration_id', 'mount', 'concepto', 'estado'], 'required'],
            [['registration_id'], 'integer'],
            [['mount'], 'number'],
            [['created_at'], 'safe'],
            [['concepto'], 'string', 'max' => 64],
            // [['comprobante_pago'], 'string', 'max' => 45],
            [['comprobante_pago'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, pdf', 'maxSize' => 1024 * 1024 * 5], // Límite de 5MB
            [['estado'], 'string', 'max' => 16],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'registration_id' => 'Registration ID',
            'mount' => 'Mount',
            'concepto' => 'Concepto',
            'created_at' => 'Created At',
            'comprobante_pago' => 'Comprobante Pago',
            'estado' => 'Estado',
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            
            // Lógica para TODOS (Nuevos y Actualizaciones)

            // if( !empty($this->comprobante_pago)
            if ($this->comprobante_pago instanceof \yii\web\UploadedFile)
			{
				$fileNamePaymentReceipt = uniqid() . '.' . $this->comprobante_pago->extension;
				$this->comprobante_pago->saveAs('files/payment/' . $fileNamePaymentReceipt);
                $this->comprobante_pago = $fileNamePaymentReceipt;
			}

            // Lógica SOLO para registros NUEVOS
            if ($insert) {
                // Ejemplo: Asignar un token inicial que nunca debe cambiar
                if (empty($this->concepto)) {
                    $this->concepto = 'PENDIENTE';
                }
                
                // Aseguramos el estado inicial
                if (empty($this->estado)) {
                    $this->estado = 'No Verificado';
                }
                
                // Aseguramos que mount no esté vacío
                if (empty($this->mount)) {
                    $this->mount = 0;
                }
            } 
            // Lógica SOLO para ACTUALIZACIONES
            else {
                // Ejemplo: Guardar un historial de quién modificó el registro
                // $this->modificado_por = Yii::$app->user->id;
            }

            return true;
        }
        return false;
    }

    // NUEVO: funcion para generar el concepto de pago
    public function generarConcepto($codigo_nombre, $codigo_apellido, $tipo_registro, $talleres_id, $visitas_id)
    {
        $this->concepto = $codigo_nombre . $codigo_apellido . $tipo_registro;

        foreach ($talleres_id as $id) {
            $this->concepto .= "T" . sprintf('%02d', $id);
        }

        foreach ($visitas_id as $id) {
            $this->concepto .= "V" . sprintf('%02d', $id);
        }
    }

}
