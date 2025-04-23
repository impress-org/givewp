import {useEffect, useState} from 'react';
import {useBlockProps} from '@wordpress/block-editor';
import {BlockEditProps} from '@wordpress/blocks';
import ServerSideRender from '@wordpress/server-side-render';
import DonationFormSelector from './components/DonationFormSelector';
import useFormOptions from './hooks/useFormOptions';
import DonationFormBlockControls from './components/DonationFormBlockControls';
import DonationFormBlockPreview from './components/DonationFormBlockPreview';
import type {BlockPreviewProps} from './components/DonationFormBlockPreview';

import './styles/index.scss';
import { __ } from '@wordpress/i18n';

/**
 * @since 3.2.1
 *
 * @see 'class-give-block-donation-form.php'
 */
type DonationFormBlockAttributes = {
    id: number;
    prevId: number;
    blockId: string;
    displayStyle: BlockPreviewProps['formFormat'];
    continueButtonTitle: string;
    showTitle: boolean;
    showGoal: boolean;
    showContent: boolean;
    contentDisplay: string;
}

/**
 * @since 3.2.1 added isResolving loading state to prevent forms from prematurely being rendered.
 * @since 3.2.0 updated to handle v2 forms.
 * @since 3.0.0
 */
export default function Edit({attributes, isSelected, setAttributes, className, clientId}: BlockEditProps<any>) {
    const {id, blockId, displayStyle, continueButtonTitle} = attributes as DonationFormBlockAttributes;
    const {formOptions, isResolving} = useFormOptions();
    const [showPreview, setShowPreview] = useState<boolean>(!!id);

    const handleSelect = (id) => {
        setAttributes({id});
        setShowPreview(true);
    };

    useEffect(() => {
        if (!blockId) {
            setAttributes({blockId: clientId});
        }

        if (!isLegacyForm && displayStyle === 'reveal') {
            setAttributes({displayStyle: 'modal'});
        }
    }, []);

    const [isLegacyForm, isLegacyTemplate, link] = (() => {
        const form = formOptions.find((form) => form.value === id);

        return [form?.isLegacyForm, form?.isLegacyTemplate, form?.link];
    })();

    if (isResolving !== false) {
        return <div {...useBlockProps()}>
            <p>{__('Loading...', 'give')}</p>
        </div>
    }

    return (
        <div {...useBlockProps()}>
            {id && showPreview ? (
                <>
                    <DonationFormBlockControls
                        attributes={attributes}
                        setAttributes={setAttributes}
                        formOptions={formOptions}
                        isResolving={isResolving}
                        isLegacyTemplate={isLegacyTemplate}
                        isLegacyForm={isLegacyForm}
                    />

                    {isLegacyForm ? (
                        <div className={!!isSelected ? `${className} isSelected` : className}>
                            <ServerSideRender block="give/donation-form" attributes={attributes} />
                        </div>
                    ) : (
                        <DonationFormBlockPreview
                            clientId={clientId}
                            formId={id}
                            formFormat={displayStyle}
                            openFormButton={continueButtonTitle}
                            link={link}
                        />
                    )}
                </>
            ) : (
                <DonationFormSelector formOptions={formOptions} isResolving={isResolving} handleSelect={handleSelect} />
            )}
        </div>
    );
}
