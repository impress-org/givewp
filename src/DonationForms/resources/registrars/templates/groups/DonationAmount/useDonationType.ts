import {useEffect} from '@wordpress/element';
import {isSubscriptionPeriod} from './subscriptionPeriod';

/**
 * @since 0.3.0
 */
export default function useDonationType() {
    const {useWatch, useFormContext} = window.givewp.form.hooks;
    const {setValue} = useFormContext();
    const subscriptionPeriod = useWatch({name: 'subscriptionPeriod'});

    useEffect(() => {
        if (!subscriptionPeriod) {
            return;
        }

        if (isSubscriptionPeriod(subscriptionPeriod)) {
            setValue('donationType', 'subscription');
        } else {
            setValue('donationType', 'single');
        }
    }, [subscriptionPeriod]);
};