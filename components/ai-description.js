(function () {
    const form = document.querySelector("[data-ai-description]");
    if (!form) return;
    const output = form.querySelector("[data-description-output]");
    form.addEventListener("submit", function (event) {
        event.preventDefault();
        const title = form.querySelector("[name='ai_title']").value.trim();
        const keywords = form.querySelector("[name='ai_keywords']").value.trim();
        output.hidden = false;
        output.innerHTML = '<div class="skeleton" style="height:18px;width:82%"></div><div class="skeleton mt-2" style="height:18px;width:94%"></div>';
        fetch("/api/ai/description", { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ title: title, keywords: keywords }) })
            .then(function (response) { if (!response.ok) throw new Error("unavailable"); return response.json(); })
            .catch(function () {
                return {
                    description: title + " brings " + (keywords || "good people and great energy") + " together for an evening designed to be remembered.",
                    tagline: "Your next favourite memory starts here.",
                    hashtags: "#" + title.toLowerCase().replace(/[^a-z0-9]+/g, "") + " #EventSpace #MakeMemories"
                };
            })
            .then(function (data) {
                output.innerHTML = "<strong>Draft ready</strong><p class=\"mb-1 mt-2\"><b>Description:</b> " + data.description + "</p><p class=\"mb-1\"><b>Tagline:</b> " + data.tagline + "</p><p class=\"mb-0\"><b>Hashtags:</b> " + data.hashtags + "</p>";
            });
    });
}());
