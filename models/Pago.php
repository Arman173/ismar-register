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
            [['comprobante_pago'], 'string', 'max' => 45],
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

}
