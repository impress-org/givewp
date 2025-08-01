import { __ } from '@wordpress/i18n';
import AdminSection, { AdminSectionField } from '@givewp/components/AdminDetailsPage/AdminSection';
import { getDonationOptionsWindowData } from '@givewp/donations/utils';
import { useFormContext } from 'react-hook-form';
import styles from '../styles.module.scss';

//TODO replace with donors API
const { donors } = getDonationOptionsWindowData();

/**
 * @since 4.6.0
 */
export default function AssociatedDonor() {
    const { register } = useFormContext();

    const emptyOptionLabel = donors && Object.keys(donors).length > 0 ?
        __('No donor attached', 'give') :
        __('No donors found.', 'give');

    return (
        <AdminSection
            title={__('Associated donor', 'give')}
            description={__('Manage the donor connected to this donation', 'give')}
        >
            <AdminSectionField>
                <label htmlFor="donorId">{__('Donor', 'give')}</label>
                <p className={styles.fieldDescription}>
                    {__('Link the donation to the selected donor', 'give')}
                </p>
                <select id="donorId" {...register('donorId', {valueAsNumber: true})}>
                    <option value="">{emptyOptionLabel}</option>
                    {Object.keys(donors).length > 0 && (
                        <>
                            {Object.entries(donors).map(([donorId, donorName]) => (
                                <option key={donorId} value={donorId}>
                                    {donorName}
                                </option>
                            ))}
                        </>
                    )}
                </select>
            </AdminSectionField>
        </AdminSection>
    );
}
