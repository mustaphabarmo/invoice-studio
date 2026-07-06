import { createFileRoute, Link } from "@tanstack/react-router";
import { ArrowRight, CircleDollarSign, Clock3, FileCheck2, FilePlus2 } from "lucide-react";
import { useEffect, useState } from "react";
import { formatMoney, getInvoices, type SavedInvoice } from "@/lib/invoice-store";
import { invoiceTotals } from "@/components/invoice/types";

export const Route = createFileRoute("/")({ component: Dashboard });

function Dashboard() {
  const [invoices, setInvoices] = useState<SavedInvoice[]>([]);
  useEffect(() => { const load = () => setInvoices(getInvoices()); load(); window.addEventListener("kumail-data-change", load); return () => window.removeEventListener("kumail-data-change", load); }, []);
  const total = invoices.reduce((sum, invoice) => sum + invoiceTotals(invoice.data).total, 0);
  const currentMonth = new Date().toISOString().slice(0, 7);
  const monthCount = invoices.filter((invoice) => invoice.createdAt.startsWith(currentMonth)).length;
  return <div className="studio-page">
    <div className="studio-heading"><div><p>Overview</p><h1>Good to see you.</h1><span>Here’s what’s happening with your invoices.</span></div><Link to="/invoices" className="studio-primary"><FilePlus2 /> Create invoice</Link></div>
    <section className="metric-grid">
      <Metric icon={<FileCheck2 />} label="Total invoices" value={String(invoices.length)} detail="All saved invoices" />
      <Metric icon={<CircleDollarSign />} label="Total invoiced" value={formatMoney(total)} detail="Across all invoices" />
      <Metric icon={<Clock3 />} label="Created this month" value={String(monthCount)} detail={new Intl.DateTimeFormat("en", { month:"long", year:"numeric" }).format(new Date())} />
    </section>
    <section className="studio-panel recent-panel"><div className="panel-head"><div><h2>Recent invoices</h2><p>Your latest saved work</p></div><Link to="/history">View history <ArrowRight /></Link></div>
      {invoices.length ? <div className="recent-list">{invoices.slice(0, 5).map((invoice) => <div key={invoice.id}><span className="invoice-avatar">{invoice.data.clientName.slice(0,2).toUpperCase()}</span><div><b>{invoice.data.clientName}</b><small>Invoice #{invoice.data.invoiceNumber} · {new Date(invoice.createdAt).toLocaleDateString()}</small></div><strong>{formatMoney(invoiceTotals(invoice.data).total, invoice.data.currency)}</strong><em>{invoice.status}</em></div>)}</div>
      : <div className="empty-state"><FilePlus2 /><h3>No invoices yet</h3><p>Create your first invoice and it will appear here.</p><Link to="/invoices">Create invoice</Link></div>}
    </section>
  </div>;
}

function Metric({ icon, label, value, detail }: { icon: React.ReactNode; label: string; value: string; detail: string }) { return <article className="metric-card"><span>{icon}</span><div><p>{label}</p><strong>{value}</strong><small>{detail}</small></div></article>; }
