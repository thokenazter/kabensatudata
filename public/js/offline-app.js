/**
 * Main script untuk offline support
 */
document.addEventListener('DOMContentLoaded', async function() {
    // Check if browser supports all required features
    if (!('indexedDB' in window)) {
        console.error('Browser tidak mendukung IndexedDB. Fitur offline tidak akan berfungsi.');
        return;
    }
    
    // Check and repair database structure
    if (typeof dbManager !== 'undefined') {
        try {
            await dbManager.checkAndRepairDatabase();
            console.log('Database structure checked and repaired if needed');
        } catch (error) {
            console.error('Error checking/repairing database:', error);
        }
    }
    
    // Inisialisasi komponen (these will be loaded from separate files)
    console.log('Offline support initialized.');
    
    // Check online status
    updateOnlineStatus();
    
    window.addEventListener('online', updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);
    
    // Update semua form yang ada untuk dukungan offline
    updateFormsForOfflineSupport();
});

// Update status online/offline
function updateOnlineStatus() {
    const status = navigator.onLine ? 'online' : 'offline';
    console.log('Connection status:', status);
    
    // Add indicator class to body
    document.body.classList.toggle('is-offline', !navigator.onLine);
    
    // Show notification when status changes
    if (status === 'offline' && typeof syncUI !== 'undefined') {
        syncUI.showNotification(
            'Mode Offline', 
            'Anda sedang bekerja dalam mode offline. Data akan disimpan secara lokal dan akan otomatis tersinkronisasi saat kembali online.', 
            'info'
        );
    }
}

// Add offline support to all forms
function updateFormsForOfflineSupport() {
    // Mencari form yang memerlukan dukungan offline
    const forms = document.querySelectorAll('form.needs-offline-support');
    
    forms.forEach(form => {
        // Add data attributes
        form.setAttribute('data-offline-submit', 'true');
        
        // Determine entity type from form if not already set
        if (!form.hasAttribute('data-entity-type')) {
            const action = form.getAttribute('action') || '';
            
            if (action.includes('families')) {
                form.setAttribute('data-entity-type', 'families');
            } else if (action.includes('family-members')) {
                form.setAttribute('data-entity-type', 'family_members');
            } else if (action.includes('buildings')) {
                form.setAttribute('data-entity-type', 'buildings');
            }
        }
        
        // Add hidden field for record ID if editing
        if (form.dataset.recordId && !form.querySelector('input[name="id"]')) {
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            idInput.value = form.dataset.recordId;
            form.appendChild(idInput);
        }
        
        console.log('Form prepared for offline support:', form);
    });
}