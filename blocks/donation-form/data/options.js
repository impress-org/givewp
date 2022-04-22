/* globals Give */
/**
 * WordPress dependencies
*/
import { __ } from '@wordpress/i18n'

/**
 * Options data for various form selects
 */
const giveFormOptions = {};

// Form Display Styles
giveFormOptions.displayStyles = [
	{ value: 'onpage', label: __( 'Full Form', 'give' ) },
	{ value: 'modal', label: __( 'Modal', 'give' ) },
	{ value: 'reveal', label: __( 'Reveal', 'give' ) },
	{ value: 'button', label: __( 'One Button Launch', 'give' ) },
];

// Form content Position
giveFormOptions.contentPosition = [
	{ value: 'above', label: __( 'Above', 'give' ) },
	{ value: 'below', label: __( 'Below', 'give' ) },
];

export default giveFormOptions;
