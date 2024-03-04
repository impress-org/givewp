import {__} from '@wordpress/i18n';
import {createInterpolateElement} from '@wordpress/element';
import styles from './DonationFormsSection.module.scss';
import SectionTable from '../SectionTable';

/**
 * Displays a blank slate for the Donation Forms table.
 *
 * @unreleased
 */
const BlankSlate = () => {
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
    const {
        event: {forms},
    } = window.GiveEventTicketsDetails;

    const tableHeaders = {
        id: __('ID', 'give'),
        title: __('Name', 'give'),
    };

    return (
        <section>
            <h2>{__('Tickets', 'give')}</h2>
            <SectionTable tableHeaders={tableHeaders} data={forms} blankSlate={<BlankSlate />} />
        </section>
    );
}
