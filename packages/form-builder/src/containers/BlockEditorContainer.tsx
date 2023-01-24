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
import {setFormBlocks, useFormState, useFormStateDispatch} from '../stores/form-state';
import {DesignPreview, FormBlocks} from '../components/canvas';
import NoticesContainer from "@givewp/form-builder/containers/NoticesContainer";
import {useDispatch} from "@wordpress/data";
import {__} from "@wordpress/i18n";

export default function BlockEditorContainer() {
    const {blocks} = useFormState();
    const dispatch = useFormStateDispatch();
    const dispatchFormBlocks = (blocks) => dispatch(setFormBlocks(blocks));
    const {createSuccessNotice} = useDispatch('core/notices');

    const {state: showSidebar, toggle: toggleShowSidebar} = useToggleState(true);
    const [selectedSecondarySidebar, setSelectedSecondarySidebar] = useState('');
    const [selectedTab, setSelectedTab] = useState('form');

    return (
        <BlockEditorProvider value={blocks} onInput={dispatchFormBlocks} onChange={dispatchFormBlocks}>
            <SlotFillProvider>
                <Sidebar.InspectorFill>
                    <BlockInspector/>
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
                            onSaveNotice={() => {
                                createSuccessNotice(
                                    __('Form updated.', 'give'),
                                    {
                                        type: 'snackbar',
                                    }
                                );
                            }
                            }
                        />
                    }
                    content={'design' === selectedTab ? <DesignPreview/> : <FormBlocks/>}
                    sidebar={!!showSidebar && <Sidebar selectedTab={selectedTab} setSelectedTab={setSelectedTab}/>}
                    secondarySidebar={
                        !!selectedSecondarySidebar && <SecondarySidebar selected={selectedSecondarySidebar}/>
                    }
                />
                <NoticesContainer/>
                <Popover.Slot/>
            </SlotFillProvider>
        </BlockEditorProvider>
    );
}
