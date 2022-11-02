import * as React from 'react';
import {useState} from 'react';
import {BlockEditorProvider, BlockInspector} from '@wordpress/block-editor';
import {Popover, SlotFillProvider} from '@wordpress/components';
import {InterfaceSkeleton} from '@wordpress/interface';
import useToggleState from '../hooks/useToggleState';

import HeaderContainer from './HeaderContainer';

import {SecondarySidebar, Sidebar} from '../components';

import '@wordpress/components/build-style/style.css';
import '@wordpress/block-editor/build-style/style.css';

import '../App.scss';
import {setFormBlocks, useFormSettings, useFormSettingsDispatch} from '../stores/form-settings/index.tsx';
import {DesignPreview, FormBlocks} from '../components/canvas';

export default function BlockEditorContainer() {
    const {blocks} = useFormSettings();
    const dispatch = useFormSettingsDispatch();
    const dispatchFormBlocks = (blocks) => dispatch(setFormBlocks(blocks));

    const {state: showSidebar, toggle: toggleShowSidebar} = useToggleState(true);
    const [selectedSecondarySidebar, setSelectedSecondarySidebar] = useState('');
    const [selectedTab, setSelectedTab] = useState('form');

    return (
        <BlockEditorProvider value={blocks} onInput={dispatchFormBlocks} onChange={dispatchFormBlocks}>
            <SlotFillProvider>
                <Sidebar.InspectorFill>
                    <BlockInspector />
                </Sidebar.InspectorFill>
                <InterfaceSkeleton
                    header={
                        <HeaderContainer
                            selectedSecondarySidebar={selectedSecondarySidebar}
                            toggleSelectedSecondarySidebar={(name) =>
                                setSelectedSecondarySidebar(name !== selectedSecondarySidebar ? name : false)
                            }
                            showSidebar={showSidebar}
                            toggleShowSidebar={toggleShowSidebar}
                        />
                    }
                    content={'design' === selectedTab ? <DesignPreview blocks={blocks} /> : <FormBlocks />}
                    sidebar={!!showSidebar && <Sidebar selectedTab={selectedTab} setSelectedTab={setSelectedTab} />}
                    secondarySidebar={
                        !!selectedSecondarySidebar && <SecondarySidebar selected={selectedSecondarySidebar} />
                    }
                />
                <Popover.Slot />
            </SlotFillProvider>
        </BlockEditorProvider>
    );
}
