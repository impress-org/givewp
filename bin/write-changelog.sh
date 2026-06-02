#!/usr/bin/env bash
#
# Release prep: compile pending changelog entries into readme.txt / changelog.txt
# using @stellarwp/changelogger.
#
# Authors add entries during development with:
#   npx changelogger add
# (or `composer run release:changelog-add`), which drops a YAML file into ./changelog.
# This command rolls them up under the release version.
#
# Usage:
#   bin/write-changelog.sh <version> [--date <date>] [--dry-run]
#   composer run release:write-changelog 4.16.0
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
