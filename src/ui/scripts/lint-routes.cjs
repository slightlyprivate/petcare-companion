#!/usr/bin/env node
/*
  Simple guard to prevent hardcoded UI route paths.
  Scans for:
  - JSX: to="/..." or to={`/...`}
  - route config: path: '/...'
  - window.location.assign('/...')
*/
const fs = require('fs');
const path = require('path');

const root = path.join(__dirname, '..');
const srcDir = path.join(root, 'src');

function isTextFile(p) {
  return /\.(tsx?|jsx?)$/.test(p);
}

function walk(dir, out) {
  const entries = fs.readdirSync(dir, { withFileTypes: true });
  for (const e of entries) {
    const full = path.join(dir, e.name);
    if (e.isDirectory()) walk(full, out);
    else if (e.isFile() && isTextFile(full)) out.push(full);
  }
  return out;
}

const files = walk(srcDir, []);
const offenders = [];
const ignoreFiles = [path.join(srcDir, 'routes', 'paths.ts')];

for (const file of files) {
  if (ignoreFiles.includes(file)) continue;
  const txt = fs.readFileSync(file, 'utf8');
  const lines = txt.split(/\r?\n/);
  lines.forEach((line, idx) => {
    const n = idx + 1;
    if (/\bto=\"\//.test(line)) offenders.push({ file, n, line });
    if (/\bto=\{(`|')\//.test(line)) offenders.push({ file, n, line });
    if (/\bpath:\s*(`|'|")\//.test(line)) offenders.push({ file, n, line });
    if (/window\.location\.assign\s*\(\s*(`|'|")\//.test(line)) offenders.push({ file, n, line });
    if (/pathname:\s*(`|'|")\//.test(line)) offenders.push({ file, n, line });
  });
}

if (offenders.length) {
  console.error('\nHardcoded UI route paths detected. Use PATHS from src/routes/paths.ts\n');
  for (const { file, n, line } of offenders) {
    console.error(`${path.relative(root, file)}:${n}: ${line.trim()}`);
  }
  process.exit(1);
} else {
  console.log('OK: No hardcoded UI routes found.');
}
