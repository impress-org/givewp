import {__} from '@wordpress/i18n';
import {useEffect, useState} from '@wordpress/element';
import {useEntityRecord} from '@wordpress/core-data';
import {useDispatch} from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
import {JSONSchemaType} from 'ajv';
import {ajvResolver} from '@hookform/resolvers/ajv';
import {GiveCampaignDetails} from './types';
import {Campaign} from '../types';
import {FormProvider, SubmitHandler, useForm} from 'react-hook-form';
import {Spinner as GiveSpinner} from '@givewp/components';
import {Spinner} from '@wordpress/components';
import Tabs from './Tabs';
import ArchiveCampaignDialog from './Components/ArchiveCampaignDialog';
import {ArrowReverse, BreadcrumbSeparatorIcon, DotsIcons, TrashIcon, TriangleIcon, ViewIcon} from '../Icons';
import ArchivedCampaignNotice from './Components/Notices/ArchivedCampaignNotice';
import NotificationPlaceholder from '../Notifications';
import cx from 'classnames';

import styles from './CampaignDetailsPage.module.scss';
import useCampaignEntityRecord from "@givewp/campaigns/admin/components/CampaignDetailsPage/useCampaignEntityRecord";

declare const window: {
    GiveCampaignDetails: GiveCampaignDetails;
} & Window;

interface Show {
    contextMenu?: boolean;
    confirmationModal?: boolean;
}

const StatusBadge = ({status}: {status: string}) => {
    const statusMap = {
        active: __('Active', 'give'),
        archived: __('Archived', 'give'),
        draft: __('Draft', 'give'),
    };

    return (
        <div className="interweave">
            <div className={`statusBadge statusBadge--${status}`}>
                <p>{statusMap[status]}</p>
            </div>
        </div>
    );
};

export default function CampaignsDetailsPage({campaignId}) {
    const [resolver, setResolver] = useState({});
    const [isSaving, setIsSaving] = useState<null | string>(null);
    const [show, _setShowValue] = useState<Show>({
        contextMenu: false,
        confirmationModal: false,
    });

    const dispatch = useDispatch('givewp/campaign-notifications');

    const setShow = (data: Show) => {
        _setShowValue((prevState) => {
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
        campaign,
        hasResolved,
        save,
        edit,
    } = useCampaignEntityRecord();

    const methods = useForm<Campaign>({
        mode: 'onChange',
        ...resolver,
    });

    const {formState, handleSubmit, reset, setValue, watch} = methods;

    const [enableCampaignPage] = watch(['enableCampaignPage']);

    // Set default values when campaign is loaded
    useEffect(() => {
        if (hasResolved) {
            reset({...campaign});
        }
    }, [hasResolved]);

    // Show campaign archived notice
    useEffect(() => {
        if (campaign?.status !== 'archived') {
            return;
        }

        dispatch.addNotice({
            id: 'update-archive-notice',
            type: 'warning',
            onDismiss: () => updateStatus('draft'),
            content: (onDismiss: Function) => <ArchivedCampaignNotice handleClick={onDismiss} />
        });
    }, [campaign?.status]);

    const onSubmit: SubmitHandler<Campaign> = async (data) => {
        if (formState.isDirty) {
            setIsSaving(data.status);

            edit(data);

            try {
                const response = await save();

                setIsSaving(null);
                reset(response);

                dispatch.addSnackbarNotice({
                    id: `save-${data.status}`,
                    content: __('Campaign updated', 'give'),
                });
            } catch (err) {
                setIsSaving(null);

                dispatch.addSnackbarNotice({
                    id: `save-error`,
                    type: 'error',
                    content: __('Campaign update failed', 'give'),
                });
            }
        }
    };

    const updateStatus = (status: 'archived' | 'draft') => {
        setValue('status', status);
        handleSubmit(async (data) => {
            edit(data);

            try {
                const response: Campaign = await save();

                setShow({
                    contextMenu: false,
                    confirmationModal: false,
                });
                reset(response);

                dispatch.addSnackbarNotice({
                    id: `update-${status}`,
                    content: getMessageByStatus(status),
                });
            } catch (err) {
                setShow({
                    contextMenu: false,
                    confirmationModal: false,
                });

                dispatch.addSnackbarNotice({
                    id: 'update-error',
                    type: 'error',
                    content: __('Something went wrong', 'give'),
                });
            }
        })();
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
                                <StatusBadge status={campaign.status} />
                            </div>

                            <div className={`${styles.flexRow} ${styles.justifyContentEnd}`}>
                                {enableCampaignPage && (
                                    <a
                                        className={`button button-secondary ${styles.editCampaignPageButton}`}
                                        href={`${window.GiveCampaignDetails.adminUrl}?action=edit_campaign_page&campaign_id=${campaignId}`}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        {__('Edit campaign page', 'give')}
                                    </a>
                                )}
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
                                    className={`button button-secondary
                                    ${styles.campaignButtonDots}
                                    ${show.contextMenu && styles.campaignButtonDotsActive}`}
                                    onClick={() => setShow({contextMenu: !show.contextMenu})}
                                >
                                    <DotsIcons />
                                </button>

                                {!isSaving && show.contextMenu && (
                                    <div className={styles.contextMenu}>
                                        {enableCampaignPage && (
                                            <a
                                                href="#"
                                                aria-label={__('View Campaign', 'give')}
                                                className={styles.contextMenuItem}
                                            >
                                                <ViewIcon /> {__('View Campaign', 'give')}
                                            </a>
                                        )}
                                        {campaign.status === 'archived' ? (
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
                        handleConfirm={() => updateStatus('archived')}
                    />
                </article>
            </form>
            <NotificationPlaceholder type="snackbar" />
        </FormProvider>
    );
}

const getMessageByStatus = (status: string) => {
    switch (status) {
        case 'archived':
            return __('Campaign is moved to archive', 'give');
        case 'active':
            return __('Campaign is now active', 'give');
        case 'draft':
            return __('Campaign is moved to draft', 'give');
    }

    return null;
};
