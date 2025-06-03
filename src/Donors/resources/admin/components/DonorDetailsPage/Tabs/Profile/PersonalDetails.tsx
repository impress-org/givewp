import { __ } from '@wordpress/i18n';
import { useState } from 'react';
import {useFormContext} from 'react-hook-form';
import IntlTelInput from 'intl-tel-input/react';

import styles from '../../DonorDetailsPage.module.scss';
import sharedStyles from '@givewp/components/AdminDetailsPage/AdminDetailsPage.module.scss';
import 'intl-tel-input/build/css/intlTelInput.css'
import { getDonorOptionsWindowData } from '@givewp/donors/utils';

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
        <div className={sharedStyles.section}>
            <div className={sharedStyles.leftColumn}>
                <div className={sharedStyles.sectionTitle}>{__('Personal Details', 'give')}</div>
                <div className={sharedStyles.sectionDescription}>
                    {__( 'This includes profile photo, name, phone, etc.', 'give',)}
                </div>
            </div>

            <div className={sharedStyles.rightColumn}>
                <div className={sharedStyles.sectionField}>
                    <div className={sharedStyles.sectionSubtitle}>{__('Name', 'give')}</div>
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

                    {errors.title && <div className={sharedStyles.errorMsg}>{`${errors.title.message}`}</div>}
                </div>
                <div className={sharedStyles.sectionField}>
                    <div className={sharedStyles.sectionSubtitle}>{__('Phone', 'give')}</div>

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

                    {errors.phone && <div className={sharedStyles.errorMsg}>{`${errors.phone.message}`}</div>}
                </div>
                <div className={sharedStyles.sectionField}>
                    <div className={sharedStyles.sectionSubtitle}>{__('Company name', 'give')}</div>
                    <input {...register('companyName')} />
                </div>
            </div>
        </div>
    );
}
