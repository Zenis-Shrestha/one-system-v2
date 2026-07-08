# Dependency Freshness Report

_Generated: 2026-06-18_

> **Confidence note:** Version data marked **(approx.)** was sourced from model knowledge (cutoff Jan 2026) because the relevant package registry was unreachable from the audit environment. All other versions were confirmed live against the respective registry (npm / NuGet / Maven Central / PyPI / Packagist).

---

## 1. Summary

| Component | Ecosystem | Runtime target (EOL?) | # Deps | # Outdated |
|---|---|---|---|---|
| `@cas-system/angular-cas-client` | npm (Angular/TS library) | Angular peer floor `>=17` — **floor admits EOL Angular 17** (TS target ES2022) | 4 | 3 |
| `dotnet-cas-client` (CasSystem.Client) | NuGet (.NET) | **net6.0 — EOL (2024-11-12)** | 2 | 2 |
| `java-cas-client` (cas-client) | Maven (Java) | **Java 11 — past free/community maintenance** (LTS 17/21 current) | 5 | 4 |
| `@cas-system/js-cas-client` | npm (vanilla JS, browser UMD) | Browser; no Node runtime pinned — nothing to flag | 0 | 0 |
| `nextjs-cas-client` | npm (Next.js/TS) | **Node 20 — maintenance LTS, EOL ~Apr 2026 (near/at EOL)** | 6 | 6 |
| `nodejs-cas-client` | npm (Node.js) | **Node 16 — EOL (Sept 2023)** | 1 | 1 |
| `python-cas-client` | PyPI (Python) | **Python 3.8 — EOL (2024-10-07)** | 3 | 3 |
| `@cas-system/react-cas-client` | npm (React/TS) | No runtime pinned (TS target ES2020) — nothing to flag | 3 | 2 |
| `@cas-system/vue-cas-client` | npm (Vue 3/TS library) | Vue 3 peer (`>=3.3`) — not EOL | 3 | 0 |
| `pkg:laravel` (laravel-cas-client-package) | Composer (Laravel/PHP) | **PHP constraint caps at 8.0 — every permitted runtime is EOL** | 6 | 5 |
| `server:composer` (CAS server) | Composer (PHP) | PHP `^8.2` — not EOL (security support ~Dec 2026) | 17 | 14 |
| `server:npm` (CAS server frontend) | npm | No engines field; Vite 6 toolchain 2 majors behind | 6 | 6 |

---

## 2. Needs attention

This section lists every dependency (and runtime) that is **major-behind**, **EOL**, and/or **vulnerable**. "Latest" values marked **(approx.)** are knowledge-sourced.

### Runtime / target-framework EOL

| Component | Runtime target | Status | Fix |
|---|---|---|---|
| `dotnet-cas-client` | net6.0 (.NET 6) | **EOL since 2024-11-12** — drags ASP.NET Core 6 shared framework into EOL | Retarget to `net8.0` (LTS) or `net10.0` |
| `nodejs-cas-client` | Node 16 (`engines.node ">=16.0.0"`) | **EOL since Sept 2023** | Raise minimum to Node 20 LTS or later |
| `python-cas-client` | Python 3.8 (`python_requires='>=3.8'`) | **EOL 2024-10-07** (3.9 EOL 2025-10) | Raise floor to `>=3.9`, ideally `>=3.10` |
| `pkg:laravel` | PHP `^7.2 7.3 7.4 8.0` | **All EOL** (8.0 EOL 2023-11); excludes supported 8.1+ | Raise floor to `^8.1` |
| `nextjs-cas-client` | Node 20 (`@types/node ^20`) | Maintenance LTS, **EOL ~Apr 2026** (near/at EOL) | Move to Node 22/24 LTS; bump `@types/node`; add `engines` field |
| `java-cas-client` | Java 11 (`maven.compiler.source/target=11`) | Past free/community maintenance (OpenJDK 11 updates ended Oct 2024) | Migrate to LTS 17 or 21 |
| `@cas-system/angular-cas-client` | Angular peer floor `>=17.0.0` | Floor **admits EOL Angular 17.x** (left active+LTS ~mid-2025) | Raise peer floor to `>=18` or `>=19` |

### Dependencies needing attention

| Component | Dependency | Declared | Latest | Status / risk | Fix |
|---|---|---|---|---|---|
| `dotnet-cas-client` | `System.Text.Json` | 7.0.3 | 10.0.9 | **major-behind + VULNERABLE** — 7.x EOL; 7.0.3 predates DoS fix **CVE-2024-43485** (GHSA-8g4q-xg66-9fp4) | Bump to `8.0.5+` (ideally 10.x), or let it flow from the shared framework after retargeting |
| `dotnet-cas-client` | `Microsoft.AspNetCore.App` | net6.0 (FrameworkReference) | net10.0 (approx.) | **major-behind + EOL** — ASP.NET Core 6 shared framework is EOL | Retarget project to `net8.0`/`net10.0` |
| `nodejs-cas-client` | `axios` | ^1.6.0 | 1.18.0 | **VULNERABLE floor** — `^1.6.0` permits `<1.8.0`, affected by **CVE-2025-27152** (SSRF/credential leak); also CVE-2023-45857 at floor boundary. No lockfile = non-deterministic | Raise to `>=1.8.0` (ideally `^1.18.0`) **and commit a lockfile** |
| `python-cas-client` | `django` | >=3.2 | 6.0.6 | **major-behind (3 majors) + EOL/VULNERABLE** — 3.2 LTS EOL Apr 2024; known CVEs (CVE-2024-27351 ReDoS, CVE-2023-46695 DoS, 3.2.x SQLi fixes) | Pin to supported LTS `>=4.2` (ideally 5.2/6.0) |
| `python-cas-client` | `flask` | >=2.0 | 3.1.3 | **major-behind** — Flask 2.x past active maintenance | Raise floor to `>=3.0` (needs Werkzeug 3.x) |
| `python-cas-client` | `requests` | >=2.28.0 | 2.34.2 | minor-behind but **floor predates security fixes** — CVE-2023-32681 (fixed 2.31.0), CVE-2024-35195 (fixed 2.32.0) | Raise floor to `>=2.32` |
| `pkg:laravel` | `laravel/framework` | ^7.0 … ^12.0 | v13.16.1 | **major-behind** — range includes EOL majors 7–10; omits current 13.x | Add `^13.0`, drop EOL majors |
| `pkg:laravel` | `firebase/php-jwt` | ^6.0 | v7.1.0 | **major-behind** — 7.0 dropped PHP 7.x, tightened key handling | Bump to `^7.0` |
| `pkg:laravel` | `phpunit/phpunit` (dev) | ^10.0 | 13.2.1 | **major-behind (3 majors)** | Bump to `^11.0`+ |
| `pkg:laravel` | `orchestra/testbench` (dev) | ^8.0 | v11.1.0 | **major-behind (3 majors)** — TB 8 targets Laravel 10 | Bump to `^9`–`^11` for Laravel 11/12 |
| `pkg:laravel` | `guzzlehttp/guzzle` | ^7.0 | 7.12.1 | current major, but **no lockfile** can resolve `<7.4.5` (CVE-2022-31090, CVE-2022-31091) | Ensure install resolves `>=7.4.5` (commit a lockfile) |
| `server:composer` | `laravel/framework` | ^12.0 | v13.16.1 | **major-behind** (locked v12.53.0; latest within ^12) | Bump constraint to `^13.0` |
| `server:composer` | `livewire/livewire` | ^3.6 | v4.3.1 | **major-behind** (locked v3.7.11) | Bump constraint to `^4.0` |
| `server:composer` | `phpunit/phpunit` (dev) | ^11.5.3 | 13.2.1 | **major-behind** (locked 11.5.55) | Bump constraint (→ 12/13) |
| `server:composer` | `phpmailer/phpmailer` | ^6.10 | v7.1.1 | **major-behind** (locked v6.12.0) — historical RCE/header-injection CVE target | Track/upgrade to `^7.0`; keep current |
| `server:composer` | `pragmarx/google2fa` | ^8.0 | v9.0.0 | **major-behind** (locked v8.0.3) | Bump to `^9.0` (pair with google2fa-laravel ^3) |
| `server:composer` | `pragmarx/google2fa-laravel` | ^2.3 | v3.0.1 | **major-behind** (locked v2.3.0) | Bump to `^3.0` |
| `server:composer` | `laravel/tinker` | ^2.10.1 | v3.0.2 | **major-behind** (locked v2.11.1) — v3 aligns with Laravel 13 | Bump to `^3.0` |
| `server:composer` | `firebase/php-jwt` | `*` | v7.1.0 | **major-behind + unpinned wildcard** (locked v6.11.1) — uncontrolled major upgrades; JWT-validation CVE risk | Pin to `^6.11` or migrate to `^7.0` |
| `server:npm` | `vite` (dev) | ^6.2.4 | 8.0.16 | **major-behind (2 majors)** (locked 6.4.1) — 6.x no longer maintained stable line | Upgrade to Vite 8 (coordinate with laravel-vite-plugin) |
| `server:npm` | `laravel-vite-plugin` (dev) | ^1.2.0 | 3.1.0 | **major-behind (2 majors)** (locked 1.3.0) — coupled to Vite major | Upgrade to `3.x` together with Vite |
| `server:npm` | `concurrently` (dev) | ^9.0.1 | 10.0.3 | **major-behind** (locked 9.2.1) | Bump to `^10` |
| `server:npm` | `axios` | ^1.8.2 | 1.18.0 | locked 1.13.2 NOT vulnerable, but **floor `^1.8.2` resolves to CVE-2025-58754-affected version** on lockless install (fixed 1.12.0) | Raise floor; rely on committed lockfile |
| `java-cas-client` | `com.squareup.okhttp3:okhttp` | 4.12.0 | 5.4.0 | **major-behind** — 4.x EOL-track; 5.x is maintained branch | Upgrade to 5.x (update Kotlin stdlib transitives) |
| `java-cas-client` | `io.jsonwebtoken:jjwt-api` | 0.12.3 | 0.12.6 | patch-behind but **security-sensitive (JWT)**; runtime modules `jjwt-impl` + `jjwt-gson/jjwt-jackson` **missing from pom.xml** | Update to 0.12.6; verify/add runtime impl modules |
| `java-cas-client` | `javax.servlet:javax.servlet-api` | 4.0.1 | 4.0.1 | current in namespace, but **`javax.*` is frozen/EOL** (Java EE) | Plan migration to `jakarta.servlet:jakarta.servlet-api` 6.x (Tomcat 10+/Jetty 11+) |
| `@cas-system/angular-cas-client` | `@angular/core` | >=17.0.0 (peer) | 20.1.x (approx.) | **major-behind** — floor admits EOL Angular 17 | Raise floor to `>=18`/`>=19` |
| `@cas-system/angular-cas-client` | `@angular/common` | >=17.0.0 (peer) | 20.1.x (approx.) | **major-behind** — same lockstep concern | Raise floor to `>=18`/`>=19` |
| `@cas-system/angular-cas-client` | `@angular/router` | >=17.0.0 (peer) | 20.1.x (approx.) | **major-behind** — same lockstep concern | Raise floor to `>=18`/`>=19` |

> Dependencies that are minor/patch-behind but **within constraint and not security-flagged** (e.g. `gson`, `slf4j-api`, `bacon/bacon-qr-code`, `laravel/pint`, `laravel/sail`, `@tailwindcss/vite`, `tailwindcss`, etc.) are listed in the per-component tables below rather than here.

---

## 3. Per-component detail

### `@cas-system/angular-cas-client` — npm (Angular/TS library)

No direct/dev dependencies; 4 peerDependencies only. Versions **(approx.)** are knowledge-sourced (npm registry unreachable).

| Name | Declared | Latest | Status |
|---|---|---|---|
| `@angular/core` | >=17.0.0 (peer) | 20.1.x (approx.) | major-behind |
| `@angular/common` | >=17.0.0 (peer) | 20.1.x (approx.) | major-behind |
| `@angular/router` | >=17.0.0 (peer) | 20.1.x (approx.) | major-behind |
| `rxjs` | >=7.0.0 (peer) | 7.8.x (approx.) | current (root lockfile resolves 7.8.2; 8.x still pre-release) |

### `dotnet-cas-client` (CasSystem.Client) — NuGet (.NET)

Manifest: `/Users/rajankalyan/PhpstormProjects/one-system-final/one-system/packages/dotnet-cas-client/CasSystem.Client.csproj`. No `packages.lock.json`.

| Name | Declared | Latest | Status |
|---|---|---|---|
| `System.Text.Json` | 7.0.3 | 10.0.9 | major-behind + vulnerable (CVE-2024-43485) |
| `Microsoft.AspNetCore.App` | net6.0 (FrameworkReference) | net10.0 (approx.) | major-behind + EOL |

### `java-cas-client` (cas-client 2.0.0) — Maven (Java)

| Name | Declared | Latest | Status |
|---|---|---|---|
| `com.squareup.okhttp3:okhttp` | 4.12.0 | 5.4.0 | major-behind |
| `com.google.code.gson:gson` | 2.10.1 | 2.14.0 | minor-behind |
| `io.jsonwebtoken:jjwt-api` | 0.12.3 | 0.12.6 | patch-behind (security-sensitive; runtime modules missing) |
| `javax.servlet:javax.servlet-api` | 4.0.1 | 4.0.1 | current (but `javax.*` namespace frozen/EOL) |
| `org.slf4j:slf4j-api` | 2.0.9 | 2.0.17 | patch-behind |

### `@cas-system/js-cas-client` — npm (vanilla JS, browser UMD)

Zero declared dependencies; nothing to assess. Manifest: `/Users/rajankalyan/PhpstormProjects/one-system-final/one-system/packages/javascript-cas-client/package.json`.

| Name | Declared | Latest | Status |
|---|---|---|---|
| _(none)_ | — | — | — |

### `nextjs-cas-client` — npm (Next.js/TS)

Manifest: `/Users/rajankalyan/PhpstormProjects/one-system-final/one-system/packages/nextjs-cas-client/package.json`. No production deps; peer ranges intentionally wide, dev/types pins bound the build.

| Name | Declared | Latest | Status |
|---|---|---|---|
| `next` | ^14.0.0 (dev); >=14 (peer) | 16.2.9 | major-behind (locked 14.2.35) |
| `react` | ^18.0.0 (dev); >=18 (peer) | 19.2.7 | major-behind (locked 18.3.1) |
| `react-dom` | ^18.0.0 (dev); >=18 (peer) | 19.2.7 | major-behind (locked 18.3.1) |
| `@types/react` | ^18.0.0 | 19.2.17 | major-behind (types-only; locked 18.3.31) |
| `@types/node` | ^20.0.0 | 25.9.3 | major-behind (types-only; locked 20.19.43) |
| `typescript` | ^5.0.0 | 6.0.3 | major-behind (locked 5.9.3; low urgency) |

> Note: locked `next` 14.2.35 is **not** vulnerable to the middleware auth-bypass CVE-2025-29927 (fixed in 14.2.25+). All locked versions are on the latest patch within their pinned major.

### `nodejs-cas-client` — npm (Node.js)

Manifest: `/Users/rajankalyan/PhpstormProjects/one-system-final/one-system/packages/nodejs-cas-client/package.json`. No lockfile.

| Name | Declared | Latest | Status |
|---|---|---|---|
| `axios` | ^1.6.0 | 1.18.0 | minor-behind, but floor permits vulnerable `<1.8.0` (CVE-2025-27152) |

### `python-cas-client` (setup.py, v2.0.0) — PyPI (Python)

No lockfile; constraints are open-ended `>=` floors.

| Name | Declared | Latest | Status |
|---|---|---|---|
| `requests` | >=2.28.0 | 2.34.2 | minor-behind (floor predates CVE-2023-32681 / CVE-2024-35195 fixes) |
| `django` (extra) | >=3.2 | 6.0.6 | major-behind + EOL/vulnerable |
| `flask` (extra) | >=2.0 | 3.1.3 | major-behind |

### `@cas-system/react-cas-client` — npm (React/TS)

No package-level lockfile; 1 peer + 2 dev deps.

| Name | Declared | Latest | Status |
|---|---|---|---|
| `react` | >=18 (peer) | 19.2.7 | current (peer range already permits 19.x) |
| `@types/react` | ^18.0.0 (dev) | 19.2.17 | major-behind (types-only) |
| `typescript` | ^5.0.0 (dev) | 6.0.3 | major-behind (build-time only) |

### `@cas-system/vue-cas-client` — npm (Vue 3/TS library)

No hard deps; open-ended peer/optional ranges. 0 formally outdated, but lower bounds cross major boundaries.

| Name | Declared | Latest | Status |
|---|---|---|---|
| `vue` | >=3.3 (peer) | 3.5.38 | current |
| `vue-router` | >=4 (optional peer) | 5.1.0 | current (range spans 4→5 major — verify router-guard compat) |
| `pinia` | >=2 (optional peer) | 3.0.4 | current (range spans 2→3 major — verify store API compat) |

> Side issue (not a version flag): the `typecheck` script uses `vue-tsc`/`typescript`, neither declared in this package nor the workspace root.

### `pkg:laravel` (laravel-cas-client-package) — Composer (Laravel/PHP)

Manifest: `/Users/rajankalyan/PhpstormProjects/one-system-final/one-system/packages/laravel-cas-client-package/composer.json`. No `composer.lock` (declared constraints only).

| Name | Declared | Latest | Status |
|---|---|---|---|
| `php` | ^7.2 7.3 7.4 8.0 | 8.4.x (approx.) | major-behind + EOL runtime |
| `laravel/framework` | ^7.0 … ^12.0 | v13.16.1 | major-behind (includes EOL majors 7–10) |
| `firebase/php-jwt` | ^6.0 | v7.1.0 | major-behind |
| `guzzlehttp/guzzle` | ^7.0 | 7.12.1 | current (ensure install ≥7.4.5 — CVE-2022-31090/31091) |
| `phpunit/phpunit` (dev) | ^10.0 | 13.2.1 | major-behind |
| `orchestra/testbench` (dev) | ^8.0 | v11.1.0 | major-behind |

### `server:composer` (CAS server) — Composer (PHP)

17 deps (10 runtime incl. php, 7 dev). Lockfile is internally healthy (each package at/near newest allowed by its constraint); the **constraints** trail the ecosystem.

| Name | Declared | Latest | Status |
|---|---|---|---|
| `php` | ^8.2 | 8.4.x (approx.) | current (not EOL; consider raising floor to ^8.3) |
| `bacon/bacon-qr-code` | ^3.0 | v3.1.1 | minor-behind (locked v3.0.3) |
| `firebase/php-jwt` | `*` | v7.1.0 | major-behind + unpinned wildcard (locked v6.11.1) |
| `josiasmontag/laravel-recaptchav3` | ^1.0 | 1.0.5 | patch-behind (locked 1.0.4) |
| `laravel/framework` | ^12.0 | v13.16.1 | major-behind (locked v12.53.0) |
| `laravel/tinker` | ^2.10.1 | v3.0.2 | major-behind (locked v2.11.1) |
| `livewire/livewire` | ^3.6 | v4.3.1 | major-behind (locked v3.7.11) |
| `phpmailer/phpmailer` | ^6.10 | v7.1.1 | major-behind (locked v6.12.0; historical CVE target) |
| `pragmarx/google2fa` | ^8.0 | v9.0.0 | major-behind (locked v8.0.3) |
| `pragmarx/google2fa-laravel` | ^2.3 | v3.0.1 | major-behind (locked v2.3.0) |
| `fakerphp/faker` (dev) | ^1.23 | v1.24.1 | current (locked v1.24.1) |
| `laravel/pail` (dev) | ^1.2.2 | v1.2.7 | patch-behind (locked v1.2.6) |
| `laravel/pint` (dev) | ^1.13 | v1.29.3 | minor-behind (locked v1.27.1) |
| `laravel/sail` (dev) | ^1.41 | v1.62.0 | minor-behind (locked v1.53.0) |
| `mockery/mockery` (dev) | ^1.6 | 1.6.12 | current (locked 1.6.12) |
| `nunomaduro/collision` (dev) | ^8.6 | v8.9.4 | patch-behind (locked v8.9.1) |
| `phpunit/phpunit` (dev) | ^11.5.3 | 13.2.1 | major-behind (locked 11.5.55) |

### `server:npm` (CAS server frontend) — npm

Manifest: `/Users/rajankalyan/PhpstormProjects/one-system-final/one-system/package.json` (lock: `package-lock.json`). All 6 devDependencies behind latest; no dep on a known-vulnerable **locked** version.

| Name | Declared | Latest | Status |
|---|---|---|---|
| `@tailwindcss/vite` | ^4.0.0 | 4.3.1 | minor-behind (locked 4.1.18) |
| `axios` | ^1.8.2 | 1.18.0 | minor-behind (locked 1.13.2 — safe; floor unsafe lockless) |
| `concurrently` | ^9.0.1 | 10.0.3 | major-behind (locked 9.2.1) |
| `laravel-vite-plugin` | ^1.2.0 | 3.1.0 | major-behind (locked 1.3.0) |
| `tailwindcss` | ^4.0.0 | 4.3.1 | minor-behind (locked 4.1.18) |
| `vite` | ^6.2.4 | 8.0.16 | major-behind (locked 6.4.1) |

---

## 4. Upgrade recommendations (ordered)

Ordered by risk: EOL runtimes and known-vulnerable dependencies first, then major-version debt, then routine minor/patch hygiene.

1. **`dotnet-cas-client` — retarget off EOL .NET 6.** Move project to `net8.0` (LTS) or `net10.0`. This fixes both the EOL runtime and the EOL ASP.NET Core 6 shared framework. Then either let `System.Text.Json` flow from the shared framework, or pin it to `8.0.5+`/`10.x` to clear **CVE-2024-43485**.
2. **`nodejs-cas-client` — close the axios SSRF window.** Raise the constraint to `>=1.8.0` (ideally `^1.18.0`) to escape **CVE-2025-27152**, **commit a lockfile**, and raise `engines.node` off EOL Node 16 to Node 20 LTS+.
3. **`python-cas-client` — drop EOL Django + raise floors.** Move `django` to a supported LTS (`>=4.2`, ideally 5.2/6.0) to clear the 3.2 EOL CVEs; raise `requests` floor to `>=2.32` (CVE-2023-32681 / CVE-2024-35195); raise `flask` to `>=3.0`; raise `python_requires` to `>=3.9` (ideally `>=3.10`).
4. **`pkg:laravel` — raise the PHP floor and Laravel range.** Set PHP to `^8.1` (every currently-permitted runtime is EOL); add `laravel/framework` `^13.0` and drop EOL majors 7–10; bump `firebase/php-jwt` to `^7.0`; ensure `guzzle` resolves `>=7.4.5` (commit a lockfile); update dev deps `phpunit` (`^11+`) and `orchestra/testbench` (`^9`–`^11`).
5. **`server:composer` — coordinated major upgrade.** Do Laravel 12→13 + Livewire 3→4 + PHPUnit 11→13 + google2fa 8→9 (with google2fa-laravel 2→3) + tinker 2→3 together; bump `phpmailer` 6→7; and **pin `firebase/php-jwt`** off its `*` wildcard (`^6.11` or `^7.0`). PHP `^8.2` is fine; optionally raise to `^8.3`.
6. **`server:npm` — Vite toolchain major bump.** Upgrade `vite` 6→8 together with `laravel-vite-plugin` 1→3 (coupled), verify against the Tailwind v4 Vite plugin, and bump `concurrently` 9→10. Keep `tailwindcss` + `@tailwindcss/vite` in lockstep at 4.3.1. The lockfile keeps axios safe today; still raise the floor.
7. **`java-cas-client` — upgrade okhttp and harden JWT.** Bump `okhttp` 4.12.0→5.x (update Kotlin stdlib transitives); update `jjwt-api` to 0.12.6 **and confirm the runtime `jjwt-impl` + serializer module are actually provided** (currently missing from pom.xml). Then plan the `javax`→`jakarta.servlet` 6.x migration and the Java 11→17/21 move.
8. **`@cas-system/angular-cas-client` — raise the EOL peer floor.** Bump the `@angular/*` peerDependency floor from `>=17` to `>=18`/`>=19` so consumers are steered off EOL Angular 17. `rxjs >=7.0.0` is fine.
9. **`nextjs-cas-client` — move off near-EOL Node 20.** Bump `@types/node` to 22/24 and add an `engines` field for Node 22/24 LTS. Optionally validate against React 19 / Next 15+ (peer ranges already permit them) and adopt TS 6 when ready. No security urgency (locked versions clean).
10. **`@cas-system/react-cas-client` — align types.** Bump `@types/react` to `^19` to match the `>=18` peer range that already allows React 19; adopt TS `^6` when convenient. Dev/build-only; no urgency.
11. **`@cas-system/vue-cas-client` — tighten loose ranges.** Add upper bounds (`vue-router '>=4 <6'`, `pinia '>=2 <4'`) to avoid silently pulling untested majors (vue-router 5, pinia 3), and raise the `vue` floor if 3.4+ features are used. Also declare `vue-tsc`/`typescript` used by the typecheck script. No outdated deps.
12. **Routine hygiene (within-constraint minor/patch).** `java-cas-client`: `gson` 2.10.1→2.14.0, `slf4j-api` 2.0.9→2.0.17. `server:composer`: `bacon/bacon-qr-code`, `laravel-recaptchav3`, `pint`, `sail`, `pail`, `collision`. `server:npm`: `tailwindcss`/`@tailwindcss/vite` to 4.3.1. These are drop-in.
13. **No action needed.** `@cas-system/js-cas-client` (zero deps). Within `@cas-system/vue-cas-client`, all current. If deps are added to `js-cas-client` later, introduce a lockfile and an `engines` field.
