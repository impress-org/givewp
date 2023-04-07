import {useEffect} from "@wordpress/element";

/**
 * @since 0.3.0
 */
export default function useDonationType(){
    const {useWatch, useFormContext} = window.givewp.form.hooks;
    const {setValue} = useFormContext();
    const subscriptionPeriod = useWatch({name: 'subscriptionPeriod'});

    useEffect(() => {
        if (subscriptionPeriod) {
            if (subscriptionPeriod === 'one-time') {
                setValue('donationType', 'single');
            } else {
                setValue('donationType', 'subscription');
            }
        }
    }, [subscriptionPeriod]);
};