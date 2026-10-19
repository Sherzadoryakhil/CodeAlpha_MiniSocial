<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <title>Something went wrong - MiniSocial</title>
    <style>
        :root { --bg: #F7F9F8; --surface: #FFFFFF; --text: #202124; --muted: #5A6570; --border: #DDE4E1; --btn: #075E54; --on-btn: #FFFFFF; --soft: #DCF8C6; --soft-text: #064B43; --focus: #128C7E; }
        @media (prefers-color-scheme: dark) { :root { --bg: #0E1513; --surface: #16201D; --text: #E7EEEB; --muted: #9EADA8; --border: #2A3B36; --btn: #25D366; --on-btn: #04231C; --soft: #1B4A36; --soft-text: #D3F8DF; --focus: #56DCC0; } }
        * { box-sizing: border-box; }
        body { font-family: 'Inter Variable', Inter, system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif; background: var(--bg); color: var(--text); display: flex; min-height: 100vh; align-items: center; justify-content: center; margin: 0; padding: 1rem; line-height: 1.55; }
        .skip { position: absolute; left: -999px; }
        .skip:focus { left: 1rem; top: 1rem; background: var(--btn); color: var(--on-btn); padding: .75rem 1rem; border-radius: 8px; }
        .box { background: var(--surface); border: 1px solid var(--border); border-radius: 20px; padding: 2rem 1.5rem; width: 100%; max-width: 440px; text-align: center; box-shadow: 0 16px 40px rgba(7, 94, 84, .14); overflow-wrap: anywhere; }
        .icon { display: inline-grid; place-items: center; width: 76px; height: 76px; border-radius: 50%; background: var(--soft); color: var(--soft-text); font-size: 2rem; margin-bottom: 1rem; }
        h1 { font-size: 1.5rem; margin: 0 0 .5rem; letter-spacing: -.015em; }
        p { color: var(--muted); margin: 0; }
        .actions { margin-top: 1.5rem; display: flex; gap: .5rem; flex-wrap: wrap; justify-content: center; }
        a.btn { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; background: var(--btn); color: var(--on-btn); padding: .6rem 1.15rem; border-radius: 12px; font-weight: 600; text-decoration: none; }
        a.btn:hover { filter: brightness(.92); }
        a:focus-visible { outline: 3px solid var(--focus); outline-offset: 2px; }
        @media (prefers-reduced-motion: reduce) { * { transition: none !important; animation: none !important; } }
    </style>
</head>
<body>
    <a class="skip" href="#main-content">Skip to main content</a>
    <main class="box" id="main-content">
        <div class="icon" aria-hidden="true">!</div>
        <h1>500: Something went wrong</h1>
        <p>It is our fault, not yours. Please try again in a moment.</p>
        <div class="actions"><a class="btn" href="/">Go to the home page</a></div>
    </main>
</body>
</html>
