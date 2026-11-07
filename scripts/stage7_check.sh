#!/usr/bin/env bash
# Linux/macOS QA runner; without vendor it reports SKIP for Laravel, never claims success.
set -euo pipefail
cd "$(dirname "${BASH_SOURCE[0]}")/.."
MODE="${1:-baseline}"
if [[ "$MODE" != "baseline" && "$MODE" != "release" ]]; then
  echo "Usage: bash scripts/stage7_check.sh [baseline|release]" >&2
  exit 2
fi
php scripts/stage7_audit.php "--mode=$MODE"
if command -v node >/dev/null 2>&1; then
  node --test tests/js/*.test.mjs
else
  echo '[WARN] Node tidak tersedia; test JS dilewati.'
  [[ "$MODE" != 'release' ]] || exit 1
fi
if [[ -f vendor/autoload.php ]]; then
  php artisan test
else
  echo '[WARN] Composer vendor belum ada; PHPUnit/Laravel TIDAK dijalankan.'
  [[ "$MODE" != 'release' ]] || exit 1
fi
echo 'Pemeriksaan lokal selesai. Tetap jalankan smoke HTTP dan E2E manual setelah merge semua Tahap.'
