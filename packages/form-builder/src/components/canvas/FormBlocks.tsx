import {BlockList, BlockTools, ButtonBlockAppender, ObserveTyping, WritingFlow,} from "@wordpress/block-editor";

const FormBlocks = () => {
    return (
        <BlockTools>
            <WritingFlow>
                <ObserveTyping>
                    <BlockList
                        renderAppender={ButtonBlockAppender}
                    />
                </ObserveTyping>
            </WritingFlow>
        </BlockTools>
    );
};

export default FormBlocks;
