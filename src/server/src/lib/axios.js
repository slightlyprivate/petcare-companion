import axios from 'axios';
import { config } from './config.js';

export function makeApiClient(req) {
  const instance = axios.create({
    baseURL: config.backendUrl,
    timeout: 15000,
    maxRedirects: 0,
    validateStatus: () => true,
  });

  instance.interceptors.request.use((cfg) => {
    // Forward selected headers from client
    const passHeaders = ['accept', 'content-type', 'user-agent'];
    cfg.headers = cfg.headers || {};
    for (const k of passHeaders) {
      const v = req.headers[k];
      if (v) cfg.headers[k] = v;
    }

    // Optional service-to-service API key
    if (config.apiKey) cfg.headers['x-api-key'] = config.apiKey;

    // If user is logged in, attach Bearer token from session
    const token = req.session?.token;
    if (token) cfg.headers['authorization'] = `Bearer ${token}`;
    return cfg;
  });

  return instance;
}

