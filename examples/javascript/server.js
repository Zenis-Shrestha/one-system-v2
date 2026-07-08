/**
 * One System CAS — JavaScript sample backend (tiny Express server).
 *
 * TWO-PART DESIGN
 * ---------------
 * 1. BROWSER (public/*.html): a static page loads the UMD `CasClient` browser SDK
 *    (served at /vendor/cas-client.js from the LOCAL package) and uses it to start
 *    login and to extract the token from the callback URL.
 * 2. BACKEND (this file): exposes POST /api/auth/validate. The browser SDK POSTs the
 *    extracted token here; this server adds the client_id + client_secret (which must
 *    NEVER live in browser code) and validates the token SERVER-TO-SERVER against the
 *    CAS server's POST {CAS_BASE}/api/validate-token. On success it creates the app's
 *    OWN session cookie and returns the user JSON back to the browser.
 *
 * The JWT is single-use: we validate it exactly once, then rely on our own session.
 */

'use strict';

require('dotenv').config();

const path = require('path');
const crypto = require('crypto');
const express = require('express');

// Local username/password store (SQLite). This is what makes the sample a REAL
// app with its OWN accounts, in addition to the CAS Single-Sign-On flow.
const { authenticate } = require('./db');

// --- Config from environment (see .env.example) ---
// INTERNAL/back-channel base: used for SERVER-TO-SERVER token validation. In a
// split-horizon (Docker) deployment this is the internal CAS url (e.g.
// http://one-system-cas) that the container can reach.
const CAS_BASE_URL = (process.env.CAS_BASE_URL || 'http://localhost:8080').replace(/\/$/, '');
// PUBLIC/browser-facing base: used to build the {CAS}/sso/login redirect the
// user's BROWSER is sent to. Falls back to CAS_BASE_URL when unset so local
// single-url dev keeps working unchanged.
const CAS_PUBLIC_URL = (process.env.CAS_PUBLIC_URL || CAS_BASE_URL).replace(/\/$/, '');
const CAS_CLIENT_ID = process.env.CAS_CLIENT_ID || 'javascript-sample';
const CAS_CLIENT_SECRET = process.env.CAS_CLIENT_SECRET || '';
const CAS_CALLBACK_URL = process.env.CAS_CALLBACK_URL || 'http://localhost:9103/cas/callback';
const PORT = parseInt(process.env.PORT || '9103', 10);

if (!CAS_CLIENT_SECRET) {
  console.warn('[CAS] WARNING: CAS_CLIENT_SECRET is empty. Set it in .env before validating tokens.');
}

const app = express();
app.use(express.json());
// Parse HTML form posts too (the browser <form> on /login submits urlencoded).
app.use(express.urlencoded({ extended: false }));

// --- Minimal in-memory session store (sample only; use a real store in production) ---
// Maps an opaque session id (stored in an httpOnly cookie) -> the user object.
// The SAME store backs both CAS-SSO sessions and local username/password logins.
const sessions = new Map();

/**
 * Create the app's OWN session for a user and set the `sid` cookie on the
 * response. Used by both the CAS validation flow and the local /login flow, so
 * the rest of the app (/api/me, /api/logout) treats them identically.
 */
function createSession(res, user) {
  const sid = crypto.randomBytes(24).toString('hex');
  sessions.set(sid, user);
  res.setHeader(
    'Set-Cookie',
    `sid=${sid}; HttpOnly; Path=/; SameSite=Lax; Max-Age=3600`
  );
  return sid;
}

function parseCookies(req) {
  const header = req.headers.cookie || '';
  const out = {};
  header.split(';').forEach((part) => {
    const idx = part.indexOf('=');
    if (idx > -1) out[part.slice(0, idx).trim()] = decodeURIComponent(part.slice(idx + 1).trim());
  });
  return out;
}

function getSessionUser(req) {
  const sid = parseCookies(req).sid;
  return sid && sessions.has(sid) ? sessions.get(sid) : null;
}

/**
 * Expose the LOCAL browser SDK file to the page via a <script src>.
 * `require.resolve` points at the package's "main"/"browser" entry, so the sample
 * always serves the exact UMD file from the linked local package — no copy/vendor.
 */
const sdkPath = require.resolve('@cas-system/js-cas-client');
app.get('/vendor/cas-client.js', (_req, res) => {
  res.type('application/javascript').sendFile(sdkPath);
});

/**
 * Expose only the PUBLIC (browser-safe) config so the static page can build the
 * CasClient. Notice: client_secret is intentionally NOT included here.
 */
app.get('/api/config', (_req, res) => {
  res.json({
    // Ship the PUBLIC base url as the browser SDK's serverUrl so the in-browser
    // getLoginUrl() builds {CAS_PUBLIC}/sso/login against a host the browser can
    // actually reach. Server-to-server validation still uses CAS_BASE_URL below.
    serverUrl: CAS_PUBLIC_URL,
    clientId: CAS_CLIENT_ID,
    callbackUrl: CAS_CALLBACK_URL,
    backendValidateUrl: '/api/auth/validate',
  });
});

/**
 * POST /api/auth/validate  — called by the browser SDK (validateTokenViaBackend).
 * Body: { "token": "<jwt>" }
 *
 * This is the SERVER-TO-SERVER step. We attach client_id + client_secret and ask the
 * CAS server to validate the (single-use) token, then mint our own app session.
 */
app.post('/api/auth/validate', async (req, res) => {
  const token = req.body && req.body.token;
  if (!token) return res.status(400).json({ error: 'Missing token' });

  try {
    const casResp = await fetch(`${CAS_BASE_URL}/api/validate-token`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        token,
        client_id: CAS_CLIENT_ID,
        client_secret: CAS_CLIENT_SECRET,
      }),
    });

    const data = await casResp.json().catch(() => ({}));

    if (!casResp.ok || !data.valid) {
      // CAS returns 401 { error } on failure.
      return res.status(401).json({ error: data.error || 'Token validation failed' });
    }

    // Token is valid + single-use is now consumed. Create OUR OWN session.
    createSession(res, data.user);

    // Return the user to the browser SDK (it stores it for UI rendering).
    return res.json({ user: data.user, expires_at: data.expires_at });
  } catch (err) {
    console.error('[CAS] Server-to-server validation error:', err.message);
    return res.status(502).json({ error: 'Could not reach CAS server' });
  }
});

/**
 * GET /api/me — lets the page confirm the app session on reload (server-side truth).
 */
app.get('/api/me', (req, res) => {
  const user = getSessionUser(req);
  if (!user) return res.status(401).json({ error: 'Not authenticated' });
  res.json({ user });
});

/**
 * POST /api/logout — clear OUR session. (CAS server logout is optional and handled
 * by the browser SDK's logout(), which calls {CAS_BASE}/api/logout.)
 */
app.post('/api/logout', (req, res) => {
  const sid = parseCookies(req).sid;
  if (sid) sessions.delete(sid);
  res.setHeader('Set-Cookie', 'sid=; HttpOnly; Path=/; Max-Age=0');
  res.json({ ok: true });
});

/**
 * Render the small local login form (server-rendered HTML, no build step).
 * `error` shows a red message; `username` pre-fills the field after a failure.
 */
function renderLoginPage({ error = '', username = '' } = {}) {
  const esc = (s) =>
    String(s).replace(/[&<>"']/g, (c) =>
      ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c])
    );
  const errorHtml = error
    ? `<p class="error" role="alert">${esc(error)}</p>`
    : '';
  return `<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Sign in — One System CAS Sample</title>
  <style>
    body { font-family: system-ui, -apple-system, sans-serif; max-width: 360px; margin: 4rem auto; padding: 0 1rem; color: #1a1a1a; }
    h1 { font-size: 1.3rem; margin-bottom: 0.25rem; }
    .muted { color: #6b7280; font-size: 0.9rem; margin-top: 0; }
    form { margin-top: 1.5rem; display: flex; flex-direction: column; gap: 0.85rem; }
    label { font-size: 0.85rem; font-weight: 600; }
    input { font-size: 1rem; padding: 0.55rem 0.7rem; border: 1px solid #d1d5db; border-radius: 6px; }
    input:focus { outline: 2px solid #2563eb; border-color: #2563eb; }
    button { font-size: 1rem; padding: 0.6rem 1.2rem; border: 0; border-radius: 6px; cursor: pointer; background: #2563eb; color: #fff; }
    button:hover { background: #1d4ed8; }
    .error { color: #b91c1c; background: #fef2f2; border: 1px solid #fecaca; padding: 0.6rem 0.75rem; border-radius: 6px; font-size: 0.9rem; }
    .alt { margin-top: 1.5rem; font-size: 0.9rem; }
    .hint { margin-top: 1.5rem; font-size: 0.8rem; color: #6b7280; }
    a { color: #2563eb; }
  </style>
</head>
<body>
  <h1>Sign in</h1>
  <p class="muted">Use a local account, or sign in with One System CAS.</p>
  ${errorHtml}
  <form method="post" action="/login">
    <div>
      <label for="username">Username</label><br />
      <input id="username" name="username" type="text" autocomplete="username"
             value="${esc(username)}" autofocus required style="width:100%" />
    </div>
    <div>
      <label for="password">Password</label><br />
      <input id="password" name="password" type="password" autocomplete="current-password"
             required style="width:100%" />
    </div>
    <button type="submit">Sign in</button>
  </form>
  <p class="alt">Or <a href="/?sso=1">sign in with One System CAS (SSO)</a>.</p>
  <p class="hint">Demo accounts: <code>rajan / rajan123</code>, <code>demo / demo123</code></p>
</body>
</html>`;
}

/**
 * GET /login — show the local username/password form.
 * If already signed in (locally or via CAS), go straight home.
 */
app.get('/login', (req, res) => {
  if (getSessionUser(req)) return res.redirect('/');
  res.type('html').send(renderLoginPage());
});

/**
 * POST /login — serves TWO contracts on one route:
 *
 *  1. BROWSER login: a urlencoded form post ({ username, password }). On success
 *     we create the app's OWN local session (the same `sid` cookie used by CAS)
 *     and redirect home; on failure we re-render the form with an error.
 *
 *  2. CAS LINK-VALIDATION: the CAS server posts JSON/form
 *     { username, password, client_validation: true } to verify that this app
 *     recognizes the account. We respond 200 { success: true } for valid creds
 *     or 401 { success: false } for invalid — and we do NOT create a browser
 *     session for this machine-to-machine call.
 *
 * The validation call is detected by the presence of `client_validation`
 * (and/or an `Accept: application/json` request).
 */
app.post('/login', (req, res) => {
  const body = req.body || {};
  const username = body.username;
  const password = body.password;

  const wantsJson = (req.headers.accept || '').includes('application/json');
  const isValidationCall =
    body.client_validation === true ||
    body.client_validation === 'true' ||
    body.client_validation === 1 ||
    body.client_validation === '1';

  const user = authenticate(username, password);

  // --- (2) CAS link-validation contract: no session, JSON only. ---
  if (isValidationCall) {
    if (user) return res.status(200).json({ success: true });
    return res.status(401).json({ success: false });
  }

  // --- (1) Browser login. ---
  if (!user) {
    // An XHR/fetch caller (Accept: application/json) gets JSON; a form gets HTML.
    if (wantsJson) return res.status(401).json({ error: 'Invalid username or password' });
    return res
      .status(401)
      .type('html')
      .send(renderLoginPage({ error: 'Invalid username or password', username }));
  }

  // Success: establish the app's OWN local session (same session CAS uses).
  createSession(res, user);
  if (wantsJson) return res.json({ user });
  return res.redirect('/');
});

// --- Static front-end (index.html, callback.html) ---
app.use(express.static(path.join(__dirname, 'public')));

// The CAS server redirects to /cas/callback?token=...; serve the callback page there.
app.get('/cas/callback', (_req, res) => {
  res.sendFile(path.join(__dirname, 'public', 'callback.html'));
});

app.listen(PORT, () => {
  console.log(`One System CAS JavaScript sample running at http://localhost:${PORT}`);
  console.log(`  CAS (internal/validate) : ${CAS_BASE_URL}`);
  console.log(`  CAS (public/login)      : ${CAS_PUBLIC_URL}`);
  console.log(`  client_id               : ${CAS_CLIENT_ID}`);
  console.log(`  callback_url            : ${CAS_CALLBACK_URL}`);
});
