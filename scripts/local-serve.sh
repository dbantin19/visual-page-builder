#!/usr/bin/env bash
# Poseidon local dev server: serves the Laravel app on PORT (default 8000) using
# PHP 8.4 (via pkgx) and the already-built Vite assets. Started by
# "Poseidon Local.app" through scripts/local-app-launch.sh.
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PKGX="/Users/derrickbanting/Documents/Codex/beanstalk/git-a2opinion-git-beanstalkapp-com-a2opinion/work/bin/pkgx"
PORT="${PORT:-8000}"

if [[ ! -x "$PKGX" ]]; then
    echo "pkgx not found at $PKGX" >&2
    exit 1
fi

# Force built-asset mode (ignore any stale Vite dev-server "hot" file).
rm -f "$ROOT/public/hot"

cd "$ROOT"
exec "$PKGX" +php.net@8.4 php artisan serve --host=127.0.0.1 --port="$PORT"
