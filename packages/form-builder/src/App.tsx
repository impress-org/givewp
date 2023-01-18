import * as React from 'react';

import {ShortcutProvider} from '@wordpress/keyboard-shortcuts';
import BlockEditorContainer from './containers/BlockEditorContainer';
import {FormStateProvider} from './stores/form-state';
import {Storage} from './common';
import type {Block} from '@givewp/form-builder/types';

import './App.scss';

import defaultBlocks from './blocks.json';
import Feedback from '@givewp/form-builder/feedback';

const {blocks: initialBlocks, formSettings: initialFormSettings} = Storage.load();

const initialState = {
    blocks: initialBlocks || (defaultBlocks as Block[]),
    settings: {
        ...initialFormSettings,
    },
};

if (initialBlocks instanceof Error) {
    alert('Unable to load initial blocks.');
    console.error(initialBlocks);
}

function App() {
    return (
        <FormStateProvider initialState={initialState}>
            <ShortcutProvider>
                <BlockEditorContainer />
                <Feedback />
            </ShortcutProvider>
        </FormStateProvider>
    );
}

export default App;
