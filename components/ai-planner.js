(function () {
    const form = document.querySelector("[data-ai-planner]");
    if (!form) return;
    const result = form.querySelector("[data-planner-result]");
    form.addEventListener("submit", function (event) {
        event.preventDefault();
        const prompt = new FormData(form).get("prompt") || "your event";
        result.hidden = false;
        result.innerHTML = '<div class="skeleton" style="height:18px;width:72%"></div><div class="skeleton mt-2" style="height:18px;width:92%"></div>';
        fetch("/api/ai/planner", { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ prompt }) })
            .then(function (response) { if (!response.ok) throw new Error("unavailable"); return response.json(); })
            .catch(function () { return { summary: "A thoughtful plan for " + prompt, budget: "Venue 40% · Food 25% · Decor 15% · Buffer 20%", timeline: "Book the venue first, confirm vendors 3 weeks out, and send invitations 10 days before.", checklist: "Guest list · Venue · Catering · Decor · Music · Day-of schedule", vendors: "Start with trusted local caterers and flexible decor partners." }; })
            .then(function (data) { result.innerHTML = "<strong>" + data.summary + "</strong><p class=\"mb-1 mt-2\"><b>Budget:</b> " + data.budget + "</p><p class=\"mb-1\"><b>Timeline:</b> " + data.timeline + "</p><p class=\"mb-1\"><b>Checklist:</b> " + data.checklist + "</p><p class=\"mb-0\"><b>Vendors:</b> " + data.vendors + "</p>"; });
    });
}());
