// Minimal structured logger with level filtering
// Levels: debug < info < warn < error

const levels = { debug: 10, info: 20, warn: 30, error: 40 };
const levelName = (process.env.LOG_LEVEL || 'info').toLowerCase();
const threshold = levels[levelName] ?? levels.info;

function ts() {
  return new Date().toISOString();
}

function log(at, msg, meta) {
  if ((levels[at] ?? levels.info) < threshold) return;
  const entry = { t: ts(), lvl: at, msg };
  if (meta && typeof meta === 'object') {
    // shallow copy to avoid accidental mutation
    entry.meta = { ...meta };
  }
  // Print JSON lines suitable for log aggregation
  // eslint-disable-next-line no-console
  console[at === 'error' ? 'error' : at === 'warn' ? 'warn' : 'log'](JSON.stringify(entry));
}

export const logger = {
  debug: (msg, meta) => log('debug', msg, meta),
  info: (msg, meta) => log('info', msg, meta),
  warn: (msg, meta) => log('warn', msg, meta),
  error: (msg, meta) => log('error', msg, meta),
};

export function withRequestContext(req) {
  const base = {
    method: req.method,
    path: req.originalUrl || req.url,
    ip: req.ip,
  };
  return {
    debug: (msg, meta) => logger.debug(msg, { ...base, ...meta }),
    info: (msg, meta) => logger.info(msg, { ...base, ...meta }),
    warn: (msg, meta) => logger.warn(msg, { ...base, ...meta }),
    error: (msg, meta) => logger.error(msg, { ...base, ...meta }),
  };
}

