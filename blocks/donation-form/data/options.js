/**
 * WordPress dependencies
*/
const { __ } = wp.i18n;

/**
 * Options data for various form selects
 */
const giveFormOptions = {};

// Form Display Styles
giveFormOptions.displayStyles = [
	{ value: 'onpage', label: __( 'Full Form' ) },
	{ value: 'modal', label: __( 'Modal' ) },
	{ value: 'reveal', label: __( 'Reveal' ) },
	{ value: 'button', label: __( 'One Button Launch' ) },
];

// Form content Position
giveFormOptions.contentPosition = [
	{ value: 'above', label: __( 'Above' ) },
	{ value: 'below', label: __( 'Below' ) },
];

export default giveFormOptions;
