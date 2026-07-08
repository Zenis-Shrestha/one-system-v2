/**
 * CAS SSO Client for Node.js
 * @module @cas-system/node-cas-client
 */

const crypto = require('crypto');
const axios = require('axios');

class CasClient {
  /**
   * @param {Object} config
   * @param {string} config.serverUrl - CAS server URL used for back-channel/server-to-server calls (e.g. https://your-cas-server.com)
   * @param {string} [config.publicUrl] - Public, browser-facing CAS base URL used ONLY to build the SSO login redirect. Falls back to serverUrl when unset.
   * @param {string} config.clientId - Registered client ID
   * @param {string} config.clientSecret - Registered client secret
   * @param {string} config.callbackUrl - OAuth callback URL
   * @param {string} [config.signatureSecret] - HMAC signature secret
   * @param {boolean} [config.enableSignatureValidation=false]
   * @param {number} [config.timeout=30000] - Request timeout in ms
   * @param {boolean} [config.verifySsl=true]
   */
  constructor(config) {
    this.config = {
      timeout: 30000,
      verifySsl: true,
      enableSignatureValidation: false,
      ...config,
    };

    if (!this.config.serverUrl) throw new Error('serverUrl is required');
    if (!this.config.clientId) throw new Error('clientId is required');
    if (!this.config.clientSecret) throw new Error('clientSecret is required');

    this.httpClient = axios.create({
      baseURL: this.config.serverUrl,
      timeout: this.config.timeout,
      headers: { 'Content-Type': 'application/json' },
    });

    // In-memory token cache
    this._cache = new Map();
  }

  /**
   * Generate CAS SSO login URL
   * The CAS server uses the client's registered callback_url, so only
   * client_id is sent as documented by the protocol.
   *
   * The browser must reach CAS at its PUBLIC address, which may differ from the
   * internal back-channel address used for token validation. Prefer publicUrl
   * for the redirect and fall back to serverUrl for single-url setups.
   * @returns {string}
   */
  getLoginUrl() {
    const params = new URLSearchParams({
      client_id: this.config.clientId,
    });
    const loginBase = this.config.publicUrl || this.config.serverUrl;
    return `${loginBase}/sso/login?${params.toString()}`;
  }

  /**
   * Generate SSO token for a user via client credentials
   * @param {string} username
   * @returns {Promise<Object|null>} Token data or null
   */
  async generateSSOToken(username) {
    try {
      const timestamp = Math.floor(Date.now() / 1000);
      const requestData = {
        client_id: this.config.clientId,
        client_secret: this.config.clientSecret,
        username,
      };

      const headers = {
        'X-Client-ID': this.config.clientId,
        'X-Timestamp': timestamp.toString(),
      };

      if (this.config.enableSignatureValidation) {
        headers['X-Signature'] = this._generateSignature(
          'POST', '/api/sso/token', requestData, timestamp
        );
      }

      const response = await this.httpClient.post('/api/sso/token', requestData, { headers });

      if (response.status === 200 && response.data.token) {
        return response.data;
      }
      return null;
    } catch (err) {
      console.error('[CAS] SSO token generation failed:', err.message);
      return null;
    }
  }

  /**
   * Validate SSO token with CAS server
   * @param {string} token - JWT token to validate
   * @returns {Promise<Object|null>} User data or null
   */
  async validateToken(token) {
    try {
      const timestamp = Math.floor(Date.now() / 1000);
      const requestData = {
        token,
        client_id: this.config.clientId,
        client_secret: this.config.clientSecret,
      };

      const headers = {
        'X-Client-ID': this.config.clientId,
        'X-Timestamp': timestamp.toString(),
      };

      if (this.config.enableSignatureValidation) {
        headers['X-Signature'] = this._generateSignature(
          'POST', '/api/validate-token', requestData, timestamp
        );
      }

      const response = await this.httpClient.post('/api/validate-token', requestData, { headers });

      if (response.status === 200 && response.data.valid && response.data.user) {
        // Cache user data (60 min TTL)
        const cacheKey = crypto.createHash('md5').update(token).digest('hex');
        this._cache.set(cacheKey, {
          data: response.data.user,
          expires: Date.now() + 60 * 60 * 1000,
        });
        return response.data.user;
      }
      return null;
    } catch (err) {
      console.error('[CAS] Token validation failed:', err.message);
      return null;
    }
  }

  /**
   * Get cached user data from token
   * @param {string} token
   * @returns {Object|null}
   */
  getUserFromToken(token) {
    const cacheKey = crypto.createHash('md5').update(token).digest('hex');
    const cached = this._cache.get(cacheKey);
    if (cached && cached.expires > Date.now()) {
      return cached.data;
    }
    this._cache.delete(cacheKey);
    return null;
  }

  /**
   * Logout from CAS server
   * @param {string} [token]
   * @returns {Promise<boolean>}
   */
  async logout(token) {
    try {
      if (token) {
        const cacheKey = crypto.createHash('md5').update(token).digest('hex');
        this._cache.delete(cacheKey);
      }
      const response = await this.httpClient.post('/api/logout');
      return response.status === 200;
    } catch (err) {
      console.error('[CAS] Logout failed:', err.message);
      return false;
    }
  }

  /**
   * Check if user has a specific role
   * @param {Object} user
   * @param {string} role
   * @returns {boolean}
   */
  userHasRole(user, role) {
    return (user.roles || []).includes(role);
  }

  /**
   * Check if user has any of the specified roles
   * @param {Object} user
   * @param {string[]} roles
   * @returns {boolean}
   */
  userHasAnyRole(user, roles) {
    const userRoles = user.roles || [];
    return roles.some((r) => userRoles.includes(r));
  }

  /**
   * Check if user has all specified roles
   * @param {Object} user
   * @param {string[]} roles
   * @returns {boolean}
   */
  userHasAllRoles(user, roles) {
    const userRoles = user.roles || [];
    return roles.every((r) => userRoles.includes(r));
  }

  /**
   * Generate HMAC SHA-256 signature
   * @private
   */
  _generateSignature(method, uri, data, timestamp) {
    const body = JSON.stringify(data);
    const payload = [method, uri, body, timestamp, this.config.clientId].join('|');
    const secret = this.config.signatureSecret || 'default-signature-secret';
    const hmac = crypto.createHmac('sha256', secret).update(payload).digest('hex');
    return `sha256=${hmac}`;
  }
}

module.exports = CasClient;
