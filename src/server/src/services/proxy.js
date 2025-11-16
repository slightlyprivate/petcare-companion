import { API_PREFIX } from '../constants.js';
import { makeApiClient } from '../lib/axios.js';
import { withRequestContext } from '../lib/logger.js';

// Unified proxy handler used by index.js
export async function handleProxy(req, res) {
  const rl = withRequestContext(req);
  try {
    const api = makeApiClient(req);
    const url = req.originalUrl.replace(new RegExp(`^${API_PREFIX}`), API_PREFIX);
    const method = req.method.toLowerCase();
    const isBinary = (req.headers['accept'] || '').includes('application/pdf');
    const response = await api.request({
      url,
      method,
      data: ['get', 'head'].includes(method) ? undefined : req.body,
      responseType: isBinary ? 'arraybuffer' : 'json',
    });

    res.status(response.status);
    for (const [k, v] of Object.entries(response.headers || {})) {
      if (['transfer-encoding'].includes(String(k).toLowerCase())) continue;
      if (v !== undefined) res.setHeader(k, v);
    }

    if (response.data === undefined) return res.end();
    if (response.request?.responseType === 'arraybuffer' || Buffer.isBuffer(response.data)) {
      return res.send(Buffer.from(response.data));
    }
    rl.debug('proxy_success', { status: response.status });
    return res.send(response.data);
  } catch (err) {
    rl.error('proxy_error', { status: err?.response?.status, error: String(err) });
    const status = err.response?.status || 502;
    const data = err.response?.data || { error: 'Bad gateway' };
    res.status(status).send(data);
  }
}
