import {__} from '@wordpress/i18n';
import {useSelect} from '@wordpress/data';
import {InspectorControls, useBlockProps} from '@wordpress/block-editor';
import {BlockEditProps} from '@wordpress/blocks';
import {PanelBody, SelectControl, TextControl, ToggleControl} from '@wordpress/components';
import useCampaign from '../shared/hooks/useCampaign';
import {CampaignSelector} from '../shared/components/CampaignSelector';

export default function Edit({attributes, setAttributes}: BlockEditProps<{
    campaignId: number;
    buttonText: string;
    useDefaultForm: boolean;
    selectedForm: string;
}>) {
    const blockProps = useBlockProps();
    const {campaign, hasResolved} = useCampaign(attributes.campaignId);

    const adminBaseUrl = useSelect(
        // @ts-ignore
        (select) => select('core').getSite()?.url + '/wp-admin/edit.php?post_type=give_forms&page=give-campaigns',
        []
    );

    const campaignForms = (() => {
        const {forms, isLoading} = campaign.forms();

        if (isLoading) {
            return []
        }

        return forms.map((form: { name: string, id: string }) => ({
            label: form.name,
            value: form.id
        }))
    })();

    return (
        <div {...blockProps}>

            <CampaignSelector attributes={attributes} setAttributes={setAttributes}>
                <div>
                    {attributes.buttonText}
                </div>
            </CampaignSelector>

            {hasResolved && campaign?.id && (
                <InspectorControls>
                    <PanelBody title="Settings" initialOpen={true}>
                        <TextControl
                            label={__('Donate button', 'give')}
                            value={attributes.buttonText}
                            onChange={(buttonText: string) => setAttributes({buttonText})}
                        />
                        <ToggleControl
                            label={__('Use default form', 'give')}
                            checked={attributes.useDefaultForm}
                            onChange={(useDefaultForm: boolean) => setAttributes({useDefaultForm})}
                            help={
                                <>
                                    {__('Uses the campaign’s default form', 'give')}.
                                    {` `}
                                    <a
                                        href={`${adminBaseUrl}&id=${attributes.campaignId}&tab=forms`}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        aria-label={__('Change campaign default form', 'give')}
                                    >
                                        {__('Change default form', 'give')}
                                    </a>
                                </>
                            }
                        />
                        <SelectControl
                            label={__('Form', 'give')}
                            onChange={(selectedForm: string) => setAttributes({selectedForm})}
                            options={campaignForms}
                            value={attributes.selectedForm}
                        />
                    </PanelBody>
                </InspectorControls>
            )}
        </div>
    );
}
