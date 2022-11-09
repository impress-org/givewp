import * as React from 'react';
import {useEffect, useState} from 'react';

import Storage from '@givewp/form-builder/common/storage';

import IframeResizer from 'iframe-resizer-react';
import {useFormState} from '../../stores/form-state';

const DesignPreview = () => {
    const {
        blocks,
        settings: {templateId},
    } = useFormState();
    const [sourceDocument, setSourceDocument] = useState('');

    useEffect(() => {
        Storage.preview(templateId, blocks).then(setSourceDocument);
    }, [
        templateId,
        JSON.stringify(blocks), // stringify to prevent re-renders caused by object as dep
    ]);

    return !sourceDocument ? (
        'Loading...'
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
