# Python CAS Client

A Python package for seamless integration with CAS (Central Authentication Service) SSO servers. Works with Django, Flask, FastAPI, and any Python framework.

## Features

- 🔐 **Secure SSO Authentication** — JWT token-based authentication
- 🛡️ **HMAC Signature Validation** — Request signing with SHA-256
- 👥 **Role-Based Access Control** — Decorators for role protection
- 🐍 **Django & Flask Support** — Built-in middleware and decorators
- 📦 **Type Hints** — Full typing support for IDE autocomplete
- 🔄 **Token Caching** — In-memory cache for validated tokens

## Installation

```bash
pip install cas-client

# With Django support
pip install cas-client[django]

# With Flask support
pip install cas-client[flask]
```

## Quick Start

### 1. Initialize the Client

```python
from cas_client import CasClient

cas = CasClient(
    server_url='https://your-cas-server.com',
    client_id='your_client_id',
    client_secret='your_client_secret',
    callback_url='https://your-app.com/cas/callback',
)
```

### 2. Django Integration

```python
# settings.py
from cas_client import CasClient

CAS_CLIENT = CasClient(
    server_url=os.environ['CAS_SERVER_URL'],
    client_id=os.environ['CAS_CLIENT_ID'],
    client_secret=os.environ['CAS_CLIENT_SECRET'],
)
CAS_PROTECTED_PATHS = ['/dashboard', '/admin']
CAS_LOGIN_URL = '/auth/login'

MIDDLEWARE = [
    ...
    'cas_client.middleware.DjangoCasMiddleware',
]

# views.py
from cas_client.middleware import django_role_required

@django_role_required('admin')
def admin_view(request):
    user = request.cas_user
    return JsonResponse({'user': user['username']})
```

### 3. Flask Integration

```python
from cas_client import CasClient
from cas_client.middleware import flask_cas_required, flask_role_required

cas = CasClient(
    server_url='https://your-cas-server.com',
    client_id='your_client_id',
    client_secret='your_client_secret',
)

@app.route('/dashboard')
@flask_cas_required(cas)
def dashboard():
    user = flask.g.cas_user
    return f'Hello {user["username"]}'

@app.route('/admin')
@flask_cas_required(cas)
@flask_role_required(cas, 'admin')
def admin():
    return 'Admin area'
```

### 4. Manual Authentication

```python
# Generate SSO token
token_data = cas.generate_sso_token('john_doe')

# Validate token
user = cas.validate_token(token)

# Role checks
if cas.user_has_role(user, 'admin'):
    print('User is admin')

# Logout
cas.logout(token)
```

## API Reference

| Method | Description |
|--------|-------------|
| `get_login_url(return_url)` | Generate CAS login URL |
| `generate_sso_token(username)` | Generate SSO token |
| `validate_token(token)` | Validate token, returns user dict |
| `get_user_from_token(token)` | Get cached user data |
| `logout(token)` | Logout from CAS server |
| `user_has_role(user, role)` | Check single role |
| `user_has_any_role(user, roles)` | Check any of roles |
| `user_has_all_roles(user, roles)` | Check all roles |

## License

MIT
