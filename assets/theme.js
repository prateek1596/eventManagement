(function () {
    const root = document.documentElement;
    const saved = localStorage.getItem("eventspace-theme");
    if (saved === "dark") root.dataset.theme = "dark";

    function addToggle() {
        const navbar = document.querySelector(".navbar .navbar-collapse");
        if (!navbar || document.querySelector(".theme-toggle")) return;
        const button = document.createElement("button");
        button.type = "button";
        button.className = "theme-toggle ms-lg-2";
        button.setAttribute("aria-label", "Toggle dark mode");
        button.addEventListener("click", function () {
            const dark = root.dataset.theme === "dark";
            root.dataset.theme = dark ? "light" : "dark";
            localStorage.setItem("eventspace-theme", dark ? "light" : "dark");
            button.textContent = dark ? "☾" : "☀";
        });
        button.textContent = root.dataset.theme === "dark" ? "☀" : "☾";
        navbar.appendChild(button);
    }
    document.readyState === "loading" ? document.addEventListener("DOMContentLoaded", addToggle) : addToggle();
}());
