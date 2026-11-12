import {readFileSync} from 'fs';
import {join} from 'path';

describe('list notice styles', () => {
    const styles = readFileSync(join(__dirname, 'styles.scss'), 'utf8');

    it('pads the notice 6px at the block ends and the inline end, and 12px at the inline start', () => {
        expect(styles).toContain('$givewp-list-table-notice-edge: calc(var(--givewp-spacing-3) / 2);');
        expect(styles).toContain('padding-block: $givewp-list-table-notice-edge;');
        expect(styles).toContain('padding-inline: var(--givewp-spacing-3) $givewp-list-table-notice-edge;');
    });

    it('keeps a 32px dismiss button target', () => {
        expect(styles).toMatch(/button \{\s*min-inline-size: 2rem;\s*min-block-size: 2rem;/);
    });
});
