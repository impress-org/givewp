import {createContext, useState, useCallback} from 'react';
import {__, sprintf} from '@wordpress/i18n';
import {ListTableApi, ListTablePage} from '@givewp/components';
import {DonationFormsRowActions} from './DonationFormsRowActions';
import Onboarding from './Onboarding';
import styles from '@givewp/components/ListTable/ListTablePage/ListTablePage.module.scss';
import {BulkActionsConfig, FilterConfig} from '@givewp/components/ListTable/ListTablePage';
import Select from '@givewp/components/ListTable/Select';
import {Interweave} from 'interweave';
import BlankSlate from '@givewp/components/ListTable/BlankSlate';

declare global {
    interface Window {
        GiveDonationForms: {
            apiNonce: string;
            onboardingApiRoot: string;
            apiRoot: string;
            authors: Array<{ id: string | number; name: string }>;
            table: { columns: Array<object> };
            pluginUrl: string;
            showMigrationOnboarding: boolean;
            migrationOnboardingCompleted: boolean;
            unsupportedAddons: Array<string>;
        };

        GiveNextGen?: {
            newFormUrl: string;
        };
    }
}

interface MigrationTransferStateProps {
    migrationOnboardingCompleted: boolean;
    showMigrationSuccessDialog: boolean;
    showTransferSuccessDialog: boolean;
    showMigrationCompletedToast: boolean;
    formId: number | null;
    formName: string | null;
}

const API = new ListTableApi(window.GiveDonationForms);

export const MigrationOnboardingContext = createContext([]);

export const updateOnboardingOption = async optionName => fetch(window.GiveDonationForms.onboardingApiRoot, {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': window.GiveDonationForms.apiNonce
    },
    body: JSON.stringify({option: optionName})
})

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

const v2FormBadge = item => {
    if (item.v2form) {
        return <div className={styles.v2Badge}>
            <svg width="4" height="4" viewBox="0 0 4 4" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="2" cy="2" r="2" fill="#2B13BF" />
            </svg>
            V2
        </div>;
    }
    return null;
}

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

    const [state, setState] = useState<MigrationTransferStateProps>({
        migrationOnboardingCompleted: Boolean(window.GiveDonationForms.migrationOnboardingCompleted),
        showMigrationSuccessDialog: false,
        showTransferSuccessDialog: false,
        showMigrationCompletedToast: false,
        formId: null,
        formName: null,
    })

    const closeMigrationSuccessDialog = useCallback(() => setState(prev => ({
        ...prev,
        showMigrationSuccessDialog: false
    })), []);

    const closeTransferSuccessDialog = useCallback(() => setState(prev => ({
        ...prev,
        showTransferSuccessDialog: false
    })), []);

    const closeMigrationCompletedToast = useCallback(() => setState(prev => ({
        ...prev,
        showMigrationCompletedToast: false
    })), []);

    return (
        <MigrationOnboardingContext.Provider value={[state, setState]}>
            <ListTablePage
                title={__('Donation Forms', 'give')}
                singleName={__('donation form', 'give')}
                pluralName={__('donation forms', 'give')}
                rowActions={DonationFormsRowActions}
                bulkActions={donationFormsBulkActions}
                apiSettings={window.GiveDonationForms}
                filterSettings={donationFormsFilters}
                listTableBlankSlate={ListTableBlankSlate}
                banner={MigrationBanner}
                rowBadge={v2FormBadge}
            >
                {!!window.GiveNextGen?.newFormUrl && (
                    <a href={window.GiveNextGen.newFormUrl} className={styles.addFormButton}>
                        {__('Add Next Gen Form', 'give')}
                    </a>
                )}
                <a href={'post-new.php?post_type=give_forms'} className={styles.addFormButton}>
                    {__('Add Form', 'give')}
                </a>
                <button className={styles.addFormButton} onClick={showLegacyDonationForms}>
                    {__('Switch to Legacy View')}
                </button>
            </ListTablePage>
        </OnboardingContext.Provider>
    );
}

const showLegacyDonationForms = async (event) => {
    await API.fetchWithArgs('/view', {isLegacy: 1});
    window.location.href = '/wp-admin/edit.php?post_type=give_forms';
};
