/**
 * Database Manager untuk operasi IndexedDB
 */
class DBManager {
    constructor() {
        this.dbName = 'pis_pk_offline_db';
        this.dbVersion = 1;
        this.db = null;
        
        // Inisialisasi database
        this.initDB();
    }
    
    /**
     * Inisialisasi koneksi database
     * @returns {Promise} Promise yang diselesaikan ketika database berhasil dibuka
     */
    initDB() {
        return new Promise((resolve, reject) => {
            if (this.db) {
                resolve(this.db);
                return;
            }
            
            const request = indexedDB.open(this.dbName, this.dbVersion);
            
            request.onerror = (event) => {
                console.error('Error opening database:', event.target.error);
                reject('Error opening database');
            };
            
            request.onsuccess = (event) => {
                this.db = event.target.result;
                console.log('Database opened successfully');
                resolve(this.db);
            };
            
            request.onupgradeneeded = (event) => {
                const db = event.target.result;
                
                // Buat object stores untuk setiap entitas
                if (!db.objectStoreNames.contains('families')) {
                    const familyStore = db.createObjectStore('families', { keyPath: 'id' });
                    familyStore.createIndex('building_id', 'building_id', { unique: false });
                    familyStore.createIndex('village_id', 'village_id', { unique: false });
                    familyStore.createIndex('sync_status', 'sync_status', { unique: false });
                }
                
                if (!db.objectStoreNames.contains('family_members')) {
                    const memberStore = db.createObjectStore('family_members', { keyPath: 'id' });
                    memberStore.createIndex('family_id', 'family_id', { unique: false });
                    memberStore.createIndex('sync_status', 'sync_status', { unique: false });
                }
                
                if (!db.objectStoreNames.contains('buildings')) {
                    const buildingStore = db.createObjectStore('buildings', { keyPath: 'id' });
                    buildingStore.createIndex('village_id', 'village_id', { unique: false });
                    buildingStore.createIndex('sync_status', 'sync_status', { unique: false });
                }
                
                if (!db.objectStoreNames.contains('sync_queue')) {
                    const syncQueueStore = db.createObjectStore('sync_queue', { 
                        keyPath: 'id', 
                        autoIncrement: true 
                    });
                    syncQueueStore.createIndex('entity', 'entity', { unique: false });
                    syncQueueStore.createIndex('record_id', 'record_id', { unique: false });
                    syncQueueStore.createIndex('action', 'action', { unique: false });
                    syncQueueStore.createIndex('synced', 'synced', { unique: false });
                    syncQueueStore.createIndex('created_at', 'created_at', { unique: false });
                }
            };
        });
    }
    
    /**
     * Mendapatkan semua data dari store
     * @param {string} storeName - Nama object store
     * @returns {Promise} Promise yang diselesaikan dengan array data
     */
    async getAll(storeName) {
        await this.initDB();
        
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction([storeName], 'readonly');
            const store = transaction.objectStore(storeName);
            const request = store.getAll();
            
            request.onsuccess = (event) => {
                resolve(event.target.result);
            };
            
            request.onerror = (event) => {
                console.error(`Error getting all from ${storeName}:`, event.target.error);
                reject(event.target.error);
            };
        });
    }
    
    /**
     * Mendapatkan data berdasarkan ID
     * @param {string} storeName - Nama object store
     * @param {number|string} id - ID record
     * @returns {Promise} Promise yang diselesaikan dengan data record
     */
    async getById(storeName, id) {
        await this.initDB();
        
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction([storeName], 'readonly');
            const store = transaction.objectStore(storeName);
            const request = store.get(id);
            
            request.onsuccess = (event) => {
                resolve(event.target.result);
            };
            
            request.onerror = (event) => {
                console.error(`Error getting ${id} from ${storeName}:`, event.target.error);
                reject(event.target.error);
            };
        });
    }
    
    /**
     * Menyimpan atau memperbarui data
     * @param {string} storeName - Nama object store
     * @param {Object} data - Data yang akan disimpan
     * @returns {Promise} Promise yang diselesaikan dengan ID record
     */
    async save(storeName, data) {
        await this.initDB();
        
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction([storeName], 'readwrite');
            const store = transaction.objectStore(storeName);
            
            // Tambahkan informasi sinkronisasi
            if (!data.sync_status) {
                data.sync_status = 'pending';
            }
            
            if (!data.updated_at) {
                data.updated_at = new Date().toISOString();
            }
            
            const request = store.put(data);
            
            request.onsuccess = (event) => {
                resolve(event.target.result);
            };
            
            request.onerror = (event) => {
                console.error(`Error saving to ${storeName}:`, event.target.error);
                reject(event.target.error);
            };
        });
    }
    
    /**
     * Menghapus data
     * @param {string} storeName - Nama object store
     * @param {number|string} id - ID record
     * @returns {Promise} Promise yang diselesaikan ketika data berhasil dihapus
     */
    async delete(storeName, id) {
        await this.initDB();
        
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction([storeName], 'readwrite');
            const store = transaction.objectStore(storeName);
            const request = store.delete(id);
            
            request.onsuccess = (event) => {
                resolve(true);
            };
            
            request.onerror = (event) => {
                console.error(`Error deleting ${id} from ${storeName}:`, event.target.error);
                reject(event.target.error);
            };
        });
    }
    
    /**
     * Mencari data berdasarkan indeks
     * @param {string} storeName - Nama object store
     * @param {string} indexName - Nama indeks
     * @param {*} value - Nilai yang dicari
     * @returns {Promise} Promise yang diselesaikan dengan array data
     */
    async getByIndex(storeName, indexName, value) {
        await this.initDB();
        
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction([storeName], 'readonly');
            const store = transaction.objectStore(storeName);
            const index = store.index(indexName);
            const request = index.getAll(value);
            
            request.onsuccess = (event) => {
                resolve(event.target.result);
            };
            
            request.onerror = (event) => {
                console.error(`Error getting by index ${indexName} from ${storeName}:`, event.target.error);
                reject(event.target.error);
            };
        });
    }
    
    /**
     * Menambahkan item ke antrian sinkronisasi
     * @param {Object} queueItem - Item untuk antrian sinkronisasi
     * @returns {Promise} Promise yang diselesaikan dengan ID queue item
     */
    async addToSyncQueue(queueItem) {
        await this.initDB();
        
        // Format default untuk item antrian
        const syncItem = {
            entity: queueItem.entity,
            record_id: queueItem.record_id,
            action: queueItem.action,
            data: queueItem.data,
            synced: false,
            created_at: new Date().toISOString(),
            ...queueItem
        };
        
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction(['sync_queue'], 'readwrite');
            const store = transaction.objectStore('sync_queue');
            const request = store.add(syncItem);
            
            request.onsuccess = (event) => {
                resolve(event.target.result);
            };
            
            request.onerror = (event) => {
                console.error('Error adding to sync queue:', event.target.error);
                reject(event.target.error);
            };
        });
    }
    
    /**
     * Mendapatkan item yang belum tersinkronisasi
     * @returns {Promise} Promise yang diselesaikan dengan array item yang belum tersinkronisasi
     */
    /**
 * Mendapatkan item yang belum tersinkronisasi
 * @returns {Promise} Promise yang diselesaikan dengan array item yang belum tersinkronisasi
 */
    async getUnsyncedItems() {
        await this.initDB();
        
        return new Promise((resolve, reject) => {
            try {
                const transaction = this.db.transaction(['sync_queue'], 'readonly');
                const store = transaction.objectStore('sync_queue');
                
                // Metode alternatif tanpa menggunakan indeks
                const request = store.getAll();
                
                request.onsuccess = (event) => {
                    // Filter hasil secara manual
                    const allItems = event.target.result || [];
                    const unsyncedItems = allItems.filter(item => item.synced === false);
                    resolve(unsyncedItems);
                };
                
                request.onerror = (event) => {
                    console.error('Error getting items:', event.target.error);
                    resolve([]);
                };
            } catch (error) {
                console.error('Error in getUnsyncedItems:', error);
                resolve([]);
            }
        });
    }
    
    /**
     * Inisialisasi koneksi database
     * @returns {Promise} Promise yang diselesaikan ketika database berhasil dibuka
     */
    initDB() {
        return new Promise((resolve, reject) => {
            if (this.db) {
                resolve(this.db);
                return;
            }
            
            const request = indexedDB.open(this.dbName, this.dbVersion);
            
            request.onerror = (event) => {
                console.error('Error opening database:', event.target.error);
                reject('Error opening database');
            };
            
            request.onsuccess = (event) => {
                this.db = event.target.result;
                console.log('Database opened successfully');
                resolve(this.db);
            };
            
            request.onupgradeneeded = (event) => {
                console.log('Database upgrade needed, creating object stores');
                const db = event.target.result;
                
                // Buat object stores untuk setiap entitas
                if (!db.objectStoreNames.contains('families')) {
                    console.log('Creating families store');
                    const familyStore = db.createObjectStore('families', { keyPath: 'id' });
                    familyStore.createIndex('building_id', 'building_id', { unique: false });
                    familyStore.createIndex('village_id', 'village_id', { unique: false });
                    familyStore.createIndex('sync_status', 'sync_status', { unique: false });
                }
                
                if (!db.objectStoreNames.contains('family_members')) {
                    console.log('Creating family_members store');
                    const memberStore = db.createObjectStore('family_members', { keyPath: 'id' });
                    memberStore.createIndex('family_id', 'family_id', { unique: false });
                    memberStore.createIndex('sync_status', 'sync_status', { unique: false });
                }
                
                if (!db.objectStoreNames.contains('buildings')) {
                    console.log('Creating buildings store');
                    const buildingStore = db.createObjectStore('buildings', { keyPath: 'id' });
                    buildingStore.createIndex('village_id', 'village_id', { unique: false });
                    buildingStore.createIndex('sync_status', 'sync_status', { unique: false });
                }
                
                if (!db.objectStoreNames.contains('sync_queue')) {
                    console.log('Creating sync_queue store');
                    const syncQueueStore = db.createObjectStore('sync_queue', { 
                        keyPath: 'id', 
                        autoIncrement: true 
                    });
                    
                    // Pastikan semua indeks dibuat dan tipe datanya sesuai
                    syncQueueStore.createIndex('entity', 'entity', { unique: false });
                    syncQueueStore.createIndex('record_id', 'record_id', { unique: false });
                    syncQueueStore.createIndex('action', 'action', { unique: false });
                    
                    // Pastikan indeks 'synced' dibuat dengan benar
                    syncQueueStore.createIndex('synced', 'synced', { unique: false });
                    
                    syncQueueStore.createIndex('created_at', 'created_at', { unique: false });
                }
            };
        });
    }

    /**
     * Memeriksa dan memperbaiki struktur database
     * @returns {Promise<boolean>} Promise yang diselesaikan dengan status perbaikan
     */
    async checkAndRepairDatabase() {
        try {
            await this.initDB();
            
            const transaction = this.db.transaction(['sync_queue'], 'readonly');
            const store = transaction.objectStore('sync_queue');
            
            // Periksa apakah indeks 'synced' ada
            if (!store.indexNames.contains('synced')) {
                console.warn('Indeks "synced" tidak ditemukan, mencoba memperbaiki database');
                
                // Tutup koneksi database
                this.db.close();
                this.db = null;
                
                // Hapus database dan buat ulang
                await this.clearAllData();
                await this.initDB();
                
                return true;
            }
            
            return true;
        } catch (error) {
            console.error('Error checking database structure:', error);
            return false;
        }
    }
    
    /**
     * Menghitung jumlah item yang belum tersinkronisasi
     * @returns {Promise} Promise yang diselesaikan dengan jumlah item yang belum tersinkronisasi
     */
    async getUnsyncedCount() {
        const items = await this.getUnsyncedItems();
        return items.length;
    }
    
    /**
     * Menghapus semua data dari database
     * @returns {Promise} Promise yang diselesaikan ketika database berhasil dihapus
     */
    async clearAllData() {
        return new Promise((resolve, reject) => {
            // Tutup koneksi database terlebih dahulu
            if (this.db) {
                this.db.close();
                this.db = null;
            }
            
            const deleteRequest = indexedDB.deleteDatabase(this.dbName);
            
            deleteRequest.onsuccess = () => {
                console.log('Database deleted successfully');
                resolve(true);
            };
            
            deleteRequest.onerror = (event) => {
                console.error('Error deleting database:', event.target.error);
                reject(event.target.error);
            };
        });
    }
}

// Export singleton instance
const dbManager = new DBManager();