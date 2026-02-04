<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\bootstrap\ActiveForm;
use app\models\Registration; // I did this
use app\models\RegistrationType;
use app\models\AdditionalTickets;
use app\models\Workshops;
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

	// New code. Rodrigo
	function toggleFilePaymentReceipt()
	{
		var paymentType = $("[name=\'Registration[payment_type]\']:checked").val();
		if( paymentType == "2" )
			showFilePaymentReceipt();
		else
			hideFilePaymentReceipt();
		
	}
		
	function toggleStudentId()
	{
		//alert("Hola");
		//var registrationType = $("[name=\'Registration[registration_type_id]\']:checked").val();
		var registrationType2 = $("[name=\'Registration[registration_type_id]\']").val();
		//alert(registrationType2);
		switch( registrationType2 )
		{
			case "1":
			case "2": 
			case "5":
			case "6":
			case "10":
			case "11":
			case "14":
			case "15": hideFileStudentId(); break;
			case "3": 
			case "4": 
			case "7": 
			case "9": 
			case "12": 
			case "13": 
			case "16": 
			case "17": showFileStudentId(); break;
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
	} // end of toogleInvoice()
	
	
	$("[name=\'Registration[registration_type_id]\']").change(function(){
		toggleStudentId();
	});
	
	
	$("[name=\'Registration[invoice_required]\']").change(function (){
		toggleInvoice();
	});
	
	$("[name=\'Registration[payment_type]\']").change(function(){
		toggleFilePaymentReceipt();
	});
	
		
	toggleStudentId();
	toggleFilePaymentReceipt();
	toggleInvoice();


	var $grid = $(\'#fee_type\'); // your registration grid identifier

	$("input[name=kvradio][value=\'1\']").prop("checked",true);

	$grid.on( \'grid.radiochecked\', function(ev, key, val){
		$("#registration-registration_type_id").val(val);
			switch( val )
			{
				case "1":
				case "2": 
				case "5":
				case "6":
				case "10":
				case "11":
				case "14":
				case "15": hideFileStudentId(); break;
				case "3": 
				case "4": 
				case "7": 
				case "9": 
				case "12": 
				case "13": 
				case "16": 
				case "17": showFileStudentId(); break;
			}
		}
	);

	//$("#workshop_type input[type=checkbox]").click(function(){
	
	var $workgrid = $(\'#workshop_type\');
	$workgrid.on(\'click\',function(){
		$("[name=\'Registration[W1]\']").val(0);
		$("[name=\'Registration[W2]\']").val(0);
		$("[name=\'Registration[W3]\']").val(0);
		$("[name=\'Registration[W4]\']").val(0);
		$("[name=\'Registration[W5]\']").val(0);
		$("[name=\'Registration[W6]\']").val(0);
		$("[name=\'Registration[W7]\']").val(0);
		$("[name=\'Registration[T1]\']").val(0);
		
		var keys = $workgrid.yiiGridView(\'getSelectedRows\');
		//if (typeof keys[0] !== \'undefined\') {
		for (i = 0; i < keys.length; i++) { 	
			switch(keys[i]){
				case 1: $("[name=\'Registration[W1]\']").val(1); break;
				case 2: $("[name=\'Registration[W2]\']").val(1); break;
				case 3: $("[name=\'Registration[W3]\']").val(1); break;
				case 4: $("[name=\'Registration[W4]\']").val(1); break;
				case 5: $("[name=\'Registration[W5]\']").val(1); break;
				case 6: $("[name=\'Registration[W6]\']").val(1); break;
				case 7: $("[name=\'Registration[W7]\']").val(1); break;
				case 8: $("[name=\'Registration[T1]\']").val(1); break;
			}
		}
	});
	
'); ?>


<div class="registration-form">

    <?php $form = ActiveForm::begin([
		'layout' => 'horizontal',
		'options' => ['enctype' => 'multipart/form-data'],
	]); ?>
	

    <h3><?= Html::encode('Información Personal') ?></h3>


	<!-- PREFIX FIELD -->
	<!-- <?= $form->field($registration, 'prefix')->inline(true)->radioList(
		[
			'Ms.' => 'Ms.',
			'Mr.' => 'Mr.',
			'Dr.' => 'Dr.',
			'Prof.' => 'Prof.',			
		]
	) ?> -->


	

    <?= $form->field($registration, 'first_name')->textInput([
		'maxlength' => true,
		// 'onchange' => "$('#registration-display_name').val(
		// 	$('#registration-first_name').val() + ' ' + 
		// 	$('#registration-last_name').val()
		// )",
	]) ?>

    <?= $form->field($registration, 'last_name')->textInput([
		'maxlength' => true,
		// 'onchange' => "$('#registration-display_name').val(
		// 	$('#registration-first_name').val() + ' ' + 
		// 	$('#registration-last_name').val()
		// )",
	]) ?>

    <!-- DISPLAY NAME -->
    <!-- <?= $form->field($registration, 'display_name')->textInput([
		'maxlength' => true,
		'placeholder' => 'As displayed in badge',
	]) ?> -->

	<?= $form->field($registration, 'organization_name')->textInput(['maxlength' => true]) ?>

	<!-- ADDRESS -->
    <!-- <?= $form->field($registration, 'address')->textInput(['maxlength' => true]) ?> -->

    <?= $form->field($registration, 'city')->textInput(['maxlength' => true]) ?>

    <?= $form->field($registration, 'state')->textInput(['maxlength' => true]) ?>

	<!-- POSTAL CODE / ZIP -->
    <!-- <?= $form->field($registration, 'zip')->textInput(['maxlength' => true]) ?> -->

    <?= $form->field($registration, 'country')->textInput(['maxlength' => true]) ?>

    <?= $form->field($registration, 'business_phone')->textInput([
		'maxlength' => true,
		'placeholder' => 'Please enter your phone number with code area (e.g. 001-555-555-5555)',
	]) ?>

    <!-- FAX -->
    <!-- <?= $form->field($registration, 'fax')->textInput(['maxlength' => true]) ?> -->

    <?= $form->field($registration, 'email')->textInput(['maxlength' => true]) ?>

	<!-- NOTA: comentando diet, NO DEBERIA fallar -->
	<!-- <?= $form->field($registration, 'diet')->inline(true)->radioList(
		[
			'None' => 'None',
			'Vegetarian' => 'Vegetarian',
		]
	) ?> -->
	
	<!-- EMERGENCY NAME AND PHONE -->
    <!-- <?= $form->field($registration, 'emergency_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($registration, 'emergency_phone')->textInput(['maxlength' => true]) ?> -->
	
    
    
    <?= $form->field($registration, 'registration_type_id')->hiddenInput()->label(false) ?>
    
    <h3><?= Html::encode('Información de Registro') ?></h3>
    
    <h4><?= Html::encode('Tipos de Registro') ?></h4>
    
	<p style="margin-left:0.5cm">
		<!-- <b> <?= Html::encode('Full participation (5 days, 19-23 Sept.):')?> </b> <?= Html::encode('All Conference Sessions Access, Workshops and Tutorials, USB Proceedings, Conference and Workshops Receptions, Lunch, Banquet, Internet.')?>
        <br> -->
        <!-- <b> <?= Html::encode('General:')?> </b> <?= Html::encode('All Conference Sessions Access, USB Proceedings, Conference Reception, Lunch, Banquet, Internet.')?>  -->
		 <b> <?= Html::encode('General:')?> </b> <?= Html::encode('Acceso a todas las conferencias, memorias de congreso, constancia digital de participación, talleres y visitas industriales.')?> 
        <br>
        <!-- <b> <?= Html::encode('Workshops and Tutorials Only (2 days, 22-23 Sept.):')?> </b> <?= Html::encode('Workshops and Tutorials Sessions Access, USB Proceedings, Lunch, Internet.')?>  -->
		 <b> <?= Html::encode('Estudiante:')?> </b> <?= Html::encode('Acceso a todas las conferencias, memorias de congreso y constancia digital de participación. Incluye un taller o una visita industrial.')?>

        <!-- <br> <b> <?= Html::encode('Single Day:')?> </b> <?= Html::encode('Sessions Access for one day only, USB Proceedings, Lunch, Internet. If applicable, a ticket to the social event (Reception/Banquet) of the day must be purchased separately.')?>  -->
		<br> <b> <?= Html::encode('Estudiante y Profesor UADY:')?> </b> <?= Html::encode('Acceso a todas las conferencias. No incluye talleres ni visitas industriales.')?>
    </p>

    <?php $dataProviderReg = new ActiveDataProvider([
		'query' => RegistrationType::find(),
	]); ?>
    
	<?= GridView::widget([
		'id' => 'fee_type',
		'dataProvider' => $dataProviderReg,
		'columns' => [
			[
				'class' => 'kartik\grid\RadioColumn',
			 	'showClear' => false,
			],
			
			[
				'class' => '\kartik\grid\DataColumn',
				'attribute' => 'name',
				'label' => 'Tipo de Registro'
			],
			
			[
				'class' => '\kartik\grid\DataColumn',
				'attribute' => 'cost_early_bird',
				'header' => 'Cuota <br> Pre-Registro',
			],			
			
			//'advanceRegistration',			
			
			[
				'class' => '\kartik\grid\DataColumn',
				'attribute' => 'cost_late',
				'header' => 'Cuota <br> Registro',
			],			
			//'lateRegistration',
			/*[
				'class' => '\kartik\grid\DataColumn',
				'attribute' => 'cost',
				'header' => 'On site <br> cost',
			],*/			
			//'costOnSite',
		],
		'summary'=>'',
		'options' => ['style' => 'width:700px;'],
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
		
		// I made this comment to avoid duplicate Student File dialog. Anabel
		/*
		$("[name=\'Registration[change_file_student_id][]\']").change(function (){
			if( $(this).is(":checked") )
				showFileStudentId();
			else
				hideFileStudentId();
		});	
		*/
		
		$("[name=\'Registration[change_file_payment_receipt][]\']").change(function (){
			if( $(this).is(":checked") )
				showFilePaymentReceipt();
			else
				hideFilePaymentReceipt();
		});
	
	
	'); ?>
    
	    
	<?php endif; ?>

	<?= $form->field($registration, 'file_student_id')->fileInput() ?>
	
	<?php $dataProvider = new ActiveDataProvider([
		'query' => Registration::find(),
	]); ?>
    

	<h3><?= Html::encode('Información para Autores') ?></h3>
    <p> <?= Html::encode('Se requiere que los autores se registren. Al menos un registro no reembolsable debe estar asociado a cada artículo aceptado.')?> </p>
    <p> <?= Html::encode('Por cada contribución, por favor indique:')?>
    <br/>
    <?= Html::encode('1) El tipo de contribución (artículo, póster, demostración).')?>
    <br/>
    <?= Html::encode('2) El título de la contribución.')?> 
    <br/>
    
    </p>
    <table>
		<tr>
			<td>
				Contribución 1:
			</td>
			<td>
				<?= $form->field($registration, 'type1')->textInput(['maxlength' => true])->label('Tipo') ?>
			</td>
			<td>
				<?= $form->field($registration, 'title1')->textInput(['maxlength' => true])->label('Título') ?>
			</td>
		</tr>
		<tr>
			<td>
				Contribución 2:
			</td>
			<td>
				<?= $form->field($registration, 'type1')->textInput(['maxlength' => true])->label('Tipo') ?>
			</td>
			<td>
				<?= $form->field($registration, 'title1')->textInput(['maxlength' => true])->label('Título') ?>
			</td>
		</tr>
	</table>

	<!------ ADITIONAL TICKETS BEGIN ------>
	<!-- <h3><?= Html::encode('Additional Tickets') ?></h3>

	<?php $dataProviderTickets = new ActiveDataProvider([
		'query' => AdditionalTickets::find(),
	]); ?>
    
	<?= GridView::widget([
		'id' => 'tickets_type',
		'dataProvider' => $dataProviderTickets,
		'columns' => [
			[
				'class' => 'yii\grid\DataColumn',
			    'value' => function ($model, $key, $index, $widget){
					//return Html::textInput('', $model->quantity);
					return Html::activeDropDownList($model, 'quantity', range(0,5));
				},
				'format' => 'raw',
			],
			'name',
			'cost'
			//'price'
		],
		'summary'=>'',
		'options' => ['style' => 'width:700px;'],
	]);?>
    
   	<?= $form->field($registration, 'banquet_ticket')->hiddenInput()->label(false) ?> -->
	<!------ ADITIONAL TICKETS END ------>

    <h3><?= Html::encode('Workshops and Tutorials') ?></h3>
	
	<?php $dataProviderWork = new ActiveDataProvider([
		'query' => Workshops::find(),
	]); ?>

	<?= GridView::widget([
		'id' => 'workshop_type',
		'dataProvider' => $dataProviderWork,
		'columns' => [
			[
				'class' => 'kartik\grid\CheckboxColumn',
				//'rowHighlight' => true,
				'header' => '',
			],
			//'id',
			'name',
			'description',
		],
		'summary'=>'',
		'options' => ['style' => 'width:700px;'],
	]);?>

	<?= $form->field($registration, 'proceedings_copies')->hiddenInput()->label(false) ?>


	<h3><?= Html::encode('Solo para Mexicanos (Documento oficial deducible de impuestos)')?></h3>

	<?= $form->field($registration, 'invoice_required')->radioList(
		[
			0 => 'No requerida',
			1 => 'Requerida',
		]
	)->label('¿Requiere Factura? (Solo México)') ?>

	
    <?= $form->field($invoice, 'business_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($invoice, 'rfc')->textInput(['maxlength' => true]) ?>

    <?= $form->field($invoice, 'address')->textInput(['maxlength' => true]) ?>

    <?= $form->field($invoice, 'zip_code')->textInput(['maxlength' => true]) ?>

    <?= $form->field($invoice, 'city')->textInput(['maxlength' => true]) ?>

    <?= $form->field($invoice, 'state')->textInput(['maxlength' => true]) ?>

    <?= $form->field($invoice, 'email')->textInput(['maxlength' => true]) ?>
        
    
	<h3>Cancellation Policy</h3>
	<p> <?= Html::encode('The registration fee will not be refunded to the authors if it is required to cover the publications expenses of accepted papers. Any cancellations after registration will incur $100 USD administrative charges. No refunds will be made for cancellations after August 1st, 2016. No refunds will be given for non-attendance. Note that "No Show" authors will have their paper removed from the Proceedings and would not get a refund. To requests for cancellations, substitutions, or other changes, please contact the Registration Chair at registration@ismar2016.org.')?> </p>

    
	<h3>Payment</h3>
    	<?= $form->field($registration, 'payment_type')->radioList([
		1 => 'Credit Card',
		2 => 'Bank Wire Transfer (Upload your bank transfer receipt)',
		// 3 => 'Code',
	]) ?>

	<?php echo $form->field($registration, 'file_payment_receipt')->fileInput() ?>
	
    
    <div class="form-group">
        <?= Html::submitButton($registration->isNewRecord ? Yii::t('app', 'Submit') : Yii::t('app', 'Update data'), ['class' => $registration->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

	<?= $form->field($registration, 'W1')->hiddenInput()->label(false) ?>
	<?= $form->field($registration, 'W2')->hiddenInput()->label(false) ?>
	<?= $form->field($registration, 'W3')->hiddenInput()->label(false) ?>
	<?= $form->field($registration, 'W4')->hiddenInput()->label(false) ?>
	<?= $form->field($registration, 'W5')->hiddenInput()->label(false) ?>
	<?= $form->field($registration, 'W6')->hiddenInput()->label(false) ?>
	<?= $form->field($registration, 'W7')->hiddenInput()->label(false) ?>
	<?= $form->field($registration, 'T1')->hiddenInput()->label(false) ?>

    <?php ActiveForm::end(); ?>

</div>
