(function () {
    const form = document.querySelector("[data-ai-event-extractor]");
    if (!form) return;

    const output = form.querySelector("[data-event-extractor-output]");
    const fields = ["name", "booking_date", "time", "venue", "contact_info", "budget", "capacity"];

    form.addEventListener("submit", function (event) {
        event.preventDefault();
        const prompt = new FormData(form).get("event_prompt") || "";
        output.hidden = false;
        output.className = "alert alert-info mt-3 mb-0";
        output.textContent = "Reading your event brief...";

        const request = window.EventSpaceAI
            ? window.EventSpaceAI.request("extract-event.php", { prompt: prompt }, null)
            : Promise.reject(new Error("AI is unavailable."));

        request.then(function (data) {
            const result = data && data.result ? data.result : {};
            fields.forEach(function (field) {
                const input = document.querySelector('[name="' + field + '"]');
                if (input && result[field]) input.value = result[field];
            });
            output.className = "alert alert-success mt-3 mb-0";
            output.textContent = "Details extracted. Review them before saving.";
        }).catch(function (error) {
            output.className = "alert alert-danger mt-3 mb-0";
            output.textContent = error.message || "Unable to extract event details.";
        });
    });
}());
