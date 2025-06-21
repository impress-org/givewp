import { __ } from '@wordpress/i18n';
import AdminSection from '@givewp/components/AdminDetailsPage/AdminSection';

/**
 * @unreleased
 */
export default function BillingDetails() {
    return (
        <AdminSection
            title={__('Billing details', 'give')}
            description={__('This includes the billing name, email and address', 'give')}
        >
            <div>
                {/* First name and Last name */}
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '1rem', marginBottom: '1rem' }}>
                    <div>
                        <label>{__('First name', 'give')}</label>
                        <input type="text" value="Ana" disabled style={{ width: '100%', padding: '0.5rem' }} />
                    </div>
                    <div>
                        <label>{__('Last name', 'give')}</label>
                        <input type="text" value="Doe" disabled style={{ width: '100%', padding: '0.5rem' }} />
                    </div>
                </div>

                {/* Email */}
                <div style={{ marginBottom: '1rem' }}>
                    <label>{__('Email', 'give')}</label>
                    <input type="email" value="johndoe@example.com" disabled style={{ width: '100%', padding: '0.5rem' }} />
                </div>

                {/* Country */}
                <div style={{ marginBottom: '1rem' }}>
                    <label>{__('Country', 'give')}</label>
                    <select disabled style={{ width: '100%', padding: '0.5rem' }}>
                        <option>{__('United States', 'give')}</option>
                    </select>
                </div>

                {/* Address 1 */}
                <div style={{ marginBottom: '1rem' }}>
                    <label>{__('Address 1', 'give')}</label>
                    <input type="text" disabled style={{ width: '100%', padding: '0.5rem' }} />
                </div>

                {/* Address 2 */}
                <div style={{ marginBottom: '1rem' }}>
                    <label>{__('Address 2', 'give')}</label>
                    <input type="text" disabled style={{ width: '100%', padding: '0.5rem' }} />
                </div>

                {/* City */}
                <div style={{ marginBottom: '1rem' }}>
                    <label>{__('City', 'give')}</label>
                    <input type="text" disabled style={{ width: '100%', padding: '0.5rem' }} />
                </div>

                {/* State and Zip */}
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '1rem' }}>
                    <div>
                        <label>{__('State / Province / County', 'give')}</label>
                        <select disabled style={{ width: '100%', padding: '0.5rem' }}>
                            <option>{__('Select a state', 'give')}</option>
                        </select>
                    </div>
                    <div>
                        <label>{__('Zip / Postal Code', 'give')}</label>
                        <input type="text" disabled style={{ width: '100%', padding: '0.5rem' }} />
                    </div>
                </div>
            </div>
        </AdminSection>
    );
}
