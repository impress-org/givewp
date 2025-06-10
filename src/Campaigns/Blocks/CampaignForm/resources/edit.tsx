import {useEffect} from 'react';
import {useBlockProps} from '@wordpress/block-editor';
import {BlockEditProps} from '@wordpress/blocks';
import {__} from '@wordpress/i18n';
import BlockInspectorControls from './components/BlockInspectorControls';
import type {BlockPreviewProps} from './components/BlockPreview';
import BlockPreview from './components/BlockPreview';
import useFormOptions from '../../shared/hooks/useFormOptions';
import CampaignSelector from '../../shared/components/CampaignSelector';
import '../../shared/components/EntitySelector/styles/index.scss';

import './styles.scss';

/**
 * @since 4.3.0
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
    useDefaultForm: boolean;
};

/**
 * @since 4.3.0
 */
export default function Edit({attributes, isSelected, setAttributes, className, clientId}: BlockEditProps<any>) {
    const {
        id,
        blockId,
        displayStyle,
        continueButtonTitle = __('Donate now', 'give'),
    } = attributes as CampaignFormBlockAttributes;
    const {formOptions, isResolving} = useFormOptions(attributes?.campaignId);

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
            <CampaignSelector
                campaignId={attributes.campaignId}
                handleSelect={(campaignId: number) => setAttributes({campaignId: Number(campaignId)})}
            >
                {id && (
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
                )}
                <BlockInspectorControls
                    attributes={attributes}
                    setAttributes={setAttributes}
                    entityOptions={formOptions}
                    isResolving={isResolving}
                    isLegacyTemplate={isLegacyTemplate}
                    isLegacyForm={isLegacyForm}
                />
            </CampaignSelector>
        </div>
    );
}
