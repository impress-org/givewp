import { __ } from "@wordpress/i18n";
import AdminSection, { AdminSectionField } from '@givewp/components/AdminDetailsPage/AdminSection';

export default function DonorEmailAddress() {
    return (
        <AdminSection
            title={__('Email Address', 'give')}
            description={__('Manage the email address of the donor', 'give')}
        >
            <AdminSectionField>
                Email Address
            </AdminSectionField>
        </AdminSection>
    );
}
