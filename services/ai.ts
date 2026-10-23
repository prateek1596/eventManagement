export async function askAI<T>(path: string, payload: Record<string, unknown>, fallback: T): Promise<T> {
    try {
        const response = await fetch(`/api/ai/${path}`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
        });
        if (!response.ok) throw new Error(`AI request failed: ${response.status}`);
        return await response.json() as T;
    } catch {
        return fallback;
    }
}
