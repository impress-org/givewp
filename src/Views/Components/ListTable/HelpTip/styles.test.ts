import {readFileSync} from 'fs';
import {join} from 'path';

const source = readFileSync(join(__dirname, 'styles.scss'), 'utf8');

// The stock popover box shrink-wraps its content. These checks keep the width rule in place, since
// jsdom cannot lay out a popover.
describe('HelpTip styles', () => {
    it('gives the stock popover box a set width, scoped under our own class', () => {
        expect(source).toMatch(
            /\.givewp-help-tip\s*\{[^}]*\.components-popover__content\s*\{[^}]*inline-size:\s*min\(20rem,\s*90vw\)/s
        );
    });

    it('lets the text wrap', () => {
        expect(source).toMatch(/&__content\s*\{[^}]*white-space:\s*normal/s);
    });
});
