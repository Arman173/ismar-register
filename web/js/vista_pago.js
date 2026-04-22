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

	/*// --- NUEVO CODIGO: FUNCION DEL CONCEPTO ---
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
    */

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
});
