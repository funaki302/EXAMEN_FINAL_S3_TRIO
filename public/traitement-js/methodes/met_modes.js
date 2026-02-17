/**
 * Méthodes pour les Modes (origine/teste)
 */

async function getAllModes() {
    const response = await fetch(BASE_URL + '/api/getAll/modes');
    if (!response.ok) {
        throw new Error("Erreur lors de getAllModes");
    }
    const data = await response.json();
    return data;
}

async function getModesStats() {
    const response = await fetch(BASE_URL + '/api/modes/stats');
    if (!response.ok) {
        throw new Error("Erreur lors de getModesStats");
    }
    const data = await response.json();
    return data;
}

async function reinitialiserDonnees() {
    try {
        const response = await fetch(BASE_URL + '/api/modes/reinitialiser', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        });

        if (!response.ok) {
            throw new Error("Erreur lors de la réinitialisation");
        }

        const result = await response.json();
        return result;
    } catch (error) {
        console.error('Erreur:', error);
        throw error;
    }
}

/**
 * Crée un select HTML pour le choix du mode
 * @param {string} selectId - L'ID du select
 * @param {Array} modes - Les modes disponibles
 * @param {number} defaultMode - Le mode par défaut (1 = origine, 2 = teste)
 */
function createModeSelect(selectId, modes, defaultMode = 2) {
    const select = document.createElement('select');
    select.className = 'form-select';
    select.id = selectId;
    select.required = true;

    modes.forEach(mode => {
        const option = document.createElement('option');
        option.value = mode.id_mode;
        option.textContent = mode.nom_mode.charAt(0).toUpperCase() + mode.nom_mode.slice(1);
        if (mode.description) {
            option.title = mode.description;
        }
        if (parseInt(mode.id_mode) === defaultMode) {
            option.selected = true;
        }
        select.appendChild(option);
    });

    return select;
}
