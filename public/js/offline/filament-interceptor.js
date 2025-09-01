/**
 * Interceptor untuk Filament Form
 */
(function() {
    // Deteksi ketika Livewire tersedia
    document.addEventListener('DOMContentLoaded', function() {
        if (window.Livewire) {
            setupLivewireInterceptor();
        } else {
            document.addEventListener('livewire:load', setupLivewireInterceptor);
        }
    });
    
    function setupLivewireInterceptor() {
        // Pastikan Livewire ada dan syncManager tersedia
        if (!window.Livewire || !window.Livewire.hook || typeof window.syncManager === 'undefined') {
            console.warn('Livewire or syncManager not available for offline interceptor');
            return;
        }
        
        console.log('Setting up Livewire interceptor for offline support');
        
        // Hook sebelum update dikirim ke server
        window.Livewire.hook('message.sent', ({ component, message, respond }) => {
            // Cek apakah ini adalah operasi form submit dari Filament
            const isFormSubmit = message.updateQueue && message.updateQueue.some(update => {
                return update.payload && update.payload.effects && update.payload.effects.returns;
            });
            
            // Cek apakah offline mode atau force offline mode aktif
            const isOffline = !navigator.onLine || localStorage.getItem('force_offline') === 'true';
            
            // Jika offline dan ini adalah form submit
            if (isOffline && isFormSubmit) {
                // Tentukan jenis entity dan action berdasarkan nama komponen
                let entityType = null;
                let actionType = 'update';
                
                const componentName = component.fingerprint.name || '';
                
                if (componentName.includes('family-resource')) {
                    entityType = 'families';
                } else if (componentName.includes('family-member-resource')) {
                    entityType = 'family_members';
                } else if (componentName.includes('building-resource')) {
                    entityType = 'buildings';
                }
                
                if (componentName.includes('create-record')) {
                    actionType = 'create';
                }
                
                // Jika bisa mengenali entity type, proses offline
                if (entityType) {
                    console.log(`Processing offline ${actionType} for ${entityType}`, component, message);
                    
                    try {
                        // Ekstrak data dari Livewire component state
                        const data = extractDataFromState(component.data, entityType, actionType);
                        
                        // Tambahkan event listener untuk memproses data setelah loop event selesai
                        setTimeout(async () => {
                            try {
                                // Simpan data ke penyimpanan lokal
                                await window.syncManager.saveOfflineData(entityType, data, actionType);
                                
                                // Tampilkan notifikasi sukses
                                if (typeof window.syncUI !== 'undefined') {
                                    window.syncUI.showNotification(
                                        'Data Tersimpan Offline',
                                        'Data berhasil disimpan secara offline dan akan disinkronisasi saat kembali online',
                                        'success'
                                    );
                                }
                                
                                // Navigasi kembali ke list view jika create/edit selesai
                                setTimeout(() => {
                                    const listUrl = getListUrl(entityType);
                                    if (listUrl) {
                                        window.location.href = listUrl;
                                    }
                                }, 1500);
                            } catch (error) {
                                console.error('Error saving data offline:', error);
                                if (typeof window.syncUI !== 'undefined') {
                                    window.syncUI.showNotification(
                                        'Error',
                                        'Gagal menyimpan data: ' + error.message,
                                        'error'
                                    );
                                }
                            }
                        }, 0);
                        
                        // Prevent the actual form submission
                        respond(false);
                        
                        // Return false to prevent the default action
                        return false;
                    } catch (error) {
                        console.error('Error in Livewire hook:', error);
                    }
                }
            }
            
            // Proceed with normal request
            return true;
        });
    }
    
    /**
     * Extract data from Filament component state
     * @param {Object} state - Livewire component state
     * @param {string} entityType - Type of entity
     * @param {string} actionType - Type of action
     * @returns {Object} Extracted data object
     */
    function extractDataFromState(state, entityType, actionType) {
        // Base data object
        const data = {
            id: state.id || null
        };
        
        // Get form data from the mount.data property
        const mountData = state.mountedFormComponentData;
        
        if (!mountData) {
            throw new Error('Could not find form data in component state');
        }
        
        // Extract fields based on entity type
        switch (entityType) {
            case 'families':
                data.building_id = getMountedValue(mountData, 'building_id');
                data.family_number = getMountedValue(mountData, 'family_number');
                data.head_name = getMountedValue(mountData, 'head_name');
                data.has_clean_water = convertToBoolean(getMountedValue(mountData, 'has_clean_water'));
                data.is_water_protected = convertToBoolean(getMountedValue(mountData, 'is_water_protected'));
                data.has_toilet = convertToBoolean(getMountedValue(mountData, 'has_toilet'));
                data.is_toilet_sanitary = convertToBoolean(getMountedValue(mountData, 'is_toilet_sanitary'));
                data.has_mental_illness = convertToBoolean(getMountedValue(mountData, 'has_mental_illness'));
                data.takes_medication_regularly = convertToBoolean(getMountedValue(mountData, 'takes_medication_regularly'));
                data.has_restrained_member = convertToBoolean(getMountedValue(mountData, 'has_restrained_member'));
                break;
                
            case 'family_members':
                data.family_id = getMountedValue(mountData, 'family_id');
                data.name = getMountedValue(mountData, 'name');
                data.relationship = getMountedValue(mountData, 'relationship');
                data.gender = getMountedValue(mountData, 'gender');
                data.birth_date = getMountedValue(mountData, 'birth_date');
                data.nik = getMountedValue(mountData, 'nik');
                data.is_pregnant = convertToBoolean(getMountedValue(mountData, 'is_pregnant'));
                data.has_jkn = convertToBoolean(getMountedValue(mountData, 'has_jkn'));
                data.is_smoker = convertToBoolean(getMountedValue(mountData, 'is_smoker'));
                data.has_tuberculosis = convertToBoolean(getMountedValue(mountData, 'has_tuberculosis'));
                data.takes_tb_medication_regularly = convertToBoolean(getMountedValue(mountData, 'takes_tb_medication_regularly'));
                data.has_hypertension = convertToBoolean(getMountedValue(mountData, 'has_hypertension'));
                data.takes_hypertension_medication_regularly = convertToBoolean(getMountedValue(mountData, 'takes_hypertension_medication_regularly'));
                break;
                
            case 'buildings':
                data.village_id = getMountedValue(mountData, 'village_id');
                data.building_number = getMountedValue(mountData, 'building_number');
                data.latitude = getMountedValue(mountData, 'latitude');
                data.longitude = getMountedValue(mountData, 'longitude');
                data.description = getMountedValue(mountData, 'description');
                break;
                
            default:
                throw new Error(`Unknown entity type: ${entityType}`);
        }
        
        return data;
    }
    
    /**
     * Get value from mounted form data
     * @param {Object} mountData - Mounted form data
     * @param {string} key - Key to get value for
     * @returns {*} The value or null if not found
     */
    function getMountedValue(mountData, key) {
        if (!mountData || typeof mountData !== 'object') {
            return null;
        }
        
        // Direct key in first level
        if (mountData[key] !== undefined) {
            return mountData[key];
        }
        
        // Look for key in nested components
        for (const componentKey in mountData) {
            if (typeof mountData[componentKey] === 'object' && mountData[componentKey] !== null) {
                // Check if this is the right component
                if (componentKey.includes(key) || (mountData[componentKey].state && mountData[componentKey].state[key] !== undefined)) {
                    return mountData[componentKey].state ? mountData[componentKey].state[key] : mountData[componentKey];
                }
            }
        }
        
        return null;
    }
    
    /**
     * Convert various input values to boolean
     * @param {*} value - Value to convert
     * @returns {boolean} Converted boolean value
     */
    function convertToBoolean(value) {
        if (typeof value === 'boolean') {
            return value;
        }
        
        if (typeof value === 'string') {
            return ['true', '1', 'yes', 'y', 'on', 'checked'].includes(value.toLowerCase());
        }
        
        if (typeof value === 'number') {
            return value === 1;
        }
        
        return false;
    }
    
    /**
     * Get list URL for entity type
     * @param {string} entityType - Type of entity
     * @returns {string|null} URL to list page or null
     */
    function getListUrl(entityType) {
        const adminPath = '/admin'; // Adjust if your admin path is different
        
        switch (entityType) {
            case 'families':
                return `${adminPath}/families`;
            case 'family_members':
                return `${adminPath}/family-members`;
            case 'buildings':
                return `${adminPath}/buildings`;
            default:
                return `${adminPath}/dashboard`;
        }
    }
})();