/**
 * Modern Modal System
 * Replaces alert() calls with a modern, accessible modal dialog
 */

class Modal {
    constructor() {
        this.modalElement = null;
        this.init();
    }

    init() {
        // Create modal HTML structure
        const modalHTML = `
            <div id="customModal" class="custom-modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
                <div class="modal-overlay"></div>
                <div class="modal-container">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 id="modalTitle" class="modal-title"></h3>
                            <button class="modal-close" aria-label="Kapat" type="button">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="modal-icon"></div>
                            <p id="modalMessage" class="modal-message"></p>
                        </div>
                        <div class="modal-footer">
                            <button class="modal-btn modal-btn-primary" id="modalConfirm">Tamam</button>
                            <button class="modal-btn modal-btn-secondary" id="modalCancel" style="display: none;">İptal</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Add modal to body
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        this.modalElement = document.getElementById('customModal');

        // Bind event listeners
        this.bindEvents();
    }

    bindEvents() {
        const modal = this.modalElement;
        const overlay = modal.querySelector('.modal-overlay');
        const closeBtn = modal.querySelector('.modal-close');
        const confirmBtn = modal.querySelector('#modalConfirm');
        const cancelBtn = modal.querySelector('#modalCancel');

        // Close on overlay click
        overlay.addEventListener('click', () => this.close());

        // Close on close button
        closeBtn.addEventListener('click', () => this.close());

        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.classList.contains('active')) {
                this.close();
            }
        });
    }

    show(options = {}) {
        const {
            title = 'Bilgi',
            message = '',
            type = 'info', // 'info', 'success', 'warning', 'error', 'confirm'
            confirmText = 'Tamam',
            cancelText = 'İptal',
            onConfirm = null,
            onCancel = null
        } = options;

        const modal = this.modalElement;
        const titleElement = modal.querySelector('#modalTitle');
        const messageElement = modal.querySelector('#modalMessage');
        const iconElement = modal.querySelector('.modal-icon');
        const confirmBtn = modal.querySelector('#modalConfirm');
        const cancelBtn = modal.querySelector('#modalCancel');

        // Set content
        titleElement.textContent = title;
        messageElement.textContent = message;
        confirmBtn.textContent = confirmText;
        cancelBtn.textContent = cancelText;

        // Set icon based on type
        iconElement.innerHTML = this.getIcon(type);
        iconElement.className = `modal-icon modal-icon-${type}`;

        // Show/hide cancel button for confirm type
        if (type === 'confirm') {
            cancelBtn.style.display = 'inline-block';
        } else {
            cancelBtn.style.display = 'none';
        }

        // Remove old event listeners by cloning buttons
        const newConfirmBtn = confirmBtn.cloneNode(true);
        const newCancelBtn = cancelBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
        cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);

        // Add new event listeners
        newConfirmBtn.addEventListener('click', () => {
            if (onConfirm) onConfirm();
            this.close();
        });

        newCancelBtn.addEventListener('click', () => {
            if (onCancel) onCancel();
            this.close();
        });

        // Show modal with animation
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';

        // Focus on confirm button for accessibility
        setTimeout(() => newConfirmBtn.focus(), 100);
    }

    close() {
        const modal = this.modalElement;
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    getIcon(type) {
        const icons = {
            info: `<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="16" x2="12" y2="12"></line>
                <line x1="12" y1="8" x2="12.01" y2="8"></line>
            </svg>`,
            success: `<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>`,
            warning: `<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>`,
            error: `<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>`,
            confirm: `<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="9" y1="9" x2="15" y2="15"></line>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <path d="M12 8v8m-4-4h8"></path>
            </svg>`
        };
        return icons[type] || icons.info;
    }

    // Convenience methods
    alert(message, title = 'Uyarı') {
        this.show({
            title,
            message,
            type: 'warning'
        });
    }

    info(message, title = 'Bilgi') {
        this.show({
            title,
            message,
            type: 'info'
        });
    }

    success(message, title = 'Başarılı') {
        this.show({
            title,
            message,
            type: 'success'
        });
    }

    error(message, title = 'Hata') {
        this.show({
            title,
            message,
            type: 'error'
        });
    }

    confirm(message, onConfirm, onCancel, title = 'Onay') {
        this.show({
            title,
            message,
            type: 'confirm',
            onConfirm,
            onCancel
        });
    }
}

// Initialize modal when DOM is ready
let modal;
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        modal = new Modal();
    });
} else {
    modal = new Modal();
}

// Export for use in other scripts
window.Modal = Modal;
window.modal = modal;
