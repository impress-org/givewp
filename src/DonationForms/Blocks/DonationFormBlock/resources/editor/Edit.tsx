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

    const getDefaultFormId = useCallback(() => {
        if (!isResolving && formOptions.length > 0) {
            return id && formOptions?.find(({value}) => value === id);
        }
    }, [isResolving, id, JSON.stringify(formOptions)]);

    // Todo: combine & return form.template / use pageSlug for new tab linke
    const isLegacyForm = () => {
        if (!isResolving && formOptions.length > 0) {
            const data = forms.find((form) => parseInt(form.id) === parseInt(id));

            return (
                data &&
                data.excerpt.rendered !== '<p>[]</p>\n' &&
                (!data.formTemplate || data.formTemplate === 'legacy')
            );
        }
    };

    const isTemplateForm = (forms, id) => {
        if (forms) {
            const data = forms.find((form) => parseInt(form.id) === parseInt(id));

            return data && data.formTemplate !== '';
        }

        return false;
    };
    console.error(forms);
    const isv3Form = formOptions && id && !isTemplateForm(forms, id) && !isLegacyForm(forms, id);
    const isv2Form = formOptions && id && isTemplateForm(forms, id);

    if (id) {
        return (
            <>
                <DonationFormBlockControls
                    attributes={attributes}
                    setAttributes={setAttributes}
                    formOptions={formOptions}
                    isResolving={isResolving}
                    isLegacyForm={isLegacyForm(forms, id)}
                />

                {isv2Form && (
                    <div className={!!isSelected ? `${className} isSelected` : className}>
                        <ServerSideRender block="give/donation-form" attributes={attributes} />
                    </div>
                )}

                {isv3Form && (
                    <DonationFormBlockPreview
                        clientId={clientId}
                        formId={id}
                        formFormat={displayStyle}
                        openFormButton={continueButtonTitle}
                        pageSlug={formOptions.pageSlug}
                    />
                )}
            </>
        );
    }

    if (!id && !showPreview) {
        return (
            <DonationFormSelector
                id={id}
                getDefaultFormId={getDefaultFormId}
                setShowPreview={setShowPreview}
                setAttributes={setAttributes}
            />
        );
    }

    return false;
}
