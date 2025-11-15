import express from 'express';
import cookieSession from 'cookie-session';

// Configuration
const PORT = Number(process.env.SERVER_PORT || 5174);
const BACKEND_URL = process.env.BACKEND_URL || 'http://localhost:8080';
const SESSION_SECRET = process.env.SESSION_SECRET;

if (!SESSION_SECRET) {
  // Fail fast to avoid accidental dev secrets in code.
  console.error('SESSION_SECRET is required (see src/server/.env.example).');
  process.exit(1);
}

const app = express();

// Trust proxy if deployed behind a reverse proxy (e.g., nginx)
app.set('trust proxy', 1);

// Sessions via cookie-session
app.use(
  cookieSession({
    name: 'pcsid',
    secret: SESSION_SECRET,
    httpOnly: true,
    secure: process.env.NODE_ENV === 'production',
    sameSite: 'lax',
    maxAge: 7 * 24 * 60 * 60 * 1000 // 7 days
  })
);

// Body parsing for JSON and urlencoded forms
app.use(express.json({ limit: '1mb' }));
app.use(express.urlencoded({ extended: true }));

// Simple health check
app.get('/health', (req, res) => {
  res.json({ ok: true });
});

// Session demo endpoint (useful to verify cookie-session works)
app.get('/session/ping', (req, res) => {
  req.session.views = (req.session.views || 0) + 1;
  res.json({ views: req.session.views });
});

// Generic API proxy: forwards /api/* to Laravel backend
app.all('/api/*', async (req, res) => {
  try {
    const targetUrl = new URL(req.originalUrl, BACKEND_URL).toString();

    // Build headers for the proxied request
    const headers = new Headers();
    for (const [key, value] of Object.entries(req.headers)) {
      if (!value) continue;
      const lower = key.toLowerCase();
      // Skip hop-by-hop or implicit headers
      if (['host', 'connection', 'content-length', 'accept-encoding'].includes(lower)) continue;
      if (Array.isArray(value)) {
        headers.set(lower, value.join(', '));
      } else {
        headers.set(lower, String(value));
      }
    }
    headers.set('x-forwarded-proto', req.protocol);
    headers.set('x-forwarded-host', req.get('host') || '');

    // Prepare body (support JSON and urlencoded). For other content-types, omit body.
    let body;
    const method = req.method.toUpperCase();
    const contentType = (req.headers['content-type'] || '').toString();
    if (!['GET', 'HEAD'].includes(method)) {
      if (contentType.startsWith('application/json')) {
        body = JSON.stringify(req.body ?? {});
      } else if (contentType.startsWith('application/x-www-form-urlencoded')) {
        const params = new URLSearchParams();
        for (const [k, v] of Object.entries(req.body ?? {})) {
          if (Array.isArray(v)) v.forEach((vv) => params.append(k, String(vv)));
          else if (v !== undefined && v !== null) params.append(k, String(v));
        }
        body = params.toString();
      }
      // For multipart/form-data or others, you may need additional handling.
    }

    const response = await fetch(targetUrl, {
      method,
      headers,
      body,
    });

    // Copy status and headers back to client
    res.status(response.status);
    response.headers.forEach((val, key) => {
      // Avoid setting forbidden/implicit headers
      if (['transfer-encoding'].includes(key.toLowerCase())) return;
      res.setHeader(key, val);
    });

    const buffer = Buffer.from(await response.arrayBuffer());
    res.send(buffer);
  } catch (err) {
    console.error('Proxy error:', err);
    res.status(502).json({ error: 'Bad gateway', detail: (err && err.message) || String(err) });
  }
});

app.listen(PORT, () => {
  console.log(`PetCare server listening on http://localhost:${PORT}`);
  console.log(`Proxying /api/* to ${BACKEND_URL}`);
});

