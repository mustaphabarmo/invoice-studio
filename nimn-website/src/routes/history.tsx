import { createFileRoute, Link } from "@tanstack/react-router";
import { Copy, Download, Eye, FileImage, FilePlus2, Search, Trash2, X } from "lucide-react";
import { useEffect, useRef, useState } from "react";
import { InvoicePreview } from "@/components/invoice/invoice-preview";
import { invoiceTotals } from "@/components/invoice/types";
import { deleteInvoice, duplicateInvoice, formatMoney, getInvoices, type SavedInvoice } from "@/lib/invoice-store";
import { exportInvoicePng, printInvoice } from "@/lib/invoice-export";

export const Route = createFileRoute("/history")({ component: HistoryPage });
function HistoryPage() {
  const [invoices, setInvoices] = useState<SavedInvoice[]>([]); const [query, setQuery] = useState(""); const [selected, setSelected] = useState<SavedInvoice>(); const previewRef = useRef<HTMLDivElement>(null);
  const load = () => { getInvoices().then(setInvoices); };
  useEffect(() => { load(); }, []);
  const filtered = invoices.filter((invoice) => `${invoice.data.invoiceNumber} ${invoice.data.clientName}`.toLowerCase().includes(query.toLowerCase()));
  async function remove(invoice: SavedInvoice) { if (window.confirm(`Delete invoice #${invoice.data.invoiceNumber}?`)) { await deleteInvoice(invoice.id); load(); if (selected?.id === invoice.id) setSelected(undefined); } }
  return <div className="studio-page history-page"><div className="studio-heading"><div><p>Archive</p><h1>Invoice history</h1><span>View, duplicate, download, or remove saved invoices.</span></div><Link to="/invoices" className="studio-primary"><FilePlus2 /> Create invoice</Link></div>
    <section className="studio-panel history-panel"><div className="history-tools"><div><Search /><input aria-label="Search invoices" placeholder="Search client or invoice number" value={query} onChange={(e) => setQuery(e.target.value)} /></div><span>{filtered.length} invoice{filtered.length === 1 ? "" : "s"}</span></div>
      {filtered.length ? <div className="history-table"><div className="history-row head"><span>Invoice</span><span>Client</span><span>Date</span><span>Amount</span><span>Status</span><span>Actions</span></div>{filtered.map((invoice) => <div className="history-row" key={invoice.id}><span><b>#{invoice.data.invoiceNumber}</b><small>{invoice.data.items.length} item{invoice.data.items.length === 1 ? "" : "s"}</small></span><span>{invoice.data.clientName}</span><span>{new Date(invoice.createdAt).toLocaleDateString()}</span><span><b>{formatMoney(invoiceTotals(invoice.data).total, invoice.data.currency)}</b></span><span><em>{invoice.status}</em></span><span className="row-actions"><button title="View invoice" onClick={() => setSelected(invoice)}><Eye /></button><button title="Duplicate invoice" onClick={() => duplicateInvoice(invoice)}><Copy /></button><button title="Delete invoice" className="danger" onClick={() => remove(invoice)}><Trash2 /></button></span></div>)}</div>
      : <div className="empty-state"><FilePlus2 /><h3>{query ? "No matching invoices" : "No invoice history"}</h3><p>{query ? "Try a different search." : "Save an invoice to build your history."}</p>{!query && <Link to="/invoices">Create invoice</Link>}</div>}
    </section>
    {selected && <div className="invoice-modal" role="dialog" aria-modal="true" aria-label={`Invoice ${selected.data.invoiceNumber}`}><button className="modal-backdrop" aria-label="Close preview" onClick={() => setSelected(undefined)} /><div className="modal-panel"><header><div><b>Invoice #{selected.data.invoiceNumber}</b><span>{selected.data.clientName}</span></div><div><button onClick={() => previewRef.current && exportInvoicePng(previewRef.current, `invoice-${selected.data.invoiceNumber}.png`)}><FileImage /> PNG</button><button className="primary" onClick={() => previewRef.current && printInvoice(previewRef.current)}><Download /> PDF</button><button aria-label="Close preview" onClick={() => setSelected(undefined)}><X /></button></div></header><div className="modal-canvas"><InvoicePreview ref={previewRef} data={selected.data} /></div></div></div>}
  </div>;
}
