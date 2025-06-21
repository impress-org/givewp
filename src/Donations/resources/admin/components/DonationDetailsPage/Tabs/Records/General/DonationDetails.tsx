import { __ } from '@wordpress/i18n';
import AdminSection from '@givewp/components/AdminDetailsPage/AdminSection';

/**
 * @unreleased
 */
export default function DonationDetails() {
    return (
        <AdminSection
            title={__('Donation details', 'give')}
            description={__('This includes the donation information', 'give')}
        >
            <div>
                {/* Amount and Status fields */}
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '1rem', marginBottom: '1rem' }}>
                    <div>
                        <label>{__('Amount', 'give')}</label>
                        <input type="text" value="$300" disabled style={{ width: '100%', padding: '0.5rem' }} />
                    </div>
                    <div>
                        <label>{__('Status', 'give')}</label>
                        <select disabled style={{ width: '100%', padding: '0.5rem' }}>
                            <option>{__('Completed', 'give')}</option>
                        </select>
                    </div>
                </div>

                {/* Donation date and time */}
                <div style={{ marginBottom: '1rem' }}>
                    <label>{__('Donation date and time', 'give')}</label>
                    <input type="text" value="7th Mar, 2025, 10:00 AM" disabled style={{ width: '100%', padding: '0.5rem' }} />
                </div>

                {/* Campaign and Form fields */}
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '1rem', marginBottom: '1rem' }}>
                    <div>
                        <label>{__('Campaign', 'give')}</label>
                        <select disabled style={{ width: '100%', padding: '0.5rem' }}>
                            <option>{__('WordCamp Fundraising', 'give')}</option>
                        </select>
                    </div>
                    <div>
                        <label>{__('Form', 'give')}</label>
                        <select disabled style={{ width: '100%', padding: '0.5rem' }}>
                            <option>{__('Fundraising Form', 'give')}</option>
                        </select>
                    </div>
                </div>

                {/* Fund */}
                <div style={{ marginBottom: '1rem' }}>
                    <label>{__('Fund', 'give')}</label>
                    <select disabled style={{ width: '100%', padding: '0.5rem' }}>
                        <option>{__('General', 'give')}</option>
                    </select>
                </div>

                {/* Donor comment */}
                <div style={{ marginBottom: '1rem' }}>
                    <label>{__('Donor comment', 'give')}</label>
                    <textarea
                        placeholder={__('Add a comment', 'give')}
                        disabled
                        style={{ width: '100%', padding: '0.5rem', minHeight: '80px' }}
                    />
                </div>

                {/* Anonymous donation */}
                <div>
                    <label>{__('Anonymous donation', 'give')}</label>
                    <div style={{ display: 'flex', gap: '1rem', marginTop: '0.5rem' }}>
                        <label style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
                            <input type="radio" name="anonymous" value="yes" defaultChecked disabled />
                            {__('Yes, please', 'give')}
                        </label>
                        <label style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
                            <input type="radio" name="anonymous" value="no" disabled />
                            {__('No, thank you', 'give')}
                        </label>
                    </div>
                </div>
            </div>
        </AdminSection>
    );
}
