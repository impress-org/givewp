/**
 * External Dependencies
 */
import { __ } from '@wordpress/i18n';
import { useState } from 'react';
import {useFormContext} from 'react-hook-form';
import IntlTelInput from 'intl-tel-input/react';
import 'intl-tel-input/build/css/intlTelInput.css'

/**
 * Internal Dependencies
 */
import AdminSection, { AdminSectionField } from '@givewp/components/AdminDetailsPage/AdminSection';
import { getDonorOptionsWindowData } from '@givewp/donors/utils';
import Upload from '../../../Inputs/Upload';
import styles from '../../DonorDetailsPage.module.scss';

const {intlTelInputSettings, nameTitlePrefixes} = getDonorOptionsWindowData();

export default function DonorPersonalDetails() {
    const {
        register,
        watch,
        setValue,
        setError,
        trigger,
        formState: {errors},
    } = useFormContext();

    const [country, setCountry] = useState<string>(intlTelInputSettings.initialCountry);
    const onChangeNumber = (number: string) => {
        if (number && !window.intlTelInputUtils.isValidNumber(number, country)) {
            const errorCode = window.intlTelInputUtils.getValidationError(number, country);
            setValue('phone', errorCode);
            setError('phone', {type: 'custom', message: intlTelInputSettings.errorMap[errorCode]});
        } else {
            setValue('phone', number);
            trigger('phone', {shouldFocus: false});
        }
    };

    return (
        <AdminSection
            title={__('Personal Details', 'give')}
            description={__('This includes profile photo, name, phone, etc.', 'give')}
        >
            <AdminSectionField
                error={errors.avatar ? `${errors.avatar.message}` : undefined}
            >
                <Upload
                    id="givewp-donor-upload-avatar"
                    label={__('Photo', 'give')}
                    value={watch('avatar')}
                    onChange={(avatarUrl, avatarAlt) => {
                        setValue('avatar', avatarUrl, {shouldDirty: true});
                    }}
                    reset={() => setValue('avatar', '', {shouldDirty: true})}
                />
            </AdminSectionField>

            <AdminSectionField
                subtitle={__('Name', 'give')}
                error={errors.title ? `${errors.title.message}` : undefined}
            >
                <div className={styles.sectionFieldInputWrapper}>
                    <select {...register('prefix')} className={styles.prefixSelect}>
                        <option value=""></option>
                        {nameTitlePrefixes.map((prefix) => (
                            <option key={prefix} value={prefix}>{prefix}</option>
                        ))}
                    </select>
                    <input {...register('firstName')} />
                    <input {...register('lastName')} />
                </div>
            </AdminSectionField>

            <AdminSectionField
                subtitle={__('Phone', 'give')}
                error={errors.phone ? `${errors.phone.message}` : undefined}
            >
                <div className={styles.phoneInput}>
                    <IntlTelInput
                        initialValue={watch('phone')}
                        onChangeCountry={setCountry}
                        onChangeNumber={onChangeNumber}
                        initOptions={{
                            initialCountry: intlTelInputSettings.initialCountry,
                            showSelectedDialCode: intlTelInputSettings.showSelectedDialCode,
                            strictMode: intlTelInputSettings.strictMode,
                            i18n: intlTelInputSettings.i18n,
                            useFullscreenPopup: intlTelInputSettings.useFullscreenPopup,
                        }}
                    />
                </div>
            </AdminSectionField>

            <AdminSectionField subtitle={__('Company name', 'give')}>
                <input {...register('companyName')} />
            </AdminSectionField>
        </AdminSection>
    );
}
