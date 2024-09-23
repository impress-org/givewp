import {__} from '@wordpress/i18n';
import {useEffect, useState} from '@wordpress/element';
import {useEntityRecord} from '@wordpress/core-data';
import {ajvResolver} from '@hookform/resolvers/ajv';
import cx from 'classnames';
import {Campaign, CampaignInputFields, GiveCampaignDetails} from './types';
import {FormProvider, SubmitHandler, useForm} from 'react-hook-form';
import {Spinner} from '@givewp/components';
import Tabs from './Tabs';
import campaignSchema from './campaignSchema';

import styles from './style.module.scss';

declare const window: {
    GiveCampaignDetails: GiveCampaignDetails;
} & Window;

export default function CampaignsDetailsPage({campaignId}) {
    const {record: campaign, hasResolved, save, edit}: {
        record: Campaign,
        hasResolved: boolean,
        save: () => any,
        edit: (data: any) => void,
    } = useEntityRecord('givewp', 'campaign', campaignId);

    const methods = useForm<CampaignInputFields>({
        resolver: ajvResolver(campaignSchema),
    });

    const {formState, handleSubmit, reset, setValue} = methods;

    // Set default values when campaign is loaded
    useEffect(() => {
        if (hasResolved) {
            reset({...campaign});
        }
    }, [hasResolved]);

    console.log(formState.isDirty);

    const onSubmit: SubmitHandler<Campaign> = async (data, e) => {
        e.preventDefault();

        if (formState.isDirty) {
            edit(data);

            save().then((response: Campaign) => {
                reset(response);
            });
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
                                {campaign.status === 'active' && (
                                    <button
                                        type="submit"
                                        disabled={!formState.isDirty}
                                        className={`button button-secondary ${styles.updateCampaignButton}`}
                                        onClick={e => {
                                            setValue('status', 'draft');
                                        }}
                                    >
                                        {__('Save as draft', 'give')}
                                    </button>
                                )}
                                <button
                                    type="submit"
                                    disabled={campaign.status !== 'draft' && !formState.isDirty}
                                    className={`button button-primary ${styles.updateCampaignButton}`}
                                    onClick={e => {
                                        if (campaign.status === 'draft') {
                                            setValue('status', 'active', {shouldDirty: true});
                                        }
                                    }}
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
