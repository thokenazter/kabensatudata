/**
 * PKM Kaben Map Tools - Fitur pengukuran, buffer, dan analisis spasial
 * Extends PkmKabenApp dengan tools advanced
 */

(function() {
    'use strict';

    // Pastikan dependencies tersedia
    if (typeof window.PkmKabenApp === 'undefined') {
        console.error('PkmKabenApp namespace tidak ditemukan');
        return;
    }

    if (typeof window.turf === 'undefined') {
        console.warn('Turf.js tidak tersedia. Fitur pengukuran dan analisis spasial akan dinonaktifkan.');
        return;
    }

    const App = window.PkmKabenApp;
    const FEATURES = window.MAP_FEATURES || {};

    // ====== MEASUREMENT TOOL MODULE ======
    App.MeasurementTool = {
        active: false,
        points: [],
        polylineLayer: null,
        markersLayer: null,
        toolbar: null,

        init() {
            if (!FEATURES.enable_measure) return;

            this.createToolbar();
            this.polylineLayer = L.layerGroup();
            this.markersLayer = L.layerGroup();
        },

        createToolbar() {
            // Buat toolbar untuk measurement
            this.toolbar = document.createElement('div');
            this.toolbar.id = 'measureToolbar';
            this.toolbar.className = 'measure-toolbar hidden';
            this.toolbar.innerHTML = `
                <h4>📏 Alat Ukur</h4>
                <div class="measure-controls">
                    <button class="measure-btn" onclick="PkmKabenApp.MeasurementTool.startMeasuring()">
                        Mulai Pengukuran
                    </button>
                    <button class="measure-btn" onclick="PkmKabenApp.MeasurementTool.clearMeasurement()">
                        Hapus Pengukuran
                    </button>
                    <button class="measure-btn" onclick="PkmKabenApp.MeasurementTool.closeTool()">
                        Tutup Alat
                    </button>
                </div>
                <div id="measureResult" class="measure-result" style="display: none;"></div>
            `;
            document.body.appendChild(this.toolbar);

            // Tambahkan tombol toggle di control panel
            this.createToggleButton();
        },

        createToggleButton() {
            const toggleBtn = document.createElement('button');
            toggleBtn.id = 'measureToggle';
            toggleBtn.className = 'map-control-btn';
            toggleBtn.innerHTML = '📏 Ukur Jarak';
            toggleBtn.onclick = this.toggleTool.bind(this);

            const controlPanel = document.getElementById('controlPanel');
            if (controlPanel) {
                controlPanel.appendChild(toggleBtn);
            }
        },

        toggleTool() {
            const toolbar = document.getElementById('measureToolbar');
            const btn = document.getElementById('measureToggle');
            
            if (toolbar.classList.contains('hidden')) {
                toolbar.classList.remove('hidden');
                btn.classList.add('active');
            } else {
                toolbar.classList.add('hidden');
                btn.classList.remove('active');
                this.stopMeasuring();
            }
        },

        closeTool() {
            this.toggleTool();
        },

        startMeasuring() {
            if (this.active) {
                this.stopMeasuring();
                return;
            }

            this.active = true;
            this.points = [];
            this.clearLayers();

            // Update UI
            const startBtn = this.toolbar.querySelector('.measure-btn');
            startBtn.textContent = 'Berhenti Ukur (ESC)';
            startBtn.classList.add('active');

            // Add map event listeners
            App.state.map.on('click', this.onMapClick, this);
            document.addEventListener('keydown', this.onKeyDown, this);

            // Add layers to map
            App.state.map.addLayer(this.polylineLayer);
            App.state.map.addLayer(this.markersLayer);

            this.showToast('Klik pada peta untuk mulai mengukur jarak', 'info');
        },

        stopMeasuring() {
            this.active = false;

            // Update UI
            const startBtn = this.toolbar.querySelector('.measure-btn');
            startBtn.textContent = 'Mulai Pengukuran';
            startBtn.classList.remove('active');

            // Remove event listeners
            App.state.map.off('click', this.onMapClick, this);
            document.removeEventListener('keydown', this.onKeyDown, this);
        },

        onMapClick(e) {
            if (!this.active) return;

            const point = [e.latlng.lng, e.latlng.lat]; // GeoJSON format [lon, lat]
            this.points.push(point);

            // Add marker
            const marker = L.circleMarker([e.latlng.lat, e.latlng.lng], {
                radius: 6,
                fillColor: '#3b82f6',
                color: 'white',
                weight: 2,
                opacity: 1,
                fillOpacity: 0.8
            });
            this.markersLayer.addLayer(marker);

            // Update polyline and calculate distance
            if (this.points.length >= 2) {
                this.updatePolyline();
                this.calculateTotalDistance();
            }
        },

        onKeyDown(e) {
            if (e.key === 'Escape') {
                this.stopMeasuring();
            }
        },

        updatePolyline() {
            // Convert points to Leaflet format [lat, lng]
            const leafletPoints = this.points.map(p => [p[1], p[0]]);

            // Clear existing polyline
            this.polylineLayer.clearLayers();

            // Add new polyline
            const polyline = L.polyline(leafletPoints, {
                color: '#3b82f6',
                weight: 3,
                opacity: 0.8
            });
            this.polylineLayer.addLayer(polyline);
        },

        calculateTotalDistance() {
            if (this.points.length < 2) return;

            try {
                const line = turf.lineString(this.points);
                const distance = turf.length(line, { units: 'meters' });
                
                this.showResult(distance);
            } catch (error) {
                console.error('Distance calculation error:', error);
            }
        },

        showResult(distanceMeters) {
            const resultDiv = document.getElementById('measureResult');
            
            let distanceText;
            if (distanceMeters < 1000) {
                distanceText = `${Math.round(distanceMeters)} meter`;
            } else {
                distanceText = `${(distanceMeters / 1000).toFixed(2)} kilometer`;
            }

            resultDiv.innerHTML = `
                <strong>Total Jarak:</strong> ${distanceText}<br>
                <small>Titik: ${this.points.length}</small>
            `;
            resultDiv.style.display = 'block';
        },

        clearMeasurement() {
            this.points = [];
            this.clearLayers();
            
            const resultDiv = document.getElementById('measureResult');
            if (resultDiv) {
                resultDiv.style.display = 'none';
            }
        },

        clearLayers() {
            if (this.polylineLayer) {
                this.polylineLayer.clearLayers();
            }
            if (this.markersLayer) {
                this.markersLayer.clearLayers();
            }
        },

        showToast(message, type = 'info') {
            // Simple toast notification
            const toast = document.createElement('div');
            toast.className = `map-toast ${type}`;
            toast.textContent = message;
            document.body.appendChild(toast);

            setTimeout(() => {
                document.body.removeChild(toast);
            }, 3000);
        }
    };

    // ====== BUFFER ANALYSIS MODULE ======
    App.BufferAnalysis = {
        active: false,
        selectedBuilding: null,
        bufferLayers: null,
        infoPanel: null,

        init() {
            if (!FEATURES.enable_buffers) return;

            this.bufferLayers = L.layerGroup();
            this.createToggleButton();
        },

        createToggleButton() {
            const toggleBtn = document.createElement('button');
            toggleBtn.id = 'bufferToggle';
            toggleBtn.className = 'map-control-btn';
            toggleBtn.innerHTML = '🎯 Buffer Epi';
            toggleBtn.onclick = this.toggleMode.bind(this);

            const controlPanel = document.getElementById('controlPanel');
            if (controlPanel) {
                controlPanel.appendChild(toggleBtn);
            }
        },

        toggleMode() {
            this.active = !this.active;
            const btn = document.getElementById('bufferToggle');
            
            if (this.active) {
                btn.classList.add('active');
                this.startBufferMode();
            } else {
                btn.classList.remove('active');
                this.stopBufferMode();
            }
        },

        startBufferMode() {
            App.state.map.addLayer(this.bufferLayers);
            App.state.map.on('click', this.onMapClick, this);
            
            App.MeasurementTool.showToast('Klik pada bangunan untuk analisis buffer epidemiologi', 'info');
        },

        stopBufferMode() {
            App.state.map.off('click', this.onMapClick, this);
            this.clearBuffers();
            this.hideInfoPanel();
        },

        onMapClick(e) {
            if (!this.active) return;

            // Cari building terdekat dari klik
            const clickedBuilding = this.findNearestBuilding(e.latlng);
            
            if (clickedBuilding) {
                this.selectedBuilding = clickedBuilding;
                this.createBuffers(clickedBuilding);
                this.analyzeBuffers(clickedBuilding);
            }
        },

        findNearestBuilding(latlng) {
            if (!App.BboxLoader || !App.BboxLoader.buildingIndex) return null;

            let nearest = null;
            let minDistance = Infinity;

            for (const [buildingNum, feature] of App.BboxLoader.buildingIndex) {
                const [lon, lat] = feature.geometry.coordinates;
                const distance = latlng.distanceTo(L.latLng(lat, lon));
                
                if (distance < minDistance && distance < 50) { // Max 50m radius
                    minDistance = distance;
                    nearest = feature;
                }
            }

            return nearest;
        },

        createBuffers(building) {
            this.clearBuffers();

            const [lon, lat] = building.geometry.coordinates;
            const center = turf.point([lon, lat]);

            // Buffer distances in meters
            const distances = [50, 100, 200];
            const colors = ['#10b981', '#f59e0b', '#ef4444'];

            distances.forEach((distance, index) => {
                try {
                    const buffer = turf.buffer(center, distance, { units: 'meters' });
                    const coords = buffer.geometry.coordinates[0].map(coord => [coord[1], coord[0]]); // Convert to [lat, lng]

                    const circle = L.polygon(coords, {
                        color: colors[index],
                        fillColor: colors[index],
                        fillOpacity: 0.1,
                        weight: 2,
                        dashArray: '5, 5'
                    });

                    this.bufferLayers.addLayer(circle);
                } catch (error) {
                    console.error(`Buffer creation error for ${distance}m:`, error);
                }
            });
        },

        analyzeBuffers(centerBuilding) {
            if (!App.BboxLoader || !App.BboxLoader.buildingIndex) return;

            const [centerLon, centerLat] = centerBuilding.geometry.coordinates;
            const centerPoint = turf.point([centerLon, centerLat]);

            const bufferStats = {
                50: 0,
                100: 0,
                200: 0,
                total: 0
            };

            // Count buildings in each buffer
            for (const [buildingNum, feature] of App.BboxLoader.buildingIndex) {
                if (feature === centerBuilding) continue; // Skip center building

                const [lon, lat] = feature.geometry.coordinates;
                const buildingPoint = turf.point([lon, lat]);
                
                try {
                    const distance = turf.distance(centerPoint, buildingPoint, { units: 'meters' });
                    
                    if (distance <= 200) {
                        bufferStats[200]++;
                        bufferStats.total++;
                        
                        if (distance <= 100) {
                            bufferStats[100]++;
                            
                            if (distance <= 50) {
                                bufferStats[50]++;
                            }
                        }
                    }
                } catch (error) {
                    console.error('Distance calculation error:', error);
                }
            }

            this.showBufferInfo(centerBuilding, bufferStats);
        },

        showBufferInfo(building, stats) {
            // Remove existing info panel
            this.hideInfoPanel();

            // Create info panel
            this.infoPanel = document.createElement('div');
            this.infoPanel.className = 'buffer-info';
            this.infoPanel.innerHTML = `
                <h5>🎯 Analisis Buffer Epidemiologi</h5>
                <p><strong>Pusat:</strong> Bangunan #${building.properties.building_number}</p>
                <div class="buffer-stats">
                    <div class="buffer-stat">
                        <div class="value" style="color: #10b981">${stats[50]}</div>
                        <div class="label">Dalam 50m</div>
                    </div>
                    <div class="buffer-stat">
                        <div class="value" style="color: #f59e0b">${stats[100]}</div>
                        <div class="label">Dalam 100m</div>
                    </div>
                    <div class="buffer-stat">
                        <div class="value" style="color: #ef4444">${stats[200]}</div>
                        <div class="label">Dalam 200m</div>
                    </div>
                    <div class="buffer-stat">
                        <div class="value">${stats.total}</div>
                        <div class="label">Total Rumah</div>
                    </div>
                </div>
                <button onclick="PkmKabenApp.BufferAnalysis.clearBuffers()" 
                        style="margin-top: 8px; padding: 6px 12px; background: #64748b; color: white; border: none; border-radius: 4px; cursor: pointer;">
                    Hapus Buffer
                </button>
            `;

            document.body.appendChild(this.infoPanel);
        },

        hideInfoPanel() {
            if (this.infoPanel) {
                document.body.removeChild(this.infoPanel);
                this.infoPanel = null;
            }
        },

        clearBuffers() {
            if (this.bufferLayers) {
                this.bufferLayers.clearLayers();
            }
            this.hideInfoPanel();
            this.selectedBuilding = null;
        }
    };

    // ====== NEAREST NEIGHBORS MODULE ======
    App.NearestNeighbors = {
        active: false,
        selectedBuilding: null,
        nearestLayers: null,
        k: 5, // Number of nearest neighbors

        init() {
            if (!FEATURES.enable_nearest) return;

            this.nearestLayers = L.layerGroup();
            this.createToggleButton();
        },

        createToggleButton() {
            const toggleBtn = document.createElement('button');
            toggleBtn.id = 'nearestToggle';
            toggleBtn.className = 'map-control-btn';
            toggleBtn.innerHTML = '👥 Tetangga 5';
            toggleBtn.onclick = this.toggleMode.bind(this);

            const controlPanel = document.getElementById('controlPanel');
            if (controlPanel) {
                controlPanel.appendChild(toggleBtn);
            }
        },

        toggleMode() {
            this.active = !this.active;
            const btn = document.getElementById('nearestToggle');
            
            if (this.active) {
                btn.classList.add('active');
                this.startNearestMode();
            } else {
                btn.classList.remove('active');
                this.stopNearestMode();
            }
        },

        startNearestMode() {
            App.state.map.addLayer(this.nearestLayers);
            App.state.map.on('click', this.onMapClick, this);
            
            App.MeasurementTool.showToast('Klik pada bangunan untuk melihat 5 tetangga terdekat', 'info');
        },

        stopNearestMode() {
            App.state.map.off('click', this.onMapClick, this);
            this.clearNearest();
        },

        onMapClick(e) {
            if (!this.active) return;

            const clickedBuilding = App.BufferAnalysis.findNearestBuilding(e.latlng);
            
            if (clickedBuilding) {
                this.selectedBuilding = clickedBuilding;
                this.findAndShowNearest(clickedBuilding);
            }
        },

        findAndShowNearest(centerBuilding) {
            if (!App.BboxLoader || !App.BboxLoader.buildingIndex) return;

            this.clearNearest();

            const [centerLon, centerLat] = centerBuilding.geometry.coordinates;
            const centerPoint = turf.point([centerLon, centerLat]);

            const neighbors = [];

            // Calculate distances to all other buildings
            for (const [buildingNum, feature] of App.BboxLoader.buildingIndex) {
                if (feature === centerBuilding) continue;

                const [lon, lat] = feature.geometry.coordinates;
                const buildingPoint = turf.point([lon, lat]);
                
                try {
                    const distance = turf.distance(centerPoint, buildingPoint, { units: 'meters' });
                    
                    neighbors.push({
                        feature: feature,
                        distance: distance,
                        coordinates: [lat, lon] // Leaflet format
                    });
                } catch (error) {
                    console.error('Distance calculation error:', error);
                }
            }

            // Sort by distance and take k nearest
            neighbors.sort((a, b) => a.distance - b.distance);
            const nearest = neighbors.slice(0, this.k);

            // Show nearest neighbors
            this.visualizeNearest(centerBuilding, nearest);
        },

        visualizeNearest(centerBuilding, nearest) {
            const [centerLat, centerLon] = [centerBuilding.geometry.coordinates[1], centerBuilding.geometry.coordinates[0]];

            // Add center marker
            const centerMarker = L.marker([centerLat, centerLon], {
                icon: L.divIcon({
                    html: '<div class="nearest-marker" style="background: #ef4444;">🏠</div>',
                    className: 'nearest-marker-container',
                    iconSize: [30, 30],
                    iconAnchor: [15, 15]
                })
            }).bindPopup(`
                <strong>Pusat Analisis</strong><br>
                Bangunan #${centerBuilding.properties.building_number}
            `);
            this.nearestLayers.addLayer(centerMarker);

            // Add nearest neighbors
            nearest.forEach((neighbor, index) => {
                const [lat, lon] = neighbor.coordinates;
                
                // Add connecting line
                const line = L.polyline([[centerLat, centerLon], [lat, lon]], {
                    color: '#8b5cf6',
                    weight: 2,
                    opacity: 0.7,
                    dashArray: '5, 5'
                });
                this.nearestLayers.addLayer(line);

                // Add neighbor marker
                const marker = L.marker([lat, lon], {
                    icon: L.divIcon({
                        html: `<div class="nearest-marker">${index + 1}</div>`,
                        className: 'nearest-marker-container',
                        iconSize: [24, 24],
                        iconAnchor: [12, 12]
                    })
                }).bindPopup(`
                    <strong>Tetangga #${index + 1}</strong><br>
                    Bangunan #${neighbor.feature.properties.building_number}<br>
                    <small>Jarak: ${Math.round(neighbor.distance)} meter</small>
                `);
                this.nearestLayers.addLayer(marker);
            });

            // Show summary
            const avgDistance = nearest.reduce((sum, n) => sum + n.distance, 0) / nearest.length;
            App.MeasurementTool.showToast(
                `Menampilkan ${nearest.length} tetangga terdekat. Jarak rata-rata: ${Math.round(avgDistance)}m`, 
                'success'
            );
        },

        clearNearest() {
            if (this.nearestLayers) {
                this.nearestLayers.clearLayers();
            }
            this.selectedBuilding = null;
        }
    };

    // ====== INITIALIZATION ======
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            App.MeasurementTool.init();
            App.BufferAnalysis.init();
            App.NearestNeighbors.init();
            
            console.log('Map Tools initialized');
        }, 1500);
    });

    // Expose untuk debugging
    window.MapTools = {
        MeasurementTool: App.MeasurementTool,
        BufferAnalysis: App.BufferAnalysis,
        NearestNeighbors: App.NearestNeighbors
    };

})();
