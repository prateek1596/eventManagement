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

    const checkinModal = document.querySelector("[data-checkin-modal]");
    const checkinForm = checkinModal?.querySelector("[data-checkin-form]");
    const checkinToken = checkinModal?.querySelector("[data-checkin-token]");
    const checkinResult = checkinModal?.querySelector("[data-checkin-result]");
    const checkinSubmit = checkinModal?.querySelector("[data-checkin-submit]");
    const checkinBackdrop = document.createElement("div");
    checkinBackdrop.className = "modal-backdrop fade";
    const setCheckinModal = function (open) {
        if (!checkinModal) return;
        checkinModal.style.display = open ? "block" : "none";
        checkinModal.classList.toggle("show", open);
        checkinModal.setAttribute("aria-hidden", open ? "false" : "true");
        document.body.classList.toggle("modal-open", open);
        if (open) {
            checkinBackdrop.classList.add("show");
            document.body.appendChild(checkinBackdrop);
            window.setTimeout(function () { checkinToken?.focus(); }, 50);
        } else if (checkinBackdrop.parentNode) {
            checkinBackdrop.remove();
        }
        if (!open) {
            checkinForm?.reset();
            if (checkinResult) {
                checkinResult.hidden = true;
                checkinResult.textContent = "";
            }
        }
    };
    document.querySelector("[data-checkin-open]")?.addEventListener("click", function () { setCheckinModal(true); });
    checkinModal?.querySelectorAll("[data-checkin-close]").forEach(function (button) {
        button.addEventListener("click", function () { setCheckinModal(false); });
    });
    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape" && checkinModal?.classList.contains("show")) setCheckinModal(false);
    });
    checkinBackdrop.addEventListener("click", function () { setCheckinModal(false); });
    const showCheckinResult = function (message, guest, success) {
        if (!checkinResult) return;
        checkinResult.hidden = false;
        checkinResult.className = "checkin-result mt-3 alert " + (success ? "alert-success" : "alert-danger");
        checkinResult.textContent = success && guest
            ? guest.name + " · " + guest.event + "\n" + message
            : message;
    };
    checkinForm?.addEventListener("submit", async function (event) {
        event.preventDefault();
        if (!checkinSubmit || !checkinToken) return;
        checkinSubmit.disabled = true;
        checkinSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Checking in...';
        try {
            const response = await fetch(checkinForm.action, {
                method: "POST",
                headers: { "X-Requested-With": "XMLHttpRequest", "Accept": "application/json" },
                body: new FormData(checkinForm)
            });
            const result = await response.json();
            showCheckinResult(result.message || "The guest could not be checked in.", result.guest, result.success === true);
            if (result.success) checkinToken.value = "";
        } catch (error) {
            showCheckinResult("Unable to reach the check-in service. Please try again.", null, false);
        } finally {
            checkinSubmit.disabled = false;
            checkinSubmit.innerHTML = '<i class="bi bi-person-check me-1"></i>Check in guest';
        }
    });
})();
