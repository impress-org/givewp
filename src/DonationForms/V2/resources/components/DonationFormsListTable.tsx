import {useState} from 'react';
import {__} from '@wordpress/i18n';
import {ListTablePage} from '@givewp/components';
import {useDonationFormsRowActions} from './DonationFormsRowActions';
import UpgradedFormTooltip from './UpgradedFormTooltip';
import Onboarding, {OnboardingContext, OnboardingStateProps} from './Onboarding';
import styles from '@givewp/components/ListTable/ListTablePage/ListTablePage.module.scss';
import formStyles from './DonationFormsListTable.module.scss';
import {ColumnFilterConfig} from '@givewp/components/ListTable/ListTablePage';
import {Interweave} from 'interweave';
import InterweaveSSR from '@givewp/components/ListTable/InterweaveSSR';
import BlankSlate from '@givewp/components/ListTable/BlankSlate';
import {CubeIcon} from '@givewp/components/AdminUI/Icons';
import AddCampaignFormModal from './AddCampaignFormModal';
import apiFetch from '@wordpress/api-fetch';
import {CampaignEntity} from '@givewp/campaigns/admin/components/types';
import type {StatusVocabularyEntry} from '@givewp/components/ListTable/filters/types';
import type {ListTableNotice} from '@givewp/components/ListTable/notices/types';
import type {ListTableScreen} from '@givewp/components/ListTable/state/types';
import donationFormsListTableScreen from './listTableScreen';
import {donationFormFilters} from './filters';

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
            table: {columns: Array<object>; statuses?: StatusVocabularyEntry[]};
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
            adminUrl: string;
        };
    }
}

/**
 * Built once: a new array on every render would recompute everything that depends on it.
 */
const standaloneFilters = donationFormFilters({withCampaign: true});
const campaignFormFilters = donationFormFilters({withCampaign: false});

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
                            {item?.v3form && window?.GiveDonationForms?.isOptionBasedFormEditorEnabled && (
                                <CubeTooltip />
                            )}
                            <Interweave attributes={{className: 'interweave'}} content={String(item?.title ?? '')} />
                        </div>
                        {item?.isDefaultCampaignForm && (
                            <div className={`${formStyles.defaultFormPill} givewp-default-form-pill`}>
                                {__('Default', 'give')}
                            </div>
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
 * @since TBD Offer "Switch to Legacy View" in the list's view menu, not in the header.
 * @since TBD Show the default form notice above the list, not as a floating tooltip.
 * @since TBD Take the campaign as a prop: it fixes the request's campaign and replaces the Campaign filter, in place of reading the address.
 * @since TBD Pass the filter declaration in place of the filter settings.
 * @since TBD Pass the actions as data; Trash, Delete and Edit author or status move from bulk actions into them. Take the screen config: the standalone list uses its own, an embedded list only the one it is given.
 */
export default function DonationFormsListTable({
    entity,
    screen,
    campaignId,
}: {
    entity?: CampaignEntity;
    screen?: ListTableScreen;
    campaignId?: number;
}) {
    const isCampaignDetailsPage = Boolean(campaignId);

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

    const notices: ListTableNotice[] =
        state.showDefaultFormTooltip && isCampaignDetailsPage
            ? [
                  {
                      id: 'default-form',
                      kind: 'onboarding',
                      title: __('Default campaign form', 'give'),
                      message: __(
                          'The default form will always appear at the top of this list. Your campaign page and blocks will collect donations through this form by default. You can change it at any time.',
                          'give'
                      ),
                      onDismiss: handleDefaultFormTooltipDismiss,
                  },
              ]
            : [];

    const rowActions = useDonationFormsRowActions(entity, isCampaignDetailsPage);
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
                filters={isCampaignDetailsPage ? campaignFormFilters : standaloneFilters}
                scope={campaignId ? {campaignId} : undefined}
                listTableBlankSlate={ListTableBlankSlate}
                columnFilters={columnFilters}
                banner={Onboarding}
                notices={notices}
                contentMode={isCampaignDetailsPage}
                screen={screen ?? (isCampaignDetailsPage ? undefined : donationFormsListTableScreen)}
                legacyView={
                    window.GiveDonationForms.isOptionBasedFormEditorEnabled && !isCampaignDetailsPage
                        ? {
                              onSwitched: () =>
                                  (window.location.href = `${window.GiveDonationForms.adminUrl}edit.php?post_type=give_forms`),
                          }
                        : undefined
                }
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
                                    campaignId={String(campaignId)}
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
                    <a href={window.GiveDonationForms.newFormUrl} className={`button button-primary ${styles.button}`}>
                        {__('Add form', 'give')}
                    </a>
                )}
            </ListTablePage>
        </OnboardingContext.Provider>
    );
}
