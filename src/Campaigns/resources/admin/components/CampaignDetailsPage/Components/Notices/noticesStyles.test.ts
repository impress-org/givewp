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
        const list = styles.indexOf('.campaignList {');
        const start = styles.indexOf('@media (max-width: 1279px) {', list);
        const media = styles.slice(start, styles.indexOf('}', start));

        expect(media).toContain('position: fixed');
        expect(media).toContain('inset-block: auto 24px');
        expect(media).toContain('max-inline-size: calc(100vw - 48px)');
        expect(list).toBeLessThan(start);
    });

    it('floats the default form balloon at the bottom end corner below 1280px, past the physical top and left', () => {
        const styles = readFileSync(join(__dirname, 'Notices.module.scss'), 'utf8');
        const form = styles.indexOf('.defaultForm {');
        const start = styles.indexOf('@media (max-width: 1279px) {', form);
        const media = styles.slice(start, styles.indexOf('}', start));

        // Still the physical placement above 1279px, and the media block comes inside the same rule.
        expect(styles.slice(form, start)).toContain('top: 420px');
        expect(styles.slice(form, start)).toContain('left: 100px');
        expect(start).toBeLessThan(styles.indexOf('.campaignSettings {'));
        expect(media).toContain('position: fixed');
        expect(media).toContain('inset-block: auto 24px');
        expect(media).toContain('inset-inline: auto 24px');
        expect(media).toContain('max-inline-size: calc(100vw - 48px)');
        expect(media).not.toMatch(/\b(left|right|top):/);
    });
});
