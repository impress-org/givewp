import * as React from 'react';
import {useEffect, useState} from 'react';
import DesignPreviewLoading from './DesignPreviewLoading';

import Storage from '@givewp/form-builder/common/storage';

import IframeResizer from 'iframe-resizer-react';
import {useFormState} from '../../stores/form-state';

const DesignPreview = () => {
    const {blocks, settings: formSettings} = useFormState();
    const [sourceDocument, setSourceDocument] = useState('');
    const [isLoading, setIsLoading] = useState<boolean>();

    useEffect(() => {
        setIsLoading(true);
        Storage.preview({blocks, formSettings}).then((document) => {
            setSourceDocument(document);
            setIsLoading(false);
        });
    }, [
        JSON.stringify(formSettings),
        JSON.stringify(blocks), // stringify to prevent re-renders caused by object as dep
    ]);

    return isLoading ? (
        <DesignPreviewLoading />
    ) : (
        <IframeResizer
            srcDoc={sourceDocument}
            checkOrigin={false} /** The srcDoc property is not a URL and requires that the origin check be disabled. */
            style={{
                width: '1px',
                minWidth: '100%',
                border: '0',
            }}
        />
    );
};

export default DesignPreview;
