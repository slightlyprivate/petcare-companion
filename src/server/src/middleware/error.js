import { withRequestContext } from '../lib/logger.js';

// 404 handler for unmatched routes
export function notFound(_req, res, _next) {
  return res.status(404).json({ error: { message: 'Not Found' } });
}

// Central error handler
export function errorHandler(err, req, res, _next) {
  const rl = withRequestContext(req);
  const status = err.status || err.statusCode || 500;
  const code = err.code || undefined;
  const message = status >= 500 ? 'Internal Server Error' : err.message || 'Request failed';

  rl.error('request_error', {
    status,
    code,
    // avoid logging entire req/res objects
    error: typeof err?.toString === 'function' ? String(err) : undefined,
    stack: process.env.NODE_ENV === 'production' ? undefined : err?.stack,
  });

  return res.status(status).json({ error: { message, code } });
}
