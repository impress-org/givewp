#!/usr/bin/env bash
#
# Compile pending changelog entries into readme.txt / changelog.txt with
# @stellarwp/changelogger and its stellarwp-readme strategy, which writes
# "= [x.y.z] YYYY-MM-DD =" headers.
#
# Authors add entries during development with:
#   composer run changelog:add
# which drops a YAML file into ./changelog. This command rolls them up.
#
# A version is required: release:prep passes the version being released, and
# the changelog has to carry that one rather than a version the changelogger
# would infer from the previous header.
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
