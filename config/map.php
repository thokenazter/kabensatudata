<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Map Feature Flags
    |--------------------------------------------------------------------------
    |
    | These flags control which map features are enabled. Each feature can be
    | individually controlled to allow gradual rollout and easy rollback.
    |
    */

    // Base map configuration
    'use_vector_tiles' => env('MAP_USE_VECTOR_TILES', false),
    'vector_style_url' => env('MAP_STYLE_URL', 'https://demotiles.maplibre.org/style.json'),
    
    // Performance features
    'enable_bbox_sync' => env('MAP_ENABLE_BBOX_SYNC', true),
    'bbox_debounce_ms' => env('MAP_BBOX_DEBOUNCE_MS', 250),
    'max_features_per_request' => env('MAP_MAX_FEATURES', 5000),
    
    // Interactive features
    'enable_search_building' => env('MAP_ENABLE_SEARCH_BUILDING', true),
    'enable_house_labels' => env('MAP_ENABLE_HOUSE_LABELS', true),
    'enable_measure' => env('MAP_ENABLE_MEASURE', true),
    'enable_buffers' => env('MAP_ENABLE_BUFFERS', true),
    'enable_nearest' => env('MAP_ENABLE_NEAREST', true),
    
    // Advanced features  
    'enable_heatmap' => env('MAP_ENABLE_HEATMAP', false),
    'enable_offline' => env('MAP_ENABLE_OFFLINE', false),
    
    // External services
    'osrm_url' => env('MAP_OSRM_URL', 'https://router.project-osrm.org'),
    
    // Map settings
    'default_center' => [
        'lat' => env('MAP_DEFAULT_LAT', -5.7465),
        'lng' => env('MAP_DEFAULT_LNG', 134.797032),
    ],
    'default_zoom' => env('MAP_DEFAULT_ZOOM', 15),
    'max_zoom' => env('MAP_MAX_ZOOM', 25),
    
    // Leaflet tuning for smooth experience
    'leaflet_options' => [
        'detect_retina' => true,
        'max_native_zoom' => 19,
        'update_when_interacting' => true,
        'keep_buffer' => 4,
        'zoom_snap' => 0.25,
        'zoom_delta' => 0.5,
        'wheel_px_per_zoom_level' => 80,
        'inertia' => true,
        'inertia_deceleration' => 3000,
    ],
];
