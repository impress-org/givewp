#!/usr/bin/env bash
#
# Release prep: run the full version-bump pipeline in order.
#
#   1. Bump every version declared in .puprc -> paths.versions
#   2. Replace @unreleased / @since TBD docblock tags with @since <version>
#   3. Compile pending changelog entries into readme.txt / changelog.txt
#
# Usage:
#   bin/release-prep.sh <version> [--date <date>] [--dry-run]
#   composer run release:prep 4.16.0
#
set -euo pipefail

cd "$(dirname "$0")/.."

VERSION="${1:-}"
shift || true

if [ -z "$VERSION" ]; then
  echo "Error: a version number is required." >&2
  echo "Usage: bin/release-prep.sh <version> [--date <date>] [--dry-run]" >&2
  exit 1
fi

# --dry-run is forwarded to every step; any other flags (e.g. --date) apply to
# the changelog step, which receives the full remaining argument list ("$@").
DRY_RUN=
case " $* " in
  *" --dry-run "*) DRY_RUN=1 ;;
esac

echo "=== 1/3: Updating version strings ==="
php bin/update-versions.php "$VERSION" ${DRY_RUN:+--dry-run}

echo ""
echo "=== 2/3: Replacing @unreleased / @since TBD tags ==="
php bin/replace-since-tags.php "$VERSION" ${DRY_RUN:+--dry-run}

echo ""
echo "=== 3/3: Writing changelog ==="
bin/write-changelog.sh "$VERSION" "$@"

echo ""
echo "✓ Release prep complete for $VERSION"
