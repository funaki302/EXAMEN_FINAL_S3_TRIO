async function createBesoin(data) {
    try {
        const response = await fetch('/api/create/besoins', {
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