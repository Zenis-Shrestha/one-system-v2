# One System CAS — Angular Sample

A small but REAL Angular app that supports TWO ways to sign in:

- **Local accounts** — its OWN username/password store backed by SQLite.
- **CAS single sign-on** — the [`@cas-system/angular-cas-client`](../../packages/angular-cas-client) SDK.

Either path establishes the SAME app session, so the guarded `/profile` page and
the nav bar work identically afterwards.

### CAS SSO flow (unchanged)

1. **Trigger CAS login** — `CasAuthService.login()` redirects to the CAS server.
2. **Handle the callback & validate the token** — the SDK's `CasCallbackComponent`
   extracts `?token=…` and calls a tiny Express backend, which performs the
   **server-to-server** `/api/validate-token` call (the only place the
   `client_secret` lives).
3. **Show the authenticated user** — a guarded `/profile` page renders the
   validated `CasUser`.
4. **Logout** — clears the app session and bounces through CAS logout.

### Local accounts (new)

- **`GET /login`** — an Angular `LoginComponent` renders a username + password
  form (plus a "Login with CAS" button).
- **`POST /login`** — the Express backend validates the credentials against a
  SQLite store (`server/db.js`, file at `./data/app.db`). On success the SPA
  establishes the app's OWN session (the same `sessionStorage` the SDK uses) and
  redirects home. Logout for a local session clears it in place (no CAS bounce).
- **CAS link-validation contract** — the SAME `POST /login` answers the CAS
  server's server-to-server check: when the body contains `client_validation`
  (`{ username, password, client_validation: true }`) it returns
  `200 { "success": true }` for valid creds or `401 { "success": false }` for
  invalid, and creates NO browser session.

Two demo users are **seeded on first startup** (hashed with a salted scrypt
hash) if the `users` table is empty:

| Username | Password   |
| -------- | ---------- |
| `rajan`  | `rajan123` |
| `demo`   | `demo123`  |

Assigned port: **9110**. Local login page: **http://localhost:9110/login**.

---

## What it demonstrates from the SDK

| SDK building block        | Where it's used                                                        |
| ------------------------- | ---------------------------------------------------------------------- |
| `CAS_CONFIG`              | `src/main.ts` — provided from runtime config                           |
| `CasTokenInterceptor`     | `src/main.ts` — registered via `HTTP_INTERCEPTORS`                      |
| `CasCallbackComponent`    | `src/main.ts` — the `/cas/callback` route                              |
| `CasAuthGuard`            | `src/main.ts` — guards the `/profile` route                            |
| `CasAuthService`          | `app.component.ts`, `home.component.ts`, `profile.component.ts`        |
| `CasConfig` / `CasUser`   | typing throughout                                                      |

> The SDK ships `CasModule.forRoot(...)` for NgModule apps. This sample is
> **standalone** (no NgModule), so it wires the same providers manually exactly
> as the package README's "Standalone Usage" section documents.

---

## Why there is a backend

Angular runs in the browser, so it **cannot** hold the `client_secret` or call
`{CAS_BASE}/api/validate-token` directly. The SDK is built for this: its
`validateTokenViaBackend()` POSTs `{ token, client_id }` to a backend endpoint
you control. This sample provides that backend in **`server/server.js`** (Express):

- `GET  /api/config` — hands the SPA the **non-secret** config (`serverUrl`,
  `clientId`, `callbackUrl`). The secret is never sent.
- `POST /api/auth/validate` — receives `{ token, client_id }` from the SDK, adds
  `client_secret`, and calls `{CAS_BASE}/api/validate-token` server-to-server.
  It returns the CAS envelope `{ valid, user, expires_at }` straight back; the
  SDK reads `user` from it. The token is single-use — validated exactly once.
- `POST /login` — local username/password validation against the SQLite store
  (`server/db.js`). Browser logins get `{ success, user }`; the CAS server's
  `client_validation` calls get just `{ success }` (and no session). `GET /login`
  is the SPA route, served by the static fallback.
- Static host — serves the built Angular app and SPA-fallback so `/profile`,
  `/login`, and `/cas/callback?token=…` resolve.

---

## How the local SDK is linked (no publishing)

Two cooperating mechanisms:

1. **`package.json`** depends on the package by path:
   `"@cas-system/angular-cas-client": "file:../../packages/angular-cas-client"`.
   `npm install` symlinks it into `node_modules/@cas-system/angular-cas-client`.
2. The package's own `package.json` points `main`/`types` at **raw TypeScript**
   (`src/index.ts`), so Angular compiles the SDK **from source**. To make that
   work across the symlink, this sample sets:
   - `"preserveSymlinks": true` in `angular.json` (so the SDK's `@angular/*`
     peer imports resolve against **this** app's `node_modules`), and
   - the SDK source glob in `tsconfig.app.json`'s `include`.

No separate build step for the package, no registry publish.

---

## Prerequisites

- **Node.js ≥ 18.19.1** (Angular 18 minimum; the Docker image uses Node 20). Uses the built-in global `fetch` in the backend.
- A running **One System CAS server** reachable at `CAS_BASE_URL`.
- A **client system registered** in the CAS server with:
  - **client_id** = `angular-sample` (or whatever you set in `.env`)
  - **callback_url** = `http://localhost:9110/cas/callback`
  - a **client_secret** you copy into `.env`

---

## Configure

```bash
cp .env.example .env
# then edit .env and set CAS_BASE_URL, CAS_CLIENT_ID, CAS_CLIENT_SECRET,
# CAS_CALLBACK_URL, APP_PORT
```

| Variable             | Meaning                                                     |
| -------------------- | ----------------------------------------------------------- |
| `CAS_BASE_URL`       | Origin of the CAS server (no trailing slash)                |
| `CAS_CLIENT_ID`      | This app's registered client id                             |
| `CAS_CLIENT_SECRET`  | Paired secret — **backend only, never sent to the browser** |
| `CAS_CALLBACK_URL`   | Must equal `http://localhost:9110/cas/callback`             |
| `APP_PORT`           | Port the app listens on (default `9110`)                    |

---

## Install & run

```bash
npm install        # installs deps + symlinks the local SDK
npm run build      # compiles Angular + the SDK source into dist/
npm start          # serves the built app + API on http://localhost:9110
```

Open **http://localhost:9110**, click **Login with CAS**, authenticate on the
CAS server, and you'll land back on **/profile** showing your validated user.

### Dev mode (optional, with live reload)

Run the backend and the Angular dev server in two terminals:

```bash
npm run dev:backend   # Express on :9110 (serves /api/config + /api/auth/validate)
npm run serve:ng      # ng serve on :4200, proxies /api + POST /login → :9110 (proxy.conf.cjs)
```

In dev mode browse **http://localhost:4200**. Set `CAS_CALLBACK_URL` to
`http://localhost:4200/cas/callback` (and register that callback) if you want the
CAS redirect to land on the dev server instead of the built app.

---

## Run with Docker

The image is built from the **`one-system/` repo root** as context, because the
build needs the sibling local SDK at `packages/angular-cas-client`.

```bash
# from the one-system/ directory:
docker build -f examples/angular/Dockerfile -t cas-angular-sample .

docker run --rm -p 9110:9110 \
  -e CAS_BASE_URL=http://host.docker.internal:8080 \
  -e CAS_CLIENT_ID=angular-sample \
  -e CAS_CLIENT_SECRET=your-secret \
  -e CAS_CALLBACK_URL=http://localhost:9110/cas/callback \
  -e APP_PORT=9110 \
  cas-angular-sample
```

---

## File map

```
examples/angular/
├── src/
│   ├── main.ts                       # bootstrap: fetch /api/config, provide CAS_CONFIG,
│   │                                 #   CasTokenInterceptor, routes (callback + guarded)
│   ├── index.html / styles.css
│   └── app/
│       ├── app.component.ts          # nav: sign-in/logout via CasAuthService streams
│       ├── services/
│       │   └── local-session.service.ts  # bridges local login into the app session
│       └── pages/
│           ├── home.component.ts     # public landing; shows local-or-CAS state
│           ├── login.component.ts    # local username/password form (+ CAS button)
│           └── profile.component.ts  # guarded; shows the signed-in user
├── server/
│   ├── server.js                     # Express: /api/config, /api/auth/validate, /login, static host
│   └── db.js                         # SQLite local user store (seed + verify)
├── data/app.db                       # SQLite file (created at runtime; git-ignored)
├── angular.json                      # preserveSymlinks: true; proxyConfig: proxy.conf.cjs
├── tsconfig.json / tsconfig.app.json # includes SDK source for compilation
├── proxy.conf.cjs                    # dev-server → backend proxy (/api + POST /login)
├── proxy.conf.json                   # legacy /api-only proxy (kept for reference)
├── Dockerfile / .dockerignore
└── .env.example
```
