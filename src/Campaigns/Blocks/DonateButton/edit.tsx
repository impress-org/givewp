import {__} from '@wordpress/i18n';
import {useSelect} from '@wordpress/data';
import {addQueryArgs} from '@wordpress/url';
import apiFetch from '@wordpress/api-fetch';
import useSWR from 'swr';
import {InspectorControls, useBlockProps} from '@wordpress/block-editor';
import {BlockEditProps} from '@wordpress/blocks';
import {PanelBody, PanelRow, SelectControl, TextControl, ToggleControl} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import useCampaign from '../shared/hooks/useCampaign';
import CampaignSelector from '../shared/components/CampaignSelector';

/**
 * @since 4.0.0
 */
export default function Edit({
    attributes,
    setAttributes,
}: BlockEditProps<{
    campaignId: number;
    buttonText: string;
    useDefaultForm: boolean;
    selectedForm: string;
    width: string;
    justification: string;
}>) {
    const blockProps = useBlockProps();
    const {buttonText = __('Donate', 'give')} = attributes;
    const {campaign, hasResolved} = useCampaign(attributes.campaignId);

    const adminBaseUrl = useSelect(
        // @ts-ignore
        (select) => select('core').getSite()?.url + '/wp-admin/edit.php?post_type=give_forms&page=give-campaigns',
        []
    );

    const campaignForms = (() => {
        const {data, isLoading}: {data: {items: []}; isLoading: boolean} = useSWR(
            addQueryArgs('/give-api/v2/admin/forms', {campaignId: attributes.campaignId, status: 'publish'}),
            (path) => apiFetch({path})
        );

        if (isLoading) {
            return [{label: __('Loading...', 'give'), value: ''}];
        }

        const options = data?.items.map((form: {name: string; id: string}) => ({
            label: form.name,
            value: form.id,
        }));

        return [{label: __('Select form', 'give'), value: ''}, ...options];
    })();

    return (
        <div {...blockProps}>
            <CampaignSelector
                campaignId={attributes.campaignId}
                handleSelect={(campaignId: number) => setAttributes({campaignId})}
            >
                <ServerSideRender block="givewp/campaign-donate-button" attributes={attributes} />
            </CampaignSelector>

            {hasResolved && campaign?.id && (
                <>
                    <InspectorControls group="settings">
                        <PanelBody title="Settings" initialOpen={true}>
                            <TextControl
                                label={__('Donate button', 'give')}
                                value={buttonText}
                                onChange={(buttonText: string) => setAttributes({buttonText})}
                            />
                            <ToggleControl
                                label={__('Use default form', 'give')}
                                checked={attributes.useDefaultForm}
                                onChange={(useDefaultForm: boolean) => setAttributes({useDefaultForm})}
                                help={
                                    <>
                                        {__('Uses the campaign’s default form.', 'give')}
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
                            {!attributes.useDefaultForm && (
                                <SelectControl
                                    label={__('Form', 'give')}
                                    onChange={(selectedForm: string) => setAttributes({selectedForm})}
                                    options={campaignForms}
                                    value={attributes.selectedForm}
                                    help={__('Donations are collected through this form.', 'give')}
                                />
                            )}
                        </PanelBody>
                    </InspectorControls>

                    <InspectorControls group={'styles'} initialOpen={true}>
                        <PanelBody title={__('Width', 'give')} initialOpen={true}>
                            <PanelRow>
                                <div role="group" className="components-button-group" aria-label="Button width">
                                    <button
                                        type="button"
                                        className="components-button is-small"
                                        onClick={() => setAttributes({width: '25%'})}
                                    >
                                        25%
                                    </button>
                                    <button
                                        type="button"
                                        className="components-button is-small"
                                        onClick={() => setAttributes({width: '50%'})}
                                    >
                                        50%
                                    </button>
                                    <button
                                        type="button"
                                        className="components-button is-small"
                                        onClick={() => setAttributes({width: '75%'})}
                                    >
                                        75%
                                    </button>
                                    <button
                                        type="button"
                                        className="components-button is-small"
                                        onClick={() => setAttributes({width: '100%'})}
                                    >
                                        100%
                                    </button>
                                </div>
                            </PanelRow>
                        </PanelBody>
                    </InspectorControls>
                </>
            )}
        </div>
    );
}
