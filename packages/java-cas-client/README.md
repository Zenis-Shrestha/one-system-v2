# Java CAS Client

A Java library for seamless integration with CAS (Central Authentication Service) SSO servers. Works with Spring Boot, Jakarta Servlet, and any Java framework.

## Features

- 🔐 **Secure SSO Authentication** — JWT token-based authentication
- 🛡️ **HMAC Signature Validation** — Request signing with SHA-256
- 👥 **Role-Based Access Control** — Servlet filter for role protection
- ☕ **Spring Boot Compatible** — Drop-in filter registration
- 🔄 **Thread-Safe Caching** — ConcurrentHashMap token cache
- 📦 **Java 11+** — Modern Java with OkHttp and Gson

## Installation

### Maven

```xml
<dependency>
    <groupId>com.cassystem</groupId>
    <artifactId>cas-client</artifactId>
    <version>2.0.0</version>
</dependency>
```

### Gradle

```groovy
implementation 'com.cassystem:cas-client:2.0.0'
```

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

## License

MIT
