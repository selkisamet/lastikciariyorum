// City and district search functionality
document.addEventListener('DOMContentLoaded', function () {
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

        searchInput.addEventListener('input', function (e) {
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
        // Use absolute URL based on current origin
        const searchUrl = window.location.origin + '/arama?q=' + encodeURIComponent(term);

        fetch(searchUrl)
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
                    </a>
                `;
            } else if (result.type === 'district') {
                html += `
                    <a href="${result.url}" class="city-card district-card" data-type="district" data-name="${escapeHtml(result.name)}">
                        <h3 class="city-name">${escapeHtml(result.name)}</h3>
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

// AI Article Generator - Bulk Generation District Selection
document.addEventListener('DOMContentLoaded', function () {
    const bulkForm = document.getElementById('bulkGenerationForm');
    if (!bulkForm) return;

    const districtOptions = document.querySelectorAll('input[name="district_option"]');
    const cityCheckboxes = document.querySelectorAll('.city-checkbox');
    const selectAllCities = document.getElementById('select_all_cities');

    let districtSelectionContainer = null;

    // Handle district option changes
    districtOptions.forEach(radio => {
        radio.addEventListener('change', function () {
            handleDistrictOptionChange(this.value);
        });
    });

    // Handle city checkbox changes
    cityCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const selectedOption = document.querySelector('input[name="district_option"]:checked');
            if (selectedOption && selectedOption.value === 'selected') {
                loadDistrictsForSelectedCities();
            }
        });
    });

    // Select all cities functionality
    if (selectAllCities) {
        selectAllCities.addEventListener('change', function () {
            cityCheckboxes.forEach(cb => {
                cb.checked = this.checked;
            });
            const selectedOption = document.querySelector('input[name="district_option"]:checked');
            if (selectedOption && selectedOption.value === 'selected') {
                loadDistrictsForSelectedCities();
            }
        });
    }

    function handleDistrictOptionChange(option) {
        if (option === 'selected') {
            showDistrictSelection();
            loadDistrictsForSelectedCities();
        } else {
            hideDistrictSelection();
        }
    }

    function showDistrictSelection() {
        if (!districtSelectionContainer) {
            createDistrictSelectionContainer();
        }
        districtSelectionContainer.style.display = 'block';
    }

    function hideDistrictSelection() {
        if (districtSelectionContainer) {
            districtSelectionContainer.style.display = 'none';
        }
    }

    function createDistrictSelectionContainer() {
        const districtRadioGroup = document.querySelector('input[name="district_option"][value="selected"]').closest('.form-group');

        districtSelectionContainer = document.createElement('div');
        districtSelectionContainer.className = 'form-group district-selection-container';
        districtSelectionContainer.innerHTML = `
            <label>İlçe Seçimi <span class="required">*</span></label>
            <small class="form-help" style="display: block; margin-bottom: 10px;">
                Önce yukarıdan en az bir il seçin. Seçili illerin ilçeleri aşağıda görünecektir.
            </small>
            <div class="district-selection-controls" style="margin-bottom: 10px;">
                <button type="button" id="select_all_districts" class="btn btn-sm btn-secondary">Tümünü Seç</button>
                <button type="button" id="deselect_all_districts" class="btn btn-sm btn-secondary">Hiçbirini Seçme</button>
            </div>
            <div id="districtCheckboxGrid" class="checkbox-grid" style="max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 15px; border-radius: 4px;">
                <p style="color: #666; text-align: center;">Önce yukarıdan il seçin...</p>
            </div>
        `;
        districtSelectionContainer.style.display = 'none';

        districtRadioGroup.parentNode.insertBefore(districtSelectionContainer, districtRadioGroup.nextSibling);

        // Add event listeners for select/deselect all
        document.getElementById('select_all_districts').addEventListener('click', function () {
            document.querySelectorAll('#districtCheckboxGrid input[type="checkbox"]').forEach(cb => cb.checked = true);
        });

        document.getElementById('deselect_all_districts').addEventListener('click', function () {
            document.querySelectorAll('#districtCheckboxGrid input[type="checkbox"]').forEach(cb => cb.checked = false);
        });
    }

    function loadDistrictsForSelectedCities() {
        const selectedCityIds = Array.from(cityCheckboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);

        const districtGrid = document.getElementById('districtCheckboxGrid');
        if (!districtGrid) return;

        if (selectedCityIds.length === 0) {
            districtGrid.innerHTML = '<p style="color: #666; text-align: center;">Önce yukarıdan il seçin...</p>';
            return;
        }

        districtGrid.innerHTML = '<p style="color: #666; text-align: center;"><span class="spinner"></span> İlçeler yükleniyor...</p>';

        // Fetch districts for selected cities
        const adminUrl = window.location.origin + '/admin/get-districts-for-cities';

        fetch(adminUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ city_ids: selectedCityIds })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayDistricts(data.districts);
                } else {
                    districtGrid.innerHTML = '<p style="color: #d32f2f;">Hata: ' + (data.error || 'İlçeler yüklenemedi') + '</p>';
                }
            })
            .catch(error => {
                console.error('Error loading districts:', error);
                districtGrid.innerHTML = '<p style="color: #d32f2f;">İlçeler yüklenirken bir hata oluştu.</p>';
            });
    }

    function displayDistricts(districts) {
        const districtGrid = document.getElementById('districtCheckboxGrid');

        if (districts.length === 0) {
            districtGrid.innerHTML = '<p style="color: #666; text-align: center;">Seçili iller için ilçe bulunamadı.</p>';
            return;
        }

        // Group districts by city
        const districtsByCity = {};
        districts.forEach(district => {
            if (!districtsByCity[district.city_name]) {
                districtsByCity[district.city_name] = [];
            }
            districtsByCity[district.city_name].push(district);
        });

        let html = '';
        Object.keys(districtsByCity).sort().forEach(cityName => {
            html += `
                <div class="district-city-group" style="margin-bottom: 20px;">
                    <h4 style="color: #333; margin-bottom: 10px; padding-bottom: 5px; border-bottom: 2px solid #4CAF50;">${escapeHtml(cityName)}</h4>
                    <div class="checkbox-grid" style="margin-left: 10px;">
            `;

            districtsByCity[cityName].forEach(district => {
                html += `
                    <label class="checkbox-label">
                        <input type="checkbox" name="district_ids[]" value="${district.id}" class="district-checkbox">
                        ${escapeHtml(district.name)}
                    </label>
                `;
            });

            html += `
                    </div>
                </div>
            `;
        });

        districtGrid.innerHTML = html;
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
