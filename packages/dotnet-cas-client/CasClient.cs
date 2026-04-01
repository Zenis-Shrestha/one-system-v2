using System.Collections.Concurrent;
using System.Net.Http.Json;
using System.Security.Cryptography;
using System.Text;
using System.Text.Json;
using System.Web;
using Microsoft.Extensions.Logging;

namespace CasSystem.Client;

/// <summary>
/// CAS SSO Client for .NET applications.
/// </summary>
/// <example>
/// var config = new CasConfig { ServerUrl = "https://your-cas-server.com", ClientId = "id", ClientSecret = "secret" };
/// var cas = new CasClient(config);
/// var user = await cas.ValidateTokenAsync(token);
/// </example>
public class CasClient
{
    private readonly CasConfig _config;
    private readonly HttpClient _http;
    private readonly ILogger<CasClient>? _logger;
    private readonly ConcurrentDictionary<string, CacheEntry> _cache = new();

    public CasClient(CasConfig config, ILogger<CasClient>? logger = null)
    {
        _config = config ?? throw new ArgumentNullException(nameof(config));
        _logger = logger;

        if (string.IsNullOrEmpty(config.ServerUrl)) throw new ArgumentException("ServerUrl is required");
        if (string.IsNullOrEmpty(config.ClientId)) throw new ArgumentException("ClientId is required");
        if (string.IsNullOrEmpty(config.ClientSecret)) throw new ArgumentException("ClientSecret is required");

        _http = new HttpClient
        {
            BaseAddress = new Uri(config.ServerUrl.TrimEnd('/')),
            Timeout = TimeSpan.FromSeconds(config.TimeoutSeconds)
        };
        _http.DefaultRequestHeaders.Add("Accept", "application/json");
    }

    /// <summary>Generate CAS SSO login URL.</summary>
    public string GetLoginUrl(string? returnUrl = null)
    {
        var redirectUri = !string.IsNullOrEmpty(returnUrl) ? returnUrl : _config.CallbackUrl;
        return $"{_config.ServerUrl.TrimEnd('/')}/sso/login?client_id={HttpUtility.UrlEncode(_config.ClientId)}&response_type=token&redirect_uri={HttpUtility.UrlEncode(redirectUri)}";
    }

    /// <summary>Generate SSO token for a user via client credentials.</summary>
    public async Task<Dictionary<string, object>?> GenerateSSOTokenAsync(string username)
    {
        try
        {
            var timestamp = DateTimeOffset.UtcNow.ToUnixTimeSeconds();
            var requestData = new Dictionary<string, string>
            {
                ["client_id"] = _config.ClientId,
                ["client_secret"] = _config.ClientSecret,
                ["username"] = username
            };

            var request = CreateRequest(HttpMethod.Post, "/api/sso/token", requestData, timestamp);
            var response = await _http.SendAsync(request);

            if (response.IsSuccessStatusCode)
            {
                var data = await response.Content.ReadFromJsonAsync<Dictionary<string, object>>();
                if (data != null && data.ContainsKey("token")) return data;
            }
            return null;
        }
        catch (Exception ex)
        {
            _logger?.LogError(ex, "CAS SSO token generation failed");
            return null;
        }
    }

    /// <summary>Validate SSO token with CAS server.</summary>
    public async Task<Dictionary<string, object>?> ValidateTokenAsync(string token)
    {
        try
        {
            var timestamp = DateTimeOffset.UtcNow.ToUnixTimeSeconds();
            var requestData = new Dictionary<string, string>
            {
                ["token"] = token,
                ["client_id"] = _config.ClientId,
                ["client_secret"] = _config.ClientSecret
            };

            var request = CreateRequest(HttpMethod.Post, "/api/sso/validate", requestData, timestamp);
            var response = await _http.SendAsync(request);

            if (response.IsSuccessStatusCode)
            {
                var json = await response.Content.ReadAsStringAsync();
                using var doc = JsonDocument.Parse(json);
                if (doc.RootElement.TryGetProperty("user", out var userElem))
                {
                    var user = JsonSerializer.Deserialize<Dictionary<string, object>>(userElem.GetRawText());
                    if (user != null)
                    {
                        var cacheKey = ComputeMd5(token);
                        _cache[cacheKey] = new CacheEntry(user, DateTimeOffset.UtcNow.AddHours(1).ToUnixTimeMilliseconds());
                        return user;
                    }
                }
            }
            return null;
        }
        catch (Exception ex)
        {
            _logger?.LogError(ex, "CAS token validation failed");
            return null;
        }
    }

    /// <summary>Get cached user data from token.</summary>
    public Dictionary<string, object>? GetUserFromToken(string token)
    {
        var key = ComputeMd5(token);
        if (_cache.TryGetValue(key, out var entry) && entry.Expires > DateTimeOffset.UtcNow.ToUnixTimeMilliseconds())
            return entry.Data;
        _cache.TryRemove(key, out _);
        return null;
    }

    /// <summary>Logout from CAS server.</summary>
    public async Task<bool> LogoutAsync(string? token = null)
    {
        try
        {
            if (token != null) _cache.TryRemove(ComputeMd5(token), out _);
            var response = await _http.PostAsync("/api/logout", new StringContent("{}", Encoding.UTF8, "application/json"));
            return response.IsSuccessStatusCode;
        }
        catch (Exception ex)
        {
            _logger?.LogError(ex, "CAS logout failed");
            return false;
        }
    }

    /// <summary>Check if user has a specific role.</summary>
    public static bool UserHasRole(Dictionary<string, object> user, string role)
    {
        if (user.TryGetValue("roles", out var rolesObj) && rolesObj is JsonElement elem)
            return elem.EnumerateArray().Any(r => r.GetString() == role);
        return false;
    }

    /// <summary>Check if user has any of the specified roles.</summary>
    public static bool UserHasAnyRole(Dictionary<string, object> user, params string[] roles)
    {
        if (user.TryGetValue("roles", out var rolesObj) && rolesObj is JsonElement elem)
        {
            var userRoles = elem.EnumerateArray().Select(r => r.GetString()).ToHashSet();
            return roles.Any(r => userRoles.Contains(r));
        }
        return false;
    }

    /// <summary>Check if user has all specified roles.</summary>
    public static bool UserHasAllRoles(Dictionary<string, object> user, params string[] roles)
    {
        if (user.TryGetValue("roles", out var rolesObj) && rolesObj is JsonElement elem)
        {
            var userRoles = elem.EnumerateArray().Select(r => r.GetString()).ToHashSet();
            return roles.All(r => userRoles.Contains(r));
        }
        return false;
    }

    // --- Internal ---

    private HttpRequestMessage CreateRequest(HttpMethod method, string uri, Dictionary<string, string> data, long timestamp)
    {
        var json = JsonSerializer.Serialize(data);
        var req = new HttpRequestMessage(method, uri)
        {
            Content = new StringContent(json, Encoding.UTF8, "application/json")
        };
        req.Headers.Add("X-Client-ID", _config.ClientId);
        req.Headers.Add("X-Timestamp", timestamp.ToString());

        if (_config.EnableSignatureValidation)
            req.Headers.Add("X-Signature", GenerateSignature(method.Method, uri, json, timestamp));

        return req;
    }

    private string GenerateSignature(string method, string uri, string body, long timestamp)
    {
        var payload = $"{method}|{uri}|{body}|{timestamp}|{_config.ClientId}";
        using var hmac = new HMACSHA256(Encoding.UTF8.GetBytes(_config.SignatureSecret));
        var hash = hmac.ComputeHash(Encoding.UTF8.GetBytes(payload));
        return "sha256=" + Convert.ToHexString(hash).ToLowerInvariant();
    }

    private static string ComputeMd5(string input)
    {
        var hash = MD5.HashData(Encoding.UTF8.GetBytes(input));
        return Convert.ToHexString(hash).ToLowerInvariant();
    }

    private record CacheEntry(Dictionary<string, object> Data, long Expires);
}
