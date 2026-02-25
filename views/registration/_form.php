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

// --- CONFIGURACIÓN ---
$costo_taller = 100.00; // <--- PRECIO DEL TALLER
$fecha_cambio_precio = '2026-02-17'; // Fecha fin de Early Bird (YYYY-MM-DD)
$fecha_actual = date('Y-m-d');

// Determinamos si es Early Bird (Pre-registro) desde PHP para inicializar
$isEarlyBird = ($fecha_actual <= $fecha_cambio_precio);

// Preparamos los precios de los Tipos de Registro para Javascript
$registrationTypes = RegistrationType::find()->asArray()->all();
$pricesJson = [];
foreach ($registrationTypes as $type) {
    $pricesJson[$type['id']] = [
        'early' => (float)$type['cost_early_bird'],
        'late' => (float)$type['cost_late'],
        'name' => $type['name']
    ];
}
$pricesJsonString = json_encode($pricesJson);
// ------------------------------------------

?>

<?php $this->registerJs('

	// Variables PHP inyectadas
    var workshopCost = ' . $costo_taller . ';
    var isEarlyBird = ' . ($isEarlyBird ? 'true' : 'false') . ';
    var typePrices = ' . $pricesJsonString . ';
    
    function calculateTotal() {
        // INICIALIZAMOS EN 0 PARA EVITAR "NaN" O "UNDEFINED"
        var total = 0;
        var baseCost = 0;
        var workshopTotal = 0;
        
        // 1. Obtener ID seleccionado
        var selectedTypeId = $(\'#registration-registration_type_id\').val();
        
        // VALIDACIÓN DE SEGURIDAD:
        // Solo intentamos buscar el precio si hay un ID y ese ID existe en nuestro JSON
        if (selectedTypeId && typePrices[selectedTypeId]) {
            if (isEarlyBird) {
                baseCost = parseFloat(typePrices[selectedTypeId].early) || 0;
            } else {
                baseCost = parseFloat(typePrices[selectedTypeId].late) || 0;
            }
        } else {
            // Si no hay selección, el costo base se queda en 0
            baseCost = 0;
        }

        // 2. Contar Talleres seleccionados del Grid
        var selectedWorkshopsCount = $(\'#workshop_type\').yiiGridView(\'getSelectedRows\').length;
        
        // 3. Lógica de Cobro de Talleres
        var paidWorkshops = 0;
        var typeStr = String(selectedTypeId); // Convertir a string para comparar
        
        if (selectedWorkshopsCount > 0) {
            if (typeStr === \'1\' || typeStr === \'12\') {
                // General (1) y Estudiante (12): 1 Gratis
                paidWorkshops = Math.max(0, selectedWorkshopsCount - 1);
            } else if (typeStr === \'17\') {
                // UADY (17): Paga todos
                paidWorkshops = selectedWorkshopsCount;
            } else {
                // Default: Paga todos
                paidWorkshops = selectedWorkshopsCount; 
            }
        }
        
        workshopTotal = paidWorkshops * workshopCost;
        total = baseCost + workshopTotal;

        // 4. Actualizar vista (Usamos toFixed solo porque ya aseguramos que son números)
        $(\'#display-base-cost\').text(\'$\' + baseCost.toFixed(2));
        $(\'#display-workshop-count\').text(selectedWorkshopsCount);
        $(\'#display-workshop-paid\').text(paidWorkshops);
        $(\'#display-workshop-total\').text(\'$\' + workshopTotal.toFixed(2));
        $(\'#display-grand-total\').text(\'$\' + total.toFixed(2));
    }

    // -- LISTENERS --

    // Cambio en Tipo de Registro (Grid Radio de Kartik)
    $(\'#fee_type\').on(\'grid.radiochecked\', function(ev, key, val) {
        $(\'#registration-registration_type_id\').val(val);
        
        // Funciones visuales existentes (si existen)
        if(typeof toggleStudentId === \'function\') toggleStudentId();
        if(typeof toggleChangeFileStudentId === \'function\') toggleChangeFileStudentId();
        
        calculateTotal();
    });

    // Cambio en Checkbox de Talleres
    $(\'#workshop_type\').on(\'click\', function() {
        // Tu función de mapeo a inputs hidden (la integramos aquí para asegurar orden)
        mapWorkshopsToHiddenInputs(); 
        calculateTotal();
    });

    // Tu función de mapeo (tal cual estaba)
    function mapWorkshopsToHiddenInputs() {
        $(\'[name="Registration[W1]"]\').val(0);
        $(\'[name="Registration[W2]"]\').val(0);
        $(\'[name="Registration[W3]"]\').val(0);
        $(\'[name="Registration[W4]"]\').val(0);
        $(\'[name="Registration[W5]"]\').val(0);
        $(\'[name="Registration[W6]"]\').val(0);
        $(\'[name="Registration[W7]"]\').val(0);
        $(\'[name="Registration[T1]"]\').val(0);
        
        var keys = $(\'#workshop_type\').yiiGridView(\'getSelectedRows\');
        // Iteramos con cuidado
        if (keys) {
            for (var i = 0; i < keys.length; i++) { 
                var k = parseInt(keys[i]);
                if(k==1) $(\'[name="Registration[W1]"]\').val(1);
                if(k==2) $(\'[name="Registration[W2]"]\').val(1);
                if(k==3) $(\'[name="Registration[W3]"]\').val(1);
                if(k==4) $(\'[name="Registration[W4]"]\').val(1);
                if(k==5) $(\'[name="Registration[W5]"]\').val(1);
                if(k==6) $(\'[name="Registration[W6]"]\').val(1);
                if(k==7) $(\'[name="Registration[W7]"]\').val(1);
                if(k==8) $(\'[name="Registration[T1]"]\').val(1);
            }
        }
    }

    // Inicializar
    $(document).ready(function() {
        calculateTotal();
    });
	
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
			case "12": showFileStudentId(); break;
			case "17": showFileStudentId(); break;
			default: hideFileStudentId(); break;
			// case "1":
			// case "2": 
			// case "5":
			// case "6":
			// case "10":
			// case "11":
			// case "14":
			// case "15": hideFileStudentId(); break;
			// case "3": 
			// case "4": 
			// case "7": 
			// case "9": 
			// case "12": 
			// case "13": 
			// case "16": 
			// case "17": showFileStudentId(); break;
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
				case "12": showFileStudentId(); break;
				case "17": showFileStudentId(); break;
				default: hideFileStudentId(); break;
				// case "1":
				// case "2": 
				// case "5":
				// case "6":
				// case "10":
				// case "11":
				// case "14":
				// case "15": hideFileStudentId(); break;
				// case "3": 
				// case "4": 
				// case "7": 
				// case "9": 
				// case "12": 
				// case "13": 
				// case "16": 
				// case "17": showFileStudentId(); break;
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


	<?= $form->field($registration, 'organization_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($registration, 'city')->textInput(['maxlength' => true]) ?>

    <?= $form->field($registration, 'state')->textInput(['maxlength' => true]) ?>

    <?= $form->field($registration, 'country')->textInput(['maxlength' => true]) ?>

    <?= $form->field($registration, 'business_phone')->textInput([
		'maxlength' => true,
		'placeholder' => 'Por favor, ingrese su número de teléfono con lada (ej. 529995555555)',
	]) ?>

    <?= $form->field($registration, 'email')->textInput(['maxlength' => true]) ?>
	
    
    
    <?= $form->field($registration, 'registration_type_id')->hiddenInput()->label(false) ?>
    
    <h3><?= Html::encode('Información de Registro') ?></h3>
    
    <h4><?= Html::encode('Tipos de Registro') ?></h4>
    
	<p style="margin-left:0.5cm">
		<b> <?= Html::encode('General:')?> </b> <?= Html::encode('Acceso a todas las conferencias, memorias de congreso, constancia digital de participación, talleres y visitas industriales.')?> 
        <br>
		<b> <?= Html::encode('Estudiante:')?> </b> <?= Html::encode('Acceso a todas las conferencias, memorias de congreso y constancia digital de participación. Incluye un taller o una visita industrial.')?>

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


	<p> <?= Html::encode('* El registro de estudiante y de profesores de la UADY requiere una prueba de estatus o, para estudiantes, una credencial de estudiante que confirme que la persona registrada es estudiante de tiempo completo en el momento de la conferencia.')?> </p>
    
    
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
        <td>Contribución 1:</td>
        <td><?= $form->field($registration, 'type1')->textInput(['maxlength' => true])->label('Tipo') ?></td>
        <td><?= $form->field($registration, 'title1')->textInput(['maxlength' => true])->label('Título') ?></td>
    </tr>
    <tr>
        <td>Contribución 2:</td>
        <td><?= $form->field($registration, 'type2')->textInput(['maxlength' => true])->label('Tipo') ?></td>
        <td><?= $form->field($registration, 'title2')->textInput(['maxlength' => true])->label('Título') ?></td>
    </tr>
    </table>

    <div class="panel panel-default" style="margin-top: 20px; border: 1px solid #ddd;">
        <div class="panel-heading" style="background-color: #f5f5f5; font-weight: bold;">
            Selección de Revista (Número Especial)
        </div>
        <div class="panel-body">
            <p style="font-size: 0.9em; color: #555; margin-bottom: 15px;">
                En caso de que su trabajo fuera elegido para un número especial de revista, 
                favor de seleccionar la revista de su preferencia y que concuerde con el área de su investigación:
            </p>
            <?= $form->field($registration, 'revista_seleccionada')->dropDownList([
                'Ninguna' => 'Ninguna',
                'IEEE Latin America Transactions' => 'IEEE Latin America Transactions (Requiere un pago de $250 USD después de aceptación)',
                'Ingeniería Revista Académica' => 'Ingeniería Revista Académica (Sin costo extra)',
                'Abstraction & Application' => 'Abstraction & Application (Sin costo extra)',
            ])->label(false) ?>
        </div>
    </div>


	<!-- TALLERES Y VISITAS (WORKSHOP) -->
    <h3><?= Html::encode('Talleres y Visitas Industriales') ?></h3>
	
	<?php $dataProviderWork = new ActiveDataProvider([
		'query' => Workshops::find(),
	]); ?>

	<p style="margin-left:0.5cm">
		<b> <?= Html::encode('General:')?> </b> <?= Html::encode('Acceso a todas las conferencias, memorias de congreso, constancia digital de participación, talleres y visitas industriales.')?> 
        <br>
		<b> <?= Html::encode('Estudiante:')?> </b> <?= Html::encode('Acceso a todas las conferencias, memorias de congreso y constancia digital de participación. Incluye un taller o una visita industrial.')?>

		<br> <b> <?= Html::encode('Estudiante y Profesor UADY:')?> </b> <?= Html::encode('Acceso a todas las conferencias. No incluye talleres ni visitas industriales.')?>
    </p>

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
			[
				'attribute' => 'date',
				'header' => 'Fecha',
				'format' => ['date', 'php:d-m-Y'],
			],
			[
				'attribute' => 'hr_inicio',
				'header' => 'Hora Inicio',
				'format' => ['time', 'php:H:i'],
			],
			[
				'attribute' => 'hr_fin',
				'header' => 'Hora Fin',
				'format' => ['time', 'php:H:i'],
			],
			// [
			// 	'attribute' => 'time',
			// 	'header' => 'Duración (minutos)',
			// 	'value' => function($model) {
			// 		return $model->time . ' minutos';
			// 	}
			// ]
		],
		'summary'=>'',
		'options' => ['style' => 'width:700px;'],
	]);?>

	<?= $form->field($registration, 'proceedings_copies')->hiddenInput()->label(false) ?>
	
	<!-- TALLERES Y VISITAS (WORKSHOP) END -->


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
        
    
	<h3>Política de cancelación</h3>
	<p> <?= Html::encode('Las cuotas de inscripción, talleres y/o visitas industriales no serán rembolsables. Es importante destacar que a los autores que no se presenten se les retirará su artículo de las memorias del congreso. Para cualquier duda o aclaracion favor de contactar concei@correo.uady.mx')?> </p>

    
	<h3>Resumen de Pago</h3>

	<div class="panel panel-default" style="margin-top: 20px;">
        <!-- <div class="panel-heading">Resumen de Pago Estimado</div> -->
        <table class="table table-bordered" style="background-color: #fff;">
            <tr>
                <th>Concepto</th>
                <th style="text-align: right;">Detalle</th>
                <th style="text-align: right;">Subtotal</th>
            </tr>
            <tr>
                <td>Couta de Registro</td>
                <td style="text-align: right;">Tarifa Base (<?= $isEarlyBird ? 'Pre-Registro' : 'Registro' ?>)</td>
                <td style="text-align: right; font-weight: bold;" id="display-base-cost">$0.00</td>
            </tr>
            <tr>
                <td>Talleres / Visitas</td>
                <td style="text-align: right;">
                    Seleccionados: <span id="display-workshop-count">0</span> | 
                    A pagar: <span id="display-workshop-paid">0</span>
                </td>
                <td style="text-align: right; font-weight: bold;" id="display-workshop-total">$0.00</td>
            </tr>
            <tr class="success">
                <td colspan="2" style="text-align: right; font-size: 1.2em;"><b>TOTAL A PAGAR:</b></td>
                <td style="text-align: right; font-size: 1.2em; color: green;"><b id="display-grand-total">$0.00</b></td>
            </tr>
        </table>
    </div>

	<h3>Instrucciones de pago</h3>
	<p> <?= Html::encode('El total a pagar lo deberá realizar através de una transferencia bancaria a la siguiente cuenta:')?> </p>

    	<?= $form->field($registration, 'payment_type')->radioList([
		// 1 => 'Credit Card',
		2 => 'Transferencia bancaria (Cargue su recibo)',
		// 3 => 'Code',
	])->label('Tipo de Pago') ?>

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

<!-- <script src="../web/js/form.js"></script> -->