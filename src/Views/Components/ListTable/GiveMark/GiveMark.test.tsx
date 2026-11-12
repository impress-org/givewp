import {renderToStaticMarkup} from 'react-dom/server';
import GiveMark from './index';

describe('GiveMark', () => {
    it('is a decorative svg sized by height', () => {
        const markup = renderToStaticMarkup(<GiveMark size={20} />);

        expect(markup).toContain('aria-hidden="true"');
        expect(markup).toContain('class="givewp-give-mark"');
        expect(markup).toContain('height="20"');
    });
});
