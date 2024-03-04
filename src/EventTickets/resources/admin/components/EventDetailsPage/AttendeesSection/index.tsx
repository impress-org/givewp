import {__} from '@wordpress/i18n';
import {FilterConfig} from '@givewp/components/ListTable/ListTablePage';
import styles from './AttendeesSection.module.scss';
import InnerPageListTable from '../InnerPageListTable';
import {ApiSettingsProps} from '../types';
import {useState} from 'react';
import {AttendeesRowActions} from './AttendeesRowActions';

const filters: Array<FilterConfig> = [
    {
        name: 'search',
        type: 'search',
        inlineSize: '14rem',
        text: __('Search by name', 'give'),
        ariaLabel: __('search attendees', 'give'),
    },
];

/**
 * Displays a blank slate for the Attendees table.
 *
 * @unreleased
 */
const ListTableBlankSlate = () => {
    const imagePath = `${window.GiveEventTicketsDetails.pluginUrl}/assets/dist/images/list-table/blank-slate-attendees-icon.svg`;
    return (
        <div className={styles.container}>
            <img src={imagePath} alt={__('No attendees yet', 'give')} />
            <h3>{__('No attendees yet', 'give')}</h3>
        </div>
    );
};

export default function AttendeesListTable() {
    const [isOpen, setOpen] = useState<boolean>(false);
    const openModal = (attendee = null) => {
        setOpen(true);
    };
    const closeModal = (response = null) => {
        setOpen(false);
    };

    const apiSettings = window.GiveEventTicketsDetails;

    const listTableApiSettings: ApiSettingsProps = {
        ...apiSettings,
        table: window.GiveEventTicketsDetails.attendeesTable,
        apiRoot: `${apiSettings.apiRoot}/event/${apiSettings.event.id}/tickets/list-table`,
    };

    return (
        <InnerPageListTable
            title={__('Attendees', 'give')}
            singleName={__('attendee', 'give')}
            pluralName={__('attendees', 'give')}
            apiSettings={listTableApiSettings}
            filterSettings={filters}
            rowActions={AttendeesRowActions(openModal)}
            listTableBlankSlate={ListTableBlankSlate}
        />
    );
}
