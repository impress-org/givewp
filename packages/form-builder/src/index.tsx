import * as React from 'react';
import * as ReactDOM from 'react-dom';
import {BlockConfiguration, registerBlockType} from '@wordpress/blocks';

import App from './App';

import sectionBlocks, {sectionBlockNames} from './blocks/section';
import fieldBlocks from './blocks/fields';
import elementBlocks from './blocks/elements';

sectionBlocks.map(({name, settings}: {name: string; settings: BlockConfiguration}) =>
    registerBlockType(name, settings)
);

[...fieldBlocks, ...elementBlocks].map(({name, settings}: {name: string; settings: BlockConfiguration}) =>
    registerBlockType(name, {...settings, parent: sectionBlockNames})
);

ReactDOM.render(
    <React.StrictMode>
        <App />
    </React.StrictMode>,
    document.getElementById('root')
);
