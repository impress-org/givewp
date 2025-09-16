import AdminSection, {AdminSectionField} from '@givewp/components/AdminDetailsPage/AdminSection';
import {__} from '@wordpress/i18n';
import {Interweave} from 'interweave';
import {useFormContext, useFormState} from 'react-hook-form';
import BlankSlate from './BlankSlate';
import styles from './styles.module.scss';

interface CustomField {
    label: string;
    value: string;
}

/**
 * @since 4.9.0 Add error prop to all AdminSectionField components
 */
export default function CustomFields() {
    const {getValues} = useFormContext();
    const {errors} = useFormState();
    const customFields: CustomField[] = getValues('customFields') || [];

    return (
        <AdminSection title={__('Custom Fields', 'give')} description={__('Custom fields filled by the donor', 'give')}>
            <AdminSectionField error={errors.customFields?.message as string}>
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
