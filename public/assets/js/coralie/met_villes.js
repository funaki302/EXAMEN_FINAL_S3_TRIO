async function getAllVilles() {
    const villes = await fetch('/api/getAll/villes');
    if (!villes.ok) {
        throw new Error("Error lors de getAllVilles");
    }

    const data = await villes.json();
    return data;
}