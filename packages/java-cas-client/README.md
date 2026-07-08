# Java CAS Client

A Java library for seamless integration with One System CAS (Central Authentication Service) SSO servers. Works with Spring Boot, Java EE / Jakarta EE 8 servlet containers, and any Java framework on the `javax.servlet` API.

## Features

- 🔐 **Secure SSO Authentication** — JWT token-based authentication
- 🛡️ **HMAC Signature Validation** — Request signing with SHA-256
- 👥 **Role-Based Access Control** — Servlet filter for role protection
- ☕ **Spring Boot Compatible** — Drop-in filter registration (Spring Boot 2.7.x)
- 🔄 **Thread-Safe Caching** — ConcurrentHashMap token cache
- 📦 **Java 17** — Built for Java 17 with OkHttp, Gson, JJWT, and SLF4J

## Installation

### Maven

```xml
<dependency>
    <groupId>io.github.insol-dev</groupId>
    <artifactId>cas-client</artifactId>
    <version>2.0.0</version>
</dependency>
```

### Gradle

```groovy
implementation 'io.github.insol-dev:cas-client:2.0.0'
```

## Requirements

- **Java 17+** (the package compiles to Java 17 bytecode).
- Built against the **`javax.servlet`** API (servlet-api 4.0.1). With Spring Boot, use **2.7.x** — Spring Boot 3 switched to `jakarta.servlet` and is **not** binary-compatible with `CasAuthFilter`.
- Pulls in OkHttp 4.12, Gson 2.10, JJWT 0.12 (`jjwt-api` + runtime `jjwt-impl` / `jjwt-jackson`), and SLF4J 2.0.

## Quick Start

### 1. Initialize the Client

```java
import com.cassystem.client.CasClient;
import com.cassystem.client.CasConfig;

CasConfig config = new CasConfig(
    "https://your-cas-server.com",
    "your_client_id",
    "your_client_secret"
).callbackUrl("https://your-app.com/cas/callback");

CasClient cas = new CasClient(config);
```

### 2. Spring Boot Filter (Recommended)

```java
import com.cassystem.client.*;
import org.springframework.boot.web.servlet.FilterRegistrationBean;
import org.springframework.context.annotation.*;

@Configuration
public class CasConfiguration {

    @Bean
    public CasClient casClient() {
        CasConfig config = new CasConfig(
            System.getenv("CAS_SERVER_URL"),
            System.getenv("CAS_CLIENT_ID"),
            System.getenv("CAS_CLIENT_SECRET")
        );
        return new CasClient(config);
    }

    @Bean
    public FilterRegistrationBean<CasAuthFilter> casAuthFilter(CasClient casClient) {
        FilterRegistrationBean<CasAuthFilter> reg = new FilterRegistrationBean<>();
        reg.setFilter(new CasAuthFilter(casClient));
        reg.addUrlPatterns("/dashboard/*", "/profile/*");
        reg.setOrder(1);
        return reg;
    }

    @Bean
    public FilterRegistrationBean<CasAuthFilter> casAdminFilter(CasClient casClient) {
        FilterRegistrationBean<CasAuthFilter> reg = new FilterRegistrationBean<>();
        reg.setFilter(new CasAuthFilter(casClient, "/auth/login", "admin"));
        reg.addUrlPatterns("/admin/*");
        reg.setOrder(2);
        return reg;
    }
}
```

### 3. Manual Authentication

```java
// Generate login URL
String loginUrl = cas.getLoginUrl("https://your-app.com/dashboard");

// Validate token
Map<String, Object> user = cas.validateToken(token);
if (user != null) {
    String username = (String) user.get("username");
    String email = (String) user.get("email");
}

// Generate SSO token (server-to-server)
Map<String, Object> tokenData = cas.generateSSOToken("john_doe");

// Role checks
boolean isAdmin = cas.userHasRole(user, "admin");
boolean isManager = cas.userHasAnyRole(user, "admin", "manager");
boolean hasBoth = cas.userHasAllRoles(user, "user", "verified");

// Logout
cas.logout(token);
```

## API Reference

| Method | Description |
|--------|-------------|
| `getLoginUrl(returnUrl)` | Generate CAS login URL |
| `generateSSOToken(username)` | Generate SSO token |
| `validateToken(token)` | Validate token, returns user Map |
| `getUserFromToken(token)` | Get cached user data |
| `logout(token)` | Logout from CAS server |
| `userHasRole(user, role)` | Check single role |
| `userHasAnyRole(user, roles...)` | Check any of roles |
| `userHasAllRoles(user, roles...)` | Check all roles |

## CAS endpoints

The client talks to these One System CAS endpoints (relative to the configured server URL):

| Call | Endpoint | Request | Response |
|------|----------|---------|----------|
| `getLoginUrl()` | `GET /sso/login?client_id=...` | browser redirect | CAS redirects back to the registered `callback_url` with `?token=<JWT>` |
| `validateToken(token)` | `POST /api/validate-token` | `{ token, client_id, client_secret }` | `{ valid: true, user: { id, username, email }, expires_at }` — token is **single-use** |
| `generateSSOToken(username)` | `POST /api/sso/token` | `{ client_id, client_secret, username }` | `{ redirect_url, token }` |
| `logout(token)` | `POST /api/logout` | — | clears the cache entry and notifies the server |

## License

MIT
