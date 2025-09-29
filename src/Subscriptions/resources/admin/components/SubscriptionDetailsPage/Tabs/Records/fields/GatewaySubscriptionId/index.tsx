import {AdminSectionField} from '@givewp/components/AdminDetailsPage/AdminSection';
import {__} from '@wordpress/i18n';
import {useFormContext, useFormState} from 'react-hook-form';

/**
 * @unreleased
 */
export default function GatewaySubscriptionId() {
    const {register, watch, getValues} = useFormContext();
    const {errors} = useFormState();

    return (
        <AdminSectionField error={errors.gatewaySubscriptionId?.message as string}>
            <label htmlFor="gatewaySubscriptionId">{__('Gateway Subscription ID', 'give')}</label>
            <input
                id="gatewaySubscriptionId"
                type="text"
                className="givewp-admin-field-input"
                {...register('gatewaySubscriptionId')}
                placeholder={__('Enter gateway subscription ID', 'give')}
            />
        </AdminSectionField>
    );
}
