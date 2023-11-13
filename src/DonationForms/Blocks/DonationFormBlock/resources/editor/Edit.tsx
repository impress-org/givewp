import {useEffect, useState} from 'react';
import {useBlockProps} from '@wordpress/block-editor';
import {BlockEditProps} from '@wordpress/blocks';
import ServerSideRender from '@wordpress/server-side-render';
import DonationFormSelector from './components/DonationFormSelector';
import useFormOptions from './hooks/useFormOptions';
import DonationFormBlockControls from './components/DonationFormBlockControls';
import DonationFormBlockPreview from './components/DonationFormBlockPreview';

import './styles/index.scss';

/**
 * @unreleased update to handle v2 forms.
 * @since 3.0.0
 */
export default function Edit({attributes, isSelected, setAttributes, className, clientId}: BlockEditProps<any>) {
    const {id, blockId, displayStyle, continueButtonTitle} = attributes;
    const {formOptions, isResolving} = useFormOptions();
    const [showPreview, setShowPreview] = useState<boolean>(!!id);

    const handleSelect = (id) => {
        setShowPreview(true);
        setAttributes({id});
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
        const form = formOptions.find((form) => form.value == id);

        return [form?.isLegacyForm, form?.isLegacyTemplate, form?.link];
    })();

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
