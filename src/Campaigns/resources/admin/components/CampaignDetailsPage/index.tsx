import {__} from '@wordpress/i18n';
import {useEffect, useState} from '@wordpress/element';
import {useEntityRecord} from '@wordpress/core-data';
import apiFetch from '@wordpress/api-fetch';
import cx from 'classnames';
import {Campaign, GiveCampaignDetails} from './types';
import {FormProvider, SubmitHandler, useForm} from 'react-hook-form';
import {Spinner as GiveSpinner} from '@givewp/components';
import {Spinner} from '@wordpress/components';
import Tabs from './Tabs';

import styles from './CampaignDetailsPage.module.scss';

declare const window: {
    GiveCampaignDetails: GiveCampaignDetails;
} & Window;

export function getGiveCampaignDetailsWindowData() {
    return window.GiveCampaignDetails;
}

export default function CampaignsDetailsPage({campaignId}) {
    const [resolver, setResolver] = useState({});
    const [isSaving, setIsSaving] = useState<null | string>(null);

    useEffect(() => {
        apiFetch({
            path: `/give-api/v2/campaigns/${campaignId}`,
            method: 'OPTIONS',
        }).then(({schema}) => {
            setResolver({
                //resolver: ajvResolver(schema),
            });
        });
    }, []);

    const {
        record: campaign,
        hasResolved,
        save,
        edit,
    }: {
        record: Campaign;
        hasResolved: boolean;
        save: () => any;
        edit: (data: Campaign) => void;
    } = useEntityRecord('givewp', 'campaign', campaignId);

    const methods = useForm<Campaign>({
        mode: 'onChange',
        ...resolver,
    });

    const {formState, handleSubmit, reset, setValue} = methods;

    // Set default values when campaign is loaded
    useEffect(() => {
        if (hasResolved) {
            reset({...campaign});
        }
    }, [hasResolved]);

    const onSubmit: SubmitHandler<Campaign> = async (data, e) => {
        e.preventDefault();

        if (formState.isDirty) {
            setIsSaving(data.status);

            edit(data);

            save()
                .then((response: Campaign) => {
                    setIsSaving(null);
                    reset(response);
                })
                .catch((response: any) => {
                    setIsSaving(null);
                    //todo: add error handling
                    console.log(response);
                });
        }
    };

    if (!hasResolved) {
        return (
            <div className={styles.loadingContainer}>
                <div className={styles.loadingContainerContent}>
                    <GiveSpinner />
                    <div className={styles.loadingContainerContentText}>{__('Loading campaign...', 'give')}</div>
                </div>
            </div>
        );
    }

    return (
        <FormProvider {...methods}>
            <form onSubmit={handleSubmit(onSubmit)}>
                <article className={`interface-interface-skeleton__content ${styles.page}`}>
                    <header className={styles.pageHeader}>
                        <div className={styles.breadcrumb}>
                            <a
                                href={`${window.GiveCampaignDetails.adminUrl}edit.php?post_type=give_forms&page=give-campaigns`}
                            >
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
                                        campaign.status === 'draft' ? styles.draftStatus : styles.activeStatus
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
                                        onClick={(e) => {
                                            setValue('status', 'draft');
                                        }}
                                    >
                                        {isSaving === 'draft' ? (
                                            <>
                                                {__('Saving draft', 'give')}
                                                <Spinner />
                                            </>
                                        ) : (
                                            __('Save as draft', 'give')
                                        )}
                                    </button>
                                )}
                                <button
                                    type="submit"
                                    disabled={campaign.status !== 'draft' && !formState.isDirty}
                                    className={`button button-primary ${styles.updateCampaignButton}`}
                                    onClick={(e) => {
                                        if (campaign.status === 'draft') {
                                            setValue('status', 'active', {shouldDirty: true});
                                        }
                                    }}
                                >
                                    {isSaving === 'active' ? (
                                        <>
                                            {__('Updating campaign', 'give')}
                                            <Spinner />
                                        </>
                                    ) : campaign.status === 'draft' ? (
                                        __('Publish campaign', 'give')
                                    ) : (
                                        __('Update campaign', 'give')
                                    )}
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
