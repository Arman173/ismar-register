<?php

use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\bootstrap\ActiveForm;
use app\models\Registration; // I did this
use app\models\RegistrationType;
use app\models\AdditionalTickets;
use app\models\Workshops;
use app\models\Concei; // modelo donde se guarda datos generales del concei
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException; //agregado para lanzar excepciones

/* @var $this yii\web\View */
/* @var $registration app\models\Registration */
/* @var $invoice app\models\Invoice */
/* @var $form yii\widgets\ActiveForm */

// cargamos datos del concei mediante su modelo
$concei = Concei::find()->one();

if (!$concei) {
	throw new NotFoundHttpException('No existe un evento Concei');
}

$precio_taller = $concei->getCostoTaller();
$precio_visita = $concei->getCostoVisita();
$preventa = $concei->es_preventa();

// Determinamos si es Early Bird (Pre-registro) desde PHP para inicializar
$isEarlyBird = $preventa;

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
$isEarlyBirdStr = $isEarlyBird ? 'true' : 'false';
$is_new_registration = $registration->isNewRecord ? 'true' : 'false';

// INYECCIÓN AL WINDOW ---
$jsVariables = <<<JS
    window.es_nuevo_registro = {$is_new_registration};
	window.costo_taller = {$precio_taller};
	window.costo_visita = {$precio_visita};
    window.workshopCost = {'costo': '100'};
    window.isEarlyBird 	= {$isEarlyBirdStr};
	window.preventa		= {$isEarlyBirdStr};
    window.typePrices 	= {$pricesJsonString};
JS;

// Registramos las variables en el HEAD para que estén disponibles antes de que cargue tu JS externo
$this->registerJs($jsVariables, \yii\web\View::POS_HEAD);
// ------------------------------------------

$this->registerJsFile('@web/js/libs/ResponsiveInputs.js');

$this->registerJsFile('@web/js/registrationForm.js');

?>

<div class="registration-form">

    <?php $form = ActiveForm::begin([
		'layout' => 'horizontal',
		'options' => ['enctype' => 'multipart/form-data'],
	]); ?>
	

    <h3><?= Html::encode('Información Personal') ?></h3>


    <?= $form->field($registration, 'first_name')->textInput([
		'maxlength' => true,
	]) ?>

    <?= $form->field($registration, 'last_name')->textInput([
		'maxlength' => true,
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

    <?php if($registration->scenario == 'Update'): ?>
	<p class="alert alert-warning"><em>Nota: No puede actualizar su tipo de registro, si tiene algún conflicto con esto por favor contáctenos</em></p>
	<?php endif; ?>

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
                'radioOptions' => [
					'disabled' => ($registration->scenario == 'Update')? true: false,
				],
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
		'options' => ['style' => 'max-width: 700px; width: 100%; margin: 0;'],
	]);?>

    <?php echo $form->field($registration, 'registration_code')->textInput(['maxlength' => true])->label(null,[
		'class'=>'control-label col-sm-3 required',
		'disabled' => ($registration->scenario == 'Update')? true: false,
	]) ?>

	<p> <?= Html::encode('* El registro de estudiante y de profesores de la UADY requiere una prueba de estatus o, para estudiantes, una credencial de estudiante que confirme que la persona registrada es estudiante de tiempo completo en el momento de la conferencia.')?> </p>


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

    <?php
    // Obtenemos los modelos actuales
    $modelosTalleres = $dataProviderTalleres->getModels();
    $modelosVisitas = $dataProviderVisitas->getModels();

    // Obtenemos los IDs que el usuario ya tiene registrados
    $talleresPagadosIds = [];
    $visitasPagadasIds = [];
    
    if ($registration->scenario == 'Update' && !$registration->isNewRecord) {
        $talleresPagadosIds = \app\models\RegistroTaller::find()->select('taller_id')->where(['registration_id' => $registration->id])->column();
        $visitasPagadasIds  = \app\models\RegistroVisita::find()->select('visita_id')->where(['registration_id' => $registration->id])->column();
    }

    $talleresJs = [];
    foreach ($modelosTalleres as $taller) {
        // LA CLAVE: Si ya lo pagó, nos saltamos este ciclo y NO se lo mandamos al JS
        if (in_array($taller->id, $talleresPagadosIds)) {
            continue; 
        }

        // Usamos el ID del taller como llave (key) del arreglo para buscarlo fácil en JS
        $talleresJs[] = [
            'id'     => $taller->id,
            'nombre'   => $taller->nombre,
            'descripcion' => $taller->descripcion,
            'fecha'   => $taller->fecha,
            'horario'  => $taller->horario,
            'modalidad'=> $taller->modalidad,
            'tallerista' => $taller->tallerista,
            'cupo_general'=> $taller->getCupoGeneral(),
            'cupo_otros'   => $taller->getCupoOtros(),
            // 'disponible' => $taller->hayCupos() ? 'Disponible' : 'No disponible'
        ];
    }

    $visitasJs = [];
    foreach ($modelosVisitas as $visita) {
        // LA CLAVE: Si ya la pagó, nos saltamos este ciclo y NO se lo mandamos al JS
        if (in_array($visita->id, $visitasPagadasIds)) {
            continue; 
        }

        $visitasJs[] = [
            'id'     => $visita->id,
            'nombre'   => $visita->nombre,
            'descripcion' => $visita->descripcion,
            'fecha'   => $visita->fecha,
            'horario'  => $visita->horario,
            'modalidad'=> $visita->modalidad,
            'cupo_general' => $visita->getCupoGeneral(), //Nuevo
            'cupo_otros'   => $visita->getCupoOtros(),   //  
            'disponible'   => '' //

        ];
    }

    $jsonTalleres = Json::encode($talleresJs);
    $jsonVisitas = Json::encode($visitasJs);

    $this->registerJs("
        window.datosTalleres = {$jsonTalleres};
        window.datosVisitas = {$jsonVisitas};
    ", \yii\web\View::POS_HEAD); // POS_HEAD asegura que cargue antes que nuestro JS externo
    ?>
	
	<?php
		# Importamos css y nuestros scripts unificados para el formulario
		$this->registerCssFile('@web/css/ResponsiveInputs.css');
		//$this->registerJsFile('@web/js/libs/ResponsiveInputs.js');
		//$this->registerJsFile(
			//'@web/js/registrationForm.js', 
			//['depends' => [\yii\web\JqueryAsset::class]]
		//);
	?>
	
	<!-- PENDIENTE INVESTIGAR SI SE PUEDE ELIMINAR -->
	<?php
		$dataProviderWork = new ActiveDataProvider([
			'query' => Workshops::find(),
		]);
	?>

	<p style="margin-left:0.5cm">
		<b> <?= Html::encode('General:')?> </b> <?= Html::encode('Acceso a todas las conferencias, memorias de congreso y constancia digital de participación. Incluye un taller o una visita industrial.')?> 
        <br>
		<b> <?= Html::encode('Estudiante:')?> </b> <?= Html::encode('Acceso a todas las conferencias, memorias de congreso y constancia digital de participación. Incluye un taller o una visita industrial.')?>

		<br> <b> <?= Html::encode('Estudiante y Profesor UADY:')?> </b> <?= Html::encode('Acceso a todas las conferencias. No incluye talleres ni visitas industriales.')?>
    </p>

	<div class="alert alert-warning" style="margin-top: 30px; border-left: 5px solid #fff79e;">
        <p style="font-size: 1.1em; margin-bottom: 0;">
            <strong style="color: #128dd5;"><span class="glyphicon glyphicon-exclamation-sign"></span> </strong> 
            Todos los talleres se realizarán el día martes 6 de octubre de 2026.
        </p>
    </div>

    <!--INTERFAZ DE TALLERES-->
    
    <?php if ($registration->scenario == 'Update' && !empty($talleresPagadosIds)): ?>
        <div class="alert alert-info" style="background-color: #e8f4f8; border-color: #bce8f1; color: #31708f; margin-bottom: 10px;">
            <h5 style="margin-top: 0;"><b><span class="glyphicon glyphicon-ok-circle"></span> Talleres ya adquiridos y pagados:</b></h5>
            <ul style="list-style-type: none; padding-left: 0;">
                <?php 
                foreach ($modelosTalleres as $taller) {
                    if (in_array($taller->id, $talleresPagadosIds)) {
                        echo "<li style='margin-bottom: 8px; padding-bottom: 8px; border-bottom: 1px dashed #bce8f1;'>";
                        echo "<strong style='font-size: 1.05em;'>" . Html::encode($taller->nombre) . "</strong><br>";
                        echo "<span style='font-size: 0.9em; color: #4e86a0;'>";
                        echo "<span class='glyphicon glyphicon-calendar'></span> " . Html::encode($taller->fecha) . " &nbsp;&nbsp;|&nbsp;&nbsp; ";
                        echo "<span class='glyphicon glyphicon-time'></span> " . Html::encode($taller->horario) . " &nbsp;&nbsp;|&nbsp;&nbsp; ";
                        echo "<span class='glyphicon glyphicon-map-marker'></span> Modalidad: " . Html::encode($taller->modalidad);
                        echo "</span>";
                        echo "</li>";
                    }
                }
                ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="panel-seleccion-trigger">
        <div class="trigger-info">
            <span class="trigger-label">
                <?= ($registration->scenario == 'Update' && !empty($talleresPagadosIds)) ? 'Nuevos talleres a agregar:' : 'Talleres seleccionados:' ?>
            </span>
            <span class="trigger-count" id="contador-talleres">0</span>
        </div>
        
        <button type="button" class="btn btn-trigger-action btn-abrir-modal-fs" data-target="#modal-talleres">
            Seleccionar <span class="glyphicon glyphicon-chevron-right"></span>
        </button>
    </div>

    <div id="modal-talleres" class="modal-fs-container oculto">
        <div class="modal-fs-header">
            <h4 class="modal-fs-title">Selección de Talleres</h4>
            <button type="button" class="btn btn-fs-close btn-cerrar-modal-fs">
                Cerrar <span class="glyphicon glyphicon-remove"></span>
            </button>
        </div>
        <div class="modal-fs-body">
            <div id="checkbox-talleres-container"></div>
        </div>
    </div>

    <!-- INTERFAZ DE VISITAS                        -->
    <div class="alert alert-warning" style="margin-top: 30px; border-left: 5px solid #ffcc84;">
        <p style="font-size: 1.1em; margin-bottom: 0;">
            <strong style="color: #d58512;"><span class="glyphicon glyphicon-exclamation-sign"></span> Requisitos de acceso en las visitas:</strong> 
            Zapatos cerrados, pantalón largo (sin roturas), cabello recogido, sin aretes, anillos, pulseras y similares. Prohibido el uso del celular y de la toma de fotografías.
        </p>
    </div>

    <?php if ($registration->scenario == 'Update' && !empty($visitasPagadasIds)): ?>
        <div class="alert alert-info" style="background-color: #fcf8e3; border-color: #faebcc; color: #8a6d3b; margin-bottom: 10px;">
            <h5 style="margin-top: 0;"><b><span class="glyphicon glyphicon-briefcase"></span> Visitas ya adquiridas y pagadas:</b></h5>
            <ul style="list-style-type: none; padding-left: 0;">
                <?php 
                foreach ($modelosVisitas as $visita) {
                    if (in_array($visita->id, $visitasPagadasIds)) {
                        echo "<li style='margin-bottom: 8px; padding-bottom: 8px; border-bottom: 1px dashed #faebcc;'>";
                        echo "<strong style='font-size: 1.05em;'>" . Html::encode($visita->nombre) . "</strong><br>";
                        echo "<span style='font-size: 0.9em; color: #a6894c;'>";
                        echo "<span class='glyphicon glyphicon-calendar'></span> " . Html::encode($visita->fecha) . " &nbsp;&nbsp;|&nbsp;&nbsp; ";
                        echo "<span class='glyphicon glyphicon-time'></span> " . Html::encode($visita->horario);
                        echo "</span>";
                        echo "</li>";
                    }
                }
                ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="panel-seleccion-trigger">
        <div class="trigger-info">
            <span class="trigger-label">
                <?= ($registration->scenario == 'Update' && !empty($visitasPagadasIds)) ? 'Nuevas visitas a agregar:' : 'Visitas seleccionadas:' ?>
            </span>
            <span class="trigger-count" id="contador-visitas">0</span>
        </div>
        
        <button type="button" class="btn btn-trigger-action btn-abrir-modal-fs" data-target="#modal-visitas">
            Seleccionar <span class="glyphicon glyphicon-chevron-right"></span>
        </button>
    </div>

    <div id="modal-visitas" class="modal-fs-container oculto">
        <div class="modal-fs-header">
            <h4 class="modal-fs-title">Selección de Visitas Industriales</h4>
            <button type="button" class="btn btn-fs-close btn-cerrar-modal-fs">
                Cerrar <span class="glyphicon glyphicon-remove"></span>
            </button>
        </div>
        <div class="modal-fs-body">
            <div id="checkbox-visitas-container"></div>
        </div>
    </div>

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
					<div id="display-talleres-nombres" style="font-size: 0.85em; color: #777; margin-top: 5px; text-align: right; line-height: 1.3;"></div>
                </td>
                <td style="text-align: right; font-weight: bold;" id="display-talleres-total">$0.00</td>
			</tr>
            <tr>
                <td>Visitas Industriales</td>
                <td style="text-align: right;">
                    Seleccionados: <span id="display-visitas-count">0</span>
					<div id="display-visitas-nombres" style="font-size: 0.85em; color: #777; margin-top: 5px; text-align: right; line-height: 1.3;"></div>
                </td>
                <td style="text-align: right; font-weight: bold;" id="display-visitas-total">$0.00</td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: right;">
                    <em>A pagar: <span id="display-total-extras-paid">0</span> x $<?= $precio_taller ?></em>
                </td>
                <td style="text-align: right; font-weight: bold; color: #d9534f;" id="display-extras-total">$0.00</td>
            </tr>
            <tr class="success">
                <td colspan="2" style="text-align: right; font-size: 1.2em;"><b>TOTAL A PAGAR:</b></td>
                <td style="text-align: right; font-size: 1.2em; color: green;"><b id="display-grand-total">$0.00</b></td>
            </tr>
        </table>
    </div>

<div id="instrucciones-pago">

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

</div>

    	<?= $form->field($registration, 'payment_type')->radioList([
		// 1 => 'Credit Card',
		2 => 'Transferencia bancaria (Cargue su recibo)',
		// 3 => 'Codigo de Registro',
	], [
		'itemOptions' => [
			'disabled' => ($registration->scenario == 'Update')? true: false
		],
		'unselect' => null,
	])->label('Tipo de Pago') ?>

	<?php echo $form->field($registration, 'file_payment_receipt')->fileInput() ?>
    
	<?php /*
    <div class="form-group">
        <?= Html::submitButton($registration->isNewRecord ? Yii::t('app', 'Submit') : Yii::t('app', 'Update data'), ['class' => $registration->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>
	*/?>

	<div class="form-group">
        <?= Html::submitButton($registration->isNewRecord ? Yii::t('app', 'Submit') : Yii::t('app', 'Update data'), [
            'class' => $registration->isNewRecord ? 'btn btn-success' : 'btn btn-primary',
            'id' => 'btn-submit',
            'onclick' => "this.disabled=true; this.innerText='Enviando...'; this.form.submit();" 
        ]) ?>
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

.panel-seleccion-trigger {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #ffffff; /* Fondo completamente blanco */
    border: 1px solid #dce0e5; /* Borde gris muy sutil */
    border-radius: 6px;
    padding: 15px 20px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04); /* Sombra casi invisible para dar relieve */
}

.trigger-info {
    font-size: 16px;
    color: #333333;
}

.trigger-count {
    font-weight: bold;
    color: #333333;
    background-color: #f4f6f8; /* Un fondo gris muy claro para el número */
    padding: 4px 12px;
    border-radius: 15px;
    margin-left: 8px;
    border: 1px solid #e1e4e8;
}

.btn-trigger-action {
    background-color: #ffffff;
    color: #333;
    border: 1px solid #ccc;
    border-radius: 4px;
    padding: 6px 16px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn-trigger-action:hover {
    background-color: #f8f9fa;
    border-color: #adadad;
}

.oculto {
    display: none !important;
}

.modal-fs-container {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background-color: #ffffff; /* Fondo blanco para el modal */
    z-index: 1040;
    display: flex;
    flex-direction: column;
}

.modal-fs-header {
    display: flex;
    flex-direction: row !important; 
    justify-content: space-between;
    align-items: center;
    padding: 15px 25px;
    background-color: #ffffff;
    border-bottom: 1px solid #e5e5e5;
}

.modal-fs-title {
    margin: 0;
    font-size: 20px;
    font-weight: 600;
    color: #333;
}

.btn-fs-close {
    background-color: transparent;
    color: #666;
    border: 1px solid transparent;
    font-size: 14px;
    font-weight: 500;
    padding: 6px 12px;
    border-radius: 4px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.btn-fs-close:hover {
    background-color: #f0f0f0;
    color: #111;
}

.modal-fs-body {
    flex-grow: 1;
    overflow-y: auto;
    padding: 20px 25px;
	padding-bottom: 40px;
    background-color: #ffffff; 
}

/* Ajustes mínimos para pantallas de celular muy pequeñas */
@media (max-width: 480px) {
    .panel-seleccion-trigger { padding: 12px 15px; }
    .trigger-info { font-size: 14px; }
    .trigger-count { padding: 2px 8px; }
    .modal-fs-header { padding: 12px 15px; }
    .modal-fs-title { font-size: 18px; }
    .modal-fs-body { padding: 15px; }
}

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
        max-height: 60vh; 			/* El cuerpo medirá como máximo el 60% de la pantalla */
        overflow-y: auto; 			/* Si el texto es más largo, crea una barra de scroll */
        padding: 20px;
    }

	@media screen and (max-width: 767px) {
		
		/* Forzamos a la tabla a comportarse como bloque */
		#fee_type table, 
		#fee_type thead, 
		#fee_type tbody, 
		#fee_type th, 
		#fee_type td, 
		#fee_type tr { 
			display: block; 
			width: 100% !important;
		}
		
		/* Ocultamos el encabezado visualmente (pero lo dejamos para lectores de pantalla) */
		#fee_type thead tr { 
			position: absolute;
			top: -9999px;
			left: -9999px;
		}
		
		/* Estilizamos cada fila (<tr>) para que parezca una tarjeta */
		#fee_type tbody tr { 
			border: 1px solid #ddd;
			border-radius: 8px; 
			margin-bottom: 15px; /* Separación entre tarjetas */
			box-shadow: 0 2px 5px rgba(0,0,0,0.05);
			background-color: #fff;
			overflow: hidden;
		}
		
		/* Estilizamos las celdas (<td>) */
		#fee_type td { 
			border: none !important;
			border-bottom: 1px solid #eee !important; 
			position: relative;
			padding: 12px 15px 12px 50% !important; /* Espacio izquierdo para la etiqueta */
			text-align: right !important; /* El valor se va a la derecha */
			min-height: 45px;
		}
		#fee_type td:last-child { border-bottom: none !important; }
		
		/* Inyectamos los títulos de las columnas usando pseudo-elementos ::before */
		#fee_type td::before { 
			position: absolute;
			top: 12px;
			left: 15px;
			width: 45%; 
			padding-right: 10px; 
			white-space: nowrap;
			text-align: left;
			font-weight: 600;
			color: #555;
		}
		
		/* Definimos el texto para cada columna basada en su posición */
		#fee_type td:nth-of-type(1)::before { content: "Seleccionar:"; }
		#fee_type td:nth-of-type(2)::before { content: "Tipo de Registro:"; }
		#fee_type td:nth-of-type(3)::before { content: "Pre-Registro:"; }
		#fee_type td:nth-of-type(4)::before { content: "Registro Regular:"; }

		#fee_type td:nth-of-type(1) {
			background-color: #f9f9f9; /* Destacar la zona de selección */
		}
		#fee_type td:nth-of-type(1) input[type="radio"] {
			transform: scale(1.5); /* Hacemos el botón más grande */
			margin-top: 0;
			cursor: pointer;
		}
	}

</style>

<?php

// Recuperar el Tipo de Registro seleccionado previamente (o 1 por defecto)
$tipoSeleccionado = $registration->registration_type_id ? $registration->registration_type_id : 1;

// Recuperar los Talleres desde el POST (Solo lo nuevo en caso de error de validación)
$postTalleres = Yii::$app->request->post('talleres_seleccionados', []);

// Recuperar las Visitas desde el POST
$postVisitas = Yii::$app->request->post('visitas_seleccionadas', []);

$jsonPostTalleres = yii\helpers\Json::encode($postTalleres);
$jsonPostVisitas = yii\helpers\Json::encode($postVisitas);

$jsRecuperarChecks = <<<JS
$(document).ready(function() {
    // Usamos setTimeout para darle tiempo a los scripts externos de dibujar 
    // la tabla y los checkboxes antes de que intentemos marcarlos.
    setTimeout(function() {
        
        // RECUPERAR TIPO DE REGISTRO
        var tipoGuardado = '{$tipoSeleccionado}';
        $("input[name='kvradio']").prop("checked", false);
        $("input[name='kvradio'][value='" + tipoGuardado + "']").prop("checked", true);
        $("#registration-registration_type_id").val(tipoGuardado);
        
        //RECUPERAR TALLERES
        var selectedTalleres = {$jsonPostTalleres};
        if (selectedTalleres && selectedTalleres.length > 0) {
            selectedTalleres.forEach(function(id) {
                $('input[name="talleres_seleccionados[]"][value="' + id + '"]').prop('checked', true);
            });
            // Actualizar el número visual del contador de talleres
            $("#contador-talleres").text(selectedTalleres.length);
        }
        
        //RECUPERAR VISITAS
        var selectedVisitas = {$jsonPostVisitas};
        if (selectedVisitas && selectedVisitas.length > 0) {
            selectedVisitas.forEach(function(id) {
                $('input[name="visitas_seleccionadas[]"][value="' + id + '"]').prop('checked', true);
            });
            // Actualizar el número visual del contador de visitas
            $("#contador-visitas").text(selectedVisitas.length);
        }
        
        // ACTUALIZAR TOTALES
        $('input[name="talleres_seleccionados[]"], input[name="visitas_seleccionadas[]"]').first().trigger('change');
        
        if(typeof calculateTotal === 'function') {
            calculateTotal();
        }
        if(typeof actualizarConceptoPago === 'function') {
            actualizarConceptoPago();
        }
        
    }, 600);
});
JS;

// Registramos el script al final de la página
$this->registerJs($jsRecuperarChecks, \yii\web\View::POS_END);
?>

<!-- <script src="../web/js/form.js"></script> -->