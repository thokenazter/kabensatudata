import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';

// Global styles
import 'leaflet/dist/leaflet.css';
import 'leaflet.markercluster/dist/MarkerCluster.css';
import 'leaflet.markercluster/dist/MarkerCluster.Default.css';

// PWA registration
// This virtual module is provided by vite-plugin-pwa
import { registerSW } from 'virtual:pwa-register';

import App from './src/App.vue';

const updateSW = registerSW({ immediate: true });

const app = createApp(App);
app.use(createPinia());
app.mount('#app');
