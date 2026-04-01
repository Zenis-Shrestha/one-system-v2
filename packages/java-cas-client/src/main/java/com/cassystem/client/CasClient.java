package com.cassystem.client;

import com.google.gson.Gson;
import com.google.gson.JsonObject;
import okhttp3.*;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import javax.crypto.Mac;
import javax.crypto.spec.SecretKeySpec;
import java.io.IOException;
import java.net.URLEncoder;
import java.nio.charset.StandardCharsets;
import java.security.MessageDigest;
import java.util.*;
import java.util.concurrent.ConcurrentHashMap;
import java.util.concurrent.TimeUnit;

/**
 * CAS SSO Client for Java applications.
 *
 * <pre>
 * CasConfig config = new CasConfig("https://your-cas-server.com", "client_id", "client_secret");
 * CasClient cas = new CasClient(config);
 *
 * // Generate login URL
 * String loginUrl = cas.getLoginUrl("https://your-app.com/dashboard");
 *
 * // Validate token
 * Map&lt;String, Object&gt; user = cas.validateToken(token);
 *
 * // Check roles
 * boolean isAdmin = cas.userHasRole(user, "admin");
 * </pre>
 */
public class CasClient {
    private static final Logger logger = LoggerFactory.getLogger(CasClient.class);
    private static final MediaType JSON = MediaType.get("application/json; charset=utf-8");
    private static final Gson gson = new Gson();

    private final CasConfig config;
    private final OkHttpClient httpClient;
    private final Map<String, CacheEntry> cache = new ConcurrentHashMap<>();

    public CasClient(CasConfig config) {
        this.config = config;
        this.httpClient = new OkHttpClient.Builder()
                .connectTimeout(config.getTimeoutSeconds(), TimeUnit.SECONDS)
                .readTimeout(config.getTimeoutSeconds(), TimeUnit.SECONDS)
                .build();
    }

    /**
     * Generate CAS SSO login URL.
     */
    public String getLoginUrl(String returnUrl) {
        String redirectUri = (returnUrl != null && !returnUrl.isEmpty()) ? returnUrl : config.getCallbackUrl();
        try {
            return config.getServerUrl() + "/sso/login?"
                    + "client_id=" + URLEncoder.encode(config.getClientId(), "UTF-8")
                    + "&response_type=token"
                    + "&redirect_uri=" + URLEncoder.encode(redirectUri, "UTF-8");
        } catch (Exception e) {
            throw new RuntimeException("Failed to encode URL", e);
        }
    }

    /**
     * Generate SSO token for a user via client credentials.
     *
     * @return Token data map or null
     */
    public Map<String, Object> generateSSOToken(String username) {
        try {
            long timestamp = System.currentTimeMillis() / 1000;
            Map<String, String> requestData = new LinkedHashMap<>();
            requestData.put("client_id", config.getClientId());
            requestData.put("client_secret", config.getClientSecret());
            requestData.put("username", username);

            Request.Builder builder = new Request.Builder()
                    .url(config.getServerUrl() + "/api/sso/token")
                    .post(RequestBody.create(gson.toJson(requestData), JSON))
                    .header("Content-Type", "application/json")
                    .header("X-Client-ID", config.getClientId())
                    .header("X-Timestamp", String.valueOf(timestamp));

            if (config.isEnableSignatureValidation()) {
                builder.header("X-Signature", generateSignature("POST", "/api/sso/token", requestData, timestamp));
            }

            try (Response response = httpClient.newCall(builder.build()).execute()) {
                if (response.isSuccessful() && response.body() != null) {
                    String body = response.body().string();
                    @SuppressWarnings("unchecked")
                    Map<String, Object> data = gson.fromJson(body, Map.class);
                    if (data.containsKey("token")) return data;
                }
            }
            return null;
        } catch (Exception e) {
            logger.error("CAS SSO token generation failed: {}", e.getMessage());
            return null;
        }
    }

    /**
     * Validate SSO token with CAS server.
     *
     * @return User data map or null
     */
    @SuppressWarnings("unchecked")
    public Map<String, Object> validateToken(String token) {
        try {
            long timestamp = System.currentTimeMillis() / 1000;
            Map<String, String> requestData = new LinkedHashMap<>();
            requestData.put("token", token);
            requestData.put("client_id", config.getClientId());
            requestData.put("client_secret", config.getClientSecret());

            Request.Builder builder = new Request.Builder()
                    .url(config.getServerUrl() + "/api/sso/validate")
                    .post(RequestBody.create(gson.toJson(requestData), JSON))
                    .header("Content-Type", "application/json")
                    .header("X-Client-ID", config.getClientId())
                    .header("X-Timestamp", String.valueOf(timestamp));

            if (config.isEnableSignatureValidation()) {
                builder.header("X-Signature", generateSignature("POST", "/api/sso/validate", requestData, timestamp));
            }

            try (Response response = httpClient.newCall(builder.build()).execute()) {
                if (response.isSuccessful() && response.body() != null) {
                    String body = response.body().string();
                    Map<String, Object> data = gson.fromJson(body, Map.class);
                    if (data.containsKey("user")) {
                        Map<String, Object> user = (Map<String, Object>) data.get("user");
                        String cacheKey = md5(token);
                        cache.put(cacheKey, new CacheEntry(user, System.currentTimeMillis() + 3600_000));
                        return user;
                    }
                }
            }
            return null;
        } catch (Exception e) {
            logger.error("CAS token validation failed: {}", e.getMessage());
            return null;
        }
    }

    /** Get cached user data from token. */
    public Map<String, Object> getUserFromToken(String token) {
        String cacheKey = md5(token);
        CacheEntry entry = cache.get(cacheKey);
        if (entry != null && entry.expires > System.currentTimeMillis()) {
            return entry.data;
        }
        cache.remove(cacheKey);
        return null;
    }

    /** Logout from CAS server. */
    public boolean logout(String token) {
        try {
            if (token != null) cache.remove(md5(token));
            Request request = new Request.Builder()
                    .url(config.getServerUrl() + "/api/logout")
                    .post(RequestBody.create("{}", JSON))
                    .build();
            try (Response response = httpClient.newCall(request).execute()) {
                return response.isSuccessful();
            }
        } catch (Exception e) {
            logger.error("CAS logout failed: {}", e.getMessage());
            return false;
        }
    }

    /** Check if user has a specific role. */
    @SuppressWarnings("unchecked")
    public boolean userHasRole(Map<String, Object> user, String role) {
        List<String> roles = (List<String>) user.getOrDefault("roles", Collections.emptyList());
        return roles.contains(role);
    }

    /** Check if user has any of the specified roles. */
    @SuppressWarnings("unchecked")
    public boolean userHasAnyRole(Map<String, Object> user, String... roles) {
        List<String> userRoles = (List<String>) user.getOrDefault("roles", Collections.emptyList());
        for (String role : roles) {
            if (userRoles.contains(role)) return true;
        }
        return false;
    }

    /** Check if user has all specified roles. */
    @SuppressWarnings("unchecked")
    public boolean userHasAllRoles(Map<String, Object> user, String... roles) {
        List<String> userRoles = (List<String>) user.getOrDefault("roles", Collections.emptyList());
        for (String role : roles) {
            if (!userRoles.contains(role)) return false;
        }
        return true;
    }

    // --- Internal helpers ---

    private String generateSignature(String method, String uri, Map<String, String> data, long timestamp) {
        try {
            String body = gson.toJson(data);
            String payload = String.join("|", method, uri, body, String.valueOf(timestamp), config.getClientId());
            Mac mac = Mac.getInstance("HmacSHA256");
            mac.init(new SecretKeySpec(config.getSignatureSecret().getBytes(StandardCharsets.UTF_8), "HmacSHA256"));
            byte[] hash = mac.doFinal(payload.getBytes(StandardCharsets.UTF_8));
            return "sha256=" + bytesToHex(hash);
        } catch (Exception e) {
            throw new RuntimeException("Failed to generate signature", e);
        }
    }

    private static String md5(String input) {
        try {
            MessageDigest md = MessageDigest.getInstance("MD5");
            return bytesToHex(md.digest(input.getBytes(StandardCharsets.UTF_8)));
        } catch (Exception e) {
            return input;
        }
    }

    private static String bytesToHex(byte[] bytes) {
        StringBuilder sb = new StringBuilder(bytes.length * 2);
        for (byte b : bytes) sb.append(String.format("%02x", b));
        return sb.toString();
    }

    private static class CacheEntry {
        final Map<String, Object> data;
        final long expires;
        CacheEntry(Map<String, Object> data, long expires) {
            this.data = data;
            this.expires = expires;
        }
    }
}
