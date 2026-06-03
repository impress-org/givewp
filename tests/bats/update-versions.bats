#!/usr/bin/env bats
#
# Tests for bin/update-versions.php

load test_helper

setup() {
    make_sandbox update-versions.php

    cat > "$SANDBOX/.puprc" <<'JSON'
{
    "paths": {
        "versions": [
            { "file": "give.php", "regex": "(define\\('GIVE_VERSION', ')([^']+)" },
            { "file": "give.php", "regex": "(Version: )(.+)" },
            { "file": "readme.txt", "regex": "(Stable tag: )(.+)" }
        ]
    }
}
JSON

    cat > "$SANDBOX/give.php" <<'PHP'
<?php
/**
 * Version: 1.0.0
 */
if (!defined('GIVE_VERSION')) {
    define('GIVE_VERSION', '1.0.0');
}
PHP

    printf 'Stable tag: 1.0.0\n' > "$SANDBOX/readme.txt"
}

teardown() {
    cleanup_sandbox
}

@test "fails when no version is given" {
    run php "$SANDBOX/bin/update-versions.php"
    assert_failure
    assert_output --partial "a version number is required"
}

@test "rejects a non-semver version" {
    run php "$SANDBOX/bin/update-versions.php" not-a-version
    assert_failure
    assert_output --partial "does not look like a semantic version"
}

@test "updates every version declared in .puprc" {
    run php "$SANDBOX/bin/update-versions.php" 9.9.9
    assert_success

    run cat "$SANDBOX/give.php"
    assert_output --partial "define('GIVE_VERSION', '9.9.9')"
    assert_output --partial "Version: 9.9.9"

    run cat "$SANDBOX/readme.txt"
    assert_output --partial "Stable tag: 9.9.9"
}

@test "dry-run reports changes without writing them" {
    run php "$SANDBOX/bin/update-versions.php" 9.9.9 --dry-run
    assert_success
    assert_output --partial "[dry run]"

    run cat "$SANDBOX/give.php"
    assert_output --partial "1.0.0"
    refute_output --partial "9.9.9"
}

@test "errors when a declared version file is missing" {
    rm "$SANDBOX/readme.txt"
    run php "$SANDBOX/bin/update-versions.php" 9.9.9
    assert_failure
    assert_output --partial "readme.txt not found"
}
