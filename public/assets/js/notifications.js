document.addEventListener('DOMContentLoaded', function () {
    if (window.successMessage) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: window.successMessage,
            showConfirmButton: false,
            timer: 5000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.style.zIndex = "9999";
                toast.style.backdropFilter = "none";
                toast.style.backgroundColor = "rgba(255, 255, 255, 0.9)";
                toast.style.boxShadow = "0px 4px 10px rgba(0, 0, 0, 0.2)";
                toast.style.borderRadius = "8px";
                toast.style.padding = "10px 20px";
            },
        });
    }
});
