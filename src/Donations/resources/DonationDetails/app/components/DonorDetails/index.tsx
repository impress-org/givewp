import {useState} from 'react';
import {__} from '@wordpress/i18n';
import {useWatch} from 'react-hook-form';

import SectionHeader, {DropdownTitle, HeaderLink} from '@givewp/components/AdminUI/SectionHeader';
import {AsyncSelectDropdownField, DisabledTextField} from '@givewp/components/AdminUI/FormElements';
import {FieldsetContainer} from '@givewp/components/AdminUI/ContainerLayout';
import {useGetRequest} from '@givewp/components/AdminUI/api';

import {StyleConfig} from './StyleConfig';
import {apiNonce, apiRoot} from '../../../../window';

/**
 *
 * @unreleased
 */
const endpoint = `${apiRoot.split('/donation')[0]}/donors`;
const {donorId, firstName, lastName, email} = window.GiveDonations.donationDetails;
const cachedDonors = {
    [donorId]: {
        value: donorId,
        label: `${firstName} ${lastName} (${email})`,
        model: {
            firstName,
            lastName,
            email,
        },
    },
};

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

interface DonorOption {
    readonly value: string;
    readonly label: string;
    readonly model: [];
}

/**
 *
 * @unreleased
 */
export function SectionContainer() {
    const {getData} = useGetRequest(endpoint, apiNonce, '', '');
    const watchedDonorId = useWatch({name: 'donorId'});
    const currentDonor = cachedDonors[watchedDonorId].model;

    const getDonors = async (inputValue: string): Promise<DonorOption[]> => {
        try {
            const response = await getData(`search=${inputValue}&return=model`);

            if (response.items) {
                return response.items.map((item) => {
                    const donor = {
                        value: item.id,
                        label: `${item.firstName} ${item.lastName} (${item.email})`,
                        model: item,
                    };
                    cachedDonors[item.id] = donor;

                    return donor;
                });
            } else {
                return [];
            }
        } catch (error) {
            return [];
        }
    };

    return (
        <FieldsetContainer dropdown>
            <AsyncSelectDropdownField
                name={'donorId'}
                label={__('Change Donor', 'give')}
                isSearchable={true}
                isClearable={false}
                placeholder={__('Please select an option', 'give')}
                defaultOptions={Object.values(cachedDonors)}
                loadOptions={getDonors}
                styleConfig={StyleConfig}
            />
            <DisabledTextField
                value={`${currentDonor.firstName} ${currentDonor.lastName}`}
                name={'name'}
                label={__('Name', 'give')}
                type={'text'}
                placeholder={__('Name', 'give')}
            />
            <DisabledTextField
                value={currentDonor.email}
                name={'emailAddress'}
                label={__('Email', 'give')}
                type={'text'}
                placeholder={__('Email address', 'give')}
            />
        </FieldsetContainer>
    );
}
