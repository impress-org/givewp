import {useState} from 'react';
import {__} from '@wordpress/i18n';
import {useWatch} from 'react-hook-form';

import SectionHeader, {DropdownTitle, HeaderLink} from '@givewp/components/AdminUI/SectionHeader';
import {AsyncSelectDropdownField, DisabledTextField} from '@givewp/components/AdminUI/FormElements';
import {FieldsetContainer} from '@givewp/components/AdminUI/ContainerLayout';
import {useGetRequest} from '@givewp/components/AdminUI/api';
import useDebounce from '@givewp/components/ListTable/hooks/useDebounce';

import {StyleConfig} from './StyleConfig';
import {apiNonce, apiRoot} from '../../../../window';

/**
 *
 * @unreleased
 */
const endpoint = `${apiRoot.split('/donation')[0]}/donors`;
const {donorId, firstName, lastName, email} = window.GiveDonations.donationDetails;

export default function DonorDetails() {
    const [dropdown, setDropdown] = useState<boolean>(true);

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
    const [cachedDonors, setCachedDonors] = useState<CachedDonors>({
        [donorId]: {
            value: donorId,
            label: `${firstName} ${lastName} (${email})`,
            model: {
                firstName,
                lastName,
                email,
            },
        },
    });

    const {getData} = useGetRequest(endpoint, apiNonce);
    const watchedDonorId = useWatch({name: 'donorId'});
    const currentDonor = cachedDonors[watchedDonorId].model;

    const loadOptions = (inputValue: string, callback: (options: DonorOption[]) => void) => {
        try {
            const params = {
                search: inputValue,
                sortColumn: 'donorInformation',
                sortDirection: 'asc',
                perPage: 10,
                return: 'model',
            };
            const queryString = Object.entries(params)
                .map(([key, value]) => `${key}=${value}`)
                .join('&');

            getData(queryString).then((response) => {
                if (response.items) {
                    const donors = response.items.map((item) => {
                        const donor: DonorOption = {
                            value: item.id,
                            label: `${item.firstName} ${item.lastName} (${item.email})`,
                            model: item,
                        };

                        setCachedDonors((cachedDonors) => ({
                            ...cachedDonors,
                            [item.id]: donor,
                        }));

                        return donor;
                    });

                    callback(donors);
                } else {
                    callback([]);
                }
            });
        } catch (error) {
            callback([]);
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
                defaultOptions={Object.values(cachedDonors).sort((a, b) => (a.label > b.label ? 1 : -1))}
                loadOptions={useDebounce(loadOptions)}
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

type DonorOption = {
    value: number;
    label: string;
    model: {
        firstName: string;
        lastName: string;
        email: string;
        [key: string]: any;
    };
};

type CachedDonors = {
    [key: number]: DonorOption;
};
