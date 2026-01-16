/**
 * Custom Modal Utility System
 * Provides beautiful, user-friendly modals to replace browser alerts
 * 
 * Usage:
 *   CustomModal.success('Message', 'Title', callback)
 *   CustomModal.error('Message', 'Title', callback)
 *   CustomModal.warning('Message', 'Title', callback)
 *   CustomModal.info('Message', 'Title', callback)
 *   CustomModal.confirm('Message', 'Title', onConfirm, onCancel)
 *   CustomModal.alert('Message')
 */

// Check if SweetAlert2 is loaded
if (typeof Swal === 'undefined') {
    console.error('CustomModal: SweetAlert2 is required. Please include SweetAlert2 before this script.');
}

const CustomModal = {
    /**
     * Show success modal
     * @param {string} message - Success message
     * @param {string} title - Optional title (default: 'Berhasil!')
     * @param {function} callback - Optional callback function
     */
    success: function(message, title = 'Berhasil!', callback = null) {
        Swal.fire({
            icon: 'success',
            title: title,
            text: message,
            confirmButtonText: 'OK',
            confirmButtonColor: '#198754',
            customClass: {
                popup: 'animated-modal',
                confirmButton: 'btn btn-success',
            },
            buttonsStyling: false,
            timer: callback ? null : 3000,
            timerProgressBar: true,
            showClass: {
                popup: 'animate__animated animate__fadeInUp animate__faster'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutDown animate__faster'
            }
        }).then((result) => {
            if (callback && typeof callback === 'function') {
                callback(result);
            }
        });
    },

    /**
     * Show error modal
     * @param {string} message - Error message
     * @param {string} title - Optional title (default: 'Error!')
     * @param {function} callback - Optional callback function
     */
    error: function(message, title = 'Error!', callback = null) {
        Swal.fire({
            icon: 'error',
            title: title,
            text: message,
            confirmButtonText: 'OK',
            confirmButtonColor: '#dc3545',
            customClass: {
                popup: 'animated-modal',
                confirmButton: 'btn btn-danger',
            },
            buttonsStyling: false,
            showClass: {
                popup: 'animate__animated animate__shakeX animate__faster'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutDown animate__faster'
            }
        }).then((result) => {
            if (callback && typeof callback === 'function') {
                callback(result);
            }
        });
    },

    /**
     * Show warning modal
     * @param {string} message - Warning message
     * @param {string} title - Optional title (default: 'Peringatan!')
     * @param {function} callback - Optional callback function
     */
    warning: function(message, title = 'Peringatan!', callback = null) {
        Swal.fire({
            icon: 'warning',
            title: title,
            text: message,
            confirmButtonText: 'OK',
            confirmButtonColor: '#ffc107',
            customClass: {
                popup: 'animated-modal',
                confirmButton: 'btn btn-warning text-dark',
            },
            buttonsStyling: false,
            showClass: {
                popup: 'animate__animated animate__fadeInUp animate__faster'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutDown animate__faster'
            }
        }).then((result) => {
            if (callback && typeof callback === 'function') {
                callback(result);
            }
        });
    },

    /**
     * Show info modal
     * @param {string} message - Info message
     * @param {string} title - Optional title (default: 'Informasi')
     * @param {function} callback - Optional callback function
     */
    info: function(message, title = 'Informasi', callback = null) {
        Swal.fire({
            icon: 'info',
            title: title,
            text: message,
            confirmButtonText: 'OK',
            confirmButtonColor: '#0dcaf0',
            customClass: {
                popup: 'animated-modal',
                confirmButton: 'btn btn-info',
            },
            buttonsStyling: false,
            timer: callback ? null : 3000,
            timerProgressBar: true,
            showClass: {
                popup: 'animate__animated animate__fadeInUp animate__faster'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutDown animate__faster'
            }
        }).then((result) => {
            if (callback && typeof callback === 'function') {
                callback(result);
            }
        });
    },

    /**
     * Show confirmation modal
     * @param {string} message - Confirmation message
     * @param {string} title - Optional title (default: 'Konfirmasi')
     * @param {function} onConfirm - Callback when confirmed
     * @param {function} onCancel - Optional callback when cancelled
     */
    confirm: function(message, title = 'Konfirmasi', onConfirm = null, onCancel = null) {
        Swal.fire({
            icon: 'question',
            title: title,
            text: message,
            showCancelButton: true,
            confirmButtonText: 'Ya',
            cancelButtonText: 'Tidak',
            confirmButtonColor: '#198754',
            cancelButtonColor: '#dc3545',
            customClass: {
                popup: 'animated-modal',
                confirmButton: 'btn btn-success me-2',
                cancelButton: 'btn btn-danger',
            },
            buttonsStyling: false,
            reverseButtons: true,
            showClass: {
                popup: 'animate__animated animate__fadeInUp animate__faster'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutDown animate__faster'
            }
        }).then((result) => {
            if (result.isConfirmed && onConfirm) {
                onConfirm(result);
            } else if (result.isDismissed && onCancel) {
                onCancel(result);
            }
        });
    },

    /**
     * Replace standard alert() function
     * @param {string} message - Message to display
     */
    alert: function(message) {
        this.info(message, 'Pemberitahuan');
    }
};

// Replace global alert() with CustomModal.alert() if SweetAlert2 is available
if (typeof Swal !== 'undefined') {
    window.alert = function(message) {
        CustomModal.alert(message);
    };
}

// Make CustomModal available globally
window.CustomModal = CustomModal;
