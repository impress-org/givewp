#!/usr/bin/env bats
#
# Tests for bin/release-prep.sh (the combined pipeline)

load test_helper

@test "fails when no version is given" {
    run "$BIN_DIR/release-prep.sh"
    assert_failure
    assert_output --partial "a version number is required"
}

@test "dry-run runs all three steps in order and completes" {
    run "$BIN_DIR/release-prep.sh" 9.9.9 --dry-run
    assert_success
    assert_output --partial "1/3"
    assert_output --partial "2/3"
    assert_output --partial "3/3"
    assert_output --partial "Release prep complete for 9.9.9"
}

@test "dry-run leaves the real version files untouched" {
    run "$BIN_DIR/release-prep.sh" 9.9.9 --dry-run
    assert_success

    run cat "$REPO_ROOT/give.php"
    refute_output --partial "9.9.9"
}
