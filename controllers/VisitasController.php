<?php

namespace app\controllers;

use Yii;
use app\models\Visita;
use app\models\VisitaSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\Registration;

/**
 * VisitasController implements the CRUD actions for Visita model.
 */
class VisitasController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
                'access' => [
                    'class' => 'yii\filters\AccessControl',
                    'rules' => [
                        [
                            'allow' => true,
                            'actions' => ['index','view','create','update','delete'],
                            'roles' => ['@'],
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Visita models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new VisitaSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Visita model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Visita model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Visita();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Visita model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Visita model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Visita model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Visita the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Visita::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionExport($id)
    {
        $visita = $this->findModel($id);

        // Hacemos un INNER JOIN para traer solo las personas registradas a ESTA visita
        $registrations = Registration::find()
            ->innerJoin('registros_visitas', 'registros_visitas.registration_id = registration.id')
            ->where(['registros_visitas.visita_id' => $id])
            ->all();

        $safeName = preg_replace('/[^A-Za-z0-9\-]/', '_', $visita->nombre);
        $filename = 'Asistentes_Visita_' . $safeName . '.csv';

        $output = fopen('php://temp', 'w');
        
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, [
            'ID', 'Folio', 'Nombre', 'Apellido', 'Organización/Compañía',
            'Email', 'Teléfono', 'Ciudad', 'País', 'Estado de Pagos'
        ]);

        foreach ($registrations as $model) {
            fputcsv($output, [
                $model->id,
                $model->getFolio(),             
                $model->first_name,
                $model->last_name,
                $model->organization_name,
                $model->email,
                $model->business_phone,
                $model->city,
                $model->country,
                $model->estadoPagos()
            ]);
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return Yii::$app->response->sendContentAsFile($csvContent, $filename, [
            'mimeType' => 'text/csv',
            'inline' => false
        ]);
    }
}
