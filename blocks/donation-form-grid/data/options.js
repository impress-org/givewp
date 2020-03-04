/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

/**
 * Options data for various form selects
 */
const giveFormOptions = {};

// Form Order By
giveFormOptions.orderBy = [
	{ value: 'date', label: __( 'Date Created' ) },
	{ value: 'title', label: __( 'Form Name' ) },
	{ value: 'amount_donated', label: __( 'Amount Donated' ) },
	{ value: 'number_donations', label: __( 'Number of Donations' ) },
	{ value: 'menu_order', label: __( 'Menu Order' ) },
	{ value: 'post__in', label: __( 'Provided Form IDs' ) },
	{ value: 'closest_to_goal', label: __( 'Closest To Goal' ) },
];

// Form Order
giveFormOptions.order = [
	{ value: 'DESC', label: __( 'Descending' ) },
	{ value: 'ASC', label: __( 'Ascending' ) },
];

// Form Display Styles
giveFormOptions.columns = [
	{ value: 'best-fit', label: __( 'Best Fit' ) },
	{ value: '1', label: '1' },
	{ value: '2', label: '2' },
	{ value: '3', label: '3' },
	{ value: '4', label: '4' },
];

// Form Display Styles
giveFormOptions.displayType = [
	{ value: 'redirect', label: __( 'Redirect' ) },
	{ value: 'modal_reveal', label: __( 'Modal' ) },
];

export default giveFormOptions;
