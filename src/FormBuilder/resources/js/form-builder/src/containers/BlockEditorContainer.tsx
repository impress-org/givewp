import {BlockEditorProvider, BlockInspector} from '@wordpress/block-editor';
import {Popover, SlotFillProvider} from '@wordpress/components';
import {Sidebar} from '../components';
import {setFormBlocks, useFormState, useFormStateDispatch} from '../stores/form-state';
import BlockEditorInterfaceSkeletonContainer
    from '@givewp/form-builder/containers/BlockEditorInterfaceSkeletonContainer';
import Onboarding from '@givewp/form-builder/components/onboarding';
import parseMissingBlocks from '@givewp/form-builder/common/parseMissingBlocks';
import {compose} from "@wordpress/compose";
import enforceTopLevelSections from "@givewp/form-builder/middleware/enforceTopLevelSections";
import updatesForNewFields from "@givewp/form-builder/middleware/updatesForNewFields";
import duplicatedFields from "@givewp/form-builder/middleware/duplicatedFields";


/**
 * @since 3.0.0
 */
export default function BlockEditorContainer() {
    const {blocks} = useFormState();
    const dispatch = useFormStateDispatch();

    const dispatchFormBlocks = (blocks) => dispatch(setFormBlocks(compose(
        // Note: compose runs in reverse order (bottom to top).
        updatesForNewFields,
        duplicatedFields,
        enforceTopLevelSections,
    )(blocks)));

    parseMissingBlocks(blocks);

    return (
        <BlockEditorProvider value={blocks} onInput={dispatchFormBlocks} onChange={dispatchFormBlocks}>
            <Onboarding />
            <SlotFillProvider>
                <Sidebar.InspectorFill>
                    <BlockInspector />
                </Sidebar.InspectorFill>
                <BlockEditorInterfaceSkeletonContainer />
                {/*@ts-ignore*/}
                <Popover.Slot />
            </SlotFillProvider>
        </BlockEditorProvider>
    );
}
