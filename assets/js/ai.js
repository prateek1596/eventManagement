(function () {
    window.EventSpaceAI = {
        request: async function (endpoint, payload, fallback) {
            try {
                const response = await fetch((window.EVENTSPACE_BASE_URL || "/eventManagement") + "/api/ai/" + endpoint, {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(payload || {})
                });
                if (!response.ok) throw new Error("AI request failed");
                return await response.json();
            } catch (error) {
                return fallback;
            }
        }
    };
})();
