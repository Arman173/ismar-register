<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "registration_workshop".
 *
 * @property int $id
 * @property int $registration_id
 * @property int $workshop_id
 * @property string $created_at
 * @property float|null $cost
 *
 * @property Registration $registration
 * @property Workshops $workshop
 */
class RegistrationWorkshop extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'registration_workshop';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cost'], 'default', 'value' => 0.00],
            [['registration_id', 'workshop_id'], 'required'],
            [['registration_id', 'workshop_id'], 'integer'],
            [['created_at'], 'safe'],
            [['cost'], 'number'],
            [['registration_id'], 'exist', 'skipOnError' => true, 'targetClass' => Registration::class, 'targetAttribute' => ['registration_id' => 'id']],
            [['workshop_id'], 'exist', 'skipOnError' => true, 'targetClass' => Workshops::class, 'targetAttribute' => ['workshop_id' => 'id']],
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
            'workshop_id' => 'Workshop ID',
            'created_at' => 'Created At',
            'cost' => 'Cost',
        ];
    }

    /**
     * Gets query for [[Registration]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRegistration()
    {
        return $this->hasOne(Registration::class, ['id' => 'registration_id']);
    }

    /**
     * Gets query for [[Workshop]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWorkshop()
    {
        return $this->hasOne(Workshops::class, ['id' => 'workshop_id']);
    }

}
