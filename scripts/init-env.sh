#!/usr/bin/env bash
# Auto-generate PROJECT_NAME from the current directory if not already set
# in .env. Useful when cloning the repo into a new directory.
#
# Usage:
#   ./scripts/init-env.sh         # create/update .env with derived name
#   ./scripts/init-env.sh myname  # use a custom project name
#
# After running this script, `docker compose up -d` will use the
# generated PROJECT_NAME to namespace containers, network, and volumes.

set -euo pipefail

ENV_FILE=".env"
EXAMPLE_FILE=".env.example"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"

cd "${PROJECT_ROOT}"

# Derive default project name from directory: lowercase, replace
# non-allowed chars with '-'.
derive_name() {
  basename "${PROJECT_ROOT}" \
    | tr '[:upper:]' '[:lower:]' \
    | sed 's/[^a-z0-9_-]/-/g'
}

PROJECT_NAME_VALUE="${1:-}"
if [[ -z "${PROJECT_NAME_VALUE}" ]]; then
  PROJECT_NAME_VALUE="$(derive_name)"
fi

if [[ ! -f "${EXAMPLE_FILE}" ]]; then
  echo "❌ ${EXAMPLE_FILE} not found in ${PROJECT_ROOT}" >&2
  exit 1
fi

if [[ -f "${ENV_FILE}" ]]; then
  if grep -qE '^PROJECT_NAME=' "${ENV_FILE}"; then
    # Update existing PROJECT_NAME line in place
    sed -i.bak -E "s|^PROJECT_NAME=.*$|PROJECT_NAME=${PROJECT_NAME_VALUE}|" "${ENV_FILE}"
    rm -f "${ENV_FILE}.bak"
    echo "✅ Updated PROJECT_NAME=${PROJECT_NAME_VALUE} in ${ENV_FILE}"
  else
    # Prepend PROJECT_NAME to existing .env
    TMP="$(mktemp)"
    printf 'PROJECT_NAME=%s\n\n' "${PROJECT_NAME_VALUE}" > "${TMP}"
    cat "${ENV_FILE}" >> "${TMP}"
    mv "${TMP}" "${ENV_FILE}"
    echo "✅ Added PROJECT_NAME=${PROJECT_NAME_VALUE} to ${ENV_FILE}"
  fi
else
  cp "${EXAMPLE_FILE}" "${ENV_FILE}"
  sed -i.bak -E "s|^PROJECT_NAME=.*$|PROJECT_NAME=${PROJECT_NAME_VALUE}|" "${ENV_FILE}"
  rm -f "${ENV_FILE}.bak"
  echo "✅ Created ${ENV_FILE} with PROJECT_NAME=${PROJECT_NAME_VALUE}"
fi

echo "ℹ️  Containers/network/volumes will be prefixed with: ${PROJECT_NAME_VALUE}_"
echo "   Run: docker compose up -d"
