(function () {
    const button = document.querySelector("[data-ai-chat-toggle]");
    const panel = document.querySelector("[data-ai-chat]");
    if (!button || !panel) return;
    const form = panel.querySelector("form");
    const input = panel.querySelector("input");
    const sendButton = form.querySelector("button");
    const log = panel.querySelector("[data-chat-log]");
    const history = [];

    button.setAttribute("aria-expanded", "false");
    button.addEventListener("click", function () {
        panel.hidden = !panel.hidden;
        button.setAttribute("aria-expanded", String(!panel.hidden));
        if (!panel.hidden) input.focus();
    });

    function addMessage(text, className) {
        const message = document.createElement("p");
        message.className = "small mb-2 " + className;
        message.textContent = text;
        log.appendChild(message);
        log.scrollTop = log.scrollHeight;
    }

    form.addEventListener("submit", async function (event) {
        event.preventDefault();
        const question = input.value.trim();
        if (!question || sendButton.disabled) return;
        addMessage(question, "text-end");
        input.value = "";
        history.push({ role: "user", content: question });
        sendButton.disabled = true;
        input.disabled = true;
        sendButton.textContent = "…";
        addMessage("Thinking…", "text-muted");

        const data = window.EventSpaceAI
            ? await window.EventSpaceAI.request("assistant.php", { message: question, history: history.slice(-7, -1) }, null)
            : null;
        const thinking = log.lastElementChild;
        if (thinking) thinking.remove();
        const answer = data && data.answer
            ? data.answer
            : "I couldn’t reach the assistant right now. You can still browse events or ask about booking steps.";
        addMessage(answer, "");
        history.push({ role: "assistant", content: answer });
        history.splice(0, Math.max(0, history.length - 8));
        sendButton.disabled = false;
        input.disabled = false;
        sendButton.textContent = "Send";
        input.focus();
    });
}());
