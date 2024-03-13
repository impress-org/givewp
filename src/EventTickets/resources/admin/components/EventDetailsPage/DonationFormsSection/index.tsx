import {__} from '@wordpress/i18n';
import {createInterpolateElement} from '@wordpress/element';
import styles from './DonationFormsSection.module.scss';
import SectionTable from '../SectionTable';
import {useState} from 'react';

/**
 * Displays a blank slate for the Donation Forms table.
 *
 * @since 3.6.0
 */
const BlankSlate = () => {
    const helpMessage = createInterpolateElement(
        __(
            '<a>To link an event to a donation form</a>, add an event block to a donation form in the visual form builder.',
            'give'
        ),
        {
            a: <a href="https://docs.givewp.com/events-documentation" target="_blank" rel="noreferrer" />,
        }
    );
    return (
        <div className={styles.container}>
            <h3>{__('No linked form yet', 'give')}</h3>
            <p className={styles.helpMessage}>{helpMessage}</p>
        </div>
    );
};

/**
 * @since 3.6.0
 */
export default function DonationFormsSection() {
    const {
        adminUrl,
        event: {forms},
    } = window.GiveEventTicketsDetails;
    const [data, setData] = useState(forms);

    const tableHeaders = {
        id: __('ID', 'give'),
        title: __('Name', 'give'),
    };

    const formattedData = data.map((form) => {
        return {
            id: form.id,
            title: <a href={`${adminUrl}post.php?post=${form.id}&action=edit`}>{form.title}</a>,
        };
    });

    return (
        <section>
            <h2>{__('Donation Forms', 'give')}</h2>
            <SectionTable tableHeaders={tableHeaders} data={formattedData} blankSlate={<BlankSlate />} />
        </section>
    );
}
