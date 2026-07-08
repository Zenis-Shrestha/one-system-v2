# JavaScript CAS Client (Browser)

A lightweight JavaScript SDK for browser-based CAS SSO integration. No build tools required — works with a simple `<script>` tag (UMD: exposes a global `CasClient`, and is `require()`-able via CommonJS).

## Features

- 🔐 **SSO Login/Logout** — One-click login redirects
- 🔄 **Token Handling** — Extract tokens from callback URLs
- 🛡️ **Backend Validation** — Send tokens to your server for secure validation
- 👥 **Role Checking** — Client-side role-based UI control
- 📦 **Zero Dependencies** — Pure vanilla JavaScript, UMD compatible
- 💾 **Session Storage** — Persists user data across page navigations

> **⚠️ Important:** Never validate tokens in the browser. This SDK sends tokens to your backend for server-side validation.

## Installation

### Script Tag (CDN)

```html
<script src="https://your-cas-server.com/assets/js/cas-client.js"></script>
```

### npm

```bash
npm install @cas-system/js-cas-client
```

## Quick Start

### 1. Initialize

```html
<script src="https://your-cas-server.com/assets/js/cas-client.js"></script>
<script>
  var cas = new CasClient({
    serverUrl: 'https://your-cas-server.com',
    clientId: 'your_client_id',
    callbackUrl: 'https://your-app.com/cas/callback',
    backendValidateUrl: '/api/auth/validate',  // Your backend endpoint
  });
</script>
```

### 2. Login Button

```html
<button onclick="cas.login()">Login with CAS</button>

<!-- Or with a return URL -->
<button onclick="cas.login('/dashboard')">Login</button>
```

### 3. Callback Page

```html
<!-- On your callback page (e.g., /cas/callback) -->
<script>
  cas.handleCallback().then(function(user) {
    if (user) {
      console.log('Welcome,', user.username);
      window.location.href = cas.consumeReturnUrl() || '/dashboard';
    } else {
      alert('Login failed');
      window.location.href = '/login';
    }
  });
</script>
```

### 4. Protected Pages

```html
<script>
  if (!cas.isAuthenticated()) {
    cas.login(window.location.href);
  }

  var user = cas.getUser();
  document.getElementById('username').textContent = user.username;

  // Role-based UI
  if (cas.userHasRole('admin')) {
    document.getElementById('admin-panel').style.display = 'block';
  }
</script>
```

### 5. Logout

```html
<button onclick="cas.logout('/')">Logout</button>
```

## API Reference

| Method | Description |
|--------|-------------|
| `login(returnUrl?)` | Redirect to CAS login (stashes `returnUrl` for after the callback) |
| `getLoginUrl()` | Get login URL without redirect |
| `consumeReturnUrl()` | Read + clear the `returnUrl` stashed by `login()` |
| `handleCallback()` | Extract + validate token on callback page |
| `extractTokenFromUrl()` | Extract token from URL query string |
| `validateTokenViaBackend(token)` | Send token to backend for validation |
| `getUser()` | Get stored user data |
| `isAuthenticated()` | Check if user is logged in |
| `logout(redirectUrl?)` | Clear session and redirect to CAS logout |
| `userHasRole(role)` | Check single role |
| `userHasAnyRole(roles)` | Check any of roles |
| `userHasAllRoles(roles)` | Check all roles |

## Backend Validation Contract

This browser SDK never holds your `client_secret` and never validates tokens itself.
`validateTokenViaBackend(token)` POSTs `{ "token": "<jwt>" }` to your `backendValidateUrl`.
Your backend must then validate the token **server-to-server** against the CAS server:

```
POST {CAS_BASE}/api/validate-token        Content-Type: application/json
{ "token": "<jwt>", "client_id": "...", "client_secret": "..." }

200 → { "valid": true, "user": { "id", "username", "email" }, "expires_at": "..." }
401 → { "error": "<message>" }
```

The token is **single-use** — validate it once, then establish your own app session. Your
backend endpoint should return a JSON body containing a `user` object back to the browser.

## License

MIT
