(function () {
    const root = document.documentElement;
    const storedTheme = window.localStorage.getItem("eventspace-theme");
    if (storedTheme === "dark" || storedTheme === "light") root.dataset.theme = storedTheme;

    const themeButton = document.querySelector("[data-theme-toggle]");
    if (themeButton) {
        themeButton.addEventListener("click", function () {
            const nextTheme = root.dataset.theme === "dark" ? "light" : "dark";
            root.dataset.theme = nextTheme;
            window.localStorage.setItem("eventspace-theme", nextTheme);
            themeButton.innerHTML = nextTheme === "dark" ? '<i class="bi bi-sun"></i>' : '<i class="bi bi-moon-stars"></i>';
        });
    }

    const sidebar = document.querySelector("[data-sidebar]");
    const backdrop = document.querySelector("[data-sidebar-backdrop]");
    const setSidebar = function (open) {
        if (!sidebar || !backdrop) return;
        sidebar.classList.toggle("is-open", open);
        backdrop.classList.toggle("is-visible", open);
    };
    document.querySelector("[data-sidebar-open]")?.addEventListener("click", function () { setSidebar(true); });
    document.querySelector("[data-sidebar-close]")?.addEventListener("click", function () { setSidebar(false); });
    backdrop?.addEventListener("click", function () { setSidebar(false); });

    const notificationButton = document.querySelector("[data-notification-toggle]");
    const notificationPopover = document.querySelector("[data-notification-popover]");
    const notificationClose = document.querySelector("[data-notification-close]");
    const setNotifications = function (open) {
        if (!notificationPopover || !notificationButton) return;
        notificationPopover.hidden = !open;
        notificationButton.setAttribute("aria-expanded", open ? "true" : "false");
    };
    notificationButton?.addEventListener("click", function (event) {
        event.stopPropagation();
        setNotifications(notificationPopover.hidden);
    });
    notificationClose?.addEventListener("click", function () { setNotifications(false); });
    document.addEventListener("click", function (event) {
        if (notificationPopover && !notificationPopover.hidden && !notificationPopover.contains(event.target) && event.target !== notificationButton) setNotifications(false);
    });
})();
