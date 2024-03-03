import {__} from '@wordpress/i18n';
import {createInterpolateElement} from '@wordpress/element';
import styles from './DonationFormsSection.module.scss';
import {ApiSettingsProps} from '../types';
import InnerPageListTable from '../InnerPageListTable';

/**
 * Displays a blank slate for the EventTickets table.
 *
 * @unreleased
 */
const ListTableBlankSlate = () => {
    const helpMessage = createInterpolateElement(
        __(
            '<a>To link an event to a donation form</a>, add an event block to a donation form in the visual form builder.',
            'give'
        ),
        {
            a: <a href="https://givewp.com/documentation/" target="_blank" rel="noreferrer" />,
        }
    );
    return (
        <div className={styles.container}>
            <h3>{__('No linked form yet', 'give')}</h3>
            <p className={styles.helpMessage}>{helpMessage}</p>
        </div>
    );
};

export default function DonationFormsSection() {
    const apiSettings: ApiSettingsProps = {
        ...window.GiveEventTicketsDetails,
        table: window.GiveEventTicketsDetails.donationFormsTable,
    };
    apiSettings.apiRoot += `/event/${apiSettings.event.id}/forms/list-table`;

    return (
        <section>
            <h2>{__('Donation Forms', 'give')}</h2>
            <InnerPageListTable
                apiSettings={apiSettings}
                singleName={__('Donation Form', 'give')}
                pluralName={__('Donation Forms', 'give')}
                title={__('Donation Forms', 'give')}
                rowActions={() => null}
                listTableBlankSlate={ListTableBlankSlate}
            />
        </section>
    );
}
