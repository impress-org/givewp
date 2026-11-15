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

    it('floats at the bottom end corner of the window below 1280px, inside the viewport', () => {
        const styles = readFileSync(join(__dirname, 'Notices.module.scss'), 'utf8');
        const start = styles.indexOf('@media (max-width: 1279px) {');
        const media = styles.slice(start, styles.indexOf('}', start));

        expect(media).toContain('position: fixed');
        expect(media).toContain('inset-block: auto 24px');
        expect(media).toContain('max-inline-size: calc(100vw - 48px)');
        expect(styles.indexOf('.campaignList {')).toBeLessThan(start);
    });
});
