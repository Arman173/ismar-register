<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "registros_talleres".
 *
 * @property int $id
 * @property int $registration_id
 * @property int $taller_id
 * @property string $created_at
 */
class RegistroTaller extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'registros_talleres';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['registration_id', 'taller_id'], 'required'],
            [['registration_id', 'taller_id'], 'integer'],
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
            'taller_id' => 'Taller ID',
            'created_at' => 'Created At',
        ];
    }

}
