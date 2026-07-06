import { Link, useRouterState } from "@tanstack/react-router";
import { FileClock, FilePlus2, LayoutDashboard, Menu, Palette, X } from "lucide-react";
import { useState, type ReactNode } from "react";

const navigation = [
  { label: "Dashboard", to: "/", icon: LayoutDashboard },
  { label: "Invoices", to: "/invoices", icon: FilePlus2 },
  { label: "History", to: "/history", icon: FileClock },
  { label: "Branding", to: "/branding", icon: Palette },
];

export function AppLayout({ children }: { children: ReactNode; initialCollapsed?: boolean }) {
  const [mobileOpen, setMobileOpen] = useState(false);
  const { location } = useRouterState();
  return (
    <div className="kumail-shell">
      {mobileOpen && <button className="shell-overlay" aria-label="Close navigation" onClick={() => setMobileOpen(false)} />}
      <aside className={`shell-sidebar ${mobileOpen ? "open" : ""}`}>
        <div className="shell-brand">
          <img src="/kumail-icon.png" alt="Kumail Innovations" />
          <div><b>Kumail</b><span>Invoice Studio</span></div>
          <button aria-label="Close navigation" onClick={() => setMobileOpen(false)}><X /></button>
        </div>
        <nav aria-label="Main navigation">
          <p>Workspace</p>
          {navigation.map(({ label, to, icon: Icon }) => {
            const active = to === "/" ? location.pathname === "/" : location.pathname.startsWith(to);
            return <Link key={to} to={to} onClick={() => setMobileOpen(false)} className={active ? "active" : ""}><Icon /><span>{label}</span></Link>;
          })}
        </nav>
        <div className="shell-sidebar-foot"><span>KI</span><div><b>Kumail Innovations</b><small>Invoice workspace</small></div></div>
      </aside>
      <div className="shell-main">
        <header className="shell-mobile-header"><button aria-label="Open navigation" onClick={() => setMobileOpen(true)}><Menu /></button><b>Kumail Invoice Studio</b></header>
        <main>{children}</main>
      </div>
    </div>
  );
}

export function Breadcrumb() { return null; }
