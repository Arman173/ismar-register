<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "registros_visitas".
 *
 * @property int $id
 * @property int $registration_id
 * @property int $visita_id
 * @property string $created_at
 */
class RegistroVisita extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'registros_visitas';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['registration_id', 'visita_id'], 'required'],
            [['registration_id', 'visita_id'], 'integer'],
            [['created_at'], 'safe'],
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
            'visita_id' => 'Visita ID',
            'created_at' => 'Created At',
        ];
    }

}
