document.addEventListener("DOMContentLoaded", function () {
    const navBar = document.querySelector('.sideNavBar');
    const mainContent = document.getElementById("main-content");

    window.openNavBar = function () {
        navBar.style.width = "250px";
        mainContent.style.marginLeft = "250px";
        mainContent.style.transition = "margin-left 0.5s";
    };

    window.closeNavBar = function () {
        navBar.style.width = "0";
        mainContent.style.marginLeft = "0";
        mainContent.style.transition = "margin-left 0.5s";
    };
});

