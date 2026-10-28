#!/usr/bin/env bats
#
# Tests for bin/convert-changelog-format.js: the conversion, the verification it
# runs before writing, and the --check and --verify modes.

load test_helper

CONVERT="$BIN_DIR/convert-changelog-format.js"
FIXTURES="$TESTS_BATS_DIR/fixtures/legacy-changelog"

setup() {
    WORK="$(mktemp -d)"
    FIXTURE="$WORK/readme.txt"
    cat > "$FIXTURE" <<'TXT'
=== GiveWP ===

= Updating =

Update as usual.

== Changelog ==

= 4.19.1: October 27th, 2026 =
* Fix: Restored the Logout link: it was hidden on mobile.
* Security: Escaped the donor search term.

= 2.22.2: September, 28th 2022 =
* New: Added a thing.
* Fixed issue - a bullet with no label and a dash in it.

= 1.8.8: May 30, 2017  =
* Fixed issue where pending payments were marked as abandoned.

= 1.3.6: Mar 9, 2016 =
* Happy new year!

= 0.9 beta =
* Initial plugin release.

[See the full GiveWP changelog on Github](https://example.test/changelog.txt)

== Upgrade Notice ==

= 4.19.1: October 27th, 2026 =
* Fix: This section is not the changelog and stays as it is.
TXT
    cp "$FIXTURE" "$WORK/original.txt"
}

teardown() {
    rm -rf "$WORK"
}

# --- conversion -------------------------------------------------------------

@test "converts the headers and the labelled bullets of the changelog section" {
    run node "$CONVERT" "$FIXTURE"
    assert_success
    assert_output --partial "4 header(s) and 3 bullet(s) converted"

    run cat "$FIXTURE"
    assert_line "= [4.19.1] 2026-10-27 ="
    assert_line "* Fix - Restored the Logout link: it was hidden on mobile."
    assert_line "* Security - Escaped the donor search term."
    assert_line "= [2.22.2] 2022-09-28 ="
    assert_line "* New - Added a thing."
    assert_line "= [1.8.8] 2017-05-30 ="
    assert_line "= [1.3.6] 2016-03-09 ="
}

@test "verifies the result against the original before writing, and says so" {
    run node "$CONVERT" "$FIXTURE"
    assert_success
    assert_output --partial "verified against the original: 5 version(s) and 7 entries match"
}

@test "puts a blank line after a converted header, as the changelogger writes new entries" {
    node "$CONVERT" "$FIXTURE"
    run bash -c "sed -n '9,11p' '$FIXTURE'"
    assert_success
    assert_output "$(printf '= [4.19.1] 2026-10-27 =\n\n* Fix - Restored the Logout link: it was hidden on mobile.')"
}

@test "leaves bullets without a label, undated headers and the link line alone" {
    run node "$CONVERT" "$FIXTURE"
    assert_success

    run cat "$FIXTURE"
    assert_line "* Fixed issue - a bullet with no label and a dash in it."
    assert_line "* Fixed issue where pending payments were marked as abandoned."
    assert_line "* Happy new year!"
    assert_line "= 0.9 beta ="
    assert_line "[See the full GiveWP changelog on Github](https://example.test/changelog.txt)"
}

@test "touches nothing outside the changelog section" {
    run node "$CONVERT" "$FIXTURE"
    assert_success

    run cat "$FIXTURE"
    assert_line "= Updating ="
    # The Upgrade Notice after the changelog keeps the legacy header and bullet.
    assert_line "= 4.19.1: October 27th, 2026 ="
    assert_line "* Fix: This section is not the changelog and stays as it is."
}

@test "running it again changes nothing" {
    node "$CONVERT" "$FIXTURE"
    cp "$FIXTURE" "$WORK/first-pass.txt"

    run node "$CONVERT" "$FIXTURE"
    assert_success
    assert_output --partial "nothing to convert"
    run diff "$WORK/first-pass.txt" "$FIXTURE"
    assert_success
}

@test "converts the legacy half of a file that already has entries in the new format" {
    printf '== Changelog ==\n= [4.20.0] 2026-11-01 =\n\n* Fix - Already new.\n\n= 4.19.1: October 27th, 2026 =\n* Fix: Still old.\n' > "$FIXTURE"
    run node "$CONVERT" "$FIXTURE"
    assert_success
    assert_output --partial "1 header(s) and 1 bullet(s) converted; verified against the original: 2 version(s) and 2 entries match"

    run cat "$FIXTURE"
    assert_line "= [4.20.0] 2026-11-01 ="
    assert_line "* Fix - Already new."
    assert_line "= [4.19.1] 2026-10-27 ="
    assert_line "* Fix - Still old."
}

@test "writes nothing when a header's date cannot be read, and reports it" {
    printf '== Changelog ==\n= 9.9.9: someday soon =\n* Fix: x\n' > "$FIXTURE"
    cp "$FIXTURE" "$WORK/untouched.txt"
    run node "$CONVERT" "$FIXTURE"
    assert_failure
    assert_output --partial "could not read the date: = 9.9.9: someday soon ="
    assert_output --partial "not written"

    run diff "$WORK/untouched.txt" "$FIXTURE"
    assert_success
}

@test "reads the legacy date shapes the history has" {
    run node -e '
        const { parseLegacyDate } = require(process.argv[1]);
        console.log([
            "October 27th, 2026", "September, 28th 2022", "January 31st, 2018 ",
            "May 30, 2017", "Mar 9, 2016", "Oct 1, 2015", "June 31, 2016",
        ].map(parseLegacyDate).join(" "));
    ' "$CONVERT"
    assert_success
    assert_output "2026-10-27 2022-09-28 2018-01-31 2017-05-30 2016-03-09 2015-10-01 2016-06-31"
}

@test "says so when the file cannot be read" {
    run node "$CONVERT" "$WORK/missing.txt"
    assert_failure 2
    assert_output --partial "missing.txt: cannot read it (ENOENT)"
}

@test "says so when the file has no changelog section, and leaves it" {
    printf '=== Plugin ===\n\n= 1.0.0: May 1st, 2026 =\n* Fix: x\n' > "$FIXTURE"
    cp "$FIXTURE" "$WORK/untouched.txt"
    run node "$CONVERT" "$FIXTURE"
    assert_failure 1
    assert_output --partial 'no "== Changelog ==" section'

    run diff "$WORK/untouched.txt" "$FIXTURE"
    assert_success
}

@test "fails with a usage message when no file is given" {
    run node "$CONVERT"
    assert_failure
    assert_output --partial "Usage:"
}

# --- --check ----------------------------------------------------------------

@test "--check fails while legacy lines remain and passes once converted" {
    run node "$CONVERT" --check "$FIXTURE"
    assert_failure
    assert_output --partial "legacy changelog line(s) remain"
    assert_output --partial "= 4.19.1: October 27th, 2026 ="

    node "$CONVERT" "$FIXTURE"

    run node "$CONVERT" --check "$FIXTURE"
    assert_success
    assert_output --partial "no legacy changelog lines"
}

# --- --verify ---------------------------------------------------------------
# The verifier reads both files with parsers of its own; these tamper with a
# converted copy and expect it to notice.

@test "--verify passes on a faithful conversion and reports what it compared" {
    node "$CONVERT" "$FIXTURE"
    run node "$CONVERT" --verify "$WORK/original.txt" "$FIXTURE"
    assert_success
    assert_output --partial "5 version(s) and 7 entries match"
}

@test "--verify fails when an entry is missing from the converted file" {
    node "$CONVERT" "$FIXTURE"
    sed -i '/Escaped the donor search term/d' "$FIXTURE"
    run node "$CONVERT" --verify "$WORK/original.txt" "$FIXTURE"
    assert_failure
    assert_output --partial "version 4.19.1"
    assert_output --partial "2 entries in the original, 1 in the converted file"
}

@test "--verify fails when an entry's text changed" {
    node "$CONVERT" "$FIXTURE"
    sed -i 's/Added a thing/Added a thong/' "$FIXTURE"
    run node "$CONVERT" --verify "$WORK/original.txt" "$FIXTURE"
    assert_failure
    assert_output --partial "should read"
    assert_output --partial "* New - Added a thing."
}

@test "--verify fails when a date does not read as the original" {
    node "$CONVERT" "$FIXTURE"
    sed -i 's/2022-09-28/2022-09-29/' "$FIXTURE"
    run node "$CONVERT" --verify "$WORK/original.txt" "$FIXTURE"
    assert_failure
    assert_output --partial 'date "September, 28th 2022" should read 2022-09-28, the converted file has 2022-09-29'
}

@test "--verify fails when a version is dropped" {
    node "$CONVERT" "$FIXTURE"
    sed -i '/^= \[2.22.2\]/,/^$/d' "$FIXTURE"
    run node "$CONVERT" --verify "$WORK/original.txt" "$FIXTURE"
    assert_failure
    assert_output --partial "the original has 5 version header(s), the converted file 4"
}

@test "--verify fails when text outside the changelog section changed" {
    node "$CONVERT" "$FIXTURE"
    sed -i 's/Update as usual./Update as you like./' "$FIXTURE"
    run node "$CONVERT" --verify "$WORK/original.txt" "$FIXTURE"
    assert_failure
    assert_output --partial "the text before the changelog section differs"
}

@test "--verify fails when the link line below the entries is gone" {
    node "$CONVERT" "$FIXTURE"
    sed -i '/See the full GiveWP changelog/d' "$FIXTURE"
    run node "$CONVERT" --verify "$WORK/original.txt" "$FIXTURE"
    assert_failure
    assert_output --partial "neither header nor entry differ"
}

@test "--verify reads the legacy dates independently of the conversion" {
    run node -e '
        const { legacyDateToIso } = require(process.argv[1]);
        console.log([
            "October 27th, 2026", "September, 28th 2022", "January 31st, 2018 ",
            "May 30, 2017", "Mar 9, 2016", "Oct 1, 2015", "June 31, 2016",
        ].map(legacyDateToIso).join(" "));
    ' "$CONVERT"
    assert_success
    assert_output "2026-10-27 2022-09-28 2018-01-31 2017-05-30 2016-03-09 2015-10-01 2016-06-31"
}

# --- the real history -------------------------------------------------------

@test "the real readme.txt history converts and verifies without losing anything" {
    cp "$FIXTURES/readme.txt" "$WORK/real-readme.txt"
    run node "$CONVERT" "$WORK/real-readme.txt"
    assert_success
    assert_output --partial "58 header(s) and 270 bullet(s) converted; verified against the original: 58 version(s) and 270 entries match"
}

@test "the real changelog.txt history converts and verifies without losing anything" {
    cp "$FIXTURES/changelog.txt" "$WORK/real-changelog.txt"
    run node "$CONVERT" "$WORK/real-changelog.txt"
    assert_success
    assert_output --partial "312 header(s) and 2009 bullet(s) converted; verified against the original: 318 version(s) and 2020 entries match"
}
