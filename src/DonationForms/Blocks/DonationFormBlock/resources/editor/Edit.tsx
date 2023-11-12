import {useCallback, useEffect, useState} from '@wordpress/element';
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
export default function Edit(props) {
    const {attributes, isSelected, setAttributes, className, clientId} = props;
    const {id, blockId, displayStyle, continueButtonTitle} = attributes;
    const {formOptions, isResolving} = useFormOptions();
    const [showPreview, setShowPreview] = useState<boolean>(!!id);

    useEffect(() => {
        if (!blockId) {
            setAttributes({blockId: clientId});
        }
    }, []);

    const [
        defaultFormId,
        isLegacyForm,
        isLegacyTemplate
    ] = (useCallback(() => {
        const form = formOptions.find(form => form.value == id)

        return [
            form?.value,
            form?.isLegacyForm,
            form?.isLegacyTemplate
        ]
    }, [id]))();

    return (
        <>
            {!id && !showPreview && (
                <DonationFormSelector
                    id={id}
                    defaultFormId={defaultFormId}
                    setShowPreview={setShowPreview}
                    setAttributes={setAttributes}
                />
            )}

            <DonationFormBlockControls
                attributes={attributes}
                setAttributes={setAttributes}
                formOptions={formOptions}
                isResolving={isResolving}
                isLegacyTemplate={isLegacyTemplate}
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
                />
            )}
        </>
    );
}
