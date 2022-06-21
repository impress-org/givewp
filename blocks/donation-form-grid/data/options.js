/**
 * WordPress dependencies
 */
import {__} from '@wordpress/i18n'

/**
 * Options data for various form selects
 */
const giveFormOptions = {};

// Form Order By
giveFormOptions.orderBy = [
    {value: 'date', label: __('Date Created', 'give')},
    {value: 'title', label: __('Form Name', 'give')},
    {value: 'amount_donated', label: __('Amount Donated', 'give')},
    {value: 'number_donations', label: __('Number of Donations', 'give')},
    {value: 'menu_order', label: __('Menu Order', 'give')},
    {value: 'post__in', label: __('Provided Form IDs', 'give')},
    {value: 'closest_to_goal', label: __('Closest To Goal', 'give')},
    {value: 'random', label: __('Random', 'give')},
];

// Form Order
giveFormOptions.order = [
    {value: 'DESC', label: __('Descending', 'give')},
    {value: 'ASC', label: __('Ascending', 'give')},
];

// Form Display Styles
giveFormOptions.columns = [
    {value: '1', label: __('Full Width', 'give')},
    {value: '2', label: __('Double', 'give')},
    {value: '3', label: __('Triple', 'give')},
    {value: '4', label: __('Max (4)', 'give')},
];

// Form Display Styles
giveFormOptions.displayType = [
    {value: 'redirect', label: __('Redirect', 'give')},
    {value: 'modal_reveal', label: __('Modal', 'give')},
];

//Filter
giveFormOptions.filter = [
    {value: 'formIDs', label: __('Form ID', 'give')},
    {value: 'categories', label: __('Categories', 'give')},
    {value: 'tags', label: __('Tags', 'give')},
];

//Image Height
giveFormOptions.imageHeight = [
    {value: 'auto', label: __('Auto', 'give')},
    {value: 'custom', label: __('Custom', 'give')},
]

export default giveFormOptions;
