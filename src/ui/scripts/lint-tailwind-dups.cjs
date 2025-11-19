#!/usr/bin/env node
/*
  Lightweight check for duplicate Tailwind classes inside a single className string.
  Flags lines like: className="border border px-2 ..."
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

for (const file of files) {
  const txt = fs.readFileSync(file, 'utf8');
  const lines = txt.split(/\r?\n/);
  lines.forEach((line, idx) => {
    const n = idx + 1;
    const m = line.match(/className=\{?['"]([^'"]+)['"]\}?/);
    if (!m) return;
    const classes = m[1].trim().split(/\s+/);
    const seen = new Set();
    const dups = new Set();
    for (const c of classes) {
      const key = c; // naive: don't normalize variants/prefixes
      if (seen.has(key)) dups.add(key);
      seen.add(key);
    }
    if (dups.size > 0) {
      offenders.push({ file, n, classes: Array.from(dups).join(', '), line: line.trim() });
    }
  });
}

if (offenders.length) {
  console.error('\nDuplicate Tailwind classes detected in className strings.\n');
  for (const { file, n, classes } of offenders) {
    console.error(`${path.relative(root, file)}:${n}: duplicate classes: ${classes}`);
  }
  process.exit(1);
} else {
  console.log('OK: No duplicate Tailwind classes found.');
}
