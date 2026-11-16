import {readFileSync} from 'fs';
import {join} from 'path';

const styles = readFileSync(join(__dirname, 'Notices.module.scss'), 'utf8');

describe('Campaign notice placement', () => {
    it('keeps the details page tips where they were: absolute, at their own spots', () => {
        const tooltip = styles.slice(styles.indexOf('.tooltip {'), styles.indexOf('}', styles.indexOf('.tooltip {')));
        const settings = styles.slice(
            styles.indexOf('.campaignSettings {'),
            styles.indexOf('}', styles.indexOf('.campaignSettings {'))
        );
        const form = styles.slice(
            styles.indexOf('.campaignForm {'),
            styles.indexOf('}', styles.indexOf('.campaignForm {'))
        );

        expect(tooltip).toContain('position: absolute');
        expect(tooltip).toContain('z-index: 11');
        expect(settings).toContain('right: 20px');
        expect(settings).toContain('top: 20px');
        expect(form).toContain('left: 250px');
        expect(form).toContain('top: 20px');
    });

    it('has no placement left for the list screens, which show their tips as notices', () => {
        expect(styles).not.toContain('.campaignList');
        expect(styles).not.toContain('.defaultForm');
        expect(styles).not.toContain('position: fixed');
    });
});
