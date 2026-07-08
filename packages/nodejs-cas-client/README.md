# Node.js CAS Client

A Node.js package for seamless integration with CAS (Central Authentication Service) SSO servers. Works with Express, Koa, Fastify, and any Node.js framework.

## Features

- 🔐 **Secure SSO Authentication** — JWT token-based authentication
- 🛡️ **HMAC Signature Validation** — Request signing with SHA-256
- 👥 **Role-Based Access Control** — Middleware for role protection
- ⚡ **Express Middleware** — Drop-in authentication & role middleware
- 📦 **Zero Config** — Works out of the box with environment variables
- 🔄 **Token Caching** — In-memory cache for validated tokens

## Installation

```bash
npm install @cas-system/node-cas-client
```

**Requirements:** Node.js >= 18. The package's only runtime dependency is `axios` (^1.12.0).

## Quick Start

### 1. Initialize the Client

```javascript
const CasClient = require('@cas-system/node-cas-client');

const cas = new CasClient({
  serverUrl: 'https://your-cas-server.com',
  clientId: 'your_client_id',
  clientSecret: 'your_client_secret',
  callbackUrl: 'https://your-app.com/cas/callback',
  // Optional
  signatureSecret: 'your-256-bit-secret',
  enableSignatureValidation: false,
  timeout: 30000,
});
```

### 2. Express Middleware (Recommended)

```javascript
const express = require('express');
const session = require('express-session');
const CasClient = require('@cas-system/node-cas-client');
const { casAuth, casRole } = require('@cas-system/node-cas-client/src/middleware');

const app = express();
app.use(session({ secret: 'session-secret', resave: false, saveUninitialized: false }));

const cas = new CasClient({
  serverUrl: process.env.CAS_SERVER_URL,
  clientId: process.env.CAS_CLIENT_ID,
  clientSecret: process.env.CAS_CLIENT_SECRET,
  callbackUrl: process.env.CAS_CALLBACK_URL,
});

// Protect routes
app.get('/dashboard', casAuth(cas), (req, res) => {
  res.json({ user: req.casUser });
});

// Role-protected routes
app.get('/admin', casAuth(cas), casRole(cas, 'admin'), (req, res) => {
  res.json({ message: 'Admin area', user: req.casUser });
});
```

### 3. Manual Authentication

```javascript
// Login redirect
app.get('/login', (req, res) => {
  const loginUrl = cas.getLoginUrl();
  res.redirect(loginUrl);
});

// Callback handler
app.get('/cas/callback', async (req, res) => {
  const { token } = req.query;
  const user = await cas.validateToken(token);

  if (user) {
    req.session.cas_user = user;
    req.session.cas_token = token;
    return res.redirect('/dashboard');
  }
  return res.redirect('/login?error=authentication_failed');
});

// Logout
app.post('/logout', async (req, res) => {
  await cas.logout(req.session.cas_token);
  req.session.destroy();
  res.redirect('/');
});
```

### 4. Generate SSO Token (Server-to-Server)

```javascript
const tokenData = await cas.generateSSOToken('john_doe');
if (tokenData) {
  console.log('Token:', tokenData.token);
  console.log('Redirect:', tokenData.redirect_url);
}
```

## Configuration

### Environment Variables

```env
CAS_SERVER_URL=https://your-cas-server.com
CAS_CLIENT_ID=your_client_id
CAS_CLIENT_SECRET=your_client_secret
CAS_CALLBACK_URL=https://your-app.com/cas/callback
CAS_SIGNATURE_SECRET=your-256-bit-secret
CAS_ENABLE_SIGNATURE=false
CAS_TIMEOUT=30000
```

## API Reference

| Method | Description |
|--------|-------------|
| `getLoginUrl()` | Generate CAS SSO login URL |
| `generateSSOToken(username)` | Generate SSO token via client credentials |
| `validateToken(token)` | Validate token, returns user data |
| `getUserFromToken(token)` | Get cached user data |
| `logout(token?)` | Logout from CAS server |
| `userHasRole(user, role)` | Check single role |
| `userHasAnyRole(user, roles)` | Check any of roles |
| `userHasAllRoles(user, roles)` | Check all roles |

## License

MIT
