// ---------------------------------------------------------------------------
// Express backend for the React CAS sample.
//
// This sample supports TWO independent ways to sign in:
//
//   A) CAS Single-Sign-On (unchanged) -- the @cas-system/react-cas-client SDK
//      redirects to {CAS}/sso/login, the CAS server redirects back with a
//      single-use ?token=, and the browser SDK POSTs that token to
//      POST /api/auth/validate, where THIS backend performs the real
//      server-to-server validation using the client_secret. That CAS session
//      lives in the browser (sessionStorage, managed by the SDK).
//
//   B) Local username/password accounts (NEW) -- this app now has its OWN local
//      user store (SQLite, ./data/app.db) and its OWN server-side session
//      (express-session, cookie-based). GET /login renders a form; POST /login
//      validates credentials and establishes the local session.
//
//   The SAME POST /login route also serves the CAS server's "link validation"
//   contract: when the body contains `client_validation: true` (the CAS server
//   posts {username, password, client_validation:true}), we return JSON
//   {success:true|false} WITHOUT creating any browser session -- it is a pure
//   credential check, not an interactive login.
//
// Jobs of this server:
//   1. GET  /api/config         -> non-secret CAS config for the browser SDK.
//   2. POST /api/auth/validate  -> proxy a single-use CAS token to the CAS server.
//   3. GET  /login              -> render the local login form (HTML).
//   4. POST /login              -> local login (browser session) OR CAS link
//                                  validation (JSON {success}) when
//                                  client_validation is present.
//   5. POST /logout             -> clear the local session.
//   6. GET  /api/me             -> report the locally-signed-in user (if any).
//   7. (production) serve the built SPA from ./dist.
// ---------------------------------------------------------------------------

import express from 'express';
import session from 'express-session';
import path from 'node:path';
import crypto from 'node:crypto';
import fs from 'node:fs';
import { fileURLToPath } from 'node:url';
import Database from 'better-sqlite3';
import 'dotenv/config';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

const {
  CAS_BASE_URL = 'http://localhost:8080',
  // PUBLIC, browser-facing CAS base used to build the /sso/login redirect.
  // In split-horizon deploys the browser must reach CAS at a PUBLIC url while
  // this backend reaches it at an INTERNAL url (CAS_BASE_URL). Falls back to
  // CAS_BASE_URL when unset, so single-url local dev keeps working unchanged.
  CAS_PUBLIC_URL = '',
  CAS_CLIENT_ID = 'react-sample',
  CAS_CLIENT_SECRET = '',
  CAS_CALLBACK_URL = '',
  PORT = '9107',
  SESSION_SECRET = 'one-system-react-sample-dev-secret',
  // Where the SQLite file lives. Override with DB_PATH; defaults to ./data/app.db.
  DB_PATH = path.join(__dirname, 'data', 'app.db'),
} = process.env;

// Strip any trailing slash so we can safely append paths.
// casBase     : INTERNAL back-channel base, used for server-to-server token
//               validation (POST {casBase}/api/validate-token).
// casPublicBase: PUBLIC base shipped to the browser so the SDK builds the
//               /sso/login redirect against a host the browser can reach.
//               Falls back to the internal base when CAS_PUBLIC_URL is unset.
const casBase = CAS_BASE_URL.replace(/\/+$/, '');
const casPublicBase = (CAS_PUBLIC_URL || CAS_BASE_URL).replace(/\/+$/, '');

// ---------------------------------------------------------------------------
// Local user store (SQLite via better-sqlite3).
//
//   users(id, username UNIQUE, password_hash)
//
// Passwords are stored as a salted scrypt hash in the form "salt:derivedKey"
// (both hex). scrypt ships with Node's stdlib, so no extra native hashing
// dependency is needed. On first startup (empty table) we seed two demo users.
// ---------------------------------------------------------------------------

// Ensure the directory for the SQLite file exists and is writable at runtime.
fs.mkdirSync(path.dirname(DB_PATH), { recursive: true });

const db = new Database(DB_PATH);
db.pragma('journal_mode = WAL');
db.exec(`
  CREATE TABLE IF NOT EXISTS users (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    username      TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL
  );
`);

/** Hash a plaintext password as "salt:derivedKey" (hex), using scrypt. */
function hashPassword(plain) {
  const salt = crypto.randomBytes(16).toString('hex');
  const derived = crypto.scryptSync(plain, salt, 64).toString('hex');
  return `${salt}:${derived}`;
}

/** Constant-time verify a plaintext password against a stored "salt:key" hash. */
function verifyPassword(plain, stored) {
  const [salt, keyHex] = String(stored).split(':');
  if (!salt || !keyHex) return false;
  const keyBuf = Buffer.from(keyHex, 'hex');
  const derived = crypto.scryptSync(plain, salt, keyBuf.length);
  return crypto.timingSafeEqual(keyBuf, derived);
}

// Seed two demo users if the table is empty.
const DEMO_USERS = [
  { username: 'rajan', password: 'rajan123' },
  { username: 'demo', password: 'demo123' },
];

const userCount = db.prepare('SELECT COUNT(*) AS n FROM users').get().n;
if (userCount === 0) {
  const insert = db.prepare(
    'INSERT INTO users (username, password_hash) VALUES (?, ?)',
  );
  const seed = db.transaction((users) => {
    for (const u of users) insert.run(u.username, hashPassword(u.password));
  });
  seed(DEMO_USERS);
  console.log(
    `  Seeded ${DEMO_USERS.length} demo users: ` +
      DEMO_USERS.map((u) => u.username).join(', '),
  );
}

const findUserByUsername = db.prepare(
  'SELECT id, username, password_hash FROM users WHERE username = ?',
);

/** Validate credentials against the SQLite store. Returns the user row or null. */
function authenticate(username, password) {
  if (!username || !password) return null;
  const row = findUserByUsername.get(String(username));
  if (!row) return null;
  if (!verifyPassword(String(password), row.password_hash)) return null;
  return { id: row.id, username: row.username };
}

// ---------------------------------------------------------------------------
// App + middleware.
// ---------------------------------------------------------------------------
const app = express();
app.use(express.json());
// Accept the classic HTML form encoding too, so POST /login works both from the
// browser form and from JSON callers (e.g. the CAS server's validation call).
app.use(express.urlencoded({ extended: true }));

// The app's OWN local session (cookie-based, server-side). This is the session
// established by local username/password login (distinct from the CAS browser
// session, which the SDK keeps in sessionStorage).
app.use(
  session({
    name: 'os.sid',
    secret: SESSION_SECRET,
    resave: false,
    saveUninitialized: false,
    cookie: {
      httpOnly: true,
      sameSite: 'lax',
      // secure:true would require HTTPS; left off so the sample works over http.
      maxAge: 1000 * 60 * 60 * 8, // 8 hours
    },
  }),
);

// ---------------------------------------------------------------------------
// 1. Public, NON-secret config for the front-end.
// ---------------------------------------------------------------------------
app.get('/api/config', (_req, res) => {
  res.json({
    // The browser SDK builds the {serverUrl}/sso/login redirect from this, so
    // ship the PUBLIC base (CAS_PUBLIC_URL) -- the host the user's browser can
    // actually reach. Server-to-server validation still uses the INTERNAL base
    // (casBase) in POST /api/auth/validate below.
    serverUrl: casPublicBase,
    clientId: CAS_CLIENT_ID,
    callbackUrl: CAS_CALLBACK_URL || undefined,
    backendValidateUrl: '/api/auth/validate',
  });
});

// ---------------------------------------------------------------------------
// 2. Server-to-server CAS token validation (UNCHANGED).
// ---------------------------------------------------------------------------
app.post('/api/auth/validate', async (req, res) => {
  const { token } = req.body ?? {};

  if (!token) {
    return res.status(400).json({ error: 'Missing "token" in request body.' });
  }

  try {
    const casRes = await fetch(`${casBase}/api/validate-token`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        token,
        client_id: CAS_CLIENT_ID,
        client_secret: CAS_CLIENT_SECRET,
      }),
    });

    if (!casRes.ok) {
      const body = await casRes.json().catch(() => ({}));
      return res
        .status(casRes.status)
        .json({ error: body.error || 'Token validation failed.' });
    }

    const data = await casRes.json();
    if (!data.valid || !data.user) {
      return res.status(401).json({ error: 'Token reported as invalid.' });
    }

    return res.json(data.user);
  } catch (err) {
    console.error('[validate] CAS server unreachable:', err);
    return res
      .status(502)
      .json({ error: 'Could not reach the CAS server for validation.' });
  }
});

// ---------------------------------------------------------------------------
// 3. GET /login -- render the local login form.
// ---------------------------------------------------------------------------
function loginPage({ error = '', username = '' } = {}) {
  const esc = (s) =>
    String(s).replace(
      /[&<>"']/g,
      (c) =>
        ({
          '&': '&amp;',
          '<': '&lt;',
          '>': '&gt;',
          '"': '&quot;',
          "'": '&#39;',
        })[c],
    );

  return `<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Sign in · One System</title>
  <style>
    :root { color-scheme: light dark; font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; }
    body {
      margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
      background: #0f172a; color: #e2e8f0;
    }
    .card {
      width: 100%; max-width: 360px; margin: 1rem; padding: 2rem;
      background: #1e293b; border: 1px solid #334155; border-radius: 12px;
    }
    h1 { margin: 0 0 0.25rem; font-size: 1.3rem; }
    .subtitle { margin: 0 0 1.5rem; color: #94a3b8; font-size: 0.9rem; }
    label { display: block; margin: 0.75rem 0 0.25rem; font-size: 0.85rem; color: #94a3b8; }
    input {
      width: 100%; box-sizing: border-box; padding: 0.6rem 0.7rem; font-size: 0.95rem;
      background: #0f172a; color: #e2e8f0; border: 1px solid #334155; border-radius: 8px;
    }
    input:focus { outline: none; border-color: #6366f1; }
    .btn {
      width: 100%; margin-top: 1.25rem; padding: 0.65rem 1rem; font-size: 0.95rem;
      border: none; border-radius: 8px; cursor: pointer; background: #6366f1; color: #fff;
    }
    .error {
      margin: 0 0 1rem; padding: 0.6rem 0.75rem; border-radius: 8px; font-size: 0.85rem;
      background: #7f1d1d33; border: 1px solid #b91c1c; color: #fca5a5;
    }
    .hint { margin-top: 1.25rem; font-size: 0.8rem; color: #64748b; text-align: center; }
    .hint code { background: #0f172a; padding: 0.1rem 0.35rem; border-radius: 4px; }
    a { color: #818cf8; }
  </style>
</head>
<body>
  <main class="card">
    <h1>Sign in</h1>
    <p class="subtitle">One System · React sample (local account)</p>
    ${error ? `<p class="error">${esc(error)}</p>` : ''}
    <form method="post" action="/login">
      <label for="username">Username</label>
      <input id="username" name="username" autocomplete="username" autofocus value="${esc(username)}" />
      <label for="password">Password</label>
      <input id="password" name="password" type="password" autocomplete="current-password" />
      <button class="btn" type="submit">Sign in</button>
    </form>
    <p class="hint">
      Demo accounts: <code>rajan / rajan123</code> · <code>demo / demo123</code><br />
      Prefer SSO? <a href="/">Use One System CAS on the home page.</a>
    </p>
  </main>
</body>
</html>`;
}

app.get('/login', (req, res) => {
  // Already signed in locally? Go straight home.
  if (req.session?.user) return res.redirect('/');
  res.type('html').send(loginPage());
});

// ---------------------------------------------------------------------------
// 4. POST /login -- dual purpose:
//
//    (a) CAS link-validation contract: if the body contains `client_validation`
//        (the CAS server posts {username, password, client_validation:true}),
//        OR the caller asks for JSON, respond with JSON {success} and DO NOT
//        create a browser session. 200 for valid, 401 for invalid.
//
//    (b) Interactive browser login: validate credentials, establish the local
//        session, and redirect home. On failure, re-render the form with an error.
// ---------------------------------------------------------------------------
app.post('/login', (req, res) => {
  const body = req.body ?? {};
  const { username, password } = body;

  const wantsJson =
    body.client_validation !== undefined ||
    (req.get('accept') || '').includes('application/json');

  const authedUser = authenticate(username, password);

  // (a) CAS validation call (or any JSON caller): pure credential check.
  if (wantsJson) {
    if (authedUser) {
      return res.status(200).json({ success: true });
    }
    return res.status(401).json({ success: false });
  }

  // (b) Interactive browser login.
  if (!authedUser) {
    return res
      .status(401)
      .type('html')
      .send(
        loginPage({
          error: 'Invalid username or password.',
          username: typeof username === 'string' ? username : '',
        }),
      );
  }

  // Establish the app's OWN local session, then redirect to the dashboard/home.
  req.session.user = {
    id: authedUser.id,
    username: authedUser.username,
    source: 'local',
  };
  return res.redirect('/');
});

// ---------------------------------------------------------------------------
// 5. POST /logout -- clear the local session.
// ---------------------------------------------------------------------------
app.post('/logout', (req, res) => {
  const wantsJson = (req.get('accept') || '').includes('application/json');
  req.session?.destroy(() => {
    res.clearCookie('os.sid');
    if (wantsJson) return res.status(200).json({ success: true });
    res.redirect('/');
  });
});

// ---------------------------------------------------------------------------
// 6. GET /api/me -- report the locally-signed-in user (null if none).
//    The SPA uses this to know whether someone is signed in via a LOCAL account
//    (the CAS session, by contrast, lives in the browser via the SDK).
// ---------------------------------------------------------------------------
app.get('/api/me', (req, res) => {
  res.json({ user: req.session?.user ?? null });
});

// ---------------------------------------------------------------------------
// 7. (Production) serve the built SPA. Registered LAST so the explicit routes
//    above (/login, /logout, /api/*) take precedence over the SPA catch-all.
// ---------------------------------------------------------------------------
const distDir = path.join(__dirname, 'dist');
app.use(express.static(distDir));
app.get('*', (_req, res) => {
  res.sendFile(path.join(distDir, 'index.html'), (err) => {
    if (err) {
      res
        .status(404)
        .send('SPA not built yet. Run "npm run build" first (production), ' +
          'or use "npm run dev" for development.');
    }
  });
});

app.listen(Number(PORT), () => {
  console.log(`\n  One System CAS React sample backend`);
  console.log(`  Listening on http://localhost:${PORT}`);
  console.log(`  CAS server : ${casBase} (internal / back-channel)`);
  console.log(`  CAS public : ${casPublicBase} (browser /sso/login redirect)`);
  console.log(`  client_id  : ${CAS_CLIENT_ID}`);
  console.log(`  callback   : ${CAS_CALLBACK_URL || '(app origin)'}`);
  console.log(`  local db   : ${DB_PATH}`);
  console.log(`  local login: http://localhost:${PORT}/login\n`);
});
