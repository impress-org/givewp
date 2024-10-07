import {__} from '@wordpress/i18n';
import {useEffect, useState, useRef} from '@wordpress/element';
import {useEntityRecord} from '@wordpress/core-data';
import apiFetch from '@wordpress/api-fetch';
import {JSONSchemaType} from 'ajv';
import {ajvResolver} from '@hookform/resolvers/ajv';
import cx from 'classnames';
import {GiveCampaignDetails} from './types';
import {Campaign} from '../types';
import {FormProvider, SubmitHandler, useForm} from 'react-hook-form';
import {Spinner as GiveSpinner} from '@givewp/components';
import {Spinner} from '@wordpress/components';
import Tabs from './Tabs';
import ArchiveCampaignDialog from './Components/ArchiveCampaignDialog';
import {DotsIcons, TrashIcon, ViewIcon, ArrowReverse} from './Icons';

import styles from './CampaignDetailsPage.module.scss';
import {BreadcrumbSeparatorIcon} from './Icons';

declare const window: {
    GiveCampaignDetails: GiveCampaignDetails;
} & Window;

interface Show {
    contextMenu?: boolean;
    confirmationModal?: boolean;
}

export function getGiveCampaignDetailsWindowData() {
    return window.GiveCampaignDetails;
}

export default function CampaignsDetailsPage({campaignId}) {
    const [resolver, setResolver] = useState({});
    const [isSaving, setIsSaving] = useState<null | string>(null);
    const [show, _setShowValue] = useState<Show>({
        contextMenu: false,
        confirmationModal: false,
    });

    const setShow = (data: Show) => {
        _setShowValue(prevState => {
            return {
                ...prevState,
                ...data,
            };
        });
    };

    useEffect(() => {
        apiFetch({
            path: `/give-api/v2/campaigns/${campaignId}`,
            method: 'OPTIONS',
        }).then(({schema}: {schema: JSONSchemaType<any>}) => {
            setResolver({
                resolver: ajvResolver(schema),
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

    const onSubmit: SubmitHandler<Campaign> = async (data) => {

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

    const updateStatus = (status: 'archive' | 'draft') => {
        setValue('status', status);
        handleSubmit(async (data) => {
            edit(data);

            save()
                .then((response: Campaign) => {
                    setShow({
                        contextMenu: false,
                        confirmationModal: false,
                    });
                    reset(response);
                })
                .catch((response: any) => {
                    setShow({
                        contextMenu: false,
                        confirmationModal: false,
                    });
                    console.log(response);
                });
        })();
    };
    
    const getStatus = (status: string) => {
        switch (status) {
            case 'archive':
                return __('Archived', 'give');
            case 'active':
                return __('Active', 'give');
            case 'draft':
                return __('Draft', 'give');
        }

        return null;
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
                            <BreadcrumbSeparatorIcon />
                            <span>{campaign.title}</span>
                        </div>
                        <div className={styles.flexContainer}>
                            <div className={styles.flexRow}>
                                <h1 className={styles.pageTitle}>{campaign.title}</h1>
                                <span
                                    className={cx(
                                        styles.status,
                                        styles[`${campaign.status}Status`],
                                    )}
                                >
                                    {getStatus(campaign.status)}
                                </span>
                            </div>

                            <div className={styles.flexRow}>
                                <button
                                    type="submit"
                                    disabled={campaign.status !== 'draft' && !formState.isDirty}
                                    className={`button button-primary ${styles.updateCampaignButton}`}
                                    onClick={(e) => {
                                        setValue('status', 'active', {shouldDirty: true});
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

                                <button
                                    className={`button button-secondary ${styles.campaignButtonDots}`}
                                    onClick={() => setShow({contextMenu: !show.contextMenu})}
                                >
                                    <DotsIcons />
                                </button>

                                {!isSaving && show.contextMenu && (
                                    <div className={styles.contextMenu}>
                                        <a
                                            href="#"
                                            aria-label={__('View Campaign', 'give')}
                                            className={styles.contextMenuItem}
                                        >
                                            <ViewIcon /> {__('View Campaign', 'give')}
                                        </a>
                                        {campaign.status === 'archive' ? (
                                            <a
                                                href="#"
                                                className={cx(styles.contextMenuItem, styles.draft)}
                                                onClick={() => updateStatus('draft')}
                                            >
                                                <ArrowReverse /> {__('Move to draft', 'give')}
                                            </a>
                                        ) : (
                                            <a
                                                href="#"
                                                className={cx(styles.contextMenuItem, styles.archive)}
                                                onClick={() => setShow({confirmationModal: true})}
                                            >
                                                <TrashIcon /> {__('Archive Campaign', 'give')}
                                            </a>
                                        )}

                                    </div>
                                )}
                            </div>
                        </div>
                    </header>
                    <Tabs />
                    <ArchiveCampaignDialog
                        title={__('Archive Campaign', 'give')}
                        isOpen={show.confirmationModal}
                        handleClose={() => setShow({confirmationModal: false, contextMenu: false})}
                        handleConfirm={() => updateStatus('archive')}
                    />
                </article>
            </form>
        </FormProvider>
    );
}
