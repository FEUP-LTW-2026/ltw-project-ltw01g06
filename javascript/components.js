/**
 * components.js — Equilibrio Gym
 * Componentes de UI reutilizáveis partilhados por todas as páginas.
 *
 * Componentes disponíveis:
 *   - Navbar            : injeção e highlight da barra de navegação
 *   - Toast             : sistema de notificações (substitui showNotification em auth-ui.js)
 *   - Avatar            : iniciais geradas a partir do nome
 *   - PageHeader        : cabeçalho de secção consistente
 *   - EmptyState        : placeholder quando uma lista está vazia
 */

/* ─────────────────────────────────────────
   TOAST — sistema de notificações global
   Substitui showNotification() em auth-ui.js
   ───────────────────────────────────────── */

const Toast = (() => {
    const COLORS = {
        success: '#10b981',
        error:   '#ef4444',
        warning: '#f59e0b',
        info:    '#3b82f6',
    };

    // Injeta o container e os keyframes uma única vez
    function _ensureContainer() {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.setAttribute('aria-live', 'polite');
            container.setAttribute('aria-atomic', 'false');
            container.style.cssText =
                'position:fixed;top:20px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:8px;';
            document.body.appendChild(container);
        }

        if (!document.getElementById('toast-styles')) {
            const style = document.createElement('style');
            style.id = 'toast-styles';
            style.textContent = `
                @keyframes toast-in  { from { transform:translateX(110%); opacity:0 } to { transform:translateX(0); opacity:1 } }
                @keyframes toast-out { from { transform:translateX(0); opacity:1 } to { transform:translateX(110%); opacity:0 } }
                .toast {
                    padding: 14px 18px;
                    border-radius: 10px;
                    color: #fff;
                    font-weight: 500;
                    font-size: 14px;
                    max-width: 340px;
                    box-shadow: 0 4px 16px rgba(0,0,0,.18);
                    animation: toast-in .28s ease forwards;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    cursor: default;
                }
                .toast__close {
                    margin-left: auto;
                    background: none;
                    border: none;
                    color: rgba(255,255,255,.75);
                    cursor: pointer;
                    font-size: 16px;
                    line-height: 1;
                    padding: 0 2px;
                }
                .toast__close:hover { color: #fff; }
                .spinner {
                    display: inline-block;
                    width: 16px; height: 16px;
                    border: 2px solid #fff;
                    border-radius: 50%;
                    border-top-color: transparent;
                    animation: spin .8s linear infinite;
                }
                @keyframes spin { to { transform: rotate(360deg); } }
            `;
            document.head.appendChild(style);
        }

        return container;
    }

    function _dismiss(el) {
        el.style.animation = 'toast-out .28s ease forwards';
        setTimeout(() => el.remove(), 300);
    }

    /**
     * Mostra uma notificação toast.
     * @param {string} message   - Texto a mostrar
     * @param {'success'|'error'|'warning'|'info'} [type='info']
     * @param {number} [duration=4000] - ms até fechar automaticamente (0 = permanente)
     */
    function show(message, type = 'info', duration = 4000) {
        const container = _ensureContainer();

        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.style.backgroundColor = COLORS[type] || COLORS.info;
        toast.setAttribute('role', 'alert');

        toast.innerHTML = `<span class="toast__text">${message}</span>
            <button class="toast__close" aria-label="Fechar">×</button>`;

        toast.querySelector('.toast__close').addEventListener('click', () => _dismiss(toast));
        container.appendChild(toast);

        if (duration > 0) {
            setTimeout(() => _dismiss(toast), duration);
        }
    }

    return { show };
})();

// Compatibilidade com auth-ui.js que chama showNotification()
window.showNotification = (message, type) => Toast.show(message, type);

// Expor globalmente
window.Toast = Toast;


/* ─────────────────────────────────────────
   AVATAR — iniciais a partir de um nome
   ───────────────────────────────────────── */

const Avatar = (() => {
    /**
     * Devolve as iniciais (até 2 letras) de um nome completo.
     * @param {string} name
     * @returns {string}
     */
    function initials(name) {
        if (!name) return '?';
        const parts = name.trim().split(/\s+/);
        if (parts.length === 1) return parts[0].charAt(0).toUpperCase();
        return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
    }

    /**
     * Preenche todos os elementos [data-avatar-name] com as suas iniciais.
     * Chamado automaticamente no DOMContentLoaded.
     */
    function hydrate() {
        document.querySelectorAll('[data-avatar-name]').forEach(el => {
            const name = el.getAttribute('data-avatar-name');
            if (name) el.textContent = initials(name);
        });
    }

    return { initials, hydrate };
})();

window.Avatar = Avatar;


/* ─────────────────────────────────────────
   NAVBAR — highlight do link ativo
   Marca como ativo o link cujo href
   corresponde à página atual.
   ───────────────────────────────────────── */

const Navbar = (() => {
    const ACTIVE_CLASS = 'navbar__link--active';

    function highlightActive() {
        const currentPage = window.location.pathname.split('/').pop() || 'index.html';
        document.querySelectorAll('.navbar__link').forEach(link => {
            const linkPage = (link.getAttribute('href') || '').split('/').pop();
            if (linkPage && linkPage === currentPage) {
                link.classList.add(ACTIVE_CLASS);
            } else {
                link.classList.remove(ACTIVE_CLASS);
            }
        });
    }

    return { highlightActive };
})();

window.Navbar = Navbar;


/* ─────────────────────────────────────────
   PAGE HEADER — cabeçalho de secção
   Uso: PageHeader.render(containerEl, { title, subtitle, action? })
   ───────────────────────────────────────── */

const PageHeader = (() => {
    /**
     * Renderiza um cabeçalho de página num elemento existente.
     * @param {HTMLElement} container
     * @param {{ title: string, subtitle?: string, action?: { label: string, href: string, icon?: string } }} opts
     */
    function render(container, { title, subtitle = '', action = null } = {}) {
        if (!container) return;
        container.innerHTML = `
            <div class="page-header">
                <div class="page-header__text">
                    <h1 class="page-header__title">${title}</h1>
                    ${subtitle ? `<p class="page-header__subtitle">${subtitle}</p>` : ''}
                </div>
                ${action ? `<a href="${action.href}" class="btn btn--primary">
                    ${action.icon ? action.icon : ''}${action.label}
                </a>` : ''}
            </div>
        `;
    }

    return { render };
})();

window.PageHeader = PageHeader;


/* ─────────────────────────────────────────
   EMPTY STATE — placeholder para listas vazias
   Uso: EmptyState.render(containerEl, { icon?, title, message, action? })
   ───────────────────────────────────────── */

const EmptyState = (() => {
    /**
     * Mostra um estado vazio dentro do container fornecido.
     * @param {HTMLElement} container
     * @param {{ icon?: string, title: string, message?: string, action?: { label: string, href?: string, onClick?: Function } }} opts
     */
    function render(container, { icon = '📭', title, message = '', action = null } = {}) {
        if (!container) return;

        const actionHTML = action
            ? action.href
                ? `<a href="${action.href}" class="btn btn--primary">${action.label}</a>`
                : `<button class="btn btn--primary" data-empty-action>${action.label}</button>`
            : '';

        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-state__icon" aria-hidden="true">${icon}</div>
                <h3 class="empty-state__title">${title}</h3>
                ${message ? `<p class="empty-state__message">${message}</p>` : ''}
                ${actionHTML}
            </div>
        `;

        if (action && action.onClick) {
            const btn = container.querySelector('[data-empty-action]');
            if (btn) btn.addEventListener('click', action.onClick);
        }
    }

    return { render };
})();

window.EmptyState = EmptyState;


/* ─────────────────────────────────────────
   LOADING SPINNER — estado de carregamento
   Uso: LoadingSpinner.show(container) / LoadingSpinner.hide(container)
   ───────────────────────────────────────── */

const LoadingSpinner = (() => {
    function show(container, message = 'A carregar...') {
        if (!container) return;
        container.innerHTML = `
            <div class="loading-state" aria-busy="true" aria-label="${message}">
                <div class="spinner" style="width:32px;height:32px;border-width:3px;"></div>
                <p class="loading-state__text">${message}</p>
            </div>
        `;
    }

    function hide(container) {
        if (!container) return;
        const state = container.querySelector('.loading-state');
        if (state) state.remove();
    }

    return { show, hide };
})();

window.LoadingSpinner = LoadingSpinner;


/* ─────────────────────────────────────────
   INIT — executa quando o DOM está pronto
   ───────────────────────────────────────── */

document.addEventListener('DOMContentLoaded', function () {
    Avatar.hydrate();
    Navbar.highlightActive();
});
