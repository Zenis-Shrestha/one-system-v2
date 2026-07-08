// -----------------------------------------------------------------------------
// Tiny Express backend for the Vue CAS sample.
//
// WHY THIS EXISTS
// ---------------
// The @cas-system/vue-cas-client SDK NEVER validates the JWT in the browser,
// because validation requires the CAS `client_secret` and the token is
// single-use. Instead, the browser SDK (CasClient.validateTokenViaBackend)
// POSTs `{ token }` to a backend endpoint. THIS server is that backend:
//
//   browser  --POST { token }-->  /api/auth/validate   (this server)
//   this server --POST { token, client_id, client_secret }--> CAS server
//   CAS server --200 { valid, user, expires_at }--> this server
//   this server --200 { user }--> browser
//
// The client_secret stays server-side and is never exposed to the browser.
//
// This single process also serves the Vue front-end:
//   - in development it mounts Vite as connect middleware (HMR included);
//   - in production it serves the built `dist/` folder.
// Everything runs on ONE origin/port so there is no CORS and the sample
// drops cleanly into a unified single-server deployment.
// -----------------------------------------------------------------------------

import { fileURLToPath } from 'node:url';
import { dirname, resolve } from 'node:path';
import express from 'express';
import session from 'express-session';
import dotenv from 'dotenv';

import { authenticateLocalUser } from './db.js';

dotenv.config();

const __dirname = dirname(fileURLToPath(import.meta.url));
const projectRoot = resolve(__dirname, '..');

// --- Configuration (all via environment, see .env.example) -------------------
const PORT = Number(process.env.PORT || process.env.APP_PORT || 9109);
// INTERNAL back-channel base: used for SERVER-TO-SERVER token validation
// (this server -> CAS). In a split-horizon deployment this is the address the
// app container can reach (e.g. the internal docker name), which the browser
// generally cannot resolve.
const CAS_BASE_URL = (process.env.CAS_BASE_URL || 'http://localhost:8080').replace(/\/+$/, '');
// PUBLIC browser-facing base: used to build the in-browser CAS login redirect
// ({CAS_PUBLIC_URL}/sso/login). The browser must reach CAS at this PUBLIC
// address, which differs from CAS_BASE_URL in a split-horizon deployment.
// Falls back to the internal base so single-url local dev keeps working.
const CAS_PUBLIC_URL = (process.env.CAS_PUBLIC_URL || CAS_BASE_URL).replace(/\/+$/, '');
const CAS_CLIENT_ID = process.env.CAS_CLIENT_ID || 'vue-sample-app';
const CAS_CLIENT_SECRET = process.env.CAS_CLIENT_SECRET || '';
const IS_PROD = process.env.NODE_ENV === 'production';
const SESSION_SECRET =
  process.env.SESSION_SECRET || 'dev-only-insecure-session-secret-change-me';

if (!CAS_CLIENT_SECRET) {
  console.warn(
    '[server] WARNING: CAS_CLIENT_SECRET is not set. Token validation will fail. ' +
      'Copy .env.example to .env and fill in the registered client credentials.',
  );
}

const app = express();
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// -----------------------------------------------------------------------------
// The app's OWN local session. This is the SAME session abstraction the app
// uses for its authenticated state: a browser login (POST /login) populates
// `req.session.user`, and the front-end can read it back via /api/auth/me.
// (The CAS browser SDK additionally mirrors its user into sessionStorage; the
// local login does the same client-side so the reactive UI is identical for
// both auth paths.)
// -----------------------------------------------------------------------------
app.use(
  session({
    name: 'app.sid',
    secret: SESSION_SECRET,
    resave: false,
    saveUninitialized: false,
    cookie: {
      httpOnly: true,
      sameSite: 'lax',
      secure: IS_PROD,
      maxAge: 1000 * 60 * 60 * 8, // 8 hours
    },
  }),
);

// -----------------------------------------------------------------------------
// SERVER-TO-SERVER token validation endpoint.
//
// The browser SDK calls this with `{ token }`. We add the client_id +
// client_secret and forward to the authoritative CAS endpoint:
//   POST {CAS_BASE_URL}/api/validate-token
// The CAS token is SINGLE-USE, so we validate it exactly once here and then
// hand the user back to the browser, which creates its own (sessionStorage)
// session via the SDK.
// -----------------------------------------------------------------------------
app.post('/api/auth/validate', async (req, res) => {
  const { token } = req.body || {};

  if (!token) {
    return res.status(400).json({ error: 'Missing "token" in request body.' });
  }

  try {
    const casResponse = await fetch(`${CAS_BASE_URL}/api/validate-token`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        token,
        client_id: CAS_CLIENT_ID,
        client_secret: CAS_CLIENT_SECRET,
      }),
    });

    // On failure the CAS server returns 401 { error }.
    if (!casResponse.ok) {
      const detail = await casResponse.json().catch(() => ({}));
      return res
        .status(casResponse.status === 401 ? 401 : 502)
        .json({ error: detail.error || 'Token validation failed.' });
    }

    // On success: { valid: true, user: { id, username, email }, expires_at }.
    const data = await casResponse.json();

    if (!data.valid || !data.user) {
      return res.status(401).json({ error: 'Token is not valid.' });
    }

    // Return the shape the SDK expects: `{ user }`.
    return res.json({ user: data.user, expires_at: data.expires_at });
  } catch (err) {
    console.error('[server] CAS validation error:', err);
    return res.status(502).json({ error: 'Unable to reach the CAS server.' });
  }
});

// -----------------------------------------------------------------------------
// Surface the CAS public config to the front-end so the SPA does not have to
// hardcode the CAS server URL / client id (the SECRET is never sent).
//
// SPLIT-HORIZON: the `serverUrl` shipped here is the PUBLIC (browser-facing)
// CAS base, because the browser SDK uses it to build the /sso/login redirect —
// the browser must reach CAS at its public host. Server-to-server validation
// (above) keeps using the INTERNAL CAS_BASE_URL and is unaffected.
// -----------------------------------------------------------------------------
app.get('/api/auth/config', (_req, res) => {
  res.json({ serverUrl: CAS_PUBLIC_URL, clientId: CAS_CLIENT_ID });
});

// -----------------------------------------------------------------------------
// LOCAL USERNAME/PASSWORD AUTH (the app's own accounts, in SQLite).
//
// POST /login serves TWO distinct contracts on the SAME route:
//
//   (A) BROWSER LOGIN — a person submits the login form. We validate against
//       the SQLite store and, on success, establish the app's OWN local
//       session (req.session.user) and return { success, user }. The SPA then
//       redirects to the dashboard.
//
//   (B) CAS LINK-VALIDATION — the CAS server POSTs
//       { username, password, client_validation: true } to verify that a CAS
//       identity maps to a real local account. For this call we DO NOT create
//       a browser session; we just answer 200 { success: true } for valid
//       credentials or 401 { success: false } for invalid ones.
//
// We detect the validation call by the presence of the `client_validation`
// field (and treat an `Accept: application/json` request as an API caller too).
// -----------------------------------------------------------------------------
app.post('/login', (req, res) => {
  const body = req.body || {};
  const { username, password } = body;

  const isValidationCall =
    body.client_validation === true ||
    body.client_validation === 'true' ||
    body.client_validation === 1 ||
    body.client_validation === '1';

  const wantsJson =
    isValidationCall ||
    (req.get('accept') || '').includes('application/json') ||
    req.is('application/json');

  const user = authenticateLocalUser(username, password);

  // --- (B) CAS link-validation contract: NO session is created. -----------
  if (isValidationCall) {
    if (user) {
      return res.status(200).json({ success: true });
    }
    return res.status(401).json({ success: false });
  }

  // --- (A) Browser login. -------------------------------------------------
  if (!user) {
    if (wantsJson) {
      return res
        .status(401)
        .json({ success: false, error: 'Invalid username or password.' });
    }
    // Non-JSON fallback (no JS / direct form post): re-render with an error.
    return res
      .status(401)
      .send(renderLoginError('Invalid username or password.'));
  }

  // Establish the app's own local session.
  req.session.user = user;

  if (wantsJson) {
    return res.json({ success: true, user });
  }
  // Non-JS fallback: redirect to the SPA home/dashboard.
  return res.redirect('/dashboard');
});

// Who is signed in via the LOCAL session (used by the SPA to hydrate state).
app.get('/api/auth/me', (req, res) => {
  if (req.session?.user) {
    return res.json({ authenticated: true, user: req.session.user });
  }
  return res.json({ authenticated: false, user: null });
});

// Clear the LOCAL session (logout). CAS sessions live in the browser SDK and
// are cleared separately by the SDK's logout().
app.post('/api/auth/local-logout', (req, res) => {
  if (req.session) {
    req.session.destroy(() => {
      res.clearCookie('app.sid');
      res.json({ success: true });
    });
    return;
  }
  res.json({ success: true });
});

// Tiny server-rendered error page for the no-JavaScript form fallback.
function renderLoginError(message) {
  return `<!doctype html><html><head><meta charset="utf-8"><title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
      body{font-family:system-ui,sans-serif;background:#f5f7fa;color:#1f2933;
        display:flex;min-height:100vh;align-items:center;justify-content:center;margin:0}
      .box{background:#fff;border:1px solid #e4e7eb;border-radius:10px;padding:24px;max-width:340px}
      .err{color:#b91c1c;margin:0 0 12px}
      a{color:#2563eb}
    </style></head><body><div class="box">
      <p class="err">${message}</p>
      <p><a href="/login">Back to login</a></p>
    </div></body></html>`;
}

// -----------------------------------------------------------------------------
// Front-end: Vite middleware in dev, static dist/ in prod.
// -----------------------------------------------------------------------------
async function start() {
  if (IS_PROD) {
    // Serve the production build.
    const distDir = resolve(projectRoot, 'dist');
    app.use(express.static(distDir));
    // SPA fallback: send index.html for any non-API route.
    app.get(/^(?!\/api\/).*/, (_req, res) => {
      res.sendFile(resolve(distDir, 'index.html'));
    });
  } else {
    // Dev: create a Vite server in middleware mode for HMR + on-the-fly
    // transpilation of the local TS/Vue package source.
    const { createServer: createViteServer } = await import('vite');
    const vite = await createViteServer({
      root: projectRoot,
      server: { middlewareMode: true },
      appType: 'spa',
    });
    app.use(vite.middlewares);
  }

  app.listen(PORT, () => {
    console.log(`\n  Vue CAS sample running:  http://localhost:${PORT}`);
    console.log(`  CAS (internal/validate): ${CAS_BASE_URL}`);
    console.log(`  CAS (public/login):      ${CAS_PUBLIC_URL}`);
    console.log(`  client_id:               ${CAS_CLIENT_ID}`);
    console.log(`  validate endpoint:       POST /api/auth/validate -> ${CAS_BASE_URL}/api/validate-token`);
    console.log(`  local login:             GET/POST /login  (SQLite users; demo: rajan/rajan123, demo/demo123)\n`);
  });
}

start();
