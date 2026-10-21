#!/usr/bin/env bash
# Start the site in the background so it keeps running after terminals close.
# Safe to run repeatedly: it does nothing when the site already answers.
set -euo pipefail

cd "$(dirname "${BASH_SOURCE[0]}")/.."

port="${PORT:-8000}"
log=/tmp/serve.log
health="http://127.0.0.1:${port}/up"

if [ -n "${CODESPACE_NAME:-}" ]; then
    url="https://${CODESPACE_NAME}-${port}.${GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN:-app.github.dev}"
else
    url="http://127.0.0.1:${port}"
fi

if curl --fail --silent --output /dev/null "$health"; then
    echo "[beloved] Server already running: ${url}"
    exit 0
fi

PORT="$port" setsid nohup bash .devcontainer/serve.sh >> "$log" 2>&1 < /dev/null &

for _ in $(seq 1 120); do
    if curl --fail --silent --output /dev/null "$health"; then
        echo "[beloved] Server running: ${url}"
        echo "[beloved] Log: ${log}"
        exit 0
    fi
    sleep 1
done

echo "[beloved] Server did not answer ${health} within 120 seconds. Last log lines:" >&2
tail -n 30 "$log" >&2 || true
exit 1
