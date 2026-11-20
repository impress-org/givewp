#!/usr/bin/env bats
#
# Tests for bin/write-changelog.sh

load test_helper

TMP_ENTRY="$BATS_TMPDIR/_skip"

# A copy of the files the script writes, with its own empty changelog/ directory,
# so tests that write for real never touch the repository's pending entries
# (the changelogger deletes every entry it writes).
make_write_sandbox() {
    make_sandbox write-changelog.sh
    cp "$REPO_ROOT/package.json" "$REPO_ROOT/readme.txt" "$REPO_ROOT/changelog.txt" "$SANDBOX/"
    ln -s "$REPO_ROOT/node_modules" "$SANDBOX/node_modules"
    mkdir "$SANDBOX/changelog"
}

add_sandbox_entry() {
    cat > "$SANDBOX/changelog/zzz-bats-tmp.yaml" <<'YAML'
significance: patch
type: fix
entry: Bats temporary test entry
YAML
}

first_version() {
    sed -n 's/^= \[\([0-9.]*\)\].*/\1/p' "$SANDBOX/readme.txt" | head -n 1
}

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

@test "re-running an existing version adds entries to its block" {
    make_write_sandbox
    add_sandbox_entry
    version="$(first_version)"
    first_entry="$(sed -n "/^= \[$version\]/,/^= \[/p" "$SANDBOX/readme.txt" | grep -m1 '^\* ')"

    run "$SANDBOX/bin/write-changelog.sh" "$version"
    assert_success

    block="$(sed -n "/^= \[$version\]/,/^= \[[^]]*\] .*/p" "$SANDBOX/readme.txt" | sed '1d;$d')"
    [[ "$block" == *"$first_entry"* ]]
    [[ "$block" == *"* Fix - Bats temporary test entry"* ]]
    [ "$(grep -c "^= \[$version\]" "$SANDBOX/readme.txt")" -eq 1 ]
}

@test "--date changes the date of an existing version and keeps its entries" {
    make_write_sandbox
    add_sandbox_entry
    version="$(first_version)"
    entries_before="$(sed -n "/^= \[$version\]/,/^= \[/p" "$SANDBOX/readme.txt" | grep -c '^\* ')"

    run "$SANDBOX/bin/write-changelog.sh" "$version" --date 2031-02-03
    assert_success

    run grep -c "^= \[$version\] 2031-02-03 =$" "$SANDBOX/readme.txt" "$SANDBOX/changelog.txt"
    assert_line "$SANDBOX/readme.txt:1"
    assert_line "$SANDBOX/changelog.txt:1"
    entries_after="$(sed -n "/^= \[$version\]/,/^= \[/p" "$SANDBOX/readme.txt" | grep -c '^\* ')"
    [ "$entries_after" -eq $((entries_before + 1)) ]
}

@test "--date changes the date of an existing version when no entries are pending" {
    make_write_sandbox
    version="$(first_version)"

    run "$SANDBOX/bin/write-changelog.sh" "$version" --date 2031-02-03
    assert_success

    run grep -c "^= \[$version\] 2031-02-03 =$" "$SANDBOX/readme.txt" "$SANDBOX/changelog.txt"
    assert_line "$SANDBOX/readme.txt:1"
    assert_line "$SANDBOX/changelog.txt:1"
}

@test "--date with --dry-run leaves the date of an existing version alone" {
    make_write_sandbox
    add_sandbox_entry
    version="$(first_version)"

    run "$SANDBOX/bin/write-changelog.sh" "$version" --date 2031-02-03 --dry-run
    assert_success

    run grep -c "2031-02-03" "$SANDBOX/readme.txt"
    assert_output "0"
}
