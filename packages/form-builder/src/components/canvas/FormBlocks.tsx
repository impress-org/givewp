import * as React from 'react';
import {
    BlockList,
    BlockTools,
    ObserveTyping,
    WritingFlow,
    ButtonBlockAppender,
} from "@wordpress/block-editor";

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
