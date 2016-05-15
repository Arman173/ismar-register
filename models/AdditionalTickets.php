<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "additional_tickets".
 *
 * @property integer $id
 * @property string $name
 * @property integer $price
 * @property integer $quantity
 */
class AdditionalTickets extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'additional_tickets';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'price', 'quantity'], 'required'],
            [['price', 'quantity'], 'integer'],
            [['name'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'price' => 'Price',
            'quantity' => 'Quantity',
        ];
    }

    public function getNamePrice()
    {
        return ' ($' . $this->price . ' USD)';
    }


}
