import * as React from 'react';
import {useEffect, useState} from 'react';

import Storage from '@givewp/form-builder/common/storage';

import IframeResizer from 'iframe-resizer-react';
import {useFormState} from '../../stores/form-state';
import DesignPreviewLoading from "@givewp/form-builder/components/canvas/DesignPreviewLoading";

const DesignPreview = () => {
    const {blocks, settings: formSettings} = useFormState();
    const [isLoading, setLoading] = useState<boolean>(false);
    const [sourceDocument, setSourceDocument] = useState<string>(null);
    const [previewHTML, setPreviewHTML] = useState<string>(null);

    useEffect(() => {
        setLoading(true);
        Storage.preview({blocks, formSettings}).then((document) => {
            setSourceDocument(document);
        });
    }, [
        JSON.stringify(formSettings),
        JSON.stringify(blocks), // stringify to prevent re-renders caused by object as dep
    ]);

    return (
        <>
            {isLoading && <DesignPreviewLoading />}
            <IframeResizer
                srcDoc={previewHTML}
                checkOrigin={false} /** The srcDoc property is not a URL and requires that the origin check be disabled. */
                style={{
                    width: '1px',
                    minWidth: '100%',
                    border: '0',
                }}
            />

            {/* @note This iFrame is used to load and render the design preview document in the background. */}
            <iframe
                onLoad={(event) => {
                    const target = event.target as HTMLIFrameElement;
                    setPreviewHTML(target.contentWindow.document.documentElement.innerHTML)
                    setLoading(false)
                }}
                srcDoc={sourceDocument}
                style={{display: 'none'}}
            />
        </>
    )
};

export default DesignPreview;
