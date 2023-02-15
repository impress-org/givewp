import {BlockEditorProvider, BlockInspector} from '@wordpress/block-editor';
import {Popover, SlotFillProvider} from '@wordpress/components';

import {Sidebar} from '../components';

import '@wordpress/components/build-style/style.css';
import '@wordpress/block-editor/build-style/style.css';

import '../App.scss';
import {setFormBlocks, useFormState, useFormStateDispatch} from '../stores/form-state';
import NoticesContainer from "@givewp/form-builder/containers/NoticesContainer";
import BlockEditorInterfaceSkeletonContainer
    from "@givewp/form-builder/containers/BlockEditorInterfaceSkeletonContainer";

/**
 * @since 0.1.0
 */
export default function BlockEditorContainer() {
    const {blocks} = useFormState();
    const dispatch = useFormStateDispatch();
    const dispatchFormBlocks = (blocks) => {
        dispatch(setFormBlocks(blocks));
    };

    return (
        <BlockEditorProvider value={blocks} onInput={dispatchFormBlocks} onChange={dispatchFormBlocks}>
            <SlotFillProvider>
                <Sidebar.InspectorFill>
                    <BlockInspector/>
                </Sidebar.InspectorFill>
                <BlockEditorInterfaceSkeletonContainer/>
                <NoticesContainer/>
                <Popover.Slot/>
            </SlotFillProvider>
        </BlockEditorProvider>
    );
}
