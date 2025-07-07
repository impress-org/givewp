import { __ } from "@wordpress/i18n";
import { useFormContext } from "react-hook-form";
import { Interweave } from 'interweave';
import AdminSection, { AdminSectionField } from '@givewp/components/AdminDetailsPage/AdminSection';
import BlankSlate from './BlankSlate';
import styles from './styles.module.scss';

interface CustomField {
    label: string;
    value: string;
}

export default function CustomFields() {
    const { getValues } = useFormContext();
    const customFields: CustomField[] = getValues('customFields') || [];

    return (
        <AdminSection
            title={__('Custom form fields', 'give')}
            description={__('Manage the custom fields filled by the donor', 'give')}
        >
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
