/**
 * Authentication Manager untuk Offline Mode
 */
class AuthManager {
    constructor() {
        this.isAuthenticated = false;
        this.user = null;
        
        // Cek token
        this.checkAuthentication();
    }
    
    /**
     * Cek status autentikasi
     */
    checkAuthentication() {
        const token = localStorage.getItem('api_token');
        const userData = localStorage.getItem('user_data');
        
        if (token && userData) {
            try {
                this.user = JSON.parse(userData);
                this.isAuthenticated = true;
                
                // Perbarui data user dari API jika online
                if (navigator.onLine) {
                    this.refreshUserData();
                }
                
                return true;
            } catch (error) {
                console.error('Error parsing user data:', error);
                this.clearAuthentication();
                return false;
            }
        }
        
        return false;
    }
    
    /**
     * Login ke API
     * @param {string} email - Email pengguna
     * @param {string} password - Password pengguna
     * @param {string} deviceName - Nama perangkat
     * @returns {Promise} Promise yang diselesaikan dengan hasil login
     */
    async login(email, password, deviceName = 'Web Browser') {
        try {
            const response = await fetch('/api/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    email,
                    password,
                    device_name: deviceName
                })
            });
            
            const data = await response.json();
            
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Login failed');
            }
            
            // Simpan token dan data user
            localStorage.setItem('api_token', data.data.token);
            localStorage.setItem('user_data', JSON.stringify(data.data.user));
            
            this.user = data.data.user;
            this.isAuthenticated = true;
            
            // Trigger event login
            const event = new CustomEvent('auth:login', {
                detail: { user: this.user }
            });
            window.dispatchEvent(event);
            
            return {
                success: true,
                user: this.user
            };
        } catch (error) {
            console.error('Login error:', error);
            
            return {
                success: false,
                message: error.message || 'Login failed'
            };
        }
    }
    
    /**
     * Logout dari aplikasi
     */
    async logout() {
        try {
            // Jika online, kirim request logout ke API
            if (navigator.onLine) {
                const token = localStorage.getItem('api_token');
                
                if (token) {
                    await fetch('/api/logout', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Authorization': `Bearer ${token}`
                        }
                    });
                }
            }
        } catch (error) {
            console.error('Logout error:', error);
        } finally {
            // Hapus token dan data user
            this.clearAuthentication();
            
            // Trigger event logout
            const event = new CustomEvent('auth:logout');
            window.dispatchEvent(event);
            
            // Redirect ke halaman login
            window.location.href = '/login';
        }
    }
    
    /**
     * Hapus data autentikasi
     */
    clearAuthentication() {
        localStorage.removeItem('api_token');
        localStorage.removeItem('user_data');
        
        this.user = null;
        this.isAuthenticated = false;
    }
    
    /**
     * Perbarui data user dari API
     */
    async refreshUserData() {
        try {
            const token = localStorage.getItem('api_token');
            
            if (!token) {
                return false;
            }
            
            const response = await fetch('/api/profile', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Authorization': `Bearer ${token}`
                }
            });
            
            const data = await response.json();
            
            if (!response.ok || !data.success) {
                // Token mungkin sudah tidak valid
                this.clearAuthentication();
                return false;
            }
            
            // Perbarui data user
            this.user = data.data;
            localStorage.setItem('user_data', JSON.stringify(data.data));
            
            return true;
        } catch (error) {
            console.error('Error refreshing user data:', error);
            return false;
        }
    }
    
    /**
     * Cek apakah user memiliki role tertentu
     * @param {string} role - Nama role
     * @returns {boolean} True jika user memiliki role
     */
    hasRole(role) {
        if (!this.user || !this.user.roles) {
            return false;
        }
        
        return this.user.roles.includes(role);
    }
    
    /**
     * Cek apakah user memiliki akses ke desa tertentu
     * @param {number} villageId - ID desa
     * @returns {boolean} True jika user memiliki akses
     */
    hasVillageAccess(villageId) {
        if (!this.user || !this.user.villages) {
            return false;
        }
        
        // Jika user memiliki akses ke semua desa
        if (this.hasRole('admin') || this.hasRole('super-admin')) {
            return true;
        }
        
        // Cek apakah user memiliki akses ke desa tertentu
        return this.user.villages.some(village => village.id === parseInt(villageId));
    }
}

// Buat instance AuthManager
const authManager = new AuthManager();