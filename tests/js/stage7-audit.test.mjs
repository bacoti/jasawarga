import test from 'node:test';
import assert from 'node:assert/strict';
import { spawnSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
const php = spawnSync('php', ['-v'], { encoding: 'utf8', timeout: 5000 });
const available = !php.error && php.status === 0;

function audit(mode) {
  const result = spawnSync('php', ['scripts/stage7_audit.php', `--mode=${mode}`, '--json'], {
    cwd: root, encoding: 'utf8', timeout: 60000,
  });
  assert.equal(result.error, undefined, result.error?.message);
  assert.ok(result.stdout.startsWith('{'), 'CLI must emit parseable JSON');
  return { exitCode: result.status, output: JSON.parse(result.stdout) };
}

test('Stage7 baseline produces machine-readable checks without hard blockers', { skip: !available }, () => {
  const { exitCode, output } = audit('baseline');
  assert.equal(exitCode, 0);
  assert.equal(output.mode, 'baseline');
  assert.equal(output.summary.FAIL, 0);
  assert.ok(output.checks.some((c) => c.id === 'review-verification-adapter'));
  assert.ok(output.checks.some((c) => c.id === 'php-lint'));
});

test('Stage7 release exit code mirrors reported FAIL checks', { skip: !available }, () => {
  const { exitCode, output } = audit('release');
  assert.equal(exitCode === 1, output.summary.FAIL > 0);
  assert.equal(output.mode, 'release');
});
