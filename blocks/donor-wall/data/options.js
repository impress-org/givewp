import giveFormOptions from "../../donation-form/data/options";

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

/**
 * Options data for various form selects
 */
const giveDonorWallOptions = {};

// Form Display Styles
giveDonorWallOptions.columns = [
	{ value: 'best-fit', label: __( 'Best Fit' ) },
	{ value: '1', label: '1' },
	{ value: '2', label: '2' },
	{ value: '3', label: '3' },
	{ value: '4', label: '4' },
];

// Order
giveDonorWallOptions.order = [
	{ value: 'DESC', label: __( 'Newest to Oldest' ) },
	{ value: 'ASC', label: __( 'Oldest to Newest' ) },
];

export default giveDonorWallOptions;
