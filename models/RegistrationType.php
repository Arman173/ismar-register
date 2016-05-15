<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "registration_type".
 *
 * @property string $id
 * @property string $name
 * @property string $cost
 *
 * @property Registration[] $registrations
 */
class RegistrationType extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'registration_type';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'cost'], 'required'],
            [['cost'], 'number'],
            [['name'], 'string', 'max' => 45]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'cost' => Yii::t('app', 'Cost'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRegistrations()
    {
        return $this->hasMany(Registration::className(), ['registration_type_id' => 'id']);
    }
	
	public function getNameCost()
	{
		return $this->name . ' ($' . $this->cost . ' USD)';
	}
}
