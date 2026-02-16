async function getAllArticles() {
    const articles = await fetch('/api/getAll/articles');
    if (!articles.ok) {
        throw new Error("Error lors de getAllArticles");
    }

    const data = await articles.json();
    return data;
}