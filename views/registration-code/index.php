<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\RegistrationCodeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Registration Codes';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="registration-code-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create One', ['create'], ['class' => 'btn btn-success']) ?>
		<?= Html::a('Generate All', ['generate'], ['class' => 'btn btn-warning', 'data'=>[
			'confirm'=>'This operation will empty the table. Are you shure?',
			'method' => 'post'
		]]) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'code',
            'registration_id',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
