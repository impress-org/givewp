import {useState} from 'react';
import {__} from '@wordpress/i18n';
import {ListTableApi, ListTablePage} from '@givewp/components';
import {useDonationFormsRowActions} from './DonationFormsRowActions';
import UpgradedFormTooltip from './UpgradedFormTooltip';
import Onboarding, {OnboardingContext, OnboardingStateProps} from './Onboarding';
import styles from '@givewp/components/ListTable/ListTablePage/ListTablePage.module.scss';
import formStyles from './DonationFormsListTable.module.scss';
import {ColumnFilterConfig, FilterConfig} from '@givewp/components/ListTable/ListTablePage';
import SwitchToLegacyViewButton from '@givewp/components/ListTable/ListTablePage/SwitchToLegacyViewButton';
import {Interweave} from 'interweave';
import InterweaveSSR from '@givewp/components/ListTable/InterweaveSSR';
import BlankSlate from '@givewp/components/ListTable/BlankSlate';
import {CubeIcon} from '@givewp/components/AdminUI/Icons';
import AddCampaignFormModal from './AddCampaignFormModal';
import DefaultFormNotice from '@givewp/campaigns/admin/components/CampaignDetailsPage/Components/Notices/DefaultFormNotice';
import apiFetch from '@wordpress/api-fetch';
import {CampaignEntity} from '@givewp/campaigns/admin/components/types';
import type {ListTableScreen} from '@givewp/components/ListTable/state/types';
import donationFormsListTableScreen from './listTableScreen';

declare global {
    interface Window {
        GiveDonationForms: {
            apiNonce: string;
            bannerActionUrl: string;
            tooltipActionUrl: string;
            migrationApiRoot: string;
            defaultFormActionUrl: string;
            apiRoot: string;
            authors: Array<{id: string | number; name: string}>;
            table: {columns: Array<object>};
            pluginUrl: string;
            showUpgradedTooltip: boolean;
            isMigrated: boolean;
            migratedFormUrl: string;
            supportedAddons: Array<string>;
            supportedGateways: Array<string>;
            isOptionBasedFormEditorEnabled: boolean;
            locale: string;
            showDefaultFormTooltip: boolean;
            campaignUrl: string;
            newFormUrl: string;
        };
    }
}

const API = new ListTableApi(window.GiveDonationForms);

const donationStatus = [
    {
        value: 'any',
        text: __('All Status', 'give'),
    },
    {
        value: 'publish',
        text: __('Published', 'give'),
    },
    {
        value: 'pending',
        text: __('Pending', 'give'),
    },
    {
        value: 'draft',
        text: __('Draft', 'give'),
    },
    {
        value: 'trash',
        text: __('Trash', 'give'),
    },
    {
        value: 'upgraded',
        text: __('Upgraded', 'give'),
    },
];

const urlParams = new URLSearchParams(window.location.search);

const isCampaignDetailsPage = urlParams.get('id') && 'give-campaigns' === urlParams.get('page');
const campaignId = urlParams.get('id');

export const donationFormsFilters: Array<FilterConfig> = [
    {
        name: 'status',
        type: 'select',
        text: __('status', 'give'),
        ariaLabel: __('Filter donation forms by status', 'give'),
        options: donationStatus,
    },
    {
        name: 'search',
        type: 'search',
        text: __('Search by name or ID', 'give'),
        ariaLabel: __('Search donation forms', 'give'),
    },
];

if (!isCampaignDetailsPage) {
    donationFormsFilters.unshift({
        name: 'campaign',
        type: 'campaignselect',
        text: __('All campaigns', 'give'),
        ariaLabel: __('Filter donation forms by campaign', 'give'),
        options: [{value: 'none', text: __('No campaign', 'give')}],
    });
}

if (isCampaignDetailsPage) {
    donationFormsFilters.push({
        name: 'campaignId',
        type: 'hidden',
        options: [
            {
                value: campaignId,
                text: __('All Campaign Forms', 'give'),
            },
        ],
    });
}

/**
 * @since 4.6.0 add option based form editor enabled check.
 */
const columnFilters: Array<ColumnFilterConfig> = [
    {
        column: 'title',
        filter: (item) => {
            const CubeTooltip = () => (
                <div className={formStyles.tooltipContainer}>
                    <CubeIcon />
                    <div className={formStyles.tooltip}>{__('Uses the Visual Form Builder', 'give')}</div>
                </div>
            );

            return (
                <>
                    <div className={formStyles.titleContainer}>
                        <div className={formStyles.migratedForm}>
                            {item?.v3form && window?.GiveDonationForms?.isOptionBasedFormEditorEnabled && <CubeTooltip />}
                            <Interweave attributes={{className: 'interweave'}} content={String(item?.title ?? '')} />
                        </div>
                        {item?.isDefaultCampaignForm && (
                            <div className={`${formStyles.defaultFormPill} givewp-default-form-pill`}>{__('Default', 'give')}</div>
                        )}
                    </div>
                </>
            );
        },
    },
    {
        column: 'status',
        filter: (item, column) => {
            if (window.GiveDonationForms.showUpgradedTooltip && item?.status_raw === 'upgraded') {
                return (
                    <div className={formStyles.upgradedForm}>
                        <UpgradedFormTooltip
                            onDismiss={() => fetch(window.GiveDonationForms.tooltipActionUrl, {method: 'POST'})}
                        />
                        <InterweaveSSR column={column} item={item} />
                    </div>
                );
            }

            return <InterweaveSSR column={column} item={item} />;
        },
    },
];

/**
 * Displays a blank slate for the Forms table.
 * @since 2.27.0
 */
const ListTableBlankSlate = (
    <BlankSlate
        imagePath={`${window.GiveDonationForms.pluginUrl}build/assets/dist/images/list-table/blank-slate-donation-forms-icon.svg`}
        description={__('No donation forms', 'give')}
        href={'https://docs.givewp.com/forms'}
        linkText={__('GiveWP Forms', 'give')}
    />
);

/**
 * @since TBD Pass the actions as data; Trash, Delete and Edit author or status move from bulk actions into them. Take the screen config: the standalone list uses its own, an embedded list only the one it is given.
 */
export default function DonationFormsListTable({entity, screen}: {entity?: CampaignEntity; screen?: ListTableScreen}) {
    const [state, setState] = useState<OnboardingStateProps>({
        showFeatureNoticeDialog: false,
        showDefaultFormTooltip: window.GiveDonationForms.showDefaultFormTooltip,
    });

    const handleDefaultFormTooltipDismiss = () => {
        apiFetch({
            url: window.GiveDonationForms.defaultFormActionUrl,
            method: 'POST',
        }).then(() => {
            setState((prevState) => {
                return {
                    ...prevState,
                    showDefaultFormTooltip: false,
                };
            });
        });
    };

    const rowActions = useDonationFormsRowActions(entity, Boolean(isCampaignDetailsPage));
    const [isOpen, setOpen] = useState<boolean>(false);
    const openModal = () => setOpen(true);
    const closeModal = () => setOpen(false);

    return (
        <OnboardingContext.Provider value={[state, setState]}>
            <ListTablePage
                title={__('Donation Forms', 'give')}
                singleName={__('donation form', 'give')}
                pluralName={__('donation forms', 'give')}
                rowActions={rowActions}
                apiSettings={window.GiveDonationForms}
                filterSettings={donationFormsFilters}
                listTableBlankSlate={ListTableBlankSlate}
                columnFilters={columnFilters}
                banner={Onboarding}
                contentMode={isCampaignDetailsPage}
                screen={screen ?? (isCampaignDetailsPage ? undefined : donationFormsListTableScreen)}
            >
                {isCampaignDetailsPage ? (
                    <div className={`${styles.flexRow} ${styles.justifyContentEnd}`}>
                        {window.GiveDonationForms.isOptionBasedFormEditorEnabled ? (
                            <>
                                <button className={styles.addCampaignFormButton} onClick={openModal}>
                                    {__('Add campaign form', 'give')}
                                </button>
                                <AddCampaignFormModal
                                    isOpen={isOpen}
                                    handleClose={closeModal}
                                    title={__('Choose how you want to edit your campaign form', 'give')}
                                    campaignId={campaignId}
                                />
                            </>
                        ) : (
                            <>
                                <a
                                    href={`${window.GiveDonationForms.newFormUrl}&campaignId=${campaignId}`}
                                    className={styles.addCampaignFormButton}
                                >
                                    {__('Add campaign form', 'give')}
                                </a>
                            </>
                        )}
                    </div>
                ) : (
                    <>
                        <a href={window.GiveDonationForms.newFormUrl} className={`button button-primary ${styles.button}`}>
                            {__('Add form', 'give')}
                        </a>
                        {window.GiveDonationForms.isOptionBasedFormEditorEnabled && (
                            <SwitchToLegacyViewButton
                                api={API}
                                className={`button button-secondary ${styles.button} ${styles.buttonSecondary}`}
                                onSwitched={() => (window.location.href = '/wp-admin/edit.php?post_type=give_forms')}
                            />
                        )}
                    </>
                )}
                {state.showDefaultFormTooltip && isCampaignDetailsPage && (
                    <DefaultFormNotice handleClick={handleDefaultFormTooltipDismiss} />
                )}
            </ListTablePage>
        </OnboardingContext.Provider>
    );
}

