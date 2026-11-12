import {readFileSync} from 'fs';
import {join} from 'path';

const scss = readFileSync(join(__dirname, 'styles.scss'), 'utf8');

// Only the badge block: from `.statusBadge {` to its closing brace (brace matching).
const sliceBlock = (source: string, opener: string) => {
    const start = source.indexOf(opener);
    if (start === -1) {
        return '';
    }
    let depth = 0;
    for (let i = source.indexOf('{', start); i < source.length; i++) {
        depth += source[i] === '{' ? 1 : source[i] === '}' ? -1 : 0;
        if (depth === 0) {
            return source.slice(start, i + 1);
        }
    }
    return '';
};
const badge = sliceBlock(scss, '.statusBadge {');

const TONE_VALUE = /^var\(--givewp-list-table-status-(success|warning|info|error|neutral)-(text|bg)\)$/;

// Every status the list columns can print as `statusBadge--<status>` (PHP StatusColumn classes).
const NEUTRAL_BY_DEFAULT = ['refunded', 'abandoned', 'draft', 'archived', 'inactive', 'upgraded', 'give_subscription'];
const EXPECTED_TONES: Record<string, string[]> = {
    success: ['completed', 'complete', 'active', 'publish', 'published'],
    warning: ['pending'],
    info: ['processing'],
    error: ['failed', 'failing', 'revoked', 'cancelled', 'expired', 'trash', 'trashed'],
};

const toneGroups = () => {
    const groups: Record<string, string[]> = {};
    for (const [, selectors, body] of badge.matchAll(/((?:&--[\w-]+,?\s*)+)\{([^}]*)\}/g)) {
        const tone = body.match(/--color:\s*var\(--givewp-list-table-status-(\w+)-text\)/)?.[1] ?? 'none';
        const names = [...selectors.matchAll(/&--([\w-]+)/g)].map((m) => m[1]);
        groups[tone] = [...(groups[tone] ?? []), ...names];
    }
    return groups;
};

describe('status badge styles', () => {
    it('finds the badge block', () => {
        expect(badge).toContain('.statusBadge');
        expect(badge).not.toContain('.subscriptionStatus');
    });

    it('defines its own tone tokens instead of relying on an ancestor', () => {
        expect(badge).toContain('@include givewp-status-tone-tokens;');
    });

    it('uses only the five tone tokens for status colors', () => {
        const lines = badge.split('\n').filter((line) => /--(?:bg-)?color:/.test(line));
        expect(lines.length).toBeGreaterThan(0);
        for (const line of lines) {
            const value = line.split(/--(?:bg-)?color:/)[1].replace(';', '').trim();
            expect({line: line.trim(), ok: TONE_VALUE.test(value)}).toEqual({line: line.trim(), ok: true});
        }
    });

    it('keeps the badge text at 13px or more', () => {
        const small = [...badge.matchAll(/font-size:\s*([\d.]+)(rem|px)/g)]
            .filter((m) => parseFloat(m[1]) < (m[2] === 'rem' ? 0.8125 : 13))
            .map((m) => m[0]);
        expect(small).toEqual([]);
    });

    it('is square with the stock 2px radius, like the other controls', () => {
        expect(badge).toContain('border-radius: 2px;');
        expect(badge).not.toContain('rounded-full');
    });

    it('keeps the server word as is', () => {
        expect(badge).not.toMatch(/text-transform/);
    });

    it('gives every known status exactly one tone, or the neutral default', () => {
        const groups = toneGroups();
        const all = Object.values(groups).flat();
        for (const [tone, names] of Object.entries(EXPECTED_TONES)) {
            for (const name of names) {
                expect({name, tone: Object.keys(groups).filter((t) => groups[t].includes(name))}).toEqual({
                    name,
                    tone: [tone],
                });
            }
        }
        for (const name of NEUTRAL_BY_DEFAULT) {
            expect({name, styled: all.includes(name)}).toEqual({name, styled: false});
        }
    });

    it('has no modifier outside the known list', () => {
        const known = [...Object.values(EXPECTED_TONES).flat(), ...NEUTRAL_BY_DEFAULT];
        const unknown = [...badge.matchAll(/&--([\w-]+)/g)].map((m) => m[1]).filter((n) => !known.includes(n));
        expect(unknown).toEqual([]);
    });
});
