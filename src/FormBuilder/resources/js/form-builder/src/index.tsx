import {createRoot, render, StrictMode} from '@wordpress/element';
import {getCategories, setCategories} from '@wordpress/blocks';
import registerBlocks from './common/registerBlocks';
import registerHooks from './supports';
import registerComponents from './components';
import {__} from '@wordpress/i18n';

import App from './App';

setCategories([
    ...getCategories(),
    {
        slug: 'input',
        title: __('Input Fields', 'give'),
    },
    {
        slug: 'content',
        title: __('Content & Media', 'give'),
    },
    {
        // layout seems to be a core category slug
        slug: 'section',
        title: __('Layout', 'give'),
    },
    {
        slug: 'custom',
        title: __('Custom Fields', 'give'),
    },
    {
        slug: 'addons',
        title: __('Add-ons', 'give'),
    },
]);

registerHooks();
registerComponents();
registerBlocks();

const root = document.getElementById('root');

const RenderApp = () => (
    <StrictMode>
        <App />
    </StrictMode>
);

if (createRoot) {
    createRoot(root).render(<RenderApp />);
} else {
    render(<RenderApp />, root);
}
