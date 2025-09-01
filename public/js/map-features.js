/**
 * PKM Kaben Map Features - Modular JavaScript untuk fitur peta terbarukan
 * Mengextend namespace PkmKabenApp yang sudah ada
 */

// Extend PkmKabenApp dengan fitur-fitur baru
(function() {
    'use strict';

    // Pastikan PkmKabenApp sudah ada
    if (typeof window.PkmKabenApp === 'undefined') {
        console.error('PkmKabenApp namespace tidak ditemukan. Pastikan script utama sudah dimuat.');
        return;
    }

    const App = window.PkmKabenApp;

    // Feature flags dari server (akan di-inject via Blade)
    const FEATURES = window.MAP_FEATURES || {};

    // ====== BBOX & DELTA SYNC MODULE ======
    App.BboxLoader = {
        lastModified: null,
        debounceTimer: null,
        buildingIndex: new Map(), // num -> feature untuk pencarian cepat

        init() {
            if (!FEATURES.enable_bbox_sync) return;
            
            // Load dari localStorage jika ada
            this.lastModified = localStorage.getItem('pkmKaben_lastModified');
            this.loadCachedIndex();

            // Attach event listener untuk map moveend
            if (App.state.map) {
                App.state.map.on('moveend', this.onMapMoveEnd.bind(this));
            }
        },

        onMapMoveEnd() {
            // Debounce untuk menghindari request berlebihan
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => {
                this.loadBuildingsInView();
            }, FEATURES.bbox_debounce_ms || 250);
        },

        async loadBuildingsInView() {
            if (!App.state.map) return;

            const bounds = App.state.map.getBounds();
            const bbox = [
                bounds.getWest(),  // minLon
                bounds.getSouth(), // minLat  
                bounds.getEast(),  // maxLon
                bounds.getNorth()  // maxLat
            ].join(',');

            try {
                const url = new URL('/api/map/buildings', window.location.origin);
                url.searchParams.set('bbox', bbox);
                if (this.lastModified) {
                    url.searchParams.set('since', this.lastModified);
                }

                const response = await fetch(url);
                if (!response.ok) {
                    console.warn('BBOX request failed:', response.status);
                    return;
                }

                const data = await response.json();
                
                // Update last modified
                this.lastModified = data.last_modified;
                localStorage.setItem('pkmKaben_lastModified', this.lastModified);

                // Update building index untuk pencarian
                data.features.forEach(feature => {
                    const num = feature.properties.building_number;
                    if (num) {
                        this.buildingIndex.set(num, feature);
                    }
                });

                // Simpan index ke localStorage
                this.saveCachedIndex();

                // Update markers (hanya jika ada data baru)
                if (data.features.length > 0) {
                    this.updateMapMarkers(data.features);
                }

                console.log(`BBOX loaded: ${data.features.length} buildings`);

            } catch (error) {
                console.error('BBOX loading error:', error);
            }
        },

        updateMapMarkers(features) {
            // Update marker layer yang ada tanpa menghapus semua
            // Implementasi ini akan disesuaikan dengan struktur marker existing
            if (App.state.markerLayer) {
                features.forEach(feature => {
                    const [lon, lat] = feature.geometry.coordinates;
                    const props = feature.properties;
                    
                    // Buat marker baru (sesuaikan dengan style existing)
                    const marker = L.marker([lat, lon], {
                        icon: this.createBuildingIcon(props)
                    }).bindPopup(this.createPopupContent(props));
                    
                    App.state.markerLayer.addLayer(marker);
                });
            }
        },

        createBuildingIcon(props) {
            // Gunakan style yang sama dengan existing
            return L.divIcon({
                html: `
                    <div class="house-marker" style="background-color: ${App.constants.defaultColor}">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20">
                            <path fill="white" d="M19 9.3V4h-3v2.6L12 3 2 12h3v8h5v-6h4v6h5v-8h3l-3-2.7zM12 18.5h-2v-6H8v6H6v-8l6-5.5 6 5.5v8h-2v-6h-2v6h-2v-6z"/>
                        </svg>
                        <span>${props.building_number}</span>
                    </div>`,
                className: 'custom-house-marker',
                iconSize: [40, 50],
                iconAnchor: [20, 50],
                popupAnchor: [0, -45]
            });
        },

        createPopupContent(props) {
            return `
                <div class="building-popup">
                    <h4>Bangunan #${props.building_number}</h4>
                    <p><strong>Desa:</strong> ${props.village_name || 'N/A'}</p>
                    <button onclick="PkmKabenApp.showBuildingDetails(${props.id})" class="btn-primary">
                        Lihat Detail
                    </button>
                </div>
            `;
        },

        loadCachedIndex() {
            try {
                const cached = localStorage.getItem('pkmKaben_buildingIndex');
                if (cached) {
                    const data = JSON.parse(cached);
                    this.buildingIndex = new Map(data);
                }
            } catch (error) {
                console.warn('Failed to load cached building index:', error);
            }
        },

        saveCachedIndex() {
            try {
                const data = Array.from(this.buildingIndex.entries());
                localStorage.setItem('pkmKaben_buildingIndex', JSON.stringify(data));
            } catch (error) {
                console.warn('Failed to save building index:', error);
            }
        }
    };

    // ====== BUILDING SEARCH MODULE ======
    App.BuildingSearch = {
        init() {
            if (!FEATURES.enable_search_building) return;

            this.createSearchUI();
            this.attachEventListeners();
        },

        createSearchUI() {
            // Cari container yang ada atau buat baru
            let searchContainer = document.getElementById('buildingSearchContainer');
            if (!searchContainer) {
                searchContainer = document.createElement('div');
                searchContainer.id = 'buildingSearchContainer';
                searchContainer.className = 'map-search-container';
                searchContainer.innerHTML = `
                    <div class="search-input-group">
                        <input type="text" id="buildingSearchInput" placeholder="Cari nomor bangunan..." />
                        <button id="buildingSearchBtn" class="search-btn">🔍</button>
                    </div>
                    <div id="buildingSearchResults" class="search-results"></div>
                `;
                
                // Tambahkan ke control panel atau navbar
                const controlPanel = document.getElementById('controlPanel');
                if (controlPanel) {
                    controlPanel.appendChild(searchContainer);
                }
            }
        },

        attachEventListeners() {
            const searchInput = document.getElementById('buildingSearchInput');
            const searchBtn = document.getElementById('buildingSearchBtn');

            if (searchInput) {
                searchInput.addEventListener('input', this.onSearchInput.bind(this));
                searchInput.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') {
                        this.performSearch(searchInput.value);
                    }
                });
            }

            if (searchBtn) {
                searchBtn.addEventListener('click', () => {
                    this.performSearch(searchInput.value);
                });
            }
        },

        onSearchInput(event) {
            const query = event.target.value.trim();
            
            // Clear results jika input kosong
            if (query === '') {
                this.clearResults();
                return;
            }

            // Cari di index lokal dulu (instant)
            this.searchInLocalIndex(query);
        },

        searchInLocalIndex(query) {
            if (!App.BboxLoader) return;

            const results = [];
            const lowerQuery = query.toLowerCase();

            // Cari di building index
            for (const [buildingNum, feature] of App.BboxLoader.buildingIndex) {
                if (buildingNum.toLowerCase().includes(lowerQuery)) {
                    results.push({
                        building_number: buildingNum,
                        village_name: feature.properties.village_name,
                        coordinates: feature.geometry.coordinates
                    });
                }
                
                // Batasi hasil untuk performa
                if (results.length >= 5) break;
            }

            this.showSearchResults(results, true);
        },

        async performSearch(query) {
            if (!query.trim()) return;

            try {
                const url = new URL('/api/map/buildings/find', window.location.origin);
                url.searchParams.set('num', query.trim());

                const response = await fetch(url);
                
                if (response.ok) {
                    const building = await response.json();
                    this.jumpToBuilding(building);
                } else if (response.status === 404) {
                    this.showNotFoundMessage(query);
                } else {
                    console.error('Search failed:', response.status);
                }

            } catch (error) {
                console.error('Search error:', error);
                this.showErrorMessage();
            }
        },

        jumpToBuilding(building) {
            if (!App.state.map) return;

            // Zoom ke lokasi building
            App.state.map.flyTo([building.lat, building.lon], 19, {
                duration: 1.0
            });

            // Highlight building jika memungkinkan
            this.highlightBuilding(building);
            
            // Clear search results
            this.clearResults();
        },

        highlightBuilding(building) {
            // Buat marker sementara untuk highlight
            if (App.state.priorityMarker) {
                App.state.map.removeLayer(App.state.priorityMarker);
            }

            App.state.priorityMarker = L.marker([building.lat, building.lon], {
                icon: L.divIcon({
                    html: `
                        <div class="priority-marker pulsing">
                            <div class="marker-inner">
                                ${building.building_number}
                            </div>
                        </div>
                    `,
                    className: 'priority-marker-container',
                    iconSize: [60, 60],
                    iconAnchor: [30, 30]
                })
            }).addTo(App.state.map);

            // Auto-remove setelah 5 detik
            setTimeout(() => {
                if (App.state.priorityMarker) {
                    App.state.map.removeLayer(App.state.priorityMarker);
                    App.state.priorityMarker = null;
                }
            }, 5000);
        },

        showSearchResults(results, isLocal = false) {
            const resultsContainer = document.getElementById('buildingSearchResults');
            if (!resultsContainer) return;

            if (results.length === 0) {
                resultsContainer.innerHTML = isLocal ? 
                    '<div class="search-no-results">Ketik untuk mencari...</div>' : 
                    '<div class="search-no-results">Tidak ditemukan</div>';
                return;
            }

            resultsContainer.innerHTML = results.map(result => `
                <div class="search-result-item" onclick="PkmKabenApp.BuildingSearch.selectResult('${result.building_number}')">
                    <strong>#${result.building_number}</strong>
                    <small>${result.village_name || 'N/A'}</small>
                </div>
            `).join('');
        },

        selectResult(buildingNumber) {
            document.getElementById('buildingSearchInput').value = buildingNumber;
            this.performSearch(buildingNumber);
        },

        showNotFoundMessage(query) {
            const resultsContainer = document.getElementById('buildingSearchResults');
            if (resultsContainer) {
                resultsContainer.innerHTML = `
                    <div class="search-not-found">
                        Bangunan #${query} tidak ditemukan atau belum termuat di viewport.
                        <br><small>Geser/zoom peta atau gunakan menu Desa, lalu coba lagi.</small>
                    </div>
                `;
            }
        },

        showErrorMessage() {
            const resultsContainer = document.getElementById('buildingSearchResults');
            if (resultsContainer) {
                resultsContainer.innerHTML = '<div class="search-error">Terjadi kesalahan saat pencarian</div>';
            }
        },

        clearResults() {
            const resultsContainer = document.getElementById('buildingSearchResults');
            if (resultsContainer) {
                resultsContainer.innerHTML = '';
            }
        }
    };

    // ====== HOUSE LABELS TOGGLE MODULE ======
    App.HouseLabels = {
        enabled: false,
        labelLayer: null,

        init() {
            if (!FEATURES.enable_house_labels) return;

            this.createToggleButton();
            this.labelLayer = L.layerGroup();
        },

        createToggleButton() {
            // Tambahkan tombol toggle ke control panel
            const toggleBtn = document.createElement('button');
            toggleBtn.id = 'houseLabelToggle';
            toggleBtn.className = 'map-control-btn';
            toggleBtn.innerHTML = '🏷️ Label #';
            toggleBtn.onclick = this.toggle.bind(this);

            const controlPanel = document.getElementById('controlPanel');
            if (controlPanel) {
                controlPanel.appendChild(toggleBtn);
            }
        },

        toggle() {
            this.enabled = !this.enabled;
            
            if (this.enabled) {
                this.showLabels();
            } else {
                this.hideLabels();
            }

            // Update button state
            const btn = document.getElementById('houseLabelToggle');
            if (btn) {
                btn.classList.toggle('active', this.enabled);
            }
        },

        showLabels() {
            if (!App.state.map || !this.labelLayer) return;

            // Clear existing labels
            this.labelLayer.clearLayers();

            // Tambahkan label untuk setiap building di viewport
            if (App.BboxLoader && App.BboxLoader.buildingIndex) {
                const bounds = App.state.map.getBounds();
                
                for (const [buildingNum, feature] of App.BboxLoader.buildingIndex) {
                    const [lon, lat] = feature.geometry.coordinates;
                    
                    if (bounds.contains([lat, lon])) {
                        const label = L.marker([lat, lon], {
                            icon: L.divIcon({
                                html: `<div class="building-label">${buildingNum}</div>`,
                                className: 'building-label-container',
                                iconSize: [30, 20],
                                iconAnchor: [15, 10]
                            })
                        });
                        
                        this.labelLayer.addLayer(label);
                    }
                }
            }

            this.labelLayer.addTo(App.state.map);
        },

        hideLabels() {
            if (App.state.map && this.labelLayer) {
                App.state.map.removeLayer(this.labelLayer);
            }
        }
    };

    // ====== INITIALIZATION ======
    // Auto-initialize semua modul saat DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        // Tunggu sebentar untuk memastikan PkmKabenApp sudah fully loaded
        setTimeout(() => {
            App.BboxLoader.init();
            App.BuildingSearch.init();
            App.HouseLabels.init();
            
            console.log('Map Features initialized');
        }, 1000);
    });

    // Expose modules untuk debugging
    window.MapFeatures = {
        BboxLoader: App.BboxLoader,
        BuildingSearch: App.BuildingSearch,
        HouseLabels: App.HouseLabels
    };

})();
