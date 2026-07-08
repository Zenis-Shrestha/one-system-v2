# One System CAS — Sample Applications

This directory contains minimal, runnable sample applications, one per One System CAS
client SDK. Each sample demonstrates the **same CAS Single Sign-On (SSO) flow** end to
end, using only its SDK's documented public API:

1. **Trigger login** — build the CAS login URL and redirect the browser to
   `{CAS_BASE_URL}/sso/login?client_id=...`.
2. **Handle the callback** — the CAS server redirects back with a single-use `?token=`.
3. **Validate the token server-to-server** — the SDK (or a tiny backend) POSTs the token
   plus the `client_id`/`client_secret` to the CAS server's token-validation endpoint and
   receives the authenticated user. The client never decodes the JWT itself.
4. **Establish a local session** — store the returned user in the app's own session and
   render it; **logout** clears the local session and best-effort notifies CAS.

The samples are deliberately small so you can read each one and see exactly how its SDK
maps onto these four steps.

## Samples

| Sample | Language / Framework | Package used | Port | Run command | How it links the local package |
|--------|----------------------|--------------|------|-------------|--------------------------------|
| [laravel](./laravel) | PHP / Laravel 11 | `cas-system/laravel-client` (laravel-cas-client-package) | 9101 | `cp .env.example .env && composer install && php artisan key:generate && php artisan serve --host=0.0.0.0 --port=9101` → http://localhost:9101 | Composer `path` repository in `composer.json` (`repositories[].type=path`, `url=../../packages/laravel-cas-client-package`, `options.symlink=true`), required as `"cas-system/laravel-client":"*"`; symlinked into `vendor/`. |
| [nodejs](./nodejs) | JavaScript / Node.js (Express) | `@cas-system/node-cas-client` | 9102 | `cp .env.example .env && (edit values) && npm install && npm start` → http://localhost:9102 | npm `file:` dependency `"@cas-system/node-cas-client": "file:../../packages/nodejs-cas-client"` (symlinked into `node_modules`). |
| [javascript](./javascript) | JavaScript (static HTML + UMD SDK) + Express backend | `@cas-system/js-cas-client` | 9103 | `cp .env.example .env && (edit values) && npm install && npm start` → http://localhost:9103 | npm `file:` dependency `"@cas-system/js-cas-client": "file:../../packages/javascript-cas-client"`; `server.js` serves the local UMD file to the browser at `/vendor/cas-client.js` via `require.resolve(...)`. |
| [python](./python) | Python / Flask | `cas-system-client` (python-cas-client) | 9104 | `cp .env.example .env && (edit values)` then `python3 -m venv .venv && source .venv/bin/activate && pip install -r requirements.txt && python app.py` → http://localhost:9104 | pip path install: `requirements.txt` lists `../../packages/python-cas-client` (editable `pip install -e ...` alternative documented in the sample README). |
| [java](./java) | Java / Spring Boot 2.7.x | `io.github.insol-dev:cas-client:2.0.0` (java-cas-client) | 9105 | One-time: `mvn install -DskipTests` in `packages/java-cas-client`, then in `examples/java`: `set -a; . ./.env; set +a; mvn spring-boot:run` → http://localhost:9105 | Local Maven install: the package is published to `~/.m2` via `mvn install`, then declared as a normal `<dependency>` on `io.github.insol-dev:cas-client:2.0.0` in `pom.xml`. |
| [dotnet](./dotnet) | C# / ASP.NET Core (.NET 8 minimal API) | `CasSystem.Client` (dotnet-cas-client) | 9106 | `cp .env.example .env && set -a && . ./.env && set +a && dotnet run` → http://localhost:9106 | `<ProjectReference Include="../../packages/dotnet-cas-client/CasSystem.Client.csproj" />` in `CasDemo.csproj` (compiles the local package from source). |
| [react](./react) | JavaScript / React (Vite SPA) + Express backend | `@cas-system/react-cas-client` | 9107 | `cp .env.example .env && npm install && npm run dev` (dev SPA on 9107, API proxied to internal 9108); prod: `npm run build && npm start` → http://localhost:9107 | npm `file:` dependency `"@cas-system/react-cas-client": "file:../../packages/react-cas-client"`; `vite.config.js` additionally aliases the import to the package TS source (`../../packages/react-cas-client/src/index.ts`) so Vite compiles it with no package build step. |
| [nextjs](./nextjs) | TypeScript / Next.js 14 (App Router) | `@cas-system/nextjs-cas-client` | 9108 | `cp .env.example .env.local && (fill in values) && npm install && npm run dev` → http://localhost:9108 | npm `file:` dependency `"@cas-system/nextjs-cas-client": "file:../../packages/nextjs-cas-client"`; consumed as TS `src/` via `next.config.mjs` `transpilePackages` + webpack subpath aliases and matching `tsconfig.json` `paths` (no package build step). |
| [vue](./vue) | JavaScript / Vue 3 (Vite SPA) + Express backend | `@cas-system/vue-cas-client` | 9109 | `cp .env.example .env && (edit values) && npm install && npm run dev` (serves SPA + API on 9109) → http://localhost:9109 | npm `file:` dependency `"@cas-system/vue-cas-client": "file:../../packages/vue-cas-client"` (symlinked); `vite.config.js` excludes it from `optimizeDeps` and dedupes/aliases the peer deps (vue, vue-router, pinia) so the symlinked package's bare imports resolve. |
| [angular](./angular) | TypeScript / Angular 18 (standalone) + Express backend | `@cas-system/angular-cas-client` | 9110 | `cp .env.example .env && (edit secrets) && npm install && npm run build && npm start` (serves app + API on 9110) → http://localhost:9110 | npm `file:` dependency `"@cas-system/angular-cas-client": "file:../../packages/angular-cas-client"` (symlinked); compiled from source via `angular.json` `preserveSymlinks: true` and `tsconfig.app.json` including `node_modules/@cas-system/angular-cas-client/src/**/*.ts`. |
| [rust](./rust) | Rust / axum (+ SQLite via rusqlite) | `rust-cas-client` (rust-cas-client) | 9111 | `cp .env.example .env && (edit values) && cargo run` → http://localhost:9111 | Cargo `path` dependency `rust-cas-client = { path = "../../packages/rust-cas-client" }` in `Cargo.toml` (compiles the local crate from source). |

Each sample has its own `README.md` with full per-sample details, the exact public API it
exercises, and any known caveats.

## Shared prerequisites

All samples assume the same CAS environment. Before running any of them:

1. **A running CAS server.** Have a One System CAS server reachable at a base URL (this is
   the value you put in `CAS_BASE_URL`).

2. **Register each sample as a `client_system` on the CAS server.** Every sample needs its
   own client registration with a matching `client_id` and `callback_url`. The default
   callback URL is the sample's port plus its callback path, for example:

   | Sample | Port | Callback URL |
   |--------|------|--------------|
   | laravel | 9101 | `http://localhost:9101/callback` |
   | nodejs | 9102 | `http://localhost:9102/callback` |
   | javascript | 9103 | `http://localhost:9103/cas/callback` |
   | python | 9104 | `http://localhost:9104/callback` |
   | java | 9105 | `http://localhost:9105/callback` |
   | dotnet | 9106 | `http://localhost:9106/cas/callback` |
   | react | 9107 | `http://localhost:9107/` |
   | nextjs | 9108 | `http://localhost:9108/api/cas/callback` |
   | vue | 9109 | `http://localhost:9109/auth/callback` |
   | angular | 9110 | `http://localhost:9110/cas/callback` |
   | rust | 9111 | `http://localhost:9111/cas/callback` |

   The `client_id`, `client_secret`, and `callback_url` you register **must** match the
   values configured in each sample's environment (see below).

3. **Environment configuration.** Each sample reads its CAS settings from environment
   variables (via an `.env.example` you copy to `.env`). The common keys are:

   - `CAS_BASE_URL` — base URL of the running CAS server.
   - `CAS_CLIENT_ID` — the registered `client_id` for this sample.
   - `CAS_CLIENT_SECRET` — the registered client secret (server-side only; never shipped to
     the browser).
   - `CAS_CALLBACK_URL` — the registered callback URL for this sample (matches the table
     above).

   Individual samples also accept additional, framework-specific keys (e.g. a `PORT`/
   `APP_PORT`, a session/secret key, or public mirrors prefixed for the bundler). The
   Laravel sample's underlying package additionally uses `CAS_SERVER_URL`/`CAS_CLIENT_ID`/
   `CAS_CLIENT_SECRET`/`CAS_CALLBACK_URL`; its `.env.example` and README document the
   mapping to `CAS_BASE_URL`. Always check the sample's own `.env.example` and `README.md`
   for the complete list.

## A note on SPA / browser samples

The browser-based SPA samples — **javascript**, **react**, **vue**, and **angular** —
each include a tiny backend (and **nextjs** uses its own server-side route handlers).
This is required: the `client_secret` must **never** be exposed in the browser, and
server-to-server token validation must happen on a trusted server.

In these samples the browser SDK talks only to that local backend (typically a
`POST /api/auth/validate`-style endpoint). The backend holds `CAS_CLIENT_SECRET`, adds the
`client_id` + `client_secret`, performs the server-to-server validation call to the CAS
server, and returns the authenticated user to the front end. Any public config exposed to
the browser (server URL, client id, callback URL) deliberately omits the secret.

## Unified deployment

Every sample ships a `Dockerfile` so it can be containerized and run alongside the CAS
server. The samples occupy ports **9101–9111** (one per sample, as listed above), so the
whole set can run concurrently next to a CAS server without port conflicts.

> **Docker build context.** Because every sample links its SDK from the sibling
> `packages/` directory (Composer path repo, npm `file:`, pip path, project reference, or
> a local Maven install), the Docker image must be built with the **`one-system/` repo
> root as the build context** so the local package is available to the build. Each
> sample's `Dockerfile` header and `README.md` document the exact
> `docker build -f examples/<sample>/Dockerfile -t <name> .` command.
