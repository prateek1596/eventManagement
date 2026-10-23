(function () {
    const chartDefaults = {
        color: getComputedStyle(document.documentElement).getPropertyValue("--app-muted"),
        borderColor: getComputedStyle(document.documentElement).getPropertyValue("--app-border")
    };
    window.EventSpaceCharts = { defaults: chartDefaults };
})();
