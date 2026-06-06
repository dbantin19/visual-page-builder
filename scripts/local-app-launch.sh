#!/usr/bin/env bash
# Helper for "Poseidon Local.app": start the local server if needed, wait for the
# listening socket, then open the browser. Launched fully detached so the .app
# never blocks. Server output -> $LOG; this helper's own trace -> $DBG.
set -uo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
URL="http://localhost:8000"
LOG="/tmp/poseidon-serve.log"
DBG="/tmp/poseidon-app.log"

log() { printf '%s %s\n' "$(/bin/date '+%H:%M:%S')" "$*" >>"$DBG" 2>/dev/null || true; }

log "--- launch helper (ROOT=$ROOT, PATH=$PATH) ---"

if /usr/sbin/lsof -nP -iTCP:8000 -sTCP:LISTEN >/dev/null 2>&1; then
    log "already listening; will just open browser"
else
    log "no listener; starting local-serve.sh"
    nohup /bin/bash "$ROOT/scripts/local-serve.sh" >"$LOG" 2>&1 </dev/null &
    log "started local-serve.sh as pid $!"
fi

# Wait up to ~90s for the listening socket (first run may fetch PHP via pkgx).
for i in $(/usr/bin/seq 1 90); do
    if /usr/sbin/lsof -nP -iTCP:8000 -sTCP:LISTEN >/dev/null 2>&1; then
        log "port listening after ${i}s; opening browser"
        /usr/bin/open "$URL"
        log "open returned $?"
        exit 0
    fi
    /bin/sleep 1
done

log "TIMEOUT after 90s; server never listened. Opening log. Tail:"
/usr/bin/tail -n 20 "$LOG" >>"$DBG" 2>/dev/null || true
/usr/bin/open "$LOG"
/usr/bin/osascript -e 'display notification "Poseidon did not start — opened the log" with title "Poseidon Local"' >/dev/null 2>&1 || true
exit 1
