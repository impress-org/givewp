import React from 'react';
import ReactDOM from 'react-dom';
import {registerBlockType} from "@wordpress/blocks";

import './index.scss';

import App from './App';

import './blocks/donation-amount-levels/index';

import sectionBlocks, {sectionBlockNames} from './blocks/section';

import fieldBlocks from './blocks/fields';

sectionBlocks.map(({name, settings}) => registerBlockType(name, settings));

fieldBlocks.map(({name, settings}) => registerBlockType(name, {
    ...settings,
    parent: sectionBlockNames,
}));

ReactDOM.render(
    <React.StrictMode>
        <App />
    </React.StrictMode>,
    document.getElementById('root'),
);
