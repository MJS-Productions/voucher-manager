#!/usr/bin/env bash
set -euo pipefail
VERSION="${1:-0.1.0-alpha}"
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DIST_DIR="${PROJECT_ROOT}/dist"
BUILD_DIR="${DIST_DIR}/voucher-manager"
rm -rf "${BUILD_DIR}" && mkdir -p "${BUILD_DIR}"
git -C "${PROJECT_ROOT}" archive HEAD | tar -x -C "${BUILD_DIR}"
composer install --working-dir="${BUILD_DIR}" --no-dev --classmap-authoritative --no-interaction
(cd "${DIST_DIR}" && zip -qr "voucher-manager-${VERSION}.zip" voucher-manager)
echo "Created ${DIST_DIR}/voucher-manager-${VERSION}.zip"
