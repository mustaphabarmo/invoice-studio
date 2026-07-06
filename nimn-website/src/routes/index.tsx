import { createFileRoute, Link } from "@tanstack/react-router";
import { ArrowRight, Check, FileText, Palette, ShieldCheck, Sparkles } from "lucide-react";

export const Route = createFileRoute("/")({ component: Landing });

function Landing() {
  return <div className="landing">
    <nav className="public-nav"><Link to="/" className="public-logo"><span><FileText /></span>Invoice Studio</Link><div><a href="#features">Features</a><Link to="/login">Log in</Link><Link to="/register" className="nav-cta">Start free</Link></div></nav>
    <main>
      <section className="hero"><div className="hero-copy"><div className="eyebrow"><Sparkles /> Invoicing, made beautifully simple</div><h1>Professional invoices.<br/><em>Without the busywork.</em></h1><p>Create polished, on-brand invoices in minutes. Keep every client, payment detail, and document organized in one focused workspace.</p><div className="hero-actions"><Link to="/register">Create your first invoice <ArrowRight /></Link><a href="#features">See how it works</a></div><small><Check /> No credit card required <Check /> Set up in under two minutes</small></div>
      <div className="hero-card"><header><span><i/><i/><i/></span><b>INVOICE</b></header><div className="mock-brand"><span>IS</span><div><b>Your Studio</b><small>Thoughtful work, clearly billed.</small></div></div><div className="mock-meta"><div><small>INVOICE TO</small><b>Northstar & Co.</b><span>Creative services</span></div><div><small>INVOICE NUMBER</small><b>INV-0042</b><span>Due in 14 days</span></div></div><div className="mock-line"><span>Brand identity & direction</span><b>₦480,000</b></div><div className="mock-line"><span>Website design</span><b>₦320,000</b></div><div className="mock-total"><span>Total</span><strong>₦800,000</strong></div></div></section>
      <section id="features" className="feature-section"><p>Everything you need</p><h2>A calmer way to run your invoicing</h2><div><Feature icon={<FileText/>} title="Create with confidence" text="A guided editor turns your details into a clean, professional invoice."/><Feature icon={<Palette/>} title="Make it unmistakably yours" text="Save your logo, colors, contact, and payment details once."/><Feature icon={<ShieldCheck/>} title="Your work, safely organized" text="Every invoice is stored securely and available from any device."/></div></section>
      <section className="landing-cta"><h2>Ready to make invoicing feel effortless?</h2><p>Join Invoice Studio and send your next polished invoice today.</p><Link to="/register">Get started free <ArrowRight /></Link></section>
    </main><footer><span>© 2026 Invoice Studio</span><span>Simple tools for serious work.</span></footer>
  </div>;
}
function Feature({icon,title,text}:{icon:React.ReactNode;title:string;text:string}) { return <article><span>{icon}</span><h3>{title}</h3><p>{text}</p></article> }
