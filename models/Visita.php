<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "visitas".
 *
 * @property int $id
 * @property string $nombre
 * @property string $descripcion
 * @property string $fecha
 * @property string $horario
 * @property string $modalidad
 * @property int $cupos
 * @property int $reservados
 */
class Visita extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'visitas';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombre', 'descripcion', 'fecha', 'horario', 'modalidad', 'cupos', 'reservados'], 'required'],
            [['cupos', 'reservados'], 'integer'],
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
            'cupos' => 'Cupos',
            'reservados' => 'Reservados',
        ];
    }

    public function getInscritosCount() {
        return \app\models\RegistroVisita::find()->where(['visita_id' => $this->id])->count();
    }

    public function getCupoGeneral() {
        if (!$this->cupos) return true;
        // General: Puede usar todos los cupos
        return $this->getInscritosCount() < $this->cupos;
    }

    public function getCupoOtros() {
        if (!$this->cupos) return true;
        $reservados = $this->reservados ? $this->reservados : 0;
        // Otros: Se les agota antes, dejando el espacio reservado
        $limite = max(0, $this->cupos - $reservados); 
        return $this->getInscritosCount() < $limite;
    }

}

