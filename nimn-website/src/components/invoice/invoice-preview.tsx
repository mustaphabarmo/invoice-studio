import { forwardRef } from "react";
import { MapPin, PhoneCall } from "lucide-react";
import { invoiceTotals, type InvoiceData } from "./types";

const currencySymbols: Record<string, string> = { NGN: "₦", USD: "$", EUR: "€", GBP: "£" };

function money(value: number, currency: string) {
  const safeCurrency = /^[A-Z]{3}$/.test(currency) ? currency : "NGN";
  return new Intl.NumberFormat("en-NG", {
    style: "currency",
    currency: safeCurrency,
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
  }).format(value);
}

function displayDate(value: string) {
  if (!value) return "—";
  const [year, month, day] = value.split("-");
  return `${day} / ${month} / ${year}`;
}

export const InvoicePreview = forwardRef<HTMLDivElement, { data: InvoiceData }>(
  function InvoicePreview({ data }, ref) {
    const totals = invoiceTotals(data);
    const symbol = currencySymbols[data.currency] ?? (data.currency || "₦");
    return (
        <div ref={ref} className="invoice-document" style={{ "--invoice-accent": data.accentColor } as React.CSSProperties}>
        <div className="invoice-body">
          <header className="invoice-brand">
            {data.logoUrl && <img src={data.logoUrl} alt="Company logo" />}
            <div>
              <h1>{data.companyName || "Company name"}</h1>
              <p>{data.companyTagline}</p>
            </div>
          </header>

          <section className="invoice-meta">
            <div>
              <strong>{data.clientLabel}</strong>
              <b>{data.clientName || "Client name"}</b>
              {data.clientAddress && <span>{data.clientAddress}</span>}
            </div>
            <dl>
              <dt>Invoice#</dt><dd>{data.invoiceNumber || "—"}</dd>
              <dt>Date</dt><dd>{displayDate(data.issueDate)}</dd>
            </dl>
          </section>

          <section className="invoice-items">
            <div className="invoice-table-head">
              <span>Services</span>
              <strong>Total ({symbol})</strong>
            </div>
            {data.items.map((item) => (
              <div className="invoice-line" key={item.id}>
                <div>
                  <h2>{item.title || "Untitled service"}</h2>
                  <p>{item.description.split("\n").map((line, index) => (
                    <span key={index}>{index > 0 ? "+ " : ""}{line.trim()}<br /></span>
                  ))}</p>
                  {item.quantity !== 1 && <small>{item.quantity} × {money(item.unitPrice, data.currency)}</small>}
                </div>
                <strong>{money(item.quantity * item.unitPrice, data.currency)}</strong>
              </div>
            ))}
          </section>

          <section className="invoice-summary-row">
            <div />
            <dl className="invoice-summary">
              {totals.discount > 0 && <><dt>Discount:</dt><dd>−{money(totals.discount, data.currency)}</dd></>}
              <dt>{data.taxLabel || "Tax"}:</dt><dd>{money(totals.tax, data.currency)}</dd>
              <dt className="grand">Grand<br />Total:</dt><dd className="grand">{money(totals.total, data.currency)}</dd>
            </dl>
          </section>

          <section className="invoice-payment">
            <h3>{data.paymentHeading}</h3>
            <p>{data.bankName}</p>
            <p>Account Name: {data.accountName}</p>
            <p>Account No.: {data.accountNumber}</p>
          </section>
          <p className="invoice-note">{data.notes}</p>
        </div>
        <footer className="invoice-footer">
          <span><PhoneCall /> {data.phone}</span>
          <span><MapPin /> {data.address}</span>
        </footer>
      </div>
    );
  },
);
