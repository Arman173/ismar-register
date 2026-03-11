<?php

namespace app\controllers;

use Yii;
use app\models\Registration;
use app\models\RegistrationSearch;
use app\models\RegistrationType;
use app\models\Invoice;
use app\models\InvoiceSearch;
use app\models\Taller;
use app\models\Visita;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;
use yii\web\BadRequestHttpException;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;

/**
 * RegistrationController implements the CRUD actions for Registration model.
 */
class RegistrationController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                    'update-display-name' => ['POST'],
                ],
            ],
			'access' => [
				'class' => 'yii\filters\AccessControl',
				'rules' => [
					[
						'allow' => true,
						'actions' => ['index','view','create','update','delete','mail'],
						'roles' => ['@'],
					],
					[
						'allow' => true,
						'actions' => ['submit','submitted','update-submit','paid','upload-payment-receipt','view-payment-receipt','view-student-id'],
						'roles' => ['?'],
					],
                    [
                        // Esta regla permite el acceso a todos los usuarios (invitados incluidos)
                        'actions' => ['create', 'view', 'update-display-name'], // <-- ¡Agrega tu acción aquí!
                        'allow' => true,
                        // 'roles' => ['?'], // El '?' significa invitados. A veces se omite para que sea totalmente público.
                    ],
				],
			],
        ];
    }

    /**
     * Lists all Registration models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new RegistrationSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Registration model.
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
     * Displays a single Registration model.
     * @param string $id
     * @return mixed
     */
    public function actionSubmitted($id, $token)
    {
		$model = $this->findModel($id);
		
		if( $model->token != $token )
		{
			throw new UnauthorizedHttpException("You are not allowed to access this registry");
		}
		
        return $this->render('view', [
            'model' => $model,
        ]);
    }
	
	/**
     * Displays a single Registration model.
     * @param string $id
     * @return mixed
     */
    public function actionViewPaymentReceipt($id, $token)
    {
		$model = $this->findModel($id);
		
		if( $model->token != $token )
		{
			throw new UnauthorizedHttpException("You are not allowed to access this registry");
		}
		
		$pathFile = 'files/payment/'.$model->payment_receipt;
		// var_dump($pathFile);
		if( file_exists( $pathFile ) )
			Yii::$app->response->sendFile($pathFile,null,['inline'=>true]);
		else
			throw new NotFoundHttpException('The requested page does not exist.');
    }
	
	/**
     * Displays a single Registration model.
     * @param string $id
     * @return mixed
     */
    public function actionViewStudentId($id, $token)
    {
		$model = $this->findModel($id);
		
		if( $model->token != $token )
		{
			throw new UnauthorizedHttpException("You are not allowed to access this registry");
		}
		
		$pathFile = 'files/studentid/'.$model->student_id;
		// var_dump($pathFile);
		if( file_exists( $pathFile ) )
			Yii::$app->response->sendFile($pathFile,null,['inline'=>true]);
		else
			throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Creates a new Registration model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        // $registration = new Registration(['scenario'=>'Create']);
		$registration = new Registration();
		$registration->invoice_required = 0;
		$registration->registration_type_id = 1;
		$registration->payment_type = 2; // credit card


		$invoice = new Invoice();
        if ($registration->load(Yii::$app->request->post())) {
			$registration->file_payment_receipt = UploadedFile::getInstance($registration,'file_payment_receipt');
			
			//Nueva línea del CV
			$registration->file_cv = UploadedFile::getInstance($registration,'file_cv');

			switch($registration->registration_type_id)
			{
				case 3:
				case 4:
				case 7:
				case 9:
				case 12:
				case 13:
				case 16:
				case 17: $registration->file_student_id = UploadedFile::getInstance($registration,'file_student_id'); break;
			}
			
			$valid = true;
			$valid = $valid && $registration->validate();
			
			if($registration->invoice_required)
			{
				if ($invoice->load(Yii::$app->request->post())) {
					$valid = $valid && $invoice->validate();
				}
			}
			
			if($valid)
            {
                // --- NUEVO: 1. Calcular el costo y asignarlo al modelo ANTES de guardar ---
                $registration->calculateTotalCost();

                // --- NUEVO: 2. Iniciar Transacción de Base de Datos ---
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if($registration->save())
                    {
                        $isSaved = true;

                        // Guardar la factura si aplica
                        if($registration->invoice_required)
                        {
                            $invoice->registration_id = $registration->id;
                            $isSaved = $isSaved && $invoice->save();
                        }

                        // --- NUEVO: 3. Guardar Talleres Seleccionados ---
                        if ($isSaved && !empty($registration->talleres_seleccionados) && is_array($registration->talleres_seleccionados)) {
                            foreach ($registration->talleres_seleccionados as $taller_id) {
                                $rw = new \app\models\RegistrationWorkshop();
                                $rw->registration_id = $registration->id;
                                $rw->workshop_id = $taller_id; // Asegúrate de que los IDs no choquen con las visitas
                                $rw->cost = 100.00; // O la lógica que defina el costo individual
                                $rw->created_at = date('Y-m-d H:i:s');
                                if (!$rw->save()) {
                                    throw new \Exception('Error al guardar Taller.');
                                }
                            }
                        }

                        // --- NUEVO: 4. Guardar Visitas Seleccionadas ---
                        if ($isSaved && !empty($registration->visitas_seleccionadas) && is_array($registration->visitas_seleccionadas)) {
                            foreach ($registration->visitas_seleccionadas as $visita_id) {
                                $rw = new \app\models\RegistrationWorkshop();
                                $rw->registration_id = $registration->id;
                                $rw->workshop_id = $visita_id; 
                                $rw->cost = 100.00;
                                $rw->created_at = date('Y-m-d H:i:s');
                                if (!$rw->save()) {
                                    throw new \Exception('Error al guardar Visita.');
                                }
                            }
                        }

                        if($isSaved)
                        {
                            // Si todo salió perfecto, commitear la transacción
                            $transaction->commit();
                            
                            // Si es actionSubmit, envías los correos aquí como lo tenías.
                            // Si es actionCreate, rediriges a 'view'
                            return $this->redirect(['view', 'id' => $registration->id]); 
                        } else {
                             throw new \Exception('Error general al guardar (Factura o Registro).');
                        }
                    }
                } catch (\Exception $e) {
                    // Si hubo cualquier error, cancelar TODO (Rollback)
                    $transaction->rollBack();
                    Yii::$app->session->setFlash('error', $e->getMessage());
                }
            }
        }

		$dataProviderTalleres = new ActiveDataProvider([
            'query' => Taller::find(),
            'pagination' => false, // O
        ]);

        $dataProviderVisitas = new ActiveDataProvider([
            'query' => Visita::find(),
            'pagination' => false, 
        ]);

		return $this->render('create', [
			'registration' => $registration,
			'invoice' => $invoice,
			'dataProviderTalleres' => $dataProviderTalleres,
            'dataProviderVisitas' => $dataProviderVisitas,
		]);
    }
	
	/**
     * Creates a new Registration model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionSubmit()
    {
        // $registration = new Registration(['scenario'=>'Create']);
		$registration = new Registration();
		$registration->registration_type_id = 1;
		$registration->payment_type = 2; // credit card
		$registration->invoice_required = 0;

		$invoice = new Invoice();
        if ($registration->load(Yii::$app->request->post())) {
			$registration->file_payment_receipt = UploadedFile::getInstance($registration,'file_payment_receipt');
			
			//Nueva línea para el CV
			$registration->file_cv = UploadedFile::getInstance($registration,'file_cv');

			switch($registration->registration_type_id)
			{
				case 3:
				case 4:
				case 7:
				case 9:
				case 12:
				case 13:
				case 16:
				case 17: $registration->file_student_id = UploadedFile::getInstance($registration,'file_student_id'); break;
			}
			
			$valid = true;
			$valid = $valid && $registration->validate();
			
			if($registration->invoice_required)
			{
				if ($invoice->load(Yii::$app->request->post())) {
					$valid = $valid && $invoice->validate();
				}
			}
			
			if($valid)
			{
				if($registration->save())
				{
					$isSaved = true;
					if($registration->invoice_required)
					{
						$invoice->registration_id = $registration->id;
						$isSaved = $isSaved && $invoice->save();
					}
					if($isSaved)
					{
						Yii::$app->session->setFlash('registration-submitted-successfully-mail');
						Yii::$app->mailer->compose('registration/view-mail', ['model'=>$registration])
							->setFrom(Yii::$app->params['adminEmail'])
							->setTo($registration->email)
							->setCc([Yii::$app->params['coordinatorEmail1'],Yii::$app->params['coordinatorEmail2']])
							->setSubject('Registro pendiente - CONCEI-3')
							->send();
						Yii::$app->session->setFlash('registration-submitted-successfully');
						return $this->redirect(['submitted', 'id' => $registration->id, 'token' => $registration->token]);
					}
				}
			}
        }

		$dataProviderTalleres = new ActiveDataProvider([
            'query' => Taller::find(),
            'pagination' => false,
        ]);

        $dataProviderVisitas = new ActiveDataProvider([
            'query' => Visita::find(),
            'pagination' => false, 
        ]);

		return $this->render('create', [
			'registration' => $registration,
			'invoice' => $invoice,
			'dataProviderTalleres' => $dataProviderTalleres,
            'dataProviderVisitas' => $dataProviderVisitas,
		]);
    }
	
	public function actionMail($id, $token)
	{
		$model = $this->findModel($id);
		
		if( $model->token != $token )
		{
			throw new UnauthorizedHttpException("You are not allowed to access this registry");
		}
		
		
	}

    /**
     * Updates an existing Registration model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $registration = $this->findModel($id);
        $registration->scenario = 'Update';
        $registration->invoice_required = 0;
        
        $invoice = (empty($registration->invoice))? new Invoice() : $registration->invoice ;
        $registration->invoice_required = (empty($registration->invoice))? 0 : 1;

        if ($registration->load(Yii::$app->request->post())) {
            
            if( isset( $registration->change_file_payment_receipt[0] ) && $registration->change_file_payment_receipt[0] === '1' )
                $registration->file_payment_receipt = UploadedFile::getInstance($registration,'file_payment_receipt');
            
            //Nueva línea del CV
            $registration->file_cv = UploadedFile::getInstance($registration,'file_cv');

            switch($registration->registration_type_id)
            {
                case 3:
                case 4:
                case 7:
                case 9:
                case 12:
                case 13:
                case 16:
                case 17: if( isset( $registration->change_file_student_id[0] ) && $registration->change_file_student_id[0] === '1' )
                $registration->file_student_id = UploadedFile::getInstance($registration,'file_student_id'); break;
            }
            
            $valid = true;
            $valid = $valid && $registration->validate();
            
            if($registration->invoice_required)
            {
                if ($invoice->load(Yii::$app->request->post())) {
                    $valid = $valid && $invoice->validate();
                }
            }
            
            if($valid)
            {
                if($registration->save())
                {
                    $isSaved = true;
                    if($registration->invoice_required)
                    {
                        $invoice->registration_id = $registration->id;
                        $isSaved = $isSaved && $invoice->save();
                    }
                    if($isSaved)
                        return $this->redirect(['view', 'id' => $registration->id]);
                }
            }
        } 
        
        // Proveedores de datos agregados fuera del if/else
        $dataProviderTalleres = new \yii\data\ActiveDataProvider([
            'query' => \app\models\Taller::find(),
            'pagination' => false, 
        ]);

        $dataProviderVisitas = new \yii\data\ActiveDataProvider([
            'query' => \app\models\Visita::find(),
            'pagination' => false, 
        ]);

        // Este render se ejecuta si es la primera vez que se entra o si falló la validación
        return $this->render('update', [
            'registration' => $registration,
            'invoice' => $invoice,
            'dataProviderTalleres' => $dataProviderTalleres,
            'dataProviderVisitas' => $dataProviderVisitas,
        ]);
    }
	
	public function actionUploadPaymentReceipt($id, $token)
	{
		$registration = $this->findModel($id);
		$registration->scenario = 'UploadPaymentReceipt';
		
		if( $registration->token != $token )
		{
			throw new UnauthorizedHttpException("You are not allowed to access this registry");
		}
		
		if ($registration->load(Yii::$app->request->post())) {
			$registration->file_payment_receipt = UploadedFile::getInstance($registration,'file_payment_receipt');
			
			if($registration->save())
			{
				Yii::$app->mailer->compose('registration/view-mail', ['model'=>$registration])
					->setFrom(Yii::$app->params['adminEmail'])
					->setTo($registration->email)
					->setCc([Yii::$app->params['coordinatorEmail1'], Yii::$app->params['coordinatorEmail2'], Yii::$app->params['accountingEmail']])
					->setSubject('Confirmación de registro - CONCEI 3')
					->send();
				return $this->redirect(['submitted', 'id' => $registration->id, 'token' => $registration->token]);
			}
		}
		
		return $this->render('upload-payment-receipt', [
			'registration' => $registration,
		]);
	}
	
	/**
     * Updates an existing Registration model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdateSubmit($id, $token)
    {
        $registration = $this->findModel($id);
        $registration->scenario = 'Update';
        
        if( $registration->token != $token )
        {
            throw new UnauthorizedHttpException("You are not allowed to access this registry");
        }
        
        $invoice = (empty($registration->invoice))? new Invoice() : $registration->invoice ;
        $registration->invoice_required = (empty($registration->invoice))? 0 : 1;

        if ($registration->load(Yii::$app->request->post())) {
            
            if( isset( $registration->change_file_payment_receipt[0] ) && $registration->change_file_payment_receipt[0] === '1' )
                $registration->file_payment_receipt = UploadedFile::getInstance($registration,'file_payment_receipt');
            
            //Nueva línea patra el CV
            $registration->file_cv = UploadedFile::getInstance($registration,'file_cv');

            switch($registration->registration_type_id)
            {
                case 3:
                case 4:
                case 7:
                case 9:
                case 12:
                case 13:
                case 16:
                case 17: if( isset( $registration->change_file_student_id[0] ) && $registration->change_file_student_id[0] === '1' )
                $registration->file_student_id = UploadedFile::getInstance($registration,'file_student_id'); break;
            }
            
            $valid = true;
            $valid = $valid && $registration->validate();
            
            if($registration->invoice_required)
            {
                if ($invoice->load(Yii::$app->request->post())) {
                    $valid = $valid && $invoice->validate();
                }
            }
            
            if($valid)
            {
                if($registration->save())
                {
                    $isSaved = true;
                    if($registration->invoice_required)
                    {
                        $invoice->registration_id = $registration->id;
                        $isSaved = $isSaved && $invoice->save();
                    }
                    if($isSaved)
                        return $this->redirect(['submitted', 'id' => $registration->id, 'token' => $registration->token]);
                }
            }
        } 
        
        // Proveedores de datos agregados fuera del if/else
        $dataProviderTalleres = new \yii\data\ActiveDataProvider([
            'query' => \app\models\Taller::find(),
            'pagination' => false, 
        ]);

        $dataProviderVisitas = new \yii\data\ActiveDataProvider([
            'query' => \app\models\Visita::find(),
            'pagination' => false, 
        ]);

        // Renderizado por defecto si falla la validación o entra por GET
        return $this->render('update', [
            'registration' => $registration,
            'invoice' => $invoice,
            'dataProviderTalleres' => $dataProviderTalleres,
            'dataProviderVisitas' => $dataProviderVisitas,
        ]);
    }

    /**
     * Deletes an existing Registration model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }
	
	public function actionPaid()
	{
		// var_dump($_POST);
		$this->enableCsrfValidation  = false;
		$post = Yii::$app->request->post();
		if( !empty($post['s_transm']) && !empty($post['c_referencia']) && !empty($post['t_pago']) && !empty($post['t_importe']) && !empty($post['n_autoriz']) && !empty($post['val_2']) && !empty($post['val_3']) )
		{
			$s_transm = Registration::extract_s_transm($post['s_transm']);
			$c_referencia =  Registration::extract_c_referencia($post['c_referencia']);
			if( $s_transm['folio'] == $c_referencia['folio'] )
			{
				$model = $this->findModel($s_transm['folio']);
				if( $model->validateLeftRightToken($s_transm['leftToken'], $c_referencia['rightToken']) )
				{
					$model->paid_by_credit_card = true;
					$model->credit_card_import = $post['t_importe'];
					$model->credit_card_autorization = $post['n_autoriz'];
					$model->credit_card_date_paid = $post['val_2'] . ' ' . $post['val_3'];
					if($model->save())
					{
						Yii::$app->mailer->compose('registration/view-mail', ['model'=>$model])
							->setFrom(Yii::$app->params['adminEmail'])
							->setTo($model->email)
							->setCc([Yii::$app->params['coordinatorEmail1'], Yii::$app->params['coordinatorEmail2'], Yii::$app->params['accountingEmail']])
							->setSubject('Registration complete on International Symposium on Intelligent Computing Systems 2016')
							->send();
						return $this->redirect(['submitted', 'id' => $model->id, 'token' => $model->token]);
					}
					else
						throw new BadRequestHttpException('The request could not be understood by the server due to malformed syntax.');
				}
				else
					throw new NotFoundHttpException('The requested page does not exist.');
			}
			else
				throw new NotFoundHttpException('The requested page does not exist.');
		}
		else
			throw new BadRequestHttpException('The request could not be understood by the server due to malformed syntax.');
	}

    /**
     * Finds the Registration model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Registration the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Registration::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
	
	public function beforeAction($action)
	{
		// your custom code here, if you want the code to run before action filters,
		// wich are triggered on the [[EVENT_BEFORE_ACTION]] event, e.g. PageCache or AccessControl
		
		if ($action->id == 'paid') {
			Yii::$app->controller->enableCsrfValidation = false;
			Yii::$app->request->enableCsrfValidation = false;
			// var_dump($this);
		}

		if (!parent::beforeAction($action)) {
			return false;
		}

		return true; // or false to not run the action
	}

    /**
     * Actualiza únicamente el campo display_name desde la vista detail.
     */
    public function actionUpdateDisplayName($id)
    {
        // 1. Buscamos el registro
        $model = $this->findModel($id);

        // 2. Recibimos el dato enviado por POST
        $newName = Yii::$app->request->post('display_name');

        if ($newName !== null) {
            // 3. Usamos updateAttributes() para forzar el UPDATE en SQL 
            // ignorando las reglas de validación (rules) que bloqueaban el save()
            $model->updateAttributes(['display_name' => $newName]);
            
            // Opcional: Mandamos el mensaje de éxito
            Yii::$app->session->setFlash('success', 'El nombre a mostrar ha sido actualizado correctamente.');
        }

        // 4. Redirigimos EXACTAMENTE a la vista de donde provino la petición
        // Esto evita que el usuario pierda su 'token' en la URL si estaba en la vista 'submitted'
        return $this->redirect(Yii::$app->request->referrer ?: ['view', 'id' => $model->id]);
    }
}
