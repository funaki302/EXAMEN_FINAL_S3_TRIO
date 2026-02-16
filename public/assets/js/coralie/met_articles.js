async function getAllArticles() {
    const articles = await fetch(BASE_URL+'/api/getAll/articles');
    if (!articles.ok) {
        throw new Error("Error lors de getAllArticles");
    }

    const data = await articles.json();
    return data;
}