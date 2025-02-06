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
    showCompanyName: boolean;
    showAvatar: boolean;
    showButton: boolean;
    donateButtonText: string;
    sortBy: string;
    donorsPerPage: number;
    loadMoreButtonText: string;
}>) {
    const blockProps = useBlockProps();
    const {campaign, hasResolved} = useCampaign(attributes.campaignId);

    const {
        showAnonymous,
        showCompanyName,
        showAvatar,
        showButton,
        donateButtonText,
        sortBy,
        donorsPerPage,
        loadMoreButtonText,
    } = attributes;

    return (
        <div {...blockProps}>
            <CampaignSelector attributes={attributes} setAttributes={setAttributes}>
                <ServerSideRender block="givewp/campaign-donors" attributes={attributes} />
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
                            label={__('Show company name', 'give')}
                            checked={showCompanyName}
                            onChange={(value) => setAttributes({showCompanyName: value})}
                        />
                        <ToggleControl
                            label={__('Show avatar', 'give')}
                            checked={showAvatar}
                            onChange={(value) => setAttributes({showAvatar: value})}
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
                                {label: __('Top Donors', 'give'), value: 'top-donors'},
                                {label: __('Recent Donors', 'give'), value: 'recent-donors'},
                            ]}
                            onChange={(value) => setAttributes({sortBy: value})}
                            help={__('The order donors are displayed in.', 'give')}
                        />
                        {/* TODO: Revert the label and help text back to what are in the designs once the backend for pagination is ready */}
                        <NumberControl
                            label={__('Limit', 'give')}
                            value={donorsPerPage}
                            min={1}
                            max={100}
                            onChange={(value) => setAttributes({donorsPerPage: parseInt(value)})}
                            help={__('The maximum number of donors to display.', 'give')}
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
