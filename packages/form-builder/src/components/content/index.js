import React from 'react';
import {
    BlockList,
    BlockTools,
    ObserveTyping,
    WritingFlow,
    ButtonBlockAppender,
} from "@wordpress/block-editor";

const Component = () => {
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

export default Component;
