import {useState} from 'react';
import {__} from '@wordpress/i18n';
import {ListTableApi, ListTablePage} from '@givewp/components';
import {DonationFormsRowActions} from './DonationFormsRowActions';
import Onboarding, {OnboardingContext, OnboardingStateProps} from './Onboarding';
import styles from '@givewp/components/ListTable/ListTablePage/ListTablePage.module.scss';
import {BulkActionsConfig, ColumnFilterConfig, FilterConfig} from '@givewp/components/ListTable/ListTablePage';
import Select from '@givewp/components/ListTable/Select';
import {Interweave} from 'interweave';
import InterweaveSSR from '@givewp/components/ListTable/InterweaveSSR';
import BlankSlate from '@givewp/components/ListTable/BlankSlate';
import {CubeIcon} from '@givewp/components/AdminUI/Icons';

declare global {
    interface Window {
        GiveDonationForms: {
            apiNonce: string;
            bannerActionUrl: string;
            tooltipActionUrl: string;
            migrationApiRoot: string;
            apiRoot: string;
            authors: Array<{id: string | number; name: string}>;
            table: {columns: Array<object>};
            pluginUrl: string;
            showUpgradedTooltip: boolean;
            isMigrated: boolean;
            supportedAddons: Array<string>;
            supportedGateways: Array<string>;
            isOptionBasedFormEditorEnabled: boolean;
        };

        GiveNextGen?: {
            newFormUrl: string;
        };
    }
}

const API = new ListTableApi(window.GiveDonationForms);

const donationStatus = [
    {
        value: 'any',
        text: __('All', 'give'),
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

const donationFormsFilters: Array<FilterConfig> = [
    {
        name: 'search',
        type: 'search',
        text: __('Search by name or ID', 'give'),
        ariaLabel: __('Search donation forms', 'give'),
    },
    {
        name: 'status',
        type: 'select',
        text: __('status', 'give'),
        ariaLabel: __('Filter donation forms by status', 'give'),
        options: donationStatus,
    },
];

const columnFilters: Array<ColumnFilterConfig> = [
    {
        column: 'title',
        filter: (item) => {
            if (item?.v3form) {
                return (
                    <div className={styles.migratedForm}>
                        <div className={styles.tooltipContainer}>
                            <CubeIcon />
                            <div className={styles.tooltip}>{__('Uses the Visual Form Builder', 'give')}</div>
                        </div>
                        <Interweave attributes={{className: 'interweave'}} content={item?.title} />
                    </div>
                );
            }

            return <Interweave attributes={{className: 'interweave'}} content={item?.title} />;
        },
    },
    {
        column: 'status',
        filter: (item, column) => {
            if (window.GiveDonationForms.showUpgradedTooltip && item?.status_raw === 'upgraded') {
                return (
                    <div className={styles.upgradedForm}>
                        <div className={styles.tooltipContainer}>
                            <div className={styles.tooltip}>
                                {__(
                                    'The name of this form is already associated with an upgraded form. You can safely delete this form',
                                    'give'
                                )}
                                .
                                <div
                                    className={styles.link}
                                    onClick={(e) => {
                                        e.currentTarget.parentElement.remove();
                                        fetch(window.GiveDonationForms.tooltipActionUrl, {method: 'POST'});
                                    }}
                                >
                                    {__('Got it', 'give')}
                                </div>
                            </div>
                        </div>
                        <InterweaveSSR column={column} item={item} />
                    </div>
                );
            }

            return <InterweaveSSR column={column} item={item} />;
        },
    },
];

const donationFormsBulkActions: Array<BulkActionsConfig> = [
    {
        label: __('Edit', 'give'),
        value: 'edit',
        action: async (selected) => {
            const authorSelect = document.getElementById('giveDonationFormsTableSetAuthor') as HTMLSelectElement;
            const author = authorSelect.value;
            const statusSelect = document.getElementById('giveDonationFormsTableSetStatus') as HTMLSelectElement;
            const status = statusSelect.value;
            if (!(author || status)) {
                return {errors: [], successes: []};
            }
            const editParams = {
                ids: selected.join(','),
                author,
                status,
            };
            return await API.fetchWithArgs('/edit', editParams, 'UPDATE');
        },
        confirm: (selected, names) => (
            <>
                <p>Donation forms to be edited:</p>
                <ul role="document" tabIndex={0}>
                    {selected.map((id, index) => (
                        <li key={id}>
                            <Interweave content={names[index]} />
                        </li>
                    ))}
                </ul>
                <div className={styles.flexRow}>
                    <label htmlFor="giveDonationFormsTableSetAuthor">{__('Set form author', 'give')}</label>
                    <Select id="giveDonationFormsTableSetAuthor" style={{paddingInlineEnd: '2rem'}}>
                        <option value="">{__('Keep current author', 'give')}</option>
                        {window.GiveDonationForms.authors.map((author) => (
                            <option key={author.id} value={author.id}>
                                {author.name}
                            </option>
                        ))}
                    </Select>
                </div>
                <div className={styles.flexRow}>
                    <label htmlFor="giveDonationFormsTableSetStatus">{__('Set form status', 'give')}</label>
                    <Select id="giveDonationFormsTableSetStatus" style={{paddingInlineEnd: '2rem'}}>
                        <option value="">{__('Keep current status')}</option>
                        <option value="publish">{__('Published', 'give')}</option>
                        <option value="private">{__('Private', 'give')}</option>
                        <option value="pending">{__('Pending Review', 'give')}</option>
                        <option value="draft">{__('Draft', 'give')}</option>
                    </Select>
                </div>
            </>
        ),
    },
    {
        label: __('Delete', 'give'),
        value: 'delete',
        type: 'danger',
        isVisible: (data, parameters) => parameters.status === 'trash' || !data?.trash,
        action: async (selected) => await API.fetchWithArgs('/delete', {ids: selected.join(',')}, 'DELETE'),
        confirm: (selected, names) => (
            <div>
                <p>{__('Really delete the following donation forms?', 'give')}</p>
                <ul role="document" tabIndex={0}>
                    {selected.map((id, index) => (
                        <li key={id}>
                            <Interweave content={names[index]} />
                        </li>
                    ))}
                </ul>
            </div>
        ),
    },
    {
        label: __('Move to Trash', 'give'),
        value: 'trash',
        type: 'danger',
        isVisible: (data, parameters) => parameters.status !== 'trash' && data?.trash,
        action: async (selected) => await API.fetchWithArgs('/trash', {ids: selected.join(',')}, 'DELETE'),
        confirm: (selected, names) => (
            <div>
                <p>{__('Really trash the following donation forms?', 'give')}</p>
                <ul role="document" tabIndex={0}>
                    {selected.map((id, index) => (
                        <li key={id}>
                            <Interweave content={names[index]} />
                        </li>
                    ))}
                </ul>
            </div>
        ),
    },
];

/**
 * Displays a blank slate for the Forms table.
 * @since 2.27.0
 */
const ListTableBlankSlate = (
    <BlankSlate
        imagePath={`${window.GiveDonationForms.pluginUrl}/assets/dist/images/list-table/blank-slate-donation-forms-icon.svg`}
        description={__('No donation forms', 'give')}
        href={'https://docs.givewp.com/forms'}
        linkText={__('GiveWP Forms', 'give')}
    />
);

export default function DonationFormsListTable() {
    const [state, setState] = useState<OnboardingStateProps>({
        showFeatureNoticeDialog: false,
    });

    return (
        <OnboardingContext.Provider value={[state, setState]}>
            <ListTablePage
                title={__('Donation Forms', 'give')}
                singleName={__('donation form', 'give')}
                pluralName={__('donation forms', 'give')}
                rowActions={DonationFormsRowActions}
                bulkActions={donationFormsBulkActions}
                apiSettings={window.GiveDonationForms}
                filterSettings={donationFormsFilters}
                listTableBlankSlate={ListTableBlankSlate}
                columnFilters={columnFilters}
                banner={Onboarding}
            >
                {window.GiveDonationForms.isOptionBasedFormEditorEnabled && (
                    <button
                        className={`button button-secondary ${styles.button} ${styles.buttonSecondary}`}
                        onClick={showLegacyDonationForms}
                    >
                        {__('Switch to Legacy View', 'give')}
                    </button>
                )}
                <a
                    href={'edit.php?post_type=give_forms&page=givewp-form-builder'}
                    className={`button button-primary ${styles.button}`}
                >
                    {__('Add Form', 'give')}
                </a>
            </ListTablePage>
        </OnboardingContext.Provider>
    );
}

const showLegacyDonationForms = async (event) => {
    await API.fetchWithArgs('/view', {isLegacy: 1});
    window.location.href = '/wp-admin/edit.php?post_type=give_forms';
};
