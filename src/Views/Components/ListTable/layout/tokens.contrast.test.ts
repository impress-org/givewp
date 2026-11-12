import {readFileSync} from 'fs';
import {join} from 'path';
import {contrastRatio, readCustomProperties} from './contrast';

const tokens = readCustomProperties(readFileSync(join(__dirname, '_tokens.scss'), 'utf8'));
const T = (name: string) => tokens[`--givewp-list-table-${name}`];

describe('list table tokens', () => {
    it.each([
        ['text on white', T('text'), '#ffffff', 4.5],
        ['text on hover row', T('text'), T('row-hover'), 4.5],
        ['secondary text on white', T('text-secondary'), '#ffffff', 4.5],
        ['secondary text on hover row', T('text-secondary'), T('row-hover'), 4.5],
        ['control edge on white (UI edge)', T('control-edge'), '#ffffff', 3],
    ])('%s passes', (_label, fg, bg, minimum) => {
        expect(fg).toBeDefined();
        expect(contrastRatio(fg, bg)).toBeGreaterThanOrEqual(minimum);
    });

    it.each(['success', 'warning', 'info', 'error', 'neutral'])('status %s text passes 4.5:1 on its fill', (tone) => {
        expect(contrastRatio(T(`status-${tone}-text`), T(`status-${tone}-bg`))).toBeGreaterThanOrEqual(4.5);
    });
});
