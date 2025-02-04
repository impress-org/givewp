import {__} from '@wordpress/i18n';
import {InspectorControls, useBlockProps} from '@wordpress/block-editor';
import {BlockEditProps} from '@wordpress/blocks';
import {FormTokenField, PanelBody, SelectControl, ToggleControl} from '@wordpress/components';
import {TokenItem} from "@wordpress/components/build-types/form-token-field/types"
import useCampaigns from "../shared/hooks/useCampaigns";

export default function Edit({attributes, setAttributes}: BlockEditProps<{
    layout: string;
    showImage: boolean;
    showDescription: boolean;
    showGoal: boolean;
    showPagination: boolean;
    sortBy: string;
    orderBy: string;
    filterBy: (string | TokenItem)[];
    perPage: number;
}>) {
    const blockProps = useBlockProps();
    const {campaigns, hasResolved} = useCampaigns();

    return (
        <div {...blockProps}>
            {hasResolved && (
                <InspectorControls>
                    <PanelBody title={__('Layout', 'give')} initialOpen={true}>
                        <SelectControl
                            label={__('Grid', 'give')}
                            onChange={(layout: string) => setAttributes({layout})}
                            options={[
                                {
                                    value: 'full',
                                    label: __('Full', 'give'),
                                }
                            ]}
                        />
                    </PanelBody>

                    <PanelBody title={__('Display Elements', 'give')} initialOpen={true}>
                        <ToggleControl
                            label={__('Show campaign image', 'give')}
                            checked={attributes.showImage}
                            onChange={(showImage) => setAttributes({showImage})}
                        />
                        <ToggleControl
                            label={__('Show description', 'give')}
                            checked={attributes.showDescription}
                            onChange={(showDescription) => setAttributes({showDescription})}
                        />
                        <ToggleControl
                            label={__('Show goal', 'give')}
                            checked={attributes.showGoal}
                            onChange={(showGoal) => setAttributes({showGoal})}
                        />
                    </PanelBody>

                    <PanelBody title={__('Grid Settings', 'give')} initialOpen={true}>
                        <SelectControl
                            label={__('Order By', 'give')}
                            onChange={(sortBy: string) => setAttributes({sortBy})}
                            help={__('The order campaigns are displayed in.', 'give')}
                            options={[
                                {
                                    value: 'date',
                                    label: __('Date Created', 'give'),
                                }
                            ]}
                        />
                        <SelectControl
                            label={__('Order', 'give')}
                            onChange={(orderBy: string) => setAttributes({orderBy})}
                            help={__('Choose whether the campaign order ascends or descends.', 'give')}
                            options={[
                                {
                                    value: 'desc',
                                    label: __('Descending', 'give'),
                                },
                                {
                                    value: 'asc',
                                    label: __('Ascending', 'give'),
                                }
                            ]}
                        />
                        <FormTokenField
                            value={attributes.filterBy}
                            label={__('Filter by Campaign', 'give')}
                            onChange={(filterBy) => setAttributes({filterBy})}
                            suggestions={campaigns?.map((campaign) => ({
                                value: String(campaign.id),
                                title: campaign.title
                            }))}
                        />
                    </PanelBody>
                </InspectorControls>
            )}
        </div>
    )
}
