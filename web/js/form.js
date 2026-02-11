const displayElement = (selector, display = true) => {
    const element = document.querySelector(selector);
    if (!element) {
        console.warn(`Elemento no encontrado para selector: ${selector}`);
        return;
    }
    element.style.display = display ? 'block' : 'none';
}

class WorkshopManager {
    constructor(containerId) {
        this.container = document.getElementById(containerId);
        this.schema = {
            name: 1,
            description: 2,
            date: 3,
            start: 4,
            end: 5
        };
        
        if (!this.container) {
            console.error(`Contenedor #${containerId} no encontrado.`);
            return;
        }

        this.init();
    }

    // 1. GESTIÓN DE DATOS (MÉTODOS DE OBTENCIÓN)
    getAllRows() {
        return Array.from(this.container.querySelectorAll('table tbody tr'));
    }

    getData(onlySelected = false) {
        return this.getAllRows()
            .filter(row => !onlySelected || row.querySelector('input.kv-row-checkbox')?.checked)
            .map(row => this._mapRowToData(row));
    }

    // Método privado (convención _) para mapear celdas a objetos
    _mapRowToData(row) {
        return Object.entries(this.schema).reduce((acc, [key, colIndex]) => {
            const cell = row.querySelector(`td[data-col-seq="${colIndex}"]`);
            acc[key] = cell ? cell.textContent.trim() : null;
            return acc;
        }, { id: row.dataset.key });
    }

    // 2. GESTIÓN DE EVENTOS
    // Este método centraliza todos los "escuchadores"
    init() {
        console.log("WorkshopManager inicializado");

        // Evento cuando se marca/desmarca algo
        this.container.addEventListener('change', (e) => {
            if (e.target.classList.contains('kv-row-checkbox')) {
                this.handleSelectionChange(e.target);
            }
        });
    }

    handleSelectionChange(checkbox) {
        const selectedData = this.getData(true);
        console.log('checkbox:', checkbox);
        console.log("Cambio detectado. Seleccionados:", selectedData);
        
        // Aquí podrías disparar otros métodos, ej:
        // this.validateScheduleConflicts();
        // this.updateTotalCost();
    }

    // 3. MÉTODOS DE UTILIDAD (EJEMPLOS PARA EL FUTURO)
    highlightRow(id, color = '#fff3cd') {
        const row = this.container.querySelector(`tr[data-key="${id}"]`);
        if (row) row.style.backgroundColor = color;
    }
}

// --- MODO DE USO ---
window.onload = () => {
    const registration_type = document.getElementById("fee_type");
    console.log("Fee Type Element:", registration_type);
    // Creamos la instancia
    const myWorkshops = new WorkshopManager("workshop_type");
    
    // Ahora tienes métodos a la mano:
    // myWorkshops.getData(true) -> Te da los seleccionados en cualquier momento.
};