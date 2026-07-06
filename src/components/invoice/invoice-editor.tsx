import { Plus, Trash2 } from "lucide-react";
import { useState } from "react";
import type { InvoiceData, InvoiceItem } from "./types";

type Props = { data: InvoiceData; onChange: (data: InvoiceData) => void };

export function InvoiceEditor({ data, onChange }: Props) {
  const set = <K extends keyof InvoiceData>(key: K, value: InvoiceData[K]) => onChange({ ...data, [key]: value });
  const updateItem = (id: string, patch: Partial<InvoiceItem>) => set("items", data.items.map((item) => item.id === id ? { ...item, ...patch } : item));
  const commonTaxRates = [0, 5, 7.5, 10, 15];
  const selectedTaxRate = commonTaxRates.includes(data.taxRate) ? String(data.taxRate || "") : "custom";
  const [showAddress, setShowAddress] = useState(!!data.clientAddress);

  return <aside className="invoice-editor">
    <EditorSection title="Client & invoice" description="Who is being billed and invoice currency.">
      <div className="client-field-group" style={{ marginBottom: "11px" }}>
        <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: "5px" }}>
          <span style={{ color: "var(--muted-foreground)", fontSize: "10px", fontWeight: "650" }}>Client name</span>
          {!showAddress && (
            <button 
              type="button" 
              onClick={() => setShowAddress(true)} 
              style={{ background: "none", border: "none", color: "var(--primary)", fontSize: "10px", fontWeight: "700", cursor: "pointer", padding: 0 }}
            >
              + Add address
            </button>
          )}
        </div>
        <input type="text" placeholder="e.g. Acme Nigeria Limited" value={data.clientName} onChange={(e) => set("clientName", e.target.value)} />
      </div>

      {showAddress && (
        <div className="client-field-group" style={{ marginBottom: "11px" }}>
          <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: "5px" }}>
            <span style={{ color: "var(--muted-foreground)", fontSize: "10px", fontWeight: "650" }}>Client address</span>
            <button 
              type="button" 
              onClick={() => { setShowAddress(false); set("clientAddress", ""); }} 
              style={{ background: "none", border: "none", color: "#b42318", fontSize: "10px", fontWeight: "700", cursor: "pointer", padding: 0 }}
            >
              Remove
            </button>
          </div>
          <input type="text" placeholder="e.g. 12 Marina Road, Lagos" value={data.clientAddress} onChange={(e) => set("clientAddress", e.target.value)} />
        </div>
      )}

      <SelectField label="Currency" value={data.currency} onChange={(v) => set("currency", v)} options={[
          ["", "Select currency"], ["NGN", "NGN — Nigerian naira"], ["USD", "USD — US dollar"], ["GBP", "GBP — British pound"], ["EUR", "EUR — Euro"], ["GHS", "GHS — Ghanaian cedi"], ["ZAR", "ZAR — South African rand"], ["CAD", "CAD — Canadian dollar"],
        ]} />
    </EditorSection>
    <EditorSection title="Items" description="Add each product or service as a separate line.">
      {data.items.map((item, index) => <div className="editor-item" key={item.id}>
        <div className="editor-item-title"><b>Item {index + 1}</b><button type="button" aria-label="Remove item" onClick={() => set("items", data.items.filter((i) => i.id !== item.id))}><Trash2 /></button></div>
        <Field label="Service or item" placeholder="e.g. Website development" value={item.title} onChange={(v) => updateItem(item.id, { title: v })} />
        <label>Description<textarea rows={5} placeholder="e.g. Design and development of the company website" value={item.description} onChange={(e) => updateItem(item.id, { description: e.target.value })} /></label>
        <div className="editor-grid">
          <Field label="Quantity" placeholder="e.g. 1" type="number" value={item.quantity ? String(item.quantity) : ""} onChange={(v) => updateItem(item.id, { quantity: Number(v) })} />
          <Field label="Unit price" placeholder="e.g. 250000" type="number" value={item.unitPrice ? String(item.unitPrice) : ""} onChange={(v) => updateItem(item.id, { unitPrice: Number(v) })} />
        </div>
      </div>)}
      <button className="add-item" type="button" onClick={() => set("items", [...data.items, { id: crypto.randomUUID(), title: "", description: "", quantity: 0, unitPrice: 0 }])}><Plus /> Add item</button>
    </EditorSection>
    <EditorSection title="Totals & note" description="Optional adjustments and a message for the client.">
      <div className="editor-grid">
        <SelectField label="Tax type" value={data.taxLabel} onChange={(v) => onChange({ ...data, taxLabel: v, taxRate: v ? data.taxRate : 0 })} options={[["", "No tax"], ["VAT", "VAT"], ["WHT", "Withholding tax (WHT)"], ["Sales tax", "Sales tax"], ["Service tax", "Service tax"]]} />
        <SelectField label="Tax rate" value={selectedTaxRate} disabled={!data.taxLabel} onChange={(v) => v !== "custom" && set("taxRate", Number(v))} options={[["", "Select rate"], ["5", "5%"], ["7.5", "7.5%"], ["10", "10%"], ["15", "15%"], ["custom", "Custom rate"]]} />
      </div>
      {data.taxLabel && selectedTaxRate === "custom" && <Field label="Custom tax rate (%)" placeholder="e.g. 12.5" type="number" value={String(data.taxRate)} onChange={(v) => set("taxRate", Number(v))} />}
      <Field label="Discount amount" placeholder="e.g. 10000" type="number" value={String(data.discount)} onChange={(v) => set("discount", Number(v))} />
      <label>Note<textarea rows={3} placeholder="e.g. Thank you for your business." value={data.notes} onChange={(e) => set("notes", e.target.value)} /></label>
    </EditorSection>
  </aside>;
}

function EditorSection({ title, description, children }: { title: string; description?: string; children: React.ReactNode }) {
  return <section><h2>{title}</h2>{description && <p className="editor-section-help">{description}</p>}<div className="editor-fields">{children}</div></section>;
}

function Field({ label, value, onChange, type = "text", placeholder }: { label: string; value: string; onChange: (value: string) => void; type?: string; placeholder?: string }) {
  return <label>{label}<input type={type} placeholder={placeholder} value={value} onChange={(e) => onChange(e.target.value)} /></label>;
}

function SelectField({ label, value, onChange, options, disabled = false }: { label: string; value: string; onChange: (value: string) => void; options: string[][]; disabled?: boolean }) {
  return <label>{label}<select value={value} disabled={disabled} onChange={(e) => onChange(e.target.value)}>{options.map(([optionValue, text]) => <option key={optionValue} value={optionValue}>{text}</option>)}</select></label>;
}
