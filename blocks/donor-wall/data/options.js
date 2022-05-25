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
	{ value: '1', label: 'Full width' },
	{ value: '2', label: 'Double' },
	{ value: '3', label: 'Max (3)' },
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

//Toggle Switch
giveDonorWallOptions.toggleOptions = [
    { value: 'Donor info', label: __( 'Donor Info', 'give' ) },
    { value: 'Wall attributes', label: __( 'Wall Attributes', 'give' ) },
];

//Filter
giveDonorWallOptions.filter = [
    { value: 'Donor ID', label: __( 'Donor ID', 'give' ) },
    { value: 'Form ID', label: __( 'Form ID', 'give' ) },
    { value: 'Categories', label: __( 'Categories', 'give' ) },
    { value: 'Tags', label: __( 'Tags', 'give' ) },
    { value: 'Donors with comments', label: __( 'Donors with comments', 'give' ) },
];



export default giveDonorWallOptions;
