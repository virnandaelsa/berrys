document.addEventListener("DOMContentLoaded", function() {
    if(window.successMessage) {
        showToast(window.successMessage, 'success');
    }
    if(window.errorMessage) {
        showToast(window.errorMessage, 'error');
    }
});

function showToast(message, type = "success") {
    const duration = 5000; // <-- SEKARANG 5 DETIK

    // Container
    let toast = document.createElement("div");
    toast.className = "flash-message " + type;

    // Icon
    let icon = document.createElement("span");
    icon.className = "icon";
    icon.innerHTML = type === 'success'
        ? '<i class="fa fa-check-circle"></i>'
        : '<i class="fa fa-times-circle"></i>';
    toast.appendChild(icon);

    // Message
    let text = document.createElement("span");
    text.style.marginLeft = "10px";
    text.textContent = message;
    toast.appendChild(text);

    // Close button
    let btn = document.createElement("button");
    btn.className = "close-btn";
    btn.innerHTML = "&times;";
    btn.setAttribute('aria-label', 'Close');
    btn.onclick = () => { toast.style.opacity = "0"; setTimeout(() => toast.remove(), 350); };
    toast.appendChild(btn);

    // Progress bar
    let progress = document.createElement("div");
    progress.className = "progress-bar";
    progress.style.transitionDuration = duration + "ms";
    toast.appendChild(progress);

    document.body.appendChild(toast);

    // Animate progress bar
    setTimeout(() => {
        progress.style.transform = "scaleX(0)";
    }, 20);

    // Hide on timeout
    setTimeout(() => {
        toast.style.opacity = "0";
        setTimeout(() => toast.remove(), 350);
    }, duration);
}
