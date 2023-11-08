import {useCallback, useEffect, useState} from '@wordpress/element';
import BlockPreview from './components/BlockPreview';
import DonationFormSelector from './components/DonationFormSelector';
import useFormOptions from './hooks/useFormOptions';
import {useBlockProps} from '@wordpress/block-editor';

import './styles/index.scss';
import DonationFormBlockControls from './components/DonationFormBlockControls';
import LegacyBlockEditor from './components/LegacyBlockEditor';
import {isLegacyForm, isTemplateForm} from '../../../../../../blocks/utils';
import {ReactNode} from 'react';
import {JSX} from 'react/jsx-runtime';

/**
 * @unreleased replace formFormat with displayStyle. Donation selector is now a component.
 * @since 3.0.0
 */
export default function Edit(props) {
    const {attributes, setAttributes, clientId} = props;
    const {id, displayStyle, openFormButton} = attributes;

    const {formOptions, isResolving, forms} = useFormOptions();
    const getDefaultFormId = useCallback(() => {
        if (!isResolving && formOptions.length > 0) {
            return id && formOptions.find(({value}) => value === id);
        }
    }, [isResolving, id, JSON.stringify(formOptions)]);

    useEffect(() => {
        if (!attributes.blockId) {
            setAttributes({blockId: clientId});
        }
    }, [attributes.blockId, clientId]);
    const [showPreview, setShowPreview] = useState(!!id);

    const showOpenFormButton = displayStyle === 'link' || displayStyle === 'modal';
    const isv2Form = isTemplateForm(forms, id);
    const isv3Form = !isTemplateForm(forms, id) && !isLegacyForm(forms, id);

    let BlockEditors: string | number | boolean | JSX.Element | Iterable<ReactNode>;

    if (!id) {
        BlockEditors = (
            <DonationFormSelector
                id={id}
                getDefaultFormId={getDefaultFormId}
                setShowPreview={setShowPreview}
                setAttributes={setAttributes}
            />
        );
    } else if (isv2Form) {
        BlockEditors = <LegacyBlockEditor props={props} attributes={attributes} />;
    } else if (isv3Form) {
        BlockEditors = (
            <>
                <DonationFormBlockControls
                    isResolving={isResolving}
                    formOptions={formOptions}
                    formId={id}
                    displayStyle={displayStyle}
                    setAttributes={setAttributes}
                    openFormButton={openFormButton}
                    showOpenFormButton={showOpenFormButton}
                />
                <BlockPreview
                    clientId={clientId}
                    formId={id}
                    displayStyle={displayStyle}
                    openFormButton={openFormButton}
                />
            </>
        );
    }

    return <div {...useBlockProps()}>{BlockEditors}</div>;
}
