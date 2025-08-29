/**
 * Sync Manager untuk sinkronisasi data offline
 */
class SyncManager {
    constructor(dbManager) {
        this.dbManager = dbManager;
        this.isOnline = navigator.onLine;
        this.syncInProgress = false;
        
        // Set event listeners untuk status online
        window.addEventListener('online', () => this.handleOnlineStatus(true));
        window.addEventListener('offline', () => this.handleOnlineStatus(false));
        
        // Event untuk memperbarui UI
        this.events = {
            syncStart: new Event('sync:start'),
            syncComplete: new Event('sync:complete'),
            syncError: new Event('sync:error'),
            onlineStatusChanged: new Event('online:changed')
        };
        
        // Setup auto sync
        this.setupAutoSync();
    }
    
    /**
     * Setup auto sync saat online
     */
    setupAutoSync() {
        // Sinkronisasi pertama kali saat load jika online
        if (this.isOnline) {
            setTimeout(() => {
                this.syncData();
            }, 2000); // Delay 2 detik setelah load
        }
        
        // Sinkronisasi berkala jika online
        setInterval(() => {
            if (this.isOnline && !this.syncInProgress) {
                this.syncData();
            }
        }, 5 * 60 * 1000); // Tiap 5 menit
    }
    
    /**
     * Menangani perubahan status online
     * @param {boolean} isOnline - Status online
     */
    handleOnlineStatus(isOnline) {
        this.isOnline = isOnline;
        
        // Update badge status sinkronisasi
        this.updateSyncUI();
        
        // Dispatch event online status changed
        window.dispatchEvent(this.events.onlineStatusChanged);
        
        if (isOnline) {
            // Coba sinkronisasi ketika kembali online
            this.syncData();
        }
    }
    
    /**
     * Memperbarui UI status sinkronisasi
     */
    async updateSyncUI() {
        const syncStatus = document.getElementById('sync-status');
        const syncBadge = document.getElementById('sync-badge');
        const syncButton = document.getElementById('sync-button');
        
        if (!syncStatus || !syncBadge || !syncButton) {
            return; // UI elements belum diinisialisasi
        }
        
        try {
            // Tangkap error jika getUnsyncedCount() gagal
            let count = 0;
            try {
                count = await this.dbManager.getUnsyncedCount();
            } catch (e) {
                console.error('Error getting unsynced count:', e);
            }
            
            if (count > 0) {
                syncBadge.textContent = count;
                syncBadge.classList.remove('hidden');
                
                if (this.isOnline) {
                    syncStatus.textContent = 'Sinkronisasi Diperlukan';
                    syncStatus.className = 'text-yellow-600';
                    syncButton.disabled = false;
                } else {
                    syncStatus.textContent = 'Offline';
                    syncStatus.className = 'text-red-600';
                    syncButton.disabled = true;
                }
            } else {
                syncBadge.classList.add('hidden');
                
                if (this.isOnline) {
                    syncStatus.textContent = 'Tersinkronisasi';
                    syncStatus.className = 'text-green-600';
                } else {
                    syncStatus.textContent = 'Offline (Tersinkronisasi)';
                    syncStatus.className = 'text-orange-600';
                }
            }
        } catch (error) {
            console.error('Error updating sync UI:', error);
            
            // Tampilkan status error pada UI
            if (syncStatus) {
                syncStatus.textContent = 'Error Sinkronisasi';
                syncStatus.className = 'text-red-600';
            }
        }
    }
    
    /**
     * Sinkronisasi data
     * @returns {Promise} Promise yang diselesaikan ketika sinkronisasi selesai
     */
    async syncData() {
        // Cek apakah force offline mode diaktifkan
        const isForceOffline = localStorage.getItem('force_offline') === 'true';
        
        if (!this.isOnline || isForceOffline || this.syncInProgress) {
            return;
        }
        
        this.syncInProgress = true;
        window.dispatchEvent(this.events.syncStart);
        
        try {
            // Dapatkan semua item yang belum tersinkronisasi
            const unsyncedItems = await this.dbManager.getUnsyncedItems();
            
            if (unsyncedItems.length === 0) {
                this.syncInProgress = false;
                window.dispatchEvent(this.events.syncComplete);
                return;
            }
            
            // Urutkan berdasarkan waktu pembuatan
            unsyncedItems.sort((a, b) => {
                return new Date(a.created_at) - new Date(b.created_at);
            });
            
            console.log(`Starting sync of ${unsyncedItems.length} items`);
            
            // Proses setiap item
            for (const item of unsyncedItems) {
                try {
                    await this.processSyncItem(item);
                    await this.dbManager.markAsSynced(item.id);
                } catch (error) {
                    console.error(`Error syncing item ${item.id}:`, error);
                    // Bisa implementasikan retry logic di sini
                    
                    // Atau tandai sebagai error
                    item.sync_error = error.message;
                    await this.dbManager.save('sync_queue', item);
                }
            }
            
            // Update UI setelah sinkronisasi
            this.updateSyncUI();
            
            this.syncInProgress = false;
            window.dispatchEvent(this.events.syncComplete);
        } catch (error) {
            console.error('Error in sync process:', error);
            this.syncInProgress = false;
            
            // Dispatch sync error event
            const errorEvent = new CustomEvent('sync:error', { detail: error });
            window.dispatchEvent(errorEvent);
        }
    }
    
    /**
     * Proses sinkronisasi per item
     * @param {Object} item - Item antrian sinkronisasi
     * @returns {Promise} Promise yang diselesaikan ketika item berhasil diproses
     */
    async processSyncItem(item) {
        try {
            // Dapatkan CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            
            // Dapatkan Bearer token jika ada (untuk API)
            const bearerToken = localStorage.getItem('api_token');
            
            // Headers untuk request
            const headers = {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            };
            
            // Tambahkan bearer token jika ada
            if (bearerToken) {
                headers['Authorization'] = `Bearer ${bearerToken}`;
            }
            
            // Gunakan API sinkronisasi
            const response = await fetch('/api/sync', {
                method: 'POST',
                headers: headers,
                body: JSON.stringify({
                    entity: item.entity,
                    action: item.action,
                    data: item.data,
                    record_id: item.record_id
                })
            });
            
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
            }
            
            const responseData = await response.json();
            
            // Jika berhasil dan ada data yang dikembalikan, perbarui data lokal
            if (responseData.success && responseData.data) {
                await this.dbManager.save(item.entity, {
                    ...responseData.data,
                    sync_status: 'synced'
                });
            }
            
            return responseData;
        } catch (error) {
            console.error(`Error processing sync item (${item.entity}, ${item.action}):`, error);
            throw error;
        }
    }
    
    /**
     * Ambil data awal untuk sinkronisasi
     * @param {Object} options - Opsi untuk initial sync
     * @returns {Promise} Promise yang diselesaikan ketika initial sync selesai
     */
    async initialSync(options = {}) {
        // Cek apakah force offline mode diaktifkan
        const isForceOffline = localStorage.getItem('force_offline') === 'true';
        
        if (!this.isOnline || isForceOffline) {
            throw new Error('Cannot perform initial sync while offline');
        }
        
        try {
            // Tampilkan indikator loading
            if (typeof syncUI !== 'undefined') {
                syncUI.showSyncInProgress();
            }
            
            // Ambil timestamp sinkronisasi terakhir jika ada
            const lastSync = localStorage.getItem('last_sync_timestamp');
            
            // Buat query params
            const params = new URLSearchParams();
            
            if (lastSync) {
                params.append('last_sync', lastSync);
            }
            
            if (options.villageId) {
                params.append('village_id', options.villageId);
            }
            
            if (options.limit) {
                params.append('limit', options.limit);
            }
            
            // Dapatkan Bearer token jika ada (untuk API)
            const bearerToken = localStorage.getItem('api_token');
            
            // Headers untuk request
            const headers = {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            };
            
            // Tambahkan bearer token jika ada
            if (bearerToken) {
                headers['Authorization'] = `Bearer ${bearerToken}`;
            }
            
            // Buat request ke API
            const response = await fetch(`/api/initial-data?${params.toString()}`, {
                method: 'GET',
                headers: headers
            });
            
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.message || 'Failed to get initial data');
            }
            
            // Simpan data ke penyimpanan lokal
            let savedCount = 0;
            
            // Simpan bangunan
            for (const building of data.data.buildings) {
                await this.dbManager.save('buildings', {
                    ...building,
                    sync_status: 'synced'
                });
                savedCount++;
            }
            
            // Simpan keluarga
            for (const family of data.data.families) {
                await this.dbManager.save('families', {
                    ...family,
                    sync_status: 'synced'
                });
                savedCount++;
            }
            
            // Simpan anggota keluarga
            for (const member of data.data.family_members) {
                await this.dbManager.save('family_members', {
                    ...member,
                    sync_status: 'synced'
                });
                savedCount++;
            }
            
            //// Simpan timestamp sinkronisasi
            localStorage.setItem('last_sync_timestamp', data.meta.sync_timestamp);
            
            // Tampilkan notifikasi sukses
            if (typeof syncUI !== 'undefined') {
                syncUI.showSyncComplete();
                syncUI.showNotification(
                    'Initial Sync Complete',
                    `Successfully synced ${savedCount} records.`,
                    'success'
                );
            }
            
            console.log(`Initial sync complete: ${savedCount} records saved`);
            
            return {
                success: true,
                savedCount,
                meta: data.meta
            };
        } catch (error) {
            console.error('Initial sync error:', error);
            
            // Tampilkan error
            if (typeof syncUI !== 'undefined') {
                syncUI.showSyncError(error);
            }
            
            throw error;
        }
    }
    
    /**
     * Menyimpan data ke penyimpanan lokal dan menambahkan ke antrian sinkronisasi
     * @param {string} entity - Jenis entitas ('families', 'family_members', etc)
     * @param {Object} data - Data yang akan disimpan
     * @param {string} action - Jenis aksi ('create', 'update', 'delete')
     * @returns {Promise} Promise yang diselesaikan ketika data berhasil disimpan
     */
    async saveOfflineData(entity, data, action = 'update') {
        try {
            // Tandai sebagai belum tersinkronisasi
            data.sync_status = 'pending';
            data.updated_at = new Date().toISOString();
            
            // Simpan ke penyimpanan lokal
            const savedId = await this.dbManager.save(entity, data);
            
            // Tambahkan ke antrian sinkronisasi
            await this.dbManager.addToSyncQueue({
                entity,
                record_id: data.id,
                action,
                data
            });
            
            // Update UI
            this.updateSyncUI();
            
            // Jika online, coba sinkronisasi
            if (this.isOnline) {
                this.syncData();
            }
            
            return savedId;
        } catch (error) {
            console.error('Error saving offline data:', error);
            throw error;
        }
    }
}

// Create sync manager instance (akan digunakan oleh form handlers)
const syncManager = new SyncManager(dbManager);