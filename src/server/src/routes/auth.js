import { Router } from 'express';
import { makeApiClient } from '../lib/axios.js';
import { config } from '../lib/config.js';
import { CookieNames, loggedInCookieOptions, tokenCookieOptions } from '../lib/cookies.js';

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
      res.cookie(CookieNames.TOKEN, token, tokenCookieOptions());
      // Non-HTTP-only hint cookie for UI state (optional)
      res.cookie(CookieNames.LOGGED_IN, '1', loggedInCookieOptions());
    }
  }

  res.status(upstream.status).json(upstream.data);
});

// Logout: clear session + cookies
auth.post('/logout', async (req, res) => {
  try {
    // Best-effort revoke on backend; ignore failures but log for visibility
    const api = makeApiClient(req);
    await api.post('/api/auth/logout');
  } catch (e) {
    console.error('Backend logout failed (continuing):', e?.response?.status || e?.message);
  }

  if (req.session) {
    req.session = null;
  }
  res.clearCookie(CookieNames.TOKEN, { path: '/' });
  res.clearCookie(CookieNames.LOGGED_IN, { path: '/' });
  res.status(204).send();
});
