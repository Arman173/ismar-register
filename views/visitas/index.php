<?php

use app\models\Visita;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\VisitaSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Visitas';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="visita-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Visita', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'nombre',
            // --- COLUMNA DE DESCRIPCIÓN CON MODAL (AMIGABLE PARA CELULARES) ---
            [
                'attribute' => 'descripcion',
                'label' => 'Detalles',
                'format' => 'raw',
                'value' => function ($model) {
                    if (empty($model->description)) {
                        return '';
                    }
                    // Ahora es un botón normal que guarda el título y la descripción en "data"
                    return Html::button('<span class="glyphicon glyphicon-info-sign"></span> Leer más', [
                        'class' => 'btn btn-info btn-xs btn-ver-detalles',
                        'data-title' => Html::encode($model->name),
                        'data-details' => Html::encode($model->description),
                    ]);
                },
            ],
            // ------------------------------------------------------------------
            'fecha',
            'horario',
            //'modalidad',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Visita $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>