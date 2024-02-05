import {BaseControl, PanelRow, TextControl} from '@wordpress/components';
import {setFormSettings, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';

import {getWindowData} from '@givewp/form-builder/common';
import {cleanForSlug, safeDecodeURIComponent} from '@wordpress/url';
import {__} from '@wordpress/i18n';

import {useCallback, useEffect, useState} from 'react';

const {
    formPage: {isEnabled, rewriteSlug, baseUrl},
} = getWindowData();

/**
 * @since 3.1.0 pass page slug from parent component to support form title as initial slug.
 * @since 3.0.0
 * @see https://github.com/WordPress/gutenberg/blob/a8c5605f5dd077a601aefce6f58409f54d7d4447/packages/editor/src/components/post-slug/index.js
 */
const PageSlugControl = ({pageSlug}) => {
    const dispatch = useFormStateDispatch();
    const [editedSlug, setEditedSlug] = useState(safeDecodeURIComponent(pageSlug));

    useEffect(() => {
        setEditedSlug(safeDecodeURIComponent(pageSlug));
    }, [pageSlug]);

    const updateSlug = useCallback(() => {
        const cleanEditedSlug = cleanForSlug(editedSlug);
        setEditedSlug(cleanEditedSlug);

        if (cleanEditedSlug !== pageSlug) {
            dispatch(setFormSettings({pageSlug: cleanEditedSlug}));
        }
    }, [pageSlug, editedSlug, dispatch]);

    return (
        !!isEnabled && (
            <PanelRow>
                <BaseControl label={__('URL', 'give')} help={__('The last part of the URL', 'give')}>
                    <div className={'givewp-form-slug'}>
                        <span>{`${baseUrl}/${rewriteSlug}`}</span>
                        <TextControl
                            value={editedSlug}
                            onChange={(value) => setEditedSlug(value)}
                            onBlur={() => updateSlug()}
                            spellCheck={false}
                        />
                    </div>
                </BaseControl>
            </PanelRow>
        )
    );
};

export default PageSlugControl;

export {isEnabled as isFormPageEnabled, PageSlugControl};
