<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>RahmanData Docs</title>
    <style>
      :root { color-scheme: light dark; }
      body { font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji", "Segoe UI Emoji"; margin: 0; padding: 32px; line-height: 1.5; }
      .wrap { max-width: 900px; margin: 0 auto; }
      .card { border: 1px solid rgba(127,127,127,.25); border-radius: 12px; padding: 18px; margin: 14px 0; }
      a { color: inherit; }
      h1 { margin: 0 0 12px; font-size: 28px; }
      h2 { margin: 0 0 8px; font-size: 18px; }
      p { margin: 6px 0 0; opacity: .85; }
      .muted { opacity: .7; }
      code { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }
    </style>
  </head>
  <body>
    <div class="wrap">
      <h1>RahmanData Documentation</h1>
      <p class="muted">Choose a documentation page:</p>

	      <div class="card">
	        <h2><a href="/docs/api">RahmanData API (OpenAPI)</a></h2>
	        <p>Endpoints exposed by this app: auth, wallet, virtual account creation, utilities, ConnectData data purchase, and webhook receiver.</p>
	      </div>

	      <div class="card">
	        <h2><a href="/docs/xpouch-merchant-api">XPouch Merchant API (Upstream)</a></h2>
	        <p>Reference for XPouch merchant endpoints (includes <strong>Utilities/VAS</strong>: airtime, data, TV, electricity, education).</p>
	      </div>

	      <div class="card">
	        <h2><a href="/docs/connectdata">ConnectData API (Upstream)</a></h2>
	        <p>Reference for ConnectData endpoints used for <strong>data plans</strong> (dropdown) and <strong>data purchase</strong>.</p>
	      </div>

	      <p class="muted">Tip: the XPouch utilities endpoints are under <code>/api/merchant/v1/utilities/*</code> in the XPouch page.</p>
	    </div>
	  </body>
	</html>
