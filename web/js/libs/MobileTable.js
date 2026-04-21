/**
 * Genera el layout móvil para cualquier tabla de selección.
 * @param {Object} config - Configuración de la tabla.
 */
function renderMobileTable(config) {
    const { 
        containerId, 
        titulo, 
        datos, 
        inputName = "seleccionados[]",
        onSelectionChange 
    } = config;

    const container = document.getElementById(containerId);
    if (!container) return;

    let eventBindings = [];
    let html = `
        <div class="mobile-grid-header">
            <h3 class="mobile-grid-title">${titulo}</h3>
        </div>
        <div class="mobile-cards-wrapper">
    `;

    datos.forEach(fila => {
        let metaHtml = '';
        
        if (fila.metaDatos && fila.metaDatos.length > 0) {
            fila.metaDatos.forEach((meta, index) => {
                const tipo = meta.tipo || 'texto';

                if (tipo === 'texto') {
                    metaHtml += `
                        <div class="mobile-card-meta-item">
                            <span class="meta-label">${meta.etiqueta}:</span>
                            <span class="meta-value">${meta.valor}</span>
                        </div>
                    `;
                } 
                else if (tipo === 'boton') {
                    const btnId = `din-btn-${fila.id}-${index}`;
                    const cssClasses = meta.claseBoton || 'btn btn-info btn-xs';
                    
                    // Si no se le pasa etiqueta, asumimos que es un botón de ancho completo (como el "Leer más")
                    const hasEtiqueta = meta.etiqueta && meta.etiqueta.trim() !== '';

                    if (hasEtiqueta) {
                        metaHtml += `
                            <div class="mobile-card-meta-item" style="align-items: center;">
                                <span class="meta-label">${meta.etiqueta}:</span>
                                <span class="meta-value">
                                    <button type="button" id="${btnId}" class="${cssClasses}">
                                        ${meta.textoBoton}
                                    </button>
                                </span>
                            </div>
                        `;
                    } else {
                        // Botón de bloque sin etiqueta al lado
                        metaHtml += `
                            <div class="mobile-card-meta-item meta-item-block" style="margin-top: 10px; display: block;">
                                <button type="button" id="${btnId}" class="${cssClasses} btn-block">
                                    ${meta.textoBoton}
                                </button>
                            </div>
                        `;
                    }

                    eventBindings.push({
                        btnId: btnId,
                        callback: meta.callback,
                        datosFila: fila
                    });
                }
            });
        }

        // Ensamblamos la tarjeta (Nota: El botón fijo de "Leer más" ya fue eliminado)
        html += `
            <div class="mobile-workshop-card">
                <div class="mobile-card-checkbox">
                    <input type="checkbox" 
                           id="mob-chk-${fila.id}" 
                           class="mobile-sync-checkbox" 
                           data-target-value="${fila.id}"
                           name="${inputName}_mobile">
                </div>
                
                <div class="mobile-card-content">
                    <label for="mob-chk-${fila.id}" class="mobile-card-title">
                        ${fila.tituloPrincipal}
                    </label>
                    
                    <div class="mobile-card-meta-container">
                        ${metaHtml}
                    </div>
                </div>
            </div>
        `;
    });

    html += `</div>`;
    container.innerHTML = html;

    // Enganchar eventos a los botones dinámicos
    eventBindings.forEach(binding => {
        const botonElement = document.getElementById(binding.btnId);
        if (botonElement && typeof binding.callback === 'function') {
            botonElement.addEventListener('click', (e) => {
                e.stopPropagation(); 
                binding.callback(binding.datosFila);
            });
        }
    });

    // Enganchar eventos a los checkboxes para la sincronización
    const checkboxes = container.querySelectorAll('.mobile-sync-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', (event) => {
            const tarjeta = event.target.closest('.mobile-workshop-card');
            if (tarjeta) {
                tarjeta.classList.toggle('is-selected', event.target.checked);
            }

            if (typeof onSelectionChange === 'function') {
                const seleccionadosActualmente = Array.from(checkboxes)
                    .filter(chk => chk.checked)
                    .map(chk => chk.getAttribute('data-target-value'));

                onSelectionChange({
                    idModificado: event.target.getAttribute('data-target-value'),
                    estaSeleccionado: event.target.checked,
                    todosLosSeleccionados: seleccionadosActualmente
                });
            }
        });
    });
}


/* Ejemplo de formato de un dato

    const datosTalleres = [
        {
            id: "3",
            tituloPrincipal: "Desarrollo de proyectos de innovación con inteligencia artificial",
            detalles: "Taller virtual de 4 horas dirigido a estudiantes...", // Esta data puede viajar aquí y usarse en el callback
            metaDatos: [
                { tipo: "texto", etiqueta: "Modalidad", valor: "Virtual" },
                { tipo: "texto", etiqueta: "Horario", valor: "9:00 am - 1:00 p.m" },
                { 
                    tipo: "boton", 
                    etiqueta: "", // Dejamos vacío para que se haga ancho completo (btn-block)
                    textoBoton: "<span class='glyphicon glyphicon-info-sign'></span> Leer más detalles", 
                    claseBoton: "btn btn-info btn-sm", // Usa las clases nativas de Bootstrap 3
                    callback: function(fila) {
                        // Aquí llamas a tu función que abre el modal o popup
                        console.log("Abriendo popup para:", fila.tituloPrincipal);
                        console.log("Detalles:", fila.detalles);
                    }
                }
            ]
        }
    ];

    Ejemplo de inicalizacion de una tabla:
    renderMobileTable({
        containerId: "grid-talleres-movil",
        titulo: "Talleres y Visitas Industriales",
        datos: datosTalleres,
        inputName: "talleres_seleccionados",
        onSelectionChange: function(estado) {
            // Sincronización con la tabla original
            const originalCheckbox = document.querySelector(`input[name="talleres_seleccionados[]"][value="${estado.idModificado}"]`);
            if(originalCheckbox) {
                originalCheckbox.checked = estado.estaSeleccionado;
                originalCheckbox.dispatchEvent(new Event('change'));
            }
        }
    });
*/