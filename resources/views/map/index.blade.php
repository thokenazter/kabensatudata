<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PKM Kaben - Pemetaan Kesehatan</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet.locatecontrol@0.79.0/dist/L.Control.Locate.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
    
    <!-- PHP untuk cek status login -->
    @php
        // Cek apakah user sudah login
        $isLoggedIn = auth()->check();
    @endphp
    
    <!-- CSS akan ditambahkan pada bagian selanjutnya -->
    <style>
        /* Reset dan Layout Dasar */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.5;
            color: #1e293b;
        }
        
        #mapContainer { 
            position: absolute;
            top: 64px;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100%;
            z-index: 1;
        }
    
        /* Navbar Styles */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: white;
            padding: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            z-index: 1001;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 64px;
        }
    
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
            background: linear-gradient(to right, #2563eb, #4f46e5);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            transition: all 0.3s ease;
            margin-left: 0.75rem;
            text-decoration: none;
        }
    
        .navbar-brand:hover {
            background: linear-gradient(to right, #4f46e5, #2563eb);
            -webkit-background-clip: text;
            background-clip: text;
        }
    
        .navbar-links {
            display: flex;
            gap: 1.5rem;
            margin-right: 1rem;
        }
    
        .navbar-links a {
            color: #1e293b;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            transition: all 0.3s ease;
            font-weight: 500;
        }
    
        .navbar-links a:hover, .navbar-links a.active {
            background: linear-gradient(to right, #2563eb, #4f46e5);
            color: white;
        }
    
        .navbar-toggle {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
            color: #1e293b;
        }
    
        /* Mobile Responsive Navbar */
        @media screen and (max-width: 768px) {
            .navbar-toggle {
                display: block;
                margin-right: 0.75rem;
            }
    
            .navbar-links {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: white;
                flex-direction: column;
                padding: 1rem;
                gap: 0.5rem;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                margin-right: 0;
            }
    
            .navbar-links.active {
                display: flex;
            }
    
            .navbar-links a {
                padding: 0.75rem 1rem;
                width: 100%;
                text-align: left;
            }
    
            #mapContainer {
                top: 56px;
            }
            
            .navbar {
                height: 56px;
            }
            
            .control-panel {
                width: 80%;
                max-width: none;
            }
            
            .map-left-controls {
                top: 110px; /* Adjusted for mobile view */
            }
        }
    
        /* Control Panel Styles */
        .control-panel {
            position: absolute;
            top: 200px;
            right: 20px;
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            z-index: 1000;
            max-width: 300px;
            width: 90%;
            transition: all 0.3s ease;
        }
    
        .panel-tabs {
            display: flex;
            margin-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }
    
        .panel-tab {
            padding: 8px 15px;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.3s ease;
        }
    
        .panel-tab.active {
            border-bottom: 2px solid #3498db;
            color: #3498db;
            font-weight: 500;
        }
    
        .panel-content {
            display: none;
        }
    
        .panel-content.active {
            display: block;
        }
    
        /* Search Panel Styles */
        .search-container {
            margin-bottom: 15px;
        }
    
        .search-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }
    
        .search-filter {
            margin-bottom: 15px;
        }
    
        .search-filter select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            background-color: white;
        }
    
        .search-results {
            max-height: 300px;
            overflow-y: auto;
            margin-top: 10px;
        }
    
        .search-result-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
    
        .search-result-item:hover {
            background-color: #f5f8ff;
        }
    
        .search-result-item:last-child {
            border-bottom: none;
        }
    
        .search-result-title {
            font-weight: bold;
            margin-bottom: 3px;
        }
    
        .search-result-subtitle {
            font-size: 12px;
            color: #666;
        }
    
        .no-results {
            text-align: center;
            padding: 15px;
            color: #666;
            font-style: italic;
        }
    
        /* Analysis Panel Styles */
        .analysis-options {
            margin-bottom: 15px;
        }
    
        .analysis-option {
            margin-bottom: 10px;
        }
    
        .analysis-option label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
    
        .analysis-option select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            background-color: white;
        }
    
        .heatmap-intensity {
            margin-top: 15px;
        }
    
        .heatmap-intensity label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
    
        .heatmap-intensity input {
            width: 100%;
        }
    
        .heatmap-legend {
            margin-top: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
    
        .heatmap-legend-title {
            font-weight: bold;
            margin-bottom: 8px;
        }
    
        .heatmap-legend-items {
            display: flex;
            justify-content: space-between;
        }
    
        .heatmap-legend-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            font-size: 12px;
        }
    
        .heatmap-legend-color {
            width: 20px;
            height: 20px;
            margin-bottom: 3px;
            border-radius: 3px;
        }
        
        .heatmap-stats {
            margin-top: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 6px;
        }
        
        .heatmap-stats h4 {
            margin-top: 0;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 14px;
        }
        
        .stat-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 13px;
        }
        
        .stat-value {
            font-weight: 500;
        }
    
        /* House Marker Styles */
        .house-marker {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 8px;
            border-radius: 8px;
            color: white;
            font-weight: bold;
            font-size: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
            position: relative;
        }
    
        .house-marker:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }
    
        .house-marker span {
            margin-top: 4px;
        }
        
        /* Status-based Styles */
        .status-healthy {
            background-color: #10b981 !important;
            border: 2px solid #15803d;
        }
        
        .status-pra-healthy {
            background-color: #f59e0b !important;
            border: 2px solid #d97706;
        }
        
        .status-unhealthy {
            background-color: #ef4444 !important;
            border: 2px solid #b91c1c;
        }
        
        .status-unknown {
            background-color: #3498db !important;
            border: 2px solid #2563eb;
        }
        
        /* Priority Marker Styles */
        .marker-priority-icon {
            position: absolute;
            top: -8px;
            right: -8px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background-color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        }
        
        .marker-priority-icon svg {
            color: #2563eb;
        }
        
        .marker-priority-icon.pregnant svg {
            color: #ec4899;
        }
        
        .marker-priority-icon.elderly svg {
            color: #8b5cf6;
        }
        
        .marker-priority-icon.infant svg {
            color: #06b6d4;
        }
        
        .marker-priority-icon.general svg {
            color: #f59e0b;
        }
        
        /* Needs Visit Badge */
        .needs-visit-badge {
            position: absolute;
            top: -5px;
            left: -5px;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background-color: #ef4444;
            border: 2px solid white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
            animation: pulse 1.5s infinite;
        }
    
        /* Priority Marker Styles */
        .marker-priority {
            z-index: 1000 !important;
            transform: scale(1.2);
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.7);
        }
    
        /* Pulse Animation for Priority Markers */
        .marker-pulse {
            animation: pulse 1.5s infinite;
        }
    
        @keyframes pulse {
            0% {
                transform: scale(1.2);
                box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.7);
            }
            70% {
                transform: scale(1.3);
                box-shadow: 0 0 0 10px rgba(255, 255, 255, 0);
            }
            100% {
                transform: scale(1.2);
                box-shadow: 0 0 0 0 rgba(255, 255, 255, 0);
            }
        }
    
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            overflow-y: auto;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .modal.show {
            opacity: 1;
            display: block;
        }
        
        /* Toggle Panel Button */
        .toggle-panel-btn {
            background: white;
            border: 2px solid rgba(0,0,0,0.2);
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1e293b;
            width: 34px;
            height: 34px;
            padding: 0;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: none;
            z-index: 900;
            background-clip: padding-box;
        }
        
        .toggle-panel-btn svg {
            width: 18px;
            height: 18px;
        }
        
        .toggle-panel-btn:hover {
            background-color: #f4f4f4;
        }
        
        .toggle-panel-btn.active {
            background-color: #ebebeb;
            color: #2074B6;
            border-color: rgba(0,0,0,0.3);
        }
        
        .toggle-panel-icon {
            transition: transform 0.3s ease;
        }
        
        .toggle-panel-icon.rotated {
            transform: rotate(-90deg);
        }
        
        /* Left Side Controls */
        .map-left-controls {
            position: absolute;
            top: 180px; /* Adjusted to be below Leaflet default controls */
            left: 10px;
            z-index: 800;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        @media screen and (max-width: 768px) {
            .map-left-controls {
                top: 180px;
            }
            
            .toggle-panel-btn {
                padding: 8px;
            }
            
            .toggle-panel-btn svg {
                width: 20px;
                height: 20px;
            }
        }
        
        /* Control Panel Toggle Styles */
        .control-panel {
            transition: transform 0.3s ease, opacity 0.3s ease;
            transform-origin: top right;
        }
        
        .control-panel.panel-hidden {
            transform: translateX(100%);
            opacity: 0;
            pointer-events: none;
        }
        
        /* Mobile Responsive Control Panel */
        @media screen and (max-width: 768px) {
            .control-panel {
                width: 80%;
                max-width: none;
            }
        }
    
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 800px;
            border-radius: 8px;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            transform: translateY(-20px);
            opacity: 0;
            transition: all 0.3s ease;
        }
        
        .modal.show .modal-content {
            transform: translateY(0);
            opacity: 1;
        }
    
        .close {
            position: absolute;
            right: 20px;
            top: 15px;
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.2s ease;
        }
    
        .close:hover {
            color: #000;
        }
    
        /* Detail Components */
        .detail-section {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 8px;
        }
    
        .family-card {
            border: 1px solid #ddd;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            background-color: white;
            transition: all 0.3s ease;
        }
        
        .family-card:hover {
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    
        .status-indicator {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 500;
            margin: 2px;
        }
    
        .status-yes {
            background-color: #d1fae5;
            color: #065f46;
        }
    
        .status-no {
            background-color: #fee2e2;
            color: #991b1b;
        }
    
        /* Table Styles */
        .family-members-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background-color: white;
        }
    
        .family-members-table th,
        .family-members-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
    
        .family-members-table th {
            background-color: #f3f4f6;
            font-weight: 600;
        }
        
        /* Responsive Table Styles untuk Mobile */
        @media screen and (max-width: 768px) {
            .family-members-table {
                border: 0;
            }
            
            .family-members-table thead {
                display: none; /* Sembunyikan header pada tampilan mobile */
            }
            
            .family-members-table tbody tr {
                display: block;
                margin-bottom: 15px;
                border: 1px solid #ddd;
                border-radius: 6px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            
            .family-members-table td {
                display: flex;
                padding: 8px 10px;
                text-align: left;
                border: none;
                border-bottom: 1px solid #eee;
            }
            
            .family-members-table td:last-child {
                border-bottom: 0;
            }
            
            .family-members-table td:before {
                content: attr(data-label);
                font-weight: 600;
                width: 40%;
                margin-right: 10px;
            }
            
            /* Tambahkan gaya agar kartu anggota keluarga lebih menarik */
            .family-members-table tr:nth-child(even) {
                background-color: #f9fafb;
            }
            
            .family-members-table tr {
                transition: all 0.3s ease;
            }
            
            .family-members-table tr:hover {
                background-color: #f0f9ff;
            }
            
            /* Toggle untuk memperluas/menciutkan daftar anggota pada mobile */
            .members-toggle {
                display: block;
                width: 100%;
                background: #f3f4f6;
                color: #4b5563;
                border: 1px solid #ddd;
                border-radius: 6px;
                padding: 8px 12px;
                margin-top: 10px;
                margin-bottom: 5px;
                font-weight: 500;
                text-align: center;
                cursor: pointer;
                transition: all 0.3s ease;
            }
            
            .members-toggle:hover {
                background: #e5e7eb;
            }
            
            .members-toggle-icon {
                display: inline-block;
                margin-left: 5px;
                transition: transform 0.3s ease;
            }
            
            .members-toggle.collapsed .members-toggle-icon {
                transform: rotate(180deg);
            }
            
            .members-container {
                max-height: 1000px;
                overflow: hidden;
                transition: max-height 0.5s ease;
            }
            
            .members-container.collapsed {
                max-height: 0;
            }
        }
    
        /* Mobile version of member cards */
        .member-card {
            border: 1px solid #eee;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 6px;
        }
    
        .member-info {
            margin-top: 5px;
            font-size: 13px;
        }
    
        .member-info p {
            margin: 3px 0;
        }
    
        /* Custom Popup Styles */
        .custom-popup {
            padding: 10px;
            max-width: 320px;
            font-size: 14px;
        }
    
        .popup-buttons {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 10px;
        }
    
        .popup-button {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .popup-button svg {
            margin-right: 5px;
        }
    
        .detail-button {
            background-color: #3498db;
            color: white;
        }
    
        .detail-button:hover {
            background-color: #2980b9;
        }
    
        .route-button {
            background-color: #2ecc71;
            color: white;
        }
    
        .route-button:hover {
            background-color: #27ae60;
        }

        /* Enhanced Popup Styles */
        .popup-header {
            border-bottom: 1px solid #eee;
            margin-bottom: 10px;
            padding-bottom: 8px;
        }
        
        .popup-header h3 {
            margin: 0 0 5px 0;
            font-size: 16px;
            color: #333;
            display: flex;
            align-items: center;
        }
        
        .popup-header h3 svg {
            margin-right: 5px;
        }
        
        .popup-header p {
            margin: 0;
            font-size: 13px;
            color: #666;
            display: flex;
            align-items: center;
        }
        
        .popup-header p svg {
            margin-right: 5px;
        }
        
        .family-list {
            margin-bottom: 10px;
        }
        
        .family-item {
            padding: 6px 0;
            border-bottom: 1px dashed #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .family-item:last-child {
            border-bottom: none;
        }
        
        .family-name {
            display: flex;
            align-items: center;
            font-weight: 500;
        }
        
        .family-name svg {
            margin-right: 5px;
            color: #3498db;
        }
        
        .member-count {
            background-color: #f1f5f9;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 12px;
            color: #64748b;
        }
        
        .total-members {
            background-color: #e8f4fd;
            padding: 8px;
            margin: 10px 0;
            border-radius: 6px;
            font-weight: 500;
            color: #2c3e50;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
    
        /* Route Summary Styles */
        .route-summary {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .route-summary h4 {
            margin: 0 0 8px 0;
            color: #2c3e50;
        }
        
        .route-summary p {
            margin: 4px 0;
            color: #34495e;
        }
    
        .route-steps {
            list-style: none;
            padding: 0;
        }
    
        .route-steps li {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
    
        .route-steps li:last-child {
            border-bottom: none;
        }
    
        /* Loading indicators */
        .route-loading {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            padding: 10px 15px;
            border-radius: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            display: none;
            z-index: 1000;
            align-items: center;
            gap: 10px;
        }
        
        .route-loading.active {
            display: flex;
        }
        
        .progress-indicator {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.2);
            display: none;
            z-index: 1000;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }
        
        .progress-indicator.active {
            display: flex;
        }
        
        .spinner {
            width: 24px;
            height: 24px;
            border: 3px solid rgba(0, 0, 0, 0.1);
            border-radius: 50%;
            border-top-color: #3498db;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    
        /* Responsive adjustments */
        @media screen and (max-width: 768px) {
            .control-panel {
                top: auto;
                bottom: 20px;
                left: 20px;
                right: 20px;
                width: auto;
                max-width: none;
            }
            
            .modal-content {
                width: 95%;
                margin: 10% auto;
            }
        }

        /* Enhanced Popup Styles - Responsive */
        @media screen and (max-width: 768px) {
            .custom-popup {
                max-width: 280px;
                font-size: 13px;
                padding: 8px;
            }
            
            .popup-header h3 {
                font-size: 15px;
            }
            
            .popup-header p {
                font-size: 12px;
            }
            
            .family-item {
                padding: 5px 0;
            }
            
            .member-count {
                font-size: 11px;
                padding: 1px 5px;
            }
            
            .total-members {
                padding: 6px;
                font-size: 13px;
            }
            
            .popup-button {
                padding: 6px 10px;
                font-size: 13px;
            }
            
            .popup-button svg {
                width: 14px;
                height: 14px;
            }
        }

        /* Smart Assistant Styling */
        .smart-assistant-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .smart-assistant-toggle {
            background-color: #2563eb;
            color: white;
            border: none;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .smart-assistant-toggle:hover {
            background-color: #1d4ed8;
            transform: scale(1.05);
        }

        .smart-assistant-panel {
            position: absolute;
            bottom: 75px;
            right: 0;
            width: 350px;
            max-height: 500px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transform: scale(0);
            transform-origin: bottom right;
            transition: transform 0.3s ease;
            opacity: 0;
        }

        .smart-assistant-panel.active {
            transform: scale(1);
            opacity: 1;
        }

        .smart-assistant-header {
            padding: 15px;
            background-color: #2563eb;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .smart-assistant-header h3 {
            margin: 0;
            font-size: 18px;
        }

        .smart-assistant-close {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
        }

        .smart-assistant-messages {
            padding: 15px;
            flex-grow: 1;
            overflow-y: auto;
            height: 360px;
            background-color: #f8fafc;
        }

        .user-message, .assistant-message {
            margin-bottom: 15px;
            padding: 10px 15px;
            border-radius: 18px;
            max-width: 85%;
            word-wrap: break-word;
        }

        .user-message {
            background-color: #2563eb;
            color: white;
            align-self: flex-end;
            margin-left: auto;
            border-bottom-right-radius: 4px;
        }

        .assistant-message {
            background-color: #e2e8f0;
            color: #1e293b;
            align-self: flex-start;
            border-bottom-left-radius: 4px;
        }

        .smart-assistant-input {
            display: flex;
            border-top: 1px solid #e2e8f0;
            padding: 10px;
        }

        .smart-assistant-input input {
            flex-grow: 1;
            border: 1px solid #cbd5e1;
            border-radius: 20px;
            padding: 8px 15px;
            outline: none;
        }

        .smart-assistant-input button {
            background-color: #2563eb;
            color: white;
            border: none;
            border-radius: 20px;
            padding: 8px 15px;
            margin-left: 10px;
            cursor: pointer;
        }

        .smart-assistant-input button:hover {
            background-color: #1d4ed8;
        }

        /* Quick Suggestions Styling */
        .quick-suggestions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
        }

        .suggestion-btn {
            background-color: #dbeafe;
            color: #1e40af;
            border: none;
            border-radius: 16px;
            padding: 6px 12px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .suggestion-btn:hover {
            background-color: #bfdbfe;
        }

        .followup-suggestions {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 6px;
        }

        /* Typing Indicator Animation */
        .typing-indicator {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 5px;
            padding: 5px 0;
        }

        .typing-indicator span {
            height: 8px;
            width: 8px;
            background-color: #94a3b8;
            border-radius: 50%;
            display: inline-block;
            animation: typingBounce 1.4s infinite ease-in-out both;
        }

        .typing-indicator span:nth-child(1) {
            animation-delay: -0.32s;
        }

        .typing-indicator span:nth-child(2) {
            animation-delay: -0.16s;
        }

        @keyframes typingBounce {
            0%, 80%, 100% { 
                transform: scale(0);
            } 
            40% { 
                transform: scale(1);
            }
        }

        /* Comparison Table Styling */
        .comparison-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 13px;
        }

        .comparison-table th, .comparison-table td {
            border: 1px solid #cbd5e1;
            padding: 6px;
            text-align: center;
        }

        .comparison-table th {
            background-color: #e2e8f0;
            font-weight: bold;
        }

        /* Responsive Adjustments */
        @media (max-width: 576px) {
            .smart-assistant-panel {
                width: calc(100vw - 40px);
                max-height: 450px;
            }
            
            .smart-assistant-container {
                bottom: 10px;
                right: 10px;
            }
        }

        /* Health Dashboard Styles */
        .health-dashboard {
            position: absolute;
            top: 80px;
            left: 60px;
            width: 320px;
            max-height: 80vh;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.15);
            z-index: 999;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .health-dashboard.collapsed {
            width: 50px;
            overflow: hidden;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: linear-gradient(90deg, #4f46e5, #2563eb);
            color: white;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        .dashboard-header h3 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
        }

        .toggle-dashboard-btn {
            background: none;
            border: none;
            color: white;
            padding: 0;
            cursor: pointer;
            transition: transform 0.3s;
        }

        .toggle-dashboard-btn.collapsed {
            transform: rotate(180deg);
        }

        .dashboard-content {
            padding: 15px;
            overflow-y: auto;
            max-height: calc(80vh - 55px);
        }

        .dashboard-section {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .dashboard-section:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .dashboard-section h4 {
            margin: 0 0 15px 0;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
        }

        /* IKS Chart Styles */
        .iks-chart-container {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .iks-summary {
            margin-left: 15px;
        }

        .iks-avg {
            display: flex;
            flex-direction: column;
            text-align: center;
        }

        .iks-label {
            font-size: 12px;
            color: #6b7280;
        }

        .iks-value {
            font-size: 24px;
            font-weight: 700;
            color: #2563eb;
        }

        /* Family Health Categories */
        .family-health-categories {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-top: 10px;
        }

        .health-category {
            flex: 1;
            padding: 10px;
            border-radius: 6px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .category-label {
            font-size: 11px;
            margin-bottom: 5px;
            color: white;
        }

        .category-value {
            font-size: 16px;
            font-weight: 700;
            color: white;
        }

        .healthy {
            background-color: #10b981;
        }

        .pra-healthy {
            background-color: #f59e0b;
        }

        .unhealthy {
            background-color: #ef4444;
        }

        /* Indicators Chart */
        .indicators-chart-container {
            height: 150px;
        }

        /* Area Stats */
        .area-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .area-stats .stat-item {
            flex: 1 1 calc(50% - 10px);
            min-width: 120px;
            background-color: #f9fafb;
            border-radius: 6px;
            padding: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .stat-icon {
            padding: 8px;
            border-radius: 50%;
            background-color: #e5e7eb;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 5px;
        }

        .stat-icon svg {
            color: #4b5563;
        }

        .stat-label {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 3px;
        }

        .stat-value {
            font-size: 15px;
            font-weight: 600;
            color: #1f2937;
        }

        /* Priority Visits */
        .priority-visits {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .priority-type {
            flex: 1;
            padding: 10px;
            border-radius: 6px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .priority-count {
            font-size: 20px;
            font-weight: 700;
            color: white;
        }

        .priority-label {
            font-size: 10px;
            color: white;
            text-align: center;
            margin-top: 5px;
        }

        .high {
            background-color: #ef4444;
        }

        .medium {
            background-color: #f59e0b;
        }

        .low {
            background-color: #10b981;
        }

        /* Responsive for dashboard */
        @media screen and (max-width: 768px) {
            .health-dashboard {
                left: 20px;
                width: 280px;
                top: 120px;
            }
            
            .health-dashboard.collapsed {
                width: 40px;
                left: 10px;
            }
            
            .dashboard-header {
                padding: 10px;
            }
            
            .dashboard-content {
                padding: 10px;
            }
            
            .iks-avg .iks-value {
                font-size: 20px;
            }
            
            .area-stats .stat-item {
                flex: 1 1 100%;
            }
        }

        /* Styling untuk Panel Koordinat */
        .coordinate-panel {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 300px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
            z-index: 1000;
            transition: all 0.3s ease;
            transform: translateY(calc(100% + 20px));
        }
        
        .coordinate-panel.active {
            transform: translateY(0);
        }
        
        .coordinate-panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 15px;
            background: linear-gradient(90deg, #4f46e5, #2563eb);
            color: white;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }
        
        .coordinate-panel-header h3 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
        }
        
        .coordinate-panel-header button {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            width: 24px;
            height: 24px;
            line-height: 24px;
            text-align: center;
        }
        
        .coordinate-panel-content {
            padding: 15px;
        }
        
        .coordinate-input-group {
            margin-bottom: 12px;
        }
        
        .coordinate-input-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            font-size: 14px;
            color: #4b5563;
        }
        
        .coordinate-input-group input {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .coordinate-btn {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 6px;
            background-color: #2563eb;
            color: white;
            font-weight: 500;
            cursor: pointer;
            margin-bottom: 10px;
            transition: background-color 0.2s ease;
        }
        
        .coordinate-btn:hover {
            background-color: #1d4ed8;
        }
        
        .coordinate-btn.secondary {
            background-color: #6b7280;
        }
        
        .coordinate-btn.secondary:hover {
            background-color: #4b5563;
        }
        
        .coordinate-btn.clear-cache {
            background-color: #dc2626;
        }
        
        .coordinate-btn.clear-cache:hover {
            background-color: #b91c1c;
        }
        
        .coordinate-btn.reload {
            background-color: #059669;
        }
        
        .coordinate-btn.reload:hover {
            background-color: #047857;
        }
        
        .coordinate-result {
            margin-top: 15px;
            padding: 10px;
            border-radius: 6px;
            background-color: #f3f4f6;
            font-size: 14px;
            min-height: 40px;
        }
        
        /* Tombol untuk membuka panel koordinat */
        .coordinate-toggle {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #2563eb;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
            z-index: 999;
            transition: all 0.3s ease;
        }
        
        .coordinate-toggle:hover {
            background: #1d4ed8;
            transform: scale(1.05);
        }
        
        .coordinate-toggle svg {
            width: 24px;
            height: 24px;
        }
        
        @media screen and (max-width: 768px) {
            .coordinate-panel {
                width: calc(100% - 40px);
                left: 20px;
                right: 20px;
            }
            
            .coordinate-toggle {
                bottom: 15px;
                right: 15px;
            }
        }
        
        /* Status validasi koordinat */
        .coordinate-status {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 5px;
            font-size: 13px;
        }
        
        .coordinate-status.valid {
            color: #10b981;
        }
        
        .coordinate-status.invalid {
            color: #ef4444;
        }

        /* Responsive adjustments */
        @media screen and (max-width: 768px) {
            /* Panel kontrol */
            .control-panel {
                top: auto;
                bottom: 20px;
                left: 10px;
                right: 10px;
                width: auto;
                max-width: none;
                max-height: 45vh;
                overflow-y: auto;
            }
            
            /* Modal popup */
            .modal-content {
                width: 95%;
                margin: 10% auto;
                max-height: 80vh;
                overflow-y: auto;
            }
            
            /* Health dashboard */
            .health-dashboard {
                width: 100%;
                left: 0;
                top: auto;
                bottom: 0;
                height: auto;
                max-height: 60vh;
                border-radius: 15px 15px 0 0;
            }
            
            .health-dashboard.collapsed {
                height: 40px;
            }
            
            .toggle-dashboard-btn {
                top: 5px;
            }
            
            /* Navbar dan map */
            #mapContainer {
                top: 56px;
                height: calc(100vh - 56px);
            }
            
            /* Koordinat panel */
            .coordinate-panel {
                width: 90%;
                left: 5%;
                right: 5%;
            }
        }

        /* Responsive styles for tablets */
        @media screen and (min-width: 769px) and (max-width: 1024px) {
            .control-panel {
                width: 300px;
                max-width: 40%;
            }
            
            .health-dashboard {
                width: 350px;
                max-width: 45%;
            }
            
            .modal-content {
                width: 80%;
                max-width: 700px;
            }
        }

        /* Tablet and phone landscape mode */
        @media screen and (max-height: 500px) {
            .control-panel {
                max-height: 80vh;
                overflow-y: auto;
            }
            
            .health-dashboard {
                max-height: 80vh;
                overflow-y: auto;
            }
            
            .modal-content {
                max-height: 90vh;
                overflow-y: auto;
            }
        }
    </style>
    <!-- CSS akan ditambahkan pada bagian selanjutnya -->
</head>
<body>
    <nav class="navbar">
        <a href="/" class="navbar-brand">Dashboard</a>
        <div class="flex items-center">
        <button class="navbar-toggle" id="navbarToggle" aria-label="Toggle navigation">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </button>
        </div>
        <div class="navbar-links" id="navbarLinks">
            <a href="#" data-village-id="1" class="village-link">Kabalsiang</a>
            <a href="#" data-village-id="2" class="village-link">Benjuring</a>
            <a href="#" data-village-id="3" class="village-link">Kompane</a>
            <a href="#" data-village-id="4" class="village-link">Kumul</a>
            <a href="#" data-village-id="5" class="village-link">Batuley</a>
        </div>
    </nav>

    <div id="mapContainer"></div>

    <!-- Left Side Controls -->
    <div class="map-left-controls">
        <button id="togglePanelBtn" class="toggle-panel-btn" aria-label="Toggle Control Panel">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="toggle-panel-icon">
                <path d="M17 10H3"></path>
                <path d="M21 6H3"></path>
                <path d="M21 14H3"></path>
                <path d="M17 18H3"></path>
            </svg>
        </button>
    </div>

    <!-- Panel Pencarian dan Analisis -->
    <div class="control-panel" id="controlPanel">
        <div class="panel-tabs">
            <div class="panel-tab active" data-tab="search">Pencarian</div>
            <div class="panel-tab" data-tab="analysis">Analisis</div>
        </div>

        <!-- Tab Pencarian -->
        <div class="panel-content active" id="search-content">
            <div class="search-container">
                <input type="text" id="searchInput" class="search-input" placeholder="Cari rumah atau nama KK...">
            </div>

            <div class="search-filter">
                <select id="searchFilter">
                    <option value="all">Semua Kategori</option>
                    <option value="building_number">Nomor Rumah</option>
                    <option value="head_name">Kepala Keluarga</option>
                    <option value="village">Desa</option>
                </select>
            </div>

            <div id="searchResults" class="search-results">
                <div class="no-results">Masukkan kata kunci untuk mencari</div>
            </div>
        </div>

        <!-- Tab Analisis -->
        <div class="panel-content" id="analysis-content">
            <div class="analysis-options">
                <div class="analysis-option">
                    <label for="heatmapType">Jenis Visualisasi:</label>
                    <select id="heatmapType">
                        <option value="none">Tidak Ada</option>
                        <option value="tb">Tuberkulosis</option>
                        <option value="hypertension">Hipertensi</option>
                        <option value="mental">Gangguan Jiwa</option>
                        <option value="clean_water">Air Bersih</option>
                        <option value="toilet">Toilet Saniter</option>
                    </select>
                </div>

                <div class="analysis-option">
                    <label for="villageFilter">Filter Desa:</label>
                    <select id="villageFilter">
                        <option value="all">Semua Desa</option>
                        <option value="1">Kabalsiang</option>
                        <option value="2">Benjuring</option>
                        <option value="3">Kompane</option>
                        <option value="4">Kumul</option>
                        <option value="5">Batuley</option>
                    </select>
                </div>

                <div class="heatmap-intensity">
                    <label for="intensitySlider">Intensitas Heat Map:</label>
                    <input type="range" id="intensitySlider" min="1" max="10" value="5">
                </div>

                <div class="heatmap-legend" id="heatmapLegend" style="display: none;">
                    <div class="heatmap-legend-title">Legenda:</div>
                    <div class="heatmap-legend-items">
                        <div class="heatmap-legend-item">
                            <div class="heatmap-legend-color" style="background-color: rgba(0, 0, 255, 0.3);"></div>
                            <span>Rendah</span>
                        </div>
                        <div class="heatmap-legend-item">
                            <div class="heatmap-legend-color" style="background-color: rgba(0, 255, 0, 0.5);"></div>
                            <span>Sedang</span>
                        </div>
                        <div class="heatmap-legend-item">
                            <div class="heatmap-legend-color" style="background-color: rgba(255, 0, 0, 0.7);"></div>
                            <span>Tinggi</span>
                        </div>
                    </div>
                </div>
                
                <div id="heatmapStats" class="heatmap-stats" style="display: none;">
                    <h4>Statistik</h4>
                    <div id="heatmapStatsContent"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal untuk detail -->
    <div id="buildingModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="PkmKabenApp.closeModal('buildingModal')">&times;</span>
            <div id="buildingDetails">Memuat detail...</div>
        </div>
    </div>

    <!-- Route Instructions Modal -->
    <div id="routeModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="PkmKabenApp.closeModal('routeModal')">&times;</span>
            <div id="routeInstructions">Memuat instruksi rute...</div>
        </div>
    </div>

    <!-- Loading Indicator -->
    <div id="routeLoading" class="route-loading">
        <div class="spinner"></div>
        <span>Mencari rute terbaik...</span>
    </div>
    
    <!-- Progress Indicator -->
    <div id="progressIndicator" class="progress-indicator">
        <div class="spinner"></div>
        <span id="progressMessage">Memuat data...</span>
    </div>

    <!-- Panel Koordinat -->
    @if($isLoggedIn)
    <div class="coordinate-panel" id="coordinatePanel">
        <div class="coordinate-panel-header">
            <h3>Check Koordinat</h3>
            <button id="closeCoordinatePanel">&times;</button>
        </div>
        <div class="coordinate-panel-content">
            <div class="coordinate-input-group">
                <label for="latitudeInput">Latitude:</label>
                <input type="text" id="latitudeInput" placeholder="-5.762572113507333">
            </div>
            <div class="coordinate-input-group">
                <label for="longitudeInput">Longitude:</label>
                <input type="text" id="longitudeInput" placeholder="134.21933285396588">
            </div>
            <button id="checkCoordinateBtn" class="coordinate-btn">Cek Koordinat</button>
            <button id="copyLinkBtn" class="coordinate-btn secondary">Salin Link</button>
            <button id="clearCacheBtn" class="coordinate-btn clear-cache">Hapus Cache</button>
            <button id="forceReloadBtn" class="coordinate-btn reload">Reload Data</button>
            <button id="runDiagnosticsBtn" class="coordinate-btn" style="background-color: #7e22ce; margin-top: 10px;">Jalankan Diagnostik</button>
            <div id="coordinateResult" class="coordinate-result"></div>
        </div>
    </div>
    @endif

    <!-- Tombol toggle untuk panel koordinat -->
    @if($isLoggedIn)
    <div class="coordinate-toggle" id="coordinateToggle">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
            <circle cx="12" cy="10" r="3"></circle>
        </svg>
    </div>
    @endif

    <!-- JavaScript akan ditambahkan pada bagian selanjutnya -->
    <script>
        // Tidak ada lagi implementasi Smart Assistant di sini
        document.addEventListener('DOMContentLoaded', function() {
            // Kode lain yang tidak berhubungan dengan Smart Assistant tetap ada
        });
    </script>
    
    <!-- Library Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/leaflet.locatecontrol@0.79.0/dist/L.Control.Locate.min.js"></script>
    <script src="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
    
    <!-- Custom JavaScript (Modularized) -->
    <script>
        // App namespace untuk menghindari variabel global
const PkmKabenApp = {
    // Konfigurasi app
    config: {
        apiEndpoints: {
            buildings: '/map/buildings',
            buildingDetail: '/map/buildings/'
        },
        mapSettings: {
            defaultView: [-5.739483493261797, 134.79414177089714],
            defaultZoom: 15,
            maxZoom: 25
        },
        cacheSettings: {
            buildingsExpiry: 3600000, // 1 jam dalam ms
            enabled: true
        }
    },
    
    // Data state
    state: {
        map: null,
        markers: [],
        allBuildingsData: [],
        markerCluster: null,
        routingControl: null,
        heatmapLayer: null,
        searchTimeout: null,
        activeVillageLink: null,
        activeHeatmapType: 'none',
        userLocation: null,
        loadedAt: null,
        markerLayer: null,  // Tambahan untuk layer marker
        priorityMarker: null,  // Tambahan untuk marker prioritas
        isLoggedIn: {{ $isLoggedIn ? 'true' : 'false' }} // Status login dari PHP
    },
    
    // Village data
    villages: [
        { id: 1, name: 'Kabalsiang', lat: -5.7465, lng: 134.797032 },
        { id: 2, name: 'Benjuring', lat: -5.7425, lng: 134.800000 },
        { id: 3, name: 'Kompane', lat: -5.6479, lng: 134.7606431 },
        { id: 4, name: 'Kumul', lat: -5.7495, lng: 134.795000 },
        { id: 5, name: 'Batuley', lat: -5.7515, lng: 134.798000 }
    ],
    
    // Objek untuk menyimpan event handlers
    eventHandlers: {},
    
    // Konstanta
    constants: {
        defaultColor: '#3498db',
        filterColors: {
            tb: '#e74c3c',
            hypertension: '#e67e22',
            mental: '#9b59b6',
            clean_water: '#3498db',
            toilet: '#8e44ad'
        }
    },
    
    // Inisialisasi aplikasi
    init() {
        // Inisialisasi peta
        this.initMap();
        
        // Tambahkan event listeners
        this.attachEventListeners();
        
        // Cek parameter URL untuk koordinat
        this.checkUrlParameters();
        
        // Muat data
        this.loadBuildings();
        
        // Inisialisasi dashboard kesehatan
        setTimeout(() => {
            if (this.dashboard) {
                this.dashboard.init();
            }
        }, 1000);
        
        // Set state loaded
        this.state.loadedAt = Date.now();
        
        // Inisialisasi toggle panel untuk mobile
        this.initMobilePanel();
    },
    
    // Fungsi untuk memeriksa parameter URL
    checkUrlParameters() {
        const urlParams = new URLSearchParams(window.location.search);
        const lat = urlParams.get('lat');
        const lng = urlParams.get('lng');
        const zoom = urlParams.get('zoom');
        
        if (lat && lng) {
            console.log('Koordinat ditemukan dalam URL:', lat, lng);
            
            // Gunakan setTimeout untuk memastikan peta sudah dimuat
            setTimeout(() => {
                this.focusToCoordinates(
                    parseFloat(lat),
                    parseFloat(lng),
                    zoom ? parseInt(zoom) : 16
                );
            }, 1000);
        }
    },
    
    // Inisialisasi peta
    initMap() {
        // Buat layers peta
        const openStreetMap = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: this.config.mapSettings.maxZoom,
            attribution: '© OpenStreetMap contributors'
        });
        
        const satelliteMap = L.tileLayer(`https://api.maptiler.com/tiles/satellite/{z}/{x}/{y}.jpg?key=H4JtE5wMxUYUZOEICzFK`, {
            maxNativeZoom: 20,
            maxZoom: this.config.mapSettings.maxZoom,
            detectRetina: true,
            crossOrigin: true,
            errorTileUrl: 'data:image/gif;base64,R0lGODlhAQABAAAAACw=',
            attribution: 'Imagery © <a href="https://www.maptiler.com/" target="_blank" rel="noopener">MapTiler</a> © <a href="https://www.mapbox.com/" target="_blank" rel="noopener">Mapbox</a>'
        });
        
        // Buat instance peta dengan layer satelit sebagai default
        this.state.map = L.map('mapContainer', {
            layers: [satelliteMap]
        }).setView(
            this.config.mapSettings.defaultView, 
            this.config.mapSettings.defaultZoom
        );
        // Pane khusus untuk label agar di atas tile lainnya
        this.state.map.createPane('labels');
        this.state.map.getPane('labels').style.zIndex = 650;
        this.state.map.getPane('labels').style.pointerEvents = 'none';

        
        // Buat layer group untuk marker
        this.state.markerLayer = L.layerGroup().addTo(this.state.map);
        
        // Tambahkan kontrol layer
        
        // Overlay label (Hybrid) - opsional. Aktifkan via layers control.
        const hybridLabels = L.tileLayer(
            `https://api.maptiler.com/tiles/hybrid/{z}/{x}/{y}.png?key=H4JtE5wMxUYUZOEICzFK`,
            {
                pane: 'labels',
                opacity: 0.9,
                maxNativeZoom: 20,
                maxZoom: this.config.mapSettings.maxZoom,
                crossOrigin: true,
                attribution: ''
            }
        );
const baseMaps = {
            "Peta Standar": openStreetMap,
            "Citra Satelit (MapTiler)": satelliteMap
        };
        
        const overlayMaps = {
            "Lokasi Rumah": this.state.markerLayer
        ,
            "Label (Hybrid)": hybridLabels};
        
        L.control.layers(baseMaps, overlayMaps, {position: 'topright'}).addTo(this.state.map);
        // Auto-switch base layer: OSM (<19) ↔ Satelit (>=19)
        const AUTO_SWITCH = false;
        const Z_SWITCH = 21;
        let satActive = true; // karena default kita pakai satelit
        if (AUTO_SWITCH) {
            this.state.map.on('zoomend', () => {
                const z = this.state.map.getZoom();
                if (z >= Z_SWITCH && !satActive) {
                    this.state.map.addLayer(satelliteMap);
                    this.state.map.removeLayer(openStreetMap);
                    satActive = true;
                } else if (z < Z_SWITCH && satActive) {
                    this.state.map.addLayer(openStreetMap);
                    this.state.map.removeLayer(satelliteMap);
                    satActive = false;
                }
            });
        }

        // Pastikan peta tidak 'blank' saat container berubah ukuran
        setTimeout(() => { try { this.state.map.invalidateSize(true); } catch(e){} }, 300);
        this.state.map.on('baselayerchange', () => { try { this.state.map.invalidateSize(); } catch(e){} });

        
        // Tambahkan kontrol lokasi
        L.control.locate({
            position: 'topleft',
            strings: {
                title: "Temukan lokasi saya"
            },
            locateOptions: {
                enableHighAccuracy: true
            },
            onLocationFound: this.onLocationFound.bind(this)
        }).addTo(this.state.map);
        
        // Tambahkan kontrol untuk memilih koordinat langsung dari peta
        const pickCoordinateControl = L.Control.extend({
            options: {
                position: 'topright'
            },
            onAdd: (map) => {
                const container = L.DomUtil.create('div', 'leaflet-bar leaflet-control');
                const button = L.DomUtil.create('a', 'pick-coordinate-button', container);
                button.href = '#';
                button.title = 'Pilih Koordinat dari Peta';
                button.innerHTML = '📍';
                button.style.fontSize = '18px';
                button.style.width = '30px';
                button.style.height = '30px';
                button.style.lineHeight = '30px';
                button.style.textAlign = 'center';
                
                // Status mode picking
                this.pickingMode = false;
                
                L.DomEvent.on(button, 'click', L.DomEvent.stopPropagation)
                    .on(button, 'click', L.DomEvent.preventDefault)
                    .on(button, 'click', () => {
                        this.togglePickCoordinateMode(button);
                    });
                
                return container;
            }
        });
        
        this.state.map.addControl(new pickCoordinateControl());
    },
    
    // Handler lokasi ditemukan
    onLocationFound(e) {
        this.state.userLocation = [e.latitude, e.longitude];
        console.log("Lokasi pengguna diperbarui:", this.state.userLocation);
    },
    // Inisialisasi event listeners
    attachEventListeners() {
        // Navbar toggle
        const navbarToggle = document.getElementById('navbarToggle');
        if (navbarToggle) {
            navbarToggle.addEventListener('click', this.toggleNavbar.bind(this));
        }
        
        // Navigasi desa
        const villageLinks = document.querySelectorAll('.village-link');
        villageLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const villageId = parseInt(link.getAttribute('data-village-id'));
                this.navigateToVillage(villageId);
            });
        });

        // Tab panel
        const panelTabs = document.querySelectorAll('.panel-tab');
        panelTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const tabName = tab.getAttribute('data-tab');
                this.changeTab(tabName);
            });
        });
        
        // Toggle panel button untuk mobile
        const togglePanelBtn = document.getElementById('togglePanelBtn');
        if (togglePanelBtn) {
            togglePanelBtn.addEventListener('click', this.toggleControlPanel.bind(this));
        }
        
        // Search input dengan debounce
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', this.debounceSearch.bind(this));
        }
        
        // Search filter
        const searchFilter = document.getElementById('searchFilter');
        if (searchFilter) {
            searchFilter.addEventListener('change', this.debounceSearch.bind(this));
        }
        
        // Heatmap controls
        const heatmapType = document.getElementById('heatmapType');
        const villageFilter = document.getElementById('villageFilter');
        const intensitySlider = document.getElementById('intensitySlider');
        
        if (heatmapType) heatmapType.addEventListener('change', this.updateHeatmap.bind(this));
        if (villageFilter) villageFilter.addEventListener('change', this.updateHeatmap.bind(this));
        if (intensitySlider) intensitySlider.addEventListener('input', this.updateHeatmapIntensity.bind(this));
        
        // Modal close buttons
        // Tidak lagi diperlukan karena sudah menggunakan atribut onclick
        // const closeBuildingModal = document.getElementById('closeBuildingModal');
        // const closeRouteModal = document.getElementById('closeRouteModal');
        
        // if (closeBuildingModal) {
        //     closeBuildingModal.addEventListener('click', this.closeModal.bind(this, 'buildingModal'));
        // }
        
        // if (closeRouteModal) {
        //     closeRouteModal.addEventListener('click', this.closeModal.bind(this, 'routeModal'));
        // }
        
        // Click events outside modals
        window.addEventListener('click', (event) => {
            const buildingModal = document.getElementById('buildingModal');
            const routeModal = document.getElementById('routeModal');
            
            if (event.target === buildingModal) {
                this.closeModal('buildingModal');
            }
            
            if (event.target === routeModal) {
                this.closeModal('routeModal');
            }
        });
        
        // Click events outside navbar on mobile
        document.addEventListener('click', (event) => {
            const navbar = document.querySelector('.navbar');
            const navbarLinks = document.getElementById('navbarLinks');
            
            if (navbar && navbarLinks && !navbar.contains(event.target)) {
                navbarLinks.classList.remove('active');
            }
        });
        
        // Tombol diagnostik
        const diagnosticsBtn = document.getElementById('diagnosticsBtn');
        if (diagnosticsBtn) {
            diagnosticsBtn.addEventListener('click', this.runDiagnostics.bind(this));
        }
        
        // Tombol koordinat
        const checkCoordinateBtn = document.getElementById('checkCoordinateBtn');
        if (checkCoordinateBtn) {
            checkCoordinateBtn.addEventListener('click', this.checkCoordinateFromPanel.bind(this));
        }
        
        // Tombol salin link
        const copyLinkBtn = document.getElementById('copyLinkBtn');
        if (copyLinkBtn) {
            copyLinkBtn.addEventListener('click', this.copyCurrentLink.bind(this));
        }
        
        // Tombol hapus cache
        const clearCacheBtn = document.getElementById('clearCacheBtn');
        if (clearCacheBtn) {
            clearCacheBtn.addEventListener('click', this.clearBuildingsCache.bind(this));
        }
        
        // Tombol reload data
        const forceReloadBtn = document.getElementById('forceReloadBtn');
        if (forceReloadBtn) {
            forceReloadBtn.addEventListener('click', this.forceReloadBuildings.bind(this));
        }
        
        // Window resize event
        window.addEventListener('resize', this.handleWindowResize.bind(this));
    },
    
    // Handler untuk resize window
    handleWindowResize() {
        // Reset status panel ke default terbuka jika beralih ke desktop
        if (window.innerWidth > 768) {
            const controlPanel = document.getElementById('controlPanel');
            const toggleIcon = document.querySelector('.toggle-panel-icon');
            const toggleBtn = document.getElementById('togglePanelBtn');
            
            if (controlPanel && toggleIcon && toggleBtn) {
                controlPanel.classList.remove('panel-hidden');
                toggleIcon.classList.add('rotated');
                toggleBtn.classList.add('active');
            }
        }
    },
    
    // Toggle control panel pada tampilan mobile
    toggleControlPanel() {
        const controlPanel = document.getElementById('controlPanel');
        const toggleIcon = document.querySelector('.toggle-panel-icon');
        const toggleBtn = document.getElementById('togglePanelBtn');
        
        if (controlPanel && toggleIcon) {
            // Cek apakah panel sedang tersembunyi
            const isPanelCurrentlyHidden = controlPanel.classList.contains('panel-hidden');
            
            // Toggle class panel-hidden
            if (isPanelCurrentlyHidden) {
                // Jika panel tersembunyi, tampilkan
                controlPanel.classList.remove('panel-hidden');
                toggleIcon.classList.add('rotated');
                toggleBtn.classList.add('active');
            } else {
                // Jika panel tampil, sembunyikan
                controlPanel.classList.add('panel-hidden');
                toggleIcon.classList.remove('rotated');
                toggleBtn.classList.remove('active');
            }
            
            // Simpan status panel ke localStorage
            localStorage.setItem('pkmKaben_panelHidden', !isPanelCurrentlyHidden);
        }
    },
    
    // Inisialisasi toggle panel untuk mobile
    initMobilePanel() {
        const controlPanel = document.getElementById('controlPanel');
        const toggleIcon = document.querySelector('.toggle-panel-icon');
        const toggleBtn = document.getElementById('togglePanelBtn');
        
        // Cek status panel dari localStorage jika ada
        const panelHidden = localStorage.getItem('pkmKaben_panelHidden') === 'true';
        
        if (controlPanel && toggleIcon) {
            if (panelHidden) {
                // Jika preference adalah panel tersembunyi
                controlPanel.classList.add('panel-hidden');
                toggleIcon.classList.remove('rotated');
                toggleBtn.classList.remove('active');
            } else {
                // Jika preference adalah panel ditampilkan
                controlPanel.classList.remove('panel-hidden');
                toggleIcon.classList.add('rotated');
                toggleBtn.classList.add('active');
            }
        }
    },
    
    // Toggle navbar pada mobile
    toggleNavbar() {
        const navbarLinks = document.getElementById('navbarLinks');
        if (navbarLinks) {
            navbarLinks.classList.toggle('active');
        }
    },
    
    // Navigasi ke desa berdasarkan ID
    navigateToVillage(villageId) {
        const village = this.villages.find(v => v.id === villageId);
        
        if (village) {
            this.state.map.setView([village.lat, village.lng], 16);
            
            // Update active state
            const villageLinks = document.querySelectorAll('.village-link');
            villageLinks.forEach(link => {
                link.classList.remove('active');
                if (parseInt(link.getAttribute('data-village-id')) === villageId) {
                    link.classList.add('active');
                    this.state.activeVillageLink = link;
                }
            });
            
            // Pada tampilan mobile, tutup navbar
            if (window.innerWidth <= 768) {
                document.getElementById('navbarLinks').classList.remove('active');
            }
        }
    },
    
    // Ganti tab pada panel
    changeTab(tabName) {
        // Update active tab
        const tabs = document.querySelectorAll('.panel-tab');
        tabs.forEach(tab => tab.classList.remove('active'));
        
        // Update active content
        const contents = document.querySelectorAll('.panel-content');
        contents.forEach(content => content.classList.remove('active'));
        
        if (tabName === 'search') {
            document.querySelector('.panel-tab[data-tab="search"]').classList.add('active');
            document.getElementById('search-content').classList.add('active');
        } else if (tabName === 'analysis') {
            document.querySelector('.panel-tab[data-tab="analysis"]').classList.add('active');
            document.getElementById('analysis-content').classList.add('active');
        }
    },
    // Buat marker untuk rumah
    createHouseMarker(building, color = this.constants.defaultColor, isPriority = false) {
        const priorityClass = isPriority ? 'marker-priority marker-pulse' : '';
        
        // Tentukan status kesehatan rumah berdasarkan rata-rata IKS dari keluarga
        let healthStatus = 'unknown';
        let healthColor = this.constants.defaultColor;
        let priorityIcon = '';
        let needsVisitBadge = '';
        
        // Periksa apakah bangunan memiliki data keluarga
        if (building.families && Array.isArray(building.families) && building.families.length > 0) {
            // Hitung rata-rata IKS
            let totalIKS = 0;
            let familiesWithIKS = 0;
            let hasPriority = false;
            let priorityType = '';
            let needsVisit = false;
            
            building.families.forEach(family => {
                // Ekstrak nilai IKS dengan berbagai kemungkinan properti
                let iksValue = null;
                
                if (family.iks !== undefined && family.iks !== null) {
                    iksValue = parseFloat(family.iks);
                } else if (family.iks_value !== undefined && family.iks_value !== null) {
                    iksValue = parseFloat(family.iks_value);
                } else if (family.health_index !== undefined && family.health_index !== null) {
                    iksValue = parseFloat(family.health_index);
                } else if (family.skor_iks !== undefined && family.skor_iks !== null) {
                    iksValue = parseFloat(family.skor_iks);
                } else if (family.indeks_keluarga_sehat !== undefined && family.indeks_keluarga_sehat !== null) {
                    iksValue = parseFloat(family.indeks_keluarga_sehat);
                }
                
                // Log nilai IKS yang berhasil diambil untuk debugging
                if (!isNaN(iksValue) && iksValue !== null) {
                    console.log(`IKS value for building ${building.id}, family: ${family.id || 'unknown'} = ${iksValue}`);
                    totalIKS += iksValue;
                    familiesWithIKS++;
                }
                
                // Periksa kondisi prioritas
                if (family.priority === true || family.is_priority === true || 
                    (family.priority_level && parseInt(family.priority_level) > 0)) {
                    hasPriority = true;
                    
                    // Tentukan jenis prioritas jika tersedia
                    if (family.priority_type) {
                        priorityType = family.priority_type;
                    } else if (family.priority_reason) {
                        priorityType = family.priority_reason;
                    }
                }
                
                // Periksa apakah perlu kunjungan
                if (family.needs_visit === true || 
                    (family.last_visit && this.isVisitOverdue(family.last_visit))) {
                    needsVisit = true;
                }
            });
            
            // Hitung rata-rata IKS
            const avgIKS = familiesWithIKS > 0 ? totalIKS / familiesWithIKS : 0;
            
            console.log(`Building ${building.id}: avgIKS = ${avgIKS}, from ${familiesWithIKS} families`);
            
            // Tentukan status kesehatan berdasarkan IKS
            if (avgIKS >= 0.8) {
                healthStatus = 'healthy';
                healthColor = '#10b981'; // Hijau untuk sehat
            } else if (avgIKS >= 0.5) {
                healthStatus = 'pra-healthy';
                healthColor = '#f59e0b'; // Kuning untuk pra-sehat
            } else if (avgIKS > 0) {
                healthStatus = 'unhealthy';
                healthColor = '#ef4444'; // Merah untuk tidak sehat
            } else {
                // Jika tidak ada data IKS yang valid, gunakan warna default
                healthStatus = 'unknown';
                healthColor = this.constants.defaultColor;
            }
            
            // Jika ada prioritas, tambahkan ikon khusus
            if (hasPriority) {
                // Tentukan ikon berdasarkan jenis prioritas
                let priorityIconSvg = '';
                
                if (priorityType.includes('hamil') || priorityType.includes('pregnant')) {
                    // Ikon ibu hamil
                    priorityIconSvg = `
                        <div class="marker-priority-icon pregnant">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 16v3a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-3"/>
                                <circle cx="12" cy="7" r="4"/>
                                <path d="M12 11v8"/>
                                <path d="M8 16h8"/>
                            </svg>
                        </div>
                    `;
                } else if (priorityType.includes('lansia') || priorityType.includes('elderly')) {
                    // Ikon lansia
                    priorityIconSvg = `
                        <div class="marker-priority-icon elderly">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 20v-8a4 4 0 0 0-4-4h-4a4 4 0 0 0-4 4v8"/>
                                <circle cx="12" cy="4" r="2"/>
                                <path d="M8 16h8"/>
                            </svg>
                        </div>
                    `;
                } else if (priorityType.includes('bayi') || priorityType.includes('balita') || 
                           priorityType.includes('infant') || priorityType.includes('child')) {
                    // Ikon bayi/balita
                    priorityIconSvg = `
                        <div class="marker-priority-icon infant">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M10 2h4"/>
                                <path d="M12 14v-4"/>
                                <path d="M4 14a8 8 0 0 1 16 0"/>
                                <path d="M8 22v-4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v4"/>
                            </svg>
                        </div>
                    `;
                } else {
                    // Ikon prioritas umum
                    priorityIconSvg = `
                        <div class="marker-priority-icon general">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="8" x2="12" y2="12"/>
                                <line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                        </div>
                    `;
                }
                
                priorityIcon = priorityIconSvg;
            }
            
            // Jika perlu kunjungan, tambahkan badge
            if (needsVisit) {
                needsVisitBadge = `<div class="needs-visit-badge"></div>`;
            }
        }
        
        // Gunakan warna yang ditentukan jika diberikan sebagai parameter
        if (color !== this.constants.defaultColor) {
            healthColor = color;
        }
        
        // Buat HTML marker
        return L.divIcon({
            html: `
                <div class="house-marker ${priorityClass} status-${healthStatus}" style="background-color: ${healthColor}">
                    ${needsVisitBadge}
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20">
                        <path fill="white" d="M19 9.3V4h-3v2.6L12 3 2 12h3v8h5v-6h4v6h5v-8h3l-3-2.7zM12 18.5h-2v-6H8v6H6v-8l6-5.5 6 5.5v8h-2v-6h-2v6h-2v-6z"/>
                    </svg>
                    ${priorityIcon}
                    <span>${building.building_number || 'N/A'}</span>
                </div>`,
            className: 'custom-house-marker',
            iconSize: [40, 50],
            iconAnchor: [20, 50],
            popupAnchor: [0, -45]
        });
    },
    
    // Fungsi helper untuk memeriksa apakah kunjungan sudah terlalu lama
    isVisitOverdue(lastVisitDate) {
        try {
            const lastVisit = new Date(lastVisitDate);
            const today = new Date();
            
            // Hitung perbedaan dalam hari (30 hari sebagai batas)
            const diffTime = Math.abs(today - lastVisit);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            return diffDays > 30; // Kunjungan dianggap terlalu lama jika lebih dari 30 hari
        } catch (e) {
            console.error('Error checking visit overdue:', e);
            return false;
        }
    },
    
    // Memuat data bangunan dengan caching
    async loadBuildings() {
        this.showProgress("Memuat data bangunan...");
        
        try {
            // Cek cache terlebih dahulu jika diaktifkan
            if (this.config.cacheSettings.enabled) {
                const cachedData = this.getCachedBuildings();
                
                if (cachedData) {
                    console.log("Menggunakan data bangunan dari cache");
                    this.state.allBuildingsData = cachedData;
                    this.renderBuildings(cachedData);
                    
                    // Perbarui dashboard setelah memuat data
                    if (this.dashboard) {
                        setTimeout(() => {
                            this.dashboard.updateDashboardData();
                        }, 500);
                    }
                    
                    this.hideProgress();
                    return;
                }
            }
            
            // Jika tidak ada cache yang valid, ambil dari API
            console.log("Mengambil data bangunan dari API");
            
            fetch(this.config.apiEndpoints.buildings)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log(`Total buildings from API: ${data.length}`);
                    
                    // Simpan data ke state
                    this.state.allBuildingsData = data;
                    
                    // Render bangunan ke peta
                    this.renderBuildings(data);
                    
                    // Simpan ke cache jika diaktifkan
                    if (this.config.cacheSettings.enabled) {
                        this.cacheBuildings(data);
                    }
                    
                    // Perbarui dashboard setelah memuat data
                    if (this.dashboard) {
                        setTimeout(() => {
                            this.dashboard.updateDashboardData();
                        }, 500);
                    }
                })
                .catch(error => {
                    console.error("Error fetching buildings:", error);
                    this.showError("Gagal memuat data bangunan. Silakan coba lagi nanti.");
                })
                .finally(() => {
                    this.hideProgress();
                });
        } catch (error) {
            console.error("Error in loadBuildings:", error);
            this.hideProgress();
        }
    },
    
    // Render marker bangunan di peta
    renderBuildings(buildings) {
        console.log(`Rendering ${buildings.length} buildings...`);
        
        // Reset markers
        this.state.markerLayer.clearLayers();
        this.state.markers = [];
        
        // Buat array untuk menyimpan bounds
        const bounds = [];
        
        // Array untuk menyimpan marker valid
        const validMarkers = [];
        
        // Array untuk mencatat bangunan dengan koordinat tidak valid
        const invalidBuildings = [];
        
        // Loop semua bangunan
        buildings.forEach(building => {
            try {
                // Parse koordinat ke number jika string
                const lat = typeof building.latitude === 'string' ? 
                    parseFloat(building.latitude) : building.latitude;
                const lng = typeof building.longitude === 'string' ? 
                    parseFloat(building.longitude) : building.longitude;
                
                // Validasi koordinat dengan output debug
                if (!this.isValidCoordinate(lat, lng)) {
                    invalidBuildings.push({
                        id: building.id,
                        building_number: building.building_number,
                        latitude: building.latitude,
                        longitude: building.longitude,
                        parsed_lat: lat,
                        parsed_lng: lng
                    });
                    return; // Skip this building
                }
                
                // Tambahkan ke bounds
                bounds.push([lat, lng]);
                
                // Buat marker
                const marker = L.marker([lat, lng], {
                    icon: this.createHouseMarker(building)
                })
                .bindPopup(this.createBuildingPopup(building, lat, lng));
                
                // Tambahkan event handler untuk klik marker
                marker.on('click', () => {
                    this.setMarkerPriority(marker, building);
                });
                
                // Tambahkan ke layer group, bukan langsung ke map
                this.state.markerLayer.addLayer(marker);
                
                // Simpan referensi
                validMarkers.push({ marker, building });
            } catch (error) {
                console.error(`Error processing building ID ${building.id}:`, error);
                invalidBuildings.push({
                    id: building.id,
                    building_number: building.building_number,
                    latitude: building.latitude,
                    longitude: building.longitude,
                    error: error.message
                });
            }
        });
        
        // Log bangunan yang tidak valid
        if (invalidBuildings.length > 0) {
            console.warn(`${invalidBuildings.length} buildings have invalid coordinates:`, invalidBuildings);
        }
        
        // Update markers state
        this.state.markers = validMarkers;
        
        console.log(`Successfully created ${this.state.markers.length} markers out of ${buildings.length} buildings`);
        
        // Tambahkan event listener untuk klik pada map untuk reset prioritas marker
        this.state.map.on('click', (e) => {
            // Pastikan klik bukan pada marker
            if (e.originalEvent.target.closest('.leaflet-marker-icon')) {
                return;
            }
            this.resetMarkerPriorities();
        });
        
        // Fit bounds jika ada marker valid
        if (bounds.length > 0) {
            try {
                this.state.map.fitBounds(bounds, {
                    padding: [50, 50],
                    maxZoom: 16
                });
                console.log(`Map fitted to bounds of ${bounds.length} markers`);
            } catch (error) {
                console.error('Error fitting bounds:', error);
            }
        } else {
            console.warn('No valid markers to fit bounds');
        }
        
        // Tambahkan tombol helper untuk mengecek koordinat spesifik
        if (!this.debugButtonAdded) {
            const debugControl = L.Control.extend({
                options: {
                    position: 'topright'
                },
                onAdd: (map) => {
                    const container = L.DomUtil.create('div', 'leaflet-bar leaflet-control');
                    const button = L.DomUtil.create('a', 'debug-button', container);
                    button.href = '#';
                    button.title = 'Debug Koordinat';
                    button.innerHTML = '🔍';
                    button.style.fontSize = '18px';
                    button.style.width = '30px';
                    button.style.height = '30px';
                    button.style.lineHeight = '30px';
                    button.style.textAlign = 'center';
                    
                    L.DomEvent.on(button, 'click', L.DomEvent.stopPropagation)
                        .on(button, 'click', L.DomEvent.preventDefault)
                        .on(button, 'click', () => {
                            this.checkSpecificCoordinates();
                        });
                    
                    return container;
                }
            });
            
            this.state.map.addControl(new debugControl());
            this.debugButtonAdded = true;
        }
    },
    
    // Buat popup untuk bangunan
    createBuildingPopup(building, lat, lng) {
        // Debug data bangunan
        console.log('Building data for popup:', building);
        console.log('Building village data:', building.village);
        
        // Hitung jumlah keluarga dan anggota
        let totalFamilies = 0;
        let totalMembers = 0;
        let familiesHtml = '';
        
        // Periksa struktur data village
        let villageName = 'Tidak diketahui';
        
        if (building.village) {
            if (typeof building.village === 'object' && building.village.name) {
                villageName = building.village.name;
                console.log('Village name from object:', villageName);
            } else if (typeof building.village === 'string') {
                villageName = building.village;
                console.log('Village name from string:', villageName);
            }
        } else if (building.desa) {
            // Alternatif jika menggunakan properti 'desa' bukan 'village'
            if (typeof building.desa === 'object' && building.desa.name) {
                villageName = building.desa.name;
                console.log('Village name from desa object:', villageName);
            } else if (typeof building.desa === 'string') {
                villageName = building.desa;
                console.log('Village name from desa string:', villageName);
            }
        } else if (building.village_name) {
            // Alternatif jika menggunakan properti 'village_name'
            villageName = building.village_name;
            console.log('Village name from village_name property:', villageName);
        } else if (building.desa_name) {
            // Alternatif jika menggunakan properti 'desa_name'
            villageName = building.desa_name;
            console.log('Village name from desa_name property:', villageName);
        }
        
        // Dapatkan nomor bangunan
        const buildingNumber = building.building_number || 'N/A';
        console.log('Building number:', buildingNumber);
        
        // Tangani kasus data keluarga
        if (building.families && Array.isArray(building.families) && building.families.length > 0) {
            console.log('Families data:', building.families);
            totalFamilies = building.families.length;
            
            // Buat daftar keluarga dengan nama kepala keluarga dan jumlah anggota
            familiesHtml = building.families.map(family => {
                const memberCount = this.countFamilyMembers(family);
                totalMembers += memberCount;
                
                // Akses ke nama kepala keluarga dengan berbagai kemungkinan properti
                let headName = 'Tidak diketahui';
                
                if (family.head_name) {
                    headName = family.head_name;
                } else if (family.head && family.head.name) {
                    headName = family.head.name;
                } else if (family.name) {
                    headName = family.name;
                } else if (family.family_name) {
                    headName = family.family_name;
                } else if (family.kepala_keluarga) {
                    headName = family.kepala_keluarga;
                }
                
                return `
                    <div class="family-item">
                        <div class="family-name">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            ${headName}
                        </div>
                        <div class="member-count">${memberCount} anggota</div>
                    </div>
                `;
            }).join('');
        } else {
            familiesHtml = '<div class="family-item">Tidak ada data keluarga</div>';
        }
        
        // Buat popup HTML
        return `
            <div class="custom-popup">
                <div class="popup-header">
                    <h3>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                        </svg>
                        Rumah ${buildingNumber}
                    </h3>
                    <p>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                        Desa: ${villageName}
                    </p>
                </div>
                
                <div class="family-list">
                    ${familiesHtml}
                </div>
                
                <div class="total-members">
                    <span>Total Anggota Keluarga:</span>
                    <span>${totalMembers} orang dari ${totalFamilies} KK</span>
                </div>
                
                <div class="popup-buttons">
                    <button class="popup-button detail-button" onclick="PkmKabenApp.showBuildingDetails(${building.id})">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                        Lihat Detail Lengkap
                    </button>
                    <button class="popup-button route-button" onclick="PkmKabenApp.showRoute(${lat}, ${lng})">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="3 11 22 2 13 21 11 13 3 11"></polygon>
                        </svg>
                        Tampilkan Rute
                    </button>
                </div>
            </div>
        `;
    },
    // Validasi koordinat
    isValidCoordinate(lat, lng) {
        // Pastikan koordinat berupa angka
        if (typeof lat !== 'number' || typeof lng !== 'number' || 
            isNaN(lat) || isNaN(lng)) {
            
            // Coba konversi jika string
            if (typeof lat === 'string' && typeof lng === 'string') {
                try {
                    lat = parseFloat(lat);
                    lng = parseFloat(lng);
                    if (isNaN(lat) || isNaN(lng)) return false;
                } catch(e) {
                    console.error('Konversi koordinat gagal:', e);
                    return false;
                }
            } else {
                return false;
            }
        }
        
        // Validasi range koordinat dengan lebih fleksibel
        // Latitude: -90 hingga 90, Longitude: -180 hingga 180
        return lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180;
    },
    
    // Fungsi tambahan untuk debug koordinat
    debugCoordinate(building) {
        const lat = parseFloat(building.latitude);
        const lng = parseFloat(building.longitude);
        
        console.log(`Debug Koordinat Building #${building.id}:`, {
            original: {
                lat: building.latitude,
                lng: building.longitude,
                type_lat: typeof building.latitude,
                type_lng: typeof building.longitude
            },
            parsed: {
                lat: lat,
                lng: lng,
                valid: this.isValidCoordinate(lat, lng)
            }
        });
        
        // Tambah marker debug jika koordinat valid tapi tidak muncul
        if (this.isValidCoordinate(lat, lng)) {
            // Marker debug dengan warna mencolok
            const debugMarker = L.marker([lat, lng], {
                icon: L.divIcon({
                    html: `
                    <div style="background-color: #ff00ff; color: white; padding: 5px; border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                        ${building.id}
                    </div>`,
                    className: 'debug-marker',
                    iconSize: [30, 30],
                    iconAnchor: [15, 15]
                })
            }).addTo(this.state.map);
            
            debugMarker.bindPopup(`
                <div>
                    <h3>Debug Marker #${building.id}</h3>
                    <p>Lat: ${lat}</p>
                    <p>Lng: ${lng}</p>
                    <p>Building Number: ${building.building_number || 'N/A'}</p>
                </div>
            `);
        }
    },
    
    // Cache data bangunan
    cacheBuildings(buildings) {
        try {
            localStorage.setItem('pkm_kaben_buildings', JSON.stringify(buildings));
            localStorage.setItem('pkm_kaben_buildings_timestamp', Date.now().toString());
            console.log("Data bangunan disimpan ke cache");
        } catch (error) {
            console.error("Error caching buildings:", error);
        }
    },
    
    // Ambil data bangunan dari cache
    getCachedBuildings() {
        try {
            const cachedData = localStorage.getItem('pkm_kaben_buildings');
            const timestamp = localStorage.getItem('pkm_kaben_buildings_timestamp');
            
            if (!cachedData || !timestamp) return null;
            
            const now = Date.now();
            const age = now - parseInt(timestamp);
            
            // Jika cache lebih tua dari waktu kedaluwarsa, kembalikan null
            if (age > this.config.cacheSettings.buildingsExpiry) {
                console.log("Cache kedaluwarsa");
                return null;
            }
            
            return JSON.parse(cachedData);
        } catch (error) {
            console.error("Error reading cache:", error);
            return null;
        }
    },
    
    // Menampilkan progress indicator
    showProgress(message) {
        const progressIndicator = document.getElementById('progressIndicator');
        const progressMessage = document.getElementById('progressMessage');
        
        if (progressIndicator && progressMessage) {
            progressMessage.textContent = message;
            progressIndicator.classList.add('active');
        }
    },
    
    // Menyembunyikan progress indicator
    hideProgress() {
        const progressIndicator = document.getElementById('progressIndicator');
        
        if (progressIndicator) {
            progressIndicator.classList.remove('active');
        }
    },
    
    // Menampilkan pesan error
    showError(message) {
        alert(message);
    },
    // Debounce untuk pencarian
    debounceSearch() {
        clearTimeout(this.state.searchTimeout);
        this.state.searchTimeout = setTimeout(() => {
            this.performSearch();
        }, 300); // Tunggu 300ms setelah pengguna berhenti mengetik
    },
    
    // Melakukan pencarian
    performSearch() {
        const searchTerm = document.getElementById('searchInput').value.trim().toLowerCase();
        const searchFilter = document.getElementById('searchFilter').value;
        const resultsContainer = document.getElementById('searchResults');
        
        // Bersihkan hasil sebelumnya
        if (resultsContainer) {
            resultsContainer.innerHTML = '';
        }
        
        // Jika kata kunci kosong, tampilkan pesan
        if (searchTerm.length === 0) {
            if (resultsContainer) {
                resultsContainer.innerHTML = '<div class="no-results">Masukkan kata kunci untuk mencari</div>';
            }
            return;
        }
        
        let results = [];
        
        // Pencarian di data bangunan dan keluarga
        if (searchFilter === 'all' || searchFilter === 'building_number') {
            // Cari berdasarkan nomor bangunan
            const buildingResults = this.state.allBuildingsData.filter(building => 
                building.building_number && building.building_number.toString().toLowerCase().includes(searchTerm)
            );
            
            results = [...results, ...buildingResults.map(building => ({
                type: 'building',
                id: building.id,
                title: `Rumah ${building.building_number}`,
                subtitle: `${building.village?.name || 'Tidak diketahui'}, ${building.families?.length || 0} keluarga`,
                lat: parseFloat(building.latitude),
                lng: parseFloat(building.longitude)
            }))];
        }
        
        if (searchFilter === 'all' || searchFilter === 'head_name') {
            // Cari berdasarkan nama kepala keluarga
            this.state.allBuildingsData.forEach(building => {
                if (building.families && Array.isArray(building.families)) {
                    const familyResults = building.families.filter(family => 
                        family.head_name && family.head_name.toLowerCase().includes(searchTerm)
                    );
                    
                    results = [...results, ...familyResults.map(family => ({
                        type: 'family',
                        buildingId: building.id,
                        id: family.id,
                        title: `Keluarga ${family.head_name}`,
                        subtitle: `Rumah ${building.building_number}, ${building.village?.name || 'Tidak diketahui'}`,
                        lat: parseFloat(building.latitude),
                        lng: parseFloat(building.longitude)
                    }))];
                }
            });
        }
        
        if (searchFilter === 'all' || searchFilter === 'village') {
            // Cari berdasarkan desa
            const villageResults = this.state.allBuildingsData.filter(building => 
                building.village && building.village.name && 
                building.village.name.toLowerCase().includes(searchTerm)
            );
            
            results = [...results, ...villageResults.map(building => ({
                type: 'building',
                id: building.id,
                title: `Rumah ${building.building_number}`,
                subtitle: `${building.village?.name || 'Tidak diketahui'}, ${building.families?.length || 0} keluarga`,
                lat: parseFloat(building.latitude),
                lng: parseFloat(building.longitude)
            }))];
        }
        
        // Tampilkan hasil pencarian
        if (results.length > 0 && resultsContainer) {
            results.forEach(result => {
                const resultItem = document.createElement('div');
                resultItem.className = 'search-result-item';
                resultItem.innerHTML = `
                    <div class="search-result-title">${result.title}</div>
                    <div class="search-result-subtitle">${result.subtitle}</div>
                `;
                
                // Tambahkan event onclick untuk navigasi ke hasil
                resultItem.onclick = () => this.navigateToSearchResult(result);
                
                resultsContainer.appendChild(resultItem);
            });
        } else if (resultsContainer) {
            resultsContainer.innerHTML = '<div class="no-results">Tidak ditemukan hasil yang sesuai</div>';
        }
    },
    
    // Navigasi ke hasil pencarian
    navigateToSearchResult(result) {
        // Pindahkan peta ke hasil
        if (this.isValidCoordinate(result.lat, result.lng)) {
            this.state.map.setView([result.lat, result.lng], 18);
            
            // Reset semua prioritas marker terlebih dahulu
            this.resetMarkerPriorities();
            
            // Cari marker yang sesuai dan buka popup
            this.state.markers.forEach(({marker, building}) => {
                if (building.id === (result.type === 'building' ? result.id : result.buildingId)) {
                    marker.openPopup();
                    
                    // Set marker sebagai prioritas dengan highlight warna
                    this.setMarkerPriority(marker, building, '#ff5722');
                }
            });
        }
    },
    
    // Fungsi untuk menetapkan marker sebagai prioritas
    setMarkerPriority(marker, building, color = this.constants.defaultColor) {
        // Reset semua prioritas marker terlebih dahulu
        this.resetMarkerPriorities();
        
        // Set marker baru sebagai prioritas
        const priorityIcon = this.createHouseMarker(building, color, true);
        marker.setIcon(priorityIcon);
        
        // Simpan referensi marker prioritas saat ini
        this.state.priorityMarker = {
            marker: marker,
            building: building,
            originalColor: color
        };
        
        // Pindahkan marker ke depan dengan membringToFront
        marker.getElement().style.zIndex = 1000;
    },
    
    // Fungsi untuk reset semua prioritas marker
    resetMarkerPriorities() {
        if (this.state.priorityMarker) {
            const { marker, building } = this.state.priorityMarker;
            const normalIcon = this.createHouseMarker(building, this.constants.defaultColor, false);
            marker.setIcon(normalIcon);
            marker.getElement().style.zIndex = '';
            this.state.priorityMarker = null;
        }
    },
    
    // Menampilkan detail bangunan
    showBuildingDetails(buildingId) {
        // Cari info bangunan
        const buildingInfo = this.state.allBuildingsData.find(b => b.id == buildingId);
        if (!buildingInfo) {
            this.showError("Data bangunan tidak ditemukan.");
                return;
            }
            
        // Cari marker yang sesuai dan set sebagai prioritas
        this.state.markers.forEach(({marker, building}) => {
            if (building.id === buildingId) {
                this.setMarkerPriority(marker, building);
            }
        });
        
        // Jika endpoint API tersedia, gunakan fungsi API detail
        if (typeof PkmKabenApp.details.showBuildingDetails === 'function') {
            // Gunakan data dari cache jika endpoint tidak dapat diakses
            PkmKabenApp.details.showBuildingDetails(buildingId);
        } else {
            // Fallback menggunakan data yang sudah ada di allBuildingsData
            this.showFallbackBuildingDetails(buildingInfo);
        }
    },
    
    // Fungsi fallback jika endpoint API tidak tersedia
    showFallbackBuildingDetails(building) {
        console.log('Menggunakan fallback untuk menampilkan detail bangunan:', building);
        
        // Format html detail menggunakan data dari allBuildingsData
        const detailsHtml = `
            <div class="alert-warning" style="background-color: #fff3cd; color: #856404; padding: 10px; margin-bottom: 15px; border-radius: 5px; border-left: 5px solid #ffeeba;">
                <strong>Perhatian:</strong> Menampilkan data dari cache lokal. Beberapa informasi mungkin tidak lengkap.
            </div>
            
            <div class="modal-header">
                <h2 class="text-xl font-bold">Detail Rumah ${building.building_number || 'N/A'}</h2>
            </div>
            
            <div class="detail-section">
                <h3 class="text-lg font-semibold">Informasi Rumah</h3>
                <p><strong>Nomor Rumah:</strong> ${building.building_number || 'N/A'}</p>
                <p><strong>Desa:</strong> ${building.village?.name || (typeof building.village === 'string' ? building.village : 'Tidak diketahui')}</p>
                <p><strong>Koordinat:</strong> ${building.latitude || '-'}, ${building.longitude || '-'}</p>
                <p><strong>Jumlah Keluarga:</strong> ${(building.families && Array.isArray(building.families)) ? building.families.length : 0}</p>
            </div>

            <div class="detail-section">
                <h3 class="text-lg font-semibold">Informasi Keluarga</h3>
                ${this.formatFallbackFamilies(building.families)}
            </div>
        `;
            
            const buildingDetailsElement = document.getElementById('buildingDetails');
            if (buildingDetailsElement) {
                buildingDetailsElement.innerHTML = detailsHtml;
            }
            
            this.openModal('buildingModal');
    },
    
    // Format keluarga untuk fallback
    formatFallbackFamilies(families) {
        if (!families || !Array.isArray(families) || families.length === 0) {
            return '<p>Tidak ada data keluarga yang tersedia.</p>';
        }
        
        return families.map(family => {
            // Format anggota keluarga jika ada
            let membersHtml = '<p>Tidak ada data anggota keluarga.</p>';
            if (family.members && Array.isArray(family.members) && family.members.length > 0) {
                const memberCount = family.members.length;
                membersHtml = `
                    <button class="members-toggle" onclick="toggleMembers(this)">
                        Lihat ${memberCount} Anggota Keluarga
                        <span class="members-toggle-icon">▼</span>
                    </button>
                    <div class="members-container">
                        <table class="family-members-table">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Hubungan</th>
                                    <th>Gender</th>
                                    <th>Usia</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${family.members.map(member => `
                                    <tr>
                                        <td data-label="Nama">${member.name || 'Tanpa Nama'}</td>
                                        <td data-label="Hubungan">${member.relationship || '-'}</td>
                                        <td data-label="Gender">${member.gender || '-'}</td>
                                        <td data-label="Usia">${PkmKabenApp.calculateAge(member.birth_date) || '-'}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
            }

            return `
                <div class="family-card">
                    <h4 class="text-md font-semibold">Keluarga ${family.family_number || family.id || '-'}</h4>
                    <p><strong>Kepala Keluarga:</strong> ${family.head_name || '-'}</p>
                    
                    <div class="mt-2">
                        <h5 class="font-semibold">Status Fasilitas (dari cache):</h5>
                        <div class="facility-status">
                            <span class="status-indicator ${family.has_clean_water ? 'status-yes' : 'status-no'}">
                                Air Bersih: ${family.has_clean_water ? 'Ya' : 'Tidak'}
                            </span>
                            <span class="status-indicator ${family.is_water_protected ? 'status-yes' : 'status-no'}">
                                Air Bersih Terlindungi: ${family.is_water_protected ? 'Ya' : 'Tidak'}
                            </span>
                            <span class="status-indicator ${family.has_toilet ? 'status-yes' : 'status-no'}">
                                Memiliki Toilet: ${family.has_toilet ? 'Ya' : 'Tidak'}
                            </span>
                            <span class="status-indicator ${family.is_toilet_sanitary ? 'status-yes' : 'status-no'}">
                                Toilet Saniter: ${family.is_toilet_sanitary ? 'Ya' : 'Tidak'}
                            </span>
                        </div>
                    </div>
                    
                    <div class="mt-2">
                        <h5 class="font-semibold">Anggota Keluarga:</h5>
                        ${membersHtml}
                    </div>
            </div>
        `;
        }).join('');
    },
    
    // Format konten yang diblur
    formatBlurredContent(building) {
        const familiesHtml = building.families && Array.isArray(building.families) ?
            building.families.map(family => `
                <div class="family-card">
                    <h4 class="text-md font-semibold">Keluarga ${family.family_number || '-'}</h4>
                    <p><strong>Kepala Keluarga:</strong> ${family.head_name || '-'}</p>
                    <div class="blur-content">
                        <div class="mt-2">
                            <h5 class="font-semibold">Status Fasilitas:</h5>
                            <div class="facility-status">
                                <!-- Status fasilitas yang diblur -->
                                Data tidak tersedia
                            </div>
                        </div>
                        <div class="mt-2">
                            <h5 class="font-semibold">Anggota Keluarga:</h5>
                            <!-- Anggota keluarga yang diblur -->
                            Data tidak tersedia
                        </div>
                    </div>
                </div>
            `).join('') : '<p>Tidak ada data keluarga.</p>';

        return `
            <div class="modal-header">
                <h2 class="text-xl font-bold">Detail Rumah ${building.building_number || 'N/A'}</h2>
            </div>
            
            <div class="detail-section">
                <h3 class="text-lg font-semibold">Informasi Rumah</h3>
                <p><strong>Nomor Rumah:</strong> ${building.building_number || 'N/A'}</p>
                <p><strong>Desa:</strong> ${building.village?.name || 'Tidak diketahui'}</p>
            </div>

            <div class="detail-section">
                <h3 class="text-lg font-semibold">Informasi Keluarga</h3>
                ${familiesHtml}
                <div class="login-overlay">
                    <div class="login-message">
                        <p>Silakan <a href="/admin">login</a> untuk melihat detail lengkap</p>
                    </div>
                </div>
            </div>
        `;
    },
    
    // Hitung usia dari tanggal lahir
    calculateAge(birthDate) {
        if (!birthDate) return '-';
        
        try {
            const birth = new Date(birthDate);
            const today = new Date();
            let age = today.getFullYear() - birth.getFullYear();
            const monthDiff = today.getMonth() - birth.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
                age--;
            }
            
            return age;
        } catch (error) {
            console.error('Error calculating age:', error);
            return '-';
        }
    },
    
    // Buka modal
    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'block';
            // Gunakan setTimeout dengan waktu sangat singkat untuk memastikan transisi CSS berjalan dengan baik
            setTimeout(() => {
                modal.classList.add('show');
            }, 10);
        }
    },
    
    // Hitung jumlah anggota keluarga
    countFamilyMembers(family) {
        // Periksa berbagai kemungkinan struktur data
        if (family.members && Array.isArray(family.members)) {
            return family.members.length;
        } else if (family.anggota && Array.isArray(family.anggota)) {
            return family.anggota.length;
        } else if (family.member_count && typeof family.member_count === 'number') {
            return family.member_count;
        } else if (family.jumlah_anggota && typeof family.jumlah_anggota === 'number') {
            return family.jumlah_anggota;
        }
        
        // Default jika tidak ada data yang cocok
        return 0;
    }
};
// Routing dan navigasi
PkmKabenApp.routing = {
    // Tampilkan rute ke target
    async showRoute(targetLat, targetLng) {
        document.getElementById('routeLoading').classList.add('active');
        
        try {
            // Debug koordinat target
            console.log('Target coordinates:', {
                targetLat: targetLat,
                targetLng: targetLng,
                targetLatType: typeof targetLat,
                targetLngType: typeof targetLng
            });

            // Dapatkan lokasi pengguna
            const userLocation = await this.getUserLocation();
            console.log('User location:', userLocation);

            // Cari building berdasarkan koordinat target untuk prioritas marker
            const targetBuilding = PkmKabenApp.state.allBuildingsData.find(building => {
                const bLat = parseFloat(building.latitude);
                const bLng = parseFloat(building.longitude);
                // Bandingkan dengan toleransi kecil karena floating point
                return Math.abs(bLat - targetLat) < 0.0001 && Math.abs(bLng - targetLng) < 0.0001;
            });
            
            // Jika building ditemukan, set marker sebagai prioritas
            if (targetBuilding) {
                PkmKabenApp.state.markers.forEach(({marker, building}) => {
                    if (building.id === targetBuilding.id) {
                        PkmKabenApp.setMarkerPriority(marker, building);
                    }
                });
            }

            // Convert koordinat ke number jika string
            const userLat = parseFloat(userLocation[0]);
            const userLng = parseFloat(userLocation[1]);
            const destLat = parseFloat(targetLat);
            const destLng = parseFloat(targetLng);

            console.log('Converted coordinates:', {
                userLat, userLng, destLat, destLng
            });

            // Validasi koordinat
            if (!PkmKabenApp.isValidCoordinate(userLat, userLng)) {
                throw new Error(`Koordinat lokasi Anda tidak valid: ${userLat}, ${userLng}`);
            }

            if (!PkmKabenApp.isValidCoordinate(destLat, destLng)) {
                throw new Error(`Koordinat tujuan tidak valid: ${destLat}, ${destLng}`);
            }

            // Hapus rute sebelumnya
            if (PkmKabenApp.state.routingControl) {
                PkmKabenApp.state.map.removeControl(PkmKabenApp.state.routingControl);
            }

            // Buat routing control baru
            PkmKabenApp.state.routingControl = L.Routing.control({
                waypoints: [
                    L.latLng(userLat, userLng),
                    L.latLng(destLat, destLng)
                ],
                router: L.Routing.osrmv1({
                    serviceUrl: 'https://router.project-osrm.org/route/v1',
                    profile: 'car'
                }),
                lineOptions: {
                    styles: [{color: '#3498db', weight: 4, opacity: 0.7}]
                },
                showAlternatives: false,
                addWaypoints: false,
                draggableWaypoints: false,
                fitSelectedRoutes: true,
                show: false
            }).addTo(PkmKabenApp.state.map);

            // Event listener untuk rute yang ditemukan
            PkmKabenApp.state.routingControl.on('routesfound', function(e) {
                try {
                    const route = e.routes[0];
                    const distance = (route.summary.totalDistance / 1000).toFixed(1);
                    const time = Math.round(route.summary.totalTime / 60);

                    // Pastikan bounds valid sebelum menggunakannya
                    if (route.bounds && route.bounds.isValid()) {
                        PkmKabenApp.state.map.fitBounds(route.bounds, {
                            padding: [50, 50]
                        });
                    }

                    // Tampilkan popup informasi rute
                    const popupContent = `
                        <div class="route-summary">
                            <h4>Informasi Rute:</h4>
                            <p>Jarak: ${distance} km</p>
                            <p>Waktu tempuh: ± ${time} menit</p>
                            <button class="popup-button detail-button" onclick="PkmKabenApp.routing.showRouteInstructions(${JSON.stringify(route.instructions).replace(/"/g, "&quot;")})">
                                Lihat Petunjuk Arah
                            </button>
                        </div>
                    `;

                    L.popup()
                        .setLatLng(L.latLng(destLat, destLng))
                        .setContent(popupContent)
                        .openOn(PkmKabenApp.state.map);

                } catch (error) {
                    console.error('Error handling route:', error);
                }
            });

            PkmKabenApp.state.routingControl.on('routingerror', function(e) {
                console.error('Routing error:', e);
                PkmKabenApp.showError('Terjadi kesalahan saat mencari rute. Silakan coba lagi.');
            });

        } catch (error) {
            console.error('Error in showRoute:', error);
            PkmKabenApp.showError(error.message || 'Terjadi kesalahan saat menampilkan rute');
        } finally {
            document.getElementById('routeLoading').classList.remove('active');
        }
    },
    
    // Fungsi untuk menampilkan instruksi rute
    showRouteInstructions(instructions) {
        const modal = document.getElementById('routeModal');
        const instructionsContainer = document.getElementById('routeInstructions');
        
        if (!modal || !instructionsContainer) {
            this.createRouteModal();
        }
        
        const instructionsHtml = `
            <ul>
                ${instructions.map(instruction => `
                    <li>${instruction.text} (${Math.round(instruction.distance)}m)</li>
                `).join('')}
            </ul>
        `;
        
        document.getElementById('routeInstructions').innerHTML = instructionsHtml;
        document.getElementById('routeModal').style.display = 'block';
    },
    
    // Buat modal instruksi rute jika belum ada
    createRouteModal() {
        if (!document.getElementById('routeModal')) {
            const modalHTML = `
                <div id="routeModal" class="modal">
                    <div class="modal-content">
                        <span class="close" onclick="PkmKabenApp.routing.closeRouteModal()">&times;</span>
                        <div class="route-instructions">
                            <h4>Petunjuk Arah</h4>
                            <div id="routeInstructions"></div>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', modalHTML);
        }
    },
    
    // Tutup modal rute
    closeRouteModal() {
        const modal = document.getElementById('routeModal');
        if (modal) {
            modal.style.display = 'none';
        }
    },
    
    // Dapatkan lokasi pengguna
    async getUserLocation() {
        // Jika sudah ada lokasi pengguna yang tersimpan, gunakan itu
        if (PkmKabenApp.state.userLocation) {
            return PkmKabenApp.state.userLocation;
        }
        
        return new Promise((resolve, reject) => {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const userLat = position.coords.latitude;
                    const userLng = position.coords.longitude;
                    PkmKabenApp.state.userLocation = [userLat, userLng];
                    resolve([userLat, userLng]);
                },
                (error) => {
                    console.error('Geolocation error:', error);
                    reject('Tidak dapat mengakses lokasi. Pastikan GPS aktif.');
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        });
    }
};
// Utility functions
PkmKabenApp.isUserLoggedIn = function() {
    return this.state.isLoggedIn;
};

PkmKabenApp.closeModal = function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
        // Untuk memastikan modal benar-benar tertutup, tambahkan display none
        // setelah animasi transisi selesai
        setTimeout(function() {
            modal.style.display = 'none';
        }, 300); // Waktu yang sama dengan durasi transisi CSS
    }
    
    // Hapus rute jika menutup modal bangunan
    if (modalId === 'buildingModal' && PkmKabenApp.state.routingControl) {
        PkmKabenApp.state.map.removeControl(PkmKabenApp.state.routingControl);
        PkmKabenApp.state.routingControl = null;
    }
};

// Expose metode publik untuk dipanggil dari HTML
PkmKabenApp.showBuildingDetails = function(buildingId) {
    PkmKabenApp.details.showBuildingDetails(buildingId);
};

PkmKabenApp.showRoute = function(targetLat, targetLng) {
    console.log('PkmKabenApp.showRoute dipanggil dengan koordinat:', targetLat, targetLng);
    
    // Jika lokasi pengguna tidak tersedia, minta izin lokasi terlebih dahulu
    if (!PkmKabenApp.state.userLocation) {
        PkmKabenApp.showProgress("Mengakses lokasi Anda...");
        
        navigator.geolocation.getCurrentPosition(
            (position) => {
                PkmKabenApp.hideProgress();
                PkmKabenApp.state.userLocation = [position.coords.latitude, position.coords.longitude];
    PkmKabenApp.routing.showRoute(targetLat, targetLng);
            },
            (error) => {
                PkmKabenApp.hideProgress();
                PkmKabenApp.showError('Tidak dapat mengakses lokasi Anda. Aktifkan GPS dan izinkan akses lokasi untuk menggunakan fitur rute.');
                console.error('Geolocation error:', error);
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    } else {
        PkmKabenApp.routing.showRoute(targetLat, targetLng);
    }
};

// Inisialisasi aplikasi saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi app
    PkmKabenApp.init();
    
    // Ekspos fungsi cek koordinat untuk dipanggil dari console atau form
    window.checkCoordinates = function(lat, lng) {
        PkmKabenApp.focusToCoordinates(lat, lng);
    };

    // Fungsi untuk mengambil peta dan menampilkan semua bounds
    window.showAllBoundaries = function() {
        const buildings = PkmKabenApp.state.allBuildingsData;
        const validCoordinates = [];
        
        buildings.forEach(building => {
            const lat = parseFloat(building.latitude);
            const lng = parseFloat(building.longitude);
            
            if (PkmKabenApp.isValidCoordinate(lat, lng)) {
                validCoordinates.push([lat, lng]);
            }
        });
        
        if (validCoordinates.length > 0) {
            PkmKabenApp.state.map.fitBounds(validCoordinates, {
                padding: [50, 50]
            });
            return `Menampilkan batas dari ${validCoordinates.length} bangunan`;
        } else {
            return 'Tidak ada koordinat valid';
        }
    };
});

// Health Dashboard
PkmKabenApp.dashboard = {
    // State untuk dashboard
    state: {
        isCollapsed: false,
        charts: {
            iksDonut: null,
            indicatorsBar: null
        },
        data: {
            iks: 0,
            categories: {
                healthy: 0,
                praHealthy: 0,
                unhealthy: 0
            },
            indicators: {
                labels: [
                    'KB', 'K4', 'Persalinan', 'Imunisasi', 'ASI', 
                    'Tumbuh Kembang', 'TB', 'Hipertensi', 'Gangguan Jiwa', 
                    'Merokok', 'JKN', 'Air Bersih'
                ],
                values: []
            },
            stats: {
                houses: 0,
                families: 0,
                members: 0,
                visits: 0
            },
            priorities: {
                high: 0,
                medium: 0,
                low: 0
            }
        }
    },
    
    // Inisialisasi dashboard
    init() {
        // Inisialisasi event listeners
        this.attachEventListeners();
        
        // Buat chart
        this.createCharts();
        
        // Update statistik
        this.updateDashboardData();
    },
    
    // Attach event listeners
    attachEventListeners() {
        const toggleBtn = document.getElementById('toggleDashboardBtn');
        
        if (toggleBtn) {
            toggleBtn.addEventListener('click', this.toggleDashboard.bind(this));
        }
    },
    
    // Toggle dashboard collapsible
    toggleDashboard() {
        const dashboard = document.getElementById('healthDashboard');
        const toggleBtn = document.getElementById('toggleDashboardBtn');
        
        if (dashboard && toggleBtn) {
            dashboard.classList.toggle('collapsed');
            toggleBtn.classList.toggle('collapsed');
            this.state.isCollapsed = dashboard.classList.contains('collapsed');
            
            // Simpan status ke localStorage
            localStorage.setItem('dashboard_collapsed', this.state.isCollapsed);
        }
    },
    
    // Buat charts
    createCharts() {
        // Donut chart untuk IKS
        const iksCtx = document.getElementById('iksDonutChart');
        if (iksCtx) {
            this.state.charts.iksDonut = new Chart(iksCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Sehat', 'Pra-Sehat', 'Tidak Sehat'],
                    datasets: [{
                        data: [45, 35, 20],
                        backgroundColor: [
                            '#10b981',
                            '#f59e0b',
                            '#ef4444'
                        ],
                        hoverOffset: 4,
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': ' + context.raw + '%';
                                }
                            }
                        }
                    }
                }
            });
        }
        
        // Bar chart untuk indikator PIS-PK
        const indicatorsCtx = document.getElementById('indicatorsBarChart');
        if (indicatorsCtx) {
            // Dummy data
            const dummyValues = [65, 74, 82, 80, 56, 45, 33, 67, 54, 28, 90, 75];
            
            this.state.charts.indicatorsBar = new Chart(indicatorsCtx, {
                type: 'bar',
                data: {
                    labels: this.state.data.indicators.labels,
                    datasets: [{
                        label: 'Persentase Keluarga',
                        data: dummyValues,
                        backgroundColor: '#4f46e5',
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        },
                        x: {
                            ticks: {
                                font: {
                                    size: 9
                                },
                                maxRotation: 45,
                                minRotation: 45
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.raw + '%';
                                }
                            }
                        }
                    }
                }
            });
        }
    },
    
    // Update data dashboard
    updateDashboardData() {
        console.log('Updating dashboard data...');
        
        // Ambil data bangunan dari state
        const buildings = PkmKabenApp.state.allBuildingsData;
        
        if (!buildings || !Array.isArray(buildings) || buildings.length === 0) {
            console.warn('No building data available for dashboard');
            this.setDefaultRandomData();
            return;
        }
        
        console.log(`Processing ${buildings.length} buildings for dashboard`);
        
        // Inisialisasi counter
        let totalIKS = 0;
        let familiesWithIKS = 0;
        let healthyFamilies = 0;
        let praHealthyFamilies = 0;
        let unhealthyFamilies = 0;
        let totalFamilies = 0;
        let totalMembers = 0;
        let totalVisits = 0;
        let highPriority = 0;
        let mediumPriority = 0;
        let lowPriority = 0;
        
        // Untuk indikator PIS-PK
        const indicatorsTotals = Array(12).fill(0);
        const indicatorsFamilies = Array(12).fill(0);
        
        // Analisis setiap bangunan
        buildings.forEach(building => {
            // Jika bangunan memiliki keluarga
            if (building.families && Array.isArray(building.families)) {
                totalFamilies += building.families.length;
                
                // Analisis setiap keluarga
                building.families.forEach(family => {
                    try {
                        // Hitung anggota keluarga
                        if (family.members && Array.isArray(family.members)) {
                            totalMembers += family.members.length;
                        } else if (family.members_count) {
                            totalMembers += parseInt(family.members_count) || 0;
                        } else if (family.jumlah_anggota) {
                            totalMembers += parseInt(family.jumlah_anggota) || 0;
                        }
                        
                        // Hitung kunjungan
                        if (family.visits && Array.isArray(family.visits)) {
                            // Filter kunjungan bulan ini
                            const thisMonth = new Date().getMonth();
                            const thisYear = new Date().getFullYear();
                            
                            const visitsThisMonth = family.visits.filter(visit => {
                                if (!visit.date) return false;
                                const visitDate = new Date(visit.date);
                                return visitDate.getMonth() === thisMonth && 
                                       visitDate.getFullYear() === thisYear;
                            });
                            
                            totalVisits += visitsThisMonth.length;
                        } else if (family.visits_count) {
                            totalVisits += parseInt(family.visits_count) || 0;
                        }
                        
                        // Analisis IKS - Coba semua kemungkinan format field
                        let iksValue = null;
                        
                        // Coba berbagai format field IKS yang mungkin ada
                        if (family.iks !== undefined && family.iks !== null) {
                            iksValue = parseFloat(family.iks);
                        } else if (family.iks_value !== undefined && family.iks_value !== null) {
                            iksValue = parseFloat(family.iks_value);
                        } else if (family.health_index !== undefined && family.health_index !== null) {
                            iksValue = parseFloat(family.health_index);
                        } else if (family.skor_iks !== undefined && family.skor_iks !== null) {
                            iksValue = parseFloat(family.skor_iks);
                        } else if (family.indeks_keluarga_sehat !== undefined && family.indeks_keluarga_sehat !== null) {
                            iksValue = parseFloat(family.indeks_keluarga_sehat);
                        }
                        
                        // Jika nilai IKS valid
                        if (!isNaN(iksValue) && iksValue !== null) {
                            console.log(`Valid IKS value found: ${iksValue} for family ${family.id || 'unknown'}`);
                            totalIKS += iksValue;
                            familiesWithIKS++;
                            
                            // Kategorikan berdasarkan IKS
                            if (iksValue >= 0.8) {
                                healthyFamilies++;
                            } else if (iksValue >= 0.5) {
                                praHealthyFamilies++;
                            } else {
                                unhealthyFamilies++;
                            }
                        }
                        
                        // Analisis prioritas
                        let priorityLevel = 0;
                        
                        if (family.priority_level !== undefined) {
                            priorityLevel = parseInt(family.priority_level) || 0;
                        } else if (family.priority === true || family.priority === "1" || family.priority === 1) {
                            priorityLevel = 3; // Anggap prioritas tinggi
                        } else if (family.tingkat_prioritas !== undefined) {
                            priorityLevel = parseInt(family.tingkat_prioritas) || 0;
                        }
                        
                        if (priorityLevel >= 3) {
                            highPriority++;
                        } else if (priorityLevel === 2) {
                            mediumPriority++;
                        } else if (priorityLevel === 1) {
                            lowPriority++;
                        }
                        
                        // Analisis indikator PIS-PK
                        if (family.indicators && Array.isArray(family.indicators)) {
                            family.indicators.forEach((indicator, index) => {
                                if (index < 12) {
                                    indicatorsFamilies[index]++;
                                    if (indicator === true || indicator === 1 || indicator === '1' || indicator === 'ya' || indicator === 'Ya') {
                                        indicatorsTotals[index]++;
                                    }
                                }
                            });
                        }
                    } catch (error) {
                        console.error('Error processing family data:', error);
                    }
                });
            }
        });
        
        // Hitung rata-rata IKS
        const avgIKS = familiesWithIKS > 0 ? totalIKS / familiesWithIKS : 0;
        console.log(`Average IKS: ${avgIKS} (from ${familiesWithIKS} families with valid IKS out of ${totalFamilies} total families)`);
        
        // Hitung persentase kategori keluarga
        const totalCategorizedFamilies = healthyFamilies + praHealthyFamilies + unhealthyFamilies;
        const healthyPercentage = totalCategorizedFamilies > 0 ? Math.round((healthyFamilies / totalCategorizedFamilies) * 100) : 33;
        const praHealthyPercentage = totalCategorizedFamilies > 0 ? Math.round((praHealthyFamilies / totalCategorizedFamilies) * 100) : 33;
        const unhealthyPercentage = totalCategorizedFamilies > 0 ? 100 - healthyPercentage - praHealthyPercentage : 34;
        
        console.log(`Health categories: Healthy=${healthyPercentage}%, PraHealthy=${praHealthyPercentage}%, Unhealthy=${unhealthyPercentage}%`);
        
        // Pastikan jumlah persentase selalu 100%
        const sumPercentage = healthyPercentage + praHealthyPercentage + unhealthyPercentage;
        let adjustedHealthy = healthyPercentage;
        let adjustedPraHealthy = praHealthyPercentage;
        let adjustedUnhealthy = unhealthyPercentage;
        
        if (sumPercentage !== 100) {
            // Jika ada perbedaan, tambahkan/kurangi dari kategori terbesar
            if (healthyPercentage >= praHealthyPercentage && healthyPercentage >= unhealthyPercentage) {
                adjustedHealthy = healthyPercentage + (100 - sumPercentage);
            } else if (praHealthyPercentage >= healthyPercentage && praHealthyPercentage >= unhealthyPercentage) {
                adjustedPraHealthy = praHealthyPercentage + (100 - sumPercentage);
            } else {
                adjustedUnhealthy = unhealthyPercentage + (100 - sumPercentage);
            }
        }
        
        // Hitung persentase indikator
        const indicatorsPercentages = indicatorsFamilies.map((total, index) => {
            return total > 0 ? Math.round((indicatorsTotals[index] / total) * 100) : 0;
        });
        
        // Update state
        this.state.data.iks = avgIKS.toFixed(2);
        this.state.data.categories.healthy = adjustedHealthy;
        this.state.data.categories.praHealthy = adjustedPraHealthy;
        this.state.data.categories.unhealthy = adjustedUnhealthy;
        this.state.data.indicators.values = indicatorsPercentages;
        this.state.data.stats.houses = buildings.length;
        this.state.data.stats.families = totalFamilies;
        this.state.data.stats.members = totalMembers;
        this.state.data.stats.visits = totalVisits;
        this.state.data.priorities.high = highPriority;
        this.state.data.priorities.medium = mediumPriority;
        this.state.data.priorities.low = lowPriority;
        
        console.log('Dashboard data updated:', {
            iks: this.state.data.iks,
            categories: this.state.data.categories,
            stats: this.state.data.stats,
            indicators: this.state.data.indicators.values
        });
        
        // Update UI
        this.updateUI();
    },
    
    // Set default/random data untuk testing
    setDefaultRandomData() {
        // Randomize beberapa data untuk demonstrasi
        const randomIKS = (Math.random() * 0.5 + 0.3).toFixed(2); // IKS antara 0.3 dan 0.8
        const randomHealthy = Math.floor(Math.random() * 30) + 25; // 25-55%
        const randomPraHealthy = Math.floor(Math.random() * 30) + 20; // 20-50%
        const randomUnhealthy = 100 - randomHealthy - randomPraHealthy;
        
        const randomIndicators = Array(12).fill(0).map(() => Math.floor(Math.random() * 70) + 30); // 30-100%
        
        const randomHouses = Math.floor(Math.random() * 100) + 100; // 100-200 rumah
        const randomFamilies = randomHouses + Math.floor(Math.random() * 100); // Lebih banyak dari rumah
        const randomMembers = randomFamilies * (Math.floor(Math.random() * 2) + 2); // 2-4 anggota per keluarga
        const randomVisits = Math.floor(Math.random() * 50) + 10; // 10-60 kunjungan
        
        const randomHighPriority = Math.floor(Math.random() * 15) + 5; // 5-20 prioritas tinggi
        const randomMediumPriority = Math.floor(Math.random() * 20) + 15; // 15-35 prioritas sedang
        const randomLowPriority = Math.floor(Math.random() * 50) + 20; // 20-70 prioritas rendah
        
        // Update state
        this.state.data.iks = randomIKS;
        this.state.data.categories.healthy = randomHealthy;
        this.state.data.categories.praHealthy = randomPraHealthy;
        this.state.data.categories.unhealthy = randomUnhealthy;
        this.state.data.indicators.values = randomIndicators;
        this.state.data.stats.houses = randomHouses;
        this.state.data.stats.families = randomFamilies;
        this.state.data.stats.members = randomMembers;
        this.state.data.stats.visits = randomVisits;
        this.state.data.priorities.high = randomHighPriority;
        this.state.data.priorities.medium = randomMediumPriority;
        this.state.data.priorities.low = randomLowPriority;
        
        // Update UI
        this.updateUI();
    },
    
    // Update UI dashboard
    updateUI() {
        // Update IKS value
        const iksValueElement = document.getElementById('iksAvgValue');
        if (iksValueElement) {
            iksValueElement.textContent = this.state.data.iks;
        }
        
        // Update kategori keluarga
        const healthyCategoryElement = document.getElementById('healthyCategoryValue');
        const praHealthyCategoryElement = document.getElementById('praHealthyCategoryValue');
        const unhealthyCategoryElement = document.getElementById('unhealthyCategoryValue');
        
        if (healthyCategoryElement) {
            healthyCategoryElement.textContent = this.state.data.categories.healthy + '%';
        }
        
        if (praHealthyCategoryElement) {
            praHealthyCategoryElement.textContent = this.state.data.categories.praHealthy + '%';
        }
        
        if (unhealthyCategoryElement) {
            unhealthyCategoryElement.textContent = this.state.data.categories.unhealthy + '%';
        }
        
        // Update statistik area
        const housesCountElement = document.getElementById('housesCountValue');
        const familiesCountElement = document.getElementById('familiesCountValue');
        const membersCountElement = document.getElementById('membersCountValue');
        const visitsCountElement = document.getElementById('visitsCountValue');
        
        if (housesCountElement) {
            housesCountElement.textContent = this.state.data.stats.houses;
        }
        
        if (familiesCountElement) {
            familiesCountElement.textContent = this.state.data.stats.families;
        }
        
        if (membersCountElement) {
            membersCountElement.textContent = this.state.data.stats.members;
        }
        
        if (visitsCountElement) {
            visitsCountElement.textContent = this.state.data.stats.visits;
        }
        
        // Update prioritas kunjungan
        const highPriorityElement = document.getElementById('highPriorityCount');
        const mediumPriorityElement = document.getElementById('mediumPriorityCount');
        const lowPriorityElement = document.getElementById('lowPriorityCount');
        
        if (highPriorityElement) {
            highPriorityElement.textContent = this.state.data.priorities.high;
        }
        
        if (mediumPriorityElement) {
            mediumPriorityElement.textContent = this.state.data.priorities.medium;
        }
        
        if (lowPriorityElement) {
            lowPriorityElement.textContent = this.state.data.priorities.low;
        }
        
        // Update charts
        this.updateCharts();
    },
    
    // Update charts
    updateCharts() {
        // Update IKS donut chart
        if (this.state.charts.iksDonut) {
            this.state.charts.iksDonut.data.datasets[0].data = [
                this.state.data.categories.healthy,
                this.state.data.categories.praHealthy,
                this.state.data.categories.unhealthy
            ];
            this.state.charts.iksDonut.update();
        }
        
        // Update indicators bar chart
        if (this.state.charts.indicatorsBar) {
            this.state.charts.indicatorsBar.data.datasets[0].data = this.state.data.indicators.values;
            this.state.charts.indicatorsBar.update();
        }
    },
    
    // Reset dashboard data
    resetData() {
        // Reset charts
        if (this.state.charts.iksDonut) {
            this.state.charts.iksDonut.destroy();
            this.state.charts.iksDonut = null;
        }
        
        if (this.state.charts.indicatorsBar) {
            this.state.charts.indicatorsBar.destroy();
            this.state.charts.indicatorsBar = null;
        }
        
        // Recreate charts
        this.createCharts();
        
        // Reset data
        this.setDefaultRandomData();
    }
};

// Utility functions
PkmKabenApp.isUserLoggedIn = function() {
    return this.state.isLoggedIn;
};

// Tambahkan fungsi untuk menghapus cache bangunan
PkmKabenApp.clearBuildingsCache = function() {
    try {
        localStorage.removeItem('pkm_kaben_buildings');
        localStorage.removeItem('pkm_kaben_buildings_timestamp');
        console.log("Cache data bangunan berhasil dihapus");
        return true;
    } catch (error) {
        console.error("Gagal menghapus cache:", error);
        return false;
    }
};

// Tambahkan fungsi untuk memaksa reload data bangunan dari API
PkmKabenApp.forceReloadBuildings = async function() {
    try {
        PkmKabenApp.showProgress("Memuat ulang data bangunan dari server...");
        
        // Nonaktifkan cache sementara
        const originalCacheSetting = PkmKabenApp.config.cacheSettings.enabled;
        PkmKabenApp.config.cacheSettings.enabled = false;
        
        // Hapus cache yang ada
        PkmKabenApp.clearBuildingsCache();
        
        // Fetch data dari API
        console.log("Memuat data bangunan dari API (force reload)...");
        const response = await fetch(PkmKabenApp.config.apiEndpoints.buildings);
        
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        
        const buildings = await response.json();
        console.log(`Total buildings from API (force reload): ${buildings.length}`);
        
        // Simpan data ke state dan render
        PkmKabenApp.state.allBuildingsData = buildings;
        PkmKabenApp.renderBuildings(buildings);
        
        // Perbarui dashboard kesehatan
        if (PkmKabenApp.dashboard) {
            setTimeout(() => {
                PkmKabenApp.dashboard.updateDashboardData();
            }, 500);
        }
        
        // Simpan ke cache baru
        PkmKabenApp.config.cacheSettings.enabled = originalCacheSetting;
        if (originalCacheSetting) {
            PkmKabenApp.cacheBuildings(buildings);
        }
        
        PkmKabenApp.hideProgress();
        return true;
    } catch (error) {
        console.error('Error reloading buildings:', error);
        PkmKabenApp.showError('Gagal memuat ulang data bangunan. Silakan coba lagi.');
        PkmKabenApp.hideProgress();
        return false;
    }
};

// Event listener untuk tombol clear cache
document.addEventListener('DOMContentLoaded', function() {
    // ... existing code ...
    
    // Tambahkan event listener untuk tombol Clear Cache
    const clearCacheBtn = document.getElementById('clearCacheBtn');
    if (clearCacheBtn) {
        clearCacheBtn.addEventListener('click', function() {
            if (PkmKabenApp.clearBuildingsCache()) {
                PkmKabenApp.forceReloadBuildings().then(success => {
                    if (success) {
                        const coordinateResult = document.getElementById('coordinateResult');
                        if (coordinateResult) {
                            coordinateResult.innerHTML = '<div class="coordinate-status valid">✓ Cache dihapus dan data diperbarui dari server</div>';
                        }
                    }
                });
            }
        });
    }
    
    // ... existing code ...
});

// Tambahkan kode di bawah fungsi lain pada bagian <script>
document.addEventListener('DOMContentLoaded', function() {
    // Fungsi untuk panel koordinat
    const coordinateToggle = document.getElementById('coordinateToggle');
    const coordinatePanel = document.getElementById('coordinatePanel');
    const closeCoordinatePanel = document.getElementById('closeCoordinatePanel');
    const checkCoordinateBtn = document.getElementById('checkCoordinateBtn');
    const copyLinkBtn = document.getElementById('copyLinkBtn');
    const clearCacheBtn = document.getElementById('clearCacheBtn');
    const forceReloadBtn = document.getElementById('forceReloadBtn');
    const latitudeInput = document.getElementById('latitudeInput');
    const longitudeInput = document.getElementById('longitudeInput');
    const coordinateResult = document.getElementById('coordinateResult');
    
    // Hanya tambahkan event listeners jika pengguna telah login
    if (PkmKabenApp.isUserLoggedIn() && coordinateToggle && coordinatePanel && closeCoordinatePanel && 
        checkCoordinateBtn && latitudeInput && longitudeInput) {
        
        // Toggle panel koordinat
        coordinateToggle.addEventListener('click', function() {
            coordinatePanel.classList.add('active');
            coordinateToggle.style.display = 'none';
        });
        
        // Tutup panel koordinat
        closeCoordinatePanel.addEventListener('click', function() {
            coordinatePanel.classList.remove('active');
            coordinateToggle.style.display = 'flex';
        });
        
        // Cek koordinat
        checkCoordinateBtn.addEventListener('click', function() {
            const lat = latitudeInput.value.trim();
            const lng = longitudeInput.value.trim();
            
            if (lat === '' || lng === '') {
                coordinateResult.innerHTML = '<div class="coordinate-status invalid">⚠️ Masukkan nilai latitude dan longitude</div>';
                return;
            }
            
            try {
                const latNum = parseFloat(lat);
                const lngNum = parseFloat(lng);
                
                if (PkmKabenApp.isValidCoordinate(latNum, lngNum)) {
                    coordinateResult.innerHTML = '<div class="coordinate-status valid">✓ Koordinat valid</div>';
                    PkmKabenApp.focusToCoordinates(latNum, lngNum);
                    coordinatePanel.classList.remove('active');
                    coordinateToggle.style.display = 'flex';
                } else {
                    coordinateResult.innerHTML = '<div class="coordinate-status invalid">⚠️ Koordinat tidak valid. Pastikan latitude antara -90 dan 90, longitude antara -180 dan 180</div>';
                }
            } catch (e) {
                coordinateResult.innerHTML = '<div class="coordinate-status invalid">⚠️ Format koordinat tidak valid</div>';
            }
        });
        
        // Salin link dengan koordinat
        copyLinkBtn.addEventListener('click', function() {
            const lat = latitudeInput.value.trim();
            const lng = longitudeInput.value.trim();
            
            if (lat === '' || lng === '') {
                coordinateResult.innerHTML = '<div class="coordinate-status invalid">⚠️ Masukkan nilai latitude dan longitude</div>';
                return;
            }
            
            try {
                const latNum = parseFloat(lat);
                const lngNum = parseFloat(lng);
                
                if (PkmKabenApp.isValidCoordinate(latNum, lngNum)) {
                    const currentUrl = new URL(window.location.href);
                    currentUrl.searchParams.set('lat', latNum);
                    currentUrl.searchParams.set('lng', lngNum);
                    currentUrl.searchParams.set('zoom', '15');
                    
                    navigator.clipboard.writeText(currentUrl.toString())
                        .then(() => {
                            coordinateResult.innerHTML = '<div class="coordinate-status valid">✓ Link koordinat disalin ke clipboard</div>';
                        })
                        .catch(() => {
                            coordinateResult.innerHTML = `<div class="coordinate-status valid">✓ Link: ${currentUrl.toString()}</div>`;
                        });
                } else {
                    coordinateResult.innerHTML = '<div class="coordinate-status invalid">⚠️ Koordinat tidak valid</div>';
                }
            } catch (e) {
                coordinateResult.innerHTML = '<div class="coordinate-status invalid">⚠️ Format koordinat tidak valid</div>';
            }
        });
        
        // Clear cache
        if (clearCacheBtn) {
            clearCacheBtn.addEventListener('click', function() {
                if (PkmKabenApp.clearBuildingsCache()) {
                    coordinateResult.innerHTML = '<div class="coordinate-status valid">✓ Cache berhasil dihapus</div>';
                } else {
                    coordinateResult.innerHTML = '<div class="coordinate-status invalid">⚠️ Gagal menghapus cache</div>';
                }
            });
        }
        
        // Force reload data
        if (forceReloadBtn) {
            forceReloadBtn.addEventListener('click', function() {
                PkmKabenApp.forceReloadBuildings().then(success => {
                    if (success) {
                        coordinateResult.innerHTML = '<div class="coordinate-status valid">✓ Data berhasil diperbarui dari server</div>';
                    } else {
                        coordinateResult.innerHTML = '<div class="coordinate-status invalid">⚠️ Gagal memperbarui data</div>';
                    }
                });
            });
        }
        
        // Tambahkan validasi saat input untuk memberi feedback langsung
        [latitudeInput, longitudeInput].forEach(input => {
            input.addEventListener('input', function() {
                coordinateResult.innerHTML = '';
            });
        });
        
        // Jika ada parameter URL, isi otomatis form koordinat
        const urlParams = new URLSearchParams(window.location.search);
        const lat = urlParams.get('lat');
        const lng = urlParams.get('lng');
        
        if (lat && lng) {
            latitudeInput.value = lat;
            longitudeInput.value = lng;
        }
    }
});

// Tambahkan fungsi untuk menjalankan diagnostik
PkmKabenApp.runDiagnostics = async function() {
    // Jika pengguna tidak login, beri tahu dan keluar dari fungsi
    if (!this.isUserLoggedIn()) {
        alert('Anda harus login untuk menjalankan fungsi diagnostik.');
        return;
    }
    
    try {
        PkmKabenApp.showProgress("Menjalankan diagnostik sistem...");
        
        const diagnosticsUrl = '/system/diagnostics';
        console.log('Memuat diagnostik dari URL:', diagnosticsUrl);
        
        const response = await fetch(diagnosticsUrl);
        
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        
        const diagnostics = await response.json();
        console.log('Hasil diagnostik:', diagnostics);
        
        // Format hasil diagnostik untuk ditampilkan
        let resultHtml = `
            <div style="font-size: 14px; line-height: 1.5;">
                <h3 style="margin-top: 10px; color: #1e40af;">Hasil Diagnostik Sistem</h3>
                
                <div style="margin-top: 10px;">
                    <strong>Database:</strong><br>
                    <table style="width: 100%; border-collapse: collapse; margin-top: 5px;">
                        <tr>
                            <th style="text-align: left; padding: 4px; border-bottom: 1px solid #ddd; border-top: 1px solid #ddd;">Tabel</th>
                            <th style="text-align: right; padding: 4px; border-bottom: 1px solid #ddd; border-top: 1px solid #ddd;">Jumlah Data</th>
                        </tr>
                        <tr>
                            <td style="padding: 4px; border-bottom: 1px solid #f0f0f0;">Buildings</td>
                            <td style="text-align: right; padding: 4px; border-bottom: 1px solid #f0f0f0;">${diagnostics.database.tables.buildings.count}</td>
                        </tr>
                        <tr>
                            <td style="padding: 4px; border-bottom: 1px solid #f0f0f0;">Villages</td>
                            <td style="text-align: right; padding: 4px; border-bottom: 1px solid #f0f0f0;">${diagnostics.database.tables.villages.count}</td>
                        </tr>
                        <tr>
                            <td style="padding: 4px; border-bottom: 1px solid #f0f0f0;">Families</td>
                            <td style="text-align: right; padding: 4px; border-bottom: 1px solid #f0f0f0;">${diagnostics.database.tables.families.count}</td>
                        </tr>
                        <tr>
                            <td style="padding: 4px; border-bottom: 1px solid #f0f0f0;">Family Members</td>
                            <td style="text-align: right; padding: 4px; border-bottom: 1px solid #f0f0f0;">${diagnostics.database.tables.family_members.count}</td>
                        </tr>
                    </table>
                </div>
                
                <div style="margin-top: 10px;">
                    <strong>Building IDs:</strong><br>
                    <span style="font-family: monospace;">
                        First ID: ${diagnostics.database.tables.buildings.first_id || 'none'}<br>
                        Last ID: ${diagnostics.database.tables.buildings.last_id || 'none'}
                    </span>
                </div>
                
                <div style="margin-top: 10px;">
                    <strong>Server:</strong><br>
                    PHP: ${diagnostics.server.php_version}<br>
                    Laravel: ${diagnostics.server.laravel_version}
                </div>
                
                <div style="margin-top: 15px; font-size: 12px; color: #666;">
                    <em>Tip: Gunakan informasi ini untuk membantu pemecahan masalah.</em>
                </div>
            </div>
        `;
        
        const coordinateResult = document.getElementById('coordinateResult');
        if (coordinateResult) {
            coordinateResult.innerHTML = resultHtml;
        }
        
        // Provide suggested solution based on diagnostic results
        if (diagnostics.database.tables.buildings.count === 0) {
            // No buildings data
            const solutionHtml = `
                <div style="margin-top: 15px; padding: 10px; background-color: #fee2e2; border-left: 4px solid #ef4444; color: #7f1d1d;">
                    <strong>Masalah terdeteksi:</strong> Tidak ada data bangunan di database.<br>
                    <strong>Solusi:</strong> Tambahkan data bangunan melalui panel admin atau hubungi administrator sistem.
                </div>
            `;
            coordinateResult.innerHTML += solutionHtml;
        } else if (!diagnostics.database.tables.buildings.first_id) {
            // No first_id (likely no buildings or database issue)
            const solutionHtml = `
                <div style="margin-top: 15px; padding: 10px; background-color: #fee2e2; border-left: 4px solid #ef4444; color: #7f1d1d;">
                    <strong>Masalah terdeteksi:</strong> Masalah dengan data ID bangunan.<br>
                    <strong>Solusi:</strong> Periksa struktur database dan pastikan data bangunan memiliki ID yang valid.
                </div>
            `;
            coordinateResult.innerHTML += solutionHtml;
        }
        
    } catch (error) {
        console.error('Error running diagnostics:', error);
        
        const coordinateResult = document.getElementById('coordinateResult');
        if (coordinateResult) {
            coordinateResult.innerHTML = `
                <div style="color: #b91c1c; margin-top: 10px;">
                    <strong>Error:</strong> Gagal menjalankan diagnostik.<br>
                    ${error.message || 'Silakan coba lagi nanti.'}
                </div>
            `;
        }
    } finally {
        PkmKabenApp.hideProgress();
    }
};

// Event listener untuk tombol diagnostik
document.addEventListener('DOMContentLoaded', function() {
    // ... existing code ...
    
    // Tambahkan event listener untuk tombol Run Diagnostics
    const runDiagnosticsBtn = document.getElementById('runDiagnosticsBtn');
    if (runDiagnosticsBtn) {
        runDiagnosticsBtn.addEventListener('click', function() {
            PkmKabenApp.runDiagnostics();
        });
    }
    
    // ... existing code ...
});

PkmKabenApp.details = {
    async showBuildingDetails(buildingId) {
        PkmKabenApp.showProgress("Memuat detail bangunan...");
        
        try {
            const url = `${PkmKabenApp.config.apiEndpoints.buildingDetail}${buildingId}`;
            console.log('Memuat detail bangunan dari URL:', url);
            
            let response = await fetch(url);
            let usedEndpoint = 'utama';
            
            console.log('Response status:', response.status);
            
            // Jika response tidak OK, coba gunakan endpoint debug
            if (!response.ok) {
                console.log('Endpoint utama gagal, mencoba endpoint alternatif');
                const debugUrl = `/debug/buildings/${buildingId}`;
                console.log('Memuat detail bangunan dari URL alternatif:', debugUrl);
                
                response = await fetch(debugUrl);
                usedEndpoint = 'alternatif';
                
                if (!response.ok) {
                    throw new Error(`Kedua endpoint gagal dengan status: ${response.status}`);
                }
            }
            
            const building = await response.json();
            console.log('Detail bangunan diterima:', building);
            
            // Format detail bangunan ke HTML
            let detailsHtml = '';
            
            if (usedEndpoint === 'alternatif') {
                detailsHtml += `
                    <div class="alert-info" style="background-color: #cff4fc; color: #055160; padding: 10px; margin-bottom: 15px; border-radius: 5px; border-left: 5px solid #9eeaf9;">
                        <strong>Info:</strong> Menggunakan endpoint alternatif karena endpoint utama tidak tersedia.
                    </div>
                `;
            }
            
            detailsHtml += this.formatBuildingDetails(building);
            
            const buildingDetailsElement = document.getElementById('buildingDetails');
            if (buildingDetailsElement) {
                buildingDetailsElement.innerHTML = detailsHtml;
                PkmKabenApp.openModal('buildingModal');
            } else {
                throw new Error('Elemen buildingDetails tidak ditemukan');
            }
            
        } catch (error) {
            console.error('Error fetching building details:', error);
            
            // Coba gunakan data dari cache jika API gagal
            const cachedBuilding = PkmKabenApp.state.allBuildingsData.find(b => b.id == buildingId);
            if (cachedBuilding) {
                console.log('Menggunakan data cache untuk building ID:', buildingId);
                PkmKabenApp.showFallbackBuildingDetails(cachedBuilding);
            } else {
                PkmKabenApp.showError(`Gagal memuat detail bangunan. Silakan coba lagi.`);
            }
        } finally {
            PkmKabenApp.hideProgress();
        }
    },

    formatBuildingDetails: function(building) {
        // Dapatkan informasi desa
        let villageName = 'Tidak diketahui';
        if (building.village) {
            if (typeof building.village === 'object' && building.village.name) {
                villageName = building.village.name;
            } else if (typeof building.village === 'string') {
                villageName = building.village;
            }
        } else if (building.village_name) {
            villageName = building.village_name;
        } else if (building.desa) {
            if (typeof building.desa === 'object' && building.desa.name) {
                villageName = building.desa.name;
            } else if (typeof building.desa === 'string') {
                villageName = building.desa;
            }
        } else if (building.desa_name) {
            villageName = building.desa_name;
        }
        
        // Format families
        let familiesHtml = '<p>Tidak ada data keluarga.</p>';
        if (building.families && Array.isArray(building.families) && building.families.length > 0) {
            familiesHtml = building.families.map(family => {
                // Format anggota keluarga
                let membersHtml = '<p>Tidak ada data anggota keluarga.</p>';
                
                if (family.members && Array.isArray(family.members) && family.members.length > 0) {
                    membersHtml = `
                        <button class="members-toggle" onclick="toggleMembers(this)">
                            Lihat ${family.members.length} Anggota Keluarga
                            <span class="members-toggle-icon">▼</span>
                        </button>
                        <div class="members-container">
                            <table class="family-members-table">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Hubungan</th>
                                        <th>Gender</th>
                                        <th>Usia</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${family.members.map(member => `
                                        <tr>
                                            <td data-label="Nama">${member.name || 'Tanpa Nama'}</td>
                                            <td data-label="Hubungan">${member.relationship || '-'}</td>
                                            <td data-label="Gender">${member.gender || '-'}</td>
                                            <td data-label="Usia">${PkmKabenApp.calculateAge(member.birth_date) || '-'}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    `;
                }
                
                // Dapatkan nama kepala keluarga
                let headName = 'Tidak diketahui';
                if (family.head_name) {
                    headName = family.head_name;
                } else if (family.head && family.head.name) {
                    headName = family.head.name;
                } else if (family.name) {
                    headName = family.name;
                } else if (family.family_name) {
                    headName = family.family_name;
                } else if (family.kepala_keluarga) {
                    headName = family.kepala_keluarga;
                }
                
                // Format data fasilitas kesehatan keluarga
                const hasCleanWater = family.has_clean_water ? 'Ya' : 'Tidak';
                const isWaterProtected = family.is_water_protected ? 'Ya' : 'Tidak';
                const hasToilet = family.has_toilet ? 'Ya' : 'Tidak';
                const isToiletSanitary = family.is_toilet_sanitary ? 'Ya' : 'Tidak';
                
                return `
                    <div class="family-card">
                        <h4 class="text-md font-semibold">Keluarga ${family.family_number || family.id || '-'}</h4>
                        <p><strong>Kepala Keluarga:</strong> ${headName}</p>
                        
                        <div class="mt-2">
                            <h5 class="font-semibold">Status Fasilitas:</h5>
                            <div class="facility-status">
                                <span class="status-indicator ${family.has_clean_water ? 'status-yes' : 'status-no'}">
                                    Air Bersih: ${hasCleanWater}
                                </span>
                                <span class="status-indicator ${family.is_water_protected ? 'status-yes' : 'status-no'}">
                                    Air Bersih Terlindungi: ${isWaterProtected}
                                </span>
                                <span class="status-indicator ${family.has_toilet ? 'status-yes' : 'status-no'}">
                                    Memiliki Toilet: ${hasToilet}
                                </span>
                                <span class="status-indicator ${family.is_toilet_sanitary ? 'status-yes' : 'status-no'}">
                                    Toilet Saniter: ${isToiletSanitary}
                                </span>
                            </div>
                        </div>
                        
                        <div class="mt-2">
                            <h5 class="font-semibold">Anggota Keluarga:</h5>
                            ${membersHtml}
                        </div>
                    </div>
                `;
            }).join('');
        }
        
        return `
            <div class="modal-header">
                <h2 class="text-xl font-bold">Detail Rumah ${building.building_number || 'N/A'}</h2>
            </div>
            
            <div class="detail-section">
                <h3 class="text-lg font-semibold">Informasi Rumah</h3>
                <p><strong>ID Bangunan:</strong> ${building.id || 'N/A'}</p>
                <p><strong>Nomor Rumah:</strong> ${building.building_number || 'N/A'}</p>
                <p><strong>Desa:</strong> ${villageName}</p>
                <p><strong>Koordinat:</strong> ${building.latitude || '-'}, ${building.longitude || '-'}</p>
                <p><strong>Jumlah Keluarga:</strong> ${(building.families && Array.isArray(building.families)) ? building.families.length : 0}</p>
            </div>

            <div class="detail-section">
                <h3 class="text-lg font-semibold">Informasi Keluarga</h3>
                ${familiesHtml}
            </div>
        `;
    }
};
    </script>
    
    <!-- JavaScript untuk handle toggle members -->
    <script>
        function toggleMembers(button) {
            // Temukan container members di bawah tombol
            const membersContainer = button.nextElementSibling;
            
            // Toggle class 'collapsed' pada container
            membersContainer.classList.toggle('collapsed');
            
            // Toggle class 'collapsed' pada tombol untuk mengubah ikon
            button.classList.toggle('collapsed');
            
            // Ubah teks tombol
            const memberCount = button.textContent.match(/\d+/)[0];
            if (button.classList.contains('collapsed')) {
                button.innerHTML = `Tampilkan ${memberCount} Anggota Keluarga <span class="members-toggle-icon">▼</span>`;
            } else {
                button.innerHTML = `Sembunyikan Anggota Keluarga <span class="members-toggle-icon">▲</span>`;
            }
        }
    </script>
    
    <!-- ====== MAP FEATURES UPGRADE - START ====== -->
    
    <!-- Feature flags dari server -->
    <script>
        window.MAP_FEATURES = @json(config('map', []));
    </script>
    
    <!-- Turf.js untuk perhitungan geometri -->
    @if(config('map.enable_measure') || config('map.enable_buffers') || config('map.enable_nearest'))
    <script src="https://cdn.jsdelivr.net/npm/@turf/turf@6/turf.min.js"></script>
    @endif
    
    <!-- MapLibre GL untuk Vector Tiles (optional) -->
    @if(config('map.use_vector_tiles'))
    <script src="https://unpkg.com/maplibre-gl@3/dist/maplibre-gl.js"></script>
    <link href="https://unpkg.com/maplibre-gl@3/dist/maplibre-gl.css" rel="stylesheet" />
    @endif
    
    <!-- Map Features CSS -->
    <link rel="stylesheet" href="{{ asset('css/map-features.css') }}">
    
    <!-- Map Features JavaScript -->
    <script src="{{ asset('js/map-features.js') }}"></script>
    
    <!-- Map Tools JavaScript (Measurement, Buffer, Nearest) -->
    <script src="{{ asset('js/map-tools.js') }}"></script>
    
    <!-- ====== MAP FEATURES UPGRADE - END ====== -->
    
    <!-- JavaScript akan ditambahkan pada bagian selanjutnya -->
</body>
</html>
