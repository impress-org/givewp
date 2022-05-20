import React, { useContext } from 'react';
import {BlockList, BlockTools, ObserveTyping, WritingFlow, ButtonBlockAppender, RichText } from "@wordpress/block-editor";
import { FormTitleContext } from '../../context/formTitle'

const Component = () => {

    const [formTitle, setFormTitle] = useContext(FormTitleContext)

    return (
        <BlockTools>
            <WritingFlow>
                <RichText
                    tagName="h1"
                    value={ formTitle }
                    onChange={ setFormTitle }
                    style={{ margin: '40px' }}
                />
                <ObserveTyping>
                    <BlockList
                        renderAppender={ButtonBlockAppender}
                    />
                </ObserveTyping>
            </WritingFlow>
        </BlockTools>
    )
}

export default Component
