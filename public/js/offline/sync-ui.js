/**
 * UI Komponen untuk status sinkronisasi
 */
class SyncUI {
    constructor(syncManager) {
        this.syncManager = syncManager;
        this.initialized = false;
        
        // Init setelah DOM loaded
        document.addEventListener('DOMContentLoaded', () => this.init());
    }
    
    /**
     * Inisialisasi UI komponen
     */
    init() {
        if (this.initialized) {
            return;
        }
        
        // Buat elemen status sinkronisasi jika belum ada
        this.createSyncStatusElement();
        
        // Add event listeners
        window.addEventListener('online:changed', () => this.updateStatusDisplay());
        window.addEventListener('sync:start', () => this.showSyncInProgress());
        window.addEventListener('sync:complete', () => this.showSyncComplete());
        window.addEventListener('sync:error', (e) => this.showSyncError(e.detail));
        
        // Tambahkan click handler pada tombol sync
        const syncButton = document.getElementById('sync-button');
        if (syncButton) {
            syncButton.addEventListener('click', () => this.syncManager.triggerSync());
        }
        
        // Initial update
        this.updateStatusDisplay();
        
        // Tambahkan tombol initial sync
        this.addInitialSyncButton();
        
        this.initialized = true;
    }
    
    /**
     * Membuat elemen status sinkronisasi
     */
    createSyncStatusElement() {
        // Periksa apakah elemen sudah ada
        if (document.getElementById('sync-status-container')) {
            return;
        }
        
        // Buat elemen container
        const container = document.createElement('div');
        container.id = 'sync-status-container';
        container.className = 'fixed bottom-4 right-4 bg-white shadow-lg rounded-lg p-3 z-50 flex items-center space-x-2';
        container.style.minWidth = '200px';
        
        // Status indicator
        const statusIndicator = document.createElement('div');
        statusIndicator.id = 'sync-indicator';
        statusIndicator.className = 'w-3 h-3 rounded-full bg-gray-400';
        
        // Status text
        const statusText = document.createElement('span');
        statusText.id = 'sync-status';
        statusText.className = 'text-gray-600 text-sm';
        statusText.textContent = 'Checking...';
        
        // Badge for unsynced count
        const badge = document.createElement('span');
        badge.id = 'sync-badge';
        badge.className = 'bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center hidden';
        badge.textContent = '0';
        
        // Sync button
        const syncButton = document.createElement('button');
        syncButton.id = 'sync-button';
        syncButton.className = 'text-blue-600 hover:text-blue-800 text-sm font-medium ml-auto';
        syncButton.textContent = 'Sync';
        
        // Append elements
        container.appendChild(statusIndicator);
        container.appendChild(statusText);
        container.appendChild(badge);
        container.appendChild(syncButton);
        
        // Append to body
        document.body.appendChild(container);
    }
    
    /**
     * Tambahkan tombol initial sync
     */
    addInitialSyncButton() {
        const container = document.getElementById('sync-status-container');
        
        if (!container || document.getElementById('initial-sync-button')) {
            return;
        }
        
        // Buat dropdown untuk opsi
        const dropdown = document.createElement('div');
        dropdown.className = 'relative inline-block text-left';
        dropdown.innerHTML = `
            <button id="initial-sync-button" class="ml-2 text-blue-600 hover:text-blue-800 text-sm font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </button>
            <div id="initial-sync-dropdown" class="hidden origin-top-right absolute right-0 bottom-full mb-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none">
                <div class="py-1">
                    <button class="text-left block w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-action="initial-sync-all">
                        Sync All Data
                    </button>
                    <button class="text-left block w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-action="initial-sync-village">
                        Sync Current Village
                    </button>
                </div>
            </div>
        `;
        
        container.appendChild(dropdown);
        
        // Toggle dropdown
        const button = document.getElementById('initial-sync-button');
        const dropdownMenu = document.getElementById('initial-sync-dropdown');
        
        button.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdownMenu.classList.toggle('hidden');
        });
        
        // Handle click outside
        document.addEventListener('click', () => {
            if (!dropdownMenu.classList.contains('hidden')) {
                dropdownMenu.classList.add('hidden');
            }
        });
        
        // Handle initial sync options
        dropdownMenu.addEventListener('click', async (e) => {
            const action = e.target.dataset.action;
            
            if (action === 'initial-sync-all') {
                try {
                    await this.syncManager.initialSync();
                } catch (error) {
                    console.error('Initial sync error:', error);
                }
            } else if (action === 'initial-sync-village') {
                // Get current village ID from URL or global variable
                const villageId = this.getCurrentVillageId();
                
                if (villageId) {
                    try {
                        await this.syncManager.initialSync({ villageId });
                    } catch (error) {
                        console.error('Initial sync error:', error);
                    }
                } else {
                    this.showNotification('Sync Error', 'No village ID found. Please select a village first.', 'error');
                }
            }
            
            dropdownMenu.classList.add('hidden');
        });
    }
    
    /**
     * Get current village ID from page
     */
    getCurrentVillageId() {
        // Try to get from URL
        const urlParams = new URLSearchParams(window.location.search);
        const villageId = urlParams.get('village_id');
        
        if (villageId) {
            return villageId;
        }
        
        // Try to get from global variable if exists
        if (typeof currentVillageId !== 'undefined') {
            return currentVillageId;
        }
        
        // Try to get from active village link
        const activeVillageLink = document.querySelector('.village-link.active');
        if (activeVillageLink && activeVillageLink.dataset.villageId) {
            return activeVillageLink.dataset.villageId;
        }
        
        return null;
    }
    
    /**
     * Update tampilan status
     */
    updateStatusDisplay() {
        const indicator = document.getElementById('sync-indicator');
        const statusText = document.getElementById('sync-status');
        
        if (!indicator || !statusText) {
            return;
        }
        
        if (this.syncManager.isOnline) {
            indicator.className = 'w-3 h-3 rounded-full bg-green-500';
            statusText.textContent = 'Online';
            statusText.className = 'text-green-600 text-sm';
        } else {
            indicator.className = 'w-3 h-3 rounded-full bg-red-500';
            statusText.textContent = 'Offline';
            statusText.className = 'text-red-600 text-sm';
        }
        
        // Request sync manager to update badge
        this.syncManager.updateSyncUI();
    }
    
    /**
     * Tampilkan indikator proses sinkronisasi
     */
    showSyncInProgress() {
        const indicator = document.getElementById('sync-indicator');
        const statusText = document.getElementById('sync-status');
        
        if (!indicator || !statusText) {
            return;
        }
        
        indicator.className = 'w-3 h-3 rounded-full bg-blue-500 animate-pulse';
        statusText.textContent = 'Syncing...';
        statusText.className = 'text-blue-600 text-sm';
    }
    
    /**
     * Tampilkan indikator sinkronisasi selesai
     */
    showSyncComplete() {
        const indicator = document.getElementById('sync-indicator');
        const statusText = document.getElementById('sync-status');
        
        if (!indicator || !statusText) {
            return;
        }
        
        indicator.className = 'w-3 h-3 rounded-full bg-green-500';
        statusText.textContent = 'Synced';
        statusText.className = 'text-green-600 text-sm';
        
        // Kembalikan ke normal setelah beberapa saat
        setTimeout(() => {
            this.updateStatusDisplay();
        }, 3000);
    }
    
    /**
     * Tampilkan indikator error sinkronisasi
     * @param {Error} error - Error object
     */
    showSyncError(error) {
        const indicator = document.getElementById('sync-indicator');
        const statusText = document.getElementById('sync-status');
        
        if (!indicator || !statusText) {
            return;
        }
        
        indicator.className = 'w-3 h-3 rounded-full bg-red-500';
        statusText.textContent = 'Sync Failed';
        statusText.className = 'text-red-600 text-sm';
        
        console.error('Sync error:', error);
        
        // Show error notification
        this.showNotification('Sync Error', error.message || 'Failed to synchronize data', 'error');
    }
    
    /**
     * Tampilkan notifikasi
     * @param {string} title - Judul notifikasi
     * @param {string} message - Pesan notifikasi
     * @param {string} type - Tipe notifikasi (success, error, info)
     */
    showNotification(title, message, type = 'info') {
        // Periksa apakah container notifikasi sudah ada
        let container = document.getElementById('notification-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'notification-container';
            container.className = 'fixed top-4 right-4 z-50 space-y-2';
            document.body.appendChild(container);
        }
        
        // Buat elemen notifikasi
        const notification = document.createElement('div');
        notification.className = 'rounded-lg shadow-lg p-4 max-w-xs transform transition-all duration-300 ease-in-out translate-x-full';
        
        // Set warna berdasarkan tipe
        switch (type) {
            case 'success':
                notification.className += ' bg-green-100 border-l-4 border-green-500';
                break;
            case 'error':
                notification.className += ' bg-red-100 border-l-4 border-red-500';
                break;
            default:
                notification.className += ' bg-blue-100 border-l-4 border-blue-500';
        }
        
        // Konten notifikasi
        notification.innerHTML = `
            <div class="flex items-start">
                <div class="ml-3 w-0 flex-1">
                    <p class="text-sm font-medium ${type === 'error' ? 'text-red-800' : type === 'success' ? 'text-green-800' : 'text-blue-800'}">
                        ${title}
                    </p>
                    <p class="mt-1 text-sm ${type === 'error' ? 'text-red-700' : type === 'success' ? 'text-green-700' : 'text-blue-700'}">
                        ${message}
                    </p>
                </div>
                <div class="ml-4 flex-shrink-0 flex">
                    <button class="inline-flex text-gray-400 hover:text-gray-500 focus:outline-none">
                        <span class="sr-only">Close</span>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        `;
        
        // Tambahkan ke container
        container.appendChild(notification);
        
        // Tampilkan dengan animasi
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 10);
        
        // Setup tutup notifikasi
        const closeButton = notification.querySelector('button');
        closeButton.addEventListener('click', () => {
            closeNotification();
        });
        
        // Auto tutup setelah beberapa detik
        const timeout = setTimeout(() => {
            closeNotification();
        }, 5000);
        
        // Fungsi tutup notifikasi
        function closeNotification() {
            clearTimeout(timeout);
            notification.classList.add('translate-x-full');
            notification.classList.add('opacity-0');
            
            setTimeout(() => {
                if (notification.parentNode === container) {
                    container.removeChild(notification);
                }
            }, 300);
        }
    }
}

// Buat instance SyncUI
const syncUI = new SyncUI(syncManager);