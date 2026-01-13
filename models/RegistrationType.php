<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "registration_type".
 *
 * @property string $id
 * @property string $name
 * @property string $cost
 * @property string $cost_early_bird
 * @property string $cost_late
 * @property string $cost_us
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
            [['name', 'cost','cost_early_bird', 'cost_late','cost_us'], 'required'],
            [['cost', 'cost_early_bird', 'cost_late','cost_us'], 'number'],
            [['name'], 'string', 'max' => 70]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Registration Type'),
            'cost' => Yii::t('app', 'Cost'),
            'cost_early_bird' => Yii::t('app', 'Advanced Fee'),
            'cost_late' => Yii::t('app', 'Late Fee'),
            'cost_us' => Yii::t('app', 'U.S. Fee'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRegistrations()
    {
        return $this->hasMany(Registration::className(), ['registration_type_id' => 'id']);
    }
	
	public function getRegistrationType()
	{
		return $this->name;
	}
	
	public function getNameCost()
	{
		return $this->name . ' $' . $this->cost . ' MXN';
	}

    public function getAdvanceRegistration()
    {
        return  'US $' . $this->cost_early_bird;
    }

    public function getLateRegistration()
    {
        return  'US $' . $this->cost_late;
    }

    public function getCostOnSite()
    {
        return ' $' . $this->cost_us . ' US';
    }
}
