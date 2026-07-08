# Vue CAS Client Sample

A runnable app that proves [`@cas-system/vue-cas-client`](../../packages/vue-cas-client)
works end-to-end against the One System CAS server **and** is a real application
with its own local accounts. A user can sign in **either** way:

- **CAS single sign-on**, or
- a **local username/password** account stored in SQLite.

The CAS SSO flow:

1. **Trigger CAS login** — `useCasAuth().login()` redirects to `{CAS}/sso/login?client_id=…`
2. **Handle the callback & validate the token** — `/auth/callback` calls
   `useCasAuth().handleCallback()`, which posts the single-use token to a tiny
   Express backend that validates it server-to-server (with the `client_secret`).
3. **Show the authenticated user** — the protected `/dashboard` route renders
   the validated user via `useCasAuth()` / `useCasUser()`.
4. **Logout** — `useCasAuth().logout()` clears the app's own session.

## Local accounts (the app's own auth)

Alongside SSO, the app has its own username/password store in **SQLite**
(`./data/app.db`, via `better-sqlite3`). On first boot it seeds two demo users
with hashed passwords (Node `scrypt`):

| Username | Password |
| -------- | -------- |
| `rajan`  | `rajan123` |
| `demo`   | `demo123`  |

- **`GET /login`** renders a small username/password form (`src/views/Login.vue`).
- **`POST /login`** validates the credentials against SQLite. On success it
  establishes the app's own server session (`express-session`, cookie `app.sid`)
  and the SPA mirrors the user into the same browser session the SDK uses, so the
  shared reactive auth state (nav bar, `<CasProtectedView>`, route guard) shows
  "signed in" identically for local and CAS logins. Then it redirects to the
  dashboard. On failure it re-renders the form with an error.
- The **same `POST /login`** route also serves the **CAS link-validation
  contract**: when the body contains `client_validation: true` (the CAS server
  posts `{ username, password, client_validation: true }`), it responds
  `200 { "success": true }` for valid credentials or `401 { "success": false }`
  for invalid ones, and does **not** create a browser session.
- **Logout** clears both the local server session (`POST /api/auth/local-logout`)
  and the SDK's browser session.

The local-auth code lives in `server/db.js` (SQLite store + seeding) and the
`/login` handlers in `server/index.js`.

## What it demonstrates from the package

| Feature | Where |
| ------- | ----- |
| Plugin install `app.use(CasPlugin, config)` | `src/main.js` |
| `useCasAuth()` composable (login / logout / handleCallback / user) | `src/App.vue`, `src/views/*` |
| `useCasUser()` composable (reactive roles) | `src/views/Dashboard.vue` |
| `<CasProtectedView>` slot component | `src/views/Home.vue` |
| `createCasAuthGuard()` router guard (`meta.requiresAuth`) | `src/main.js`, `src/router/index.js` |

## Architecture (why there is a backend)

The JWT is HS256-signed and the token is **single-use**; validating it needs the
`client_secret`, which must **never** ship to the browser. So the browser SDK
(`CasClient.validateTokenViaBackend`) only POSTs `{ token }` to a backend
endpoint. This sample's tiny **Express** backend is that endpoint:

```
browser  ──POST { token } ─────────────►  /api/auth/validate   (Express, this app)
Express  ──POST { token, client_id, client_secret } ─►  {CAS_BASE_URL}/api/validate-token
CAS      ──200 { valid, user, expires_at } ─────────►  Express
Express  ──200 { user } ────────────────────────────►  browser  (SDK stores session)
```

One Node process serves **both** the Vue front-end and the `/api/auth/validate`
endpoint on the same origin/port (Vite middleware in dev, static `dist/` in
prod) — no CORS, and it drops cleanly into the unified single-server deployment.

## Prerequisites

- Node.js 20+ (uses the built-in `fetch`).
- A running One System CAS server reachable at `CAS_BASE_URL`.
- A client registered on the CAS server with:
  - **client_id**: `vue-sample-app` (or your own — set `CAS_CLIENT_ID`)
  - **callback_url**: `http://localhost:9109/auth/callback`
  - the matching **client_secret**

## Install

```bash
# from one-system/examples/vue
cp .env.example .env          # then edit values (esp. CAS_CLIENT_SECRET)
npm install                   # links the local package via file:../../packages/vue-cas-client
```

The dependency on the SDK is a local path link in `package.json`:

```json
"@cas-system/vue-cas-client": "file:../../packages/vue-cas-client"
```

The package ships raw TypeScript + `.vue` source (its `main` is `src/index.ts`),
so Vite compiles it on the fly. `vite.config.js` excludes it from dependency
pre-bundling so esbuild doesn't choke on the in-package `.vue` component.

## Run

```bash
npm run dev      # Express + Vite middleware on http://localhost:9109
```

Then open **http://localhost:9109**, click **Login with SSO**, authenticate on
the CAS server, and you'll land back on the dashboard as the authenticated user.

Production mode (serves the built bundle from the same Express process):

```bash
npm run build
NODE_ENV=production npm start
```

## Configuration (`.env`)

| Variable | Purpose |
| -------- | ------- |
| `CAS_BASE_URL` | CAS server origin (used by the backend to validate). |
| `CAS_CLIENT_ID` | The registered client_id. |
| `CAS_CLIENT_SECRET` | **Server-side only.** Used to call `/api/validate-token`. |
| `CAS_CALLBACK_URL` | Registered callback URL (`…/auth/callback`). |
| `APP_PORT` | Port for this app (default **9109**). |
| `SESSION_SECRET` | Secret used to sign the local session cookie. |
| `APP_DB_PATH` | Optional override for the SQLite file (default `./data/app.db`). |
| `VITE_CAS_BASE_URL` / `VITE_CAS_CLIENT_ID` / `VITE_CAS_CALLBACK_URL` | Public values exposed to the SPA (no secret). |

## Docker

This sample's `file:` dependency lives outside this folder, so build with the
`one-system/` directory as the context:

```bash
# from the one-system/ directory
docker build -f examples/vue/Dockerfile -t vue-cas-sample .
docker run --rm -p 9109:9109 --env-file examples/vue/.env vue-cas-sample
```

## CAS client-system registration expected

| Field | Value |
| ----- | ----- |
| client_id | `vue-sample-app` |
| callback_url | `http://localhost:9109/auth/callback` |
| port | **9109** |
