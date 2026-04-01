# .NET CAS Client

A .NET library for seamless integration with CAS (Central Authentication Service) SSO servers. Works with ASP.NET Core 6+.

## Features

- 🔐 **Secure SSO Authentication** — JWT token-based authentication
- 🛡️ **HMAC Signature Validation** — Request signing with SHA-256
- 👥 **Role-Based Access Control** — ASP.NET Core middleware for role protection
- ⚡ **Async/Await** — Fully asynchronous API
- 💉 **Dependency Injection** — Built-in service registration extensions
- 🔄 **Thread-Safe Caching** — ConcurrentDictionary token cache

## Installation

### NuGet

```bash
dotnet add package CasSystem.Client
```

### Package Manager

```powershell
Install-Package CasSystem.Client
```

## Quick Start

### 1. Configure in Program.cs

```csharp
using CasSystem.Client;

var builder = WebApplication.CreateBuilder(args);

// Register CAS client
builder.Services.AddCasClient(new CasConfig
{
    ServerUrl = "https://your-cas-server.com",
    ClientId = "your_client_id",
    ClientSecret = "your_client_secret",
    CallbackUrl = "https://your-app.com/cas/callback",
});

builder.Services.AddSession();

var app = builder.Build();

app.UseSession();
app.UseMiddleware<CasAuthMiddleware>();

app.MapGet("/dashboard", (HttpContext ctx) =>
{
    var user = ctx.Items["cas_user"] as Dictionary<string, object>;
    return Results.Json(new { user });
});

app.Run();
```

### 2. Manual Authentication

```csharp
using CasSystem.Client;

var config = new CasConfig
{
    ServerUrl = "https://your-cas-server.com",
    ClientId = "your_client_id",
    ClientSecret = "your_client_secret",
};
var cas = new CasClient(config);

// Generate login URL
var loginUrl = cas.GetLoginUrl("https://your-app.com/dashboard");

// Validate token
var user = await cas.ValidateTokenAsync(token);
if (user != null)
{
    var username = user["username"].ToString();
}

// Role checks
bool isAdmin = CasClient.UserHasRole(user, "admin");

// Logout
await cas.LogoutAsync(token);
```

### 3. Controller Usage

```csharp
[ApiController]
[Route("api/[controller]")]
public class DashboardController : ControllerBase
{
    private readonly CasClient _cas;
    public DashboardController(CasClient cas) => _cas = cas;

    [HttpGet]
    public IActionResult Index()
    {
        var user = HttpContext.Items["cas_user"] as Dictionary<string, object>;
        if (user == null) return Unauthorized();
        return Ok(new { user });
    }
}
```

## API Reference

| Method | Description |
|--------|-------------|
| `GetLoginUrl(returnUrl?)` | Generate CAS login URL |
| `GenerateSSOTokenAsync(username)` | Generate SSO token |
| `ValidateTokenAsync(token)` | Validate token, returns user dict |
| `GetUserFromToken(token)` | Get cached user data |
| `LogoutAsync(token?)` | Logout from CAS server |
| `UserHasRole(user, role)` | Check single role |
| `UserHasAnyRole(user, roles)` | Check any of roles |
| `UserHasAllRoles(user, roles)` | Check all roles |

## License

MIT
