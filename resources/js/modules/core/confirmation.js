/**
 * Core Confirmation Dialog Helper
 * Integrates with SweetAlert2 or native confirm dialog.
 */
export const confirmAction = (options = {}) => {
    const {
        title = 'Apakah Anda yakin?',
        text = 'Tindakan ini tidak dapat dibatalkan!',
        confirmButtonText = 'Ya, lanjutkan',
        cancelButtonText = 'Batal',
        icon = 'warning',
        onConfirm = () => {},
        onCancel = () => {}
    } = options;

    if (window.Swal) {
        return window.Swal.fire({
            title,
            text,
            icon,
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#64748b',
            confirmButtonText,
            cancelButtonText,
            reverseButtons: true,
            customClass: {
                popup: 'rounded-2xl',
                confirmButton: 'rounded-xl px-5 py-2.5 font-semibold text-sm',
                cancelButton: 'rounded-xl px-5 py-2.5 font-semibold text-sm'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                onConfirm();
            } else if (result.dismiss === window.Swal.DismissReason.cancel) {
                onCancel();
            }
            return result.isConfirmed;
        });
    }

    if (window.confirm(`${title}\n${text}`)) {
        onConfirm();
        return Promise.resolve(true);
    } else {
        onCancel();
        return Promise.resolve(false);
    }
};

window.confirmAction = confirmAction;
