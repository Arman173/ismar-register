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
 * @property string $prefix
 * @property string $first_name
 * @property string $last_name
 * @property string $display_name
 * @property string $business_phone
 * @property string $fax
 * @property string $email
 * @property string $email2
 * @property string $address
 * @property string $city
 * @property string $state
 * @property string $zip
 * @property string $country
 * @property string $student_id
 * @property string $payment_receipt
 * @property string $emergency_name
 * @property string $emergency_phone
 * @property string $diet
 * @property string $token
 * @property int $banquet_ticket
 * @property int $proceedings_copies
 * @property int $W1
 * @property int $W2
 * @property int $W3
 * @property int $W4
 * @property int $W5
 * @property int $W6
 * @property int $W7
 * @property int $T1
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
            [['registration_type_id', 'organization_name', 'first_name', 'last_name', 'email', 'invoice_required', 'city', 'zip','country',], 'required'],
            [['registration_type_id', 'W1', 'W2', 'W3', 'W4', 'W5', 'W6', 'W7', 'T1', 'banquet_ticket', 'proceedings_copies'], 'integer'],
			[['diet', 'type1'], 'string', 'max' => 20],
            [['organization_name', 'display_name', 'email', 'email2', 'address', 'emergency_name'], 'string', 'max' => 150],
            [['first_name', 'last_name', 'city', 'state', 'country','title1'], 'string', 'max' => 100],
            [['business_phone', 'fax', 'student_id', 'payment_receipt', 'emergency_phone','one_day_registration',], 'string', 'max' => 45],
			[['registration_type_id'], 'exist', 'targetClass' => 'app\models\RegistrationType', 'targetAttribute' => 'id'],
			// [['business_phone', 'fax', 'emergency_phone'], 'match', 'pattern' => '/^(?:1(?:[. -])?)?(?:\((?=\d{3}\)))?([2-9]\d{2})(?:(?<=\(\d{3})\))? ?(?:(?<=\d{3})[.-])?([2-9]\d{2})[. -]?(\d{4})(?: (?i:ext)\.? ?(\d{1,5}))?$/', 'message' => '{attribute} is invalid. Please enter your {attribute} with area code in a valid format (e.g. 001-555-5555555)'],
            [['zip', 'prefix'], 'string', 'max' => 10],
            [['email'], 'unique'],
			[['email'], 'email'],
			[['file_payment_receipt'], 'file', 'skipOnEmpty' => false, 'extensions' => 'pdf, png, jpg, jpeg, bmp, doc, docx', 'on' => 'UploadPaymentReceipt'],
			[['file_payment_receipt'], 'file', 'skipOnEmpty' => true, 'extensions' => 'pdf, png, jpg, jpeg, bmp, doc, docx', 'on' => 'Update'],
			
			
/*			[['file_student_id'], 'file', 'skipOnEmpty' => false, 'extensions' => 'pdf, png, jpg, jpeg, bmp, doc, docx', 'when' => function ($model){
				switch($model->registration_type_id)
				{
					case "1":
					case "2": 
					case "6":
					case "7":
					case "10":
					case "11": return true;
					case "3": 
					case "4": 
					case "5": 
					case "8": 
					case "9": 
					case "12": 
					case "13": return false;
				}
			}, 'whenClient' => 'function (attribute,value){
				switch( $("[name=\'Registration[registration_type_id]\']:checked").val() )
				{
					case "1":
					case "2": 
					case "6":
					case "7":
					case "10":
					case "11": return true;
					case "3": 
					case "4": 
					case "5": 
					case "8": 
					case "9": 
					case "12": 
					case "13": return false;
				}
			}', 'on' => 'Create'],
			[['file_student_id'], 'file', 'skipOnEmpty' => true, 'extensions' => 'pdf, png, jpg, jpeg, bmp, doc, docx', 'on' => 'Update'],*/
			
			
			[['file_student_id'], 'file', 'skipOnEmpty' => true, 'extensions' => 'pdf, png, jpg, jpeg, bmp, doc, docx'],
			[['invoice_required'], 'boolean'],
			[['change_file_payment_receipt', 'change_file_student_id'], 'each', 'rule'=>['in', 'range'=>[0,1]]],
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
			'prefix' => Yii::t('app', 'Prefix'),
            'first_name' => Yii::t('app', 'First Name'),
            'last_name' => Yii::t('app', 'Last name / Family name'),
            'display_name' => Yii::t('app', 'Display Name'),
//          'degree' => Yii::t('app', 'Degree'),
            'business_phone' => Yii::t('app', 'Phone (incl. country code)'),
            'fax' => Yii::t('app', 'Fax'),
            'email' => Yii::t('app', 'Email'),
            'address' => Yii::t('app', 'Address'),
            'city' => Yii::t('app', 'City'),
            'state' => Yii::t('app', 'Province / State'),
            'zip' => Yii::t('app', 'Postal Code / Zip'),
            'country' => Yii::t('app', 'Country'),
            'student_id' => Yii::t('app', 'Student Proof'),
			'payment_receipt' => Yii::t('app', 'Payment Receipt'),
			'file_student_id' => Yii::t('app', 'Status Proof (PDF)'),
			'change_file_student_id' => Yii::t('app', 'Status Proof (PDF)'),
			'file_payment_receipt' => Yii::t('app', 'Payment Receipt File'),
			'change_file_payment_receipt' => Yii::t('app', 'Payment Receipt'),
            'emergency_name' => Yii::t('app', 'Emergency Name'),
            'emergency_phone' => Yii::t('app', 'Emergency Phone'),
			'diet' => Yii::t('app', 'Dietary Restrictions'),
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
	
	public function beforeSave($insert)
	{
		if(parent::beforeSave($insert))
		{
			if($this->paid_by_credit_card == true)
				$this->payment = 'Credit Card';
			// PAYMENT_RECEIPT
			if( !empty($this->file_payment_receipt) )
			{
				$fileNamePaymentReceipt = uniqid() . '.' . $this->file_payment_receipt->extension;
				$this->file_payment_receipt->saveAs('files/payment/' . $fileNamePaymentReceipt);
				$this->payment_receipt = $fileNamePaymentReceipt;
				if($this->paid_by_credit_card == true)
					$this->payment = 'both';
				else
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
}