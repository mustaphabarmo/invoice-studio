import { initialInvoice, type InvoiceData } from "@/components/invoice/types";

export type Branding = Pick<InvoiceData, "companyName" | "companyTagline" | "logoUrl" | "accentColor" | "paymentHeading" | "bankName" | "accountName" | "accountNumber" | "phone" | "address">;
export type SavedInvoice = { id: string; createdAt: string; updatedAt: string; status: "Draft" | "Final"; data: InvoiceData };

const BRAND_KEY = "kumail_invoice_branding";
const HISTORY_KEY = "kumail_invoice_history";

export const defaultBranding: Branding = {
  companyName: "Kumail Innovations Limited",
  companyTagline: "Meet All Your Needs",
  logoUrl: "/kumail-icon.png",
  paymentHeading: "PAYMENT ACCOUNT",
  bankName: "KUDA BANK",
  accountName: "Kumail Innovations",
  accountNumber: "3001174378",
  phone: "08088508852",
  address: "Imam House by Ahmadu Bello way",
  accentColor: "#55119b",
};

export function getBranding(): Branding {
  if (typeof window === "undefined") return defaultBranding;
  try { return { ...defaultBranding, ...JSON.parse(localStorage.getItem(BRAND_KEY) || "{}") }; } catch { return defaultBranding; }
}
export function saveBranding(value: Branding) { localStorage.setItem(BRAND_KEY, JSON.stringify(value)); window.dispatchEvent(new Event("kumail-data-change")); }
export function brandedInvoice(): InvoiceData {
  const count = getInvoices().length;
  const generatedInvoiceNumber = `INV-${String(count + 1).padStart(4, "0")}`;
  const today = new Date().toISOString().slice(0, 10);
  return {
    ...initialInvoice,
    ...getBranding(),
    invoiceNumber: generatedInvoiceNumber,
    issueDate: today,
    dueDate: "",
    items: initialInvoice.items.map((item) => ({ ...item, id: crypto.randomUUID() }))
  };
}
export function getInvoices(): SavedInvoice[] {
  if (typeof window === "undefined") return [];
  try { return JSON.parse(localStorage.getItem(HISTORY_KEY) || "[]") as SavedInvoice[]; } catch { return []; }
}
export function saveInvoice(data: InvoiceData, status: SavedInvoice["status"] = "Final", existingId?: string) {
  const invoices = getInvoices();
  const now = new Date().toISOString();
  const index = existingId ? invoices.findIndex((invoice) => invoice.id === existingId) : -1;
  const saved: SavedInvoice = index >= 0
    ? { ...invoices[index], data, status, updatedAt: now }
    : { id: crypto.randomUUID(), data, status, createdAt: now, updatedAt: now };
  if (index >= 0) invoices[index] = saved; else invoices.unshift(saved);
  localStorage.setItem(HISTORY_KEY, JSON.stringify(invoices));
  window.dispatchEvent(new Event("kumail-data-change"));
  return saved;
}
export function deleteInvoice(id: string) { localStorage.setItem(HISTORY_KEY, JSON.stringify(getInvoices().filter((invoice) => invoice.id !== id))); window.dispatchEvent(new Event("kumail-data-change")); }
export function duplicateInvoice(invoice: SavedInvoice) { return saveInvoice({ ...invoice.data, invoiceNumber: `${invoice.data.invoiceNumber}-COPY`, items: invoice.data.items.map((item) => ({ ...item, id: crypto.randomUUID() })) }, "Draft"); }
export function formatMoney(value: number, currency = "NGN") { return new Intl.NumberFormat("en-NG", { style: "currency", currency, maximumFractionDigits: 2 }).format(value); }
