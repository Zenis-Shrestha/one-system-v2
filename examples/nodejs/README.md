# One System CAS — Node.js / Express sample

A real little Express app that proves the
[`@cas-system/node-cas-client`](../../packages/nodejs-cas-client) package works
end-to-end against a One System CAS SSO server **and** carries its OWN local
username/password accounts (backed by SQLite). A user can sign in **either** way.

## What it shows

### Local accounts (SQLite)

| Step | Route | What happens |
|------|-------|--------------|
| (1) Login form | `GET /login` | Renders a small username + password form (plus a "Login with CAS SSO" button) |
| (2) Local login | `POST /login` | Validates credentials against the SQLite store; on success creates this app's **own** session and redirects home; on failure re-renders the form with an error |
| — Link validation | `POST /login` (JSON) | The SAME route also serves the CAS server's **link-validation contract** — see below |

The local user store lives at `./data/app.db` (override with `DB_PATH`). The
`users` table (`id`, `username` UNIQUE, `password_hash`) is created on startup,
and if it is empty two **demo accounts** are seeded with salted-scrypt hashes:

| Username | Password |
|----------|----------|
| `rajan`  | `rajan123` |
| `demo`   | `demo123`  |

### CAS SSO (unchanged)

| Step | Route | What happens |
|------|-------|--------------|
| (a) Trigger login | `GET /cas/login` | Redirects the browser to `cas.getLoginUrl()` → `{CAS_BASE}/sso/login?client_id=…` |
| (b) Handle callback + validate | `GET /callback` | Reads `?token=<JWT>`, validates it **server-to-server** via `cas.validateToken(token)` (the package POSTs to `{CAS_BASE}/api/validate-token` with the client secret), then creates this app's **own** session |
| (c) Show the user | `GET /` | Shows whether you are signed in (local **or** CAS), who you are, and a logout button; otherwise a Login link |
| (d) Logout | `GET /logout` | Clears the app session and calls `cas.logout()` |

There is also a `GET /profile` route protected by the package's `casAuth`
middleware, to demonstrate the middleware export.

> **Note:** The CAS SSO trigger moved from `GET /login` to `GET /cas/login` so
> that `GET /login` can render the local form. The redirect contract itself
> (`{CAS_BASE}/sso/login?client_id=…`) and every other CAS route are unchanged.

### CAS link-validation contract (`POST /login`)

When the CAS server links a local account it calls this app to confirm the
credentials. `app/Services/ClientCredentialValidator.php` POSTs
`{"username","password","client_validation":true}` to `/login`. This sample
detects that call by the presence of the `client_validation` field (and/or an
`Accept: application/json` request) and responds with **JSON only — no browser
session is created**:

| Result | Status | Body |
|--------|--------|------|
| Valid credentials | `200` | `{"success": true}` |
| Invalid credentials | `401` | `{"success": false}` |

The JWT is HS256 and signed with a secret held only by the CAS server. This
client never holds that secret and never decodes the JWT itself — it always
validates by calling the CAS server, exactly as the package does internally.
The token is **single-use**: it is validated exactly once in `/callback`, after
which this app relies solely on its own session cookie.

## Prerequisites

- Node.js >= 18 (tested on Node 20)
- A running One System CAS server reachable at `CAS_BASE_URL`
- This client registered on the CAS server with:
  - **client_id** = your `CAS_CLIENT_ID`
  - **client_secret** = your `CAS_CLIENT_SECRET`
  - **callback_url** = `http://localhost:9102/callback` (must match `CAS_CALLBACK_URL` exactly)

## How it depends on the local package

`package.json` links the package by relative path (no publishing required):

```json
"@cas-system/node-cas-client": "file:../../packages/nodejs-cas-client"
```

`npm install` symlinks/copies the local package into `node_modules`, so the
sample uses the real package source.

> **Import paths used in this sample**
> - `require('@cas-system/node-cas-client')` → the `CasClient` class (package `main`).
> - `require('@cas-system/node-cas-client/src/middleware')` → `{ casAuth, casRole }`.
>   The package ships the middleware at `src/middleware.js` and has no `exports`
>   map, so the sample references that real path rather than the bare
>   `/middleware` subpath shown in the package README.

## Install & run (local)

```bash
cp .env.example .env        # then edit the values to match your CAS registration
npm install
npm start
```

Open <http://localhost:9102>, click **Sign in**, then either sign in with a
demo account (`rajan / rajan123` or `demo / demo123`) or click **Login with CAS
SSO**.

> `better-sqlite3` is a native addon and is compiled during `npm install`.
> On the first run the app creates `./data/app.db` and seeds the demo accounts.

## Configuration (`.env`)

| Variable | Meaning |
|----------|---------|
| `CAS_BASE_URL` | Origin of the CAS server, e.g. `http://localhost:8080` |
| `CAS_CLIENT_ID` | This client's registered id |
| `CAS_CLIENT_SECRET` | This client's registered secret (server-side only) |
| `CAS_CALLBACK_URL` | Must match the registered callback, `http://localhost:9102/callback` |
| `PORT` | Port to listen on (**assigned: 9102**) |
| `SESSION_SECRET` | Secret for this app's own session cookie (not the CAS JWT secret) |
| `DB_PATH` | Path to the local SQLite account store (default `./data/app.db`) |

## Run with Docker

The Dockerfile pulls in the local package, so build from the **repo root**
(`one-system/`) and pass the example's Dockerfile:

```bash
# from the one-system/ directory:
docker build -f examples/nodejs/Dockerfile -t one-system-cas-nodejs .

docker run --rm -p 9102:9102 \
  -e CAS_BASE_URL=http://host.docker.internal:8080 \
  -e CAS_CLIENT_ID=your_client_id \
  -e CAS_CLIENT_SECRET=your_client_secret \
  -e CAS_CALLBACK_URL=http://localhost:9102/callback \
  -e SESSION_SECRET=change-me \
  one-system-cas-nodejs
```

The image creates a writable `/app/data` directory and points `DB_PATH` at
`/app/data/app.db` so the SQLite store works at runtime. Mount a volume there
(`-v one-system-nodejs-data:/app/data`) if you want the local accounts to
persist across container restarts.

Assigned port for this sample: **9102**.
