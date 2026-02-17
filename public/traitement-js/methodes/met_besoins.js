async function createBesoin(data) {
    try {
        const response = await fetch(BASE_URL+'/api/create/besoins', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        if (!response.ok) {
            throw new Error("Erreur lors de la création du besoin");
        }
        
        const result = await response.json();
        return result;
    } catch (error) {
        console.error('Erreur:', error);
        throw error;
    }
}

async function getAllBesoins() {
    const besoins = await fetch(BASE_URL+'/api/getAll/besoins');
    if (!besoins.ok) {
        throw new Error("Error lors de getAllArticles");
    }

    const data = await besoins.json();
    return data;
}