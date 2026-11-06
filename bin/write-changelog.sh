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
# Running it again for a version that is already written adds the pending
# entries to that version's block (the changelogger does this). With --date, the
# date in that block's header is also changed to the one given, which the
# changelogger does not do: it keeps the existing date, and writes nothing when
# no entries are pending.
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

# The --date value, when it is a plain YYYY-MM-DD date.
DATE=
PREV=
DRY_RUN=
for arg in "$@"; do
  case "$PREV" in --date) DATE="$arg" ;; esac
  case "$arg" in --date=*) DATE="${arg#--date=}" ;; --dry-run) DRY_RUN=1 ;; esac
  PREV="$arg"
done
case "$DATE" in [0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]) ;; *) DATE= ;; esac

echo "Writing changelog for $VERSION..."
npx --no-install changelogger write --overwrite-version "$VERSION" "$@"

if [ -n "$DATE" ] && [ -z "$DRY_RUN" ]; then
  VERSION="$VERSION" DATE="$DATE" perl -pi -e \
    's/^= \[\Q$ENV{VERSION}\E\] \d{4}-\d{2}-\d{2} =$/= [$ENV{VERSION}] $ENV{DATE} =/' \
    readme.txt changelog.txt
fi
