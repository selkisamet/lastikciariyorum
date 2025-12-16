// City and district search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('citySearch');
    const citiesGrid = document.getElementById('citiesGrid');
    const citiesSectionTitle = document.getElementById('citiesSectionTitle');
    let searchTimeout = null;
    let originalContent = '';
    let originalTitle = '';

    if (searchInput && citiesGrid) {
        // Store original content
        originalContent = citiesGrid.innerHTML;
        originalTitle = citiesSectionTitle ? citiesSectionTitle.textContent : 'Tüm İller';

        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.trim();

            // Clear previous timeout
            if (searchTimeout) {
                clearTimeout(searchTimeout);
            }

            // Show all cities if search term is empty
            if (searchTerm.length < 2) {
                restoreOriginalContent();
                return;
            }

            // Debounce search
            searchTimeout = setTimeout(() => {
                performSearch(searchTerm);
            }, 300);
        });
    }

    function restoreOriginalContent() {
        citiesGrid.innerHTML = originalContent;
        if (citiesSectionTitle) {
            citiesSectionTitle.textContent = originalTitle;
        }
    }

    function performSearch(term) {
        fetch('/arama?q=' + encodeURIComponent(term))
            .then(response => response.json())
            .then(data => {
                displaySearchResults(data.results, term);
            })
            .catch(error => {
                console.error('Search error:', error);
                citiesGrid.innerHTML = '<div class="search-error">Arama sırasında bir hata oluştu.</div>';
            });
    }

    function displaySearchResults(results, searchTerm) {
        if (results.length === 0) {
            citiesGrid.innerHTML = '<div class="search-no-results">Sonuç bulunamadı</div>';
            if (citiesSectionTitle) {
                citiesSectionTitle.textContent = 'Arama Sonuçları';
            }
            return;
        }

        let html = '';
        results.forEach(result => {
            if (result.type === 'city') {
                html += `
                    <a href="${result.url}" class="city-card" data-type="city" data-name="${escapeHtml(result.name)}">
                        <h3 class="city-name">${escapeHtml(result.name)}</h3>
                        <div class="city-stats">
                            <span class="stat">
                                <span class="stat-icon">🏢</span>
                                ${result.company_count} Firma
                            </span>
                        </div>
                    </a>
                `;
            } else if (result.type === 'district') {
                html += `
                    <a href="${result.url}" class="city-card district-card" data-type="district" data-name="${escapeHtml(result.name)}">
                        <h3 class="city-name">${escapeHtml(result.name)}</h3>
                        <p class="district-city">${escapeHtml(result.city_name)}</p>
                        <div class="city-stats">
                            <span class="stat">
                                <span class="stat-icon">🏢</span>
                                ${result.company_count} Firma
                            </span>
                        </div>
                    </a>
                `;
            }
        });

        citiesGrid.innerHTML = html;
        if (citiesSectionTitle) {
            citiesSectionTitle.textContent = `Arama Sonuçları (${results.length})`;
        }
    }

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
});

// Lazy loading images
if ('loading' in HTMLImageElement.prototype) {
    const images = document.querySelectorAll('img[loading="lazy"]');
    images.forEach(img => {
        img.src = img.dataset.src || img.src;
    });
} else {
    // Fallback for browsers that don't support lazy loading
    const script = document.createElement('script');
    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js';
    document.body.appendChild(script);
}
