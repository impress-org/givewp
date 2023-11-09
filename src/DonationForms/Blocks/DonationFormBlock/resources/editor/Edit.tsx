import {useCallback, useEffect, useState} from '@wordpress/element';
import DonationFormBlockPreview from './components/DonationFormBlockPreview';
import DonationFormSelector from './components/DonationFormSelector';
import useFormOptions from './hooks/useFormOptions';
import DonationFormBlockControls from './components/DonationFormBlockControls';

import ServerSideRender from '@wordpress/server-side-render';
import {isLegacyForm, isTemplateForm} from '../../../../../../blocks/utils';
import Inspector from '../../../../../../blocks/donation-form/edit/inspector';

import './styles/index.scss';

/**
 * @unreleased update to handle v2 forms.
 * @since 3.0.0
 */
export default function Edit(props) {
    const {attributes, isSelected, setAttributes, className, clientId} = props;
    const {id, blockId, displayStyle, continueButtonTitle} = attributes;
    const {formOptions, isResolving, forms} = useFormOptions();
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

    const showOpenFormButton = displayStyle === 'newTab' || displayStyle === 'modal';
    const isv2Form = forms && id && isTemplateForm(forms, id);
    const isv3Form = forms && id && !isTemplateForm(forms, id) && !isLegacyForm(forms, id);
    if (isv2Form) {
        return (
            <div className={!!isSelected ? `${className} isSelected` : className}>
                <Inspector {...{...props}} />
                <ServerSideRender block="give/donation-form" attributes={attributes} />
            </div>
        );
    }

    if (isv3Form) {
        return (
            <>
                <DonationFormBlockControls
                    isResolving={isResolving}
                    formOptions={formOptions}
                    formId={id}
                    formFormat={displayStyle}
                    setAttributes={setAttributes}
                    openFormButton={continueButtonTitle}
                    showOpenFormButton={showOpenFormButton}
                />
                <DonationFormBlockPreview
                    clientId={clientId}
                    formId={id}
                    formFormat={displayStyle}
                    openFormButton={continueButtonTitle}
                />
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
