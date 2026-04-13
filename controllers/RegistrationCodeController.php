<?php

namespace app\controllers;

use Yii;
use app\models\RegistrationCode;
use app\models\RegistrationCodeSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\validators\NumberValidator;

/**
 * RegistrationCodeController implements the CRUD actions for RegistrationCode model.
 */
class RegistrationCodeController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
					'generate' => ['POST'],
                ],
            ],
			'access' => [
				'class' => 'yii\filters\AccessControl',
				'rules' => [
					[
						'allow' => true,
						'actions' => ['index','view','create','update','delete','generate'],
						'roles' => ['@'],
					],
				],
			],
        ];
    }

    /**
     * Lists all RegistrationCode models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new RegistrationCodeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single RegistrationCode model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new RegistrationCode model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new RegistrationCode();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing RegistrationCode model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing RegistrationCode model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }
	
	public function actionGenerate()
    {
        $number = null;
		
		// var_dump(Yii::$app->request->post());

        if ( ($number = Yii::$app->request->post('number')) && (new NumberValidator(['integerOnly'=>true]))->validate($number) ) {
			Yii::$app->db->createCommand()->truncateTable(RegistrationCode::tableName())->execute();
			for($i=0; $i<$number; $i++)
			{
				$registrationCode = new RegistrationCode([
					'code' => registrationCode::getRandom(5),
				]);
				if( !$registrationCode->save() )
					$i--;
			}
			return $this->redirect(['index']);
        }else{
			return $this->render('generate');
		}
    }

    /**
     * Finds the RegistrationCode model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return RegistrationCode the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = RegistrationCode::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
