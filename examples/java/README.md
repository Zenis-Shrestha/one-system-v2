# One System CAS - Java (Spring Boot) sample

A small but real app with its **own local username/password accounts** that ALSO
supports the **`java-cas-client`** package (`io.github.insol-dev:cas-client`) CAS single
sign-on flow. A user can sign in **either way**.

## Local accounts (SQLite)

Local accounts live in a SQLite file (`./data/app.db`) accessed via Spring's
`JdbcTemplate` over the `org.xerial:sqlite-jdbc` driver. On startup, if the
`users` table is empty, two demo users are seeded with **salted PBKDF2 hashes**
(never plaintext):

| Username | Password |
|----------|----------|
| `rajan`  | `rajan123` |
| `demo`   | `demo123`  |

| Endpoint | What it does |
|----------|--------------|
| `GET /login` | Renders a small username/password form (plus a "Sign in with CAS SSO" link). |
| `POST /login` (form) | Validates against the SQLite store. On success, establishes the app's **own** session (the same `cas_user` session attribute the CAS flow uses) and redirects to `/`. On failure, re-renders the form with an error. |
| `POST /login` (validation) | **CAS link-validation contract.** When the body contains `client_validation` (the CAS server POSTs `{"username","password","client_validation":true}`), responds with JSON `{"success":true}` (HTTP 200) for valid credentials or `{"success":false}` (HTTP 401) for invalid - and does **not** create a browser session. Detected by the `client_validation` field and/or `Accept: application/json`. |

The store lives in `LocalUserStore.java`; the routes in `LoginController.java`.

## CAS single sign-on

It also demonstrates the full SSO loop:

| Step | Endpoint | What it does |
|------|----------|--------------|
| (a) trigger login | `GET /cas/login` | Redirects the browser to `{CAS_BASE}/sso/login?client_id=...` via `CasClient.getLoginUrl()`. (Linked from the `/login` form.) |
| (b) handle callback | `GET /callback?token=<JWT>` | Validates the single-use token **server-to-server** with `CasClient.validateToken(token)` (which POSTs to `{CAS_BASE}/api/validate-token` using the `client_secret`), then stores the user in our own HTTP session. |
| (c) show user | `GET /` and `GET /profile` | `/` shows whoever is signed in - local **or** CAS - and a logout link. `/profile` is **guarded by the package's `CasAuthFilter`** (unauthenticated requests are redirected to `/login`). |
| (d) logout | `GET /logout` | Clears our session and best-effort calls `CasClient.logout()`. |

> **Route change:** the CAS-SSO redirect moved from `GET /login` to `GET /cas/login`,
> because `/login` is now the local username/password form. `/profile`'s
> `CasAuthFilter` still redirects unauthenticated users to `/login`, where they can
> pick local sign-in or the CAS SSO link.

The JWT is HS256, signed with a secret only the CAS server holds. This app never
holds the JWT secret - it always validates by calling `/api/validate-token`
through the package.

## What it uses from the package

- `com.cassystem.client.CasConfig(serverUrl, clientId, clientSecret).callbackUrl(...)`
- `com.cassystem.client.CasClient` - `getLoginUrl()`, `validateToken(token)`, `logout(token)`
- `com.cassystem.client.CasAuthFilter` - registered via Spring's `FilterRegistrationBean` to protect `/profile/*`

The wiring lives in:

- `CasConfiguration.java` - builds the `CasClient` bean and registers `CasAuthFilter`
- `CasController.java` - the home page, `/cas/login`, `/callback`, `/profile`, `/logout`
- `LoginController.java` - the local `/login` form + the CAS link-validation contract
- `LocalUserStore.java` - the SQLite-backed user store (schema, seeding, PBKDF2)

Local auth adds `org.springframework.boot:spring-boot-starter-jdbc` and
`org.xerial:sqlite-jdbc` to the build.

> **Servlet API note:** the package targets `javax.servlet` (servlet-api 4.0.1),
> so this sample uses **Spring Boot 2.7.x** (also `javax.servlet`). Spring Boot 3
> moved to `jakarta.servlet` and would not be binary-compatible with the
> package's `CasAuthFilter`.

## Prerequisites

- JDK 17+ (tested on Temurin 17)
- Maven 3.6+
- A running One System CAS server
- This client registered on that CAS server (see below)

## CAS client registration

Register this app on the CAS server with:

- **client_id**: `java-sample` (or your own; set `CAS_CLIENT_ID`)
- **client_secret**: a secret you also set as `CAS_CLIENT_SECRET`
- **callback_url**: `http://localhost:9105/callback` (must match `CAS_CALLBACK_URL` exactly)

## Configuration

Copy `.env.example` to `.env` and adjust:

| Variable | Meaning | Default |
|----------|---------|---------|
| `CAS_BASE_URL` | CAS server origin | `http://localhost:8080` |
| `CAS_CLIENT_ID` | registered client id | `java-sample` |
| `CAS_CLIENT_SECRET` | registered client secret (server-side only) | `changeme-client-secret` |
| `CAS_CALLBACK_URL` | registered callback URL | `http://localhost:9105/callback` |
| `APP_PORT` | port this app listens on | `9105` |
| `APP_DB_URL` | SQLite JDBC URL for the local user store (dir must be writable) | `jdbc:sqlite:./data/app.db` |

`application.properties` reads each value as `${ENV_VAR:default}`, so the app
also starts with localhost defaults if no env is set.

## Install the local package (one-time)

There is no published artifact. Install the local `java-cas-client` package into
your local Maven repository once, then this sample resolves it normally:

```bash
cd ../../packages/java-cas-client
mvn install -DskipTests
```

This publishes `io.github.insol-dev:cas-client:2.0.0` to `~/.m2/repository`.

## Run (local)

```bash
# from examples/java/
set -a; . ./.env; set +a          # export your .env into the shell
mvn spring-boot:run
```

Or build and run the fat jar:

```bash
mvn package -DskipTests
java -jar target/cas-java-sample.jar
```

Then open **http://localhost:9105** and click *Login with CAS SSO*.

## Run with Docker

The Docker build needs the package source, so the **build context is the
`one-system/` repo root** (the Dockerfile installs the package, then builds the
sample):

```bash
# from one-system/ (the repo root)
docker build -f examples/java/Dockerfile -t one-system-cas-java .
docker run --rm -p 9105:9105 --env-file examples/java/.env one-system-cas-java
```

## Port

This sample is assigned port **9105**.
