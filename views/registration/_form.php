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

	// --- NUEVO CODIGO: FUNCION DEL CONCEPTO ---
    function calculateConceptoPago() {
        var lastName = $(\'#registration-last_name\').val() ? $(\'#registration-last_name\').val().trim().toUpperCase() : \'\';
        var firstName = $(\'#registration-first_name\').val() ? $(\'#registration-first_name\').val().trim().toUpperCase() : \'\';

        var lastNameCode = lastName.substring(0, 3).padStart(3, \'0\');
        var firstNameCode = firstName.substring(0, 3).padStart(3, \'0\');

        var typeId = $(\'#registration-registration_type_id\').val();
        var typeCode = \'RU\'; 
        if (typeId === \'1\') {
            typeCode = \'RG\';
        } else if (typeId === \'12\') {
            typeCode = \'RE\';
        }

        var finalCode = lastNameCode + firstNameCode + typeCode;
        $(\'#display-concepto-pago\').text(finalCode);
    }

	calculateConceptoPago();

    $(\'#registration-first_name, #registration-last_name\').on(\'keyup change\', function() {
        calculateConceptoPago();
    });
    // ------------------------------------------
    
    function calculateTotal() {
        var total = 0;
        var baseCost = 0;
        var extrasTotalCost = 0; // Costo de los talleres/visitas que SÍ se cobran
        
        // 1. Obtener ID del tipo de registro
        var selectedTypeId = $(\'#registration-registration_type_id\').val();
        
        // Costo base según Early Bird
        if (selectedTypeId && typePrices[selectedTypeId]) {
            if (isEarlyBird) {
                baseCost = parseFloat(typePrices[selectedTypeId].early) || 0;
            } else {
                baseCost = parseFloat(typePrices[selectedTypeId].late) || 0;
            }
        } else {
            baseCost = 0;
        }

        // 2. Contar de AMBAS tablas
        var selectedTalleresCount = $(\'#grid-talleres\').yiiGridView(\'getSelectedRows\').length;
        var selectedVisitasCount = $(\'#grid-visitas\').yiiGridView(\'getSelectedRows\').length;
        var totalExtrasCount = selectedTalleresCount + selectedVisitasCount;
        
        // 3. Lógica de Cobro de Extras (Talleres + Visitas)
        var paidExtras = 0;
        var typeStr = String(selectedTypeId); 
        
        if (totalExtrasCount > 0) {
            if (typeStr === \'1\' || typeStr === \'12\') {
                // General (1) y Estudiante (12): 1 Gratis en total (sea taller o visita)
                paidExtras = Math.max(0, totalExtrasCount - 1);
            } else if (typeStr === \'17\') {
                // UADY (17): Paga todos
                paidExtras = totalExtrasCount;
            } else {
                // Default: Paga todos
                paidExtras = totalExtrasCount; 
            }
        }
        
        extrasTotalCost = paidExtras * workshopCost;
        total = baseCost + extrasTotalCost;

        // 4. Actualizar vista en la tabla
        $(\'#display-base-cost\').text(\'$\' + baseCost.toFixed(2));
        
        // Fila Talleres
        $(\'#display-talleres-count\').text(selectedTalleresCount);
        $(\'#display-talleres-total\').text(\'$\' + (selectedTalleresCount * workshopCost).toFixed(2));
        
        // Fila Visitas
        $(\'#display-visitas-count\').text(selectedVisitasCount);
        $(\'#display-visitas-total\').text(\'$\' + (selectedVisitasCount * workshopCost).toFixed(2));
        
        // Fila Resumen Extras a Pagar
        $(\'#display-total-extras-paid\').text(paidExtras);
        $(\'#display-extras-total\').text(\'$\' + extrasTotalCost.toFixed(2));
        
        // Total Final
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
		calculateConceptoPago();
    });

    // Cambio en Checkbox de Talleres
    $(\'#grid-talleres, #grid-visitas\').on(\'click\', function() {
        // Tu función de mapeo a inputs hidden (la integramos aquí para asegurar orden)
        // mapWorkshopsToHiddenInputs(); 
        calculateTotal();
    });

    // Tu función de mapeo (tal cual estaba)
    // function mapWorkshopsToHiddenInputs() {
    //     $(\'[name="Registration[W1]"]\').val(0);
    //     $(\'[name="Registration[W2]"]\').val(0);
    //     $(\'[name="Registration[W3]"]\').val(0);
    //     $(\'[name="Registration[W4]"]\').val(0);
    //     $(\'[name="Registration[W5]"]\').val(0);
    //     $(\'[name="Registration[W6]"]\').val(0);
    //     $(\'[name="Registration[W7]"]\').val(0);
    //     $(\'[name="Registration[T1]"]\').val(0);
        
    //     var keys = $(\'#workshop_type\').yiiGridView(\'getSelectedRows\');
    //     // Iteramos con cuidado
    //     if (keys) {
    //         for (var i = 0; i < keys.length; i++) { 
    //             var k = parseInt(keys[i]);
    //             if(k==1) $(\'[name="Registration[W1]"]\').val(1);
    //             if(k==2) $(\'[name="Registration[W2]"]\').val(1);
    //             if(k==3) $(\'[name="Registration[W3]"]\').val(1);
    //             if(k==4) $(\'[name="Registration[W4]"]\').val(1);
    //             if(k==5) $(\'[name="Registration[W5]"]\').val(1);
    //             if(k==6) $(\'[name="Registration[W6]"]\').val(1);
    //             if(k==7) $(\'[name="Registration[W7]"]\').val(1);
    //             if(k==8) $(\'[name="Registration[T1]"]\').val(1);
    //         }
    //     }
    // }

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

	function showRegistrationCode()
	{
		$("[name=\'Registration[registration_code]\']").removeAttr("disabled");
		$(".field-registration-registration_code").show();
	}
	
	function hideRegistrationCode()
	{
		$("[name=\'Registration[registration_code]\']").attr("disabled","disabled");
		$(".field-registration-registration_code").hide();
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

	function toggleRegistrationCode()
	{
		if( $("[name=\'Registration[payment_type]\']:checked").val() == 3 ){
			showRegistrationCode();
		}else{
			hideRegistrationCode();
		}
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

	function toggleModalidadPresentacion() {
		var registrationType = $("[name=\'Registration[registration_type_id]\']").val();
		// El ID 17 corresponde a "Estudiantes y Profesores UADY"
		 if (registrationType == "17") {
			$("#div-modalidad-presentacion").hide();
			$("#registration-modalidad_presentacion").val("");
			$("#leyenda-modalidad-uady").show(); // <--- MUESTRA LA LEYENDA
		} else { 
			$("#div-modalidad-presentacion").show();
			$("#leyenda-modalidad-uady").hide(); // <--- OCULTA LA LEYENDA
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
		toggleRegistrationCode();
	});
	
		
	toggleStudentId();
	toggleFilePaymentReceipt();
	toggleInvoice();
	toggleModalidadPresentacion();
	toggleRegistrationCode();


	var $grid = $(\'#fee_type\'); // your registration grid identifier

	$("input[name=kvradio][value=\'1\']").prop("checked",true);

	$grid.on( \'grid.radiochecked\', function(ev, key, val){
		$("#registration-registration_type_id").val(val);

	calculateConceptoPago();
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
		toggleModalidadPresentacion();
		}
	);

	//$("#workshop_type input[type=checkbox]").click(function(){
	
	// var $workgrid = $(\'#workshop_type\');
	// $workgrid.on(\'click\',function(){
	// 	$("[name=\'Registration[W1]\']").val(0);
	// 	$("[name=\'Registration[W2]\']").val(0);
	// 	$("[name=\'Registration[W3]\']").val(0);
	// 	$("[name=\'Registration[W4]\']").val(0);
	// 	$("[name=\'Registration[W5]\']").val(0);
	// 	$("[name=\'Registration[W6]\']").val(0);
	// 	$("[name=\'Registration[W7]\']").val(0);
	// 	$("[name=\'Registration[T1]\']").val(0);
		
	// 	var keys = $workgrid.yiiGridView(\'getSelectedRows\');
	// 	//if (typeof keys[0] !== \'undefined\') {
	// 	for (i = 0; i < keys.length; i++) { 	
	// 		switch(keys[i]){
	// 			case 1: $("[name=\'Registration[W1]\']").val(1); break;
	// 			case 2: $("[name=\'Registration[W2]\']").val(1); break;
	// 			case 3: $("[name=\'Registration[W3]\']").val(1); break;
	// 			case 4: $("[name=\'Registration[W4]\']").val(1); break;
	// 			case 5: $("[name=\'Registration[W5]\']").val(1); break;
	// 			case 6: $("[name=\'Registration[W6]\']").val(1); break;
	// 			case 7: $("[name=\'Registration[W7]\']").val(1); break;
	// 			case 8: $("[name=\'Registration[T1]\']").val(1); break;
	// 		}
	// 	}
	// });


		// Al hacer clic en cualquier botón de "Leer más"
  		$(".btn-ver-detalles").on("click", function(e) {
        e.preventDefault();
        
        // Obtenemos la info guardada en el botón
        var titulo = $(this).data("title");
        var detalles = $(this).data("details");
        
        // La ponemos dentro del Modal
        $("#modal-title-text").html(titulo);
        $("#modal-body-text").html(detalles);
        
        // Mostramos el Modal
        $("#modal-detalles").modal("show");
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
		'placeholder' => 'Por favor, ingrese su número de teléfono (ej. 529995555555)',
	]) ?>

    <?= $form->field($registration, 'email')->textInput(['maxlength' => true]) ?>
	
    
    
    <?= $form->field($registration, 'registration_type_id')->hiddenInput()->label(false) ?>
    
    <h3><?= Html::encode('Información de Registro') ?></h3>
    
    <h4><?= Html::encode('Tipos de Registro') ?></h4>
    
	<p style="margin-left:0.5cm">
		<b> <?= Html::encode('General:')?> </b> <?= Html::encode('Acceso a todas las conferencias, memorias de congreso y constancia digital de participación. Incluye un taller o una visita industrial.')?> 
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

	<?php
    $areasTrabajo = [
        'Energías renovables' => 'Energías renovables',
        'Ingeniería ambiental' => 'Ingeniería ambiental',
        'Inteligencia artificial' => 'Inteligencia artificial',
        'Alimentación y salud' => 'Alimentación y salud',
        'Ingeniería de las estructuras y la construcción' => 'Ingeniería de las estructuras y la construcción',
        'Procesamiento de imágenes' => 'Procesamiento de imágenes',
        'Robótica y visión computacional' => 'Robótica y visión computacional',
        'Moléculas y materiales funcionales' => 'Moléculas y materiales funcionales',
        'Ingeniería física' => 'Ingeniería física',
        'Cómputo científico/cuántico' => 'Cómputo científico/cuántico',
        'Ciencia y tecnología de la información' => 'Ciencia y tecnología de la información',
        'Biotecnología y Bioprocesos' => 'Biotecnología y Bioprocesos',
        'Ingeniería de procesos e innovación industrial' => 'Ingeniería de procesos e innovación industrial',
        'Matemáticas básicas y aplicadas' => 'Matemáticas básicas y aplicadas',
        'Ingeniería de software' => 'Ingeniería de software',
        'Tecnologías emergentes en computación' => 'Tecnologías emergentes en computación',
        'Educación, sociedad y formación humanista en ciencias' => 'Educación, sociedad y formación humanista en ciencias',
    ];
    ?>

    <?= $form->field($registration, 'area_trabajo')->dropDownList($areasTrabajo, ['prompt' => 'Seleccione el área de su trabajo...']) ?>


	<div id="div-modalidad-presentacion">
        <?= $form->field($registration, 'modalidad_presentacion')->dropDownList([
            'Presencial' => 'Presencial',
            'Virtual' => 'Virtual',
            'Cualquiera' => 'Cualquiera',
        ], ['prompt' => 'Seleccione una modalidad...']) ?>
    </div>

    <div id="leyenda-modalidad-uady" style="display: none; color: #7f8c8d; font-size: 0.9em; margin-bottom: 15px; margin-left: 200px;">
        <em>* Nota: Para Estudiantes y Profesores UADY, la modalidad de presentación es Presencial.</em>
    </div>
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

    <h3><?= Html::encode('Talleres y Visitas Industriales') ?></h3>
	
	<?php $dataProviderWork = new ActiveDataProvider([
		'query' => Workshops::find(),
	]); ?>

	<p style="margin-left:0.5cm">
		<b> <?= Html::encode('General:')?> </b> <?= Html::encode('Acceso a todas las conferencias, memorias de congreso y constancia digital de participación. Incluye un taller o una visita industrial.')?> 
        <br>
		<b> <?= Html::encode('Estudiante:')?> </b> <?= Html::encode('Acceso a todas las conferencias, memorias de congreso y constancia digital de participación. Incluye un taller o una visita industrial.')?>

		<br> <b> <?= Html::encode('Estudiante y Profesor UADY:')?> </b> <?= Html::encode('Acceso a todas las conferencias. No incluye talleres ni visitas industriales.')?>
    </p>

	<?= GridView::widget([
		'id' => 'grid-talleres',
		'dataProvider' => $dataProviderTalleres,
		'columns' => [
			[
				'class' => 'kartik\grid\CheckboxColumn',
				'name' => 'talleres_seleccionados',
				//'rowHighlight' => true,
				'header' => '',
			],

			[
				'attribute' => 'nombre',
				'label' => 'Nombre del Taller',
			],
			// Ventana emergente para descripción de talleres
            [
                'attribute' => 'descripcion',
                'label' => 'Detalles',
                'format' => 'raw',
                'value' => function ($model) {
                    if (empty($model->descripcion)) {
                        return '';
                    }
                    return Html::button('<span class="glyphicon glyphicon-info-sign"></span> Leer más', [
                        'class' => 'btn btn-info btn-xs btn-ver-detalles',
                        'data-title' => Html::encode($model->nombre),
                        'data-details' => Html::encode($model->descripcion),
                    ]);
                },
            ],

			[
				'attribute' => 'fecha',
				'header' => 'Fecha',
			],
			[
				'attribute' => 'horario',
				'header' => 'Horario',
			]
		],
		'summary'=>'',
		'options' => ['style' => 'width:700px;'],
	]);?>

<div class="alert alert-warning" style="margin-top: 30px; border-left: 5px solid #ffcc84;">
        <p style="font-size: 1.1em; margin-bottom: 0;">
            <strong style="color: #d58512;"><span class="glyphicon glyphicon-exclamation-sign"></span> Requisitos de acceso en las visitas:</strong> 
            Zapatos cerrados, pantalón largo (sin roturas), cabello recogido, sin aretes, anillos, pulseras y similares. Prohibido el uso del celular y de la toma de fotografías.
        </p>
    </div>

	<?= GridView::widget([
		'id' => 'grid-visitas',
		'dataProvider' => $dataProviderVisitas,
		'columns' => [
			[
				'class' => 'kartik\grid\CheckboxColumn',
				'name' => 'visitas_seleccionadas',
				//'rowHighlight' => true,
				'header' => '',
			],
			//'id',
			[
				'attribute' => 'nombre',
				'label' => 'Nombre de la Empresa',
			],

			[
				'attribute' => 'fecha',
				'label' => 'Fecha',
			],
			// Ventana emergente para visitas...
            [
                'attribute' => 'descripcion',
                'label' => 'Detalles',
                'format' => 'raw',
                'value' => function ($model) {
                    if (empty($model->descripcion)) {
                        return '';
                    }
                    return Html::button('<span class="glyphicon glyphicon-info-sign"></span> Leer más', [
                        'class' => 'btn btn-info btn-xs btn-ver-detalles',
                        'data-title' => Html::encode($model->nombre),
                        'data-details' => Html::encode($model->descripcion),
                    ]);
                },
            ],

			[
				'attribute' => 'horario',
				'label' => 'Horario',
				// 'format' => ['time', 'php:H:i'],
			]
		],
		'summary'=>'',
		'options' => ['style' => 'width:700px;'],
	]);?>

	<?= $form->field($registration, 'proceedings_copies')->hiddenInput()->label(false) ?>
	
	<!-- TALLERES Y VISITAS (WORKSHOP) END -->

	<h3><?= Html::encode('Solo para Mexicanos (Documento oficial deducible de impuestos)')?></h3>

    <div style= "color: red; font-size: 1.1em; margin-bottom: 15px;">
        <strong> </strong> El ConCEI NO emite facturas a nombre de la Universidad Autónoma de Yucatán.
    </div>

	<?= $form->field($registration, 'invoice_required')->radioList(
		[
			0 => 'No requerida',
			1 => 'Requerida',
		]
	)->label('¿Requiere Factura? (Solo México)') ?>

	
    <?= $form->field($invoice, 'business_name')->textInput(['maxlength' => true])->hint('<span style="color:red;">Recuerda: El ConCEI NO emite facturas a nombre de la Universidad Autónoma de Yucatán.</span>') ?>


    <?= $form->field($invoice, 'rfc')->textInput(['maxlength' => true]) ?>

    <?= $form->field($invoice, 'address')->textInput(['maxlength' => true]) ?>

    <?= $form->field($invoice, 'zip_code')->textInput(['maxlength' => true]) ?>

    <?= $form->field($invoice, 'city')->textInput(['maxlength' => true]) ?>

    <?= $form->field($invoice, 'state')->textInput(['maxlength' => true]) ?>

    <?= $form->field($invoice, 'email')->textInput(['maxlength' => true]) ?>
        
    <?= $form->field($invoice, 'email')->textInput(['maxlength' => true]) ?>
        
    
    <h3><?= Html::encode('Bolsa de Trabajo')?></h3>
    <p> <?= Html::encode('Si deseas compartir tu Curriculum Vitae (CV) con las empresas patrocinadoras, puedes adjuntarlo aquí, en formato pdf.')?> </p>
    <?= $form->field($registration, 'file_cv')->fileInput()->label('Subir CV') ?>
    
	<h3>Política de cancelación</h3>
	<p> <?= Html::encode('Las cuotas de inscripción, talleres y visitas industriales no serán rembolsables. Es importante destacar que a los autores que no se presenten se les retirará su artículo de las memorias del congreso. Para cualquier duda o aclaracion favor de contactar concei@correo.uady.mx')?> </p>
    
	<h3>Resumen de Pago</h3>
    <div class="panel panel-default" style="margin-top: 20px;">
        <table class="table table-bordered" style="background-color: #fff;">
            <tr>
                <th>Concepto</th>
                <th style="text-align: right;">Detalle</th>
                <th style="text-align: right;">Subtotal</th>
            </tr>
            <tr>
                <td>Cuota de Registro</td>
                <td style="text-align: right;">Tarifa Base (<?= $isEarlyBird ? 'Pre-Registro' : 'Registro' ?>)</td>
                <td style="text-align: right; font-weight: bold;" id="display-base-cost">$0.00</td>
            </tr>
            <tr>
                <td>Talleres</td>
                <td style="text-align: right;">
                    Seleccionados: <span id="display-talleres-count">0</span>
                </td>
                <td style="text-align: right; font-weight: bold;" id="display-talleres-total">$0.00</td>
            </tr>
            <tr>
                <td>Visitas Industriales</td>
                <td style="text-align: right;">
                    Seleccionados: <span id="display-visitas-count">0</span>
                </td>
                <td style="text-align: right; font-weight: bold;" id="display-visitas-total">$0.00</td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: right;">
                    <em>A pagar: <span id="display-total-extras-paid">0</span> x $<?= $costo_taller ?></em>
                </td>
                <td style="text-align: right; font-weight: bold; color: #d9534f;" id="display-extras-total">$0.00</td>
            </tr>
            <tr class="success">
                <td colspan="2" style="text-align: right; font-size: 1.2em;"><b>TOTAL A PAGAR:</b></td>
                <td style="text-align: right; font-size: 1.2em; color: green;"><b id="display-grand-total">$0.00</b></td>
            </tr>
        </table>
    </div>

	<h3>Instrucciones de pago</h3>
	
	<div class="well" style="background-color: #f8f9fa; border-left: 5px solid #0055A5;">
		<p style="font-size: 1.1em; margin-bottom: 10px;">El pago podrá ser realizado por transferencia bancaria con los siguientes datos:</p>
		<ul style="list-style-type: none; padding-left: 0; font-size: 1.1em;">
			<li><b>Banco:</b> HSBC</li>
			<li><b>Cuenta:</b> 4100561613</li>
			<li><b>CLABE:</b> 021910041005616132</li>
			<li><b>Sucursal:</b> 00902</li>
			<li><b>A nombre de:</b> UADY Facultad de Matemáticas</li>
		</ul>

        <hr style="border-top: 2px dashed #bdc3c7; margin: 20px 0;">

        <h4 style="margin-top:0; color: #2c3e50;">Concepto de Pago Obligatorio</h4>
        <p style="font-size: 1.05em;">Al hacer su transferencia en la app de su banco, ingrese <b>exactamente</b> la siguiente clave en el apartado de "Concepto" o "Motivo de pago":</p>
        
        <div style="text-align: center; font-size: 2.2em; font-weight: bold; letter-spacing: 2px; color: #d9534f; margin: 15px 0;" id="display-concepto-pago">
            000000RU
        </div>
        
        <p style="font-size: 0.85em; color: #7f8c8d; text-align: center; margin-bottom: 0;"><i>*Esta clave se actualiza automáticamente al escribir su nombre, apellido, elegir su tipo de registro, talleres y visitas.</i></p>
	</div>

    	<?= $form->field($registration, 'payment_type')->radioList([
		// 1 => 'Credit Card',
		2 => 'Transferencia bancaria (Cargue su recibo)',
		3 => 'Codigo de Registro',
	], [
		'itemOptions' => [
			'disabled' => ($registration->scenario == 'Update')? true: false
		],
		'unselect' => null,
	])->label('Tipo de Pago') ?>

	<?php echo $form->field($registration, 'file_payment_receipt')->fileInput() ?>
	
	<?php echo $form->field($registration, 'registration_code')->textInput(['maxlength' => true])->label(null,[
		'class'=>'control-label col-sm-3 required',
		'disabled' => ($registration->scenario == 'Update')? true: false,
	]) ?>
    
    <div class="form-group">
        <?= Html::submitButton($registration->isNewRecord ? Yii::t('app', 'Submit') : Yii::t('app', 'Update data'), ['class' => $registration->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<div class="modal fade" id="modal-detalles" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="modal-title-text">Detalles</h4>
      </div>
      <div class="modal-body" id="modal-body-text" style="word-wrap: break-word;">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<style>
    #modal-detalles {
        text-align: center;
    }
    #modal-detalles::before {
        content: '';
        display: inline-block;
        height: 100%;
        vertical-align: middle;
        margin-right: -4px;
    }
    #modal-detalles .modal-dialog {
        display: inline-block;
        text-align: left;
        vertical-align: middle;
        margin: 0 auto;
        width: 90%; 
        max-width: 600px; 
    }
	#modal-detalles .modal-body {
        max-height: 60vh; /* El cuerpo medirá como máximo el 60% de la pantalla */
        overflow-y: auto; /* Si el texto es más largo, crea una barra de scroll */
        padding: 20px;
    }

</style>

<!-- <script src="../web/js/form.js"></script> -->