import {BlockList, BlockTools, DefaultBlockAppender, ObserveTyping, WritingFlow} from '@wordpress/block-editor';

const FormBlocks = () => {
    return (
        <BlockTools>
            <WritingFlow>
                <ObserveTyping>
                    <BlockList renderAppender={DefaultBlockAppender} />
                </ObserveTyping>
            </WritingFlow>
        </BlockTools>
    );
};

export default FormBlocks;
