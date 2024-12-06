import {BlockEditorProvider, BlockInspector} from '@wordpress/block-editor';
import {Popover, SlotFillProvider} from '@wordpress/components';
import {Sidebar} from '../components';
import {setFormBlocks, useFormState, useFormStateDispatch} from '../stores/form-state';
import BlockEditorInterfaceSkeletonContainer
    from '@givewp/form-builder/containers/BlockEditorInterfaceSkeletonContainer';
import Onboarding from '@givewp/form-builder/components/onboarding';
import parseMissingBlocks from '@givewp/form-builder/common/parseMissingBlocks';
import {__} from "@wordpress/i18n";


/**
 * @since 3.6.2 Early return dispatchFormBlocks if there are no unwrapped blocks.
 * @since 3.0.0
 */
export default function BlockEditorContainer() {
    const {blocks} = useFormState();
    const dispatch = useFormStateDispatch();
    const dispatchFormBlocks = (blocks) => {
        const hasUnwrappedBlocks = blocks.some((block) => block.name !== 'givewp/section');

        if (!hasUnwrappedBlocks) {
            return dispatch(setFormBlocks(blocks));
        }

        dispatch(setFormBlocks(blocks.map((block) => {
            return block.name == 'givewp/section'
                ? block
                : {
                    ...block,
                    name: 'givewp/section',
                    attributes: {
                        title: __('Section Title', 'give'),
                        description: __('Section Description', 'give'),
                        innerBlocksTemplate: [[block.name, block.attributes]],
                    }
                }
        })));
    };

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
