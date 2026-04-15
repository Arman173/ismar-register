<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "talleres".
 *
 * @property int $id
 * @property string $nombre
 * @property string $descripcion
 * @property string $fecha
 * @property string $horario
 * @property string $modalidad
 */
class Taller extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'talleres';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombre', 'descripcion', 'fecha', 'horario', 'modalidad'], 'required'],
            [['nombre'], 'string', 'max' => 150],
            [['descripcion'], 'string', 'max' => 1024],
            [['fecha', 'horario'], 'string', 'max' => 255],
            [['modalidad'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nombre' => 'Nombre',
            'descripcion' => 'Descripcion',
            'fecha' => 'Fecha',
            'horario' => 'Horario',
            'modalidad' => 'Modalidad',
        ];
    }

}
