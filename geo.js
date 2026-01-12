const API_BASE = 'https://apis.datos.gob.ar/georef/api';

/**
 * Carga las provincias en un select
 * @param {string} selectId - ID del select de provincias
 * @param {string} selectedValue - Valor preseleccionado (opcional)
 */
async function loadProvinces(selectId, selectedValue = '') {
    const select = document.getElementById(selectId);
    if(!select) return;
    
    try {
        const res = await fetch(`${API_BASE}/provincias?orden=nombre`);
        const data = await res.json();
        
        select.innerHTML = '<option value="">Selecciona una provincia</option>';
        data.provincias.forEach(p => {
            const option = document.createElement('option');
            option.value = p.nombre;
            option.textContent = p.nombre;
            if(p.nombre === selectedValue) option.selected = true;
            select.appendChild(option);
        });
        
        // Configurar evento de cambio
        select.addEventListener('change', (e) => {
            const cityTargetId = select.dataset.cityTarget;
            loadCities(e.target.value, cityTargetId);
        });

        // Cargar ciudades si ya hay una provincia seleccionada
        if(selectedValue) {
            loadCities(selectedValue, select.dataset.cityTarget, select.dataset.cityValue);
        }
    } catch (e) {
        console.error('Error cargando provincias', e);
    }
}

/**
 * Carga las localidades/ciudades basadas en la provincia
 */
async function loadCities(provinceName, selectId, selectedValue = '') {
    const select = document.getElementById(selectId);
    if(!select || !provinceName) return;
    
    select.innerHTML = '<option value="">Cargando...</option>';
    
    try {
        const res = await fetch(`${API_BASE}/localidades?provincia=${encodeURIComponent(provinceName)}&orden=nombre&max=5000`);
        const data = await res.json();
        
        select.innerHTML = '<option value="">Selecciona una ciudad</option>';
        data.localidades.forEach(l => {
            const option = document.createElement('option');
            option.value = l.nombre;
            option.textContent = l.nombre;
            if(l.nombre === selectedValue) option.selected = true;
            select.appendChild(option);
        });
    } catch (e) {
        console.error('Error cargando ciudades', e);
        select.innerHTML = '<option value="">Error al cargar</option>';
    }
}