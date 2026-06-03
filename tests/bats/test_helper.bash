#!/usr/bin/env bash
#
# Shared setup for the release-script Bats suite.
#

TESTS_BATS_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$TESTS_BATS_DIR/../.." && pwd)"
BIN_DIR="$REPO_ROOT/bin"

export REPO_ROOT BIN_DIR

# Load the assertion helpers installed via npm (node_modules/bats-*).
export BATS_LIB_PATH="$REPO_ROOT/node_modules:${BATS_LIB_PATH:-}"
bats_load_library bats-support
bats_load_library bats-assert

# Create an isolated copy of a bin script so the PHP scripts (which resolve the
# project root from their own location) operate on fixtures instead of the repo.
#
#   make_sandbox <script-name>
#
# Sets SANDBOX to a fresh temp dir containing bin/<script-name>.
make_sandbox() {
    local script="$1"
    SANDBOX="$(mktemp -d)"
    mkdir -p "$SANDBOX/bin"
    cp "$BIN_DIR/$script" "$SANDBOX/bin/$script"
    # PHP scripts require this shared helper relative to their own location.
    if [ -f "$BIN_DIR/lib/release-utils.php" ]; then
        mkdir -p "$SANDBOX/bin/lib"
        cp "$BIN_DIR/lib/release-utils.php" "$SANDBOX/bin/lib/release-utils.php"
    fi
}

cleanup_sandbox() {
    if [ -n "${SANDBOX:-}" ] && [ -d "$SANDBOX" ]; then
        rm -rf "$SANDBOX"
    fi
}
