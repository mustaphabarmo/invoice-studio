import { createFileRoute } from "@tanstack/react-router";
import { ArrowLeft, ArrowRight, Download, FileImage, RotateCcw, Save } from "lucide-react";
import { useRef, useState } from "react";
import { InvoiceEditor } from "@/components/invoice/invoice-editor";
import { InvoicePreview } from "@/components/invoice/invoice-preview";
import type { InvoiceData } from "@/components/invoice/types";
import { brandedInvoice, saveInvoice } from "@/lib/invoice-store";
import { exportInvoicePng, printInvoice } from "@/lib/invoice-export";

export const Route = createFileRoute("/invoices")({ component: InvoicePage });

function InvoicePage() {
  const [data, setData] = useState<InvoiceData>(() => brandedInvoice());
  const [savedId, setSavedId] = useState<string>();
  const [notice, setNotice] = useState("");
  const [exporting, setExporting] = useState(false);
  const [step, setStep] = useState<"edit" | "preview">("edit");
  const previewRef = useRef<HTMLDivElement>(null);
  const filename = (ext: string) => `invoice-${data.invoiceNumber || "draft"}.${ext}`;
  function persist() { const saved = saveInvoice(data, "Final", savedId); setSavedId(saved.id); setNotice("Invoice saved to history"); setTimeout(() => setNotice(""), 2400); }
  async function png() { if (!previewRef.current) return; setExporting(true); try { await exportInvoicePng(previewRef.current, filename("png")); } finally { setExporting(false); } }
  function reset() { setData(brandedInvoice()); setSavedId(undefined); setStep("edit"); }

  return <div className="invoice-page studio-page">
    <div className="invoice-toolbar"><div><p>Invoice studio</p><h1>{step === "edit" ? "Create invoice" : "Preview invoice"}</h1><span>{step === "edit" ? "Complete the invoice details, then continue to preview." : "Review the finished invoice before saving or exporting."}</span></div><div className="invoice-actions">
      <button type="button" onClick={reset}><RotateCcw /> New</button>
      {step === "edit" ? <button className="primary" type="button" onClick={() => setStep("preview")}><span>Preview invoice</span><ArrowRight /></button> : <>
        <button type="button" onClick={() => setStep("edit")}><ArrowLeft /> Edit</button>
        <button type="button" onClick={persist}><Save /> {savedId ? "Update" : "Save"}</button>
        <button type="button" onClick={png} disabled={exporting}><FileImage /> {exporting ? "Exporting…" : "PNG"}</button>
        <button className="primary" type="button" onClick={() => previewRef.current && printInvoice(previewRef.current)}><Download /> PDF</button>
      </>}
    </div></div>
    {notice && <div className="save-notice">{notice}</div>}
    {step === "edit" ? <div className="invoice-form-stage"><InvoiceEditor data={data} onChange={setData} /></div> : <div className="invoice-stage invoice-preview-stage"><InvoicePreview ref={previewRef} data={data} /></div>}
  </div>;
}
