import React from 'react';
import {createRoot} from 'react-dom/client';
import {BlockConfiguration, registerBlockType} from '@wordpress/blocks';

import App from './App';

import sectionBlocks, {sectionBlockNames} from './blocks/section';
import fieldBlocks from './blocks/fields';
import elementBlocks from './blocks/elements';

sectionBlocks.map(({name, settings}: { name: string; settings: BlockConfiguration }) =>
    registerBlockType(name, settings)
);

[...fieldBlocks, ...elementBlocks].map(({name, settings}: { name: string; settings: BlockConfiguration }) =>
    registerBlockType(name, {...settings, parent: sectionBlockNames})
);

const container = document.getElementById('root');
const root = createRoot(container!);

root.render(
    <React.StrictMode>
        <App/>
    </React.StrictMode>
);
