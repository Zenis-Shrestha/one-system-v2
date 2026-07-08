@extends('public.documentation.layout')

@section('title', 'Java integration — One System CAS SSO')
@section('description', 'Integrate Java and Spring Boot applications with One System CAS single sign-on using the cas-client library.')

@section('content')
<section class="border-b border-[var(--color-line)] pb-10 mb-12">
    <div class="">
        <div class="flex items-center gap-3 mb-4">
            <span class="os-icon-tile os-icon-tile-ink">
                <i class="fab fa-java"></i>
            </span>
            <div>
                <p class="os-eyebrow">Integration guide</p>
                <h1 class="text-3xl font-semibold text-[var(--color-ink)] tracking-tight leading-tight">{{ $javaGuide['title'] }}</h1>
            </div>
        </div>
        <p class="text-lg text-[var(--color-muted)] leading-relaxed mb-5">{{ $javaGuide['description'] }}</p>
        <div class="flex flex-wrap gap-2">
            <span class="os-badge"><i class="fas fa-clock"></i> 8 min setup</span>
            <span class="os-badge"><i class="fas fa-signal"></i> Intermediate</span>
            <span class="os-badge">Java 17+</span>
            <span class="os-badge">cas-client 2.0.0</span>
        </div>
    </div>
</section>

<nav class="mb-12 os-card os-card-pad">
    <h2 class="text-xs font-semibold text-[var(--color-faint)] uppercase tracking-widest mb-3">On this page</h2>
    <ol class="space-y-1.5 text-sm text-[var(--color-accent)]">
        <li><a href="#dependency" class="hover:text-[var(--color-accent-strong)]">1. Add the dependency</a></li>
        <li><a href="#client" class="hover:text-[var(--color-accent-strong)]">2. Configure the client</a></li>
        <li><a href="#filter" class="hover:text-[var(--color-accent-strong)]">3. Register the auth filter</a></li>
        <li><a href="#callback" class="hover:text-[var(--color-accent-strong)]">4. Handle the SSO callback</a></li>
        <li><a href="#manual" class="hover:text-[var(--color-accent-strong)]">5. Manual auth &amp; roles</a></li>
        <li><a href="#reference" class="hover:text-[var(--color-accent-strong)]">6. API reference</a></li>
    </ol>
</nav>

<section class="mb-12">
    <p class="text-[var(--color-ink-2)] leading-relaxed">
        The <code class="os-code-inline">cas-client</code> library wraps the One System CAS SSO protocol for any Java
        framework. It ships a programmatic <code class="os-code-inline">CasClient</code>, a configuration builder
        (<code class="os-code-inline">CasConfig</code>), and a drop-in servlet filter
        (<code class="os-code-inline">CasAuthFilter</code>) for protecting routes. It depends on OkHttp, Gson,
        JJWT, and SLF4J, targets Java 17, and is built against the
        <code class="os-code-inline">javax.servlet</code> API (servlet-api 4.0.1) — so it works with Spring Boot 2.7.x,
        Jakarta EE 8 / Java EE servlet containers, and plain Java.
    </p>
</section>

<section id="dependency" class="mb-12">
    <h2 class="text-xl font-semibold text-[var(--color-ink)] mb-2">1. Add the dependency</h2>
    <p class="text-[var(--color-muted)] mb-4">Add <code class="os-code-inline">io.github.insol-dev:cas-client</code> to your build.</p>

    <div class="os-codeblock mb-4">
        <div class="os-codeblock-head"><span>pom.xml</span><span>Maven</span></div>
        <pre><code>&lt;dependency&gt;
    &lt;groupId&gt;io.github.insol-dev&lt;/groupId&gt;
    &lt;artifactId&gt;cas-client&lt;/artifactId&gt;
    &lt;version&gt;2.0.0&lt;/version&gt;
&lt;/dependency&gt;</code></pre>
    </div>

    <div class="os-codeblock">
        <div class="os-codeblock-head"><span>build.gradle</span><span>Gradle</span></div>
        <pre><code>implementation 'io.github.insol-dev:cas-client:2.0.0'</code></pre>
    </div>

    <div class="os-alert mt-4">
        <i class="fas fa-info-circle mt-0.5 text-[var(--color-accent)]"></i>
        <div>
            Requires <strong>Java 17+</strong>. The package targets the
            <code class="os-code-inline">javax.servlet</code> API (servlet-api 4.0.1), so use
            <strong>Spring Boot 2.7.x</strong> — Spring Boot 3 moved to
            <code class="os-code-inline">jakarta.servlet</code> and is not binary-compatible with
            <code class="os-code-inline">CasAuthFilter</code>. Transitive dependencies are OkHttp 4.12, Gson 2.10,
            JJWT 0.12, and SLF4J 2.0.
        </div>
    </div>
</section>

<section id="client" class="mb-12">
    <h2 class="text-xl font-semibold text-[var(--color-ink)] mb-2">2. Configure the client</h2>
    <p class="text-[var(--color-muted)] mb-4">
        Build a <code class="os-code-inline">CasConfig</code> with your CAS server URL, client ID, and client
        secret, then expose a singleton <code class="os-code-inline">CasClient</code>. Keep the client secret in
        the environment — it is only ever used server to server.
    </p>

    <div class="os-codeblock mb-4">
        <div class="os-codeblock-head"><span>CasConfiguration.java</span><span>Spring Boot</span></div>
        <pre><code>import com.cassystem.client.CasClient;
import com.cassystem.client.CasConfig;
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;

@Configuration
public class CasConfiguration {

    @Bean
    public CasClient casClient() {
        CasConfig config = new CasConfig(
            System.getenv("CAS_SERVER_URL"),     // e.g. https://your-cas-server.com
            System.getenv("CAS_CLIENT_ID"),
            System.getenv("CAS_CLIENT_SECRET")
        ).callbackUrl("https://your-app.com/cas/callback");

        return new CasClient(config);
    }
}</code></pre>
    </div>

    <div class="os-alert mb-2">
        <i class="fas fa-info-circle mt-0.5 text-[var(--color-accent)]"></i>
        <div>
            <code class="os-code-inline">CasConfig</code> is a fluent builder. Optional setters include
            <code class="os-code-inline">timeoutSeconds(int)</code>, <code class="os-code-inline">verifySsl(boolean)</code>,
            <code class="os-code-inline">enableSignatureValidation(boolean)</code>, and
            <code class="os-code-inline">signatureSecret(String)</code> for HMAC-SHA256 request signing.
        </div>
    </div>
</section>

<section id="filter" class="mb-12">
    <h2 class="text-xl font-semibold text-[var(--color-ink)] mb-2">3. Register the auth filter</h2>
    <p class="text-[var(--color-muted)] mb-4">
        <code class="os-code-inline">CasAuthFilter</code> is a standard servlet filter. Register it with a
        <code class="os-code-inline">FilterRegistrationBean</code> and map the URL patterns you want to protect.
        It looks for an authenticated user in the session or a <code class="os-code-inline">Bearer</code> token in the
        <code class="os-code-inline">Authorization</code> header, and otherwise redirects to your login URL (or returns
        <code class="os-code-inline">401</code> for JSON requests). Pass required roles to gate by role.
    </p>

    <div class="os-codeblock">
        <div class="os-codeblock-head"><span>CasConfiguration.java</span><span>Filter registration</span></div>
        <pre><code>import com.cassystem.client.CasAuthFilter;
import com.cassystem.client.CasClient;
import org.springframework.boot.web.servlet.FilterRegistrationBean;
import org.springframework.context.annotation.Bean;

@Bean
public FilterRegistrationBean&lt;CasAuthFilter&gt; casAuthFilter(CasClient casClient) {
    FilterRegistrationBean&lt;CasAuthFilter&gt; reg = new FilterRegistrationBean&lt;&gt;();
    reg.setFilter(new CasAuthFilter(casClient));
    reg.addUrlPatterns("/dashboard/*", "/profile/*");
    reg.setOrder(1);
    return reg;
}

@Bean
public FilterRegistrationBean&lt;CasAuthFilter&gt; casAdminFilter(CasClient casClient) {
    FilterRegistrationBean&lt;CasAuthFilter&gt; reg = new FilterRegistrationBean&lt;&gt;();
    // (casClient, loginUrl, requiredRoles...)
    reg.setFilter(new CasAuthFilter(casClient, "/auth/login", "admin"));
    reg.addUrlPatterns("/admin/*");
    reg.setOrder(2);
    return reg;
}</code></pre>
    </div>

    <div class="os-alert mt-4">
        <i class="fas fa-info-circle mt-0.5 text-[var(--color-accent)]"></i>
        <div>
            The filter stores the validated user under the <code class="os-code-inline">cas_user</code> session
            attribute and also exposes it as a request attribute, so downstream controllers can read it with
            <code class="os-code-inline">request.getAttribute("cas_user")</code>.
        </div>
    </div>
</section>

<section id="callback" class="mb-12">
    <h2 class="text-xl font-semibold text-[var(--color-ink)] mb-2">4. Handle the SSO callback</h2>
    <p class="text-[var(--color-muted)] mb-4">
        Redirect unauthenticated users to <code class="os-code-inline">casClient.getLoginUrl()</code>. The CAS server
        authenticates them and redirects back to your registered <code class="os-code-inline">callbackUrl</code> with a
        single-use <code class="os-code-inline">token</code> query parameter. Validate it server to server, then store the
        user in the session to create your app's own session.
    </p>

    <div class="os-codeblock">
        <div class="os-codeblock-head"><span>CasController.java</span><span>Spring MVC</span></div>
        <pre><code>import com.cassystem.client.CasClient;
import javax.servlet.http.HttpSession;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.*;

import java.util.Map;

@Controller
public class CasController {

    private final CasClient casClient;

    public CasController(CasClient casClient) {
        this.casClient = casClient;
    }

    // Kick off SSO: send the browser to the CAS server
    @GetMapping("/auth/login")
    public String login() {
        return "redirect:" + casClient.getLoginUrl();
    }

    // Registered callback_url — token arrives as a query param
    @GetMapping("/cas/callback")
    public String callback(@RequestParam("token") String token, HttpSession session) {
        // Server-to-server validation (single use)
        Map&lt;String, Object&gt; user = casClient.validateToken(token);

        if (user == null) {
            return "redirect:/auth/login?error=invalid_token";
        }

        // Create the app's own session
        session.setAttribute("cas_user", user);
        return "redirect:/dashboard";
    }

    @PostMapping("/auth/logout")
    public String logout(@RequestParam(value = "token", required = false) String token,
                         HttpSession session) {
        casClient.logout(token);
        session.invalidate();
        return "redirect:/";
    }
}</code></pre>
    </div>

    <div class="os-alert mt-4">
        <i class="fas fa-shield-halved mt-0.5 text-[var(--color-accent)]"></i>
        <div>
            <code class="os-code-inline">validateToken</code> POSTs <code class="os-code-inline">{ token, client_id, client_secret }</code>
            to <code class="os-code-inline">/api/validate-token</code>. On success the server returns
            <code class="os-code-inline">{ valid: true, user: {...}, expires_at }</code> and the library hands you the
            <code class="os-code-inline">user</code> map. Tokens are single-use — validate once, then rely on your session.
        </div>
    </div>
</section>

<section id="manual" class="mb-12">
    <h2 class="text-xl font-semibold text-[var(--color-ink)] mb-2">5. Manual auth &amp; roles</h2>
    <p class="text-[var(--color-muted)] mb-4">
        Beyond the filter, <code class="os-code-inline">CasClient</code> exposes helpers for token validation,
        service-to-service token issuance, and role checks. The user map contains
        <code class="os-code-inline">id</code>, <code class="os-code-inline">username</code>,
        <code class="os-code-inline">email</code>, and a <code class="os-code-inline">roles</code> list.
    </p>

    <div class="os-codeblock">
        <div class="os-codeblock-head"><span>Usage.java</span><span>CasClient API</span></div>
        <pre><code>// Build the CAS login URL (server resolves the registered callback)
String loginUrl = casClient.getLoginUrl();

// Validate a single-use token -> user map, or null
Map&lt;String, Object&gt; user = casClient.validateToken(token);
if (user != null) {
    String username = (String) user.get("username");
    String email    = (String) user.get("email");
}

// Read a previously validated user from the in-memory cache
Map&lt;String, Object&gt; cached = casClient.getUserFromToken(token);

// Service-to-service token issuance (IP-whitelisted, uses client credentials)
Map&lt;String, Object&gt; tokenData = casClient.generateSSOToken("john_doe");
// -> { redirect_url, token }

// Role checks
boolean isAdmin   = casClient.userHasRole(user, "admin");
boolean isStaff   = casClient.userHasAnyRole(user, "admin", "manager");
boolean isTrusted = casClient.userHasAllRoles(user, "user", "verified");

// Logout (clears the cache entry and notifies the CAS server)
casClient.logout(token);</code></pre>
    </div>
</section>

<section id="reference" class="mb-12">
    <h2 class="text-xl font-semibold text-[var(--color-ink)] mb-4">6. API reference</h2>

    <div class="os-card overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-[var(--color-line)] bg-[var(--color-surface-2)] text-left">
                    <th class="px-4 py-2.5 font-semibold text-[var(--color-ink-2)]">Method</th>
                    <th class="px-4 py-2.5 font-semibold text-[var(--color-ink-2)]">Description</th>
                </tr>
            </thead>
            <tbody class="text-[var(--color-ink-2)]">
                <tr class="border-b border-[var(--color-line)]">
                    <td class="px-4 py-2.5"><code class="os-code-inline">getLoginUrl([returnUrl])</code></td>
                    <td class="px-4 py-2.5 text-[var(--color-muted)]">Build the CAS SSO login URL for the configured client.</td>
                </tr>
                <tr class="border-b border-[var(--color-line)]">
                    <td class="px-4 py-2.5"><code class="os-code-inline">validateToken(token)</code></td>
                    <td class="px-4 py-2.5 text-[var(--color-muted)]">Validate a token server to server; returns the user map or <code class="os-code-inline">null</code>.</td>
                </tr>
                <tr class="border-b border-[var(--color-line)]">
                    <td class="px-4 py-2.5"><code class="os-code-inline">getUserFromToken(token)</code></td>
                    <td class="px-4 py-2.5 text-[var(--color-muted)]">Return the cached user for a token, if still valid.</td>
                </tr>
                <tr class="border-b border-[var(--color-line)]">
                    <td class="px-4 py-2.5"><code class="os-code-inline">generateSSOToken(username)</code></td>
                    <td class="px-4 py-2.5 text-[var(--color-muted)]">Issue an SSO token for a user via client credentials (IP-whitelisted).</td>
                </tr>
                <tr class="border-b border-[var(--color-line)]">
                    <td class="px-4 py-2.5"><code class="os-code-inline">logout(token)</code></td>
                    <td class="px-4 py-2.5 text-[var(--color-muted)]">Invalidate the cached token and notify the CAS server.</td>
                </tr>
                <tr class="border-b border-[var(--color-line)]">
                    <td class="px-4 py-2.5"><code class="os-code-inline">userHasRole(user, role)</code></td>
                    <td class="px-4 py-2.5 text-[var(--color-muted)]">Check a single role.</td>
                </tr>
                <tr class="border-b border-[var(--color-line)]">
                    <td class="px-4 py-2.5"><code class="os-code-inline">userHasAnyRole(user, roles...)</code></td>
                    <td class="px-4 py-2.5 text-[var(--color-muted)]">Check whether the user has any of the roles.</td>
                </tr>
                <tr>
                    <td class="px-4 py-2.5"><code class="os-code-inline">userHasAllRoles(user, roles...)</code></td>
                    <td class="px-4 py-2.5 text-[var(--color-muted)]">Check whether the user has all of the roles.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="os-alert os-alert-success mt-6">
        <i class="fas fa-check-circle mt-0.5"></i>
        <div><strong>Done.</strong> The filter intercepts protected routes, validates CAS tokens, and populates the
            <code class="os-code-inline">cas_user</code> attribute for your controllers.</div>
    </div>
</section>

<section class="border-t border-[var(--color-line)] pt-10">
    <h2 class="text-xs font-semibold text-[var(--color-faint)] uppercase tracking-widest mb-4">Next steps</h2>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="{{ route('docs.api.overview') }}" class="os-card os-card-hover flex items-center gap-3 p-4">
            <i class="fas fa-code text-[var(--color-muted)]"></i>
            <span class="text-sm font-medium text-[var(--color-ink-2)]">API reference</span>
        </a>
        <a href="{{ route('docs.security') }}" class="os-card os-card-hover flex items-center gap-3 p-4">
            <i class="fas fa-shield-halved text-[var(--color-muted)]"></i>
            <span class="text-sm font-medium text-[var(--color-ink-2)]">Security guide</span>
        </a>
        <a href="{{ route('docs.deployment') }}" class="os-card os-card-hover flex items-center gap-3 p-4">
            <i class="fas fa-server text-[var(--color-muted)]"></i>
            <span class="text-sm font-medium text-[var(--color-ink-2)]">Deployment</span>
        </a>
    </div>
</section>
@endsection
