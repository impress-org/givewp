/**
 * WordPress dependencies
 */
import ServerSideRender from '@wordpress/server-side-render';
/**
 * Internal dependencies
 */
import './block.scss';
import Inspector from './inspector';
import {useCallback, useEffect, useState} from '@wordpress/element';
import useFormOptions from '../../../src/DonationForms/Blocks/DonationFormBlock/resources/editor/hooks/useFormOptions';
import DonationFormSelector
    from '../../../src/DonationForms/Blocks/DonationFormBlock/resources/editor/components/DonationFormSelector';
import {isLegacyForm, isTemplateForm} from '../../utils/index';
import DonationFormBlockControls
    from '../../../src/DonationForms/Blocks/DonationFormBlock/resources/editor/components/DonationFormBlockControls';
import DonationFormBlockPreview
    from '../../../src/DonationForms/Blocks/DonationFormBlock/resources/editor/components/DonationFormBlockPreview';

/**
 * Render Block UI For Editor
 */

const GiveForm = (props) => {
    const {attributes, isSelected, setAttributes, className, clientId} = props;
    const {id, blockId, displayStyle, openFormButton} = attributes;
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

    const showOpenFormButton = displayStyle === 'link' || displayStyle === 'modal';
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
                    id={id}
                    displayStyle={displayStyle}
                    setAttributes={setAttributes}
                    openFormButton={openFormButton}
                    showOpenFormButton={showOpenFormButton}
                />
                <DonationFormBlockPreview
                    clientId={clientId}
                    formId={id}
                    displayStyle={displayStyle}
                    openFormButton={openFormButton}
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
};

export default GiveForm;
