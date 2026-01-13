<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Registration;

/**
 * RegistrationSearch represents the model behind the search form about `app\models\Registration`.
 */
class RegistrationSearch extends Registration
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'registration_type_id'], 'integer'],
            [['organization_name', 'registration_type_name', 'first_name', 'last_name', 'display_name', 'business_phone', 'fax', 'email', 'email2', 'address', 'city', 'state', 'zip', 'country', 'diet','student_id', 'payment_receipt', 'emergency_name', 'emergency_phone', 'token', 'creation_date', 'modification_date', 'payment', 'invoice_required', 'a1'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
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
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Registration::find()->joinWith('registrationType');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
			'pagination' => [
        		'pageSize' => 500,
	    	], 
        ]);
		
		$dataProvider->sort->attributes['registration_type_name'] = [
			'asc' => ['registration_type.name' => SORT_ASC],
			'desc' => ['registration_type.name' => SORT_DESC],
		];

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'registration_type_id' => $this->registration_type_id,
			'invoice_required' => $this->invoice_required,
        ]);

        $query->andFilterWhere(['like', 'registration_type.name', $this->registration_type_name])
			->andFilterWhere(['like', 'organization_name', $this->organization_name])
            ->andFilterWhere(['like', 'first_name', $this->first_name])
            ->andFilterWhere(['like', 'last_name', $this->last_name])
            ->andFilterWhere(['like', 'display_name', $this->display_name])
           // ->andFilterWhere(['like', 'degree', $this->degree])
            ->andFilterWhere(['like', 'business_phone', $this->business_phone])
            ->andFilterWhere(['like', 'fax', $this->fax])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'email2', $this->email2])
            ->andFilterWhere(['like', 'address', $this->address])
            ->andFilterWhere(['like', 'city', $this->city])
            ->andFilterWhere(['like', 'state', $this->state])
//            ->andFilterWhere(['like', 'province', $this->province])
            ->andFilterWhere(['like', 'zip', $this->zip])
            ->andFilterWhere(['like', 'country', $this->country])
            ->andFilterWhere(['like', 'student_id', $this->student_id])
            ->andFilterWhere(['like', 'payment_receipt', $this->payment_receipt])
            ->andFilterWhere(['like', 'emergency_name', $this->emergency_name])
            ->andFilterWhere(['like', 'emergency_phone', $this->emergency_phone])
			->andFilterWhere(['like', 'token', $this->token])
            ->andFilterWhere(['like', 'diet', $this->diet])
            ->andFilterWhere(['like', 'creation_date', $this->creation_date])
			->andFilterWhere(['like', 'modification_date', $this->modification_date])
			->andFilterWhere(['like', 'payment', $this->payment])
            ->andFilterWhere(['like', 'a1', $this->a1]);

        return $dataProvider;
    }
}
