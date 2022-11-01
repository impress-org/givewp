import * as React from 'react';
import {useEffect, useState} from 'react';
import {Storage} from '../../common';
import IframeResizer from 'iframe-resizer-react';

const DesignPreview = ({blocks}) => {
    const [sourceDocument, setSourceDocument] = useState('');

    useEffect(() => {
        Storage.preview(blocks).then(setSourceDocument);
    }, [JSON.stringify(blocks)]);

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
