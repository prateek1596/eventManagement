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
        fetch("/api/ai/search", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ query: value })
        }).then(function (response) {
            if (!response.ok) throw new Error("unavailable");
            return response.json();
        }).catch(function () {
            const location = value.match(/\b(in|at)\s+([a-z\s]+?)(?:\s+under|\s*$)/i);
            const price = value.match(/(?:under|below|less than)\s*[₹$]?([\d,]+)/i);
            return { query: value, location: location ? location[2].trim() : "", maxPrice: price ? price[1].replace(",", "") : "" };
        }).then(function (filters) {
            const params = new URLSearchParams();
            params.set("q", filters.location || filters.query || "");
            status.textContent = "Showing matches" + (filters.location ? " in " + filters.location : "") + (filters.maxPrice ? " under ₹" + filters.maxPrice : "") + ".";
            window.setTimeout(function () { window.location.href = "catalog.php?" + params.toString(); }, 350);
        });
    });
}());
