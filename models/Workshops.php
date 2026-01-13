<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "workshops".
 *
 * @property integer $id
 * @property string $name
 */
class Workshops extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'workshops';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 100],
			[['name'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Workshop / Tutorial Title',
			'description' => 'Details',
        ];
    }
}
