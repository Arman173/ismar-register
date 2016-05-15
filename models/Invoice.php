<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "invoice".
 *
 * @property string $registration_id
 * @property string $business_name
 * @property string $rfc
 * @property string $address
 * @property string $zip_code
 * @property string $city
 * @property string $state
 * @property string $email
 *
 * @property Registration $registration
 */
class Invoice extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'invoice';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['business_name', 'rfc', 'address', 'zip_code', 'city', 'state', 'email'], 'required', 'whenClient' => 'function (attribute, value){
				return $("[name=\'Registration[invoice_required]\']:checked").val() == "1";
			}'],
            [['business_name', 'address', 'city', 'state', 'email'], 'string', 'max' => 175],
            [['rfc'], 'string', 'max' => 20],
            [['zip_code'], 'string', 'max' => 10],
			[['email'], 'email']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'registration_id' => Yii::t('app', 'Registration ID'),
            'business_name' => Yii::t('app', 'Razón Social'),
            'rfc' => Yii::t('app', 'RFC'),
            'address' => Yii::t('app', 'Dirección'),
            'zip_code' => Yii::t('app', 'Código Postal'),
            'city' => Yii::t('app', 'Ciudad'),
            'state' => Yii::t('app', 'Estado'),
            'email' => Yii::t('app', 'Correo'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRegistration()
    {
        return $this->hasOne(Registration::className(), ['id' => 'registration_id']);
    }
}
