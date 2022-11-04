import * as React from 'react';
import {useEffect, useState} from 'react';

import Storage from '../../common/storage/index.ts';

import IframeResizer from 'iframe-resizer-react';
import {useFormSettings} from "../../stores/form-settings/index.tsx";

const DesignPreview = () => {
    const {blocks, template} = useFormSettings();
    const [sourceDocument, setSourceDocument] = useState('');

    useEffect(() => {
        Storage.preview(template, blocks).then(setSourceDocument);
    }, [
        template,
        JSON.stringify(blocks) // stringify to prevent re-renders caused by object as dep
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
