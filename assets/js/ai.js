(function () {
    window.EventSpaceAI = {
        request: async function (endpoint, payload, fallback) {
            const controller = new AbortController();
            const timeout = window.setTimeout(function () { controller.abort(); }, 15000);
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                const response = await fetch((window.EVENTSPACE_BASE_URL || "/eventManagement") + "/api/ai/" + endpoint, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        ...(csrfToken ? { "X-CSRF-Token": csrfToken.content } : {})
                    },
                    body: JSON.stringify(payload || {}),
                    signal: controller.signal
                });
                if (!response.ok) throw new Error("AI request failed with status " + response.status);
                return await response.json();
            } catch (error) {
                console.warn("EventSpace AI request failed:", error);
                return fallback;
            } finally {
                window.clearTimeout(timeout);
            }
        }
    };
})();
