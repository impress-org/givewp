#!/usr/bin/env bats
#
# Tests for bin/write-changelog.sh

load test_helper

TMP_ENTRY="$BATS_TMPDIR/_skip"

setup() {
    # A temporary changelog entry so `write --dry-run` has something to render.
    TMP_ENTRY="$REPO_ROOT/changelog/zzz-bats-tmp.yaml"
    cat > "$TMP_ENTRY" <<'YAML'
significance: patch
type: fix
entry: Bats temporary test entry
YAML
}

teardown() {
    rm -f "$TMP_ENTRY"
    cleanup_sandbox
}

@test "fails when no version is given" {
    run "$BIN_DIR/write-changelog.sh"
    assert_failure
    assert_output --partial "a version number is required"
}

@test "errors when changelogger is not installed" {
    make_sandbox write-changelog.sh
    run "$SANDBOX/bin/write-changelog.sh" 9.9.9
    assert_failure
    assert_output --partial "is not installed"
}

@test "dry-run renders the new version into both changelog files" {
    run "$BIN_DIR/write-changelog.sh" 9.9.9 --dry-run
    assert_success
    assert_output --partial "= [9.9.9]"
    assert_output --partial "readme.txt"
    assert_output --partial "changelog.txt"
    assert_output --partial "Bats temporary test entry"
}

@test "dry-run does not modify the real changelog files" {
    run "$BIN_DIR/write-changelog.sh" 9.9.9 --dry-run
    assert_success

    run cat "$REPO_ROOT/readme.txt"
    refute_output --partial "= [9.9.9]"
}
