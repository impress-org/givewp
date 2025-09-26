import AdminSection, { AdminSectionField } from '@givewp/components/AdminDetailsPage/AdminSection';
import { __ } from '@wordpress/i18n';
import { Interweave } from 'interweave';
import BlankSlate from './BlankSlate';
import styles from './styles.module.scss';
import { useDonorEntityRecord } from '@givewp/donors/utils';

/**
 * @since 4.9.0 Add error prop to all AdminSectionField components
 */
export default function CustomFields() {
    const { record: donor } = useDonorEntityRecord();
    const customFields = donor?.customFields || [];

    return (
        <AdminSection title={__('Custom Fields', 'give')} description={__('Custom fields filled by the donor', 'give')}>
            <AdminSectionField>
                {!customFields.length ? (
                    <BlankSlate />
                ) : (
                    <div className={styles.customFields}>
                        {customFields.map((field, index) => (
                            <div key={index} className={styles.field}>
                                <div className={styles.label}>{field.label}</div>
                                <Interweave className={styles.value} content={field.value} />
                            </div>
                        ))}
                    </div>
                )}
            </AdminSectionField>
        </AdminSection>
    );
}
