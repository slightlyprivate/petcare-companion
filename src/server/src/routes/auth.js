import { Router } from 'express';
import { makeApiClient } from '../lib/axios.js';
import { config } from '../lib/config.js';

const TOKEN_COOKIE = 'pc_token';

export const auth = Router();

// Issue a CSRF token
auth.get('/csrf', (req, res) => {
  res.json({ csrfToken: req.session?.csrfToken });
});

// Start OTP flow: request code
auth.post('/request', async (req, res) => {
  const api = makeApiClient(req);
  const upstream = await api.post('/api/auth/request', req.body);
  res.status(upstream.status).json(upstream.data);
});

// Verify OTP → returns token from Laravel; store in session and set httpOnly cookie
auth.post('/verify', async (req, res) => {
  const api = makeApiClient(req);
  const upstream = await api.post('/api/auth/verify', req.body);

  if (upstream.status >= 200 && upstream.status < 300) {
    const token = upstream.data?.token || upstream.data?.access_token;
    if (token) {
      // Store in session (httpOnly cookie via cookie-session)
      req.session.token = token;
      // Set dedicated token cookie (httpOnly)
      res.cookie(TOKEN_COOKIE, token, {
        httpOnly: true,
        secure: config.secureCookies,
        sameSite: (config.sameSite || 'lax'),
        path: '/',
        maxAge: 7 * 24 * 60 * 60 * 1000,
      });
      // Non-HTTP-only hint cookie for UI state (optional)
      res.cookie('pc_logged_in', '1', {
        httpOnly: false,
        secure: config.secureCookies,
        sameSite: (config.sameSite || 'lax'),
        path: '/',
        maxAge: 7 * 24 * 60 * 60 * 1000,
      });
    }
  }

  res.status(upstream.status).json(upstream.data);
});

// Logout: clear session + cookies
auth.post('/logout', (req, res) => {
  if (req.session) {
    req.session = null;
  }
  res.clearCookie(TOKEN_COOKIE, { path: '/' });
  res.clearCookie('pc_logged_in', { path: '/' });
  res.status(204).send();
});

