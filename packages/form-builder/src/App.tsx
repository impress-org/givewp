import * as React from 'react';

import {ShortcutProvider} from '@wordpress/keyboard-shortcuts';
import BlockEditorContainer from './containers/BlockEditorContainer';
import {FormStateProvider} from './stores/form-state';
import {Storage} from './common';
import type {Block} from '@givewp/form-builder/types';

import './App.scss';

import defaultBlocks from './blocks.json';
import {__} from '@wordpress/i18n';

const {blocks: initialBlocks, formSettings: initialFormSettings} = Storage.load();

const initialState = {
    blocks: initialBlocks || (defaultBlocks as Block[]),
    settings: {
        formTitle: __('My Default Donation Form Title'),
        enableDonationGoal: false,
        enableAutoClose: false,
        registration: 'none',
        goalType: 'amount',
        designId: 'classic',
        primaryColor: '#69b86b',
        secondaryColor: '#f49420',
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
            </ShortcutProvider>
        </FormStateProvider>
    );
}

export default App;
