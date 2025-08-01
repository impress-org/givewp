/**
 * External Dependencies
 */
import { __ } from '@wordpress/i18n';
import { useState } from 'react';
import {useFormContext} from 'react-hook-form';

/**
 * Internal Dependencies
 */
import AdminSection, { AdminSectionField } from '@givewp/components/AdminDetailsPage/AdminSection';
import { getDonorOptionsWindowData } from '@givewp/donors/utils';
import Upload from '../../../Inputs/Upload';
import PhoneInput from '../../../Inputs/Phone';
import styles from '../../DonorDetailsPage.module.scss';

const {nameTitlePrefixes, intlTelInputSettings} = getDonorOptionsWindowData();

/**
 * @since 4.4.0
 */
export default function DonorPersonalDetails() {
    const {
        register,
        watch,
        setValue,
        setError,
        trigger,
        clearErrors,
        formState: {errors},
    } = useFormContext();

    const [avatarUrl, setAvatarUrl] = useState<string>(watch('avatarUrl'));

    const handlePhoneChange = (value: string) => {
        setValue('phone', value, {shouldDirty: true});
        trigger('phone', {shouldFocus: false});
    };

    const handlePhoneError = (errorMessage: string | null) => {
        if (errorMessage) {
            setError('phone', {type: 'custom', message: errorMessage});
        } else {
            clearErrors('phone');
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
                    value={avatarUrl}
                    onChange={(avatarId, avatarUrl) => {
                        setValue('avatarId', avatarId, {shouldDirty: true});
                        setAvatarUrl(avatarUrl);
                    }}
                    reset={() => {
                        setValue('avatarId', null, {shouldDirty: true});
                        setAvatarUrl('');
                    }}
                />
            </AdminSectionField>

            <AdminSectionField
                subtitle={__('Name', 'give')}
                error={errors.title ? `${errors.title.message}` : undefined}
            >
                <div className={styles.sectionFieldInputWrapper}>
                    <select {...register('prefix')} className={styles.prefixSelect} aria-label={__('Title prefix', 'give')}>
                        <option value=""></option>
                        {nameTitlePrefixes.map((prefix) => (
                            <option key={prefix} value={prefix}>{prefix}</option>
                        ))}
                    </select>
                    <input {...register('firstName')} aria-label={__('First name', 'give')} />
                    <input {...register('lastName')} aria-label={__('Last name', 'give')} />
                </div>
            </AdminSectionField>

            <AdminSectionField
                subtitle={__('Phone', 'give')}
                error={errors.phone ? `${errors.phone.message}` : undefined}
            >
                <PhoneInput
                    value={watch('phone')}
                    onChange={handlePhoneChange}
                    onError={handlePhoneError}
                    intlTelInputSettings={intlTelInputSettings}
                />
            </AdminSectionField>

            <AdminSectionField subtitle={__('Company name', 'give')}>
                <input {...register('company')} aria-label={__('Company name', 'give')} />
            </AdminSectionField>
        </AdminSection>
    );
}
