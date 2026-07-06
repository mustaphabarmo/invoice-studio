import { createFileRoute, Link } from "@tanstack/react-router";
import { ArrowRight, CircleDollarSign, Clock3, FileCheck2, FilePlus2 } from "lucide-react";
import { useEffect, useState } from "react";
import { formatMoney, getInvoices, type SavedInvoice } from "@/lib/invoice-store";
import { invoiceTotals } from "@/components/invoice/types";
export const Route = createFileRoute("/dashboard")({ component: Dashboard });
function Dashboard() {
 const [invoices,setInvoices]=useState<SavedInvoice[]>([]); const [loading,setLoading]=useState(true);
 useEffect(()=>{getInvoices().then(setInvoices).finally(()=>setLoading(false))},[]);
 const total=invoices.reduce((s,i)=>s+invoiceTotals(i.data).total,0), month=new Date().toISOString().slice(0,7);
 return <div className="studio-page"><div className="studio-heading"><div><p>Overview</p><h1>Good to see you.</h1><span>Here’s what’s happening with your invoices.</span></div><Link to="/invoices" className="studio-primary"><FilePlus2/>Create invoice</Link></div><section className="metric-grid"><Metric icon={<FileCheck2/>} label="Total invoices" value={String(invoices.length)}/><Metric icon={<CircleDollarSign/>} label="Total invoiced" value={formatMoney(total)}/><Metric icon={<Clock3/>} label="Created this month" value={String(invoices.filter(i=>i.createdAt.startsWith(month)).length)}/></section><section className="studio-panel recent-panel"><div className="panel-head"><div><h2>Recent invoices</h2><p>Your latest saved work</p></div><Link to="/history">View history <ArrowRight/></Link></div>{loading?<div className="empty-state"><p>Loading invoices…</p></div>:invoices.length?<div className="recent-list">{invoices.slice(0,5).map(i=><div key={i.id}><span className="invoice-avatar">{i.data.clientName.slice(0,2).toUpperCase()}</span><div><b>{i.data.clientName}</b><small>Invoice #{i.data.invoiceNumber} · {new Date(i.createdAt).toLocaleDateString()}</small></div><strong>{formatMoney(invoiceTotals(i.data).total,i.data.currency)}</strong><em>{i.status}</em></div>)}</div>:<div className="empty-state"><FilePlus2/><h3>No invoices yet</h3><p>Create your first invoice and it will appear here.</p><Link to="/invoices">Create invoice</Link></div>}</section></div>
}
function Metric({icon,label,value}:{icon:React.ReactNode;label:string;value:string}){return <article className="metric-card"><span>{icon}</span><div><p>{label}</p><strong>{value}</strong><small>Live from your workspace</small></div></article>}
