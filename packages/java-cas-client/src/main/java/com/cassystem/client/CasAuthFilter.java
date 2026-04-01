package com.cassystem.client;

import javax.servlet.*;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.servlet.http.HttpSession;
import java.io.IOException;
import java.util.Arrays;
import java.util.Map;

/**
 * Servlet filter for CAS authentication.
 *
 * <p>Register in web.xml or Spring Boot:</p>
 * <pre>
 * &#64;Bean
 * public FilterRegistrationBean&lt;CasAuthFilter&gt; casFilter() {
 *     FilterRegistrationBean&lt;CasAuthFilter&gt; reg = new FilterRegistrationBean&lt;&gt;();
 *     CasConfig config = new CasConfig("https://your-cas-server.com", "id", "secret");
 *     reg.setFilter(new CasAuthFilter(new CasClient(config)));
 *     reg.addUrlPatterns("/dashboard/*", "/admin/*");
 *     return reg;
 * }
 * </pre>
 */
public class CasAuthFilter implements Filter {
    private final CasClient casClient;
    private String loginUrl = "/auth/login";
    private String[] requiredRoles = null;

    public CasAuthFilter(CasClient casClient) {
        this.casClient = casClient;
    }

    public CasAuthFilter(CasClient casClient, String loginUrl) {
        this.casClient = casClient;
        this.loginUrl = loginUrl;
    }

    public CasAuthFilter(CasClient casClient, String loginUrl, String... requiredRoles) {
        this.casClient = casClient;
        this.loginUrl = loginUrl;
        this.requiredRoles = requiredRoles;
    }

    @Override
    public void doFilter(ServletRequest request, ServletResponse response, FilterChain chain)
            throws IOException, ServletException {
        HttpServletRequest httpReq = (HttpServletRequest) request;
        HttpServletResponse httpRes = (HttpServletResponse) response;
        HttpSession session = httpReq.getSession(false);

        // Check session
        @SuppressWarnings("unchecked")
        Map<String, Object> casUser = session != null ?
                (Map<String, Object>) session.getAttribute("cas_user") : null;

        if (casUser == null) {
            // Check Authorization header
            String authHeader = httpReq.getHeader("Authorization");
            if (authHeader != null && authHeader.startsWith("Bearer ")) {
                String token = authHeader.substring(7);
                casUser = casClient.getUserFromToken(token);
                if (casUser == null) {
                    casUser = casClient.validateToken(token);
                }
                if (casUser != null && session != null) {
                    session.setAttribute("cas_user", casUser);
                }
            }
        }

        if (casUser == null) {
            String accept = httpReq.getHeader("Accept");
            if (accept != null && accept.contains("application/json")) {
                httpRes.setStatus(401);
                httpRes.setContentType("application/json");
                httpRes.getWriter().write("{\"error\":\"Authentication required\"}");
            } else {
                httpRes.sendRedirect(loginUrl + "?return_url=" + httpReq.getRequestURI());
            }
            return;
        }

        // Check roles if configured
        if (requiredRoles != null && requiredRoles.length > 0) {
            if (!casClient.userHasAnyRole(casUser, requiredRoles)) {
                httpRes.setStatus(403);
                httpRes.setContentType("application/json");
                httpRes.getWriter().write("{\"error\":\"Insufficient permissions\"}");
                return;
            }
        }

        httpReq.setAttribute("cas_user", casUser);
        chain.doFilter(request, response);
    }

    @Override
    public void init(FilterConfig config) {}

    @Override
    public void destroy() {}
}
