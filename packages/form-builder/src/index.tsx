import {createRoot, render, StrictMode} from '@wordpress/element';
import {BlockSupports, getCategories, registerBlockType, setCategories} from '@wordpress/blocks';

import App from './App';

import sectionBlocks, {sectionBlockNames} from './blocks/section';
import fieldBlocks from './blocks/fields';
import elementBlocks from './blocks/elements';
import {FieldBlock} from '@givewp/form-builder/types';
import {__} from '@wordpress/i18n';

const supportOverrides: BlockSupports = {
    customClassName: false,
    html: false,
};

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
]);

sectionBlocks.map(({name, settings}: FieldBlock) =>
    registerBlockType(name, {
        ...settings,
        supports: {
            ...settings.supports,
            ...supportOverrides,
        },
    })
);

[...fieldBlocks, ...elementBlocks].map(({name, settings}: FieldBlock) =>
    registerBlockType(name, {
        ...settings,
        parent: sectionBlockNames,
        supports: {
            ...settings.supports,
            ...supportOverrides,
        },
    })
);

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
