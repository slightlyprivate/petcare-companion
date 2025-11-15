import express from 'express';
import cookieSession from 'cookie-session';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import cookieParser from 'cookie-parser';
import { config, requireConfig } from './lib/config.js';
import { makeApiClient } from './lib/axios.js';
import { ensureCsrfToken, requireCsrfOnMutations } from './middleware/csrf.js';
import { auth as authRouter } from './routes/auth.js';

// Configuration
requireConfig();
const PORT = config.port;
const BACKEND_URL = config.backendUrl;
const SESSION_SECRET = config.sessionSecret;
const FRONTEND_DIR = config.frontendDir;

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
    secure: config.secureCookies,
    sameSite: config.sameSite,
    maxAge: 7 * 24 * 60 * 60 * 1000 // 7 days
  })
);

// Parse cookies (for token cookie handling if needed)
app.use(cookieParser());

// Body parsing for JSON and urlencoded forms
app.use(express.json({ limit: '1mb' }));
app.use(express.urlencoded({ extended: true }));

// Static assets for the SPA (if present)
app.use(express.static(FRONTEND_DIR));

// CSRF token issuance for session
app.use(ensureCsrfToken);

// Simple health check
app.get('/health', (req, res) => {
  res.json({ ok: true });
});

// Auth routes (OTP request/verify, logout, csrf)
app.use('/auth', authRouter);

// CSRF required on mutating API requests
app.use('/api', requireCsrfOnMutations);

// Session demo endpoint (useful to verify cookie-session works)
app.get('/session/ping', (req, res) => {
  req.session.views = (req.session.views || 0) + 1;
  res.json({ views: req.session.views });
});

// Generic API proxy: forwards /api/* to Laravel backend
app.all('/api/*', async (req, res) => {
  try {
    const api = makeApiClient(req);
    const url = req.originalUrl.replace(/^\/api/, '/api');
    const method = req.method.toLowerCase();
    const isBinary = (req.headers['accept'] || '').includes('application/pdf');
    const response = await api.request({
      url,
      method,
      data: ['get', 'head'].includes(method) ? undefined : req.body,
      responseType: isBinary ? 'arraybuffer' : 'json',
    });

    // status and headers
    res.status(response.status);
    for (const [k, v] of Object.entries(response.headers || {})) {
      if (['transfer-encoding'].includes(String(k).toLowerCase())) continue;
      if (v !== undefined) res.setHeader(k, v);
    }

    if (response.data === undefined) return res.end();
    // If arraybuffer, send as buffer; else JSON
    if (response.request?.responseType === 'arraybuffer' || Buffer.isBuffer(response.data)) {
      return res.send(Buffer.from(response.data));
    }
    return res.send(response.data);
  } catch (err) {
    console.error('Proxy error:', err);
    const status = err.response?.status || 502;
    const data = err.response?.data || { error: 'Bad gateway' };
    res.status(status).send(data);
  }
});

// SPA fallback for non-API GET requests
app.get('*', (req, res, next) => {
  if (req.path.startsWith('/api')) return next();
  const indexPath = path.join(FRONTEND_DIR, 'index.html');
  res.sendFile(indexPath, (err) => {
    if (err) next(err);
  });
});

app.listen(PORT, () => {
  console.log(`PetCare server listening on http://localhost:${PORT}`);
  console.log(`Proxying /api/* to ${BACKEND_URL}`);
  console.log(`Serving static from ${FRONTEND_DIR}`);
});
