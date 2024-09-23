import {__} from '@wordpress/i18n';
import {useState, useEffect} from '@wordpress/element';
import {useEntityRecord} from '@wordpress/core-data';
import {ajvResolver} from '@hookform/resolvers/ajv';
import cx from 'classnames';
import {Campaign, GiveCampaignDetails} from './types';
import {FormProvider, SubmitHandler, useForm} from 'react-hook-form';
import {Spinner} from '@givewp/components';
import Tabs from './Tabs';
import campaignSchema from './campaignSchema';

import styles from './style.module.scss';

declare const window: {
    GiveCampaignDetails: GiveCampaignDetails;
} & Window;

export default function CampaignsDetailsPage({campaignId}) {
    const {record: campaign, hasResolved}: {
        record: Campaign,
        hasResolved: boolean,
        save: Function
    } = useEntityRecord('givewp', 'campaign', campaignId);

    const methods = useForm<Campaign>({
        defaultValues: {
            ...campaign
        },
        resolver: ajvResolver(campaignSchema)
    });


    const {formState, handleSubmit, reset} = methods;

    // Set default values when campaign is loaded
    useEffect(() => {
        if (hasResolved){
            reset({...campaign });
        }
    }, [hasResolved]);


    const [submitting, setSubmitting] = useState(false);
    const [isPublishMode, setIsPublishMode] = useState(false);


    console.log(campaign)

    const onSubmit: SubmitHandler<Campaign> = async (data, event) => {

        console.log(data)

        event.preventDefault();

        try {
            // if (isPublishMode) {
            //     console.log('publishing...');
            //     const endpoint = `/${campaign.properties.id}/publish`;
            //     const response = await API.fetchWithArgs(endpoint, {}, 'PUT');
            //     console.log('Campaign published.', response);
            //     location.reload();
            // } else if (formState.isDirty) {
            //     console.log('updating...');
            //     const endpoint = `/${campaign.properties.id}`;
            //     const response = await API.fetchWithArgs(endpoint, campaignDetailsInputs, 'PUT');
            //     console.log('Campaign updated.', response);
            //     location.reload();
            // }
        } catch (error) {
            console.error('Error updating campaign.', error);
        }
    };

    if (!hasResolved) {
        return <Spinner />;
    }

    return (
        <FormProvider {...methods}>
            <form onSubmit={handleSubmit(onSubmit)}>
                <article className={styles.page}>
                    <header className={styles.pageHeader}>
                        <div className={styles.breadcrumb}>
                            <a href={`${window.GiveCampaignDetails.adminUrl}edit.php?post_type=give_forms&page=give-campaigns`}>
                                {__('Campaigns', 'give')}
                            </a>
                            {' > '}
                            <span>{campaign.title}</span>
                        </div>
                        <div className={styles.flexContainer}>
                            <div className={styles.flexRow}>
                                <h1 className={styles.pageTitle}>{campaign.title}</h1>
                                <span
                                    className={cx(
                                        styles.status,
                                        campaign.status === 'draft'
                                            ? styles.draftStatus
                                            : styles.activeStatus,
                                    )}
                                >
                                    {campaign.status}
                                </span>
                            </div>

                            <div className={styles.flexRow}>
                                {campaign.status === 'draft' && (
                                    <button
                                        type="submit"
                                        disabled={formState.isSubmitting || !formState.isDirty}
                                        className={`button button-secondary ${styles.updateCampaignButton}`}
                                    >
                                        {__('Save as draft', 'give')}
                                    </button>
                                )}
                                <button
                                    type="submit"
                                    onClick={() => {
                                        setIsPublishMode(campaign.status === 'draft')
                                    }}
                                    disabled={
                                        campaign.status !== 'draft' &&
                                        (formState.isSubmitting || !formState.isDirty)
                                    }
                                    className={`button button-primary ${styles.updateCampaignButton}`}
                                >
                                    {campaign.status === 'draft'
                                        ? __('Publish campaign', 'give')
                                        : __('Update campaign', 'give')}
                                </button>
                            </div>
                        </div>
                    </header>
                    <Tabs />
                </article>
            </form>
        </FormProvider>
    );
}
