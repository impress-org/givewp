#!/usr/bin/env node
/**
 * Converts the changelog section of a readme-style file from GiveWP's legacy
 * format to the changelogger's stellarwp-readme format, in place, and checks
 * its own work before writing anything:
 *
 *   = 4.19.1: October 27th, 2026 =     ->  = [4.19.1] 2026-10-27 =
 *   * Fix: Restored the Logout link.   ->  * Fix - Restored the Logout link.
 *
 * Only the lines between "== Changelog ==" and the next "== Section ==" heading
 * change; everything else stays byte for byte. A converted header is followed by
 * a blank line, as the changelogger writes it, so old and new entries read the
 * same. Running it again changes nothing, so it is safe on a file that is
 * already converted or half converted.
 *
 * Before the file is written, the result is compared with the original by the
 * verifier in the second half of this file, which reads both texts with parsers
 * of its own rather than trusting the conversion: the text outside the section,
 * the sequence of versions, each date, each entry's label and text, and every
 * line that is neither header nor entry have to match. When they don't, nothing
 * is written, the problems are listed with their lines, and the exit code is 1.
 *
 * Old entries are not all regular (abbreviated months, "September, 28th 2022",
 * no ordinal, trailing spaces), so a date is read token by token: a month name
 * or its three-letter form, a day with an optional ordinal and a four-digit year,
 * in any order, with commas and extra spaces ignored. The first releases of 2015
 * have headers with no date at all ("= 0.9 beta ="); those are not the legacy
 * format and are left alone.
 *
 *   node bin/convert-changelog-format.js readme.txt changelog.txt
 *   node bin/convert-changelog-format.js --check readme.txt       # exit 1 while legacy lines remain
 *   node bin/convert-changelog-format.js --verify <original> <converted>
 *
 * `--verify` runs the same comparison on two files you name, so the result can be
 * checked again with the original taken from git: `git show HEAD~1:readme.txt > before.txt`.
 * The add-ons are converted with this command, run from this checkout against
 * their readme.txt.
 */

"use strict";

const fs = require("node:fs");

// ---------------------------------------------------------------------------
// Conversion
// ---------------------------------------------------------------------------

const MONTHS = [
    "january", "february", "march", "april", "may", "june",
    "july", "august", "september", "october", "november", "december",
];

const SECTION_HEADING = /^== .+ ==\s*$/;
const CHANGELOG_HEADING = /^== Changelog ==\s*$/;
// "= 4.19.1: October 27th, 2026 =", tolerant of extra spaces around the "=".
const LEGACY_HEADER = /^=\s*(\S+?):\s*(.+?)\s*=\s*$/;
// "* Fix: text" with a short label; "* Fix - text" (already converted) has no colon label.
const LEGACY_BULLET = /^\* ([A-Za-z][A-Za-z ]{0,30}?): (.*)$/;

/**
 * Reads a legacy date such as "October 27th, 2026", "September, 28th 2022",
 * "May 30, 2017" or "Mar 9, 2016" as YYYY-MM-DD. Returns undefined when the
 * three parts can't be found.
 */
function parseLegacyDate(text) {
    const tokens = text.replace(/,/g, " ").trim().split(/\s+/);
    let month, day, year;
    for (const token of tokens) {
        const lower = token.toLowerCase();
        const monthIndex = MONTHS.findIndex((m) => m === lower || m.slice(0, 3) === lower);
        if (monthIndex !== -1 && month === undefined) {
            month = monthIndex + 1;
        } else if (/^\d{4}$/.test(token) && year === undefined) {
            year = token;
        } else if (/^\d{1,2}(st|nd|rd|th)?$/i.test(token) && day === undefined) {
            day = token.replace(/\D/g, "");
        } else {
            return undefined;
        }
    }
    if (month === undefined || day === undefined || year === undefined) return undefined;
    return `${year}-${String(month).padStart(2, "0")}-${day.padStart(2, "0")}`;
}

/** Converts one version header line, or returns undefined when it isn't a legacy header. */
function convertHeader(line) {
    const match = line.match(LEGACY_HEADER);
    if (!match) return undefined;
    const date = parseLegacyDate(match[2]);
    if (!date) return undefined;
    return `= [${match[1]}] ${date} =`;
}

/** Converts one "* Label: entry" bullet, or returns undefined when it isn't one. */
function convertBullet(line) {
    const match = line.match(LEGACY_BULLET);
    if (!match) return undefined;
    return `* ${match[1]} - ${match[2]}`;
}

/**
 * Converts the changelog section of a whole file. Returns the new text, how
 * many headers and bullets changed, and the legacy headers it could not read.
 */
function convertChangelog(text) {
    const lines = text.split("\n");
    let inChangelog = false;
    let headers = 0;
    let bullets = 0;
    const leftovers = [];
    const out = [];
    lines.forEach((line, index) => {
        if (CHANGELOG_HEADING.test(line)) {
            inChangelog = true;
            out.push(line);
            return;
        }
        if (inChangelog && SECTION_HEADING.test(line)) inChangelog = false;
        if (!inChangelog) {
            out.push(line);
            return;
        }

        if (LEGACY_HEADER.test(line)) {
            const header = convertHeader(line);
            if (header === undefined) {
                leftovers.push({ line: index + 1, text: line });
                out.push(line);
                return;
            }
            headers += 1;
            out.push(header);
            // The changelogger leaves a blank line between a header and its entries.
            if ((lines[index + 1] ?? "").trim() !== "") out.push("");
            return;
        }
        const bullet = convertBullet(line);
        if (bullet !== undefined) {
            bullets += 1;
            out.push(bullet);
            return;
        }
        out.push(line);
    });
    return { text: out.join("\n"), headers, bullets, leftovers };
}

/** Lines in the changelog section still in the legacy shape, for --check. */
function legacyLines(text) {
    const lines = text.split("\n");
    let inChangelog = false;
    const found = [];
    lines.forEach((line, index) => {
        if (CHANGELOG_HEADING.test(line)) { inChangelog = true; return; }
        if (inChangelog && SECTION_HEADING.test(line)) inChangelog = false;
        if (!inChangelog) return;
        if (LEGACY_HEADER.test(line) || LEGACY_BULLET.test(line)) {
            found.push({ line: index + 1, text: line });
        }
    });
    return found;
}

// ---------------------------------------------------------------------------
// Verification: reads the original and the converted text with parsers of its
// own, so a mistake in the conversion above can't approve itself.
// ---------------------------------------------------------------------------

const MONTH_NUMBERS = {
    jan: 1, feb: 2, mar: 3, apr: 4, may: 5, jun: 6,
    jul: 7, aug: 8, sep: 9, oct: 10, nov: 11, dec: 12,
};

const LEGACY_DATED_HEADER = /^=\s*(\S+?):\s*(.+?)\s*=\s*$/;
const NEW_DATED_HEADER = /^= \[([^\]]+)\] (\S+) =$/;
const UNDATED_HEADER = /^=\s*([^:=\[\]]+?)\s*=\s*$/;
const LABELLED_LEGACY_ENTRY = /^\* ([A-Za-z][A-Za-z ]{0,30}?): (.*)$/;

/**
 * Reads a legacy date with regular expressions rather than the token walk
 * above: the year is the four-digit number, the month the first word that
 * starts like a month name, the day the first one- or two-digit number that is
 * not the year. Undefined when one of them is missing.
 */
function legacyDateToIso(text) {
    const year = text.match(/\b(\d{4})\b/)?.[1];
    const monthWord = text.match(/\b([A-Za-z]{3,})\b/)?.[1];
    const month = monthWord ? MONTH_NUMBERS[monthWord.slice(0, 3).toLowerCase()] : undefined;
    const day = text.replace(year ?? "", "").match(/\b(\d{1,2})(?:st|nd|rd|th)?\b/)?.[1];
    if (!year || !month || !day) return undefined;
    return `${year}-${String(month).padStart(2, "0")}-${day.padStart(2, "0")}`;
}

/** Splits a file into the lines before the changelog, the changelog, and the lines after. */
function splitSections(text) {
    const lines = text.split("\n");
    const start = lines.findIndex((line) => CHANGELOG_HEADING.test(line));
    if (start === -1) return { before: lines, changelog: [], after: [], offset: 0 };
    let end = lines.findIndex((line, index) => index > start && SECTION_HEADING.test(line));
    if (end === -1) end = lines.length;
    return {
        before: lines.slice(0, start + 1),
        changelog: lines.slice(start + 1, end),
        after: lines.slice(end),
        offset: start + 2, // 1-based line number of the first changelog line
    };
}

/**
 * Reads a changelog section into blocks: one per version header, each with
 * its entries and its other non-blank lines. Lines before the first header
 * go into a block with no version.
 */
function parseBlocks(lines, offset, readHeader) {
    const blocks = [{ version: null, line: offset, entries: [], other: [] }];
    lines.forEach((line, index) => {
        const lineNumber = offset + index;
        const header = readHeader(line);
        if (header) {
            blocks.push({ ...header, line: lineNumber, raw: line, entries: [], other: [] });
            return;
        }
        if (line.trim() === "") return;
        const block = blocks[blocks.length - 1];
        if (line.startsWith("* ")) block.entries.push({ line: lineNumber, raw: line });
        else block.other.push({ line: lineNumber, raw: line });
    });
    return blocks;
}

function readLegacyHeader(line) {
    const dated = line.match(LEGACY_DATED_HEADER);
    if (dated) return { version: dated[1], dateText: dated[2], dated: true };
    // A file converted half way has entries in the new format already; they stay as they are.
    const converted = line.match(NEW_DATED_HEADER);
    if (converted) return { version: converted[1], date: converted[2], dated: true };
    const undated = line.match(UNDATED_HEADER);
    if (undated) return { version: undated[1], dated: false };
    return undefined;
}

function readNewHeader(line) {
    const dated = line.match(NEW_DATED_HEADER);
    if (dated) return { version: dated[1], date: dated[2], dated: true };
    const undated = line.match(UNDATED_HEADER);
    if (undated) return { version: undated[1], dated: false };
    return undefined;
}

/** What the converted entry has to read, given the original one. */
function expectedEntry(legacyRaw) {
    const labelled = legacyRaw.match(LABELLED_LEGACY_ENTRY);
    return labelled ? `* ${labelled[1]} - ${labelled[2]}` : legacyRaw;
}

/**
 * Compares an original and a converted text. Returns the problems found and
 * how many versions and entries were compared.
 */
function verify(originalText, convertedText) {
    const problems = [];
    const original = splitSections(originalText);
    const converted = splitSections(convertedText);

    const same = (a, b) => a.length === b.length && a.every((line, i) => line === b[i]);
    if (!same(original.before, converted.before)) {
        problems.push("the text before the changelog section differs");
    }
    if (!same(original.after, converted.after)) {
        problems.push("the text after the changelog section differs");
    }

    const legacy = parseBlocks(original.changelog, original.offset, readLegacyHeader);
    const fresh = parseBlocks(converted.changelog, converted.offset, readNewHeader);

    if (legacy.length !== fresh.length) {
        problems.push(
            `the original has ${legacy.length - 1} version header(s), the converted file ${fresh.length - 1}`
        );
    }

    let entries = 0;
    const count = Math.min(legacy.length, fresh.length);
    for (let i = 0; i < count; i++) {
        const a = legacy[i];
        const b = fresh[i];
        const where = a.version
            ? `version ${a.version} (original line ${a.line}, converted line ${b.line})`
            : "the lines before the first version";

        if (a.version !== b.version) {
            problems.push(`${where}: the converted file has version ${b.version ?? "(none)"} there instead`);
            continue;
        }
        if (a.version) {
            if (a.dated) {
                const expected = a.date ?? legacyDateToIso(a.dateText);
                if (!expected) problems.push(`${where}: could not read the original date "${a.dateText}"`);
                else if (!b.dated) problems.push(`${where}: the converted header lost its date`);
                else if (b.date !== expected) problems.push(`${where}: date "${a.dateText}" should read ${expected}, the converted file has ${b.date}`);
            } else if (a.raw !== b.raw) {
                problems.push(`${where}: an undated header should stay as it is; got "${b.raw}"`);
            }
        }

        if (a.entries.length !== b.entries.length) {
            problems.push(`${where}: ${a.entries.length} entries in the original, ${b.entries.length} in the converted file`);
        }
        const n = Math.min(a.entries.length, b.entries.length);
        for (let j = 0; j < n; j++) {
            const expected = expectedEntry(a.entries[j].raw);
            if (b.entries[j].raw !== expected) {
                problems.push(`${where}: entry at converted line ${b.entries[j].line} should read\n    ${expected}\n  but reads\n    ${b.entries[j].raw}`);
            }
        }
        entries += n;

        if (a.other.length !== b.other.length || a.other.some((o, k) => o.raw !== b.other[k].raw)) {
            problems.push(`${where}: the lines that are neither header nor entry differ`);
        }
    }

    return { problems, versions: count - 1, entries };
}

// ---------------------------------------------------------------------------
// Command line
// ---------------------------------------------------------------------------

/** Reads a file, or prints why it can't and returns undefined. */
function readText(file) {
    try {
        return fs.readFileSync(file, "utf8");
    } catch (error) {
        console.error(`${file}: cannot read it (${error.code ?? error.message})`);
        return undefined;
    }
}

/** True when the file has a "== Changelog ==" section; prints why not otherwise. */
function hasChangelogSection(file, text) {
    if (splitSections(text).offset !== 0) return true;
    console.error(`${file}: no "== Changelog ==" section, nothing to convert`);
    return false;
}

function usage() {
    console.error("Usage: convert-changelog-format.js <file>...");
    console.error("       convert-changelog-format.js --check <file>...");
    console.error("       convert-changelog-format.js --verify <original> <converted>");
    return 2;
}

function convertFiles(files) {
    let status = 0;
    for (const file of files) {
        const text = readText(file);
        if (text === undefined) { status = 2; continue; }
        if (!hasChangelogSection(file, text)) { status = 1; continue; }
        const result = convertChangelog(text);
        for (const { line, text: content } of result.leftovers) {
            console.error(`  ${file}:${line}: could not read the date: ${content}`);
        }
        if (result.text === text) {
            console.log(`${file}: nothing to convert`);
            if (result.leftovers.length > 0) status = 1;
            continue;
        }
        const checked = verify(text, result.text);
        if (result.leftovers.length > 0 || checked.problems.length > 0) {
            status = 1;
            console.error(`${file}: not written, the conversion did not verify against the original:`);
            for (const problem of checked.problems) console.error(`  - ${problem}`);
            continue;
        }
        fs.writeFileSync(file, result.text);
        console.log(
            `${file}: ${result.headers} header(s) and ${result.bullets} bullet(s) converted; ` +
            `verified against the original: ${checked.versions} version(s) and ${checked.entries} entries match`
        );
    }
    return status;
}

function checkFiles(files) {
    let status = 0;
    for (const file of files) {
        const text = readText(file);
        if (text === undefined) { status = 2; continue; }
        if (!hasChangelogSection(file, text)) { status = 1; continue; }
        const legacy = legacyLines(text);
        if (legacy.length === 0) {
            console.log(`${file}: no legacy changelog lines`);
            continue;
        }
        status = 1;
        console.error(`${file}: ${legacy.length} legacy changelog line(s) remain:`);
        for (const { line, text } of legacy) console.error(`  ${line}: ${text}`);
    }
    return status;
}

function verifyFiles(originalPath, convertedPath) {
    const original = readText(originalPath);
    const converted = readText(convertedPath);
    if (original === undefined || converted === undefined) return 2;
    const result = verify(original, converted);
    if (result.problems.length === 0) {
        console.log(`${convertedPath}: ${result.versions} version(s) and ${result.entries} entries match ${originalPath}`);
        return 0;
    }
    console.error(`${convertedPath} does not match ${originalPath}:`);
    for (const problem of result.problems) console.error(`  - ${problem}`);
    return 1;
}

function main(argv) {
    if (argv[0] === "--verify") {
        return argv.length === 3 ? verifyFiles(argv[1], argv[2]) : usage();
    }
    const check = argv.includes("--check");
    const files = argv.filter((arg) => arg !== "--check");
    if (files.length === 0 || files.some((f) => f.startsWith("--"))) return usage();
    return check ? checkFiles(files) : convertFiles(files);
}

module.exports = {
    parseLegacyDate, convertHeader, convertBullet, convertChangelog, legacyLines,
    legacyDateToIso, splitSections, verify,
};

if (require.main === module) process.exit(main(process.argv.slice(2)));
