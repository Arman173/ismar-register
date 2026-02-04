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
 * @property int $time
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
            [['time'], 'default', 'value' => 60],
            [['name', 'description'], 'required'],
            [['date'], 'safe'],
            [['time'], 'integer'],
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
            'time' => 'Time',
        ];
    }

}
