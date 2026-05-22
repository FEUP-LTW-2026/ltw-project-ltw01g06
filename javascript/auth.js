/**
 * Authentication API Client
 * Handles communication with PHP backend
 */

const API_BASE_URL = '/api'; // Change this to your PHP server URL in production

class AuthService {
    constructor(baseUrl = API_BASE_URL) {
        this.baseUrl = baseUrl;
        this.token = localStorage.getItem('auth_token');
    }

    /**
     * Register a new user
     * @param {Object} userData - { full_name, username, email, password, role? }
     */
    async register(userData) {
        try {
            const response = await fetch(`${this.baseUrl}/auth/register.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(userData),
                credentials: 'include'
            });

            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'Registration failed');
            }

            return data;
        } catch (error) {
            console.error('Registration error:', error);
            throw error;
        }
    }

    /**
     * Login user
     * @param {string} email - Email or username
     * @param {string} password - User password
     */
    async login(email, password) {
        try {
            const response = await fetch(`${this.baseUrl}/auth/login.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email, password }),
                credentials: 'include'
            });

            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'Login failed');
            }

            // Store token and user data
            if (data.data.token) {
                this.token = data.data.token;
                localStorage.setItem('auth_token', data.data.token);
                localStorage.setItem('user', JSON.stringify(data.data.user));
            }

            return data;
        } catch (error) {
            console.error('Login error:', error);
            throw error;
        }
    }

    /**
     * Logout user
     */
    async logout() {
        try {
            const response = await fetch(`${this.baseUrl}/auth/logout.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'include'
            });

            // Clear local storage regardless of response
            this.token = null;
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');

            const data = await response.json();
            return data;
        } catch (error) {
            // Still clear local storage on error
            this.token = null;
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
            throw error;
        }
    }

    /**
     * Check if user is authenticated
     */
    async checkAuth() {
        try {
            const response = await fetch(`${this.baseUrl}/auth/check.php`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'include'
            });

            const data = await response.json();
            
            if (!response.ok) {
                this.token = null;
                localStorage.removeItem('auth_token');
                localStorage.removeItem('user');
                return null;
            }

            // Update stored user data
            if (data.data.user) {
                localStorage.setItem('user', JSON.stringify(data.data.user));
            }

            return data.data.user;
        } catch (error) {
            console.error('Auth check error:', error);
            return null;
        }
    }

    /**
     * Get current user from localStorage
     */
    getCurrentUser() {
        const user = localStorage.getItem('user');
        return user ? JSON.parse(user) : null;
    }

    /**
     * Check if user is logged in (local check)
     */
    isLoggedIn() {
        return !!this.token && !!this.getCurrentUser();
    }

    /**
     * Get auth token
     */
    getToken() {
        return this.token;
    }
}

// Export singleton instance
const authService = new AuthService();

// If using ES modules
// export default authService;

// If using in browser directly
if (typeof window !== 'undefined') {
    window.AuthService = AuthService;
    window.authService = authService;
}
