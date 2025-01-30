import {__} from '@wordpress/i18n';
import {useSelect} from '@wordpress/data';
import {InspectorControls, useBlockProps} from '@wordpress/block-editor';
import {BlockEditProps} from '@wordpress/blocks';
import {PanelBody, SelectControl} from '@wordpress/components';
import {ExternalLink} from '@wordpress/components';
import useCampaign from '../shared/hooks/useCampaign';
import {CampaignSelector} from '../shared/components/CampaignSelector';

import './styles.scss';

const goalOptions = () => {
    const options = [
        {value: 'amount', label: __('Amount raised', 'give')},
        {value: 'donations', label: __('Number of donations', 'give')},
        {value: 'donors', label: __('Number of donors', 'give')},
    ]

    if (!window.GiveCampaignOptions.isRecurringEnabled) {
        return [
            ...options,
            {value: 'amountFromSubscriptions', label: __('Recurring amount raised', 'give')},
            {value: 'subscriptions', label: __('Number of recurring donations', 'give')},
            {value: 'donorsFromSubscriptions', label: __('Number of recurring donors', 'give')},
        ]
    }

    return options;
}

const currency = new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: window.GiveCampaignOptions.currency,
})

/**
 * @unreleased
 */
export default function Edit({attributes, setAttributes}: BlockEditProps<{
    campaignId: number;
    goalType: string;
}>) {
    const {campaign, hasResolved} = useCampaign(attributes.campaignId);


    const blockProps = useBlockProps();

    const adminBaseUrl = useSelect(
        // @ts-ignore
        (select) => select('core').getSite()?.url + '/wp-admin/edit.php?post_type=give_forms&page=give-campaigns',
        []
    );

    if (!hasResolved) {
        return null;
    }

    console.log(campaign)

    return (
        <div {...blockProps}>
            <CampaignSelector attributes={attributes} setAttributes={setAttributes}>
                <div className="give-campaign-goal">
                    <div className="give-campaign-goal__container">
                        <div className="give-campaign-goal__container-item">
                            <span>{__('Amount raised', 'give')}</span>
                            <strong>
                                {currency.format(campaign.goalProgress)}
                            </strong>
                        </div>
                        <div className="give-campaign-goal__container-item">
                            <span>{__('Our goal', 'give')}</span>
                            <strong>{currency.format(campaign.goal || 0)}</strong>
                        </div>
                    </div>
                    <div className="give-campaign-goal__progress-bar">
                        <div className="give-campaign-goal__progress-bar-container">
                            <div className="give-campaign-goal__progress-bar-progress"
                                 style={{width: campaign.goalProgress > 100 ? '100%' : `${campaign.goalProgress}%`}}>
                            </div>
                        </div>
                    </div>
                </div>
            </CampaignSelector>

            {hasResolved && campaign?.id && (
                <InspectorControls>
                    <PanelBody title={__('Settings', 'give')} initialOpen={true}>
                        <SelectControl
                            label={__('Goal type', 'give')}
                            onChange={(goalType: string) => setAttributes({goalType})}
                            options={goalOptions()}
                            value={attributes.goalType}
                            help={
                                <ExternalLink
                                    href={`${adminBaseUrl}&id=${attributes.campaignId}&tab=settings#campaign-goal`}
                                    title={__('Edit campaign goal', 'give')}
                                >
                                    {__('Edit campaign goal', 'give')}
                                </ExternalLink>
                            }
                        />
                    </PanelBody>
                </InspectorControls>
            )}
        </div>
    );
}
