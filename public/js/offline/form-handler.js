/**
 * Form Handler untuk dukungan offline
 */
class OfflineFormHandler {
    constructor(syncManager) {
        this.syncManager = syncManager;
        this.initialized = false;
        
        // Init setelah DOM loaded
        document.addEventListener('DOMContentLoaded', () => this.init());
    }
    
    /**
     * Inisialisasi form handler
     */
    init() {
        if (this.initialized) {
            return;
        }
        
        // Cari semua form dengan attribute data-offline-submit
        const forms = document.querySelectorAll('form[data-offline-submit]');
        
        forms.forEach(form => {
            this.setupOfflineForm(form);
        });
        
        this.initialized = true;
        
        // Setup global handler untuk form yang ditambahkan melalui Ajax
        this.setupMutationObserver();
    }
    
    /**
     * Setup form untuk dukungan offline
     * @param {HTMLFormElement} form - Form element
     */
    setupOfflineForm(form) {
        if (form.getAttribute('data-offline-initialized') === 'true') {
            return;
        }
        
        form.addEventListener('submit', (e) => this.handleFormSubmit(e));
        form.setAttribute('data-offline-initialized', 'true');
        
        console.log('Offline form initialized:', form);
    }
    
    /**
     * Setup mutation observer untuk mendeteksi form baru
     */
    setupMutationObserver() {
        const observer = new MutationObserver(mutations => {
            mutations.forEach(mutation => {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach(node => {
                        if (node.nodeName === 'FORM' && node.hasAttribute('data-offline-submit')) {
                            this.setupOfflineForm(node);
                        } else if (node.querySelectorAll) {
                            const forms = node.querySelectorAll('form[data-offline-submit]');
                            forms.forEach(form => this.setupOfflineForm(form));
                        }
                    });
                }
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
    
    /**
     * Handle submit form dengan dukungan offline
     * @param {Event} e - Submit event
     */
    async handleFormSubmit(e) {
        const form = e.target;
        const entityType = form.getAttribute('data-entity-type');
        const actionType = form.getAttribute('data-action-type') || 'update';
        
        // Validasi attribute yang diperlukan
        if (!entityType) {
            console.error('Form is missing data-entity-type attribute. Offline submit aborted.');
            return;
        }
        
        // Cek apakah force offline mode diaktifkan
        const isForceOffline = localStorage.getItem('force_offline') === 'true';
        
        // Jika online dan tidak dalam force offline mode, biarkan form disubmit secara normal
        if (navigator.onLine && !isForceOffline && form.getAttribute('data-force-offline') !== 'true') {
            return;
        }
        
        // Cegah submit normal
        e.preventDefault();
        
        try {
            // Kumpulkan data form
            const formData = new FormData(form);
            const jsonData = {};
            
            for (const [key, value] of formData.entries()) {
                // Handle array inputs (name="field[]")
                if (key.endsWith('[]')) {
                    const cleanKey = key.substring(0, key.length - 2);
                    if (!jsonData[cleanKey]) {
                        jsonData[cleanKey] = [];
                    }
                    jsonData[cleanKey].push(value);
                } else {
                    jsonData[key] = value;
                }
            }
            
            // Tambahkan ID jika tersedia
            if (form.dataset.recordId) {
                jsonData.id = parseInt(form.dataset.recordId, 10);
            }
            
            // Simpan data ke penyimpanan lokal dan antrian sinkronisasi
            await this.syncManager.saveOfflineData(entityType, jsonData, actionType);
            
            // Show success message
            const message = form.getAttribute('data-success-message') || 'Data berhasil disimpan secara offline dan akan disinkronisasi saat kembali online.';
            
            if (typeof syncUI !== 'undefined') {
                syncUI.showNotification('Data Tersimpan', message, 'success');
            } else {
                alert(message);
            }
            
            // Trigger event untuk memberi tahu komponen lain
            const event = new CustomEvent('offline:data-saved', {
                detail: {
                    entityType,
                    data: jsonData,
                    action: actionType
                }
            });
            window.dispatchEvent(event);
            
            // Reset form jika diminta
            if (form.getAttribute('data-reset-on-submit') === 'true') {
                form.reset();
            }
            
            // Arahkan ke halaman lain jika diminta
            const redirectUrl = form.getAttribute('data-success-redirect');
            if (redirectUrl) {
                window.location.href = redirectUrl;
            }
        } catch (error) {
            console.error('Error saving form data offline:', error);
            
            if (typeof syncUI !== 'undefined') {
                syncUI.showNotification('Error', 'Gagal menyimpan data: ' + error.message, 'error');
            } else {
                alert('Gagal menyimpan data: ' + error.message);
            }
        }
    }
}

// Create instance
const offlineFormHandler = new OfflineFormHandler(syncManager);