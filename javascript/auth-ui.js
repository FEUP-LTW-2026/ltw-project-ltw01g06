/**
 * Authentication UI Handler
 * Connects auth.js to the HTML forms
 */

/**
 * Switch the visible auth tab panel.
 * Works with the [data-tab] pattern — no inline onclick needed.
 * @param {string} tab - 'login' | 'register'
 */
function switchTab(tab) {
    document.querySelectorAll('.auth-tabs__tab').forEach(function(t) {
        t.classList.remove('auth-tabs__tab--active');
        t.setAttribute('aria-selected', 'false');
    });
    document.querySelectorAll('.auth-card').forEach(function(c) {
        c.classList.add('auth-card--hidden');
    });
    const activeTab = document.getElementById('tab-' + tab);
    const activePanel = document.getElementById('panel-' + tab);
    if (activeTab) {
        activeTab.classList.add('auth-tabs__tab--active');
        activeTab.setAttribute('aria-selected', 'true');
    }
    if (activePanel) {
        activePanel.classList.remove('auth-card--hidden');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Wire [data-tab] buttons — replaces inline onclick on auth.html
    document.querySelectorAll('[data-tab]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            switchTab(btn.getAttribute('data-tab'));
        });
    });

    // Initialize auth service
    const auth = window.authService || new AuthService();

    // Get form elements
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const loginTab = document.getElementById('loginTab');
    const registerTab = document.getElementById('registerTab');
    const loginFormContainer = document.getElementById('loginFormContainer');
    const registerFormContainer = document.getElementById('registerFormContainer');

    // Legacy tab switching (for pages that still use old IDs)
    if (loginTab && registerTab) {
        loginTab.addEventListener('click', () => {
            loginTab.classList.add('active');
            registerTab.classList.remove('active');
            loginFormContainer.style.display = 'block';
            registerFormContainer.style.display = 'none';
        });

        registerTab.addEventListener('click', () => {
            registerTab.classList.add('active');
            loginTab.classList.remove('active');
            registerFormContainer.style.display = 'block';
            loginFormContainer.style.display = 'none';
        });
    }

    // Login form handler
    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;
            const submitBtn = loginForm.querySelector('button[type="submit"]');
            const errorDiv = document.getElementById('loginError');

            // Disable button and show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span> A entrar...';
            if (errorDiv) errorDiv.style.display = 'none';

            try {
                const result = await auth.login(email, password);
                
                // Show success message
                showNotification('Login efetuado com sucesso!', 'success');
                
                // Redirect based on role
                const user = result.data.user;
                setTimeout(() => {
                    if (user.role === 'admin') {
                        window.location.href = '/pages/admin.html';
                    } else if (user.role === 'trainer') {
                        window.location.href = '/pages/trainer-dashboard.html';
                    } else {
                        window.location.href = '/pages/dashboard.html';
                    }
                }, 1000);

            } catch (error) {
                // Show error message
                if (errorDiv) {
                    errorDiv.textContent = error.message || 'Credenciais inválidas';
                    errorDiv.style.display = 'block';
                }
                showNotification(error.message || 'Erro ao fazer login', 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Entrar';
            }
        });
    }

    // Register form handler
    if (registerForm) {
        registerForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const fullName = document.getElementById('registerName').value;
            const username = document.getElementById('registerUsername').value;
            const email = document.getElementById('registerEmail').value;
            const password = document.getElementById('registerPassword').value;
            const confirmPassword = document.getElementById('registerConfirmPassword').value;
            const submitBtn = registerForm.querySelector('button[type="submit"]');
            const errorDiv = document.getElementById('registerError');

            // Validate passwords match
            if (password !== confirmPassword) {
                if (errorDiv) {
                    errorDiv.textContent = 'As passwords não coincidem';
                    errorDiv.style.display = 'block';
                }
                return;
            }

            // Disable button and show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span> A registar...';
            if (errorDiv) errorDiv.style.display = 'none';

            try {
                const result = await auth.register({
                    full_name: fullName,
                    username: username,
                    email: email,
                    password: password
                });
                
                // Show success message
                showNotification('Registo efetuado com sucesso! Pode fazer login.', 'success');
                
                // Switch to login tab
                if (loginTab) {
                    loginTab.click();
                }

                // Clear form
                registerForm.reset();

            } catch (error) {
                // Show error message
                if (errorDiv) {
                    errorDiv.textContent = error.message || 'Erro ao registar';
                    errorDiv.style.display = 'block';
                }
                showNotification(error.message || 'Erro ao registar', 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Registar';
            }
        });
    }

    // Logout handler
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', async function(e) {
            e.preventDefault();
            
            try {
                await auth.logout();
                showNotification('Logout efetuado com sucesso!', 'success');
                setTimeout(() => {
                    window.location.href = '/pages/auth.html';
                }, 1000);
            } catch (error) {
                // Still redirect even on error
                window.location.href = '/pages/auth.html';
            }
        });
    }

    // Check authentication on protected pages
    checkAuthOnLoad();
});

/**
 * Check if user is authenticated on page load
 */
async function checkAuthOnLoad() {
    const auth = window.authService || new AuthService();
    const protectedPages = ['dashboard.html', 'admin.html', 'trainer-dashboard.html', 'schedule.html'];
    const currentPage = window.location.pathname.split('/').pop();

    if (protectedPages.includes(currentPage)) {
        const user = await auth.checkAuth();
        
        if (!user) {
            showNotification('Sessão expirada. Por favor faça login.', 'warning');
            setTimeout(() => {
                window.location.href = '/pages/auth.html';
            }, 1500);
            return;
        }

        // Update UI with user info
        updateUserUI(user);

        // Check role-based access
        if (currentPage === 'admin.html' && user.role !== 'admin') {
            showNotification('Acesso negado. Apenas administradores.', 'error');
            setTimeout(() => {
                window.location.href = '/pages/dashboard.html';
            }, 1500);
        }
    }

    // If on auth page and already logged in, redirect
    if (currentPage === 'auth.html') {
        const user = auth.getCurrentUser();
        if (user) {
            window.location.href = '/pages/dashboard.html';
        }
    }
}

/**
 * Update UI elements with user information
 */
function updateUserUI(user) {
    // Update username display
    const userNameElements = document.querySelectorAll('.user-name, #userName');
    userNameElements.forEach(el => {
        el.textContent = user.full_name || user.username;
    });

    // Update user role display
    const userRoleElements = document.querySelectorAll('.user-role, #userRole');
    userRoleElements.forEach(el => {
        el.textContent = user.role.charAt(0).toUpperCase() + user.role.slice(1);
    });

    // Update user avatar
    const userAvatarElements = document.querySelectorAll('.user-avatar, #userAvatar');
    userAvatarElements.forEach(el => {
        if (user.photo && user.photo !== 'default_profile.png') {
            el.src = `/uploads/profiles/${user.photo}`;
        }
    });
}

// showNotification está definido em components.js como window.showNotification = Toast.show
// Os estilos de .spinner e animações também estão em components.js
