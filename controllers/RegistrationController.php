<?php

namespace app\controllers;

use Yii;
// para la descarda de excel del resumen de la base de datos
use yii\db\Query;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
// modelos a usar en el controlador
use app\models\Registration;
use app\models\RegistrationSearch;
use app\models\RegistrationType;
use app\models\RegistrationCode;
use app\models\RegistrationWorkshop;
// Agregamos estos modelos para la relacion con talleres y visitas
use app\models\RegistroTaller;
use app\models\RegistroVisita;
// Este es para almacenar los pagos que se hagan tanto en un submit
// de registro como en un update
use app\models\Pago;
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

                    //Nuevos permisos para los botones del admin
                    //'approve-payment' => ['POST'],
                    //'reject-payment' => ['POST'],
                ],
            ],
			'access' => [
				'class' => 'yii\filters\AccessControl',
				'rules' => [
					[
						'allow' => true,
						'actions' => ['index','view','create','update','delete','mail','export', 'export-resume'],
						'roles' => ['@'],
					],
					[
						'allow' => true,
						'actions' => ['submit','submitted','update-submit','paid','upload-payment-receipt','view-payment-receipt','view-student-id'],
						'roles' => ['?'],
					],
                    [
                        // Esta regla permite el acceso a todos los usuarios (invitados incluidos)
                        'actions' => ['create', 'view', 'update-display-name'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['rechazar-pago', 'verificar-pago', 'view-payment-receipt'],
                        'allow' => true,
                        'roles' => ['@']
                    ]
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

    //Esta función es para exportar los registros a CSV respetando los filtros de búsqueda que podría hacer el admin
    public function actionExport()
    {
        // Aquí instanciamos el modelo de búsqueda para mantener los filtros del admin
        $searchModel = new RegistrationSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        //Esto es para exportar todos los resultados filtrados
        $dataProvider->pagination = false;
        $models = $dataProvider->getModels();

        // Nombre del archivo
        $filename = 'Registros_' . date('Ymd_His') . '.csv';

        // Usamos un archivo temporal en memoria en lugar de la salida directa
        $output = fopen('php://temp', 'w');
        
        // Escribimos el BOM para que Excel detecte correctamente los acentos (UTF-8)
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Escribimos los encabezados
        fputcsv($output, [
            'ID',
            'Folio',
            'Nombre',
            'Apellido',
            'Organización/Compañía',
            'Email',
            'Teléfono',
            'Ciudad',
            'País',
            'Total a Pagar',
            'Estado de Pagos',
            'Confirmado'
        ]);

        // Escribimos los datos
        foreach ($models as $model) {
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
                $model->total_amount,
                $model->estadoPagos(),         
                $model->confirmado ? 'Sí' : 'No'
            ]);
        }

        // Devolvemos el cursor al inicio y leemos el contenido
        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        // Enviamos el archivo usando la respuesta de Yii2
        return Yii::$app->response->sendContentAsFile($csvContent, $filename, [
            'mimeType' => 'text/csv',
            'inline' => false
        ]);
    }

    public function actionExportResume()
    {
        // 1. Instanciamos el documento de Excel
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        // 2. Definimos las tablas a exportar
        $tablesToExport = [
            'registration'       => 'Registros',
            'pagos'              => 'Pagos',
            'talleres'           => 'Talleres',
            'visitas'            => 'Visitas',
            'registros_talleres' => 'Registro_Talleres',
            'registros_visitas'  => 'Registro_Visitas',
            'registration_type'  => 'Tipos_Registro',
            'invoice'            => 'Facturacion'
        ];

        // 3. NUEVO: Definimos qué columnas SÍ queremos exportar (Lista Blanca).
        // Si una tabla NO se pone en este arreglo, se exportarán todas sus columnas por defecto.
        $includedColumns = [
            'registration' => ['id', 'registration_type_id', 'first_name', 'last_name', 'display_name', 'email', 'business_phone', 'organization_name', 'city', 'state', 'country', 'creation_date', 'modification_date'],
            'pagos'        => ['id', 'registration_id', 'mount', 'concepto', 'estado', 'remplazado'],
            // 'talleres'     => ['id', 'nombre', 'modalidad', 'cupos', 'reservados']
        ];

        $sheetIndex = 0;

        foreach ($tablesToExport as $tableName => $sheetTitle) {
            $worksheet = new Worksheet($spreadsheet, $sheetTitle);
            $spreadsheet->addSheet($worksheet, $sheetIndex);

            $schema = Yii::$app->db->getTableSchema($tableName);
            if (!$schema) {
                continue; 
            }
            
            $allColumns = $schema->getColumnNames();
            
            // 4. NUEVO: Lógica de selección de columnas
            if (isset($includedColumns[$tableName])) {
                // array_intersect evita que la base de datos tire un error 
                // si llegas a escribir mal el nombre de una columna en el arreglo de arriba.
                $columnsToExport = array_intersect($includedColumns[$tableName], $allColumns);
            } else {
                // Si la tabla no está especificada, exportamos todas sus columnas
                $columnsToExport = $allColumns;
            }

            // Validamos que haya columnas para exportar (por si el arreglo quedó vacío)
            if (empty($columnsToExport)) {
                continue;
            }

            // Escribimos los encabezados
            $colLetter = 'A';
            foreach ($columnsToExport as $column) {
                $worksheet->setCellValue($colLetter . '1', strtoupper($column));
                $worksheet->getStyle($colLetter . '1')->getFont()->setBold(true);
                $colLetter++;
            }

            // 5. NUEVO: Optimización en BD
            // Al usar select(), la base de datos usa menos memoria RAM.
            $data = (new Query())
                ->select($columnsToExport)
                ->from($tableName)
                ->all();

            // Volcamos los datos
            $rowNum = 2;
            foreach ($data as $row) {
                $colLetter = 'A';
                foreach ($columnsToExport as $column) {
                    $worksheet->setCellValue($colLetter . $rowNum, $row[$column]);
                    $colLetter++;
                }
                $rowNum++;
            }

            $sheetIndex++;
        }

        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'Resumen_ConCEI_Filtrado_' . date('Ymd_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');
        $excelContent = ob_get_clean();

        return Yii::$app->response->sendContentAsFile($excelContent, $filename, [
            'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'inline' => false
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
    // public function actionViewPaymentReceipt($id, $token)
    // {
	// 	$model = $this->findModel($id);
		
	// 	if( $model->token != $token )
	// 	{
	// 		throw new UnauthorizedHttpException("You are not allowed to access this registry");
	// 	}
		
	// 	$pathFile = 'files/payment/'.$model->payment_receipt;
	// 	// var_dump($pathFile);
	// 	if( file_exists( $pathFile ) )
	// 		Yii::$app->response->sendFile($pathFile,null,['inline'=>true]);
	// 	else
	// 		throw new NotFoundHttpException('The requested page does not exist.');
    // }

    // Le ponemos $token = null por defecto para que los Admins puedan entrar a la URL sin mandar token
    public function actionViewPaymentReceipt($pago_id, $token = null)
    {
        // 1. Buscamos el pago específico que queremos ver
        $pago = Pago::findOne($pago_id);
        if (!$pago) {
            throw new NotFoundHttpException('El pago solicitado no existe.');
        }

        // 2. Buscamos al dueño (Registration) de este pago para validar su token
        $registration = Registration::findOne($pago->registration_id);
        if (!$registration) {
            throw new NotFoundHttpException('El registro asociado a este pago no existe.');
        }

        // 3. SEGURIDAD MIXTA: ¿Es invitado o es Administrador?
        if (Yii::$app->user->isGuest) {
            // Si no ha iniciado sesión, es un usuario externo. ¡Exigimos el token exacto!
            if ($registration->token !== $token) {
                throw new UnauthorizedHttpException("No tienes permiso para ver este comprobante.");
            }
        }
        // (Si NO es invitado, es el Admin logueado, así que se salta la validación del token y pasa directo)

        // 4. Ruta del archivo
        // Usamos el alias de Yii para asegurar la ruta absoluta en cualquier servidor
        $pathFile = Yii::getAlias('@webroot/files/payment/') . $pago->comprobante_pago;
        
        // 5. Mostramos el archivo si existe
        if (!empty($pago->comprobante_pago) && file_exists($pathFile)) {
            return Yii::$app->response->sendFile($pathFile, null, ['inline' => true]);
        } else {
            throw new NotFoundHttpException('El archivo físico del comprobante no se encontró en el servidor.');
        }
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
        //NUEVO
        //$registration->confirmado = 0;


		$invoice = new Invoice();
        if ($registration->load(Yii::$app->request->post())) {

            // ==========================================
            // PARA DEPURACIÓN: Congelamos la pantalla
            // echo "<h2 style='color:red;'>DATOS RECIBIDOS EN EL POST:</h2>";
            // echo "<pre>"; 
            // print_r(Yii::$app->request->post()); 
            // echo "</pre>"; 
            // exit;
            // ==========================================

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
        $pago         = new Pago();

		$registration->registration_type_id = 1;
		$registration->payment_type = 2; // credit card
		$registration->invoice_required = 0;

        //NUEVO: Todo registro empieza en revisión
        //$registration->confirmado = 0;

		$invoice = new Invoice();
        if ($registration->load(Yii::$app->request->post())) {

            // ==========================================
            // PARA DEPURACIÓN: Congelamos la pantalla
            // echo "<h2 style='color:red;'>DATOS RECIBIDOS EN EL POST:</h2>";
            // echo "<pre>"; 
            // print_r(Yii::$app->request->post()); 
            // echo "</pre>"; 
            // exit;
            // ==========================================

            $registration->talleres = Yii::$app->request->post('talleres_seleccionados', []);
            $registration->visitas = Yii::$app->request->post('visitas_seleccionadas', []);

            $registration->file_payment_receipt = UploadedFile::getInstance($registration,'file_payment_receipt');
			$pago->comprobante_pago = UploadedFile::getInstance($registration,'file_payment_receipt');
			
			//Nueva línea para el CV
			$registration->file_cv = UploadedFile::getInstance($registration,'file_cv');

			switch($registration->registration_type_id)
			{
				case 3:
				case 4:
				case 7:
				case 9:
				case 12: $registration->file_student_id = UploadedFile::getInstance($registration,'file_student_id'); break;
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

                $transaction = Yii::$app->db->beginTransaction();
                try {
                    // primero guardamos el registro principal para obtener su ID
                    if (!$registration->save(false)) {
                        throw new \Exception('No se pudo guardar el registro principal.');
                    }

                    $pago->registration_id = $registration->id;
                    $pago->estado = 'confirmado';

                    // despues guardamos el pago del registro principal para obtener su ID
                    if (!$pago->save(false)) {
                        throw new \Exception('No se pudo guardar el pago del registro principal.');
                    }

                    // logica del guardado del codigo de registro (si el tipo de registro es por registration code 18)
                    if ($registration->registration_type_id == 18) {
                        $registrationCode = RegistrationCode::find()->where(['code' => $registration->registration_code])->one();
                        // Validamos que el código realmente exista antes de intentar guardar
                        if ($registrationCode) {
                            $registrationCode->registration_id = $registration->id;
                            if (!$registrationCode->save(false)) {
                                throw new \Exception('No se pudo actualizar el estado del código de registro.');
                            }
                        } else {
                            throw new \Exception('El código de registro proporcionado no es válido.');
                        }
                    }
                    
                    // guardado de la factura
                    if ($registration->invoice_required) {
                        $invoice->registration_id = $registration->id;
                        if (!$invoice->save(false)) {
                            throw new \Exception('No se pudo guardar la información de la factura.');
                        }
                    }

                    // agregando registro de talleres seleccionados al registro
                    // $registration->talleres = Yii::$app->request->post('talleres_seleccionados', []);
                    // agregando registro de visitas seleccionadas al registro
                    // $registration->visitas = Yii::$app->request->post('visitas_seleccionadas', []);

                    $talleres = $this->generarTalleres($registration->talleres, $registration->id, $pago->id);
                    $visitas = $this->generarVisitas($registration->visitas, $registration->id, $pago->id);

                    $pago->generarConcepto(
                        $registration->getLastNameCode(), $registration->getFirstNameCode(),
                        $registration->getRegistrationTypeCode(),
                        $registration->talleres, $registration->visitas
                    );

                    // $registration->talleres_seleccionados = $registration->talleresPost;
                    // $registration->visitas_seleccionadas = $registration->visitasPost;

                    // $pago->mount = 0; // O el cálculo real si ya lo tienes
                    $registration->calculateTotalCost();
                    $pago->mount = $registration->total_amount;
                    
                    if (!$pago->save()) {
                        // 1. Extraemos los errores de validación internos de Yii2
                        $erroresYii = json_encode($pago->getErrors());
                        
                        // 2. Los metemos en la excepción para que tu programa en C++ los pueda leer
                        throw new \Exception("Falló la validación del modelo Pago. Detalles: " . $erroresYii);
                    }

                    $registration->ultimo_pago = $pago->id;
                    $registration->save(false);

                    // Si llegamos hasta aquí, todo fue un éxito
                    $transaction->commit();
                    
                    Yii::$app->session->setFlash('registration-submitted-successfully-mail');
                    Yii::$app->mailer->compose('registration/view-mail', ['model'=>$registration,'pago'=>$pago])
                        ->setFrom(Yii::$app->params['adminEmail'])
                        ->setTo($registration->email)
                        ->setCc([Yii::$app->params['coordinatorEmail1'],Yii::$app->params['coordinatorEmail2']])
                        ->setSubject('Notificación de Registro - ConCEI-3')
                        ->send();
                    Yii::$app->session->setFlash('registration-submitted-successfully');
                    return $this->redirect(['submitted', 'id' => $registration->id, 'token' => $registration->token]);
                    
                } catch (\Throwable $e) {
                    // Catch Throwable for PHP 7+ compatibility
                    // Revertimos todos los cambios
                    $transaction->rollBack();

                    // guardamos los errores en runtime/logs/app.log
                    Yii::error("Error al crear Registration/Invoice: " . $e->getMessage() . "\n" . $e->getTraceAsString(), __METHOD__);
                    
                    // MENSAJE PARA EL USUARIO: Amigable y sin exponer datos críticos
                    Yii::$app->session->setFlash('error', 'An unexpected error occurred while processing your registration. Please try again later or contact support.');
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

            $registration->talleres = Yii::$app->request->post('talleres_seleccionados', []);
            $registration->visitas = Yii::$app->request->post('visitas_seleccionadas', []);

            //Se comentó el if para la seccion de actualización (registro pendiente), 11/05/2026

            //if( isset( $registration->change_file_payment_receipt[0] ) && $registration->change_file_payment_receipt[0] === '1' )
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
                // NUEVO: actualizacion de registro con nuestro nuevo sistema de pagos
                $transaction = Yii::$app->db->beginTransaction();
                try {

                    if (!$registration->save()) {
                        throw new \Exception('No se pudo actualizar el registro principal. Detalles: ' . json_encode($registration->getErrors()));
                    }

                    if ($registration->invoice_required) {
                        $invoice->registration_id = $registration->id;
                        if (!$invoice->save()) {
                            throw new \Exception('No se pudo guardar la información de la factura. Detalles: ' . json_encode($invoice->getErrors()));
                        }
                    }

                    // INICIO DEL BLINDAJE DE TALLERES Y VISITAS
                    // ==========================================
                    
                    // Obtenemos lo que el usuario YA tiene registrado en la BD
                    // $talleresPagados = RegistroTaller::find()->select('taller_id')->where(['registration_id' => $registration->id])->column();
                    // $visitasPagadas  = RegistroVisita::find()->select('visita_id')->where(['registration_id' => $registration->id])->column();

                    // // Obtenemos lo que viene del formulario (POST)
                    // $talleresPost = Yii::$app->request->post('talleres_seleccionados', []);
                    // $visitasPost  = Yii::$app->request->post('visitas_seleccionadas', []);

                    // // Comparamos y nos quedamos ÚNICAMENTE con los IDs nuevos.
                    // $nuevosTalleres = array_diff($talleresPost, $talleresPagados);
                    // $nuevasVisitas  = array_diff($visitasPost, $visitasPagadas);

                    // // Si realmente hay talleres o visitas nuevas, generamos un NUEVO pago
                    // if (!empty($nuevosTalleres) || !empty($nuevasVisitas)) {
                        
                    //     $nuevoPago = new Pago();
                    //     $nuevoPago->registration_id = $registration->id;

                    //     //Se comentó esto 11/05/2026

                    //    /*$nuevoPago->estado = 'No Verificado';
                        
                    //     // Si subió un nuevo comprobante para estos nuevos talleres, lo asignamos
                    //     if (isset($registration->change_file_payment_receipt[0]) && $registration->change_file_payment_receipt[0] === '1') {
                    //         $nuevoPago->comprobante_pago = $registration->file_payment_receipt;
                    //     }*/

                    //     // NUEVO: se agregó la generación del concepto de pago
                    //     $nuevoPago->generarConcepto(
                    //         $registration->getLastNameCode(), $registration->getFirstNameCode(),
                    //         '',
                    //         $nuevosTalleres ?? [], $nuevasVisitas ?? []
                    //     );

                    //     $nuevoPago->estado = 'confirmado'; // Cambiamos a confirmado
                            
                    //     // Asignamos el comprobante directamente si el usuario subió uno
                    //     if ($registration->file_payment_receipt) {
                    //         $nuevoPago->comprobante_pago = $registration->file_payment_receipt;
                    //     }

                    //     // Guardamos el nuevo pago
                    //     if (!$nuevoPago->save(false)) {
                    //         throw new \Exception('No se pudo generar el nuevo registro de pago para los talleres/visitas adicionales.');
                    //     }

                    //     // Reutilizamos tus funciones para insertar en las tablas relacionales
                    //     if (!empty($nuevosTalleres)) {
                    //         // Nota: Asumo que generarTalleres hace sus propios inserts o arroja excepciones si falla.
                    //         $this->generarTalleres($nuevosTalleres, $registration->id, $nuevoPago->id);
                    //     }
                    //     if (!empty($nuevasVisitas)) {
                    //         $this->generarVisitas($nuevasVisitas, $registration->id, $nuevoPago->id);
                    //     }
                        
                    //     $registration->ultimo_pago = $nuevoPago->id;

                    //     $registration->save(false);
                    //     // (Opcional) Si necesitas recalcular el costo o actualizar el monto del nuevo pago, hazlo aquí
                    //     // $nuevoPago->mount = ...;
                    //     // $nuevoPago->save();
                    // }
                    // FIN DEL BLINDAJE

                    // NUEVO: creamos nuestro nuevo pago
                    $this->generarPago($registration);

                    // Si llegamos hasta aquí sin que nada truene, confirmamos los cambios en la BD
                    $transaction->commit();
                    
                    Yii::$app->session->setFlash('success', 'Registration updated successfully.');
                    return $this->redirect(['view', 'id' => $registration->id]);

                } catch (\Throwable $e) {
                    // NUEVO: rollback para deshacer los insert que se generán en la actualización (pagos y talleres/visitas)
                    $transaction->rollBack();
                    Yii::error("Error al actualizar Registration/Invoice: " . $e->getMessage() . "\n" . $e->getTraceAsString(), __METHOD__);
                    Yii::$app->session->setFlash('error', 'An unexpected error occurred while updating your registration. Please try again later or contact support.');
                    return $this->redirect(['update', 'id' => $registration->id, 'token' => $registration->token]);
                }
                // if($registration->save())
                // {
                //     $isSaved = true;
                //     if($registration->invoice_required)
                //     {
                //         $invoice->registration_id = $registration->id;
                //         $isSaved = $isSaved && $invoice->save();
                //     }

                //     // INICIO DEL BLINDAJE DE TALLERES Y VISITAS
                //     // ==========================================
                //     if ($isSaved) {
                //         // 1. Obtenemos lo que el usuario YA tiene registrado en la BD
                //         $talleresPagados = RegistroTaller::find()->select('taller_id')->where(['registration_id' => $registration->id])->column();
                //         $visitasPagadas  = RegistroVisita::find()->select('visita_id')->where(['registration_id' => $registration->id])->column();

                //         // 2. Obtenemos lo que viene del formulario (POST)
                //         $talleresPost = Yii::$app->request->post('talleres_seleccionados', []);
                //         $visitasPost  = Yii::$app->request->post('visitas_seleccionadas', []);

                //         // 3. LA MAGIA: Comparamos y nos quedamos ÚNICAMENTE con los IDs nuevos.
                //         $nuevosTalleres = array_diff($talleresPost, $talleresPagados);
                //         $nuevasVisitas  = array_diff($visitasPost, $visitasPagadas);

                //         // 4. Si realmente hay talleres o visitas nuevas, generamos un NUEVO pago
                //         if (!empty($nuevosTalleres) || !empty($nuevasVisitas)) {
                            
                //             $nuevoPago = new Pago();
                //             $nuevoPago->registration_id = $registration->id;
                //             $nuevoPago->estado = 'No Verificado';
                            
                //             // Si subió un nuevo comprobante para estos nuevos talleres, lo asignamos
                //             if (isset($registration->change_file_payment_receipt[0]) && $registration->change_file_payment_receipt[0] === '1') {
                //                 $nuevoPago->comprobante_pago = $registration->file_payment_receipt;
                //             }

                //             if ($nuevoPago->save(false)) {
                //                 // Reutilizamos tus funciones generarTalleres y generarVisitas
                //                 if (!empty($nuevosTalleres)) {
                //                     $this->generarTalleres($nuevosTalleres, $registration->id, $nuevoPago->id);
                //                 }
                //                 if (!empty($nuevasVisitas)) {
                //                     $this->generarVisitas($nuevasVisitas, $registration->id, $nuevoPago->id);
                //                 }
                //             }
                //         }
                //     }
                //     // FIN DEL BLINDAJE
                //     if($isSaved)
                //         return $this->redirect(['view', 'id' => $registration->id]);
                // }
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
	
	public function actionUploadPaymentReceipt($id, $pago_id, $token)
	{
		$registration = $this->findModel($id);
        $pago         = Pago::findOne($pago_id);
        $nuevoPago    = new Pago();
		$registration->scenario = 'UploadPaymentReceipt';
		
		if( $registration->token != $token )
		{
			throw new UnauthorizedHttpException("You are not allowed to access this registry");
		}

        $registros_taller = RegistroTaller::find()->where(['pago_id' => $pago->id])->all();
        $registros_visita  = RegistroVisita::find()->where(['pago_id' => $pago->id])->all();

        $nuevoPago->attributes = $pago->attributes;
        $nuevoPago->id = null;
		
		if ($registration->load(Yii::$app->request->post())) {
			$registration->file_payment_receipt = UploadedFile::getInstance($registration,'file_payment_receipt');
            $nuevoPago->comprobante_pago = UploadedFile::getInstance($registration,'file_payment_receipt');
            $nuevoPago->estado = 'confirmado';
			
            $registration->confirmado = 0; // NUEVO: Regresa a revisión al subir nuevo recibo
            
            $pago->remplazado = 1; // ya esta remplazado

			if($pago->save() && $nuevoPago->save())
			{
                // Relacionamos los registros de talleres y visitas al nuevo pago
                foreach ($registros_taller as $taller) {
                    $taller->pago_id = $nuevoPago->id; // Asignamos el ID del nuevo pago
                    $taller->save(false);
                }
                foreach ($registros_visita as $visita) {
                    $visita->pago_id = $nuevoPago->id; // Asignamos el ID del nuevo pago
                    $visita->save(false);
                }
                $registration->ultimo_pago = $pago->id;
                $registration->save(false);
				Yii::$app->mailer->compose('registration/view-mail', ['model'=>$registration, 'pago'=>$pago])
					->setFrom(Yii::$app->params['adminEmail'])
					->setTo($registration->email)
					->setCc([Yii::$app->params['coordinatorEmail1'], Yii::$app->params['coordinatorEmail2'], Yii::$app->params['accountingEmail']])
					->setSubject('Notificación de registro - ConCEI-3')
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
            
           // Nuevo: 11/05/2026
           // if( isset( $registration->change_file_payment_receipt[0] ) && $registration->change_file_payment_receipt[0] === '1' )
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


                     // Inicio de lo nuevo para ocultar talleres y visitas ya seleccionados
                    if ($isSaved) {
                        // Obtenemos lo que el usuario YA tiene registrado en la BD
                        // $talleresPagados = RegistroTaller::find()->select('taller_id')->where(['registration_id' => $registration->id])->column();
                        // $visitasPagadas  = RegistroVisita::find()->select('visita_id')->where(['registration_id' => $registration->id])->column();

                        // Obtenemos lo que viene del formulario (POST)
                        // $talleresPost = Yii::$app->request->post('talleres_seleccionados', []);
                        // $visitasPost  = Yii::$app->request->post('visitas_seleccionadas', []);

                        // Comparamos y nos quedamos ÚNICAMENTE con los IDs nuevos.
                        // array_diff ignora lo que ya existe y extrae solo las adiciones reales.
                        // $nuevosTalleres = array_diff($talleresPost, $talleresPagados);
                        // $nuevasVisitas  = array_diff($visitasPost, $visitasPagadas);

                        // Si realmente hay talleres o visitas nuevas, generamos un NUEVO pago
                        // if (!empty($nuevosTalleres) || !empty($nuevasVisitas)) {
                            
                        //     $nuevoPago = new Pago();
                        //     $nuevoPago->registration_id = $registration->id;

                        //    /* $nuevoPago->estado = 'No Verificado';
                            
                        //     // Si subió un nuevo comprobante para estos nuevos talleres, lo asignamos
                        //     if (isset($registration->change_file_payment_receipt[0]) && $registration->change_file_payment_receipt[0] === '1') {
                        //         $nuevoPago->comprobante_pago = $registration->file_payment_receipt;
                        //     }*/

                        //     $nuevoPago->estado = 'confirmado'; // Directo a confirmado
                            
                        //     if ($registration->file_payment_receipt) {
                        //         $nuevoPago->comprobante_pago = $registration->file_payment_receipt;
                        //     }
                            

                        //     if ($nuevoPago->save(false)) {
                        //         // Reutilizamos tus funciones generarTalleres y generarVisitas
                        //         if (!empty($nuevosTalleres)) {
                        //             $talleresGenerados = $this->generarTalleres($nuevosTalleres, $registration->id, $nuevoPago->id);
                        //         }
                        //         if (!empty($nuevasVisitas)) {
                        //             $visitasGeneradas = $this->generarVisitas($nuevasVisitas, $registration->id, $nuevoPago->id);
                        //         }

                        //         // Nuevo
                        //         $concei = \app\models\Concei::find()->one();
                        //         $costoTaller = $concei->getCostoTaller();
                        //         $costoVisita = $concei->getCostoVisita();

                        //         $montoNuevosTalleres = count($nuevosTalleres) * $costoTaller;
                        //         $montoNuevasVisitas  = count($nuevasVisitas) * $costoVisita;

                        //         $nuevoPago->mount = $montoNuevosTalleres + $montoNuevasVisitas;
                        //         $nuevoPago->save(false);
                        //         // Fin de lo nuevo

                        //         // Opcional: Generar el concepto del nuevo pago como lo haces en actionSubmit
                        //         $nuevoPago->generarConcepto(
                        //             $registration->getLastNameCode(), $registration->getFirstNameCode(),
                        //             '',
                        //             $nuevosTalleres ?? [], $nuevasVisitas ?? []
                        //         );
                        //         $nuevoPago->save(false);

                        //         $registration->ultimo_pago = $nuevoPago->id;
                        //         $registration->save(false);
                        //     }
                        // }
                    }

                    $this->generarPago($registration);

                    // Fin de lo nuevo
                    if($isSaved)
                        // Código añadido para enviar el correo de actualización
                        try {
                            Yii::$app->mailer->compose('registration/view-mail-update', ['model'=>$registration])
                                ->setFrom(Yii::$app->params['adminEmail'])
                                ->setTo($registration->email)
                                ->setCc([Yii::$app->params['coordinatorEmail1'], Yii::$app->params['coordinatorEmail2']])
                                ->setSubject('Actualización de Registro - ConCEI-3')
                                ->send();
                        } catch (\Throwable $e) {
                            Yii::error("Error al enviar correo de actualización: " . $e->getMessage());
                        }
                        // Fin Código añadido
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

    public function actionRechazarPago($pago_id) 
    {
        // 1. Buscamos el pago específico
        $pago = Pago::findOne($pago_id);
        
        if ($pago) {
            // 2. Obtenemos el registro (el usuario) al que pertenece este pago 
            // para poder enviarle el correo
            $registration = Registration::findOne($pago->registration_id);

            if ($registration) {
                // 3. Borramos el archivo físico del servidor
                // if (!empty($pago->comprobante_pago)) {
                //     $rutaArchivo = Yii::getAlias('@webroot/files/payment/') . $pago->comprobante_pago;
                //     if (file_exists($rutaArchivo)) {
                //         unlink($rutaArchivo);
                //     }
                // }

                // 4. Limpiamos el nombre en la BD y cambiamos el estado
                // $pago->comprobante_pago = ''; // o null, según tus reglas
                $pago->estado = 'rechazado';
                // echo $pago->mount;
                // exit;

                // 5. Guardamos y notificamos
                if ($pago->save(false)) {
                    try {
                        Yii::$app->mailer->compose('registration/view-mail', [
                                'model' => $registration, 
                                'pago' => $pago // Le pasamos el pago a la vista del correo por si quieres imprimir el concepto rechazado
                            ])
                            ->setFrom(Yii::$app->params['adminEmail'])
                            ->setTo($registration->email)
                            ->setCc([Yii::$app->params['coordinatorEmail1'], Yii::$app->params['coordinatorEmail2']])
                            ->setSubject('Acción Requerida: Problema con su comprobante de pago')
                            ->send();
                        
                        Yii::$app->session->setFlash('success', 'El pago fue rechazado y el usuario notificado.');
                    } catch (\Throwable $e) {
                        // Usamos \Throwable para atrapar cualquier error de PHP 7+ al enviar el correo
                        Yii::error("Error al enviar correo de rechazo: " . $e->getMessage());
                        Yii::$app->session->setFlash('warning', 'Pago rechazado, pero el servidor de correo falló. Notifica al usuario manualmente.');
                    }
                } else {
                    Yii::$app->session->setFlash('error', 'Error al intentar actualizar el estado del pago.');
                }
            }
        } else {
            Yii::$app->session->setFlash('error', 'El pago especificado no existe.');
        }

        // Retornamos a la vista del registro principal. 
        // Usamos el registration_id para saber a qué perfil regresar.
        return $this->redirect(['view', 'id' => $pago->registration_id]);
    }

    public function actionVerificarPago($pago_id) 
    {
        // Buscamos el pago específico
        $pago = Pago::findOne($pago_id);
        
        if ($pago) {
            // Cambiamos el estado utilizando la lógica de estados existentes
            $pago->estado = 'Verificado'; 

            // Guardamos los cambios
            if ($pago->save(false)) {
                Yii::$app->session->setFlash('success', 'El pago ha sido marcado como VERIFICADO exitosamente.');
            } else {
                Yii::$app->session->setFlash('error', 'Error al intentar actualizar el estado del pago.');
            }
        } else {
            Yii::$app->session->setFlash('error', 'El pago especificado no existe.');
        }

        // Redireccionamos de vuelta al panel de detalles del registro
        return $this->redirect(['view', 'id' => $pago->registration_id]);
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

    public function generarTalleres($talleres_seleccionados, $registro_id, $pago_id)
    {
        $talleres = [];
        foreach ($talleres_seleccionados as $taller_id) {
            $record = new RegistroTaller();
            $record->registration_id = $registro_id;
            $record->taller_id = $taller_id;
            $record->pago_id = $pago_id;

            if (!$record->save(false)) {
                throw new \Exception('No se pudo guardar el taller seleccionado con ID: ' . $taller_id);
            }

            $taller = Taller::findOne($taller_id);
            if (!$taller) {
                throw new \Exception('No existe un taller con ID: ' . $taller_id);
            }
            $talleres[] = ['id' => $taller_id];
        }

        return $talleres;
    }

    public function generarVisitas($visitas_seleccionadas, $registro_id, $pago_id)
    {
        $visitas = [];

        foreach ($visitas_seleccionadas as $visita_id) {
            $record = new RegistroVisita();
            $record->registration_id = $registro_id;
            $record->visita_id = $visita_id;
            $record->pago_id = $pago_id;

            if (!$record->save(false)) {
                throw new \Exception('No se pudo guardar la visita seleccionada con ID: ' . $visita_id);
            }

            $visita = Visita::findOne($visita_id);
            if (!$visita) {
                throw new \Exception('No existe una visita con ID: ' . $visita_id);
            }
            $visitas[] = ['id' => $visita_id];
        }
        return $visitas;
    }

    // NUEVO: función para generar el pago
    public function generarPago($registration) {
        
        // 1. Obtenemos lo que el usuario YA tiene registrado en la BD (Historial)
        $talleresPagados = RegistroTaller::find()->select('taller_id')->where(['registration_id' => $registration->id])->column();
        $visitasPagadas  = RegistroVisita::find()->select('visita_id')->where(['registration_id' => $registration->id])->column();

        // 2. Obtenemos lo que viene del formulario (POST)
        $talleresPost = Yii::$app->request->post('talleres_seleccionados', []);
        $visitasPost  = Yii::$app->request->post('visitas_seleccionadas', []);

        // 3. Comparamos y nos quedamos ÚNICAMENTE con los IDs nuevos.
        $nuevosTalleres = array_diff($talleresPost, $talleresPagados);
        $nuevasVisitas  = array_diff($visitasPost, $visitasPagadas);

        // Si NO hay nada nuevo, abortamos la creación del pago y devolvemos false
        if (empty($nuevosTalleres) && empty($nuevasVisitas)) {
            return false;
        }

        // ========================================================
        // 4. LÓGICA DEL BENEFICIO (1 elemento gratis)
        // ========================================================
        $tipoRegistro = (int) $registration->registration_type_id;
        $tieneBeneficio = in_array($tipoRegistro, [1, 12, 18]); // El 17 queda excluido automáticamente
        
        $historialTotal = count($talleresPagados) + count($visitasPagadas);
        $beneficioDisponible = ($tieneBeneficio && $historialTotal === 0);

        // Cantidades reales que se van a GUARDAR
        $cantidadGuardarTalleres = count($nuevosTalleres);
        $cantidadGuardarVisitas  = count($nuevasVisitas);

        // Cantidades que se van a COBRAR
        $cantidadCobrarTalleres = $cantidadGuardarTalleres;
        $cantidadCobrarVisitas  = $cantidadGuardarVisitas;

        // Si tiene el beneficio intacto, descontamos 1 elemento de la cuenta a cobrar
        if ($beneficioDisponible) {
            if ($cantidadCobrarTalleres > 0) {
                $cantidadCobrarTalleres--; // Descontamos 1 taller
            } else if ($cantidadCobrarVisitas > 0) {
                $cantidadCobrarVisitas--; // Si no eligió talleres, descontamos 1 visita
            }
        }
        // ========================================================

        // 5. Calculamos el monto final con las cantidades ya descontadas
        $concei = \app\models\Concei::find()->one();
        $costoTaller = $concei->getCostoTaller();
        $costoVisita = $concei->getCostoVisita();

        $montoTotal = ($cantidadCobrarTalleres * $costoTaller) + ($cantidadCobrarVisitas * $costoVisita);

        // 6. CREAMOS EL NUEVO PAGO
        $nuevoPago = new Pago();
        $nuevoPago->registration_id = $registration->id;
        $nuevoPago->estado = 'confirmado'; 
        $nuevoPago->mount = $montoTotal; // Si solo eligió su gratis, el monto será 0.00 y es correcto.

        // Si subió un comprobante, lo asignamos
        if ($registration->file_payment_receipt) {
            $nuevoPago->comprobante_pago = $registration->file_payment_receipt;
        }

        if ($nuevoPago->save(false)) {
            // 7. Generamos los registros en BD reutilizando tus funciones
            if (!empty($nuevosTalleres)) {
                $this->generarTalleres($nuevosTalleres, $registration->id, $nuevoPago->id);
            }
            if (!empty($nuevasVisitas)) {
                $this->generarVisitas($nuevasVisitas, $registration->id, $nuevoPago->id);
            }

            // 8. Generamos el concepto para el banco
            $nuevoPago->generarConcepto(
                $registration->getLastNameCode(), 
                $registration->getFirstNameCode(),
                '', 
                $nuevosTalleres ?? [], 
                $nuevasVisitas ?? []
            );
            $nuevoPago->save(false);

            // 9. Actualizamos el registro con su último pago
            $registration->ultimo_pago = $nuevoPago->id;
            $registration->save(false);

            return true; // Éxito
        }

        return false;
    }

    // NUEVO: funcion para calcular el monto del pago

    //ACCIÓN DEL ADMIN: Aprueba el pago, cambia estatus a confirmado/aceptado y envía correo.
    
   /*public function actionApprovePayment($id)
    {
        $model = $this->findModel($id);
        $model->confirmado = 1;

        if ($model->save(false)) {
            Yii::$app->mailer->compose('registration/approved-mail', ['model' => $model])
                ->setFrom(Yii::$app->params['adminEmail'])
                ->setTo($model->email)
                ->setSubject('Confirmación de Registro - ConCEI-3')
                ->send();

            Yii::$app->session->setFlash('success', 'Pago verificado. Se ha notificado al usuario que su registro está Aceptado.');
        } else {
            Yii::$app->session->setFlash('error', 'Error al guardar en la base de datos.');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }*/

    
    // ACCIÓN DEL ADMIN: Rechaza el pago, lo deja pendiente y envía correo de alerta.
   /* public function actionRejectPayment($id)
    {
        $model = $this->findModel($id);
        $model->confirmado = 0;

        if ($model->save(false)) {
            Yii::$app->mailer->compose('registration/rejected-mail', ['model' => $model])
                ->setFrom(Yii::$app->params['adminEmail'])
                ->setTo($model->email)
                ->setSubject('Atención: Problema con su comprobante de pago - ConCEI-3')
                ->send();

            Yii::$app->session->setFlash('warning', 'Pago rechazado. Se ha notificado al usuario del problema.');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }*/

}