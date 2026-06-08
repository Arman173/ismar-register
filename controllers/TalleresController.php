<?php

namespace app\controllers;

use Yii;
use app\models\Taller;
use app\models\TallerSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\Registration;

/**
 * TalleresController implements the CRUD actions for Taller model.
 */
class TalleresController extends Controller
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
     * Lists all Taller models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new TallerSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Taller model.
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
     * Creates a new Taller model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Taller();

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
     * Updates an existing Taller model.
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
     * Deletes an existing Taller model.
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
     * Finds the Taller model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Taller the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Taller::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionExport($id)
    {
        $taller = $this->findModel($id);

        // Hacemos un INNER JOIN para traer solo las personas registradas a ESTE taller
        $registrations = Registration::find()
            ->innerJoin('registros_talleres', 'registros_talleres.registration_id = registration.id')
            ->where(['registros_talleres.taller_id' => $id])
            ->all();

        // Limpiamos el nombre para que el archivo no truene si el nombre tiene tildes o caracteres raros
        $safeName = preg_replace('/[^A-Za-z0-9\-]/', '_', $taller->nombre);
        $filename = 'Asistentes_Taller_' . $safeName . '.csv';

        $output = fopen('php://temp', 'w');
        
        // Escribimos el BOM para UTF-8 (Excel lee los acentos correctamente con esto)
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
                $model->estadoPagos(),
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
