/**
 * Convert a WordPress (PHP) date format string into a moment.js format string.
 *
 * WordPress stores the site date format (`get_option('date_format')`) as a PHP
 * format string, but react-dates formats the selected date with moment.js. This
 * maps the common PHP date tokens to their moment equivalents so a picker can
 * render the date in the site's configured format and locale.
 *
 * Tokens not in the map are wrapped as moment bracket literals (`[x]`), which
 * moment renders verbatim, matching how PHP treats unknown format characters as
 * literals. Explicitly escaped characters (`\x`) are also emitted as literals so
 * their text cannot collide with a moment token.
 *
 * @since @unreleased
 */
export function phpToMomentDateFormat(format: string): string {
    const tokens: Record<string, string> = {
        // Day.
        d: 'DD',
        D: 'ddd',
        j: 'D',
        l: 'dddd',
        N: 'E',
        w: 'd',
        z: 'DDDD',
        // Week.
        W: 'WW',
        // Month.
        F: 'MMMM',
        m: 'MM',
        M: 'MMM',
        n: 'M',
        t: '',
        L: '',
        // Year.
        o: 'GGGG',
        Y: 'YYYY',
        y: 'YY',
        // Time.
        a: 'a',
        A: 'A',
        g: 'h',
        G: 'H',
        h: 'hh',
        H: 'HH',
        i: 'mm',
        s: 'ss',
        u: 'SSSSSS',
        // Timezone.
        e: 'z',
        T: 'z',
        Z: 'ZZ',
        // Full date/time (best-effort).
        c: 'YYYY-MM-DDTHH:mm:ssZ',
        r: 'ddd, DD MMM YYYY HH:mm:ss ZZ',
        U: 'X',
    };

    let result = '';
    for (let i = 0; i < format.length; i++) {
        const char = format[i];
        if (char === '\\') {
            // Escaped literal: render the next character verbatim in moment brackets
            // so its text cannot be read as a moment token.
            result += `[${format[i + 1] || ''}]`;
            i++;
            continue;
        }
        // PHP pairs the day-of-month token `j` with the ordinal suffix `S`, e.g. "jS".
        // Moment has a single `Do` token for both, so collapse the pair to avoid two
        // day-of-month tokens rendering as "19th19".
        if (char === 'j' && format[i + 1] === 'S') {
            result += 'Do';
            i++;
            continue;
        }
        if (tokens[char] !== undefined) {
            result += tokens[char];
            continue;
        }
        // Unknown character: emit verbatim, wrapped so moment treats it as a literal.
        result += `[${char}]`;
    }

    return result;
}
