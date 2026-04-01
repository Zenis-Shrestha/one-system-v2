"""
CAS SSO Client for Python
Provides SSO authentication, token validation, HMAC signing, and role management.
"""

import hashlib
import hmac
import json
import time
import logging
from typing import Optional, Dict, List, Any
from urllib.parse import urlencode

import requests

logger = logging.getLogger('cas_client')


class CasClient:
    """
    CAS SSO Client for Python applications.

    Args:
        server_url: CAS server URL (e.g., https://your-cas-server.com)
        client_id: Registered client ID
        client_secret: Registered client secret
        callback_url: OAuth callback URL
        signature_secret: HMAC signature secret (optional)
        enable_signature_validation: Enable HMAC request signing (default: False)
        timeout: Request timeout in seconds (default: 30)
        verify_ssl: Verify SSL certificates (default: True)
    """

    def __init__(
        self,
        server_url: str,
        client_id: str,
        client_secret: str,
        callback_url: str = '',
        signature_secret: str = '',
        enable_signature_validation: bool = False,
        timeout: int = 30,
        verify_ssl: bool = True,
    ):
        if not server_url:
            raise ValueError('server_url is required')
        if not client_id:
            raise ValueError('client_id is required')
        if not client_secret:
            raise ValueError('client_secret is required')

        self.config = {
            'server_url': server_url.rstrip('/'),
            'client_id': client_id,
            'client_secret': client_secret,
            'callback_url': callback_url,
            'signature_secret': signature_secret or 'default-signature-secret',
            'enable_signature_validation': enable_signature_validation,
            'timeout': timeout,
            'verify_ssl': verify_ssl,
        }

        self._session = requests.Session()
        self._session.headers.update({'Content-Type': 'application/json'})
        self._session.verify = verify_ssl
        self._session.timeout = timeout

        # In-memory token cache
        self._cache: Dict[str, Dict[str, Any]] = {}

    def get_login_url(self, return_url: Optional[str] = None) -> str:
        """Generate CAS SSO login URL.

        Args:
            return_url: URL to redirect after successful login

        Returns:
            Full CAS login URL string
        """
        params = {
            'client_id': self.config['client_id'],
            'response_type': 'token',
            'redirect_uri': return_url or self.config['callback_url'],
        }
        return f"{self.config['server_url']}/sso/login?{urlencode(params)}"

    def generate_sso_token(self, username: str) -> Optional[Dict[str, Any]]:
        """Generate SSO token for a user via client credentials.

        Args:
            username: Username to generate token for

        Returns:
            Token data dict with 'token' and 'redirect_url', or None
        """
        try:
            timestamp = int(time.time())
            request_data = {
                'client_id': self.config['client_id'],
                'client_secret': self.config['client_secret'],
                'username': username,
            }

            headers = {
                'X-Client-ID': self.config['client_id'],
                'X-Timestamp': str(timestamp),
            }

            if self.config['enable_signature_validation']:
                headers['X-Signature'] = self._generate_signature(
                    'POST', '/api/sso/token', request_data, timestamp
                )

            response = self._session.post(
                f"{self.config['server_url']}/api/sso/token",
                json=request_data,
                headers=headers,
                timeout=self.config['timeout'],
            )

            if response.status_code == 200:
                data = response.json()
                if 'token' in data:
                    return data

            return None
        except Exception as e:
            logger.error(f'CAS SSO token generation failed: {e}')
            return None

    def validate_token(self, token: str) -> Optional[Dict[str, Any]]:
        """Validate SSO token with CAS server.

        Args:
            token: JWT token to validate

        Returns:
            User data dict or None
        """
        try:
            timestamp = int(time.time())
            request_data = {
                'token': token,
                'client_id': self.config['client_id'],
                'client_secret': self.config['client_secret'],
            }

            headers = {
                'X-Client-ID': self.config['client_id'],
                'X-Timestamp': str(timestamp),
            }

            if self.config['enable_signature_validation']:
                headers['X-Signature'] = self._generate_signature(
                    'POST', '/api/sso/validate', request_data, timestamp
                )

            response = self._session.post(
                f"{self.config['server_url']}/api/sso/validate",
                json=request_data,
                headers=headers,
                timeout=self.config['timeout'],
            )

            if response.status_code == 200:
                data = response.json()
                if 'user' in data:
                    cache_key = hashlib.md5(token.encode()).hexdigest()
                    self._cache[cache_key] = {
                        'data': data['user'],
                        'expires': time.time() + 3600,
                    }
                    return data['user']

            return None
        except Exception as e:
            logger.error(f'CAS token validation failed: {e}')
            return None

    def get_user_from_token(self, token: str) -> Optional[Dict[str, Any]]:
        """Get cached user data from token.

        Args:
            token: JWT token

        Returns:
            Cached user data or None
        """
        cache_key = hashlib.md5(token.encode()).hexdigest()
        cached = self._cache.get(cache_key)
        if cached and cached['expires'] > time.time():
            return cached['data']
        self._cache.pop(cache_key, None)
        return None

    def logout(self, token: Optional[str] = None) -> bool:
        """Logout from CAS server.

        Args:
            token: JWT token to invalidate cache for

        Returns:
            True if successful
        """
        try:
            if token:
                cache_key = hashlib.md5(token.encode()).hexdigest()
                self._cache.pop(cache_key, None)

            response = self._session.post(
                f"{self.config['server_url']}/api/logout",
                timeout=self.config['timeout'],
            )
            return response.status_code == 200
        except Exception as e:
            logger.error(f'CAS logout failed: {e}')
            return False

    @staticmethod
    def user_has_role(user: Dict[str, Any], role: str) -> bool:
        """Check if user has a specific role."""
        return role in (user.get('roles') or [])

    @staticmethod
    def user_has_any_role(user: Dict[str, Any], roles: List[str]) -> bool:
        """Check if user has any of the specified roles."""
        user_roles = set(user.get('roles') or [])
        return bool(user_roles & set(roles))

    @staticmethod
    def user_has_all_roles(user: Dict[str, Any], roles: List[str]) -> bool:
        """Check if user has all specified roles."""
        user_roles = set(user.get('roles') or [])
        return set(roles).issubset(user_roles)

    def _generate_signature(self, method: str, uri: str, data: dict, timestamp: int) -> str:
        """Generate HMAC SHA-256 signature for request."""
        body = json.dumps(data, separators=(',', ':'))
        payload = '|'.join([method, uri, body, str(timestamp), self.config['client_id']])
        secret = self.config['signature_secret']
        signature = hmac.new(secret.encode(), payload.encode(), hashlib.sha256).hexdigest()
        return f'sha256={signature}'
