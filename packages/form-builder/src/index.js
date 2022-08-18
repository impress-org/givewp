import React from 'react';
import ReactDOM from 'react-dom';
import {registerBlockType} from '@wordpress/blocks';

import './index.scss';

import App from './App';

import sectionBlocks, {sectionBlockNames} from './blocks/section';

import fieldBlocks from './blocks/fields';
import elementBlocks from './blocks/elements';

sectionBlocks.map(({name, settings}) => registerBlockType(name, settings));

[...fieldBlocks, ...elementBlocks].map(({name, settings}) =>
    registerBlockType(name, {...settings, parent: sectionBlockNames})
);

ReactDOM.render(
    <React.StrictMode>
        <App />
    </React.StrictMode>,
    document.getElementById('root')
);
