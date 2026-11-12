import {renderToStaticMarkup} from 'react-dom/server';
import TestModeBadge from './index';

describe('TestModeBadge', () => {
    it('shows the label with a decorative icon', () => {
        const markup = renderToStaticMarkup(<TestModeBadge label="Test mode" />);

        expect(markup).toContain('Test mode');
        expect(markup).toContain('<svg');
        expect(markup).toContain('aria-hidden="true"');
    });
});
