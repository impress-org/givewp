/**
 * Custom @stellarwp/changelogger writing strategy that reproduces GiveWP's
 * legacy WordPress changelog format:
 *
 *   = 4.16.0: June 2nd, 2026 =
 *   * Feature: Added a sample donation widget
 *   * Fix: Resolved a rounding issue in donation totals
 *
 * Referenced from package.json -> changelogger.files[].strategy as a path.
 * The changelogger loads any strategy whose name ends in .js/.ts (resolved from
 * the current working directory) and validates that it exports formatChanges,
 * formatVersionHeader, versionHeaderMatcher and changelogHeaderMatcher.
 */

const MONTHS = [
    "January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December",
];

// feature -> Feature, fix -> Fix, ... matches the labels configured in
// package.json (each label is just the capitalized type key).
function typeLabel(type) {
    return type.charAt(0).toUpperCase() + type.slice(1);
}

function ordinalSuffix(day) {
    const mod100 = day % 100;
    if (mod100 >= 11 && mod100 <= 13) {
        return "th";
    }
    switch (day % 10) {
        case 1: return "st";
        case 2: return "nd";
        case 3: return "rd";
        default: return "th";
    }
}

// "2026-06-02" -> "June 2nd, 2026". Falls back to the raw value if it is not a
// plain ISO date (changelogger normalizes --date to YYYY-MM-DD).
function formatLegacyDate(date) {
    const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(String(date));
    if (!match) {
        return String(date);
    }
    const [, year, month, day] = match;
    const dayNum = parseInt(day, 10);
    return `${MONTHS[parseInt(month, 10) - 1]} ${dayNum}${ordinalSuffix(dayNum)}, ${year}`;
}

function escapeRegExp(value) {
    return value.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
}

module.exports = {
    // Group entries by type and render "* <Label>: <entry>" lines.
    formatChanges(version, changes) {
        const grouped = changes.reduce((acc, change) => {
            (acc[change.type] = acc[change.type] || []).push(change.entry);
            return acc;
        }, {});

        return Object.entries(grouped)
            .map(([type, entries]) =>
                entries.map((entry) => `* ${typeLabel(type)}: ${entry}`).join("\n")
            )
            .filter((section) => section.length > 0)
            .join("\n");
    },

    // Leading/trailing newlines are trimmed by the writer; a single trailing
    // newline keeps the bullets flush under the header (no blank line between).
    formatVersionHeader(version, date) {
        return `\n= ${version}: ${formatLegacyDate(date)} =\n`;
    },

    formatVersionLink() {
        return "";
    },

    // Detect an existing block for this version (so re-runs replace it).
    versionHeaderMatcher(content, version) {
        const re = new RegExp(`^(= ${escapeRegExp(version)}: [^=]+ =)$`, "m");
        const match = content.match(re);
        return match ? match[1].trim() : undefined;
    },

    // Insert before the first existing version block, or right after the
    // "== Changelog ==" heading when there are none yet.
    changelogHeaderMatcher(content) {
        const firstVersion = content.match(/^= [^:]+: [^=]+ =$/m);
        if (!firstVersion) {
            const mainHeader = content.match(/^== Changelog ==$/m);
            return mainHeader ? mainHeader.index + mainHeader[0].length + 1 : 0;
        }
        return firstVersion.index;
    },
};
