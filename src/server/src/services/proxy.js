import { API_PREFIX } from '../constants.js';
import { makeApiClient } from '../lib/axios.js';
import { withRequestContext } from '../lib/logger.js';

// Helper: copy upstream headers onto Express response (skip hop-by-hop)
export function copyUpstreamHeaders(res, upstream) {
  const headers = upstream?.headers || {};
  for (const [k, v] of Object.entries(headers)) {
    const key = String(k).toLowerCase();
    if (['transfer-encoding', 'content-encoding'].includes(key)) continue;
    if (v !== undefined) res.setHeader(k, v);
  }
}

// Helper: disable caching for JSON responses in development
export function applyNoCache(res) {
  if (process.env.NODE_ENV === 'production') return;
  res.removeHeader('ETag');
  res.setHeader('Cache-Control', 'no-store, no-cache, must-revalidate');
  res.setHeader('Pragma', 'no-cache');
  res.setHeader('Expires', '0');
}

// Helper: standard success sender (with logging)
export function sendUpstreamJson(rl, res, upstream) {
  res.status(upstream.status);
  copyUpstreamHeaders(res, upstream);
  applyNoCache(res);
  rl?.debug?.('proxy_success', { status: upstream.status });
  return res.send(upstream.data);
}

// Helper: standard error sender (with logging)
export function sendUpstreamError(
  rl,
  res,
  err,
  fallbackStatus = 502,
  fallbackData = { error: 'Bad gateway' },
) {
  const status = err?.response?.status || fallbackStatus;
  const data = err?.response?.data || fallbackData;
  rl?.error?.('proxy_error', { status, error: String(err) });
  res.status(status);
  if (err?.response) copyUpstreamHeaders(res, err.response);
  applyNoCache(res);
  return res.send(data);
}

// Unified proxy handler used by index.js
export async function handleProxy(req, res) {
  const rl = withRequestContext(req);
  try {
    const api = makeApiClient(req);
    const orig = req.originalUrl || req.url || '';
    const url = orig.startsWith(API_PREFIX) ? orig : `${API_PREFIX}${orig}`;
    const method = req.method.toLowerCase();
    const isBinary = (req.headers['accept'] || '').includes('application/pdf');
    const response = await api.request({
      url,
      method,
      data: ['get', 'head'].includes(method) ? undefined : req.body,
      responseType: isBinary ? 'arraybuffer' : 'json',
    });

    res.status(response.status);
    copyUpstreamHeaders(res, response);
    applyNoCache(res);

    if (response.data === undefined) return res.end();
    if (response.request?.responseType === 'arraybuffer' || Buffer.isBuffer(response.data)) {
      return res.send(Buffer.from(response.data));
    }
    rl.debug('proxy_success', { status: response.status });
    return res.send(response.data);
  } catch (err) {
    return sendUpstreamError(rl, res, err);
  }
}
