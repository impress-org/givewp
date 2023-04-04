import {useState} from 'react';

import {__} from '@wordpress/i18n';

import SectionHeader, {DropdownTitle, HeaderLink} from '@givewp/components/AdminUI/SectionHeader';
import {SelectDropdownField, TextInputField} from '@givewp/components/AdminUI/FormElements';
import {useFormContext} from 'react-hook-form';
import {FieldsetContainer} from '@givewp/components/AdminUI/ContainerLayout';

import {StyleConfig} from './StyleConfig';
import styles from './style.module.scss';

/**
 *
 * @unreleased
 */

export default function BillingAddress() {
    const [dropdown, setDropdown] = useState(true);

    const handleDropdown = () => setDropdown(!dropdown);

    return (
        <section>
            <SectionHeader>
                <DropdownTitle
                    isOpen={dropdown}
                    title={__('Billing address details', 'give')}
                    handleDropdown={handleDropdown}
                />
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
    const {register} = useFormContext();

    return (
        <FieldsetContainer dropdown>
            <SelectDropdownField
                name={'country'}
                label={__('Country', 'give')}
                isSearchable={false}
                isClearable={false}
                placeholder={__('Please select an option', 'give')}
                options={[{value: 'UnitedStates', label: 'US'}]}
                styleConfig={StyleConfig}
            />
            <TextInputField
                {...register('primaryAddress')}
                name={'address1'}
                label={__('Address 1', 'give')}
                type={'text'}
                placeholder={__('Address 1', 'give')}
            />
            <TextInputField
                {...register('secondaryAddress')}
                name={'address2'}
                label={__('Address 2', 'give')}
                type={'text'}
                placeholder={__('Address 2', 'give')}
            />
            <div className={styles.cityStateProvinceCounty}>
                <TextInputField
                    {...register('city')}
                    name={'city'}
                    label={__('City', 'give')}
                    type={'text'}
                    placeholder={__('City', 'give')}
                />
                <TextInputField
                    {...register('stateProvinceCounty')}
                    name={'state'}
                    label={__('State/Province/County', 'give')}
                    type={'text'}
                    placeholder={__('State/Province/County', 'give')}
                />
            </div>
            <TextInputField
                {...register('zip')}
                name={'zip'}
                label={'Zip/Postal code'}
                type={'text'}
                placeholder={__('Zip/Postal code', 'give')}
            />
        </FieldsetContainer>
    );
}
