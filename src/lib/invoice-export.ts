export async function exportInvoicePng(node: HTMLElement, filename: string) {
  await document.fonts.ready;
  const clone = node.cloneNode(true) as HTMLElement;
  clone.style.margin = "0"; clone.style.boxShadow = "none";
  const styles = Array.from(document.styleSheets).map((sheet) => { try { return Array.from(sheet.cssRules).map((rule) => rule.cssText).join("\n"); } catch { return ""; } }).join("\n");
  const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="794" height="1123"><foreignObject width="100%" height="100%"><div xmlns="http://www.w3.org/1999/xhtml"><style>${styles}</style>${clone.outerHTML}</div></foreignObject></svg>`;
  const image = new Image();
  const url = URL.createObjectURL(new Blob([svg], { type: "image/svg+xml;charset=utf-8" }));
  await new Promise<void>((resolve, reject) => { image.onload = () => resolve(); image.onerror = reject; image.src = url; });
  const canvas = document.createElement("canvas"); canvas.width = 2382; canvas.height = 3369;
  const context = canvas.getContext("2d"); if (!context) throw new Error("Canvas is unavailable");
  context.fillStyle = "#fff"; context.fillRect(0, 0, canvas.width, canvas.height); context.drawImage(image, 0, 0, canvas.width, canvas.height); URL.revokeObjectURL(url);
  const link = document.createElement("a"); link.download = filename; link.href = canvas.toDataURL("image/png", 1); link.click();
}

export function printInvoice(node: HTMLElement) {
  document.querySelectorAll(".invoice-document.print-target").forEach((element) => element.classList.remove("print-target"));
  node.classList.add("print-target"); window.print();
  window.setTimeout(() => node.classList.remove("print-target"), 1000);
}
