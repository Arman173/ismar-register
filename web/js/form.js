/**
 * WorkshopManager
 * Maneja la lógica de visualización de talleres basada en el tipo de registro
 * y la selección de múltiples talleres.
 */
class WorkshopManager {
    
    constructor(config) {
        // 1. Configuración: Mapeamos los IDs del DOM a propiedades de la clase
        // Esto hace que si cambias un ID en el HTML, solo cambias aquí la config.
        this.selectors = {
            feeType: 'input[name="kvradio"]',           // Radios de la tabla de tipos de registro
            workshopSelector: 'input[name="workshop_selector"]', // Radio Si/No
            
            // Contenedores a mostrar/ocultar
            tableMulti: '#workshop_type',       // Tabla Checkboxes (Si)
            tableSingle: '#workshop_type_radio' // Tabla Radios (No)
        };

        // IDs de tipos de registro
        this.types = {
            GENERAL: '1',
            STUDENT: '12',
            UADY: '17'
        };

        // Inicializamos
        this.init();
    }

    init() {
        // Cacheamos referencias jQuery para no buscarlas cada vez
        this.$feeTypeInputs = $(this.selectors.feeType);
        this.$workshopSelectorInputs = $(this.selectors.workshopSelector);
        this.$tableMulti = $(this.selectors.tableMulti);
        this.$tableSingle = $(this.selectors.tableSingle);

        // Bind de eventos
        this.bindEvents();

        // Ejecutar lógica inicial (por si el usuario recarga y ya hay datos seleccionados)
        this.updateVisibility();
    }

    bindEvents() {
        // Usamos arrow functions para mantener el contexto de 'this'
        
        // Evento: Cambio en Tipo de Registro (Tabla Fee)
        $(document).on('change', this.selectors.feeType, () => {
            this.updateVisibility();
        });

        // Evento: Cambio en Selector Si/No
        $(document).on('change', this.selectors.workshopSelector, () => {
            this.updateVisibility();
        });
    }

    /**
     * Obtiene los valores actuales del DOM
     */
    getCurrentValues() {
        return {
            feeType: this.$feeTypeInputs.filter(':checked').val(),
            isMultiple: this.$workshopSelectorInputs.filter(':checked').val() === 'si'
        };
    }

    /**
     * Resetea la vista (oculta todo) antes de aplicar lógica
     */
    resetView() {
        this.$tableMulti.hide();
        this.$tableSingle.hide();
    }

    /**
     * Núcleo de la lógica
     */
    updateVisibility() {
        const { feeType, isMultiple } = this.getCurrentValues();

        // 1. Primero ocultamos todo para partir de un estado limpio
        this.resetView();

        // Si no hay tipo de registro seleccionado, no hacemos nada más
        if (!feeType) return;

        // 2. Aplicamos la lógica
        
        // CASO: Selección Múltiple ("SI")
        // Aplica para todos (General, Estudiante y UADY)
        if (isMultiple) {
            this.$tableMulti.show();
            return;
        }

        // CASO: Selección Única ("NO")
        // Aquí es donde entra la excepción de UADY
        if (!isMultiple) {
            if (feeType === this.types.UADY) {
                // Caso UADY + NO: No mostrar nada (según tu requerimiento)
                // Ya ocultamos todo en resetView(), así que no hacemos nada.
            } else {
                // Caso General (1) o Estudiante (12) + NO: Mostrar tabla de radios
                this.$tableSingle.show();
            }
        }
    }
}

// Inicialización cuando el DOM esté listo
$(document).ready(function() {
    // Instanciamos la clase.
    new WorkshopManager();
});