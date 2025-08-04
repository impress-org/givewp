import { __ } from '@wordpress/i18n';
import AdminSection, { AdminSectionField } from '@givewp/components/AdminDetailsPage/AdminSection';
import { useEntityRecords } from '@wordpress/core-data';
import { useFormContext } from 'react-hook-form';
import { Donor } from '@givewp/donors/admin/components/types';
import styles from '../styles.module.scss';
import { getDonationOptionsWindowData } from '@givewp/donations/utils';

/**
 * @unreleased updated to use the donors entity
 * @since 4.6.0
 */
export default function AssociatedDonor() {
    const { register, watch } = useFormContext();
    const {mode} = getDonationOptionsWindowData();

    const {
        records: donorRecords,
        hasResolved,
        isResolving
    } = useEntityRecords<Donor>('givewp', 'donor', {
        mode,
    });

    // Transform donors to the expected format: { [donorId]: "Name (email)" }
    const donors = donorRecords?.reduce<Record<number, string>>((acc, donor) => {
        acc[donor.id] = `${donor.name} (${donor.email})`;
        return acc;
    }, {}) || {};

    const emptyOptionLabel = hasResolved && Object.keys(donors).length > 0 ?
        __('No donor attached', 'give') :
        hasResolved ? __('No donors found.', 'give') : __('Loading donors...', 'give');

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
                <select id="donorId" {...register('donorId', {valueAsNumber: true})} disabled={isResolving} value={watch('donorId')}>
                    <option value="">{emptyOptionLabel}</option>
                    {hasResolved && Object.keys(donors).length > 0 && (
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
