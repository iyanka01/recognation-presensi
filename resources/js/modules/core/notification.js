/**
 * Core Notification Helper
 * Provides consistent toast notifications across portals.
 */
export const notify = {
    show(message, type = 'success', duration = 4000) {
        if (window.Swal) {
            const Toast = window.Swal.mixin({
                toast: true,
                position: 'bottom-end',
                showConfirmButton: false,
                timer: duration,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', window.Swal.stopTimer);
                    toast.addEventListener('mouseleave', window.Swal.resumeTimer);
                }
            });

            Toast.fire({
                icon: type,
                title: message
            });
        } else {
            console.log(`[Notification - ${type}]: ${message}`);
        }
    },

    success(message, duration) {
        this.show(message, 'success', duration);
    },

    error(message, duration) {
        this.show(message, 'error', duration);
    },

    warning(message, duration) {
        this.show(message, 'warning', duration);
    },

    info(message, duration) {
        this.show(message, 'info', duration);
    }
};

window.notify = notify;
