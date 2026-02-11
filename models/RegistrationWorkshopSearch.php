<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\RegistrationWorkshop;

/**
 * RegistrationWorkshopSearch represents the model behind the search form of `app\models\RegistrationWorkshop`.
 */
class RegistrationWorkshopSearch extends RegistrationWorkshop
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'registration_id', 'workshop_id'], 'integer'],
            [['created_at'], 'safe'],
            [['cost'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param string|null $formName Form name to be used into `->load()` method.
     *
     * @return ActiveDataProvider
     */
    public function search($params, $formName = null)
    {
        $query = RegistrationWorkshop::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'registration_id' => $this->registration_id,
            'workshop_id' => $this->workshop_id,
            'created_at' => $this->created_at,
            'cost' => $this->cost,
        ]);

        return $dataProvider;
    }
}
