import {__} from '@wordpress/i18n';
import {InspectorControls, useBlockProps} from '@wordpress/block-editor';
import {BlockEditProps} from '@wordpress/blocks';
import {FormTokenField, PanelBody, SelectControl, TextControl, ToggleControl} from '@wordpress/components';
import {TokenItem} from '@wordpress/components/build-types/form-token-field/types'
import GridControl from '../shared/components/GridControl';
import useCampaigns from '../shared/hooks/useCampaigns';
import {CampaignGridType} from './types';
import CampaignGridApp from './app/index'

export default function Edit({attributes, setAttributes}: BlockEditProps<CampaignGridType>) {
    const blockProps = useBlockProps();
    const {campaigns, hasResolved} = useCampaigns();
    const suggestions = campaigns?.map((campaign) => campaign.title);

    return (
        <div {...blockProps}>
            {hasResolved && (
                <>
                    <CampaignGridApp attributes={attributes} />
                    <InspectorControls>
                        <PanelBody title={__('Layout', 'give')} initialOpen={true}>
                            <GridControl
                                label={__('Grid', 'give')}
                                value={attributes.layout}
                                onChange={(layout) => setAttributes({layout})}
                                options={[
                                    {
                                        value: 'full',
                                        label: __('Full Width', 'give'),
                                    },
                                    {
                                        value: 'double',
                                        label: __('Double', 'give'),
                                    },
                                    {
                                        value: 'triple',
                                        label: __('Triple', 'give'),
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
                                value={attributes.sortBy}
                                help={__('The order campaigns are displayed in.', 'give')}
                                options={[
                                    {
                                        value: 'date',
                                        label: __('Date Created', 'give'),
                                    },
                                    {
                                        value: 'amount',
                                        label: __('Total Amount Raised', 'give'),
                                    },
                                    {
                                        value: 'donations',
                                        label: __('Number of Donations', 'give'),
                                    },
                                    {
                                        value: 'donors',
                                        label: __('Number of Donors', 'give'),
                                    }
                                ]}
                            />
                            <SelectControl
                                label={__('Order', 'give')}
                                onChange={(orderBy: string) => setAttributes({orderBy})}
                                value={attributes.orderBy}
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
                                value={attributes.filterBy?.map((item: TokenItem) => item.title)}
                                label={__('Filter by Campaign', 'give')}
                                onChange={(values) => {
                                    const filterBy = campaigns
                                        .filter((campaign) => values.includes(campaign.title))
                                        .map((campaign) => {
                                            return {
                                                value: String(campaign.id),
                                                title: campaign.title
                                            }
                                        });

                                    setAttributes({filterBy})
                                }}
                                suggestions={suggestions}
                            />
                            <TextControl
                                type="number"
                                min="1"
                                label={__('Campaigns per page', 'give')}
                                value={attributes.perPage}
                                onChange={(perPage: string) => setAttributes({perPage: Number(perPage)})}
                                help={__('Set the number of campaigns to be displayed on the first page load.', 'give')}
                            />
                            <ToggleControl
                                label={__('Show pagination', 'give')}
                                checked={attributes.showPagination}
                                onChange={(showPagination) => setAttributes({showPagination})}
                                help={__('All campaigns will be spread across multiple pages.', 'give')}
                            />
                        </PanelBody>
                    </InspectorControls>
                </>
            )}
        </div>
    )
}
