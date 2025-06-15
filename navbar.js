// --- FORCE SIDEBAR CLOSED ON INITIAL LOAD -------------------------
document.addEventListener('DOMContentLoaded', () => {
    // Remove any inline width or open-class that slipped in
    const side = document.getElementById('mySidenav');
    side.style.width = '0';           // guarantees hidden
    side.classList.remove('open');    // if some code adds a class
});
