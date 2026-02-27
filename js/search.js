document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('search-input');
    const suggestions = document.getElementById('search-suggestions');
    if (!input || !suggestions) return;

    input.addEventListener('input', function() {
        const query = this.value.trim();
        if (!query) { suggestions.style.display = 'none'; suggestions.innerHTML = ''; return; }

        fetch(`search_suggestions.php?q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                suggestions.innerHTML = '';
                if (!data || data.length === 0) { suggestions.style.display = 'none'; return; }

                data.forEach(game => {
                    const div = document.createElement('div');
                    div.textContent = game.titre;
                    div.addEventListener('click', () => {
                        window.location.href = `search.php?q=${encodeURIComponent(game.titre)}`;
                    });
                    suggestions.appendChild(div);
                });

                suggestions.style.display = 'block';
            })
            .catch(err => { console.error(err); suggestions.style.display = 'none'; });
    });

    document.addEventListener('click', e => {
        if (!input.contains(e.target) && !suggestions.contains(e.target)) {
            suggestions.style.display = 'none';
        }
    });
});