# One System CAS Client SDK — Package Review Report

This report synthesizes the per-package code reviews of the One System CAS client SDKs against the
authoritative One System CAS server protocol (the Laravel server ground truth). It covers protocol
conformance, cross-package consistency, per-package findings, and prioritized follow-ups.

## Authoritative protocol (reference)

- **Login redirect:** `GET {CAS_BASE}/sso/login?client_id={CLIENT_ID}` only. The server uses the client's
  **pre-registered** `callback_url` and 302-redirects to `{callback_url}?token={JWT}`.
- **Token validation (server-to-server):** `POST {CAS_BASE}/api/validate-token` with body
  `{ token, client_id, client_secret }`. Success `200` returns `{ valid: true, user: { id, username, email }, expires_at }`;
  failure is HTTP `401` with `{ error }`. **Alt path:** `POST {CAS_BASE}/api/sso/validate`.
- **Service-to-service token issuance:** `POST {CAS_BASE}/api/sso/token` with `{ client_id, client_secret, username }`
  returns `{ redirect_url, token }` (IP-whitelisted).
- **Logout:** `POST {CAS_BASE}/api/logout`.
- **Token is SINGLE-USE** — validate exactly once, then establish the client's own session.
- The JWT is HS256-signed with the server's `JWT_SECRET`; clients normally do **not** hold `JWT_SECRET` and
  must rely on `/api/validate-token` rather than decoding locally. Payload includes a **singular** `role`
  (plus `userId, username, email, clientSystemId, clientId, iat, exp, jti`).

---

## 1. Summary

| Package | Language | Protocol conformance | # Issues | # Fixes applied |
|---|---|---|---:|---:|
| `@cas-system/angular-cas-client` | Angular / TypeScript | minor-issues | 6 | 4 |
| `CasSystem.Client` (dotnet-cas-client) | .NET / C# | minor-issues | 7 | 3 |
| `io.github.insol-dev:cas-client` (java-cas-client) | Java | minor-issues | 7 | 3 |
| `@cas-system/nextjs-cas-client` | Next.js / TypeScript | minor-issues | 7 | 10 |
| `@cas-system/node-cas-client` | Node.js | minor-issues | 7 | 7 |
| `cas-client` (python-cas-client) | Python | minor-issues | 5 | 2 |
| `@cas-system/react-cas-client` | React / TypeScript | minor-issues | 4 | 5 |
| `@cas-system/vue-cas-client` | Vue 3 / TypeScript | minor-issues | 4 | 1 |
| `cas-system/laravel-client` | PHP / Laravel | minor-issues | 9 | 0 (review-only / symlink) |

All nine packages are rated **minor-issues**. None is fully conformant out of the box; each had at least one
material divergence. The Laravel package is the only one with **no fixes applied** (its directory is a symlink
into a sibling repo, so it was review-only) and carries the most severe unresolved defects.

---

## 2. Cross-package consistency analysis

### 2.1 Validation endpoint path

After fixes, the **server-to-server (or backend-proxy) packages converge on the canonical
`POST /api/validate-token`**:

- Fixed to `/api/validate-token`: **dotnet**, **java**, **nextjs**, **node** (all were previously on the alt
  `/api/sso/validate` and were corrected).
- **react** and **vue** are browser SDKs that POST `{ token }` to a consumer-owned `backendValidateUrl`; their
  docs/JSDoc were aligned to point the backend at `/api/validate-token` (react) or remain on the protocol-listed
  alt `/api/sso/validate` (vue).
- **python** intentionally stays on the alt `/api/sso/validate` (conformant per protocol; not changed).
- **angular** is the outlier: it primarily calls a `backendValidateUrl` but **retains a direct-to-CAS fallback to
  `/api/sso/validate`** from the browser.
- **laravel** `CasAuthService` uses the alt `/api/sso/validate` (conformant). However its separate
  `SignatureClient` posts to a **non-protocol path `/api/sso/validate-token`** — a unique divergence not present
  anywhere else.

**Verdict:** endpoint paths are now broadly consistent (canonical or the documented alt), with two genuine
divergences: angular's browser-side direct fallback and laravel's `SignatureClient` non-protocol path.

### 2.2 Validate-token request body keys

- Server-to-server packages (**dotnet, java, node, python, laravel/CasAuthService**) send the correct
  `{ token, client_id, client_secret }`.
- Browser SDKs by design send only `{ token }` to the consumer backend (**react, vue**), or `{ token, client_id }`
  (**angular**, **nextjs**) — `client_secret` is correctly never sent from the browser.
- **angular's** direct-to-CAS fallback sends only `{ token, client_id }` to the CAS server directly, which is
  **missing the protocol-required `client_secret`** and would 401 server-side.
- **laravel's** `SignatureClient.validateSSOToken()` sends only `{ token }` (missing `client_id`/`client_secret`)
  to the wrong path — non-conformant.

**Verdict:** body keys are consistent within each architecture class. The two non-conformant exceptions are
angular's direct fallback and laravel's `SignatureClient`.

### 2.3 Parsing the validate-token response

The server envelope is `{ valid: true, user: {...}, expires_at }`. Handling diverged significantly and was a
common bug class:

- **Correctly checks `valid` AND unwraps `user` (post-fix):** angular (new `extractUser()` helper),
  dotnet, java, node, python, nextjs (`data.valid && data.user`).
- **react / vue:** rely on the consumer backend returning a bare `CasUser`; react fixed the README example to
  `const { user } = await response.json()` (was forwarding the raw envelope, which left `id/username/email`
  undefined). This is the SDK's own contract, not the server's, but is now internally consistent.
- **laravel `CasAuthService`:** only checks `isset($data['user'])` and **ignores the `valid` flag** (still
  acceptable because the server returns 401 on failure, but not protocol-faithful).
- **laravel `SignatureClient`:** reads `$response['data']['user_data']` — the **wrong key** (server uses `user`).

A recurring pre-fix defect across nearly every package was **accepting a `200` with a present `user` object
regardless of `valid`** (dotnet, java, node, python, laravel all had this; nextjs went further and read a
non-existent `data.success`, so login could *never* succeed). Most are now fixed; laravel remains unfixed.

### 2.4 Login redirect query params

The protocol login is `GET /sso/login?client_id=...` only. Many packages appended non-protocol
`response_type=token` and `redirect_uri=<callback>`:

- **Removed (now `client_id` only):** dotnet, java, node, python, **vue**.
- **Still appended (reported, not changed):** angular, nextjs, react, laravel/CasAuthService.

**Verdict:** `client_id` and the `/sso/login` path are correct everywhere. The extra params remain a source of
**inconsistency**: half the packages emit a clean URL, the other half still pass `response_type`/`redirect_uri`.
These are likely harmless (the server resolves the registered callback) but should be unified.

### 2.5 Service-to-service token issuance & logout

- **`POST /api/sso/token`** with `{ client_id, client_secret, username }`: consistent across dotnet, java, node,
  python, nextjs, laravel. nextjs previously parsed a non-existent `data.success` (always returned null) — fixed
  to gate on `data.token`. Browser SDKs (angular, react, vue) do not expose token issuance (correct).
- **Logout:** server-to-server `POST /api/logout` is consistent (dotnet, java, node, python, react, vue, nextjs,
  laravel). **angular diverges**: it does a browser `GET {serverUrl}/api/logout?redirect_uri=...` via
  `window.location` — the protocol defines logout as a `POST` and no browser GET-logout route exists.

### 2.6 Config surface consistency

Server/back-end SDKs (dotnet, java, node, python, laravel, nextjs/server) share a coherent surface:
`serverUrl/server_url`, `clientId/client_id`, `clientSecret/client_secret`, `callbackUrl/callback_url`, plus
optional `signatureSecret`, `enableSignatureValidation`, `timeout`, `verifySsl`.

Browser SDKs (angular, react, vue) correctly **omit `clientSecret`** and add `backendValidateUrl` (react/vue;
angular also has it). Notable divergences:

- **angular** is the only browser SDK with a `backendValidateUrl`-optional design that **falls through to a
  direct CAS call** — react and vue deliberately *require* the backend and have no fallback.
- **nextjs** adds env-var driven config (`CAS_SERVER_URL`, `CAS_CLIENT_ID`, `CAS_CLIENT_SECRET`,
  `CAS_CALLBACK_URL`, `CAS_COOKIE_SECRET`) and HMAC-signed HttpOnly session cookies — richer than the others but
  consistent in key naming.
- **HMAC request-signing extension** (`signatureSecret`, `X-Signature`/`X-Client-ID`/`X-Timestamp` headers) is
  **not part of the documented protocol** yet appears in dotnet, java, node, python, laravel, and was present in
  nextjs (now removed). It is off by default everywhere except where defaults are weak (dotnet ships
  `SignatureSecret = "default-signature-secret"`). This is the single most widespread non-protocol surface.

### 2.7 Roles

The server validate-token user shape is `{ id, username, email }` and the JWT carries a **singular `role`**, but
**dotnet, java, nextjs, react, node, laravel, angular all expose role helpers that read a `roles` array**. Against
the documented response these checks are effectively no-ops (or always false). This is a **systemic
cross-package mismatch**: every SDK assumes a `roles` array the documented response does not provide.

### 2.8 Packaging consistency

- **angular**: `main`/`types` point at TS source (`src/index.ts`); sibling react points at `dist/`. Self-consistent
  but unconventional.
- **vue**: `main`/`module`/`types` point at raw `src/index.ts`, no build step. Ships uncompiled TS/.vue.
- **nextjs**: `main`/`exports` point at `dist/...` but **`src/index.ts` / `src/server/index.ts` did not exist** —
  imports would have been unresolvable after build. Fixed by creating the barrels (build still unverified).
- **node**: dead `types: src/index.d.ts` (file absent) — removed; test script points at a non-existent `test/`.
- **laravel**: vendor-name/namespace cosmetic divergence; declared MIT but no LICENSE shipped; `autoload-dev`
  Tests namespace with no `tests/` directory.

**Verdict:** packaging is the **least consistent** dimension — three different publish strategies (source-publish,
dist-publish, uncompiled) and several manifest/file mismatches.

---

## 3. Per-package details

### 3.1 `@cas-system/angular-cas-client` (Angular / TypeScript) — minor-issues

**API summary:** Angular library: `CasModule.forRoot(config, interceptUrls?)`, `CasClientService` (low-level),
`CasAuthService` (reactive `user$`/`isAuthenticated$`/`isLoading$`), `CasAuthGuard`, `CasTokenInterceptor`
(+ `CAS_INTERCEPT_URLS`), `CasCallbackComponent`, models `CasConfig`/`CasUser`/new `CasValidateResponse`,
`CAS_CONFIG` token. Config: `serverUrl`, `clientId`, `callbackUrl?`, `backendValidateUrl?`. Session in
`sessionStorage` (`cas_token`, `cas_user`, `cas_return_url`).

**Endpoints used:**
- `GET {serverUrl}/sso/login?client_id=...&response_type=token&redirect_uri=...`
- `POST {backendValidateUrl}` OR fallback `POST {serverUrl}/api/sso/validate` body `{ token, client_id }`
- `GET {serverUrl}/api/logout?redirect_uri=...` (via `window.location`)

**Issues found:**
- **High (FIXED):** `validateTokenViaBackend()` cast the entire body to `CasUser`, so `id/username/email` (which
  live under `.user`) were undefined and a `valid:false` 200 was accepted as a logged-in user.
- **Medium:** Unsafe/incomplete direct-to-CAS fallback to `/api/sso/validate` with only `{ token, client_id }`
  (no `client_secret`) — would 401 server-side. Left in place (documented behavior), reported.
- **Medium:** `logout()` does a browser `GET` to `/api/logout` — protocol defines logout as `POST` and no browser
  GET-logout route exists. Reported, not changed.
- **Low:** `getLoginUrl()` appends non-protocol `response_type=token`/`redirect_uri`.
- **Low:** `package.json` `main`/`types` point at `.ts` source (source-publish), unlike sibling react (dist).
- **Low:** `isAuthenticated$` hand-rolls a subscription without unsubscribe/share (leak/duplication risk).

**Fixes applied:**
- Rewrote `validateTokenViaBackend()` with a private `extractUser()` helper that unwraps
  `{ valid, user, expires_at }` (returns null on `valid===false` or absent user) while still accepting a bare
  `CasUser` from a custom backend; persists token/user only on success.
- Added `CasValidateResponse` interface; typed the post as `post<CasUser | CasValidateResponse>`; exported it from
  the public API.

**Remaining concerns:**
- Direct-to-CAS fallback cannot succeed (no `client_secret`); decide between requiring `backendValidateUrl`
  (like react/vue) or removing the fallback.
- README should show the backend forwarding shape (backend must add `client_secret` and forward to
  `/api/validate-token`).
- Confirm whether a browser GET-logout route exists or logout should be server-to-server POST + local clear.
- Confirm the server ignores `response_type`/`redirect_uri`.
- If a compiled npm publish is intended, switch `main`→`dist/index.js`, `types`→`dist/*.d.ts`, ship `dist`.

---

### 3.2 `CasSystem.Client` (dotnet-cas-client) (.NET / C#) — minor-issues

**API summary:** `CasClient` — `GetLoginUrl(returnUrl?)`, `GenerateSSOTokenAsync(username)`,
`ValidateTokenAsync(token)` → `Dictionary<string,object>?`, `GetUserFromToken(token)` (in-memory MD5-keyed cache,
1h TTL), `LogoutAsync(token?)`, static role helpers. `CasConfig` — `ServerUrl/ClientId/ClientSecret/CallbackUrl/
SignatureSecret/EnableSignatureValidation/TimeoutSeconds/VerifySsl`. `CasAuthMiddleware` (ASP.NET Core),
`AddCasClient(config)` DI. Targets net6.0, NuGet `CasSystem.Client` v2.0.0, MIT.

**Endpoints used:**
- `GET {ServerUrl}/sso/login?client_id={clientId}` (fixed: removed extra params)
- `POST {ServerUrl}/api/validate-token` body `{ token, client_id, client_secret }` (fixed: was `/api/sso/validate`)
- `POST {ServerUrl}/api/sso/token` body `{ client_id, client_secret, username }`
- `POST {ServerUrl}/api/logout`

**Issues found:**
- **High (FIXED):** Validation hit the wrong endpoint `/api/sso/validate` → fixed to `/api/validate-token`.
- **Medium (FIXED):** `GetLoginUrl` appended `response_type=token`/`redirect_uri` → now emits only `client_id`.
- **Medium (FIXED):** `ValidateTokenAsync` never inspected `valid` → now treats `valid:false` as rejection.
- **Medium (report-only):** Role helpers + middleware read a `roles` ARRAY; documented user shape is
  `{ id, username, email }` and JWT carries singular `role` → role checks always false.
- **Low (report-only):** `CasConfig.VerifySsl` declared but never wired into `HttpClient`.
- **Low (report-only):** Non-protocol headers `X-Client-ID`/`X-Timestamp`/`X-Signature`; `SignatureSecret`
  defaults to `"default-signature-secret"` (weak default).
- **Low (report-only):** Single-use token vs middleware Bearer revalidation could double-validate a consumed token.

**Fixes applied:** endpoint → `/api/validate-token`; honor `valid` flag; remove non-protocol login params (+ XML
doc update). (Body keys were already correct.)

**Remaining concerns:** roles mapping (singular `role` vs `roles` array); unused `VerifySsl`; non-protocol signing
headers + weak default; single-use vs Bearer revalidation.

---

### 3.3 `io.github.insol-dev:cas-client` (java-cas-client) (Java) — minor-issues

**API summary:** `com.cassystem.client.CasClient(CasConfig)`: `getLoginUrl(String returnUrl)` + new no-arg
`getLoginUrl()`, `generateSSOToken(username)` (POST `/api/sso/token`), `validateToken(token)` (POST
`/api/validate-token`), `getUserFromToken` (md5-keyed cache, 1h TTL), `logout`, role helpers. `CasConfig`
builder; `CasAuthFilter` (`javax.servlet.Filter`, Bearer + `cas_user` session, redirect to `loginUrl?return_url=`,
403 on missing role). Deps: OkHttp 4.12, Gson 2.10.1, jjwt-api 0.12.3 (unused), javax.servlet-api 4.0.1, slf4j.
MIT, Java 11.

**Endpoints used:** `POST {serverUrl}/api/validate-token` (was `/api/sso/validate`); `POST /api/sso/token`;
`POST /api/logout`; `GET /sso/login?client_id=...`.

**Issues found:**
- **High (FIXED):** `validateToken` used alt `/api/sso/validate` → switched to canonical `/api/validate-token`.
- **Medium (FIXED):** Ignored `valid` flag → now requires `Boolean.TRUE.equals(data.get("valid"))` AND a user.
- **Medium (FIXED):** `getLoginUrl` emitted `response_type`/`redirect_uri` → now `client_id` only (kept
  `getLoginUrl(String)` for source compat, added no-arg overload).
- **Medium (reported):** No callback token-extraction support — `CasAuthFilter` only reads Bearer/session, never
  extracts `?token=` from the CAS redirect (protocol step 2).
- **Low (reported):** README claims "Jakarta Servlet" but code/pom use `javax.servlet.*` (Java EE) — not
  Jakarta-compatible as shipped.
- **Low (reported):** Hardcoded 1h cache TTL ignores `expires_at`.
- **Low (reported):** Unused `jjwt-api` dependency (correct not to decode locally).
- **Low (reported):** README over-claims HMAC signing as a headline feature (off by default, non-protocol).

**Fixes applied:** endpoint → `/api/validate-token` (URL + signature path); require `valid` true + user; remove
non-protocol login params + add no-arg overload.

**Remaining concerns:** add a callback handler that reads `request.getParameter("token")` and validates once
(single-use); `javax` vs `jakarta` README mismatch; `expires_at` ignored; remove unused `jjwt-api`; de-emphasize
HMAC in docs.

---

### 3.4 `@cas-system/nextjs-cas-client` (Next.js / TypeScript) — minor-issues

**API summary:** Client root: `CasProvider`/`useCasAuthContext`, hooks `useCasAuth`/`useCasUser`, components
`CasLoginButton`/`CasProtectedRoute`, shared types. `/server`: `CasClient`
(`validateToken`/`generateToken`/`logout`/`getLoginUrl`, static `hasRoles`/`hasAnyRole`) + cookie/session helpers
(HMAC HttpOnly cookie signed with `CAS_COOKIE_SECRET || CAS_CLIENT_SECRET`). `/handlers`:
`createCallbackHandler`/`createLogoutHandler`/`createUserHandler`. `/middleware`: `createCasMiddleware`. Config:
`serverUrl/clientId/clientSecret/callbackUrl` + env vars.

**Endpoints used:** `POST /api/validate-token` (was `/api/sso/validate`); `POST /api/sso/token`;
`POST /api/logout` (Bearer + `{ client_id }`); `GET /sso/login?client_id=...&response_type=token&redirect_uri=...`.

**Issues found:**
- **High (FIXED):** `validateToken` posted to `/api/sso/validate` and parsed `data.success` (always undefined) →
  **every validation returned null, login could never succeed**. Fixed: `/api/validate-token`, gate
  `data.valid && data.user`.
- **High (FIXED):** `generateToken` parsed `data.success`, but `/api/sso/token` returns `{ redirect_url, token }`
  → always null. Fixed: gate on `data.token`.
- **High (FIXED):** `package.json` pointed at `dist/index.js`/`dist/server/index.js` but **no `src/index.ts` /
  `src/server/index.ts` existed** → unresolvable after build. Fixed: created the barrels.
- **Medium (FIXED):** Sent non-protocol `X-Timestamp`/`X-Signature` HMAC headers → removed (and removed unused
  `hmacSha256` to avoid `noUnusedLocals` failure).
- **Medium (FIXED):** `files[]` listed README that did not exist → created an accurate README.
- **Low (FIXED):** Internal types modeled wrong shape (`{success,message}`) → aligned to
  `{valid,user,expires_at,error}` and `{redirect_url,token,error}`.
- **Low (reported):** Login redirect still appends `response_type`/`redirect_uri`.
- **Low (reported):** `CasUser.roles` never populated by validate-token response → role helpers are no-ops.

**Fixes applied:** endpoint + `data.valid` gate; `generateToken` `data.token` gate; removed HMAC headers + helper;
rewrote types; created `src/index.ts` + `src/server/index.ts` barrels; created README; fixed stale
`/api/sso/validate` doc comment in `handlers/callback.ts`.

**Remaining concerns:** login redirect extra params; roles not populated; **no build/install was run — a `tsc`
build must confirm `dist/index.js`, `dist/server/index.js`, `dist/middleware.js`, `dist/handlers/index.js` are all
emitted**; single-use depends on server enforcement.

---

### 3.5 `@cas-system/node-cas-client` (Node.js) — minor-issues

**API summary:** Single `CasClient` (default export) with `{ serverUrl, clientId, clientSecret, callbackUrl,
signatureSecret?, enableSignatureValidation?, timeout?, verifySsl? }`. Methods: `getLoginUrl()`,
`generateSSOToken(username)`, `validateToken(token)`, `getUserFromToken` (md5-keyed cache, 60min TTL),
`logout(token?)`, role helpers; private `_generateSignature()` (`sha256=<hex>`). `src/middleware.js`:
`{ casAuth, casRole }` Express middleware. Uses axios.

**Endpoints used:** `POST /api/validate-token` (was `/api/sso/validate`); `GET /sso/login?client_id=...`;
`POST /api/sso/token`; `POST /api/logout`.

**Issues found:**
- **High (FIXED):** `validateToken()` used alt `/api/sso/validate` → fixed to `/api/validate-token` (URL +
  signature URI).
- **High (FIXED):** Package-name drift — manifest is `@cas-system/node-cas-client` but README install/require and
  `@module` JSDoc said `@insol-dev/node-cas-client` → aligned to `@cas-system/...`.
- **Medium (FIXED):** Ignored `valid` flag → now requires `response.data.valid && response.data.user`.
- **Medium (FIXED):** `getLoginUrl()` emitted `response_type`/`redirect_uri` → now `client_id` only.
- **Low (FIXED):** `types: src/index.d.ts` pointed at a non-existent file → removed.
- **Low (reported):** Test script runs `node test/test.js` but there is no `test/` directory.
- **Low (reported):** `repository.url` is `github.com/insol-dev/...` — inconsistent with the `@one-system` scope.
- **Low (reported):** Validated-token cache is in-memory only (not multi-instance safe).

**Fixes applied:** endpoint + signature URI; `valid && user` gate; `getLoginUrl()` `client_id` only (JSDoc + API
table); `@module` rename; removed dead `types`; rewrote README references and `getLoginUrl` examples.

**Remaining concerns:** add tests or fix the test script; correct `repository.url`; in-memory cache not
multi-instance safe; verify `casAuth` default `loginRoute` (`/auth/login`) is wired to `cas.getLoginUrl()` (README
shows a manual route, default does not point there).

---

### 3.6 `cas-client` (python-cas-client) (Python) — minor-issues

**API summary:** `cas_client.CasClient(server_url, client_id, client_secret, callback_url='', signature_secret='',
enable_signature_validation=False, timeout=30, verify_ssl=True)`. Methods: `get_login_url(return_url=None)`,
`generate_sso_token(username)`, `validate_token(token)`, `get_user_from_token(token)` (in-memory cache),
`logout(token=None)`, static role helpers. `cas_client.middleware`: `DjangoCasMiddleware`,
`django_role_required(*roles)`, `flask_cas_required(cas_client)`, `flask_role_required(cas_client, *roles)`.
Optional HMAC signing (off by default).

**Endpoints used:** `POST /api/sso/token`; `POST /api/sso/validate` (alt — conformant); `POST /api/logout`;
`GET /sso/login?client_id=...` (after fix).

**Issues found:**
- **Medium (FIXED):** `get_login_url()` built `response_type`/`redirect_uri` params → now `client_id` only.
- **Medium (FIXED):** `validate_token()` only checked `if 'user' in data`, ignored `valid` → now requires
  `data.get('valid') and 'user' in data`.
- **Low (reported):** Hardcoded 3600s cache TTL ignores `expires_at` (middleware revalidates on miss, so safe).
- **Low (reported):** Targets alt `/api/sso/validate` (conformant per protocol; body keys correct).
- **Low (reported):** README still implies `get_login_url(return_url)` controls post-login redirect (now a no-op).
- **Low (reported):** HMAC signing is a non-protocol extension (off by default, harmless).

**Fixes applied:** `get_login_url()` `client_id` only (+ docstring); `validate_token()` requires
`data.get('valid')` and `user`.

**Remaining concerns:** consider parsing `expires_at` for accurate TTL; clarify `return_url` is not honored;
confirm server accepts (or document as no-op) the HMAC headers; confirm the alt `/api/sso/validate` route is
enabled in the deployment.

---

### 3.7 `@cas-system/react-cas-client` (React / TypeScript) — minor-issues

**API summary:** Named exports: `CasClient` (`getLoginUrl`, `login`, `extractTokenFromUrl`,
`validateTokenViaBackend`, `handleCallback`, getters, `logout`, role helpers); `CasProvider` (+ `CasContext`);
hooks `useCasAuth()`/`useCasUser()`; components `CasProtectedRoute`/`CasLoginButton`; types
`CasConfig{serverUrl,clientId,callbackUrl?,backendValidateUrl?}`, `CasUser{id,username,email,roles?}`, etc.
**Browser never holds `client_secret`** — validation delegated to the consumer's backend via
`config.backendValidateUrl` (browser POSTs `{ token }`); session in `sessionStorage`.

**Endpoints used:** `GET /sso/login?client_id=...&response_type=token&redirect_uri=...`;
`POST {backendValidateUrl}` body `{ token }`; `POST /api/logout` (`credentials: include`); docs now reference
`POST /api/validate-token` for the consumer backend (was `/api/sso/validate`).

**Issues found:**
- **Medium (FIXED):** README Express example forwarded the raw CAS envelope (`await response.json()`) but
  `validateTokenViaBackend` casts to `CasUser` → `id/username/email` undefined. Fixed example to return just
  `user`.
- **Medium (FIXED):** Validate-endpoint drift — README "How It Works" step 5, README example, `cas-client.ts`
  JSDoc, and `types.ts` JSDoc all pointed at `/api/sso/validate` → aligned all four to `/api/validate-token`.
- **Low (FIXED):** `callbackUrl` default JSDoc said `origin + pathname` but code uses `window.location.href` →
  corrected.
- **Low (reported):** `getLoginUrl` appends `response_type`/`redirect_uri` (could be a deliberate returnUrl
  feature) — left as-is.

**Fixes applied:** README example endpoint + `const { user } = ...`; README step 5 endpoint; `cas-client.ts`
JSDoc; `types.ts` JSDoc (backendValidateUrl endpoint + callbackUrl default).

**Remaining concerns:** verify server tolerates/honors the extra login params; the actual server-call conformance
depends on the consumer backend the README documents (SDK cannot enforce); single-use enforced server-side;
consider documenting the bare-`CasUser` backend contract as a typed contract.

---

### 3.8 `@cas-system/vue-cas-client` (Vue 3 / TypeScript) — minor-issues

**API summary:** Named exports: `CasClient` (`getLoginUrl`, `login`, `extractTokenFromUrl`,
`validateTokenViaBackend`, `handleCallback`, getters, `logout`, role helpers, `clearSession`); `CasPlugin`;
composables `useCasAuth()`/`useCasUser()`; `createCasAuthGuard(casContext, options)`; Pinia `useCasStore()`;
`CasProtectedView` component; `CAS_AUTH_KEY`; types. Config: `serverUrl, clientId, callbackUrl?,
backendValidateUrl?, autoHandleCallback?`. Browser never holds `client_secret` — POSTs only `{ token }` to
`backendValidateUrl`.

**Endpoints used:** `GET {serverUrl}/sso/login?client_id={clientId}`; `POST {backendValidateUrl}` body
`{ token }`; `POST /api/logout` (Bearer); documented backend forward to `POST /api/sso/validate` (alt);
`token` query param read via `extractTokenFromUrl`.

**Issues found:**
- **High (FIXED):** `getLoginUrl()` appended `response_type=token`/`redirect_uri=callback` → fixed to
  `{serverUrl}/sso/login?client_id={clientId}` exactly.
- **Low (reported):** Validates via developer backend (correct); README/JSDoc forward to alt `/api/sso/validate`
  (conformant). `validateTokenViaBackend` posts only `{ token }` (correct by design).
- **Low (reported):** `main`/`module`/`types` point at raw `src/index.ts`, no build step — ships uncompiled
  TS/.vue.
- **Low (reported):** `callbackUrl` now unused for building the login URL; `returnUrl` param is a harmless no-op.

**Fixes applied:** `getLoginUrl()` now emits only `client_id` (param renamed `_returnUrl`, ignored, kept for
back-compat; JSDoc updated). `login()` and the router guard still call it safely.

**Remaining concerns:** no compiled output / build step despite `publishConfig.access=public`; clarify in README
that `callbackUrl` is the registered URL, not a value sent on `/sso/login`; consider dropping the ignored
`returnUrl` param; if the deployment only exposes primary `/api/validate-token`, the README backend examples (alt
`/api/sso/validate`) would need adjusting.

---

### 3.9 `cas-system/laravel-client` (PHP / Laravel) — minor-issues (review-only)

**API summary:** Auto-discovered Laravel package (provider `CasClientServiceProvider`, alias `CasClient`). Core
`CasSystem\LaravelClient\Services\CasAuthService`: `getLoginUrl($returnUrl)`, `generateSSOToken($username)`,
`validateToken($token)`, `getUserFromToken($token)`, `logout($token)`, role helpers. Routes (default prefix
`cas`): `GET /cas/login`, `GET /cas/callback`, `POST /cas/logout`, `GET /cas/user`, `POST /cas/auth/validate`,
plus global `POST /auth/validate`. Middleware `cas.auth`/`cas.role`. Config `config/cas-client.php`
(`server_url`, `client_id`, `client_secret`, `callback_url`, signature/cache/logging/user blocks). Ships a
migration, `CasUserTrait`, `cas:install` command, and a separate `SignatureClient` (HMAC, largely buggy).

**Endpoints used:**
- `GET {server_url}/sso/login?client_id=...&response_type=token&redirect_uri=...`
- `POST {server_url}/api/sso/token` body `{ client_id, client_secret, username }`
- `POST {server_url}/api/sso/validate` body `{ token, client_id, client_secret }` (alt; parses `response['user']`)
- `POST {server_url}/api/logout`
- `POST {cas_server_url(null)}/api/sso/validate-token` body `{ token }` — **NON-PROTOCOL path, missing
  client_id/secret** (`SignatureClient`)

**Issues found:**
- **High:** **Single-use token contract violated.** `CasAuthentication` middleware re-runs `validateToken()` on
  any request carrying `?token=` (handle() lines 32 & 43), and `CasController::user()` (lines 122–126)
  re-validates the stored token server-to-server. A genuinely single-use token fails on the second validation,
  breaking the session/user endpoint. The session should be the source of truth after callback.
- **Medium:** `SignatureClient::validateSSOToken()` posts to non-protocol `/api/sso/validate-token` with only
  `{ token }` (missing `client_id`/`client_secret`) and reads `$response['data']['user_data']` (server uses
  `user`). Unused by the main flow but exposed via the `cas-signature-client` binding/facade.
- **Medium:** `SignatureClient` reads `config('cas-client.cas_server_url')` but the config key is `server_url` →
  host is always null; signed requests target an invalid/relative URL.
- **Medium:** Undefined constant `JSON_SORT_KEYS` (line 233) — throws an `Error` on PHP 8+; latent bug in the
  signature-generation path.
- **Low:** `validateToken()` only checks `isset($data['user'])`, ignores `valid` and `expires_at` (acceptable
  because server 401s on failure, but not protocol-faithful; could drive cache TTL).
- **Low:** `getLoginUrl()` sends `response_type=token`/`redirect_uri` (non-protocol; `client_id` is correct).
- **Low:** README/config drift — README documents `CAS_CLIENT_USERNAME`/`CAS_CLIENT_PASSWORD`/
  `CAS_SIGNATURE_SECRET`, `field_mapping`, and `App\Models\User`, but config uses `CAS_CLIENT_ID`/
  `CAS_CLIENT_SECRET`, has no `field_mapping`, and defaults to `App\Models\Auth\User`; README's `roles` example
  is not in the documented validate-token response.
- **Low:** `firebase/php-jwt` is a hard dependency and imported but never used (correct per protocol — dead
  weight).
- **Low:** Package-name vs namespace cosmetic divergence (`cas-system/laravel-client` vs
  `CasSystem\LaravelClient\`); `autoload-dev` Tests namespace but no `tests/` directory.

**Fixes applied:** **None** — the directory is a symlink into a sibling repo
(`smis-cambodia/packages/laravel-cas-client-package`), so no files were modified. All issues are reported only.

**Remaining concerns:**
- **Highest priority:** stop re-validating the single-use token in `CasAuthentication` middleware and
  `CasController::user()`; trust the Laravel session established at callback.
- `SignatureClient` is effectively broken (null host from wrong config key, non-protocol path, missing
  client_id/secret, undefined `JSON_SORT_KEYS`); it is wired as `cas-signature-client` with a facade, so consumers
  can hit these bugs. Remove if legacy, otherwise correct to `POST /api/validate-token` with
  `{ token, client_id, client_secret }` and response key `user`.
- Confirm whether the live server returns `roles`/`role` before relying on role gating.
- README references LICENSE/CONTRIBUTING/CHANGELOG that are not present; MIT declared but no LICENSE shipped.
- Consider verifying the `valid` boolean and using `expires_at` for cache TTL (vs hardcoded 60 minutes).

---

## 4. Recommended follow-ups (prioritized)

### P0 — Correctness defects that break auth or remain unfixed

1. **Laravel: stop re-validating the single-use token.** Remove the re-validation in `CasAuthentication::handle()`
   (lines 32 & 43) and `CasController::user()` (lines 122–126); make the Laravel session the source of truth
   after callback. This is the most severe **unfixed** defect and will break sessions/user endpoint in production
   under genuine single-use enforcement.
2. **Laravel `SignatureClient`: fix or remove.** It is exposed via a facade yet has four compounding bugs (null
   host from `cas_server_url` vs `server_url`, non-protocol `/api/sso/validate-token`, missing
   `client_id`/`client_secret`, undefined `JSON_SORT_KEYS`). Either delete the legacy service or correct it to
   `POST /api/validate-token` with `{ token, client_id, client_secret }` and response key `user`.
3. **Next.js: run a real build.** The high-severity validate/generate/packaging bugs were fixed but **no `tsc`
   build was run**. Verify `dist/index.js`, `dist/server/index.js`, `dist/middleware.js`, and
   `dist/handlers/index.js` all emit from the newly-created barrels before publish.

### P1 — Cross-package roles mismatch (systemic, not auto-fixed)

4. **Resolve the `role` vs `roles` mismatch across all SDKs.** dotnet, java, nextjs, react, node, laravel, and
   angular all expose role helpers that read a `roles` array the documented validate-token response
   (`{ id, username, email }`) does not provide; the JWT carries a singular `role`. Decide how the server surfaces
   roles, then align every SDK. Until then, all role gating is effectively a no-op or always-false.

### P2 — Browser-SDK protocol divergences

5. **Angular: resolve the direct-to-CAS validate fallback.** It posts `{ token, client_id }` (no `client_secret`)
   to `/api/sso/validate` from the browser and cannot succeed. Either require `backendValidateUrl` (like react/vue)
   or remove the fallback.
6. **Angular: fix the logout flow.** Confirm whether a browser GET-logout route exists; otherwise change to
   server-to-server `POST /api/logout` + local session clear.
7. **Unify the login-redirect query params.** Remove non-protocol `response_type`/`redirect_uri` from angular,
   nextjs, react, and laravel/`CasAuthService` (already removed in dotnet, java, node, python, vue), or get a
   written confirmation that the server ignores them. This is the largest remaining consistency gap.

### P3 — Packaging / publish hygiene

8. **Angular packaging:** if a compiled publish is intended, switch `main`→`dist/index.js`, `types`→`dist/*.d.ts`,
   add `dist` to `files`, build with `ngc`.
9. **Vue packaging:** add a build step (currently ships raw `src/index.ts`/.vue despite `publishConfig.access`).
10. **Node:** add tests or fix the `test/test.js` script (no `test/` dir); correct `repository.url`
    (`github.com/insol-dev/...` vs `@one-system` scope).
11. **Laravel:** ship a LICENSE file (MIT declared, none present); remove or implement the empty `tests/` setup;
    remove the unused `firebase/php-jwt` dependency.

### P4 — Documentation, dead weight, and non-protocol extensions

12. **HMAC signing extension:** present (off by default) in dotnet, java, node, python, laravel. Confirm whether
    the server accepts `X-Signature`/`X-Client-ID`/`X-Timestamp`; otherwise document as client-only/no-op and
    consider removing. Fix dotnet's weak `SignatureSecret = "default-signature-secret"` default. De-emphasize the
    HMAC "headline feature" in the java README.
13. **Java:** add a callback handler that reads `?token=` and validates once (protocol step 2); fix the
    `javax` vs `jakarta` README claim (or provide a jakarta build); remove the unused `jjwt-api` dependency.
14. **dotnet:** wire `CasConfig.VerifySsl` into an `HttpClientHandler` or remove the property.
15. **expires_at handling (java, python, laravel):** consider driving cache TTL from server `expires_at` instead
    of a hardcoded 1h/60-min TTL.
16. **Validate-token `valid` flag (laravel):** check the `valid` boolean instead of relying solely on the server's
    401-on-failure behavior.
17. **README contract docs:** angular (show the backend `client_secret`-forwarding shape), react (document the
    bare-`CasUser` backend contract as typed), python/vue (clarify `return_url`/`callbackUrl` are not honored on
    `/sso/login`), laravel (reconcile env-var/model/`field_mapping`/`roles` drift).
