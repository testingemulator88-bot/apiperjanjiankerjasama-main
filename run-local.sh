#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

export API_HOST="${API_HOST:-127.0.0.1}"
export API_PORT="${API_PORT:-8081}"

exec "${ROOT_DIR}/run.sh" local
