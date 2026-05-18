#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
MODE="${1:-local}"
HOST="${API_HOST:-127.0.0.1}"
PORT="${API_PORT:-8081}"

case "${MODE}" in
  local)
    API_DIR="${ROOT_DIR}/api/2026"
    ;;
  prod)
    API_DIR="${ROOT_DIR}/api"
    ;;
  *)
    echo "[ERROR] Unknown mode: ${MODE}" >&2
    echo "Usage: ./run.sh [local|prod]" >&2
    exit 1
    ;;
esac

if [ ! -d "${API_DIR}" ]; then
  echo "[ERROR] API directory not found: ${API_DIR}" >&2
  exit 1
fi

if ! command -v php >/dev/null 2>&1; then
  echo "[ERROR] php command not found in PATH" >&2
  exit 1
fi

echo "[API] Mode: ${MODE}"
echo "[API] Starting at http://${HOST}:${PORT}"
echo "[API] Root: ${API_DIR}"
cd "${API_DIR}"
exec php -S "${HOST}:${PORT}"
