import {useEffect, useState} from 'react';
import {useBlockProps} from '@wordpress/block-editor';
import {BlockEditProps} from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import BlockInspectorControls from './components/BlockInspectorControls';
import BlockPreview from './components/BlockPreview';
import type {BlockPreviewProps} from './components/BlockPreview';
import useFormOptions from "../../shared/hooks/useFormOptions";
import CampaignSelector from "../../shared/components/CampaignSelector";
import EntitySelector from "../../shared/components/EntitySelector/EntitySelector";
import '../../shared/components/EntitySelector/styles/index.scss';

import "./styles.scss";

/**
 * @unreleased
 *
 * @see 'class-give-block-donation-form.php'
 */
type CampaignFormBlockAttributes = {
    campaignId: number;
    id: number;
    prevId: number;
    blockId: string;
    displayStyle: BlockPreviewProps['displayStyle'];
    continueButtonTitle: string;
    showTitle: boolean;
    showGoal: boolean;
    showContent: boolean;
    contentDisplay: string;
}

/**
 * @unreleased
 */
export default function Edit({attributes, isSelected, setAttributes, className, clientId}: BlockEditProps<any>) {
    const {id, blockId, displayStyle, continueButtonTitle = __('Donate now', 'give')} = attributes as CampaignFormBlockAttributes;
    const [showPreview, setShowPreview] = useState<boolean>(!!id);
    const {formOptions, isResolving} = useFormOptions(attributes?.campaignId);

    const handleFormSelect = (id: number) => {
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

    return (
        <div {...useBlockProps()}>
            {id && showPreview ? (
                <>
                    <BlockInspectorControls
                        attributes={attributes}
                        setAttributes={setAttributes}
                        entityOptions={formOptions}
                        isResolving={isResolving}
                        isLegacyTemplate={isLegacyTemplate}
                        isLegacyForm={isLegacyForm}
                    />
                    <BlockPreview
                        clientId={clientId}
                        formId={id}
                        displayStyle={displayStyle}
                        continueButtonTitle={continueButtonTitle}
                        link={link}
                        isLegacyForm={isLegacyForm}
                        attributes={attributes}
                        isSelected={isSelected}
                        className={className}
                    />
                </>
            ) : (
                <CampaignSelector
                    campaignId={attributes.campaignId}
                    handleSelect={(campaignId: number) => setAttributes({campaignId})}
                >
                    <EntitySelector
                        id={'formId'}
                        label={__('Choose a donation form', 'give')}
                        options={formOptions}
                        isLoading={isResolving}
                        emptyMessage={__('No Donation forms were found.', 'give')}
                        loadingMessage={__('Loading Donation Forms...', 'give')}
                        buttonText={__('Confirm', 'give')}
                        onConfirm={handleFormSelect}
                    />
                </CampaignSelector>
            )}
        </div>
    );
}
