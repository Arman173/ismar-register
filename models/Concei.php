<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "concei".
 *
 * @property int $id
 * @property string $titulo
 * @property float $costo_preventa_taller
 * @property float $costo_preventa_visita
 * @property float $costo_taller
 * @property float $costo_visita
 * @property string $fin_preventa
 */
class Concei extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'concei';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['titulo', 'costo_preventa_taller', 'costo_preventa_visita', 'costo_taller', 'costo_visita'], 'required'],
            [['costo_preventa_taller', 'costo_preventa_visita', 'costo_taller', 'costo_visita'], 'number'],
            [['fin_preventa'], 'safe'],
            [['titulo'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'titulo' => 'Titulo',
            'costo_preventa_taller' => 'Costo Preventa Taller',
            'costo_preventa_visita' => 'Costo Preventa Visita',
            'costo_taller' => 'Costo Taller',
            'costo_visita' => 'Costo Visita',
            'fin_preventa' => 'Fin Preventa',
        ];
    }

}
