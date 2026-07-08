# One System CAS — JavaScript Sample (UMD browser SDK + tiny Express backend)

A small but **real** app: it has its OWN local username/password accounts (stored
in SQLite) **and** keeps the **`@cas-system/js-cas-client`** CAS Single-Sign-On flow.
Users can sign in EITHER way.

CAS SSO flow:

- **(a) Trigger CAS login** — a static page uses the UMD `CasClient` to redirect to the CAS server.
- **(b) Handle the callback & validate the token** — the SDK extracts `?token=` from the callback URL and hands it to a tiny backend that validates it **server-to-server**.
- **(c) Show the authenticated user** — the page renders the user returned by the backend.
- **(d) Logout** — clears the app session and the CAS session.

## Local username/password accounts

The backend also keeps its own accounts in a SQLite file (`./data/app.db`, via
[`better-sqlite3`](https://github.com/WiseLibs/better-sqlite3)). A `users` table
(`id`, `username` UNIQUE, `password_hash`) is created on startup and **seeded with
two demo users** if empty (passwords stored salted+hashed with Node's `scrypt`):

| Username | Password   |
|----------|------------|
| `rajan`  | `rajan123` |
| `demo`   | `demo123`  |

- **`GET /login`** renders a small username+password form.
- **`POST /login`** validates against the SQLite store. On success it establishes
  the app's OWN local session (the same `sid` cookie the CAS flow uses) and
  redirects home; on failure it re-renders the form with an error.
- **`POST /login` also serves the CAS link-validation contract.** When the CAS
  server posts `{ "username", "password", "client_validation": true }`, the route
  responds `200 { "success": true }` for valid credentials or `401 { "success":
  false }` for invalid — and does **not** create a browser session for that call.
  The validation call is detected by the `client_validation` field (and/or an
  `Accept: application/json` request).

The home page shows whether you're signed in (locally OR via CAS), who you are, and
offers logout (which clears the local session).

## Why two parts? (read this)

The browser SDK is intentionally **secret-free**. Token validation requires the
`client_secret`, which must never ship in browser code. So this sample is split:

| Part | Lives in | Responsibility |
|------|----------|----------------|
| **Front-end** (`public/index.html`, `public/callback.html`) | the browser | Loads the UMD `CasClient` (via `<script src="/vendor/cas-client.js">`), starts login, extracts the token from the callback URL. |
| **Backend** (`server.js`, tiny Express) | the server | Holds `client_secret`; exposes `POST /api/auth/validate`; performs the server-to-server call to `{CAS_BASE}/api/validate-token`; creates the app's own session. |

Flow:

```
Browser                         This Express backend                CAS server
  │  click "Login"                                                      │
  │  cas.login()  ───────────────────────────────────────────────────► /sso/login?client_id=...
  │                                                                     │ (user authenticates)
  │  ◄────────────────────────────────────────  302 /cas/callback?token=<jwt>
  │  callback.html: cas.handleCallback()                                │
  │  POST /api/auth/validate { token }  ──►  POST /api/validate-token   │
  │                                          { token, client_id,        │
  │                                            client_secret }  ───────► (validates, single-use)
  │                                          ◄── { valid, user, ... }   │
  │  ◄── { user } + Set-Cookie: sid          (creates app session)      │
  │  show user / logout                                                 │
```

The browser SDK's `validateTokenViaBackend(token)` POSTs `{ "token": "<jwt>" }` to
`backendValidateUrl` (`/api/auth/validate`). The backend adds `client_id` + `client_secret`
and calls the CAS server. The JWT is **single-use** — validated exactly once, then the
backend mints its own `sid` session cookie.

## How it depends on the local package

`package.json` links the SDK locally — no publishing:

```json
"@cas-system/js-cas-client": "file:../../packages/javascript-cas-client"
```

`server.js` resolves the actual UMD file with `require.resolve('@cas-system/js-cas-client')`
and serves it at `/vendor/cas-client.js`, so the browser loads the exact local package file.

## Prerequisites

- Node.js 18+ (uses the built-in `fetch`).
- A running One System CAS server.
- This sample registered as a CAS client with:
  - **client_id**: `javascript-sample` (or your own — set `CAS_CLIENT_ID`)
  - **callback_url**: `http://localhost:9103/cas/callback` (must match `CAS_CALLBACK_URL`)
  - a **client_secret** (set `CAS_CLIENT_SECRET`)

## Configure

```bash
cp .env.example .env
# edit .env: CAS_BASE_URL, CAS_CLIENT_ID, CAS_CLIENT_SECRET, CAS_CALLBACK_URL, PORT
```

| Var | Meaning |
|-----|---------|
| `CAS_BASE_URL` | CAS server origin, e.g. `http://localhost:8080` |
| `CAS_CLIENT_ID` | this app's registered client id |
| `CAS_CLIENT_SECRET` | paired secret — **backend only**, never sent to the browser |
| `CAS_CALLBACK_URL` | registered callback, `http://localhost:9103/cas/callback` |
| `PORT` | app port (**9103**) |

## Install & run

```bash
npm install
npm start
# → http://localhost:9103
```

Open <http://localhost:9103>, click **Login**, authenticate at the CAS server, and you're
returned signed in. Click **Logout** to clear the session.

## Run with Docker

The `file:` dependency points outside this folder, so build from the `one-system/` root
(so both the example and the package are in the build context):

```bash
# from the one-system/ directory:
docker build -f examples/javascript/Dockerfile -t cas-sample-js .
docker run --rm -p 9103:9103 --env-file examples/javascript/.env cas-sample-js
```

The sample listens on the assigned port **9103**.

## Files

| File | Purpose |
|------|---------|
| `server.js` | Express backend: serves the SDK + static pages, `POST /api/auth/validate` (server-to-server CAS), `GET`/`POST /login` (local accounts + CAS link-validation), session, logout. |
| `db.js` | SQLite user store (`better-sqlite3`): `users` table, demo-user seeding, salted `scrypt` password hashing, `authenticate()`. |
| `public/index.html` | Home: local-login link, CAS login button, authenticated-user / logout UI. |
| `public/callback.html` | Runs `cas.handleCallback()` at `/cas/callback`. |
| `.env.example` | Configuration template. |
| `Dockerfile` | Minimal `node:18-alpine` image (adds a build toolchain for `better-sqlite3` and a writable `./data` dir). |

> The SQLite database lives at `./data/app.db` (override with `APP_DB_PATH`). The
> directory must be writable at runtime; in Docker it is created at
> `/app/examples/javascript/data` and `APP_DB_PATH` points there.
