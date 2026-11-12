import {readFileSync} from 'fs';
import {join} from 'path';

const scss = readFileSync(join(__dirname, 'styles.scss'), 'utf8');

// Only the badge block: from `.statusBadge {` up to the next sibling block, `.subscriptionStatus`.
const start = scss.indexOf('.statusBadge {');
const end = scss.indexOf('.subscriptionStatus');
const badge = start === -1 || end === -1 ? '' : scss.slice(start, end);

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

    it('uses only the five tone tokens for status colors', () => {
        const lines = badge.split('\n').filter((line) => /--(?:bg-)?color:/.test(line));
        expect(lines.length).toBeGreaterThan(0);
        for (const line of lines) {
            const value = line.split(/--(?:bg-)?color:/)[1].replace(';', '').trim();
            expect({line: line.trim(), ok: TONE_VALUE.test(value)}).toEqual({line: line.trim(), ok: true});
        }
    });

    it('keeps the badge text at 13px or more', () => {
        const small = [...badge.matchAll(/font-size:\s*([\d.]+)rem/g)]
            .filter((m) => parseFloat(m[1]) < 0.8125)
            .map((m) => m[0]);
        expect(small).toEqual([]);
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
