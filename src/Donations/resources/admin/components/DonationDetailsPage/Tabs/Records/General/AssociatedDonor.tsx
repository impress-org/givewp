import { __ } from '@wordpress/i18n';
import AdminSection, { AdminSectionField } from '@givewp/components/AdminDetailsPage/AdminSection';
import apiFetch from '@wordpress/api-fetch';
import { useEntityRecord } from '@wordpress/core-data';
import { useFormContext } from 'react-hook-form';
import { Donor } from '@givewp/donors/admin/components/types';
import { useCallback, useEffect, useState } from 'react';
import styles from '../styles.module.scss';
import { getDonationOptionsWindowData } from '@givewp/donations/utils';
import { AsyncPaginate } from 'react-select-async-paginate';
import { GroupBase, OptionsOrGroups } from 'react-select';

type OptionType = {
    value: number;
    label: string;
  };

const DONORS_PER_PAGE = 5;

/**
 * @unreleased updated to async donor dropdown
 * @since 4.6.0
 */
export default function AssociatedDonor() {
    const { watch, setValue } = useFormContext();
    const { mode } = getDonationOptionsWindowData();
    const donationDonorId = watch('donorId');
    const [page, setPage] = useState(0);
    const [selectedOption, setSelectedOption] = useState(null);
    const [isResolving, setIsResolving] = useState(true);

    const {
        record: currentDonor,
        hasResolved: hasResolvedDonor,
        isResolving: isResolvingDonor,
    } = useEntityRecord<Donor>('givewp', 'donor', donationDonorId);

    useEffect(() => {
        if (hasResolvedDonor && currentDonor && donationDonorId) {
            setSelectedOption({
                value: currentDonor.id,
                label: `${currentDonor.name} (${currentDonor.email})`,
            });
        } else if (!donationDonorId) {
            setSelectedOption(null);
        }
    }, [currentDonor, hasResolvedDonor, donationDonorId]);

    const loadOptions = async (searchInput: string) => {
        const currentPage = searchInput !== '' ? 1 : page + 1;

        if (searchInput !== '') {
            setPage(1);
        }

        try {
            const queryParams = new URLSearchParams({
                mode,
                per_page: DONORS_PER_PAGE.toString(),
                page: currentPage.toString(),
                sort: 'name',
                direction: 'ASC',
                includeSensitiveData: 'true',
                anonymousDonors: 'include',
                ...(searchInput && { search: searchInput }),
            });

            const donors = await apiFetch<Donor[]>({
                path: `/givewp/v3/donors?${queryParams.toString()}`,
            });

            const newOptions = (donors || []).map(donor => ({
                value: donor.id,
                label: `${donor.name} (${donor.email})`,
            }));

            // If this is a new search, reset page counter
            if (searchInput !== '') {
                setPage(1);
            } else if (!searchInput) {
                setPage(currentPage);
            }

            const hasMoreResults = (donors?.length || 0) >= DONORS_PER_PAGE;

            if (isResolving) {
                setIsResolving(false);
            }

            return {
                options: newOptions,
                hasMore: hasMoreResults,
            };
        } catch (error) {
            console.error('Error loading donors:', error);
            return {
                options: [],
                hasMore: false,
            };
        }
    };

    const mapOptionsForMenu = useCallback(
        (options: OptionType[]) => {
            const filteredOptions = options.filter((option, index, self) =>
                index === self.findIndex((t) => t.value === option.value)
            )
            .sort((a, b) => a.label.localeCompare(b.label));

          if (!selectedOption) {
            return filteredOptions;
          }

          return [
            selectedOption,
            ...filteredOptions.filter(option => option.value !== selectedOption.value)
          ];
        },
        [selectedOption]
      );

    return (
        <AdminSection
            title={__('Associated donor', 'give')}
            description={__('Manage the donor connected to this donation', 'give')}
        >
            <AdminSectionField>
                <label htmlFor="donorId">{__('Donor', 'give')}</label>
                <p className={styles.fieldDescription}>{__('Link the donation to the selected donor', 'give')}</p>
                <AsyncPaginate
                    value={selectedOption}
                    loadOptions={loadOptions}
                    mapOptionsForMenu={mapOptionsForMenu}
                    onChange={(selectedOption: any) => {
                        setValue('donorId', selectedOption?.value);
                    }}
                    debounceTimeout={600}
                    placeholder={__('Loading donors...', 'give')}
                    loadingMessage={() => __('Loading donors...', 'give')}
                    noOptionsMessage={() => __('No donors found.', 'give')}
                />
            </AdminSectionField>
        </AdminSection>
    );
}
