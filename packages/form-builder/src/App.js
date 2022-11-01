import React, {useState} from 'react';

import {ShortcutProvider} from '@wordpress/keyboard-shortcuts';
import {BlockEditorProvider, BlockInspector} from '@wordpress/block-editor';
import {Popover, SlotFillProvider} from '@wordpress/components';
import {InterfaceSkeleton} from "@wordpress/interface";

import HeaderContainer from './containers/HeaderContainer';
import {SecondarySidebar, Sidebar} from './components';
import {defaultFormSettings, FormSettingsProvider} from './settings/context';
import {Storage} from './common';

import {useToggleState} from "./hooks";

import '@wordpress/components/build-style/style.css';
import '@wordpress/block-editor/build-style/style.css';

import './App.scss';

import defaultBlocks from './blocks.json'
import {FormBlocks, DesignPreview} from "./components/canvas";

function App() {

    const {
        state: showSidebar,
        toggle: toggleShowSidebar,
    } = useToggleState(true);

    const [selectedSecondarySidebar, setSelectedSecondarySidebar] = useState('');
    const toggleSelectedSecondarySidebar = (name) => setSelectedSecondarySidebar(name !== selectedSecondarySidebar ? name : false);

    const {blocks: initialBlocks, settings: initialFormSettings} = Storage.load();
    if (initialBlocks instanceof Error) {
        alert('Unable to load initial blocks.');
        console.error(initialBlocks);
    }

    const [formSettings, setFormSettings] = useState({
        ...defaultFormSettings,
        ...initialFormSettings,
    });

    const [blocks, updateBlocks] = useState(initialBlocks || defaultBlocks);

    const saveCallback = () => {
        return Storage.save({blocks, formSettings})
                      .catch(error => alert(error.message));
    };

    const [selectedTab, setSelectedTab] = useState('form');

    return (
        <FormSettingsProvider formSettings={formSettings} setFormSettings={setFormSettings}>
            <ShortcutProvider>
                <BlockEditorProvider
                    value={blocks}
                    onInput={(blocks) => updateBlocks(blocks)}
                    onChange={(blocks) => updateBlocks(blocks)}
                >
                    <SlotFillProvider>
                        <Sidebar.InspectorFill>
                            <BlockInspector />
                        </Sidebar.InspectorFill>
                        <InterfaceSkeleton
                            header={<HeaderContainer
                                saveCallback={saveCallback}
                                selectedSecondarySidebar={selectedSecondarySidebar}
                                toggleSelectedSecondarySidebar={toggleSelectedSecondarySidebar}
                                showSidebar={showSidebar}
                                toggleShowSidebar={toggleShowSidebar}
                            />}
                            content={'design' === selectedTab ? <DesignPreview blocks={blocks} /> : <FormBlocks />}
                            sidebar={!!showSidebar && <Sidebar selectedTab={selectedTab} setSelectedTab={setSelectedTab} />}
                            secondarySidebar={!!selectedSecondarySidebar &&
                                <SecondarySidebar selected={selectedSecondarySidebar} />}
                        />
                        <Popover.Slot />
                    </SlotFillProvider>
                </BlockEditorProvider>
            </ShortcutProvider>
        </FormSettingsProvider>
    );
}

export default App;
