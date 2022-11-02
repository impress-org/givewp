import * as React from 'react';
import {useEffect, useState} from 'react';
import type {Block} from '../../types/block';

import Storage from '../../common/storage/index.ts';

import IframeResizer from 'iframe-resizer-react';

type PropTypes = {
    blocks: Block[];
};

const DesignPreview = ({blocks}: PropTypes) => {
    const [sourceDocument, setSourceDocument] = useState('');

    useEffect(() => {
        Storage.preview(blocks).then(setSourceDocument);
        // stringify to prevent re-renders caused by object as dep
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
