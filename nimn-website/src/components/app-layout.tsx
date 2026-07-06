import { Link, useRouterState } from "@tanstack/react-router";
import { FileClock, FilePlus2, LayoutDashboard, LogOut, Menu, Palette, X } from "lucide-react";
import { useState, type ReactNode } from "react";
import { currentUser, logout } from "@/lib/api";

const navigation = [
  { label: "Dashboard", to: "/dashboard", icon: LayoutDashboard },
  { label: "Invoices", to: "/invoices", icon: FilePlus2 },
  { label: "History", to: "/history", icon: FileClock },
  { label: "Branding", to: "/branding", icon: Palette },
];

export function AppLayout({ children }: { children: ReactNode; initialCollapsed?: boolean }) {
  const [mobileOpen, setMobileOpen] = useState(false);
  const { location } = useRouterState();
  const user = currentUser();
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
            const active = location.pathname.startsWith(to);
            return <Link key={to} to={to} onClick={() => setMobileOpen(false)} className={active ? "active" : ""}><Icon /><span>{label}</span></Link>;
          })}
        </nav>
        <div className="shell-sidebar-foot"><span>{user ? `${user.first_name[0]}${user.last_name[0]}` : "IS"}</span><div><b>{user ? `${user.first_name} ${user.last_name}` : "Invoice Studio"}</b><small>{user?.email || "Invoice workspace"}</small></div><button title="Log out" onClick={async()=>{await logout();window.location.href="/login"}}><LogOut/></button></div>
      </aside>
      <div className="shell-main">
        <header className="shell-mobile-header"><button aria-label="Open navigation" onClick={() => setMobileOpen(true)}><Menu /></button><b>Kumail Invoice Studio</b></header>
        <main>{children}</main>
      </div>
    </div>
  );
}

export function Breadcrumb() { return null; }
