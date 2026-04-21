class ResponsiveCheckboxGrid {
    constructor(config) {
        this.container = document.getElementById(config.containerId);
        this.data = config.data || [];
        this.inputName = config.inputName || "seleccionados[]";
        this.columns = config.columns || [];
        this.onSelectionChange = config.onSelectionChange || function() {};
        
        if (!this.container) return;
        this.init();
    }

    init() {
        this.render();
        this.bindEvents();
    }

    render() {
        let html = `<div class="rcg-table">`;
        
        // 1. Renderizar Cabeceras (Solo visibles en escritorio)
        html += `<div class="rcg-header-row">`;
        html += `<div class="rcg-header-cell rcg-checkbox-cell"></div>`; // Espacio para el checkbox
        this.columns.forEach(col => {
            html += `<div class="rcg-header-cell">${col.label}</div>`;
        });
        html += `</div>`;

        // 2. Renderizar Filas de Datos
        this.data.forEach(row => {
            html += `
                <div class="rcg-row" data-id="${row.id}">
                    <div class="rcg-cell rcg-checkbox-cell">
                        <input type="checkbox" id="chk-${this.inputName}-${row.id}" 
                               class="rcg-checkbox" name="${this.inputName}" value="${row.id}">
                    </div>
            `;
            
            this.columns.forEach((col, index) => {
                // Si la columna tiene una función 'format', la ejecutamos. Si no, mostramos el texto plano.
                const cellValue = col.format ? col.format(row) : (row[col.attribute] || '');
                // En móvil, usamos el 'data-label' para mostrar el nombre de la columna antes del valor
                html += `
                    <div class="rcg-cell" data-label="${col.label}">
                        ${cellValue}
                    </div>
                `;
            });
            
            html += `</div>`;
        });

        html += `</div>`;
        this.container.innerHTML = html;
    }

    bindEvents() {
        // Evento para checkboxes
        const checkboxes = this.container.querySelectorAll('.rcg-checkbox');
        checkboxes.forEach(chk => {
            chk.addEventListener('change', (e) => {
                const rowElement = e.target.closest('.rcg-row');
                if (rowElement) {
                    rowElement.classList.toggle('is-selected', e.target.checked);
                }
                
                const seleccionados = Array.from(checkboxes)
                    .filter(c => c.checked)
                    .map(c => c.value);
                    
                this.onSelectionChange({
                    idModificado: e.target.value,
                    estaSeleccionado: e.target.checked,
                    todosLosSeleccionados: seleccionados
                });
            });
        });

        // Delegación de eventos para botones personalizados (como el "Leer más")
        this.container.addEventListener('click', (e) => {
            const btn = e.target.closest('.rcg-action-btn');
            if (btn) {
                e.stopPropagation(); // Evitar que el clic marque el checkbox accidentalmente
                const action = btn.getAttribute('data-action');
                const rowId = btn.closest('.rcg-row').getAttribute('data-id');
                const rowData = this.data.find(r => String(r.id) === String(rowId));
                
                // Buscar la columna que definió esta acción para ejecutar su callback
                const columnWithAction = this.columns.find(c => c.actionName === action);
                if (columnWithAction && typeof columnWithAction.onAction === 'function') {
                    columnWithAction.onAction(rowData);
                }
            }
        });
    }
}