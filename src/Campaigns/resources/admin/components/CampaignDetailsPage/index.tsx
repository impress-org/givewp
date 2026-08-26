import { createCampaignPage, getCampaignOptionsWindowData, updateCampaignStatus, useCampaignEntityRecord } from '@givewp/campaigns/utils';
import { useDispatch } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import cx from 'classnames';
import { useForm } from 'react-hook-form';
import { ArrowReverse, TrashIcon, ViewIcon } from '../Icons';
import { Campaign } from '../types';
import ArchivedCampaignNotice from './Components/Notices/ArchivedCampaignNotice';
import styles from './CampaignDetailsPage.module.scss';
import AdminDetailsPage from '@givewp/components/AdminDetailsPage';
import ConfirmationDialog from '@givewp/components/AdminDetailsPage/ConfirmationDialog';
import tabDefinitions from './Tabs/definitions';
import '@givewp/campaigns/store';

const StatusBadge = ({ status }: { status: string }) => {
    const statusMap = {
        active: __('Active', 'give'),
        archived: __('Archived', 'give'),
    };

    return (
        <div className="interweave">
            <div className={`statusBadge statusBadge--${status}`}>
                <p>{statusMap[status]}</p>
            </div>
        </div>
    );
};

export default function CampaignsDetailsPage({ campaignId }) {
    const { adminUrl } = getCampaignOptionsWindowData();
    const [isCreatingPage, setIsCreatingPage] = useState<boolean>(false);
    const [showConfirmationDialog, setShowConfirmationDialog] = useState<string | null>(null);

    const dispatch = useDispatch(`givewp/admin-details-page-notifications`);
    const { receiveEntityRecords } = useDispatch(coreStore);

    const { record: campaign } = useCampaignEntityRecord(campaignId);
    const methods = useForm<Campaign>({
        mode: 'onBlur',
        shouldFocusError: true,
    });
    const { setValue } = methods;

    // Show campaign archived notice
    useEffect(() => {
        if (campaign?.status !== 'archived') {
            return;
        }

        dispatch.addNotice({
            id: 'update-archive-notice',
            type: 'warning',
            content: (onDismiss) => (
                <ArchivedCampaignNotice
                    handleClick={() => {
                        onDismiss();
                        updateStatus('active');
                    }}
                />
            ),
        });
    }, [campaign?.status]);

    const updateStatus = async (status: 'archived' | 'active') => {
        const campaignStatusResponse = await updateCampaignStatus(campaign.id, status);

        if (campaignStatusResponse) {
            receiveEntityRecords('givewp', 'campaign', campaignStatusResponse, undefined, false);
            setValue('status', campaignStatusResponse?.status);
            dispatch.addSnackbarNotice({
                id: `save-success`,
                content: getMessageByStatus(status),
            });
        } else {
            dispatch.addSnackbarNotice({
                id: `save-error`,
                type: 'error',
                content: __('Failed to update campaign status', 'give'),
            });
        }
    };

    async function handleCampaignPageCreation() {
        setIsCreatingPage(true);
        const campaignPageResponse = await createCampaignPage(campaign.id);
        setIsCreatingPage(false);

        if (campaignPageResponse) {
            receiveEntityRecords('givewp', 'campaign', {...campaign, pageId: campaignPageResponse?.id}, undefined, false);
            setValue('pageId', campaignPageResponse?.id);
            dispatch.addSnackbarNotice({
                id: `save-success`,
                content: __('Campaign page created', 'give'),
            });
        } else {
            dispatch.addSnackbarNotice({
                id: `save-error`,
                type: 'error',
                content: __('Failed to create campaign page', 'give'),
            });
        }
    }

    const SecondaryActionButton = ({ className }: { className: string }) => {
        return (
            !isCreatingPage && campaign.pageId > 0 ? (
                <a
                    className={`button button-secondary ${className}`}
                    href={`${adminUrl}post.php?post=${campaign.pageId}&action=edit`}
                    rel="noopener noreferrer"
                >
                    {__('Edit campaign page', 'give')}
                </a>
            ) : (
                <button
                    type="button"
                    className={`button button-tertiary ${className}`}
                    onClick={handleCampaignPageCreation}
                    disabled={isCreatingPage}
                >
                    {isCreatingPage
                        ? __('Creating Campaign Page', 'give')
                        : __('Create Campaign Page', 'give')}
                </button>
            )
        );
    };

    const ContextMenuItems = ({ className }: { className: string }) => {
        return (
            <>
                {campaign.pagePermalink && (
                    <a
                        href={campaign.pagePermalink}
                        aria-label={__('View Campaign', 'give')}
                        className={className}
                    >
                        <ViewIcon /> {__('View Campaign', 'give')}
                    </a>
                )}
                {campaign.status === 'archived' ? (
                    <a
                        href="#"
                        className={cx(className, styles.draft)}
                        onClick={() => {
                            updateStatus('active');
                            dispatch.dismissNotification('update-archive-notice');
                        }}
                    >
                        <ArrowReverse /> {__('Move to Active', 'give')}
                    </a>
                ) : (
                    <a
                        href="#"
                        className={cx(className, styles.archive)}
                        onClick={() => setShowConfirmationDialog('archive')}
                    >
                        <TrashIcon /> {__('Archive Campaign', 'give')}
                    </a>
                )}
            </>
        );
    };

    return (
        <AdminDetailsPage
            objectId={campaign?.id}
            objectType="campaign"
            objectTypePlural="campaigns"
            useObjectEntityRecord={useCampaignEntityRecord}
            tabDefinitions={tabDefinitions}
            breadcrumbUrl={`${adminUrl}edit.php?post_type=give_forms&page=give-campaigns`}
            breadcrumbTitle={__('Campaigns', 'give')}
            pageTitle={campaign?.title}
            StatusBadge={() => <StatusBadge status={campaign?.status} />}
            SecondaryActionButton={SecondaryActionButton}
            ContextMenuItems={ContextMenuItems}
        >
            <ConfirmationDialog
                title={__('Archive Campaign', 'give')}
                actionLabel={__('Archive campaign', 'give')}
                isOpen={showConfirmationDialog === 'archive'}
                handleClose={() => setShowConfirmationDialog(null)}
                handleConfirm={() => {
                    updateStatus('archived');
                    setShowConfirmationDialog(null);
                }}
            >
                {__('Are you sure you want to archive your campaign? All forms associated with this campaign will be inaccessible to donors.', 'give')}
            </ConfirmationDialog>
        </AdminDetailsPage>
    );
}

const getMessageByStatus = (status: string) => {
    switch (status) {
        case 'archived':
            return __('Campaign is moved to archive', 'give');
        case 'active':
            return __('Campaign is now active', 'give');
    }

    return null;
};
