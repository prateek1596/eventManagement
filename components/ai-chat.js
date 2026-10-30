(function () {
    const button = document.querySelector("[data-ai-chat-toggle]");
    const panel = document.querySelector("[data-ai-chat]");
    if (!button || !panel) return;
    button.addEventListener("click", function () { panel.hidden = !panel.hidden; if (!panel.hidden) panel.querySelector("input").focus(); });
    panel.querySelector("form").addEventListener("submit", function (event) {
        event.preventDefault();
        const input = panel.querySelector("input");
        const log = panel.querySelector("[data-chat-log]");
        if (!input.value.trim()) return;
        const question = input.value.trim();
        log.insertAdjacentHTML("beforeend", '<div class="text-end mb-2"><span class="badge bg-primary">' + question.replace(/[<>&"]/g, "") + "</span></div>");
        input.value = "";
        const request = window.EventSpaceAI
            ? window.EventSpaceAI.request("assistant.php", { message: question }, null)
            : Promise.resolve(null);
        request.then(function (data) {
            const answer = data && data.answer ? data.answer : "I can help with event details, tickets, venues, and booking steps.";
            log.insertAdjacentHTML("beforeend", '<p class="small mb-2">' + answer.replace(/[<>&"]/g, "") + "</p>");
        });
    });
}());
