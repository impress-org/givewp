import { __ } from "@wordpress/i18n";
import AdminSection, { AdminSectionField } from '@givewp/components/AdminDetailsPage/AdminSection';

export default function DonorAddress() {
    return (
        <AdminSection
            title={__('Address', 'give')}
            description={__('Manage the address of the donor', 'give')}
        >
            <AdminSectionField>
                Address
            </AdminSectionField>
        </AdminSection>
    );
}
