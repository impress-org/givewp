import {useState} from 'react';
import {__} from '@wordpress/i18n';

import SectionHeader, {DropdownTitle, HeaderLink} from '@givewp/components/AdminUI/SectionHeader';
import {DisabledTextField, SelectDropdownField} from '@givewp/components/AdminUI/FormElements';
import {FieldsetContainer} from '@givewp/components/AdminUI/ContainerLayout';

import {StyleConfig} from './StyleConfig';

/**
 *
 * @unreleased
 */

const {firstName, email} = window.GiveDonations.donationDetails;

export default function DonorDetails() {
    const [dropdown, setDropdown] = useState(true);

    const handleDropdown = () => setDropdown(!dropdown);

    return (
        <section>
            <SectionHeader>
                <DropdownTitle isOpen={dropdown} title={__('Donor details', 'give')} handleDropdown={handleDropdown} />
                <HeaderLink href={'/'}>{__('View donor details', 'give')}</HeaderLink>
            </SectionHeader>
            {dropdown && <SectionContainer />}
        </section>
    );
}

/**
 *
 * @unreleased
 */

export function SectionContainer() {
    return (
        <FieldsetContainer dropdown>
            <SelectDropdownField
                name={'donorId'}
                label={__('Change Donor', 'give')}
                isSearchable={false}
                isClearable={false}
                placeholder={__('Please select an option', 'give')}
                options={[{value: 1, label: 'test'}]}
                styleConfig={StyleConfig}
            />
            <DisabledTextField
                value={firstName}
                name={'firstName'}
                label={__('Name', 'give')}
                type={'text'}
                placeholder={__('Name', 'give')}
            />
            <DisabledTextField
                value={email}
                name={'emailAddress'}
                label={__('Email', 'give')}
                type={'text'}
                placeholder={__('Email address', 'give')}
            />
        </FieldsetContainer>
    );
}
