import {useEffect, useState} from 'react';
import {useBlockProps} from '@wordpress/block-editor';
import {BlockEditProps} from '@wordpress/blocks';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';
import useFormOptions from './hooks/useFormOptions';
import useCampaignOptions from "./hooks/useCampaignOptions";
import DonationFormBlockControls from './components/DonationFormBlockControls';
import DonationFormBlockPreview from './components/DonationFormBlockPreview';
import type {BlockPreviewProps} from './components/DonationFormBlockPreview';
import EntitySelector from "./components/EntitySelector";
import './styles/index.scss';
import {useSelect} from "@wordpress/data";
import { store as blockEditorStore } from '@wordpress/block-editor';
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
    campaignId: string;
}

/**
 * @since 3.2.1 added isResolving loading state to prevent forms from prematurely being rendered.
 * @since 3.2.0 updated to handle v2 forms.
 * @since 3.0.0
 */
export default function Edit({attributes, isSelected, setAttributes, className, clientId}: BlockEditProps<any>) {
    const {id, blockId, displayStyle, continueButtonTitle} = attributes as DonationFormBlockAttributes;
    const {formOptions, isResolving} = useFormOptions();
    const {campaignOptions, campaignForms, hasResolved} = useCampaignOptions(attributes);
    const [showPreview, setShowPreview] = useState<boolean>(!!id);

    const handleFormSelect = (id) => {
        setAttributes({id});
        setShowPreview(true);
    };

    const handleCampaignSelect = (campaignId) => {
        setAttributes({campaignId});
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
        const form = formOptions?.find((form) => form.value === id);

        return [form?.isLegacyForm, form?.isLegacyTemplate, form?.link];
    })();

    if (isResolving !== false) {
        return <div {...useBlockProps()}>
            <p>{__('Loading...', 'give')}</p>
        </div>
    }

    const isCampaignFormBlock = attributes?.campaignId === null;
    const isCampaignSelected = !!attributes?.campaignId;
    const entityOptions = isCampaignFormBlock ? campaignOptions : isCampaignSelected ? campaignForms : formOptions;
   
    return (
        <div {...useBlockProps()}>
            {id && showPreview ? (
                <>
                    <DonationFormBlockControls
                        attributes={attributes}
                        setAttributes={setAttributes}
                        entityOptions={entityOptions}
                        campaignOptions={campaignOptions}
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
                <EntitySelector
                    id={isCampaignFormBlock ? 'campaignId' : 'formId'}
                    label={isCampaignFormBlock ? __('Choose a campaign to view your forms', 'give') : __('Choose a donation form', 'give')}
                    options={entityOptions}
                    isLoading={!hasResolved || isResolving}
                    emptyMessage={isCampaignFormBlock ? __('No campaigns were found.', 'give') : __('No donation forms were found.', 'give')}
                    loadingMessage={isCampaignFormBlock ? __('Loading Campaigns...', 'give') : __('Loading Donation Forms...', 'give')}
                    buttonText={isCampaignFormBlock ? __('Continue', 'give') : __('Confirm', 'give')}
                    onConfirm={isCampaignFormBlock ? handleCampaignSelect : handleFormSelect}
                />
            )}
        </div>
    );
}
