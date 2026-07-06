import { createFileRoute } from "@tanstack/react-router";
import { Check, RotateCcw, Upload } from "lucide-react";
import { useRef, useState } from "react";
import { defaultBranding, getBranding, saveBranding, type Branding } from "@/lib/invoice-store";

export const Route = createFileRoute("/branding")({ component: BrandingPage });
function BrandingPage() {
  const [brand, setBrand] = useState<Branding>(() => getBranding()); const [saved, setSaved] = useState(false); const fileRef = useRef<HTMLInputElement>(null);
  const set = <K extends keyof Branding>(key: K, value: Branding[K]) => setBrand({ ...brand, [key]: value });
  function upload(file?: File) { if (!file || !file.type.startsWith("image/")) return; const reader = new FileReader(); reader.onload = () => set("logoUrl", String(reader.result)); reader.readAsDataURL(file); }
  function save() { saveBranding(brand); setSaved(true); setTimeout(() => setSaved(false), 2200); }
  return <div className="studio-page brand-page"><div className="studio-heading"><div><p>Brand settings</p><h1>Branding</h1><span>These defaults are applied to every new invoice.</span></div><button className="studio-primary" onClick={save}><Check /> {saved ? "Saved" : "Save branding"}</button></div>
    <div className="brand-grid"><section className="studio-panel brand-form"><h2>Company identity</h2><p>Manage the details shown on your invoices.</p>
      <div className="logo-upload"><img src={brand.logoUrl} alt="Current logo" /><div><b>Company logo</b><span>PNG, JPG or SVG. A square image works best.</span><button onClick={() => fileRef.current?.click()}><Upload /> Upload logo</button><input ref={fileRef} hidden type="file" accept="image/*" onChange={(e) => upload(e.target.files?.[0])} /></div></div>
      <BrandField label="Company name" value={brand.companyName} set={(v) => set("companyName", v)} /><BrandField label="Tagline" value={brand.companyTagline} set={(v) => set("companyTagline", v)} /><label className="brand-field">Brand color<input type="color" value={brand.accentColor} onChange={(e) => set("accentColor", e.target.value)} /></label>
      <h3>Payment details</h3><BrandField label="Payment section heading" value={brand.paymentHeading} set={(v) => set("paymentHeading", v)} /><div className="brand-fields"><BrandField label="Bank name" value={brand.bankName} set={(v) => set("bankName", v)} /><BrandField label="Account number" value={brand.accountNumber} set={(v) => set("accountNumber", v)} /></div><BrandField label="Account name" value={brand.accountName} set={(v) => set("accountName", v)} />
      <h3>Contact</h3><div className="brand-fields"><BrandField label="Phone" value={brand.phone} set={(v) => set("phone", v)} /><BrandField label="Address" value={brand.address} set={(v) => set("address", v)} /></div>
      <button className="reset-brand" onClick={() => setBrand(defaultBranding)}><RotateCcw /> Restore defaults</button>
    </section><aside className="studio-panel brand-preview"><p>Preview</p><div style={{ background: brand.accentColor }}><img src={brand.logoUrl} alt="" /><h2>{brand.companyName}</h2><span>{brand.companyTagline}</span><hr /><small>{brand.phone}</small><small>{brand.address}</small></div></aside></div>
  </div>;
}
function BrandField({ label, value, set }: { label:string; value:string; set:(v:string)=>void }) { return <label className="brand-field">{label}<input value={value} onChange={(e) => set(e.target.value)} /></label>; }
