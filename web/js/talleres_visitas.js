
function mostrarModalDetalles(titulo, detalles, tallerista = null){
    detalles = detalles || "";
    // tallerista = tallerista || "";

    let urlRegex = /(https?:\/\/[^\s<]+)/g;
    let links = detalles.match(urlRegex);
    let urlEncontrada = links ? links[0] : null;

    let textoLimpio = detalles.replace(/Para m[aá]s informaci[oó]n[\s\S]*/gi, "");
    textoLimpio = textoLimpio.replace(/\n/g, "<br>");
    textoLimpio = textoLimpio.replace(/(Descripci[oó]n:)\s*/gi, "<strong class=\"text-primary\" style=\"font-size: 1.1em;\">$1</strong> ");
    textoLimpio = textoLimpio.replace(/(Requisitos?:)\s*/gi, "<br><br><strong class=\"text-danger\" style=\"font-size: 1.1em;\">$1</strong><br>");

    let htmlFinal = "<div style=\"text-align: justify; font-size: 1.05em; line-height: 1.6; color: #444;\">";

   if (tallerista != null) {
        htmlFinal += "<strong class=\"text-primary\" style=\"font-size: 1.1em;\">Tallerista:</strong><br>";
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

window.onload = function() {
    console.log("pagina cargada...");

    const talleres = window.datosTalleres;
    const datosTalleres = [];
    const visitas = window.datosVisitas;
    const datosVisitas = [];
    const modal = document.getElementById('modal-detalles');

    if (!talleres || !visitas) {
        console.error("No se encontraron los datos de talleres o visitas. Asegúrate de que estén definidos en el servidor.");
        return;
    }

    console.log("Datos de talleres:", talleres);
    console.log("Datos de visitas:", visitas);

    new ResponsiveCheckboxGrid({
            containerId: 'checkbox-talleres-container',
            inputName: 'talleres_seleccionados[]',
            data: window.datosTalleres,
            columns: [
                { attribute: 'nombre', label: 'Nombre del Taller' },
                { 
                    attribute: 'descripcion', 
                    label: 'Descripción',
                    actionName: 'ver_mas', // Identificador para el evento click
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
                { attribute: 'horario', label: 'Horario' }
            ],
            onSelectionChange: function(estado) {
                console.log('Seleccionados:', estado.todosLosSeleccionados);
                // Aquí puedes validar si el usuario ya excedió el límite de talleres, etc.
            }
        });
    
    new ResponsiveCheckboxGrid({
            containerId: 'checkbox-visitas-container',
            inputName: 'visitas_seleccionadas[]',
            data: window.datosVisitas,
            columns: [
                { attribute: 'nombre', label: 'Nombre de la Empresa' },
                {
                    attribute: 'descripcion', 
                    label: 'Información',
                    actionName: 'ver_mas', // Identificador para el evento click
                    format: function(model) {
                        return `<button type='button' class='btn btn-info btn-xs rcg-action-btn' data-action='ver_mas'>
                                    <span class='glyphicon glyphicon-info-sign'></span> Leer más
                                </button>`;
                    },
                    onAction: function(model) {
                        //Modificadoooooo puede fallaaar
                        mostrarModalDetalles(model.nombre, model.descripcion, null);
                    }
                },
                //{ attribute: 'modalidad', label: 'Modalidad' },
                { attribute: 'horario', label: 'Horario' }
            ],
            onSelectionChange: function(estado) {
                console.log('Seleccionados:', estado.todosLosSeleccionados);
                // Aquí puedes validar si el usuario ya excedió el límite de visitas (si aplica), etc.
            }
        });

}