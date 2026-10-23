(function () {
    const form = document.querySelector("[data-natural-search]");
    if (!form) return;
    const status = form.querySelector("[data-search-status]");
    form.addEventListener("submit", function (event) {
        event.preventDefault();
        const input = form.querySelector("input");
        const value = input.value.trim();
        if (!value) return;
        status.hidden = false;
        status.textContent = "Understanding your request…";
        const fallbackSearch = function () {
            const location = value.match(/\b(in|at)\s+([a-z\s]+?)(?:\s+under|\s*$)/i);
            const price = value.match(/(?:under|below|less than)\s*[₹$]?([\d,]+)/i);
            return { query: value, location: location ? location[2].trim() : "", maxPrice: price ? price[1].replace(",", "") : "" };
        };
        const request = window.EventSpaceAI
            ? window.EventSpaceAI.request("search.php", { query: value }, null)
            : Promise.resolve(null);
        request.then(function (filters) {
            filters = filters || fallbackSearch();
            const params = new URLSearchParams();
            params.set("q", filters.query || "");
            if (filters.location) params.set("location", filters.location);
            if (filters.from) params.set("from", filters.from);
            if (filters.to) params.set("to", filters.to);
            if (filters.maxPrice) params.set("max_price", filters.maxPrice);
            status.textContent = "Showing matches" + (filters.location ? " in " + filters.location : "") + (filters.maxPrice ? " under ₹" + filters.maxPrice : "") + ".";
            window.setTimeout(function () { window.location.href = "catalog.php?" + params.toString(); }, 350);
        });
    });
}());
