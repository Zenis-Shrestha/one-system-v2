/**
 * Express middleware for CAS authentication
 * @module @insol-dev/node-cas-client/middleware
 */

/**
 * CAS Authentication middleware for Express
 * Validates the user session and redirects to CAS login if not authenticated.
 *
 * @param {CasClient} casClient - Initialized CasClient instance
 * @param {Object} [options]
 * @param {string} [options.sessionKey='cas_user'] - Session key for user data
 * @param {string} [options.loginRoute='/auth/login'] - Redirect path on auth failure
 * @returns {Function} Express middleware
 *
 * @example
 * const { casAuth } = require('@insol-dev/node-cas-client/middleware');
 * app.use('/dashboard', casAuth(casClient));
 */
function casAuth(casClient, options = {}) {
  const sessionKey = options.sessionKey || 'cas_user';
  const loginRoute = options.loginRoute || '/auth/login';

  return (req, res, next) => {
    if (req.session && req.session[sessionKey]) {
      req.casUser = req.session[sessionKey];
      return next();
    }

    // Check Authorization header
    const authHeader = req.headers.authorization;
    if (authHeader && authHeader.startsWith('Bearer ')) {
      const token = authHeader.slice(7);
      const cachedUser = casClient.getUserFromToken(token);
      if (cachedUser) {
        req.casUser = cachedUser;
        return next();
      }

      // Validate with CAS server
      return casClient.validateToken(token).then((user) => {
        if (user) {
          req.casUser = user;
          if (req.session) req.session[sessionKey] = user;
          return next();
        }
        return res.status(401).json({ error: 'Invalid or expired token' });
      }).catch(() => res.status(401).json({ error: 'Authentication failed' }));
    }

    // Redirect to login
    if (req.accepts('html')) {
      const returnUrl = encodeURIComponent(req.originalUrl);
      return res.redirect(`${loginRoute}?return_url=${returnUrl}`);
    }
    return res.status(401).json({ error: 'Authentication required' });
  };
}

/**
 * CAS Role middleware for Express
 * Checks if authenticated user has the required role(s).
 *
 * @param {CasClient} casClient
 * @param {...string} roles - Required roles (user needs ANY of these)
 * @returns {Function} Express middleware
 *
 * @example
 * app.use('/admin', casAuth(casClient), casRole(casClient, 'admin', 'superadmin'));
 */
function casRole(casClient, ...roles) {
  return (req, res, next) => {
    if (!req.casUser) {
      return res.status(401).json({ error: 'Authentication required' });
    }

    if (casClient.userHasAnyRole(req.casUser, roles)) {
      return next();
    }

    return res.status(403).json({ error: 'Insufficient permissions' });
  };
}

module.exports = { casAuth, casRole };
