<?php

namespace app\models;

use Yii;
use app\models\RegistrationType;

/**
 * This is the model class for table "registration".
 *
 * @property string $id
 * @property string $registration_type_id
 * @property string $organization_name
 * @property string $first_name
 * @property string $last_name
 * @property string $display_name
 * @property string $business_phone
 * @property string $email
 * @property string $email2
 * @property string $address
 * @property string $city
 * @property string $state
 * @property string $country
 * @property string $student_id
 * @property string $payment_receipt
 * @property string $emergency_name
 * @property string $emergency_phone
 * @property string $token
 * @property int $banquet_ticket
 * @property int $proceedings_copies
 * @property string $one_day_registration
 * @property Invoice $invoice
 * @property RegistrationType $registrationType
 */
class Registration extends \yii\db\ActiveRecord
{
	public $file_payment_receipt;
	public $file_student_id;
	public $change_file_student_id;
	public $change_file_payment_receipt;
	// public $invoice_required = 0;
	public $registration_type_name;
	public $payment_type;
	public $registration_code;

	//Se agregaron estas 3 líneas para lo de los autores:
	public $type2;
    public $title2;
    public $revista_seleccionada;

	public $file_cv; // <-- Para el CV

	// --- VARIABLES PARA TALLERES, VISITAS Y COSTO TOTAL ---
    public $talleres_seleccionados = [];
    public $visitas_seleccionadas = [];
    public $total_amount = 0.00;
	
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'registration';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // 1. ELIMINADO 'zip' de la lista de required
            [['registration_type_id', 'organization_name', 'first_name', 'last_name', 'email', 'invoice_required', 'city', 'country'], 'required'],
            
            [['registration_type_id', 'banquet_ticket', 'proceedings_copies'], 'integer'],
            
            // 2. ELIMINADOS 'display_name', 'address', 'emergency_name' de max 150
            [['organization_name', 'email', 'email2'], 'string', 'max' => 150],
            
            [['first_name', 'last_name', 'city', 'state', 'country','title1'], 'string', 'max' => 100],
            
            // 3. ELIMINADOS 'fax', 'emergency_phone' de max 45
            [['business_phone', 'student_id', 'payment_receipt', 'one_day_registration',], 'string', 'max' => 45],
            
            [['registration_type_id'], 'exist', 'targetClass' => 'app\models\RegistrationType', 'targetAttribute' => 'id'],

            [['email'], 'unique'],
            [['email'], 'email'],
            
			[['invoice_required'], 'required', 'except' => ['Update']],
			[['payment_type'], 'required', 'except' => ['Update']],
			[['payment_type'], 'in', 'range' => [2,3], 'except' => ['Update']],
            // [['file_payment_receipt'], 'file', 'skipOnEmpty' => false, 'extensions' => 'pdf, png, jpg, jpeg, bmp, doc, docx', 'on' => 'UploadPaymentReceipt'],
            // [['file_payment_receipt'], 'file', 'skipOnEmpty' => true, 'extensions' => 'pdf, png, jpg, jpeg, bmp, doc, docx', 'on' => 'Update'],
			[
				['file_payment_receipt'], 
				'file', 
				'skipOnEmpty' => false, 
				'extensions' => 'pdf, png, jpg, jpeg, bmp, doc, docx, zip',
				'when' => function ($model){
					if($model->payment_type == 2){
						return true;
					}
					return false;
				},
				'whenClient' => 'function (attribute,value){
					if( $("[name=\'Registration[payment_type]\']:checked").val() == 2 )
						return true;
					return false;
				}',
				'except' => ['Update'],
			],
			[
				['file_payment_receipt'],
				'required',
				'when' => function ($model){
					if($model->payment_type == 2 && empty($model->payment_receipt))
						return true;
					return false;
				},
				'whenClient' => 'function (attribute,value){
					if( $("[name=\'Registration[payment_type]\']:checked").val() == 2 )
						return true;
					return false;
				}',
				'except' => ['Update'],
			],
			[
				['registration_code'],
				'required',
				'when' => function ($model){
					if($model->payment_type == 3)
						return true;
					return false;
				},
				'whenClient' => 'function (attribute,value){
					if( $("[name=\'Registration[payment_type]\']:checked").val() == 3 )
						return true;
					return false;
				}',
				'except' => ['Update'],
			],
			[['registration_code'], 'validateRegistrationCode','when' => function ($model){
				if($model->payment_type == 3)
					return true;
				return false;
			}, 'except' => ['Update']],
            
            [['file_student_id'], 'file', 'skipOnEmpty' => true, 'extensions' => 'pdf, png, jpg, jpeg, bmp, doc, docx'],
			// <-- CV-->
            [['file_cv'], 'file', 'skipOnEmpty' => true, 'extensions' => 'pdf'], 
            // <-- CAMPO DE LA BD (cv_file) -->
            [['cv_file'], 'string', 'max' => 255],

			[['invoice_required'], 'boolean'],
            [['change_file_payment_receipt', 'change_file_student_id'], 'each', 'rule'=>['in', 'range'=>[0,1]]],
			[['type2', 'title2', 'revista_seleccionada'], 'safe'],
			
			[['talleres_seleccionados', 'visitas_seleccionadas'], 'safe'],
            [['total_amount'], 'number'], // Valida que sea numérico

			[['area_trabajo'], 'string', 'max' => 255],
            [['modalidad_presentacion'], 'string', 'max' => 50],

			// campo para confirmar validacion del comprobante de pago
			[['confirmado'], 'boolean'],
            [['confirmado'], 'default', 'value' => false],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
			'folio' => Yii::t('app', 'Registration Number'),
            'registration_type_id' => Yii::t('app', 'Registration Fee'),
            'organization_name' => Yii::t('app', 'Organization / Company'),
            'first_name' => Yii::t('app', 'First Name'),
            'last_name' => Yii::t('app', 'Last name / Family name'),
            'display_name' => Yii::t('app', 'Display Name'),
//          'degree' => Yii::t('app', 'Degree'),
            'business_phone' => Yii::t('app', 'Phone (incl. country code)'),
            'email' => Yii::t('app', 'Email'),
            'address' => Yii::t('app', 'Address'),
            'city' => Yii::t('app', 'City'),
            'state' => Yii::t('app', 'Province / State'),
            'country' => Yii::t('app', 'Country'),
            'student_id' => Yii::t('app', 'Student Proof'),
			'payment_receipt' => Yii::t('app', 'Payment Receipt'),
			'file_student_id' => Yii::t('app', 'Status Proof (PDF)'),
			'change_file_student_id' => Yii::t('app', 'Status Proof (PDF)'),
			'file_payment_receipt' => Yii::t('app', 'Payment Receipt File'),
			'change_file_payment_receipt' => Yii::t('app', 'Payment Receipt'),
            'emergency_name' => Yii::t('app', 'Emergency Name'),
            'emergency_phone' => Yii::t('app', 'Emergency Phone'),
            'token' => Yii::t('app', 'Token'),
			'creation_date' => Yii::t('app', 'Creation Date'),
			'modification_date' => Yii::t('app', 'Modification Date'),
			'paid_by_credit_card' => Yii::t('app', 'Paid by Credit Card'),
			'invoice_required' => Yii::t('app', 'Factura (Mexicanos con RFC)'),
			'payment' => Yii::t('app', 'Payment'),
			'type1' => Yii::t('app', 'Type'),
			'title1' => Yii::t('app', 'Title'),
			'one_day_registration' => Yii::t('app', 'One Day Registration'),
			'banquet_ticket' => Yii::t('app', 'Additional Ticket to Attend the Banquet '),
			'proceedings_copies' => Yii::t('app', 'Additional Copy of Conference Proceedings'),
			'file_cv' => Yii::t('app', 'Curriculum Vitae (CV)'), // <-- Línea nueva para el CV
			'area_trabajo' => Yii::t('app', 'Área a la que pertenece su trabajo'),
            'modalidad_presentacion' => Yii::t('app', 'Modalidad de presentación deseada'),

			'confirmado' => Yii::t('app', 'Confirmado'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */



	public function getRegistration()
	{
		return $this->name;
	}

    public function getInvoice()
    {
        return $this->hasOne(Invoice::className(), ['registration_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRegistrationType()
    {
        return $this->hasOne(RegistrationType::className(), ['id' => 'registration_type_id']);
    }

	/**
     * Calcula y asigna el costo total del registro incluyendo talleres/visitas extra.
     * Réplica exacta de la lógica del frontend.
     */
    public function calculateTotalCost()
    {
        $costoTaller = 100.00; 
        $fechaCambioPrecio = '2026-02-17';
        $isEarlyBird = (date('Y-m-d') <= $fechaCambioPrecio);

        // 1. Obtener costo base usando la relación (si está cargada) o buscando el tipo
        $baseCost = 0;
        $tipoRegistro = RegistrationType::findOne($this->registration_type_id);
        if ($tipoRegistro) {
            $baseCost = $isEarlyBird ? $tipoRegistro->cost_early_bird : $tipoRegistro->cost_late;
        }

        // 2. Contar extras (Asegurándonos de que no sean vacíos/null)
        $countTalleres = is_array($this->talleres_seleccionados) ? count($this->talleres_seleccionados) : 0;
        $countVisitas = is_array($this->visitas_seleccionadas) ? count($this->visitas_seleccionadas) : 0;
        $totalExtrasCount = $countTalleres + $countVisitas;

        // 3. Lógica de cobro (1 gratis para General(1) y Estudiante(12))
        $paidExtras = 0;
        $typeIdStr = (string)$this->registration_type_id;

        if ($totalExtrasCount > 0) {
            if ($typeIdStr === '1' || $typeIdStr === '12') {
                $paidExtras = max(0, $totalExtrasCount - 1);
            } elseif ($typeIdStr === '17') {
                $paidExtras = $totalExtrasCount;
            } else {
                $paidExtras = $totalExtrasCount;
            }
        }

        $extrasTotalCost = $paidExtras * $costoTaller;
        
        // Asignar a la variable (y columna de BD) total_amount
        $this->total_amount = $baseCost + $extrasTotalCost; 
    }
	
	public function beforeSave($insert)
	{
		if(parent::beforeSave($insert))
		{
			// --- NUEVO CODIGO: Rellenar datos ocultos ---
            // Como ocultamos el input, lo calculamos aquí:
            $this->display_name = $this->first_name . ' ' . $this->last_name;
            
            // Si tu base de datos NO permite nulos en estos campos, 
            // ponemos cadenas vacías para que no truene el sistema:
            if(empty($this->address)) $this->address = ''; 
            // if(empty($this->zip)) $this->zip = '';
            // if(empty($this->fax)) $this->fax = '';
            // if(empty($this->prefix)) $this->prefix = '';
            if(empty($this->emergency_name)) $this->emergency_name = '';
            if(empty($this->emergency_phone)) $this->emergency_phone = '';
            // ---------------------------------------------
			
			// if($this->paid_by_credit_card == true)
			// 	$this->payment = 'Credit Card';
			// PAYMENT_RECEIPT
			if( !empty($this->file_payment_receipt) )
			{
				$fileNamePaymentReceipt = uniqid() . '.' . $this->file_payment_receipt->extension;
				$this->file_payment_receipt->saveAs('files/payment/' . $fileNamePaymentReceipt);
				$this->payment_receipt = $fileNamePaymentReceipt;
				$this->payment = 'Bank Transfer';
			}
			
			if(empty($this->payment))
				$this->payment = 'None';
			
			// STUDENT_ID
			if( !empty($this->file_student_id) )
			{
				$fileNameStudentId = uniqid() . '.' . $this->file_student_id->extension;
				$this->file_student_id->saveAs('files/studentid/' . $fileNameStudentId);
				$this->student_id = $fileNameStudentId;
			}
			
			if( empty($this->student_id) )
				$this->student_id = null;

			// CV
			if( !empty($this->file_cv) )
			{
				$fileNameCv = uniqid() . '.' . $this->file_cv->extension;
				$this->file_cv->saveAs('files/cv/' . $fileNameCv);
				$this->cv_file = $fileNameCv;
			}
			
			if( empty($this->token) )
			{
				$this->token = Yii::$app->getSecurity()->generateRandomString();
			}
			
			if($this->isNewRecord)
				$this->creation_date = date('Y-m-d H:i:s');
			else
				$this->modification_date = date('Y-m-d H:i:s');
			
			return true;
		}
		return false;
	}
	
	public function getFullName()
	{
		return $this->first_name . " " . $this->last_name;
	}
	
	public function getFolio()
	{
		return str_pad($this->id, 4, '0', STR_PAD_LEFT);
	}
	
	public function getLeftToken()
	{
		return strtoupper( str_replace('_', '0', str_replace('-', '9', substr($this->token,0, 10))) );
	}
	
	public function getRightToken()
	{
		return strtoupper( str_replace('_', '0', str_replace('-', '9', substr($this->token,-8))) );
	}
	
	public function create_s_transm()
	{
		$s_transm  = '00'; // Campus (2)
		$s_transm .= '04'; // Dependencia (2)
		$s_transm .= '00'; // Nivel (2)
		$s_transm .= $this->folio; // Folio (4)
		$s_transm .= $this->leftToken; // Token (10)
		return $s_transm;
	}
	
	public function create_c_referencia()
	{
		$c_referencia  = $this->folio; // Folio (4)
		$c_referencia .= $this->rightToken; // Folio (8)
		$c_referencia .= '01'; // Día (2)
		$c_referencia .= '06'; // Mes (2)
		$c_referencia .= '2016'; // Año (4)
		return $c_referencia;
	}
	
	public static function extract_s_transm($s_transm)
	{
		$params['campus'] = substr($s_transm, 0, 2);
		$params['dependencia'] = substr($s_transm, 2, 2);
		$params['nivel'] = substr($s_transm, 4, 2);
		$params['folio'] = substr($s_transm, 6, 4);
		$params['leftToken'] = substr($s_transm, 10, 10);
		return $params;
	}
	
	public static function extract_c_referencia($c_referencia)
	{
		$params['folio'] = substr($c_referencia, 0, 4);
		$params['rightToken'] = substr($c_referencia, 4, 8);
		$params['dia'] = substr($c_referencia, 12, 2);
		$params['mes'] = substr($c_referencia, 14, 2);
		$params['ano'] = substr($c_referencia, 16, 4);
		return $params;
	}
	
	public function validateLeftToken($leftToken)
	{
		return ( $leftToken == $this->leftToken )? true : false ;
	}
	
	public function validateRightToken($rightToken)
	{
		return ( $rightToken == $this->rightToken )? true : false ;
	}
	
	public function validateLeftRightToken($leftToken, $rightToken)
	{
		return $this->validateLeftToken($leftToken) && $this->validateRightToken($rightToken);
	}

	public function validateRegistrationCode($attribute, $params)
	{
		// var_dump($attribute); var_dump($this->$attribute); die();
		$registrationCode = RegistrationCode::find()->where(['code'=>$this->$attribute])->one();
		if( !empty($registrationCode) && empty($registrationCode->registration_id) )
			return;
		else
			$this->addError($attribute, 'Invalid registration code.');
	}

// **************Función para el concepto de pago*************
	/*public function getConceptoPago()
    {
        // Revisamos si las variables tienen texto, si no, usamos un string vacío
        $apellido = $this->last_name ? (string)$this->last_name : '';
        $nombre = $this->first_name ? (string)$this->first_name : '';

        // 1 y 2. Nombres y apellidos seguros
        $apellidoStr = str_pad(substr(strtoupper(trim($apellido)), 0, 3), 3, '0', STR_PAD_LEFT);
        $nombreStr = str_pad(substr(strtoupper(trim($nombre)), 0, 3), 3, '0', STR_PAD_LEFT);
        
        // 3. Tipo de registro
        $tipoRegistro = 'RU'; // Por defecto (UADY)
        if ($this->registration_type_id == 1) { 
            $tipoRegistro = 'RG';
        } elseif ($this->registration_type_id == 12) {
            $tipoRegistro = 'RE';
        }
        
        return $apellidoStr . $nombreStr . $tipoRegistro;
    }*/

	// **************Función para el concepto de pago*************
    public function getConceptoPago()
    {
        // Apellidos y Nombres
        $apellido = $this->last_name ? (string)$this->last_name : '';
        $nombre = $this->first_name ? (string)$this->first_name : '';

        $apellidoStr = str_pad(substr(strtoupper(trim($apellido)), 0, 3), 3, '0', STR_PAD_LEFT);
        $nombreStr = str_pad(substr(strtoupper(trim($nombre)), 0, 3), 3, '0', STR_PAD_LEFT);
        
        // Tipo de registro
        $tipoRegistro = 'RU'; // Por defecto (UADY)
        if ($this->registration_type_id == 1) { 
            $tipoRegistro = 'RG';
        } elseif ($this->registration_type_id == 12) {
            $tipoRegistro = 'RE';
        }
        
        $concepto = $apellidoStr . $nombreStr . $tipoRegistro;

        // Obtener e integrar Talleres seleccionados
        $talleres = $this->talleres_seleccionados;
        if (empty($talleres) && !$this->isNewRecord) {
            $talleresBD = \app\models\RegistroTaller::find()->where(['registration_id' => $this->id])->all();
            $talleres = array_map(function($t) { return $t->taller_id; }, $talleresBD);
        }

        if (!empty($talleres) && is_array($talleres)) {
            sort($talleres); 
            foreach ($talleres as $tallerId) {
                $concepto .= 'T' . str_pad($tallerId, 2, '0', STR_PAD_LEFT);
            }
        }

        // Obtener e integrar Visitas seleccionadas
        $visitas = $this->visitas_seleccionadas;
        if (empty($visitas) && !$this->isNewRecord) {
            $visitasBD = \app\models\RegistroVisita::find()->where(['registration_id' => $this->id])->all();
            $visitas = array_map(function($v) { return $v->visita_id; }, $visitasBD);
        }

        if (!empty($visitas) && is_array($visitas)) {
            sort($visitas); 
            foreach ($visitas as $visitaId) {
                $concepto .= 'V' . str_pad($visitaId, 2, '0', STR_PAD_LEFT);
            }
        }
        
        return $concepto;
    }

}