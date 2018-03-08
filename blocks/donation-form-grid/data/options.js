/**
 * Internal dependencies
 */
const {__} = wp.i18n;

/**
 * Options data for various form selects
 */
const giveFormOptions = {};

// Form Display Styles
giveFormOptions.columns = [
	{value: '1', label: '1'},
	{value: '2', label: '2'},
	{value: '3', label: '3'},
	{value: '4', label: '4'},
];

// Form Display Styles
giveFormOptions.displayType = [
	{value: 'redirect', label: __( 'Redirect' ) },
	{value: 'modal', label: __( 'Modal' ) },
];

export default giveFormOptions;
