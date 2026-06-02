#!/usr/bin/env bash
#
# Compile pending changelog entries into readme.txt / changelog.txt using
# @stellarwp/changelogger (and the custom legacy-format strategy).
#
# Authors add entries during development with:
#   composer run changelog:add
# which drops a YAML file into ./changelog. This command rolls them up.
#
# A version is required. changelogger's own auto-versioning is NOT used: it
# only reads bracketed "= [x.y.z]" headers, which the legacy GiveWP format
# (= x.y.z: date =) doesn't use, so it would fall back to 0.1.0. The release
# version is supplied explicitly (release:prep passes it through).
#
# Usage:
#   bin/write-changelog.sh <version> [--date <date>] [--dry-run]
#   composer run changelog:write -- 4.16.0
#
set -euo pipefail

cd "$(dirname "$0")/.."

VERSION="${1:-}"
shift || true

if [ -z "$VERSION" ]; then
  echo "Error: a version number is required." >&2
  echo "Usage: bin/write-changelog.sh <version> [--date <date>] [--dry-run]" >&2
  exit 1
fi

if ! command -v npx >/dev/null 2>&1; then
  echo "Error: npx (Node.js) is required to run @stellarwp/changelogger." >&2
  exit 1
fi

if [ ! -x "node_modules/.bin/changelogger" ]; then
  echo "Error: @stellarwp/changelogger is not installed. Run \`npm install\` first." >&2
  exit 1
fi

echo "Writing changelog for $VERSION..."
exec npx --no-install changelogger write --overwrite-version "$VERSION" "$@"
