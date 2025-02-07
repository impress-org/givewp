import {InspectorControls, useBlockProps} from '@wordpress/block-editor';
import {BlockEditProps} from '@wordpress/blocks';
import {
    __experimentalNumberControl as NumberControl,
    PanelBody,
    SelectControl,
    TextControl,
    ToggleControl,
} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';
import {CampaignSelector} from '../shared/components/CampaignSelector';
import useCampaign from '../shared/hooks/useCampaign';

export default function Edit({
    attributes,
    setAttributes,
}: BlockEditProps<{
    campaignId: number;
    showAnonymous: boolean;
    showIcon: boolean;
    showButton: boolean;
    donateButtonText: string;
    sortBy: string;
    donationsPerPage: number;
    loadMoreButtonText: string;
}>) {
    const blockProps = useBlockProps();
    const {campaign, hasResolved} = useCampaign(attributes.campaignId);

    const {showAnonymous, showIcon, showButton, donateButtonText, sortBy, donationsPerPage, loadMoreButtonText} =
        attributes;

    return (
        <div {...blockProps}>
            <CampaignSelector attributes={attributes} setAttributes={setAttributes}>
                <ServerSideRender block="givewp/campaign-donations" attributes={attributes} />
            </CampaignSelector>

            {hasResolved && campaign?.id && (
                <InspectorControls>
                    <PanelBody title={__('Display Elements', 'give')} initialOpen={true}>
                        <ToggleControl
                            label={__('Show anonymous', 'give')}
                            checked={showAnonymous}
                            onChange={(value) => setAttributes({showAnonymous: value})}
                        />
                        <ToggleControl
                            label={__('Show icon', 'give')}
                            checked={showIcon}
                            onChange={(value) => setAttributes({showIcon: value})}
                        />
                        <ToggleControl
                            label={__('Show button', 'give')}
                            checked={showButton}
                            onChange={(value) => setAttributes({showButton: value})}
                        />
                        <TextControl
                            label={__('Donate Button', 'give')}
                            value={donateButtonText}
                            onChange={(value) => setAttributes({donateButtonText: value})}
                            help={__('This shows on the header', 'give')}
                        />
                    </PanelBody>

                    <PanelBody title={__('Settings', 'give')} initialOpen={true}>
                        <SelectControl
                            label={__('Sort by', 'give')}
                            value={sortBy}
                            options={[
                                {label: __('Top donations', 'give'), value: 'top-donations'},
                                {label: __('Recent donations', 'give'), value: 'recent-donations'},
                            ]}
                            onChange={(value) => setAttributes({sortBy: value})}
                            help={__('The order donations are displayed in.', 'give')}
                        />
                        {/* TODO: Revert the label and help text back to what are in the designs once the backend for pagination is ready */}
                        <NumberControl
                            label={__('Limit', 'give')}
                            value={donationsPerPage}
                            min={1}
                            max={100}
                            onChange={(value) => setAttributes({donationsPerPage: parseInt(value)})}
                            help={__('The maximum number of donations to display.', 'give')}
                        />
                        {/* TODO: Revert the field back once the backend for pagination is ready
                        <TextControl
                            label={__('Load More Button', 'give')}
                            value={loadMoreButtonText}
                            onChange={(value) => setAttributes({loadMoreButtonText: value})}
                        />
                        */}
                    </PanelBody>
                </InspectorControls>
            )}
        </div>
    );
}
