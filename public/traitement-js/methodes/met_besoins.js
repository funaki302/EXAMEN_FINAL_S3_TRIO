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

async function updateBesoin(data) {
    try {
        console.log('Données envoyées pour mise à jour:', data); // Vérification des données envoyées

        const response = await fetch(BASE_URL+'/api/update/besoins/' + data.id_besoin, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });

        console.log('Réponse brute du serveur:', response); // Vérification de la réponse brute

        if (!response.ok) {
            const errorText = await response.text(); // Lire le texte de l'erreur
            console.error('Erreur serveur:', errorText); // Afficher l'erreur serveur
            throw new Error("Erreur lors de la mise à jour du besoin: " + errorText);
        }

        const result = await response.json();
        console.log('Résultat JSON du serveur:', result); // Vérification du résultat JSON
        return result;
    } catch (error) {
        console.error('Erreur attrapée dans updateBesoin:', error);
        throw error;
    }
}