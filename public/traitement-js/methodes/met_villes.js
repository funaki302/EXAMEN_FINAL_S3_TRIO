async function getAllVilles() {
    const villes = await fetch(BASE_URL+'/api/getAll/villes');
    if (!villes.ok) {
        throw new Error("Error lors de getAllVilles");
    }

    const data = await villes.json();
    return data;
}

async function createVille(data) {
    try {
        const response = await fetch(BASE_URL+'/api/create/villes', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        if (!response.ok) {
            throw new Error("Erreur lors de la création de la ville");
        }
        
        const result = await response.json();
        return result;
    } catch (error) {
        console.error('Erreur:', error);
        throw error;
    }
}