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

@test "dry-run renders the new version into both changelog files in the stellarwp-readme format" {
    run "$BIN_DIR/write-changelog.sh" 9.9.9 --dry-run
    assert_success
    # The changelogger's stellarwp-readme strategy: "= [<version>] <date> =" and "* <Label> - <entry>",
    # the format the other StellarWP plugins use. The labels come from changelogger.types in package.json.
    assert_output --partial "= [9.9.9] "
    assert_output --partial "* Fix - Bats temporary test entry"
    refute_output --partial "= 9.9.9:"
    refute_output --partial "* Fix: "
    assert_output --partial "readme.txt"
    assert_output --partial "changelog.txt"
}

@test "writes the release date as YYYY-MM-DD" {
    run "$BIN_DIR/write-changelog.sh" 9.9.9 --dry-run --date 2026-07-15
    assert_success
    assert_output --partial "= [9.9.9] 2026-07-15 ="
}

@test "dry-run does not modify the real changelog files" {
    run "$BIN_DIR/write-changelog.sh" 9.9.9 --dry-run
    assert_success

    run cat "$REPO_ROOT/readme.txt"
    refute_output --partial "= [9.9.9]"
}

@test "the changelog history is in the stellarwp-readme format" {
    # bin/convert-changelog-format.js moved the history once; a legacy header or bullet
    # ("= 4.19.1: October 27th, 2026 =", "* Fix: ...") would sit next to the new entries.
    run node "$BIN_DIR/convert-changelog-format.js" --check "$REPO_ROOT/readme.txt" "$REPO_ROOT/changelog.txt"
    assert_success
}
