import React, { useState } from 'react';

import { parse } from '@wordpress/blocks'
import { ShortcutProvider } from '@wordpress/keyboard-shortcuts';
import { BlockEditorProvider, BlockInspector} from '@wordpress/block-editor';
import { SlotFillProvider, Popover } from '@wordpress/components';
import { InterfaceSkeleton } from "@wordpress/interface";

import Header from './components/header'
import { Sidebar, SecondarySidebar } from './components/sidebar'
import Content from './components/content'
import { FormSettingsProvider, defaultFormSettings } from './settings/context';

import { useToggleState } from "./hooks";

import '@wordpress/components/build-style/style.css';
import '@wordpress/block-editor/build-style/style.css';

import './App.scss';

import Storage from './components/storage'


function App() {

    const {
        state: showSecondarySidebar,
        toggle: toggleSecondarySidebar
    } = useToggleState( false )

    const {
        state: showSidebar,
        toggle: toggleShowSidebar
    } = useToggleState( true )

    const { blocks: initialBlocks, settings: initialFormSettings } =  Storage.load();
    if (initialBlocks instanceof Error ) {
        alert( 'Unable to load initial blocks.' )
        console.error(initialBlocks);
    }

    const [formSettings, setFormSettings] = useState( {
        ...defaultFormSettings,
        ...initialFormSettings,
    } )

    const [ blocks, updateBlocks ] = useState( initialBlocks || parse(`
        <!-- wp:custom-block-editor/donation-amount /-->
        <!-- wp:custom-block-editor/donor-info /-->
        <!-- wp:custom-block-editor/payment-details /-->
    `));

    const saveCallback = () => {
        return Storage.save( { blocks, formSettings } )
            .catch(error => alert(error.message));
    }

    return (
        <FormSettingsProvider formSettings={formSettings} setFormSettings={setFormSettings}>
            <ShortcutProvider>
                <BlockEditorProvider
                    value={ blocks }
                    onInput={ ( blocks ) => updateBlocks( blocks ) }
                    onChange={ ( blocks ) => updateBlocks( blocks ) }
                >
                    <SlotFillProvider>
                        <Sidebar.InspectorFill>
                            <BlockInspector />
                        </Sidebar.InspectorFill>
                        <InterfaceSkeleton
                            header={ <Header
                                saveCallback={saveCallback}
                                toggleSecondarySidebar={toggleSecondarySidebar}
                                toggleShowSidebar={toggleShowSidebar}
                            /> }
                            content={ <Content /> }
                            sidebar={ !! showSidebar && <Sidebar /> }
                            secondarySidebar={ !! showSecondarySidebar && <SecondarySidebar /> }
                        />
                        <Popover.Slot />
                    </SlotFillProvider>
                </BlockEditorProvider>
            </ShortcutProvider>
        </FormSettingsProvider>
    );
}

export default App;
