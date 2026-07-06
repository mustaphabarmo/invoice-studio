export type InvoiceItem = {
  id: string;
  title: string;
  description: string;
  quantity: number;
  unitPrice: number;
};

export type InvoiceData = {
  companyName: string;
  companyTagline: string;
  logoUrl: string;
  accentColor: string;
  clientLabel: string;
  clientName: string;
  clientAddress: string;
  invoiceNumber: string;
  issueDate: string;
  dueDate: string;
  currency: string;
  taxLabel: string;
  taxRate: number;
  discount: number;
  items: InvoiceItem[];
  paymentHeading: string;
  bankName: string;
  accountName: string;
  accountNumber: string;
  notes: string;
  phone: string;
  address: string;
};

export const initialInvoice: InvoiceData = {
  companyName: "",
  companyTagline: "",
  logoUrl: "",
  accentColor: "#55119b",
  clientLabel: "Invoice to:",
  clientName: "",
  clientAddress: "",
  invoiceNumber: "",
  issueDate: "",
  dueDate: "",
  currency: "NGN",
  taxLabel: "",
  taxRate: 0,
  discount: 0,
  items: [{ id: crypto.randomUUID(), title: "", description: "", quantity: 0, unitPrice: 0 }],
  paymentHeading: "",
  bankName: "",
  accountName: "",
  accountNumber: "",
  notes: "",
  phone: "",
  address: "",
};

export function invoiceTotals(data: InvoiceData) {
  const subtotal = data.items.reduce(
    (sum, item) => sum + Number(item.quantity || 0) * Number(item.unitPrice || 0),
    0,
  );
  const discount = Math.max(0, Number(data.discount || 0));
  const taxable = Math.max(0, subtotal - discount);
  const tax = taxable * (Math.max(0, Number(data.taxRate || 0)) / 100);
  return { subtotal, discount, tax, total: taxable + tax };
}
