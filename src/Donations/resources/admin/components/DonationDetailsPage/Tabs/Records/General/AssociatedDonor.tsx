import { __ } from '@wordpress/i18n';
import AdminSection, { AdminSectionField } from '@givewp/components/AdminDetailsPage/AdminSection';
import { useEntityRecords } from '@wordpress/core-data';
import { useFormContext } from 'react-hook-form';
import { Donor } from '@givewp/donors/admin/components/types';
import { useMemo } from 'react';
import styles from '../styles.module.scss';
import { getDonationOptionsWindowData } from '@givewp/donations/utils';

/**
 * @since 4.7.0 updated to use the donors entity
 * @since 4.6.0
 */
export default function AssociatedDonor() {
    const { register, watch } = useFormContext();
    const {mode} = getDonationOptionsWindowData();
    const donationDonorId = watch('donorId');

    const {
        records: donorRecords,
        hasResolved,
        isResolving
    } = useEntityRecords<Donor>('givewp', 'donor', {
        mode,
        //TODO: remove this once we have a better way to get all donors
        per_page: 30,
    });

    // Transform donors to the expected format: { [donorId]: "Name (email)" }
    const donors = useMemo(() => {
        return donorRecords?.reduce<Record<number, string>>((acc, donor) => {
            acc[donor.id] = `${donor.name} (${donor.email})`;
            return acc;
        }, {}) || {};
    }, [donorRecords]);

    const shouldShowEmptyOption = isResolving || (hasResolved && Object.keys(donors).length === 0);
    const emptyOptionLabel = isResolving ?
        __('Loading donors...', 'give') :
        __('No donors found.', 'give');

    return (
        <AdminSection
            title={__('Associated donor', 'give')}
            description={__('Manage the donor connected to this donation', 'give')}
        >
            <AdminSectionField>
                <label htmlFor="donorId">{__('Donor', 'give')}</label>
                <p className={styles.fieldDescription}>{__('Link the donation to the selected donor', 'give')}</p>
                <select
                    id="donorId"
                    {...register('donorId', {valueAsNumber: true})}
                    disabled={isResolving}
                    value={donationDonorId}
                >
                    {shouldShowEmptyOption && <option value="">{emptyOptionLabel}</option>}
                    {hasResolved && Object.keys(donors).length > 0 && (
                        <>
                            {donationDonorId.length > 0 &&
                                <option value="">{__('No donor attached', 'give')}</option>
                            }
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
