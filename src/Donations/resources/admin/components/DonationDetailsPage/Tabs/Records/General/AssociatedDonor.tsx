import { __ } from '@wordpress/i18n';
import AdminSection from '@givewp/components/AdminDetailsPage/AdminSection';

/**
 * @unreleased
 */
export default function AssociatedDonor() {
    return (
        <AdminSection
            title={__('Associated donor', 'give')}
            description={__('Manage the donor connected to this donation', 'give')}
        >
            <div>
                <div>
                    <label>{__('Donor', 'give')}</label>
                    <p style={{ fontSize: '0.875rem', color: '#646970', margin: '0.25rem 0 0.5rem 0' }}>
                        {__('Link the donation to the selected donor', 'give')}
                    </p>
                    <select disabled style={{ width: '100%', padding: '0.5rem' }}>
                        <option>John Doe (johndoe@example.com)</option>
                    </select>
                </div>
            </div>
        </AdminSection>
    );
}
