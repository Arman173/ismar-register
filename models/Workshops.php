<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "workshops".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string|null $date
 * @property string $hr_inicio
 * @property string $hr_fin
 */
class Workshops extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'workshops';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'description', 'hr_inicio', 'hr_fin'], 'required'],
            [['date', 'hr_inicio', 'hr_fin'], 'safe'],
            [['name'], 'string', 'max' => 150],
            [['description'], 'string', 'max' => 200],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'description' => 'Description',
            'date' => 'Date',
            'hr_inicio' => 'Hr Inicio',
            'hr_fin' => 'Hr Fin',
        ];
    }

}
