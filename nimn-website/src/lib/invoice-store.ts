import { initialInvoice, type InvoiceData } from "@/components/invoice/types";
import { api } from "@/lib/api";

export type Branding = Pick<InvoiceData, "companyName" | "companyTagline" | "logoUrl" | "accentColor" | "paymentHeading" | "bankName" | "accountName" | "accountNumber" | "phone" | "address">;
export type SavedInvoice = { id: string; createdAt: string; updatedAt: string; status: "Draft" | "Final"; data: InvoiceData };
type ApiInvoice = { id: string; created_at: string; updated_at: string; status: "Draft" | "Final"; data: InvoiceData };

export const defaultBranding: Branding = { companyName: "Your company", companyTagline: "Built for better business", logoUrl: "/kumail-icon.png", paymentHeading: "PAYMENT ACCOUNT", bankName: "", accountName: "", accountNumber: "", phone: "", address: "", accentColor: "#55119b" };
let brandingCache = defaultBranding;
let invoiceCache: SavedInvoice[] = [];
const mapInvoice = (value: ApiInvoice): SavedInvoice => ({ id: value.id, createdAt: value.created_at, updatedAt: value.updated_at, status: value.status, data: value.data });

export function getBranding() { return brandingCache; }
export async function loadBranding() { const result = await api<{data: Branding | null}>("/studio/branding"); brandingCache = { ...defaultBranding, ...result.data }; return brandingCache; }
export async function saveBranding(value: Branding) { const result = await api<{data: Branding}>("/studio/branding", { method: "PUT", body: JSON.stringify({ data: value }) }); brandingCache = result.data; return result.data; }
export async function getInvoices() { const result = await api<{data: ApiInvoice[]}>("/studio/invoices"); invoiceCache = result.data.map(mapInvoice); return invoiceCache; }
export function brandedInvoice(): InvoiceData { const today = new Date().toISOString().slice(0, 10); return { ...initialInvoice, ...brandingCache, invoiceNumber: `INV-${String(invoiceCache.length + 1).padStart(4, "0")}`, issueDate: today, items: initialInvoice.items.map((item) => ({ ...item, id: crypto.randomUUID() })) }; }
export async function saveInvoice(data: InvoiceData, status: SavedInvoice["status"] = "Final", existingId?: string) { const result = await api<{data: ApiInvoice}>("/studio/invoices", { method: "POST", body: JSON.stringify({ id: existingId, data, status }) }); return mapInvoice(result.data); }
export async function deleteInvoice(id: string) { await api(`/studio/invoices/${id}`, { method: "DELETE" }); }
export async function duplicateInvoice(invoice: SavedInvoice) { return saveInvoice({ ...invoice.data, invoiceNumber: `${invoice.data.invoiceNumber}-COPY`, items: invoice.data.items.map((item) => ({ ...item, id: crypto.randomUUID() })) }, "Draft"); }
export function formatMoney(value: number, currency = "NGN") { return new Intl.NumberFormat("en-NG", { style: "currency", currency, maximumFractionDigits: 2 }).format(value); }
