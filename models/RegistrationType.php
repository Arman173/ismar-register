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
 * @property string $cost_registration
 * @property string $cost_on_site
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
            [['name', 'cost','cost_early_bird', 'cost_registration','cost_on_site'], 'required'],
            [['cost', 'cost_early_bird', 'cost_registration','cost_on_site'], 'number'],
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
            'cost_early_bird' => Yii::t('app', 'Early Bird Fee Deadline'),
            'cost_registration' => Yii::t('app', 'Registration Fee Deadline'),
            'cost_on_site' => Yii::t('app', 'On Site Fee'),
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

    public function getNameCostEarlyBird()
    {
        return ' ($' . $this->cost_early_bird . ' USD)';
    }

    public function getNameCostRegistration()
    {
        return ' ($' . $this->cost_registration . ' USD)';
    }

    public function getNameCostOnSite()
    {
        return ' ($' . $this->cost_on_site . ' USD)';
    }
}
