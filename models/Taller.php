<?php

namespace app\models;

use Yii;
use app\models\RegistroTaller;

/**
 * This is the model class for table "talleres".
 *
 * @property int $id
 * @property string $nombre
 * @property string $descripcion
 * @property string $fecha
 * @property string $horario
 * @property string $modalidad
 * @property string $tallerista
 * @property int $cupos
 * @property int $reservados
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
            [['nombre', 'descripcion', 'fecha', 'horario', 'modalidad', 'tallerista', 'cupos', 'reservados'], 'required'],
            [['cupos', 'reservados'], 'integer'],
            [['nombre', 'tallerista'], 'string', 'max' => 150],
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
            'tallerista' => 'Tallerista',
            'cupos' => 'Cupos sin reservados',
            'reservados' => 'Reservados',
        ];
    }

    public function getInscritosCount() {
        return \app\models\RegistroTaller::find()->where(['taller_id' => $this->id])->count();
    }

    public function getCupoGeneral() {
        if (!$this->cupos) return true;
        $reservados = $this->reservados ? $this->reservados : 0;
        return $this->getInscritosCount() < ($this->cupos + $reservados);
    }

    public function getCupoOtros() {
        if (!$this->cupos) return true;
        return $this->getInscritosCount() < $this->cupos;
    }

}
