/**
 * One System CAS — Node.js / Express sample app
 * -----------------------------------------------------
 * This sample proves @cas-system/node-cas-client works end-to-end AND now also
 * acts as a REAL application with its OWN local username/password accounts,
 * backed by a SQLite user store. A user can sign in EITHER way:
 *
 *   LOCAL accounts (new):
 *     (1) GET  /login    -> render a local username+password form (+ CAS button)
 *     (2) POST /login    -> validate against SQLite; on success create our OWN
 *                           session and redirect home; on failure re-render
 *                           the form with an error.
 *         POST /login ALSO serves the CAS link-validation contract: when the
 *         body contains "client_validation" (the CAS server posts
 *         {username,password,client_validation:true}) we answer with JSON
 *         {success:true} (200) / {success:false} (401) and create NO session.
 *
 *   CAS SSO (unchanged):
 *     (a) GET  /cas/login -> trigger CAS SSO login (browser redirect)
 *     (b) GET  /callback  -> handle the callback, validate the single-use token
 *                            SERVER-TO-SERVER via the package, create our session
 *     (d) GET  /logout    -> clear our session (and tell the CAS server)
 *
 *   Shared:
 *     (c) GET  /          -> show whether you are signed in (local OR CAS), who
 *                            you are, and offer logout.
 *
 * The JWT is HS256 and signed with a secret the CAS server holds. Client apps
 * must NEVER hold that secret — we always validate the token by calling the CAS
 * server's POST /api/validate-token, which the package does for us inside
 * cas.validateToken(token).
 */

require('dotenv').config();

const express = require('express');
const session = require('express-session');

// ---------------------------------------------------------------------------
// Local user store (SQLite). Opens/creates ./data/app.db and seeds two demo
// accounts on first run. See db.js.
// ---------------------------------------------------------------------------
const { initDb } = require('./db');

// ---------------------------------------------------------------------------
// Import the LOCAL package (linked via "file:../../packages/nodejs-cas-client"
// in package.json). The package's main export is the CasClient class.
// ---------------------------------------------------------------------------
const CasClient = require('@cas-system/node-cas-client');

// The middleware lives at src/middleware.js. The package has no "exports" map,
// so we reference the real file path under the package root.
const { casAuth } = require('@cas-system/node-cas-client/src/middleware');

// ---------------------------------------------------------------------------
// Configuration — everything comes from the environment (.env / .env.example).
// ---------------------------------------------------------------------------
const PORT = parseInt(process.env.PORT || '9102', 10);

const cas = new CasClient({
  serverUrl: process.env.CAS_BASE_URL,        // internal/back-channel base for server-to-server token validation (e.g. http://one-system-cas)
  publicUrl: process.env.CAS_PUBLIC_URL,      // PUBLIC, browser-facing base used to build the /sso/login redirect (falls back to serverUrl when unset)
  clientId: process.env.CAS_CLIENT_ID,        // registered client_id
  clientSecret: process.env.CAS_CLIENT_SECRET, // registered client_secret (server-side only!)
  callbackUrl: process.env.CAS_CALLBACK_URL,  // must match the registered callback_url
});

// Open the local user store (creates the file + seeds demo users if empty).
const users = initDb();

const app = express();

// Body parsers so POST /login works for BOTH a browser form (urlencoded) and
// the CAS server's link-validation call (application/json).
app.use(express.urlencoded({ extended: false }));
app.use(express.json());

// We keep our OWN application session once a user is authenticated (locally or
// via a validated CAS token). The CAS token is single-use; after validation we
// never touch it for auth again.
app.use(
  session({
    secret: process.env.SESSION_SECRET || 'change-me-in-production',
    resave: false,
    saveUninitialized: false,
  })
);

// ---------------------------------------------------------------------------
// (1) Render the local login form.
//     A small username+password form that POSTs back to /login, plus a button
//     to fall back to the CAS SSO flow.
// ---------------------------------------------------------------------------
app.get('/login', (req, res) => {
  // If already signed in, no need to log in again.
  if (req.session.user) return res.redirect('/');
  res.send(renderLogin());
});

// ---------------------------------------------------------------------------
// (2) Handle local login AND the CAS link-validation contract.
//
//   - CAS validation call: body contains "client_validation" (and/or the
//     request prefers application/json). We validate the credentials against
//     the SQLite store and respond with JSON only — NO browser session is
//     created. 200 {success:true} for valid, 401 {success:false} for invalid.
//
//   - Browser login: validate credentials; on success store the user in our
//     OWN session and redirect home; on failure re-render the form with an
//     error message.
// ---------------------------------------------------------------------------
app.post('/login', (req, res) => {
  const { username, password } = req.body || {};

  // Detect the CAS server's validation call: the presence of client_validation
  // in the body, and/or an explicit JSON Accept header with no HTML preference.
  const isValidationCall =
    Object.prototype.hasOwnProperty.call(req.body || {}, 'client_validation') ||
    (req.is('application/json') && !req.accepts('html'));

  const user = users.verifyCredentials(username, password);

  if (isValidationCall) {
    // Contract with the CAS server's ClientCredentialValidator: do NOT create a
    // session, just report validity as JSON.
    if (user) {
      return res.status(200).json({ success: true });
    }
    return res.status(401).json({ success: false });
  }

  // Browser login flow.
  if (!user) {
    return res
      .status(401)
      .send(renderLogin('Invalid username or password.', username));
  }

  // Success: establish our OWN local session (same session used for SSO).
  req.session.user = {
    id: user.id,
    username: user.username,
    auth: 'local',
  };
  res.redirect('/');
});

// ---------------------------------------------------------------------------
// (a) Trigger CAS login (CAS SSO flow — unchanged behavior).
//     cas.getLoginUrl() builds {CAS_BASE}/sso/login?client_id=...; the CAS
//     server authenticates the user and 302-redirects back to our registered
//     callback_url with ?token=<JWT>.
//
//     Reachable from the local login form's "Login with CAS SSO" button and the
//     home page. Kept at a dedicated path so GET /login can render the local
//     form without breaking the SSO redirect contract.
// ---------------------------------------------------------------------------
app.get('/cas/login', (req, res) => {
  res.redirect(cas.getLoginUrl());
});

// ---------------------------------------------------------------------------
// (b) Handle the CAS callback and validate the token SERVER-TO-SERVER.
//     cas.validateToken(token) POSTs to {CAS_BASE}/api/validate-token with the
//     client_id + client_secret and returns the user object on success, or
//     null on failure. The token is single-use, so we validate exactly once,
//     then store the resulting user in our OWN session.
// ---------------------------------------------------------------------------
app.get('/callback', async (req, res) => {
  const { token } = req.query;

  if (!token) {
    return res.status(400).send(renderError('Missing "token" query parameter.'));
  }

  const user = await cas.validateToken(token);

  if (!user) {
    // Validation failed (expired / already used / bad signature, etc.)
    return res.status(401).send(renderError('Token validation failed. Please try logging in again.'));
  }

  // Success: create our own session. We do NOT keep the JWT around for auth.
  // Tag the auth source so the home page can show "via CAS".
  req.session.user = { ...user, auth: user.auth || 'cas' };
  res.redirect('/');
});

// ---------------------------------------------------------------------------
// (c) Home — show whether you are signed in (local OR CAS), who you are, and
//     offer logout. Public page: shows a Login link when no session exists.
// ---------------------------------------------------------------------------
app.get('/', (req, res) => {
  const user = req.session.user;
  res.send(renderHome(user));
});

// ---------------------------------------------------------------------------
// Example of the package's casAuth middleware protecting a route. If there is
// no session it redirects to the login route we pass in (here: /login).
// ---------------------------------------------------------------------------
app.get(
  '/profile',
  casAuth(cas, { sessionKey: 'user', loginRoute: '/login' }),
  (req, res) => {
    res.send(renderProfile(req.casUser));
  }
);

// ---------------------------------------------------------------------------
// (d) Logout. Clear our own LOCAL session and (best-effort) notify the CAS
//     server. Works for both local and CAS sessions.
// ---------------------------------------------------------------------------
app.get('/logout', async (req, res) => {
  try {
    await cas.logout(); // optional: tells the CAS server about the logout
  } catch (_) {
    /* logout is best-effort; ignore failures */
  }
  req.session.destroy(() => res.redirect('/'));
});

app.listen(PORT, () => {
  console.log(`One System CAS Node.js sample running on http://localhost:${PORT}`);
  console.log(`CAS server: ${process.env.CAS_BASE_URL}`);
  console.log(`Callback:   ${process.env.CAS_CALLBACK_URL}`);
});

// ---------------------------------------------------------------------------
// Tiny inline HTML helpers (kept minimal so the auth flow stays the focus).
// ---------------------------------------------------------------------------
function page(body) {
  return `<!doctype html><html><head><meta charset="utf-8">
<title>One System CAS — Node.js sample</title>
<style>
  body{font-family:system-ui,sans-serif;max-width:640px;margin:64px auto;padding:0 16px;line-height:1.5}
  a.btn,button.btn{display:inline-block;padding:10px 18px;border-radius:8px;text-decoration:none;font-weight:600;border:0;font-size:1rem;cursor:pointer}
  a.login,button.login{background:#2563eb;color:#fff}
  a.cas{background:#0f766e;color:#fff}
  a.logout{background:#ef4444;color:#fff}
  pre{background:#f4f4f5;padding:16px;border-radius:8px;overflow:auto}
  .err{color:#b91c1c}
  form.card{margin-top:8px}
  form.card label{display:block;font-weight:600;margin:12px 0 4px}
  form.card input{width:100%;box-sizing:border-box;padding:10px 12px;border:1px solid #d4d4d8;border-radius:8px;font-size:1rem}
  .divider{margin:24px 0;border:none;border-top:1px solid #e4e4e7}
  .badge{display:inline-block;padding:2px 10px;border-radius:999px;font-size:.8rem;font-weight:600;background:#e0e7ff;color:#3730a3;vertical-align:middle}
  .hint{color:#71717a;font-size:.9rem}
</style></head><body>${body}</body></html>`;
}

function renderLogin(error, username) {
  const errBlock = error ? `<p class="err">${escapeHtml(error)}</p>` : '';
  return page(`
    <h1>Sign in</h1>
    <p>Use a local account, or sign in with One System CAS SSO.</p>
    ${errBlock}
    <form class="card" method="post" action="/login">
      <label for="username">Username</label>
      <input id="username" name="username" autocomplete="username"
             value="${escapeHtml(username || '')}" required autofocus>
      <label for="password">Password</label>
      <input id="password" name="password" type="password"
             autocomplete="current-password" required>
      <p><button class="btn login" type="submit">Sign in</button></p>
    </form>
    <p class="hint">Demo accounts: <code>rajan / rajan123</code> &middot; <code>demo / demo123</code></p>
    <hr class="divider">
    <p><a class="btn cas" href="/cas/login">Login with CAS SSO</a></p>`);
}

function renderHome(user) {
  if (!user) {
    return page(`
      <h1>One System CAS — Node.js sample</h1>
      <p>You are <strong>not signed in</strong>.</p>
      <p><a class="btn login" href="/login">Sign in</a></p>`);
  }
  const who = user.username || user.email || user.id;
  const via = user.auth === 'local' ? 'a local account' : 'One System CAS SSO';
  return page(`
    <h1>Welcome, ${escapeHtml(who)} <span class="badge">${escapeHtml(user.auth || 'cas')}</span></h1>
    <p>You are signed in via ${escapeHtml(via)}.</p>
    <pre>${escapeHtml(JSON.stringify(user, null, 2))}</pre>
    <p>
      <a class="btn login" href="/profile">View protected /profile</a>
      &nbsp;
      <a class="btn logout" href="/logout">Logout</a>
    </p>`);
}

function renderProfile(user) {
  return page(`
    <h1>Protected profile</h1>
    <p>This route is guarded by the package's <code>casAuth</code> middleware.</p>
    <pre>${escapeHtml(JSON.stringify(user, null, 2))}</pre>
    <p><a href="/">&larr; Home</a></p>`);
}

function renderError(message) {
  return page(`
    <h1 class="err">Authentication error</h1>
    <p class="err">${escapeHtml(message)}</p>
    <p><a class="btn login" href="/login">Try again</a></p>`);
}

function escapeHtml(str) {
  return String(str).replace(/[&<>"']/g, (c) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
  }[c]));
}
