#!/usr/bin/env node
/*
  Guardrail: disallow explicit `any` usage in TS/TSX files.
  This is a heuristic scan that flags common forms:
  - ": any", "as any", "any[]", "useState<any>"
*/
const fs = require('fs');
const path = require('path');

const root = path.join(__dirname, '..');
const srcDir = path.join(root, 'src');

function isTsFile(p) {
  return /\.(tsx?)$/.test(p);
}

function walk(dir, out) {
  const entries = fs.readdirSync(dir, { withFileTypes: true });
  for (const e of entries) {
    const full = path.join(dir, e.name);
    if (e.isDirectory()) walk(full, out);
    else if (e.isFile() && isTsFile(full)) out.push(full);
  }
  return out;
}

const files = walk(srcDir, []);
const offenders = [];
const PATTERN = /(:\s*any\b|as\s+any\b|\bany\[\]|useState<\s*any\s*>)/;

for (const file of files) {
  const txt = fs.readFileSync(file, 'utf8');
  const lines = txt.split(/\r?\n/);
  lines.forEach((line, idx) => {
    const n = idx + 1;
    if (PATTERN.test(line)) offenders.push({ file, n, line });
  });
}

if (offenders.length) {
  console.error('\nDisallowed TypeScript `any` usage detected. Prefer unknown or precise types.\n');
  for (const { file, n, line } of offenders) {
    console.error(`${path.relative(root, file)}:${n}: ${line.trim()}`);
  }
  process.exit(1);
} else {
  console.log('OK: No explicit `any` detected.');
}

