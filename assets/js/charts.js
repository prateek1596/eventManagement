(function () {
    if (typeof Chart === "undefined") return;
    const styles = getComputedStyle(document.documentElement);
    const accent = styles.getPropertyValue("--app-accent").trim();
    const muted = styles.getPropertyValue("--app-muted").trim();
    const border = styles.getPropertyValue("--app-border").trim();
    const data = window.eventSpaceDashboardData || {};
    const bookingCanvas = document.getElementById("bookingsChart");
    if (bookingCanvas) {
        new Chart(bookingCanvas, {
            type: "line",
            data: { labels: data.bookingLabels || [], datasets: [{ data: data.bookingValues || [], borderColor: accent, backgroundColor: accent + "20", fill: true, tension: .4, pointRadius: 3, pointBackgroundColor: accent }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false }, ticks: { color: muted } }, y: { beginAtZero: true, ticks: { precision: 0, color: muted }, grid: { color: border } } } }
        });
    }
    const categoryCanvas = document.getElementById("categoryChart");
    if (categoryCanvas) {
        new Chart(categoryCanvas, {
            type: "doughnut",
            data: { labels: data.categoryLabels || [], datasets: [{ data: data.categoryValues || [], backgroundColor: [accent, "#a16cf4", "#f2b880", "#55c8ae"], borderWidth: 0 }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: "70%", plugins: { legend: { position: "bottom", labels: { color: muted, usePointStyle: true, padding: 16 } } } }
        });
    }
})();
