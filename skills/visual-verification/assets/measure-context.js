// Measure real token consumption from Claude Code session transcripts.
// Usage:
//   node ~/.claude/scripts/measure-context.js              -> all projects, ranked by cost
//   node ~/.claude/scripts/measure-context.js --base       -> baseline context of the newest session
//
// "Baseline" = system prompt + CLAUDE.md + skill listings + MCP tool schemas + SessionStart hooks,
// read from cache on EVERY turn. It is the multiplier: baseline x turns = most of the bill.
const fs = require('fs'), path = require('path');
const ROOT = path.join(process.env.USERPROFILE || process.env.HOME, '.claude', 'projects');
// Opus list price per million tokens.
const P = { cacheRead: 1.5, cacheWrite: 18.75, output: 75, input: 15 };

function usages(file) {
  const out = [];
  for (const line of fs.readFileSync(file, 'utf8').split('\n')) {
    if (!line.trim()) continue;
    let j; try { j = JSON.parse(line) } catch { continue }
    const u = j.message && j.message.usage;
    if (u) out.push(u);
  }
  return out;
}

if (process.argv.includes('--base')) {
  let newest = null;
  for (const d of fs.readdirSync(ROOT)) {
    const dir = path.join(ROOT, d);
    if (!fs.statSync(dir).isDirectory()) continue;
    for (const f of fs.readdirSync(dir).filter(x => x.endsWith('.jsonl'))) {
      const p = path.join(dir, f), m = fs.statSync(p).mtimeMs;
      if (!newest || m > newest.m) newest = { p, m };
    }
  }
  const u = usages(newest.p)[0];
  const base = (u.cache_read_input_tokens || 0) + (u.cache_creation_input_tokens || 0) + (u.input_tokens || 0);
  console.log('newest session:', path.basename(newest.p));
  console.log('BASELINE CONTEXT:', base.toLocaleString(), 'tokens');
  console.log('(audit 2026-08-24 measured 84,477 before pruning)');
  process.exit(0);
}

const rows = [];
let G = { cr: 0, cw: 0, o: 0, i: 0, n: 0 };
for (const d of fs.readdirSync(ROOT)) {
  const dir = path.join(ROOT, d);
  if (!fs.statSync(dir).isDirectory()) continue;
  const s = { cr: 0, cw: 0, o: 0, i: 0, n: 0 };
  for (const f of fs.readdirSync(dir).filter(x => x.endsWith('.jsonl')))
    for (const u of usages(path.join(dir, f))) {
      s.n++; s.i += u.input_tokens || 0; s.o += u.output_tokens || 0;
      s.cr += u.cache_read_input_tokens || 0; s.cw += u.cache_creation_input_tokens || 0;
    }
  if (!s.n) continue;
  for (const k of ['cr', 'cw', 'o', 'i', 'n']) G[k] += s[k];
  rows.push({ d, ...s, cost: s.cr / 1e6 * P.cacheRead + s.cw / 1e6 * P.cacheWrite + s.o / 1e6 * P.output + s.i / 1e6 * P.input });
}
rows.sort((a, b) => b.cost - a.cost);
console.log('PROJECT                                   turns   cacheR   avgCtx     est$');
for (const r of rows.slice(0, 15))
  console.log(r.d.replace('C--Users-Juan-', '').slice(0, 40).padEnd(40), String(r.n).padStart(6),
    ((r.cr / 1e6).toFixed(0) + 'M').padStart(8), (Math.round(r.cr / r.n / 1000) + 'k').padStart(8),
    ('$' + r.cost.toFixed(0)).padStart(9));
const gc = G.cr / 1e6 * P.cacheRead + G.cw / 1e6 * P.cacheWrite + G.o / 1e6 * P.output + G.i / 1e6 * P.input;
console.log('TOTAL: turns', G.n, '| cacheR', (G.cr / 1e9).toFixed(2) + 'B', '| out', (G.o / 1e6).toFixed(1) + 'M', '| est $' + gc.toFixed(0));
