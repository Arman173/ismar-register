<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\bootstrap\ActiveForm;
use app\models\RegistrationType;
use app\models\AdditionalTickets;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;

/* @var $this yii\web\View */
/* @var $registration app\models\Registration */
/* @var $invoice app\models\Invoice */
/* @var $form yii\widgets\ActiveForm */
?>


<?php $this->registerJs('
	
	function showFileStudentId()
	{
		$("[name=\'Registration[file_student_id]\']").removeAttr("disabled");
		$(".field-registration-file_student_id").show();
	}
	
	function hideFileStudentId()
	{
		$("[name=\'Registration[file_student_id]\']").attr("disabled","disabled");
		$(".field-registration-file_student_id").hide();
	}
	
	function showFilePaymentReceipt()
	{
		$("[name=\'Registration[file_payment_receipt]\']").removeAttr("disabled");
		$(".field-registration-file_payment_receipt").show();
	}
	
	function hideFilePaymentReceipt()
	{
		$("[name=\'Registration[file_payment_receipt]\']").attr("disabled","disabled");
		$(".field-registration-file_payment_receipt").hide();
	}
	
	function toggleStudentId()
	{
		var registrationType = $("[name=\'Registration[registration_type_id]\']:checked").val();
		switch( registrationType )
		{
			case "1":
			case "2": 
			case "6":
			case "7":
			case "10":
			case "11": hideFileStudentId(); break;
			case "3": 
			case "4": 
			case "5": 
			case "8": 
			case "9": 
			case "12": 
			case "13": showFileStudentId(); break;
		}
	}
	
	function toggleInvoice()
	{
		if( $("[name=\'Registration[invoice_required]\']:checked").val() == "0" )
		{
			$("[name=\'Invoice[business_name]\']").attr("disabled","disabled");
			$(".field-invoice-business_name").hide();
			$("[name=\'Invoice[rfc]\']").attr("disabled","disabled");
			$(".field-invoice-rfc").hide();
			$("[name=\'Invoice[address]\']").attr("disabled","disabled");
			$(".field-invoice-address").hide();
			$("[name=\'Invoice[zip_code]\']").attr("disabled","disabled");
			$(".field-invoice-zip_code").hide();
			$("[name=\'Invoice[city]\']").attr("disabled","disabled");
			$(".field-invoice-city").hide();
			$("[name=\'Invoice[state]\']").attr("disabled","disabled");
			$(".field-invoice-state").hide();
			$("[name=\'Invoice[email]\']").attr("disabled","disabled");
			$(".field-invoice-email").hide();
		}
		else
		{
			$("[name=\'Invoice[business_name]\']").removeAttr("disabled");
			$(".field-invoice-business_name").show();
			$("[name=\'Invoice[rfc]\']").removeAttr("disabled");
			$(".field-invoice-rfc").show();
			$("[name=\'Invoice[address]\']").removeAttr("disabled");
			$(".field-invoice-address").show();
			$("[name=\'Invoice[zip_code]\']").removeAttr("disabled");
			$(".field-invoice-zip_code").show();
			$("[name=\'Invoice[city]\']").removeAttr("disabled");
			$(".field-invoice-city").show();
			$("[name=\'Invoice[state]\']").removeAttr("disabled");
			$(".field-invoice-state").show();
			$("[name=\'Invoice[email]\']").removeAttr("disabled");
			$(".field-invoice-email").show();
		}
	}
	$("[name=\'Registration[registration_type_id]\']").change(function(){
		toggleStudentId();
	});
	
	$("[name=\'Registration[invoice_required]\']").change(function (){
		toggleInvoice();
	});
	
	toggleStudentId();
	toggleInvoice();


	var $grid = $(\'#fee_type\'); // your grid identifier

	$("input[name=kvradio][value=\'1\']").prop("checked",true);

	$grid.on(\'grid.radiochecked\', function(ev, key, val) {
		$("#registration-registration_type_id").val(val);

	});

'); ?>


<div class="registration-form">

    <?php $form = ActiveForm::begin([
		'layout' => 'horizontal',
		'options' => ['enctype' => 'multipart/form-data'],
	]); ?>
	

    <h3><?= Html::encode('Personal Information') ?></h3>


	<?= $form->field($registration, 'prefix')->inline(true)->radioList(
		[
			'Ms.' => 'Ms.',
			'Mr.' => 'Mr.',
			'Dr.' => 'Dr.',
			'Prof.' => 'Prof.',			
		]
	) ?>    


	

    <?= $form->field($registration, 'first_name')->textInput([
		'maxlength' => true,
		'onchange' => "$('#registration-display_name').val(
			$('#registration-first_name').val() + ' ' + 
			$('#registration-last_name').val()
		)",
	]) ?>

    <?= $form->field($registration, 'last_name')->textInput([
		'maxlength' => true,
		'onchange' => "$('#registration-display_name').val(
			$('#registration-first_name').val() + ' ' + 
			$('#registration-last_name').val()
		)",
	]) ?>

    <?= $form->field($registration, 'display_name')->textInput([
		'maxlength' => true,
		'placeholder' => 'As displayed in badge',
	]) ?>

	<?= $form->field($registration, 'organization_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($registration, 'address')->textInput(['maxlength' => true]) ?>

    <?= $form->field($registration, 'city')->textInput(['maxlength' => true]) ?>

    <?= $form->field($registration, 'state')->textInput(['maxlength' => true]) ?>

    <?= $form->field($registration, 'zip')->textInput(['maxlength' => true]) ?>

    <?= $form->field($registration, 'country')->textInput(['maxlength' => true]) ?>

    <?= $form->field($registration, 'business_phone')->textInput([
		'maxlength' => true,
		'placeholder' => 'Please enter your phone number with code area (e.g. 001-555-555-5555)',
	]) ?>

    <?= $form->field($registration, 'fax')->textInput(['maxlength' => true]) ?>

    <?= $form->field($registration, 'email')->textInput(['maxlength' => true]) ?>

	<?= $form->field($registration, 'diet')->inline(true)->radioList(
		[
			'None' => 'None',
			'Vegetarian' => 'Vegetarian',
		]
	) ?>    
	
    <?= $form->field($registration, 'emergency_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($registration, 'emergency_phone')->textInput(['maxlength' => true]) ?>
	
    
    <h3><?= Html::encode('Registration Information') ?></h3>

    <?php $dataProvider = new ActiveDataProvider([
		'query' => RegistrationType::find(),
	]); ?>

	<?= $form->field($registration, 'registration_type_id')->hiddenInput(

	) ?>

	<?= GridView::widget([
		'id' => 'fee_type',
		'dataProvider' => $dataProvider,
		'columns' => [
			['class' => 'kartik\grid\RadioColumn'

			],
			'name',
			'nameCostEarlyBird',
			'nameCostRegistration',
			'nameCostOnSite',
		]
	]);?>


	<p> <?= Html::encode('* Student Registration and Life Member Registration require a proof of status or, for students, a student ID confirming that the registered person is a full-time student at the time of the conference.')?> </p>
	<?php if(!$registration->isNewRecord): ?>

	<?php $this->registerJs('

		function showChangeFileStudentId()
		{
			$("[name=\'Registration[change_file_student_id][]\']").removeAttr("disabled");
			$(".field-registration-change_file_student_id").show();
		}
		
		function hideChangeFileStudentId()
		{
			$("[name=\'Registration[change_file_student_id][]\']").attr("disabled","disabled");
			$(".field-registration-change_file_student_id").hide();
		}
		
		function toggleChangeFileStudentId()
		{
			var registrationType = $("[name=\'Registration[registration_type_id]\']:checked").val();
			switch( registrationType )
			{
				case "2": 
				case "4": 
				case "5": showChangeFileStudentId(); break;
				case "1":
				case "3": hideChangeFileStudentId(); break;
			}
		}
		
		hideFileStudentId();
		hideFilePaymentReceipt();
		toggleChangeFileStudentId();
		
		$("[name=\'Registration[change_file_student_id][]\']").change(function (){
			if( $(this).is(":checked") )
				showFileStudentId();
			else
				hideFileStudentId();
		});
		
		$("[name=\'Registration[change_file_payment_receipt][]\']").change(function (){
			if( $(this).is(":checked") )
				showFilePaymentReceipt();
			else
				hideFilePaymentReceipt();
		});
		
	'); ?>
	
	<?= $form->field($registration, 'change_file_student_id')->checkboxList(
		[
			1 => Html::a(
				$registration->student_id,
				'@web/files/studentid/'.$registration->student_id,
				['target'=>'_blank']
			),
		]
	) ?>
	<?php endif; ?>

	<?= $form->field($registration, 'file_student_id')->fileInput() ?>
	
	<?php if(!$registration->isNewRecord): ?>
	<?php echo $form->field($registration, 'change_file_payment_receipt')->checkboxList(
		[
			1 => Html::a(
				$registration->payment_receipt,
				'@web/files/payment/'.$registration->payment_receipt,
				['target'=>'_blank']
			),
		]
	); ?>
	<?php endif; ?>
	
	<?php if(!$registration->isNewRecord): ?>
	<?php echo $form->field($registration, 'file_payment_receipt')->fileInput() ?>
	<?php endif; ?>


	<h3>Information for Authors</h3>
	<p> <?= Html::encode('Authors are required to register. At least one non-refundable registration must be attached to each accepted paper.')?> </p>
	<p> <?= Html::encode('For each contribution, please list:')?>
    <br/>
	<?= Html::encode('1) The type of contribution (Paper, poster, demo, workshop paper, tutorial, etc.).')?>
    <br/>
	<?= Html::encode('2) The title of contribution.')?> 
    <br/>
    <?= Html::encode('Contribution 1:')?>
    </p>
    <table>
    <tr>
    <?= $form->field($registration, 'type1')->textInput(['maxlength' => true]) ?> 
	</tr>
    <tr>
    <?= $form->field($registration, 'title1')->textInput(['maxlength' => true]) ?>
    </tr>
	</table>

    <h3><?= Html::encode('Workshop and Tutorials') ?></h3>
    
    <h3><?= Html::encode('Additional Tickets') ?></h3>

	<?php $dataProvider2 = new ActiveDataProvider([
		'query' => AdditionalTickets::find(),
	]); ?>

	<?= GridView::widget([
		'id' => 'fee_type',
		'dataProvider' => $dataProvider2,
		'columns' => [
			['class' => 'kartik\grid\CheckboxColumn'],
			'name',
			'namePrice'
		]
	]);?>
    
    <h3><?= Html::encode('Social Events') ?></h3>
	<p> <?= Html::encode('To aid in conference planning, please let us know which events you will be attending.')?>
    
	<h3><?= Html::encode('For Mexicans Only')?></h3>

	<?= $form->field($registration, 'invoice_required')->radioList(
		[
			0 => 'Not required',
			1 => 'Required',
		]
	) ?>

	
    <?= $form->field($invoice, 'business_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($invoice, 'rfc')->textInput(['maxlength' => true]) ?>

    <?= $form->field($invoice, 'address')->textInput(['maxlength' => true]) ?>

    <?= $form->field($invoice, 'zip_code')->textInput(['maxlength' => true]) ?>

    <?= $form->field($invoice, 'city')->textInput(['maxlength' => true]) ?>

    <?= $form->field($invoice, 'state')->textInput(['maxlength' => true]) ?>

    <?= $form->field($invoice, 'email')->textInput(['maxlength' => true]) ?>
    

	<h3>Cancellation Policy</h3>
	<p> <?= Html::encode('The registration fee will not be refunded to the authors if it is required to cover the publications expenses of accepted papers. Any cancellations after registration will incur $100 USD administrative charges. No refunds will be made for cancellations after July 20, 2016. No refunds will be given for non-attendance. To requests for cancellations, substitutions, or other changes, please contact the Registration Chair at registration@ismar2016.org.')?> </p>

    <div class="form-group">
        <?= Html::submitButton($registration->isNewRecord ? Yii::t('app', 'Submit') : Yii::t('app', 'Update data'), ['class' => $registration->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
