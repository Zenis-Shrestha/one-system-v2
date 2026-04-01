namespace CasSystem.Client;

/// <summary>
/// Configuration for CAS SSO Client.
/// </summary>
public class CasConfig
{
    /// <summary>CAS server URL (e.g., https://your-cas-server.com)</summary>
    public string ServerUrl { get; set; } = "";

    /// <summary>Registered client ID</summary>
    public string ClientId { get; set; } = "";

    /// <summary>Registered client secret</summary>
    public string ClientSecret { get; set; } = "";

    /// <summary>OAuth callback URL</summary>
    public string CallbackUrl { get; set; } = "";

    /// <summary>HMAC signature secret</summary>
    public string SignatureSecret { get; set; } = "default-signature-secret";

    /// <summary>Enable HMAC request signing</summary>
    public bool EnableSignatureValidation { get; set; } = false;

    /// <summary>Request timeout in seconds</summary>
    public int TimeoutSeconds { get; set; } = 30;

    /// <summary>Verify SSL certificates</summary>
    public bool VerifySsl { get; set; } = true;
}
