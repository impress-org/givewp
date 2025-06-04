import { __ } from "@wordpress/i18n";
import AdminSection, { AdminSectionField } from '@givewp/components/AdminDetailsPage/AdminSection';

export default function DonorCustomFields() {
    return (
        <AdminSection
            title={__('Custom Fields', 'give')}
            description={__('Manage the custom fields filled by the donor', 'give')}
        >
            <AdminSectionField>
                Custom Fields
            </AdminSectionField>
        </AdminSection>
    );
}
