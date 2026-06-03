#!/usr/bin/env bats
#
# Tests for bin/replace-since-tags.php

load test_helper

setup() {
    make_sandbox replace-since-tags.php

    mkdir -p "$SANDBOX/src" "$SANDBOX/vendor"

    cat > "$SANDBOX/src/Foo.php" <<'PHP'
<?php
/**
 * @unreleased
 * @unreleased Added a new thing
 * @since TBD changed behavior
 * @since 1.0.0 original
 */
class Foo {}
PHP

    # Excluded directory: should never be touched.
    cat > "$SANDBOX/vendor/Bar.php" <<'PHP'
<?php
/** @unreleased */
class Bar {}
PHP
}

teardown() {
    cleanup_sandbox
}

@test "fails when no version is given" {
    run php "$SANDBOX/bin/replace-since-tags.php"
    assert_failure
    assert_output --partial "a version number is required"
}

@test "rejects a non-semver version" {
    run php "$SANDBOX/bin/replace-since-tags.php" nope
    assert_failure
    assert_output --partial "does not look like a semantic version"
}

@test "replaces @unreleased and @since TBD, preserving descriptions" {
    run php "$SANDBOX/bin/replace-since-tags.php" 9.9.9
    assert_success

    run cat "$SANDBOX/src/Foo.php"
    assert_output --partial "@since 9.9.9"
    assert_output --partial "@since 9.9.9 Added a new thing"
    assert_output --partial "@since 9.9.9 changed behavior"
    # An existing @since version is left alone.
    assert_output --partial "@since 1.0.0 original"
    refute_output --partial "@unreleased"
    refute_output --partial "@since TBD"
}

@test "does not touch excluded directories (vendor)" {
    run php "$SANDBOX/bin/replace-since-tags.php" 9.9.9
    assert_success

    run cat "$SANDBOX/vendor/Bar.php"
    assert_output --partial "@unreleased"
}

@test "dry-run reports changes without writing them" {
    run php "$SANDBOX/bin/replace-since-tags.php" 9.9.9 --dry-run
    assert_success
    assert_output --partial "[dry run]"

    run cat "$SANDBOX/src/Foo.php"
    assert_output --partial "@unreleased"
}
