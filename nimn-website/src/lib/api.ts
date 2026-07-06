const API_URL = (import.meta.env.VITE_API_URL || "http://localhost:8000/api/v1").replace(/\/$/, "");
const TOKEN_KEY = "invoice_studio_token";

export type User = { id: number; first_name: string; last_name: string; email: string };

export function token() { return localStorage.getItem(TOKEN_KEY); }
export function setToken(value: string | null) { value ? localStorage.setItem(TOKEN_KEY, value) : localStorage.removeItem(TOKEN_KEY); }

export async function api<T>(path: string, options: RequestInit = {}): Promise<T> {
  const response = await fetch(`${API_URL}${path}`, {
    ...options,
    headers: { Accept: "application/json", "Content-Type": "application/json", ...(token() ? { Authorization: `Bearer ${token()}` } : {}), ...options.headers },
  });
  const body = await response.json().catch(() => ({}));
  if (!response.ok) {
    const errors = body.errors ? Object.values(body.errors).flat().join(" ") : body.message;
    throw new Error(errors || "Something went wrong. Please try again.");
  }
  return body as T;
}

export async function authenticate(path: "/login" | "/register", payload: Record<string, string>) {
  const result = await api<{ data: { user: User; token: string } }>(path, { method: "POST", body: JSON.stringify(payload) });
  setToken(result.data.token);
  localStorage.setItem("invoice_studio_user", JSON.stringify(result.data.user));
  return result.data.user;
}

export async function logout() {
  try { await api("/studio/logout", { method: "POST" }); } finally { setToken(null); localStorage.removeItem("invoice_studio_user"); }
}

export function currentUser(): User | null {
  try { return JSON.parse(localStorage.getItem("invoice_studio_user") || "null"); } catch { return null; }
}
