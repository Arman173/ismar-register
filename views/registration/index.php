<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\RegistrationSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Registrations');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="registration-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Yii::t('app', 'Create Registration'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
			[
				'attribute' => 'registration_type_name',
				'label' => 'Registration Type',
				'value' => function ($model, $key, $index, $column){
					return $model->registrationType->name;
				}
			],
            'organization_name',
			//'prefix',
            'first_name',
            'last_name',
            // 'display_name',
            // 'degree',
            // 'business_phone',
            // 'fax',
            'email:email',
            // 'email2:email',
            // 'address',
            // 'city',
            // 'state',
            // 'zip',
            'country',
            // 'student_id',
            // 'payment_receipt',
            // 'emergency_name',
            // 'emergency_phone',
            // 'token',
			'diet',
			'creation_date',
			'modification_date',
			'payment',
			[
				'attribute' => 'invoice_required',
				'label' => 'Invoice Required',
				'value' => function ($model, $key, $index, $column){
					return ($model->invoice_required)?'true':'false';
				},
				'filter' => Html::activeDropDownList(
					$searchModel,
					'invoice_required',
					[
						0=> 'false',
						1=> 'true'
					],
					[
						'class'=>'form-control',
						'prompt' => ''
					]
				),
			],


            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
