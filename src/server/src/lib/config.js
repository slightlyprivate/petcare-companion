import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

export const config = {
  port: Number(process.env.SERVER_PORT || 5174),
  backendUrl: process.env.BACKEND_URL || 'http://localhost:8080',
  sessionSecret: process.env.SESSION_SECRET,
  frontendDir:
    process.env.FRONTEND_DIR || path.resolve(__dirname, '../../../ui'),
  sameSite: (process.env.COOKIE_SAMESITE || 'lax').toLowerCase(),
  secureCookies:
    String(process.env.COOKIE_SECURE || '').toLowerCase() === 'true' ||
    process.env.NODE_ENV === 'production',
  apiKey: process.env.LARAVEL_API_KEY || '',
};

export function requireConfig() {
  if (!config.sessionSecret) {
    throw new Error('SESSION_SECRET is required (see src/server/.env.example).');
  }
}

