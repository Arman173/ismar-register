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
 * @property string $hr_inicio
 * @property string $hr_fin
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
            [['nombre', 'descripcion', 'fecha', 'hr_inicio', 'hr_fin'], 'required'],
            [['fecha', 'hr_inicio', 'hr_fin'], 'safe'],
            [['nombre'], 'string', 'max' => 150],
            [['descripcion'], 'string', 'max' => 200],
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
            'hr_inicio' => 'Hr Inicio',
            'hr_fin' => 'Hr Fin',
        ];
    }

}
