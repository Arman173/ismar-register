document.addEventListener('DOMContentLoaded', (event) => {
    console.log('El DOM está listo');

    // Variables PHP inyectadas
    var workshopCost = 100;
    var isEarlyBird = false;
    const registrationPrices = {
        "1": {
            "early": 50,
            "late": 80,
            "name": "Student"
        },
        "2": {
            "early": 100,
            "late": 150,
            "name": "Professional"
        },
        "3": {
            "early": 70,
            "late": 120,
            "name": "Online"
        }
    };

	// --- NUEVO CODIGO: FUNCION DEL CONCEPTO ---
    function calculateConceptoPago() {
        var lastName = $('#registration-last_name').val() ? $('#registration-last_name').val().trim().toUpperCase() : '';
        var firstName = $('#registration-first_name').val() ? $('#registration-first_name').val().trim().toUpperCase() : '';

        var lastNameCode = lastName.substring(0, 3).padStart(3, '0');
        var firstNameCode = firstName.substring(0, 3).padStart(3, '0');

        var typeId = $('#registration-registration_type_id').val();
        var typeCode = 'RU'; 
        if (typeId === '1') {
            typeCode = 'RG';
        } else if (typeId === '12') {
            typeCode = 'RE';
        }

        var finalCode = lastNameCode + firstNameCode + typeCode;
        $('#display-concepto-pago').text(finalCode);
    }

	calculateConceptoPago();

    $('#registration-first_name, #registration-last_name').on('keyup change', function() {
        calculateConceptoPago();
    });
    // ------------------------------------------

    // NUEVA LÓGICA PARA ACTUALIZAR CONCEPTO
function actualizarConceptoPago() {
    let apellido = $('#registration-last_name').val();
    let nombre = $('#registration-first_name').val();
    
    // Si están vacíos, no fallará
    apellido = (apellido ? apellido.trim().toUpperCase().substring(0, 3) : '').padStart(3, '0');
    nombre = (nombre ? nombre.trim().toUpperCase().substring(0, 3) : '').padStart(3, '0');

    let tipoId = $('#registration-registration_type_id').val();
    let tipoStr = 'RU'; // UADY
    if (tipoId == '1') tipoStr = 'RG'; //General
    else if (tipoId == '12') tipoStr = 'RE'; //Estudiante

    let concepto = apellido + nombre + tipoStr;

    // Talleres
    let talleres = [];
    $('input[name="talleres_seleccionados[]"]:checked').each(function() {
        talleres.push(parseInt($(this).val()));
    });
    talleres.sort((a, b) => a - b);
    talleres.forEach(function(id) {
        concepto += 'T' + id.toString().padStart(2, '0'); 
    });

    // Visitas
    let visitas = [];
    $('input[name="visitas_seleccionadas[]"]:checked').each(function() {
        visitas.push(parseInt($(this).val()));
    });
    visitas.sort((a, b) => a - b);
    visitas.forEach(function(id) {
        concepto += 'V' + id.toString().padStart(2, '0');
    });

    $('#display-concepto-pago').text(concepto);
}

// Ejecutar los eventos cuando la página haya cargado
$(document).ready(function() {
    // Detectar cuando el usuario escribe su nombre o apellido
    $('#registration-first_name, #registration-last_name').on('input', function() {
        actualizarConceptoPago();
    });

    // Detectar cuando el usuario marca o desmarca un taller o visita
    $('body').on('change', 'input[name="talleres_seleccionados[]"], input[name="visitas_seleccionadas[]"]', function() {
        actualizarConceptoPago();
    });
});
    
    function calculateTotal() {
        var total = 0;
        var baseCost = 0;
        var extrasTotalCost = 0; // Costo de los talleres/visitas que SÍ se cobran
        
        // 1. Obtener ID del tipo de registro
        var selectedTypeId = $('#registration-registration_type_id').val();
        
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
        const talleresContainer = document.getElementById('checkbox-talleres-container');
		const visitasContainer = document.getElementById('checkbox-visitas-container');

		const selectedTalleresCount = talleresContainer ? talleresContainer.querySelectorAll('.rcg-checkbox:checked').length : 0;
		const selectedVisitasCount = visitasContainer ? visitasContainer.querySelectorAll('.rcg-checkbox:checked').length : 0;
        var totalExtrasCount = selectedTalleresCount + selectedVisitasCount;
        
        // 3. Lógica de Cobro de Extras (Talleres + Visitas)
        var paidExtras = 0;
        var typeStr = String(selectedTypeId); 
        
        if (totalExtrasCount > 0) {
            if (typeStr === '1' || typeStr === '12') {
                // General (1) y Estudiante (12): 1 Gratis en total (sea taller o visita)
                paidExtras = Math.max(0, totalExtrasCount - 1);
            } else if (typeStr === '17') {
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
        $('#display-base-cost').text('$' + baseCost.toFixed(2));
        
        // Fila Talleres
        $('#display-talleres-count').text(selectedTalleresCount);
        $('#display-talleres-total').text('$' + (selectedTalleresCount * workshopCost).toFixed(2));
        
        // Fila Visitas
        $('#display-visitas-count').text(selectedVisitasCount);
        $('#display-visitas-total').text('$' + (selectedVisitasCount * workshopCost).toFixed(2));
        
        // Fila Resumen Extras a Pagar
        $('#display-total-extras-paid').text(paidExtras);
        $('#display-extras-total').text('$' + extrasTotalCost.toFixed(2));
        
        // Total Final
        $('#display-grand-total').text('$' + total.toFixed(2));
    }

    // -- LISTENERS --

    // Cambio en Tipo de Registro (Grid Radio de Kartik)

    $('#fee_type').on('grid.radiochecked', function(ev, key, val) {
        $('#registration-registration_type_id').val(val);
        
        // Funciones visuales existentes (si existen)
        if(typeof toggleStudentId === 'function') toggleStudentId();
        if(typeof toggleChangeFileStudentId === 'function') toggleChangeFileStudentId();
        
        calculateTotal();
		calculateConceptoPago();
    });

    // Cambio en Checkbox de Talleres
    $('#grid-talleres, #grid-visitas').on('click', function() {
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

    var $grid = $('#fee_type'); // your registration grid identifier
    $grid.on( 'grid.radiochecked', function(ev, key, val){
        calculateConceptoPago();
    });

	// Inicializar
    $(document).ready(function() {
        calculateTotal();
    });

    function showFileStudentId()
	{
		$("[name='Registration[file_student_id]']").removeAttr("disabled");
		$(".field-registration-file_student_id").show();
	}
	
	function hideFileStudentId()
	{
		$("[name='Registration[file_student_id]']").attr("disabled","disabled");
		$(".field-registration-file_student_id").hide();
	}
	
	function showFilePaymentReceipt()
	{
		$("[name='Registration[file_payment_receipt]']").removeAttr("disabled");
		$(".field-registration-file_payment_receipt").show();
	}
	
	function hideFilePaymentReceipt()
	{
		$("[name='Registration[file_payment_receipt]']").attr("disabled","disabled");
		$(".field-registration-file_payment_receipt").hide();
	}

	function showRegistrationCode()
	{
		$("[name='Registration[registration_code]']").removeAttr("disabled");
		$(".field-registration-registration_code").show();
	}
	
	function hideRegistrationCode()
	{
		$("[name='Registration[registration_code]']").attr("disabled","disabled");
		$(".field-registration-registration_code").hide();
	}

	// New code. Rodrigo
	function toggleFilePaymentReceipt()
	{
		var paymentType = $("[name='Registration[payment_type]']:checked").val();
		if( paymentType == "2" )
			showFilePaymentReceipt();
		else
			hideFilePaymentReceipt();
		
	}

	function toggleRegistrationCode()
	{
		if( $("[name='Registration[payment_type]']:checked").val() == 3 ){
			showRegistrationCode();
		}else{
			hideRegistrationCode();
		}
	}
		
	function toggleStudentId()
	{
		//alert("Hola");
		//var registrationType = $("[name=\'Registration[registration_type_id]\']:checked").val();
		var registrationType2 = $("[name='Registration[registration_type_id]']").val();
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
		var registrationType = $("[name='Registration[registration_type_id]']").val();
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
		if( $("[name='Registration[invoice_required]']:checked").val() == "0" )
		{
			$("[name='Invoice[business_name]']").attr("disabled","disabled");
			$(".field-invoice-business_name").hide();
			$("[name='Invoice[rfc]']").attr("disabled","disabled");
			$(".field-invoice-rfc").hide();
			$("[name='Invoice[address]']").attr("disabled","disabled");
			$(".field-invoice-address").hide();
			$("[name='Invoice[zip_code]']").attr("disabled","disabled");
			$(".field-invoice-zip_code").hide();
			$("[name='Invoice[city]']").attr("disabled","disabled");
			$(".field-invoice-city").hide();
			$("[name='Invoice[state]']").attr("disabled","disabled");
			$(".field-invoice-state").hide();
			$("[name='Invoice[email]']").attr("disabled","disabled");
			$(".field-invoice-email").hide();
		}
		else
		{
			$("[name='Invoice[business_name]']").removeAttr("disabled");
			$(".field-invoice-business_name").show();
			$("[name='Invoice[rfc]']").removeAttr("disabled");
			$(".field-invoice-rfc").show();
			$("[name='Invoice[address]']").removeAttr("disabled");
			$(".field-invoice-address").show();
			$("[name='Invoice[zip_code]']").removeAttr("disabled");
			$(".field-invoice-zip_code").show();
			$("[name='Invoice[city]']").removeAttr("disabled");
			$(".field-invoice-city").show();
			$("[name='Invoice[state]']").removeAttr("disabled");
			$(".field-invoice-state").show();
			$("[name='Invoice[email]']").removeAttr("disabled");
			$(".field-invoice-email").show();
		}
	} // end of toogleInvoice()
	
	
	$("[name='Registration[registration_type_id]']").change(function(){
		toggleStudentId();
	});
	
	
	$("[name='Registration[invoice_required]']").change(function (){
		toggleInvoice();
	});
	
	$("[name='Registration[payment_type]']").change(function(){
		toggleFilePaymentReceipt();
		toggleRegistrationCode();
	});
	
		
	toggleStudentId();
	toggleFilePaymentReceipt();
	toggleInvoice();
	toggleModalidadPresentacion();
	toggleRegistrationCode();


	var $grid = $('#fee_type'); // your registration grid identifier

	$("input[name=kvradio][value='1']").prop("checked",true);

	$grid.on( 'grid.radiochecked', function(ev, key, val){
		$("#registration-registration_type_id").val(val);

	actualizarConceptoPago();
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
});
