import * as React from 'react';

import {ShortcutProvider} from '@wordpress/keyboard-shortcuts';
import BlockEditorContainer from './containers/BlockEditorContainer.tsx';
import {FormSettingsProvider} from './stores/form-settings/index.tsx';
import {Storage} from './common/index.ts';

import '@wordpress/components/build-style/style.css';
import '@wordpress/block-editor/build-style/style.css';

import './App.scss';

import defaultBlocks from './blocks.json';

const {blocks: initialBlocks, formSettings: initialFormSettings} = Storage.load();

const initialState = {
    blocks: initialBlocks || defaultBlocks,
    formTitle: 'My Default Donation Form Title',
    enableDonationGoal: false,
    enableAutoClose: false,
    registration: 'none',
    goalFormat: 'amount-raised',
    template: 'classic',
    ...initialFormSettings,
};

if (initialBlocks instanceof Error) {
    alert('Unable to load initial blocks.');
    console.error(initialBlocks);
}

function App() {
    return (
        <FormSettingsProvider initialState={initialState}>
            <ShortcutProvider>
                <BlockEditorContainer />
            </ShortcutProvider>
        </FormSettingsProvider>
    );
}

export default App;
