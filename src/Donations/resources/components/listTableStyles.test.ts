import {readFileSync} from 'fs';
import {join} from 'path';

describe('Donations list table styles', () => {
    it('lays out the "Enter donations" button as inline-flex, so the gap before the Add-on tag applies', () => {
        const styles = readFileSync(join(__dirname, 'ListTable.module.scss'), 'utf8');
        const start = styles.indexOf('.manualDonationsNotice {');
        const block = styles.slice(start, styles.indexOf('img {', start));

        expect(block).toContain('display: inline-flex');
        expect(block).toContain('gap: .5rem');
    });
});
