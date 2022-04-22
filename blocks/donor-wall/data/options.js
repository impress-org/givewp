/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Options data for various form selects
 */
const giveDonorWallOptions = {};

// Form Display Styles
giveDonorWallOptions.columns = [
	{ value: 'best-fit', label: __( 'Best Fit', 'give' ) },
	{ value: '1', label: '1' },
	{ value: '2', label: '2' },
	{ value: '3', label: '3' },
	{ value: '4', label: '4' },
];

// Order
giveDonorWallOptions.order = [
	{ value: 'DESC', label: __( 'Descending', 'give' ) },
	{ value: 'ASC', label: __( 'Ascending', 'give' ) },
];

// Order
giveDonorWallOptions.orderBy = [
	{ value: 'donation_amount', label: __( 'Donation Amount', 'give' ) },
	{ value: 'post_date', label: __( 'Date Created', 'give' ) },
];

export default giveDonorWallOptions;
