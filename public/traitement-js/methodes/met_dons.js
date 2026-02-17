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


async function getDonsRestants() {
    const dons = await fetch(BASE_URL+'/api/getAll/dons-restants');
    if (!dons.ok) {
        throw new Error("Error lors de getDonsRestants");
    }

    const data = await dons.json();
    return data;
}

async function updateDon(data) {
    try {
        const response = await fetch(BASE_URL+'/api/update/dons/' + data.id_don, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });

        console.log('Réponse brute du serveur:', response);

        if (!response.ok) {
            const errorText = await response.text(); 
            console.error('Erreur serveur:', errorText); 
            throw new Error("Erreur lors de la mise à jour du don: " + errorText);
        }

        const result = await response.json();
        console.log('Résultat JSON du serveur:', result); 
        return result;
    } catch (error) {
        console.error('Erreur attrapée dans updateDon:', error);
        throw error;
    }
}