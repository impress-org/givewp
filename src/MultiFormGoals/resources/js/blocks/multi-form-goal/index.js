/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
const {registerBlockType} = wp.blocks;

/**
 * Internal dependencies
 */
import GiveIcon from '@givewp/components/GiveIcon';
import edit from './edit';
import save from './save';

/**
 * Required styles (both common and editor styles)
 */
import '../../../css/common.scss';
import '../../../css/editor.scss';

/**
 * Register Block
 */

export default registerBlockType('give/multi-form-goal', {
    title: __('Multi-Form Goal', 'give'),
    description: __(
        'The Multi-Form Goals block displays progress made across donation forms towards a common goal.',
        'give'
    ),
    category: 'give',
    icon: <GiveIcon color="grey" />,
    keywords: [__('donation', 'give'), __('multi form goals', 'give')],
    supports: {
        align: ['wide'],
    },
    edit: edit,
    save: save,
});
