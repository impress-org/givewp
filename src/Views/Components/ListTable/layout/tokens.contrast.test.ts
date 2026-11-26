import {readFileSync} from 'fs';
import {join} from 'path';
import {contrastRatio, readCustomProperties} from './contrast';

const tokens = readCustomProperties(readFileSync(join(__dirname, '_tokens.scss'), 'utf8'));
const T = (name: string) => tokens[`--givewp-list-table-${name}`];

// The status colors are WordPress admin tokens, so they are not in `_tokens.scss`. The fallback hex values
// are read from `InterweaveSSR/styles.scss`. The selected row is the stock 4% admin color on white.
const statusStyles = readFileSync(join(__dirname, '..', 'InterweaveSSR', 'styles.scss'), 'utf8');
const statusFallback = (token: string) =>
    new RegExp(`var\\(--wpds-color-${token}, (#[0-9a-f]{6})\\)`, 'i').exec(statusStyles)?.[1] as string;
const STATUS_RED = statusFallback('stroke-interactive-error');
const STATUS_AMBER = statusFallback('stroke-surface-warning-strong');
const SELECTED_ROW = '#f7f8fe';

describe('list table tokens', () => {
    it.each([
        ['text on white', T('text'), '#ffffff', 4.5],
        ['text on hover row', T('text'), T('row-hover'), 4.5],
        ['secondary text on white', T('text-secondary'), '#ffffff', 4.5],
        ['secondary text on hover row', T('text-secondary'), T('row-hover'), 4.5],
        ['failed status text on white', STATUS_RED, '#ffffff', 4.5],
        ['failed status text on hover row', STATUS_RED, T('row-hover'), 4.5],
        ['failed status text on selected row', STATUS_RED, SELECTED_ROW, 4.5],
        ['attention status text (amber) on white', STATUS_AMBER, '#ffffff', 4.5],
        ['attention status text (amber) on hover row', STATUS_AMBER, T('row-hover'), 4.5],
        ['attention status text (amber) on selected row', STATUS_AMBER, SELECTED_ROW, 4.5],
        ['control edge on white (UI edge)', T('control-edge'), '#ffffff', 3],
        ['tag text on the neutral status fill', T('tag-text'), T('status-neutral-bg'), 4.5],
        ['test badge text on its fill', T('text'), T('test-badge-bg'), 4.5],
        ['test badge edge on white (UI edge)', T('test-badge-edge'), '#ffffff', 3],
        ['action edge fallback (no color-mix) on white (UI edge)', T('action-edge'), '#ffffff', 3],
        ['action edge fallback (no color-mix) on hover row (UI edge)', T('action-edge'), T('row-hover'), 3],
        ['link fallback (no color-mix) on white', T('link'), '#ffffff', 4.5],
        ['link fallback (no color-mix) on hover row', T('link'), T('row-hover'), 4.5],
        ['link fallback (no color-mix) on the page grey', T('link'), '#f0f0f1', 4.5],
    ])('%s passes', (_label, fg, bg, minimum) => {
        expect(fg).toBeDefined();
        expect(contrastRatio(fg, bg)).toBeGreaterThanOrEqual(minimum);
    });
});

// The action edge (the selected tile bar and the funnel) is the admin theme color mixed with black. It is a
// meaningful graphic, so it needs 3:1 on white and on the hover row in every admin color scheme.
describe('action edge across the admin color schemes', () => {
    const scss = readFileSync(join(__dirname, '_tokens.scss'), 'utf8');
    const share =
        Number(
            /--givewp-list-table-action-edge: color-mix\(in srgb, var\(--wp-admin-theme-color, #[0-9a-f]{6}\) (\d+)%, #000\)/i.exec(
                scss
            )?.[1]
        ) / 100;
    const linkShare =
        Number(
            /--givewp-list-table-link: color-mix\(in srgb, var\(--wp-admin-theme-color, #[0-9a-f]{6}\) (\d+)%, #000\)/i.exec(
                scss
            )?.[1]
        ) / 100;
    const toHex = (rgb: number[]) => `#${rgb.map((value) => Math.round(value).toString(16).padStart(2, '0')).join('')}`;
    const mixWithBlack = (hex: string, amount = share) =>
        toHex([1, 3, 5].map((index) => parseInt(hex.slice(index, index + 2), 16) * amount));

    // WordPress core's scheme colors, and the values newer WordPress versions use for the same schemes.
    const schemes: Record<string, string> = {
        fresh: '#2271b1',
        light: '#0085ba',
        modern: '#3858e9',
        blue: '#e1a948',
        coffee: '#c7a589',
        ectoplasm: '#a3b745',
        midnight: '#e14d43',
        ocean: '#9ebaa0',
        sunrise: '#dd823b',
        'light (newer)': '#007cba',
        'blue (newer)': '#437aa8',
        'coffee (newer)': '#916745',
        'ectoplasm (newer)': '#646c3e',
        'midnight (newer)': '#cf4339',
        'ocean (newer)': '#567958',
        'sunrise (newer)': '#ad631e',
    };

    it('reads the mix share from the tokens file', () => {
        expect(share).toBeGreaterThan(0);
        expect(linkShare).toBeGreaterThan(0);
    });

    it.each(Object.entries(schemes))('%s passes 4.5:1 as link text on white and on the hover row', (_name, color) => {
        const edge = mixWithBlack(color, linkShare);

        expect(contrastRatio(edge, '#ffffff')).toBeGreaterThanOrEqual(4.5);
        expect(contrastRatio(edge, tokens['--givewp-list-table-row-hover'])).toBeGreaterThanOrEqual(4.5);
    });

    it.each(Object.entries(schemes))('%s passes 3:1 on white and on the hover row', (_name, color) => {
        const edge = mixWithBlack(color);

        expect(contrastRatio(edge, '#ffffff')).toBeGreaterThanOrEqual(3);
        expect(contrastRatio(edge, tokens['--givewp-list-table-row-hover'])).toBeGreaterThanOrEqual(3);
    });
});
