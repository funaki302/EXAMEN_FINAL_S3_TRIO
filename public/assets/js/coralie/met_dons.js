async function createDon(data) {
    try {
        const response = await fetch(BASE_URL+'/api/create/dons', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });

        if (!response.ok) {
            throw new Error("Erreur lors de la création du don");
        }

        const result = await response.json();
        return result;
    } catch (error) {
        console.error('Erreur:', error);
        throw error;
    }
}

async function getAllDons() {
    const dons = await fetch(BASE_URL+'/api/getAll/dons-recus');
    if (!dons.ok) {
        throw new Error("Error lors de getAllDons");
    }

    const data = await dons.json();
    return data;
}