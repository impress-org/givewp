import {readFileSync} from 'fs';
import {resolve} from 'path';
import {LIST_TABLE_SCREEN_KEYS} from './types';

/**
 * The keys of `ListTableScreens::permissions()`, read from the PHP source.
 */
function phpScreenKeys(): string[] {
    const source = readFileSync(
        resolve(__dirname, '../../../../Framework/ListTable/Layouts/ListTableScreens.php'),
        'utf8'
    );
    const permissions = source.slice(source.indexOf('function permissions()'));

    return Array.from(permissions.matchAll(/'([a-z][a-z0-9-]*)'\s*=>\s*UserPermissions::/g), (match) => match[1]);
}

describe('ListTableScreenKey', () => {
    it('lists the same eight keys as the PHP screen list', () => {
        const phpKeys = phpScreenKeys();

        expect(phpKeys).toHaveLength(8);
        expect([...LIST_TABLE_SCREEN_KEYS].sort()).toEqual([...phpKeys].sort());
    });

    it('has no duplicate keys', () => {
        expect(new Set(LIST_TABLE_SCREEN_KEYS).size).toBe(LIST_TABLE_SCREEN_KEYS.length);
    });
});
