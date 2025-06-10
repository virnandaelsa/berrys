document.addEventListener("DOMContentLoaded", function() {
    if(window.successMessage) {
        showToast(window.successMessage, 'success');
    }
    if(window.errorMessage) {
        showToast(window.errorMessage, 'error');
    }
});

function showToast(message, type = "success") {
    let toast = document.createElement("div");
    toast.className = "flash-message " + type;
    toast.innerText = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = "0";
        setTimeout(() => toast.remove(), 500);
    }, 2500);
}

console.log("window.successMessage:", window.successMessage);
console.log("window.errorMessage:", window.errorMessage);
