import {createHigherOrderComponent} from '@wordpress/compose';
import {useEffect} from '@wordpress/element';
import {addFilter} from '@wordpress/hooks';
import StripeAccountPanel from './StripeAccountPanel';

function addAttribute(settings, name) {
    if (name === 'givewp/payment-gateways') {
        settings.attributes = {
            ...settings.attributes,
            stripeUseGlobalDefault: {
                type: 'boolean',
                default: true,
            },
            stripeAccountId: {
                type: 'string',
                default: '',
            },
        };
    }

    return settings;
}

addFilter(
    'blocks.registerBlockType',
    'givewp/stripe-payment-element',
    addAttribute
);

const withInspectorControls = createHigherOrderComponent(( BlockEdit ) => {
    return ( props ) => {
        useEffect(() => {
            if (!props.attributes.hasOwnProperty('stripeUseGlobalDefault')) {
                props.setAttributes({
                    stripeUseGlobalDefault: true,
                    stripeAccountId: '',
                });
            }
        }, []);

        if ( props.name === 'givewp/payment-gateways' ) {
            return (
                <>
                    <BlockEdit { ...props } />
                    <StripeAccountPanel {...props} />
                </>
            );
        }
        return <BlockEdit { ...props } />;
    };
}, 'withInspectorControl');

addFilter(
    'editor.BlockEdit',
    'givewp/stripe-payment-element',
    withInspectorControls
);
