/*
    Este archivo se encargará de reunir toda la logica de JavaScript para
    el formulario del ConCEI
*/

function verificarCuposDinamicos() {
    console.log("Verificando cupos dinámicos...");
    // Buscamos el radio seleccionado. Si Yii2 no ha marcado nada, asumimos '1' (General)
    const selectedRadio = document.querySelector('input[name="kvradio"]:checked');
    const tipoId = selectedRadio ? selectedRadio.value : '1'; 
    const esGeneral = (tipoId === '1');

    function actualizarFilas(datos, inputName, spanClass) {
        if (!datos) return;
        //Comienza a revisar la lista de talleres/visitas, uno por uno.
        datos.forEach(function(item) {
            const tieneCupo = esGeneral ? item.cupo_general : item.cupo_otros;
            
            const checkbox = $('input[name="' + inputName + '"][value="' + item.id + '"]');
            const fila = checkbox.closest('tr');
            const spanStatus = $('.' + spanClass + '[data-id="' + item.id + '"]');
            
            if (spanStatus.length > 0) {
                if (!tieneCupo) {
                    checkbox.prop('disabled', true).prop('checked', false);
                    fila.css('opacity', '0.5');
                    spanStatus.html('<span style="color:red;font-weight:bold;">Agotado</span>');
                } else {
                    checkbox.prop('disabled', false);
                    fila.css('opacity', '1');
                    spanStatus.html('<span style="color:green;font-weight:bold;">Disponible</span>');
                }
            }
        });
    }

    actualizarFilas(window.datosTalleres, 'talleres_seleccionados[]', 'status-cupo-taller');
    actualizarFilas(window.datosVisitas, 'visitas_seleccionadas[]', 'status-cupo-visita');
    
    if (typeof calculateTotal === 'function') calculateTotal(); 
}

// funcion que se ejecuta cada que el radio button del tipo de registro cambia
function cambioTipoRegistro(event) {
    if (event.target && event.target.name === 'kvradio') {
        const valorSeleccionado = event.target.value;
        $("#registration-registration_type_id").val(valorSeleccionado);
        console.log("ID del tipo de registro seleccionado:", valorSeleccionado);

        const fila = event.target.closest('tr'); 

        const nombreRegistro = fila.cells[1].innerText; 
        
        console.log("Nombre del registro:", nombreRegistro);

        switch( valorSeleccionado )
        {
            case "12": showFileStudentId(); break;
            case "17": showFileStudentId(); break;
            default: hideFileStudentId(); break;
        }
        calculateTotal();
        toggleModalidadPresentacion();
        verificarCuposDinamicos(); // Nuevo
        toggleRegistrationCode();

    }
}

// Función para mostrar el modal de talleres y visitas ----------------------------------------------------------------------
// de parametreos se necesita el titulo, detalles y talleristas (este ultimo es opcional)
function mostrarModalDetalles(titulo, detalles, tallerista = null){

    detalles = detalles || "";

    let urlRegex = /(https?:\/\/[^\s<]+)/g;
    let links = detalles.match(urlRegex);
    let urlEncontrada = links ? links[0] : null;

    let textoLimpio = detalles.replace(/Para m[aá]s informaci[oó]n[\s\S]*/gi, "");
    textoLimpio = textoLimpio.replace(/\n/g, "<br>");
    textoLimpio = textoLimpio.replace(/(Descripci[oó]n:)\s*/gi, "<strong class=\"text-primary\" style=\"font-size: 1.1em;\">$1</strong> ");
    textoLimpio = textoLimpio.replace(/(Requisitos?:)\s*/gi, "<br><br><strong class=\"text-danger\" style=\"font-size: 1.1em;\">$1</strong><br>");

    let htmlFinal = "<div style=\"text-align: justify; font-size: 1.05em; line-height: 1.6; color: #444;\">";

   if (tallerista != null) {

        htmlFinal += "<strong class=\"text-primary\" style=\"font-size: 1.1em;\">Tallerista/as:</strong><br>";
        htmlFinal += tallerista + "<br><br>";
    }

    htmlFinal +=textoLimpio + "</div>";

  if (urlEncontrada) {

        htmlFinal += "<hr style=\"margin-top: 15px; margin-bottom: 15px; border-top: 1px solid #ddd;\">";
        htmlFinal += "<div>";
        htmlFinal += "  <p style=\"margin-bottom: 5px; color: #555; font-size: 1.05em;\">Para más información visita la página del <strong>CONCEI 2026</strong>:</p>";
        htmlFinal += "  <a href=\"" + urlEncontrada + "\" target=\"_blank\" style=\"word-break: break-all; font-weight: bold; font-size: 1.05em;\">" + urlEncontrada + "</a>";
        htmlFinal += "</div>";

    }

    $("#modal-title-text").html(titulo);

    $("#modal-body-text").html(htmlFinal);

    $("#modal-detalles").modal("show");

}

function calculateConceptoPago() {
    var lastName = $('#registration-last_name').val() ? $('#registration-last_name').val().trim().toUpperCase() : '';
    var firstName = $('#registration-first_name').val() ? $('#registration-first_name').val().trim().toUpperCase() : '';

    var lastNameCode = lastName.substring(0, 3).padStart(3, '0');
    var firstNameCode = firstName.substring(0, 3).padStart(3, '0');

    let typeCode = '';

    console.log(window.es_nuevo_registro);
    if (window.es_nuevo_registro) {

        const typeId = $('#registration-registration_type_id').val();
        if (typeId === '1') {
            typeCode = 'RG';
        } else if (typeId === '12') {
            typeCode = 'RE';
        } else if (typeId === '17') {
            typeCode = 'RU'
        } else {
            typeCode = '';
        }
    }

    const finalCode = lastNameCode + firstNameCode + typeCode;
    console.log(finalCode);
    // $('#display-concepto-pago').text(finalCode);
}

// NUEVA LÓGICA PARA ACTUALIZAR CONCEPTO
function actualizarConceptoPago() {
    let nombre = $('#registration-first_name').val();
    let apellido = $('#registration-last_name').val();
    
    // Si están vacíos, no fallará
    apellido = (apellido ? apellido.trim().toUpperCase().substring(0, 3) : '').padStart(3, '0');
    nombre = (nombre ? nombre.trim().toUpperCase().substring(0, 3) : '').padStart(3, '0');

    let tipoStr = '';
    if (window.es_nuevo_registro) {
        const tipoId = $('#registration-registration_type_id').val();
        tipoStr = 'RU'; // UADY
        if (tipoId == '1') tipoStr = 'RG'; //General
        else if (tipoId == '12') tipoStr = 'RE'; //Estudiante
    }

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

function calculateTotal() {
    const preciosRegistros = window.typePrices;
    
    var total = 0;
    var baseCost = 0;
    var extrasTotalCost = 0; 
    const workshopCost = parseFloat(window.costo_taller);
    
    // Obtener ID del tipo de registro (más seguro)
    const radioChecked = document.querySelector('input[name="kvradio"]:checked');
    const selectedTypeId = radioChecked ? radioChecked.value : $("#registration-registration_type_id").val();
    
    // MODIFICACIÓN: Si NO es un nuevo registro, el costo base es $0
    if (window.es_nuevo_registro) {
        // Costo base según Early Bird
        if (selectedTypeId && preciosRegistros[selectedTypeId]) {
            if (window.preventa) {
                baseCost = parseFloat(preciosRegistros[selectedTypeId].early) || 0;
            } else {
                baseCost = parseFloat(preciosRegistros[selectedTypeId].late) || 0;
            }
        } else {
            baseCost = 0;
        }
    } else {
        // ES ACTUALIZAR: El ticket base ya se pagó, el costo a sumar es 0
        baseCost = 0;
    }

    // Contar de AMBAS tablas (solo cuenta los checkboxes marcados, es decir, lo NUEVO)
    const talleresContainer = document.getElementById('checkbox-talleres-container');
    const visitasContainer = document.getElementById('checkbox-visitas-container');

    const selectedTalleresCount = talleresContainer ? talleresContainer.querySelectorAll('.rcg-checkbox:checked').length : 0;
    const selectedVisitasCount = visitasContainer ? visitasContainer.querySelectorAll('.rcg-checkbox:checked').length : 0;
    var totalExtrasCount = selectedTalleresCount + selectedVisitasCount;
    
    // Lógica de Cobro de Extras (Talleres + Visitas)
    var paidExtras = 0;
    var typeStr = String(selectedTypeId);
    
    if (totalExtrasCount > 0) {
        if (window.es_nuevo_registro) {
            if (typeStr === '1' || typeStr === '12' || typeStr === '18') {
                paidExtras = Math.max(0, totalExtrasCount - 1);
            } else {
                // UADY u otros: Paga todo extra
                paidExtras = totalExtrasCount; 
            }
        } else {
            paidExtras = totalExtrasCount;
        }
    }
    
    extrasTotalCost = paidExtras * workshopCost;
    total = baseCost + extrasTotalCost;

    console.log(total);
    // if (total > 0) {
    //     console.log("si hay que pagar!");
    //     $("input[name='Registration[payment_type]']:checked").show();
    // } else {
    //     $("input[name='Registration[payment_type]']:checked").hide()
    // }

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
    
    actualizarConceptoPago();
    toggleFilePaymentReceipt();
}

/* FUNCION PARA ABRIR Y CERRAR MODALS
    se usa para inicializar los eventos de abrir y cerrar de los
    modals que muestran a los checkboxes de los tallers y visitas
 */
function inicializarModals() {
    // Abrir Modal
    const botonesAbrir = document.querySelectorAll('.btn-abrir-modal-fs');
    
    botonesAbrir.forEach(function(boton) {
        boton.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const modal = document.querySelector(targetId);
            
            if (modal) {
                modal.classList.remove('oculto');
                document.body.style.overflow = 'hidden'; // Evita el scroll del fondo
            }
        });
    });

    // Cerrar Modal
    const botonesCerrar = document.querySelectorAll('.btn-cerrar-modal-fs');
    
    botonesCerrar.forEach(function(boton) {
        boton.addEventListener('click', function() {
            // Busca el contenedor padre del modal actual
            const modal = this.closest('.modal-fs-container');
            
            if (modal) {
                modal.classList.add('oculto');
                document.body.style.overflow = ''; // Regresa el scroll al form principal
            }
        });
    });
}

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
    console.log($(".field-registration-registration_code"))
    $("[name='Registration[registration_code]']").attr("disabled","disabled");
    $(".field-registration-registration_code").hide();
}

function toggleFilePaymentReceipt() {
    var paymentType = $("input[name='Registration[payment_type]']:checked").val();
    
    var radioChecked = document.querySelector('input[name="kvradio"]:checked');
    var selectedTypeId = radioChecked ? radioChecked.value : $("#registration-registration_type_id").val();
    
    var tText = document.getElementById('contador-talleres') ? document.getElementById('contador-talleres').textContent : "0";
    var vText = document.getElementById('contador-visitas') ? document.getElementById('contador-visitas').textContent : "0";
    
    var tCount = parseInt(tText) || 0;
    var vCount = parseInt(vText) || 0;
    var totalExtrasCount = tCount + vCount;

    let requierePago = false;

    if (window.es_nuevo_registro) {
        if (selectedTypeId == "1" || selectedTypeId == "12") {
            requierePago = true;
        } else if (selectedTypeId == "18") {
            requierePago = totalExtrasCount > 1;
        } else if (selectedTypeId == "17") {
            requierePago = totalExtrasCount > 0; 
        }
    } else {
        // CORRECCIÓN: Si agregó extras O si seleccionó Transferencia bancaria (2)
        if (totalExtrasCount > 0 || paymentType == "2") {
            requierePago = true;
        }
    }

    if (requierePago) {
        $('#instrucciones-pago').show();
        $('.field-registration-payment_type').show();
        
        if (paymentType == "2") {
            showFilePaymentReceipt();
        } else {
            hideFilePaymentReceipt();
        }
    } else {
        $('#instrucciones-pago').hide();
        $('.field-registration-payment_type').hide();
        
        // CORRECCIÓN: Solo desmarcamos el radio button si es un registro nuevo.
        if (window.es_nuevo_registro) {
            $("input[name='Registration[payment_type]']").prop('checked', false); 
        }
        hideFilePaymentReceipt();
    }
}

/*
// New code. Rodrigo
function toggleFilePaymentReceipt()
{
    var paymentType = $("input[name='Registration[payment_type]']:checked").val();
    
    var radioChecked = document.querySelector('input[name="kvradio"]:checked');
    var selectedTypeId = radioChecked ? radioChecked.value : $("#registration-registration_type_id").val();
    
    var tText = document.getElementById('contador-talleres') ? document.getElementById('contador-talleres').textContent : "0";
    var vText = document.getElementById('contador-visitas') ? document.getElementById('contador-visitas').textContent : "0";
    
    var tCount = parseInt(tText) || 0;
    var vCount = parseInt(vText) || 0;
    var totalExtrasCount = tCount + vCount;

    let requierePago = false;

    if (window.es_nuevo_registro) {
        if (selectedTypeId == "1" || selectedTypeId == "12") {
            requierePago = true;
        } else if (selectedTypeId == "18") {
            requierePago = totalExtrasCount > 1;
        } else if (selectedTypeId == "17") {
            requierePago = totalExtrasCount > 0; 
        }
    } else {
        requierePago = totalExtrasCount > 0;
    }

    if (requierePago) {
        $('#instrucciones-pago').show();
        $('.field-registration-payment_type').show();
        
        if (paymentType == "2") {
            showFilePaymentReceipt();
        } else {
            hideFilePaymentReceipt();
        }
    } else {
        $('#instrucciones-pago').hide();
        $('.field-registration-payment_type').hide();
        
        $("input[name='Registration[payment_type]']").prop('checked', false); 
        hideFilePaymentReceipt();
    }
}*/


function toggleRegistrationCode()
{
    const selectedTypeId = $("[name='Registration[registration_type_id]']").val();
    // console.log(selectedTypeId.value);
    if( selectedTypeId == "18" ){
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

// document.addEventListener('DOMContentLoaded', (event) => {
//     console.log('El DOM está listo');

//     // Variables PHP inyectadas
//     /*var workshopCost = 100;
//     var isEarlyBird = false;
//     const registrationPrices = {
//         "1": {
//             "early": 50,
//             "late": 80,
//             "name": "Student"
//         },
//         "2": {
//             "early": 100,
//             "late": 150,
//             "name": "Professional"
//         },
//         "3": {
//             "early": 70,
//             "late": 120,
//             "name": "Online"
//         }
//     };

// 	calculateConceptoPago();*/

//     // $('#registration-first_name, #registration-last_name').on('keyup change', function() {
//     //     calculateConceptoPago();
//     // });
//     // ------------------------------------------



// // Ejecutar los eventos cuando la página haya cargado
// // $(document).ready(function() {
// //     // Detectar cuando el usuario escribe su nombre o apellido
// //     $('#registration-first_name, #registration-last_name').on('input', function() {
// //         actualizarConceptoPago();
// //     });

// //     // Detectar cuando el usuario marca o desmarca un taller o visita
// //     $('body').on('change', 'input[name="talleres_seleccionados[]"], input[name="visitas_seleccionadas[]"]', function() {
// //         actualizarConceptoPago();
// //         calculateTotal(); // <--- ¡AQUÍ ESTÁ LA MAGIA!

// //     });
// // });
    

//     // -- LISTENERS --

//     // Cambio en Tipo de Registro (Grid Radio de Kartik)

//     $('#fee_type').on('grid.radiochecked', function(ev, key, val) {
//         $('#registration-registration_type_id').val(val);
        
//         // Funciones visuales existentes (si existen)
//         if(typeof toggleStudentId === 'function') toggleStudentId();
//         if(typeof toggleChangeFileStudentId === 'function') toggleChangeFileStudentId();
        
//         calculateTotal();
// 		calculateConceptoPago();
//         toggleRegistrationCode();
//     });

//     // Cambio en Checkbox de Talleres
//     $('#grid-talleres, #grid-visitas').on('click', function() {
//         calculateTotal();
//     });

//     var $grid = $('#fee_type'); // your registration grid identifier
//     $grid.on( 'grid.radiochecked', function(ev, key, val){
//         calculateConceptoPago();
//         toggleRegistrationCode();
//         console.log("...")
//     });

// 	// Inicializar
//     $(document).ready(function() {
//         calculateTotal();
//     });
	
	
// 	$("[name='Registration[registration_type_id]']").change(function(){
// 		toggleStudentId();
// 	});
	
	
// 	$("[name='Registration[invoice_required]']").change(function (){
// 		toggleInvoice();
// 	});
	
// 	$("[name='Registration[payment_type]']").change(function(){
// 		toggleFilePaymentReceipt();
// 		// toggleRegistrationCode();
// 	});
	
		
// 	toggleStudentId();
// 	toggleFilePaymentReceipt();
// 	toggleInvoice();
// 	toggleModalidadPresentacion();
// 	toggleRegistrationCode();


// 	var $grid = $('#fee_type'); // your registration grid identifier

// 	$("input[name=kvradio][value='1']").prop("checked",true);

// 	$grid.on( 'grid.radiochecked', function(ev, key, val){
//             $("#registration-registration_type_id").val(val);

//             toggleRegistrationCode();
//             // console.log("...")

//             actualizarConceptoPago();
//                 switch( val )
//                 {
//                     case "12": showFileStudentId(); break;
//                     case "17": showFileStudentId(); break;
//                     default: hideFileStudentId(); break;
//                 }
//             toggleModalidadPresentacion();
// 		}
// 	);
// });

window.addEventListener('load', function() {
    console.log('DOM cargado...');

    // Cargamos los datos de talleres y visitas inyectados por PHP en
    // variables globales del window.
    const talleres = window.datosTalleres;
    const visitas = window.datosVisitas;
    
    const feeTypeGrid = document.getElementById('fee_type');
    console.log("feeTypeGrid:", feeTypeGrid);
    // elemento html del modal de detalles de talleres y visitas
    const modal = document.getElementById('modal-detalles');
    // elemento html de contadores de seleccion de talleres y visitas
    const contadorTalleres = document.getElementById('contador-talleres');
    const contadorVisitas = document.getElementById('contador-visitas');

    const firstName = document.querySelector('#registration-first_name');
    const lastName = document.querySelector('#registration-last_name');

    firstName.addEventListener('input', (e) => {
        actualizarConceptoPago();
    });

    lastName.addEventListener('input', (e) => {
        actualizarConceptoPago();
    });

    if (!talleres || !visitas) {
        console.error("No se encontraron los datos de talleres o visitas. Asegúrate de que estén definidos en el servidor.");
        return;
    }

    console.log("Datos de talleres:", talleres);
    console.log("Datos de visitas:", visitas);
    console.log("Es preventa:", window.preventa);

    feeTypeGrid.addEventListener('change', cambioTipoRegistro);

    new ResponsiveCheckboxGrid({
            containerId: 'checkbox-talleres-container',
            inputName: 'talleres_seleccionados[]',
            data: window.datosTalleres,
            columns: [
                { attribute: 'nombre', label: 'Nombre del Taller' },
                { 
                    attribute: 'descripcion', 
                    label: 'Descripción',
                    actionName: 'ver_mas', 
                    format: function(model) {
                        return `<button type='button' class='btn btn-info btn-xs rcg-action-btn' data-action='ver_mas'>
                                    <span class='glyphicon glyphicon-info-sign'></span> Leer más
                                </button>`;
                    },
                    onAction: function(model) {
                        //Modificado
                        mostrarModalDetalles(model.nombre, model.descripcion, model.tallerista);
                    }
                },
                { attribute: 'modalidad', label: 'Modalidad' },
                { attribute: 'horario', label: 'Horario' },
                { 
                    attribute: 'disponible', 
                    label: 'Disponibilidad',
                    format: function(model) {
                        return '<span class="status-cupo-taller" data-id="' + model.id + '"></span>';
                    }
                }
            ],
            onSelectionChange: function(estado) {
                console.log('Seleccionados:', estado.todosLosSeleccionados);
                contadorTalleres.textContent = estado.todosLosSeleccionados.length;
                calculateTotal();
            }
        });
    
    new ResponsiveCheckboxGrid({
            containerId: 'checkbox-visitas-container',
            inputName: 'visitas_seleccionadas[]',
            data: window.datosVisitas,
            columns: [
                { attribute: 'nombre', label: 'Nombre de la Empresa' },
                { attribute: 'fecha', label: 'Fecha' },
                //{ attribute: 'modalidad', label: 'Modalidad' },
                { attribute: 'horario', label: 'Horario' },
                {
                    attribute: 'descripcion', 
                    label: 'Requisitos',
                    actionName: 'ver_mas', // Identificador para el evento click
                    format: function(model) {
                        return `<button type='button' class='btn btn-info btn-xs rcg-action-btn' data-action='ver_mas'>
                                    <span class='glyphicon glyphicon-info-sign'></span> Leer más
                                </button>`;
                    },
                    onAction: function(model) {
                        mostrarModalDetalles(model.nombre, model.descripcion, null);
                    }

                },

                { 
                    attribute: 'disponible', 
                    label: 'Disponibilidad',
                    format: function(model) {
                        return '<span class="status-cupo-visita" data-id="' + model.id + '"></span>';
                    }
               
                }
            ],
        
            onSelectionChange: function(estado) {
                console.log('Seleccionados:', estado.todosLosSeleccionados);
                contadorVisitas.textContent = estado.todosLosSeleccionados.length;
                calculateTotal();
            }
        });

    $("[name='Registration[invoice_required]']").change(function (){
		toggleInvoice();
	});



    // $("input[name=kvradio][value='1']").prop("checked",true);
    // $grid.on( 'grid.radiochecked', function(ev, key, val){
    //     $("#registration-registration_type_id").val(val);

    //     toggleRegistrationCode();
    //     // console.log("...")

    //     actualizarConceptoPago();
    //         switch( val )
    //         {
    //             case "12": showFileStudentId(); break;
    //             case "17": showFileStudentId(); break;
    //             default: hideFileStudentId(); break;
    //         }
    //     toggleModalidadPresentacion();
    // });

    inicializarModals();
    toggleStudentId();
    toggleModalidadPresentacion();
    toggleInvoice();
    toggleRegistrationCode();
    verificarCuposDinamicos();

    // Detectar cuando el usuario cambia el método de pago
    $("input[name='Registration[payment_type]']").change(function() {
        toggleFilePaymentReceipt();
    });
    
    // Ejecutar la función una vez al cargar la página para revisar el estado inicial
    toggleFilePaymentReceipt();
});