/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Options data for various form selects
 */
const giveFormOptions = {};

// Form Order By
giveFormOptions.orderBy = [
	{ value: 'date', label: __( 'Date Created', 'give' ) },
	{ value: 'title', label: __( 'Form Name', 'give' ) },
	{ value: 'amount_donated', label: __( 'Amount Donated', 'give' ) },
	{ value: 'number_donations', label: __( 'Number of Donations', 'give' ) },
	{ value: 'menu_order', label: __( 'Menu Order', 'give' ) },
	{ value: 'post__in', label: __( 'Provided Form IDs', 'give' ) },
	{ value: 'closest_to_goal', label: __( 'Closest To Goal', 'give' ) },
];

// Form Order
giveFormOptions.order = [
	{ value: 'DESC', label: __( 'Descending', 'give' ) },
	{ value: 'ASC', label: __( 'Ascending', 'give' ) },
];

// Form Display Styles
giveFormOptions.columns = [
	{ value: 'best-fit', label: __( 'Best Fit', 'give' ) },
	{ value: '1', label: '1' },
	{ value: '2', label: '2' },
	{ value: '3', label: '3' },
	{ value: '4', label: '4' },
];

// Form Display Styles
giveFormOptions.displayType = [
	{ value: 'redirect', label: __( 'Redirect', 'give' ) },
	{ value: 'modal_reveal', label: __( 'Modal', 'give' ) },
];

export default giveFormOptions;
