import {readFileSync} from 'fs';
import {join} from 'path';

describe('Campaign List balloon position', () => {
    it('hangs from the bottom edge of the page header, at the inline end', () => {
        const styles = readFileSync(join(__dirname, 'Notices.module.scss'), 'utf8');
        const start = styles.indexOf('.campaignList {');
        const block = styles.slice(start, styles.indexOf('}', start));

        expect(block).toContain('inset-block-start: 100%');
        expect(block).toContain('inset-inline-end: 24px');
        expect(block).not.toMatch(/\b(left|right|top):/);
    });
});
