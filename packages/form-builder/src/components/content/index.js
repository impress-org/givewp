import React from 'react';
import {
    BlockList,
    BlockTools,
    ObserveTyping,
    WritingFlow,
    ButtonBlockAppender,
    RichText,
} from "@wordpress/block-editor";
import {useFormSettings} from "../../settings/context";

const Component = () => {

    const [{formTitle}, updateFormSetting] = useFormSettings();

    return (
        <BlockTools>
            <WritingFlow>
                <RichText
                    tagName="h1"
                    value={formTitle}
                    onChange={(formTitle) => updateFormSetting(formTitle)}
                    style={{margin: '40px'}}
                />
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
