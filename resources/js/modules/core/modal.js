/**
 * Core Modal Helper
 * Standardizes modal triggers and esc-key closing.
 */
export const initModal = () => {
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('[data-modal-active="true"]').forEach((modal) => {
                modal.setAttribute('data-modal-active', 'false');
                modal.classList.add('hidden');
            });
        }
    });
};

initModal();
