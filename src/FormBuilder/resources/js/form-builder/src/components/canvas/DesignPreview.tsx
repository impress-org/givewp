import {useEffect, useState, createRef} from 'react';

import Storage from '@givewp/form-builder/common/storage';

import IframeResizer from 'iframe-resizer-react';
import {useFormState} from '../../stores/form-state';
import DesignPreviewLoading from '@givewp/form-builder/components/canvas/DesignPreviewLoading';

export const iframeRef = createRef();

const DesignPreview = () => {
    const {blocks, settings: formSettings} = useFormState();
    const [isLoading, setLoading] = useState<boolean>(false);
    const [isEditing, setIsEditing] = useState<boolean>(false);
    const [designUpdated, setDesignUpdated] = useState<boolean>(true);
    const [sourceDocument, setSourceDocument] = useState<string>(null);
    const [previewHTML, setPreviewHTML] = useState<string>(null);

    useEffect(() => {
        setLoading(true);
        Storage.preview({blocks, formSettings}).then((document) => {
            setSourceDocument(document);
        });
    }, [
        formSettings.designId,
        formSettings.goalType,
        formSettings.goalStartDate,
        formSettings.goalEndDate,
        formSettings.goalProgressType,
        JSON.stringify(blocks), // stringify to prevent re-renders caused by object as dep
    ]);

    useEffect(() => {
        setDesignUpdated(true);
    }, [formSettings.designId]);

    return (
        <>
            {isLoading && <DesignPreviewLoading design={formSettings.designId} editing={isEditing} designUpdated={designUpdated} />}
            <IframeResizer
                forwardRef={iframeRef}
                srcDoc={previewHTML}
                checkOrigin={
                    false
                } /** The srcDoc property is not a URL and requires that the origin check be disabled. */
                style={{
                    width: '1px',
                    minWidth: '100%',
                    border: '0',
                    display: isLoading && designUpdated ? 'none' : 'inherit',
                    opacity: isLoading ? 0.5 : 1,
                    transition: 'opacity 0.3s ease-in-out',
                }}
                onInit={iframe => {
                    iframe.iFrameResizer.resize();
                    setLoading(false);
                    setIsEditing(true);
                    setDesignUpdated(false);
                }}
            />

            {/* @note This iFrame is used to load and render the design preview document in the background. */}
            <iframe
                onLoad={(event) => {
                    const target = event.target as HTMLIFrameElement;
                    setPreviewHTML(target.contentWindow.document.documentElement.innerHTML);
                }}
                srcDoc={sourceDocument}
                style={{display: 'none'}}
            />
        </>
    );
};

export default DesignPreview;
