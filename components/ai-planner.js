(function () {
    const form = document.querySelector("[data-ai-planner]");
    if (!form) return;
    const result = form.querySelector("[data-planner-result]");
    const escapeHtml = function (value) {
        return String(value).replace(/[&<>"']/g, function (character) {
            return { "&": "&amp;", "<": "&lt;", ">": "&gt;", "\"": "&quot;", "'": "&#039;" }[character];
        });
    };
    form.addEventListener("submit", function (event) {
        event.preventDefault();
        const prompt = new FormData(form).get("prompt") || "your event";
        result.hidden = false;
        result.innerHTML = '<div class="skeleton" style="height:18px;width:72%"></div><div class="skeleton mt-2" style="height:18px;width:92%"></div>';
        const fallback = {
            summary: "A thoughtful first plan for " + prompt,
            budget: ["Venue and permits", "Food and beverages", "Decor and production", "Contingency"],
            timeline: ["Confirm the guest list and budget", "Shortlist the venue and vendors", "Send invitations and confirm suppliers", "Prepare the day-of run sheet"],
            checklist: ["Guest list", "Venue", "Catering", "Decor", "Music", "Day-of schedule"],
            vendors: ["Local caterer", "Decor and lighting partner", "Photographer", "Event host or DJ"]
        };
        const request = window.EventSpaceAI
            ? window.EventSpaceAI.request("planner.php", { prompt: prompt }, fallback)
            : Promise.resolve(fallback);
        request.then(function (data) {
            const list = function (items) {
                return Array.isArray(items) ? "<ul class=\"mb-0\">" + items.map(function (item) { return "<li>" + escapeHtml(item) + "</li>"; }).join("") + "</ul>" : "<p class=\"mb-0\">" + escapeHtml(items || "") + "</p>";
            };
            result.innerHTML = "<strong>" + escapeHtml(data.summary || fallback.summary) + "</strong><div class=\"row g-3 mt-1\"><div class=\"col-md-6\"><b>Budget split</b>" + list(data.budget) + "</div><div class=\"col-md-6\"><b>Timeline</b>" + list(data.timeline) + "</div><div class=\"col-md-6\"><b>Checklist</b>" + list(data.checklist) + "</div><div class=\"col-md-6\"><b>Vendor ideas</b>" + list(data.vendors) + "</div></div><button type=\"button\" class=\"btn btn-sm btn-outline-primary mt-3\" data-save-planner>Save as draft event</button>";
            const saveButton = result.querySelector("[data-save-planner]");
            saveButton.addEventListener("click", function () {
                saveButton.textContent = "Draft details ready to copy";
                saveButton.disabled = true;
            });
        });
    });
}());
