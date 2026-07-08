# rust-cas-client

A small, framework-agnostic Rust client for the **One System CAS** SSO protocol.
It is the Rust counterpart to the `@cas-system/node-cas-client`, `cas-client`
(Python), and Java/.NET SDKs in this repo.

The crate does two things:

1. **Build the login redirect** — `{CAS_BASE}/sso/login?client_id=ID`.
2. **Validate the single-use token server-to-server** — POST
   `{token, client_id, client_secret}` to `{CAS_BASE}/api/validate-token` and
   parse the returned user.

The `client_secret` is only ever used server-side and is never exposed to the
browser.

## Install (path dependency)

```toml
[dependencies]
rust-cas-client = { path = "../../packages/rust-cas-client" }
```

## Usage

```rust
use rust_cas_client::{CasClient, CasConfig};

let cas = CasClient::new(CasConfig {
    server_url: std::env::var("CAS_BASE_URL").unwrap(),       // server-side origin
    client_id: std::env::var("CAS_CLIENT_ID").unwrap(),       // e.g. "rust-sample"
    client_secret: std::env::var("CAS_CLIENT_SECRET").unwrap(),
    callback_url: std::env::var("CAS_CALLBACK_URL").unwrap(), // browser-facing
})?;

// 1) Redirect the browser here:
let url = cas.login_url()?;        // -> {CAS_BASE}/sso/login?client_id=rust-sample

// 2) At the callback (?token=...), validate exactly once:
if let Some(user) = cas.validate_token(&token).await? {
    // create your own application session for `user`
}
```

## API

| Method | Description |
| --- | --- |
| `CasClient::new(CasConfig)` | Construct; errors if required fields are empty. |
| `login_url()` | Build `{CAS_BASE}/sso/login?client_id=ID`. |
| `validate_token(&token)` | Server-to-server validation → `Some(CasUser)` / `None`. |
| `logout()` | Best-effort `POST /api/logout` notification. |
| `client_id()` / `callback_url()` | Config accessors. |

`CasUser` exposes `id`, `username`, `email`, and optional `roles`.

## Notes

- The CAS token is **single-use** — call `validate_token` exactly once per
  callback, then trust your own session.
- `validate_token` POSTs `{token, client_id, client_secret}` to
  `/api/validate-token` (and also sends an `X-Client-ID` header). On a `200`
  with `{"valid": true, "user": {...}, "expires_at": ...}` it returns
  `Ok(Some(CasUser))`; any non-200, `valid != true`, or unparseable body yields
  `Ok(None)`, and a transport failure yields `Err(CasError::Http)`.
- `server_url` is the address used for the **server-side** validation call. In
  the Docker deployment that is the internal host (e.g. `http://one-system-cas`),
  while `callback_url` is the public, browser-facing host.

## Test

```bash
cargo test
```
