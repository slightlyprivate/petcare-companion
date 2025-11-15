import crypto from 'node:crypto';

export function ensureCsrfToken(req, _res, next) {
  if (!req.session) return next();
  if (!req.session.csrfToken) {
    req.session.csrfToken = crypto.randomBytes(20).toString('hex');
  }
  next();
}

export function requireCsrfOnMutations(req, res, next) {
  const method = req.method.toUpperCase();
  const needs = ['POST', 'PUT', 'PATCH', 'DELETE'].includes(method);
  if (!needs) return next();
  const provided = req.get('x-csrf-token') || req.get('x-xsrf-token');
  if (provided && req.session?.csrfToken && provided === req.session.csrfToken) {
    return next();
  }
  return res.status(403).json({ error: 'CSRF token missing or invalid' });
}

