async function getAllArticles() {
    const articles = await fetch(BASE_URL+'/api/getAll/articles');
    if (!articles.ok) {
        throw new Error("Error lors de getAllArticles");
    }

    const data = await articles.json();
    return data;
}

async function createArticle(data) {
    try {
        const response = await fetch(BASE_URL+'/api/create/articles', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        if (!response.ok) {
            throw new Error("Erreur lors de la création de l'article");
        }
        
        const result = await response.json();
        return result;
    } catch (error) {
        console.error('Erreur:', error);
        throw error;
    }
}