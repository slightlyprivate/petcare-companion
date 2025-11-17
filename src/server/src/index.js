import express from 'express';
import cookieSession from 'cookie-session';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import cookieParser from 'cookie-parser';
import { config, requireConfig } from './lib/config.js';
import { ensureCsrfToken, requireCsrfOnMutations } from './middleware/csrf.js';
import { auth as authRouter } from './routes/auth.js';
import { API_PREFIX } from './constants.js';
import { handleProxy } from './services/proxy.js';
import { BFF_REWRITE_PREFIXES } from '../../shared/bffPaths.js';
import { logger } from './lib/logger.js';
import { errorHandler, notFound } from './middleware/error.js';
// moved imports above

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
    maxAge: 7 * 24 * 60 * 60 * 1000, // 7 days
  }),
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
// Also enforce CSRF on BFF-mapped JSON endpoints
app.use(BFF_REWRITE_PREFIXES, requireCsrfOnMutations);

// Session demo endpoint (useful to verify cookie-session works)
app.get('/session/ping', (req, res) => {
  req.session.views = (req.session.views || 0) + 1;
  res.json({ views: req.session.views });
});

// Generic API proxy: forwards /api/* to Laravel backend
app.all(`${API_PREFIX}/*`, handleProxy);
// Forward BFF JSON endpoints by rewriting to /api/* internally via handleProxy
app.all(
  BFF_REWRITE_PREFIXES.map((p) => `${p}/*`),
  handleProxy,
);

// SPA fallback for non-API GET requests
app.get('*', (req, res, next) => {
  if (req.path.startsWith(API_PREFIX)) return next();
  const indexPath = path.join(FRONTEND_DIR, 'index.html');
  res.sendFile(indexPath, (err) => {
    if (err) next(err);
  });
});

app.listen(PORT, () => {
  logger.info(`PetCare server listening on http://localhost:${PORT}`);
  logger.info(`Proxying ${API_PREFIX}/* to ${BACKEND_URL}`);
  logger.info(`Serving static from ${FRONTEND_DIR}`);
});

// Fallbacks and error handling
app.use(notFound);
app.use(errorHandler);
